<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Resolve or create cart for authenticated user or session.
     */
    public function resolveCart(?User $user, ?string $sessionId): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add or update a product in the cart.
     */
    public function addItem(Cart $cart, int $productId, float $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $productId, $quantity) {
            $product = Product::with('inventory')->findOrFail($productId);

            // Validate stock
            $available = $product->inventory?->quantity_net ?? 0;
            if ($quantity > $available && ! ($product->inventory?->allow_backorders ?? false)) {
                throw ValidationException::withMessages([
                    'quantity' => ["Only {$available} {$product->unit_measure} available."],
                ]);
            }

            // Check min/max
            if ($quantity < $product->min_order_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ["Minimum order is {$product->min_order_quantity} {$product->unit_measure}."],
                ]);
            }
            if ($product->max_order_quantity && $quantity > $product->max_order_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ["Maximum order is {$product->max_order_quantity} {$product->unit_measure}."],
                ]);
            }

            $item = $cart->items()->updateOrCreate(
                ['product_id' => $productId],
                [
                    'quantity'    => $quantity,
                    'unit_price'  => $product->price,
                    'total_price' => round($quantity * $product->price, 2),
                ]
            );

            $cart->recalculate();

            return $item->load('product.primaryImage');
        });
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Cart $cart, int $cartItemId): void
    {
        $cart->items()->findOrFail($cartItemId)->delete();
        $cart->recalculate();
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update([
            'subtotal_amount'          => 0,
            'delivery_estimate_amount' => 0,
            'total_amount'             => 0,
        ]);
    }

    /**
     * Merge a guest session cart into an authenticated user cart.
     */
    public function mergeGuestCart(string $sessionId, User $user): Cart
    {
        return DB::transaction(function () use ($sessionId, $user) {
            $guestCart = Cart::where('session_id', $sessionId)->first();

            if (! $guestCart || $guestCart->items->isEmpty()) {
                return $this->resolveCart($user, null);
            }

            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $this->addItem($userCart, $guestItem->product_id, $guestItem->quantity);
            }

            $guestCart->delete();

            return $userCart->load('items.product.primaryImage');
        });
    }
}
