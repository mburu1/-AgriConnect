<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create an order from the user's active cart.
     */
    public function createFromCart(User $user, Cart $cart, array $deliveryDetails): Order
    {
        return DB::transaction(function () use ($user, $cart, $deliveryDetails) {
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
            }

            // Validate stock and reserve inventory
            foreach ($cart->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->lockForUpdate()->first();

                $net = $inventory ? ($inventory->quantity_available - $inventory->quantity_reserved) : 0;

                if ($item->quantity > $net && ! ($inventory?->allow_backorders ?? false)) {
                    throw ValidationException::withMessages([
                        'stock' => ["{$item->product->title}: only {$net} {$item->product->unit_measure} available."],
                    ]);
                }

                if ($inventory) {
                    $inventory->quantity_reserved += $item->quantity;
                    $inventory->save();
                }
            }

            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'user_id'         => $user->id,
                'subtotal_amount' => $cart->subtotal_amount,
                'shipping_fee'    => $cart->delivery_estimate_amount,
                'total_amount'    => $cart->total_amount,
                'status'          => 'PENDING',
                'payment_status'  => 'UNPAID',
                'payment_method'  => 'MPESA',
                'recipient_name'  => $deliveryDetails['recipient_name'],
                'recipient_phone' => $deliveryDetails['recipient_phone'],
                'county_id'       => $deliveryDetails['county_id'],
                'delivery_address'=> $deliveryDetails['delivery_address'],
                'delivery_instructions' => $deliveryDetails['delivery_instructions'] ?? null,
            ]);

            // Transfer cart items to order items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item->product_id,
                    'farmer_id'     => $item->product->farmer_id,
                    'product_title' => $item->product->title,
                    'unit_measure'  => $item->product->unit_measure,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'subtotal'      => $item->total_price,
                ]);
            }

            // Record initial status history
            OrderStatusHistory::create([
                'order_id'            => $order->id,
                'changed_by_user_id'  => $user->id,
                'from_status'         => null,
                'to_status'           => 'PENDING',
                'comment'             => 'Order placed by customer.',
            ]);

            // Clear cart
            $cart->items()->delete();
            $cart->recalculate();

            // Notify user
            Notification::create([
                'user_id' => $user->id,
                'type'    => 'ORDER_PLACED',
                'title'   => 'Order Placed!',
                'message' => "Your order #{$order->order_number} has been placed successfully.",
                'data'    => ['order_id' => $order->id],
            ]);

            AuditLog::record('CREATED_ORDER', 'orders', $order->id, null, $order->toArray());

            return $order->load(['items.product', 'statusHistories']);
        });
    }

    /**
     * Transition an order to a new status with validation.
     */
    public function updateStatus(Order $order, string $newStatus, User $changedBy, ?string $comment = null): Order
    {
        $allowedTransitions = [
            'PENDING'               => ['PAYMENT_PENDING', 'CANCELLED'],
            'PAYMENT_PENDING'       => ['PAID', 'PAYMENT_FAILED', 'CANCELLED'],
            'PAID'                  => ['PROCESSING', 'REFUNDED'],
            'PROCESSING'            => ['READY_FOR_FULFILLMENT', 'CANCELLED'],
            'READY_FOR_FULFILLMENT' => ['DISPATCHED'],
            'DISPATCHED'            => ['DELIVERED'],
            'DELIVERED'             => ['REFUNDED'],
            'PAYMENT_FAILED'        => ['PAYMENT_PENDING', 'CANCELLED'],
        ];

        $allowed = $allowedTransitions[$order->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from {$order->status} to {$newStatus}."],
            ]);
        }

        return DB::transaction(function () use ($order, $newStatus, $changedBy, $comment) {
            $oldStatus = $order->status;

            $updates = ['status' => $newStatus];

            if ($newStatus === 'DISPATCHED') {
                $updates['dispatched_at'] = now();
            } elseif ($newStatus === 'DELIVERED') {
                $updates['delivered_at'] = now();
            } elseif ($newStatus === 'CANCELLED') {
                $updates['cancelled_at']         = now();
                $updates['cancellation_reason']  = $comment;
                $this->releaseReservedStock($order);
            }

            $order->update($updates);

            OrderStatusHistory::create([
                'order_id'           => $order->id,
                'changed_by_user_id' => $changedBy->id,
                'from_status'        => $oldStatus,
                'to_status'          => $newStatus,
                'comment'            => $comment,
            ]);

            // Notify customer
            $this->notifyStatusChange($order, $newStatus);

            AuditLog::record(
                'UPDATED_ORDER_STATUS',
                'orders',
                $order->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );

            return $order->fresh()->load(['items', 'statusHistories', 'payment']);
        });
    }

    /**
     * Release reserved inventory when an order is cancelled.
     */
    private function releaseReservedStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)->first();
            if ($inventory) {
                $inventory->quantity_reserved = max(0, $inventory->quantity_reserved - $item->quantity);
                $inventory->save();
            }
        }
    }

    /**
     * Send a notification to the order owner on status change.
     */
    private function notifyStatusChange(Order $order, string $newStatus): void
    {
        $messages = [
            'PAID'                  => 'Your payment was confirmed. We\'re preparing your order.',
            'PROCESSING'            => 'Your order is being processed by the farmer.',
            'READY_FOR_FULFILLMENT' => 'Your order is packed and ready for dispatch.',
            'DISPATCHED'            => 'Your order is on the way!',
            'DELIVERED'             => 'Your order has been delivered. Enjoy your fresh produce!',
            'CANCELLED'             => 'Your order has been cancelled.',
            'REFUNDED'              => 'Your refund has been processed.',
        ];

        if (isset($messages[$newStatus])) {
            Notification::create([
                'user_id' => $order->user_id,
                'type'    => "ORDER_{$newStatus}",
                'title'   => "Order #{$order->order_number} Update",
                'message' => $messages[$newStatus],
                'data'    => ['order_id' => $order->id, 'status' => $newStatus],
            ]);
        }
    }
}
