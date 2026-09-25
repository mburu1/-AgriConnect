<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'payment_id',
        'gateway',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'phone_number',
        'amount',
        'result_code',
        'result_desc',
        'raw_response',
        'transaction_date',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'result_code'      => 'integer',
        'raw_response'     => 'array',
        'transaction_date' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isSuccessful(): bool
    {
        return $this->result_code === 0;
    }
}
