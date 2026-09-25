<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortCode;
    private string $passKey;
    private string $callbackUrl;
    private string $baseUrl;

    public function __construct()
    {
        $this->consumerKey    = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortCode      = config('mpesa.shortcode');
        $this->passKey        = config('mpesa.passkey');
        $this->callbackUrl    = config('mpesa.callback_url');
        $this->baseUrl        = config('mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Initiate M-Pesa STK Push (Lipa na M-Pesa Online).
     */
    public function stkPush(Order $order, string $phoneNumber): array
    {
        $accessToken = $this->getAccessToken();

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortCode . $this->passKey . $timestamp);

        // Normalize phone: 07xx → 2547xx
        $phone = $this->normalizePhone($phoneNumber);

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                'BusinessShortCode' => $this->shortCode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => (int) ceil($order->total_amount),
                'PartyA'            => $phone,
                'PartyB'            => $this->shortCode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $this->callbackUrl,
                'AccountReference'  => $order->order_number,
                'TransactionDesc'   => "AgriConnect order #{$order->order_number}",
            ]);

        $data = $response->json();

        if ($response->failed() || ($data['ResponseCode'] ?? '1') !== '0') {
            Log::error('M-Pesa STK Push failed', ['response' => $data, 'order_id' => $order->id]);
            throw new \RuntimeException($data['errorMessage'] ?? 'M-Pesa request failed. Please try again.');
        }

        // Create payment record
        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'user_id'           => $order->user_id,
                'payment_reference' => Str::uuid(),
                'payment_method'    => 'MPESA',
                'amount'            => $order->total_amount,
                'currency'          => 'KES',
                'status'            => 'PENDING',
            ]
        );

        // Log the STK transaction initiation
        PaymentTransaction::create([
            'payment_id'          => $payment->id,
            'gateway'             => 'MPESA_DARAJA',
            'merchant_request_id' => $data['MerchantRequestID'] ?? null,
            'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
            'phone_number'        => $phone,
            'amount'              => $order->total_amount,
        ]);

        return [
            'merchant_request_id' => $data['MerchantRequestID'],
            'checkout_request_id' => $data['CheckoutRequestID'],
            'customer_message'    => $data['CustomerMessage'],
        ];
    }

    /**
     * Handle M-Pesa callback from Safaricom.
     */
    public function handleCallback(array $payload): void
    {
        $body     = $payload['Body']['stkCallback'] ?? [];
        $resultCode = $body['ResultCode'] ?? -1;
        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;

        $transaction = PaymentTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $transaction) {
            Log::warning('M-Pesa callback: unknown CheckoutRequestID', ['id' => $checkoutRequestId]);
            return;
        }

        $items = collect($body['CallbackMetadata']['Item'] ?? [])
            ->pluck('Value', 'Name');

        $transaction->update([
            'result_code'          => $resultCode,
            'result_desc'          => $body['ResultDesc'] ?? null,
            'mpesa_receipt_number' => $items->get('MpesaReceiptNumber'),
            'amount'               => $items->get('Amount'),
            'phone_number'         => $items->get('PhoneNumber'),
            'transaction_date'     => $items->has('TransactionDate')
                ? Carbon::createFromFormat('YmdHis', $items->get('TransactionDate'))
                : null,
            'raw_response'         => $payload,
        ]);

        $payment = $transaction->payment;
        $order   = $payment->order;

        if ($resultCode === 0) {
            // Success
            $payment->update(['status' => 'COMPLETED']);
            $order->update(['payment_status' => 'PAID', 'status' => 'PAID']);

            AuditLog::record('PROCESSED_PAYMENT', 'payments', $payment->id, null, [
                'receipt' => $items->get('MpesaReceiptNumber'),
                'amount'  => $items->get('Amount'),
            ]);
        } else {
            // Failed
            $payment->update(['status' => 'FAILED']);
            $order->update(['payment_status' => 'FAILED', 'status' => 'PAYMENT_FAILED']);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

        if ($response->failed()) {
            throw new \RuntimeException('Could not obtain M-Pesa access token.');
        }

        return $response->json('access_token');
    }

    private function normalizePhone(string $phone): string
    {
        // Strip spaces, dashes, plus
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        // 07... → 2547...
        if (str_starts_with($phone, '07') || str_starts_with($phone, '01')) {
            $phone = '254' . substr($phone, 1);
        }

        return $phone;
    }
}
