<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService  $cartService,
        private readonly MpesaService $mpesaService,
    ) {}

    /**
     * List authenticated user's orders.
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product.primaryImage', 'payment'])
            ->latest()
            ->paginate(10);

        return $this->successResponse($orders);
    }

    /**
     * Show order details.
     * GET /api/orders/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'statusHistories', 'payment.transactions', 'county'])
            ->findOrFail($id);

        return $this->successResponse($order);
    }

    /**
     * Place an order from the active cart.
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recipient_name'         => ['required', 'string', 'max:255'],
            'recipient_phone'        => ['required', 'string'],
            'county_id'              => ['required', 'exists:counties,id'],
            'delivery_address'       => ['required', 'string'],
            'delivery_instructions'  => ['nullable', 'string'],
        ]);

        $cart = $this->cartService->resolveCart(
            $request->user(),
            $request->header('X-Session-Id')
        )->load('items.product');

        $order = $this->orderService->createFromCart($request->user(), $cart, $data);

        return $this->successResponse($order, 'Order placed successfully.', 201);
    }

    /**
     * Initiate M-Pesa STK Push payment.
     * POST /api/orders/{id}/pay
     */
    public function pay(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $result = $this->mpesaService->stkPush($order, $data['phone_number']);

        return $this->successResponse($result, 'STK Push sent. Please complete payment on your phone.');
    }

    /**
     * M-Pesa callback (unauthenticated — called by Safaricom).
     * POST /api/payments/mpesa/callback
     */
    public function mpesaCallback(Request $request): JsonResponse
    {
        $this->mpesaService->handleCallback($request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Cancel an order.
     * POST /api/orders/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->orderService->updateStatus(
            $order,
            'CANCELLED',
            $request->user(),
            $data['reason'] ?? 'Cancelled by customer.'
        );

        return $this->successResponse($order, 'Order cancelled.');
    }

    // ─── Admin / Farmer order management ─────────────────────────

    /**
     * Update order status (Admin / Farmer).
     * POST /api/admin/orders/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $data = $request->validate([
            'status'  => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ]);

        $order = $this->orderService->updateStatus(
            $order,
            $data['status'],
            $request->user(),
            $data['comment'] ?? null
        );

        return $this->successResponse($order, 'Order status updated.');
    }
}
