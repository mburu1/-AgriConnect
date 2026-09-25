<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly CartService $cartService) {}

    /**
     * Get the current cart.
     * GET /api/cart
     */
    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart(
            $request->user(),
            $request->header('X-Session-Id')
        )->load('items.product.primaryImage');

        return $this->successResponse($cart);
    }

    /**
     * Add or update a product in the cart.
     * POST /api/cart/items
     */
    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $cart = $this->cartService->resolveCart(
            $request->user(),
            $request->header('X-Session-Id')
        );

        $item = $this->cartService->addItem($cart, $data['product_id'], $data['quantity']);

        return $this->successResponse($item, 'Item added to cart.', 201);
    }

    /**
     * Remove an item from the cart.
     * DELETE /api/cart/items/{itemId}
     */
    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->resolveCart(
            $request->user(),
            $request->header('X-Session-Id')
        );

        $this->cartService->removeItem($cart, $itemId);

        return $this->successResponse(null, 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     * DELETE /api/cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart(
            $request->user(),
            $request->header('X-Session-Id')
        );

        $this->cartService->clear($cart);

        return $this->successResponse(null, 'Cart cleared.');
    }

    /**
     * Merge guest cart into authenticated user cart (call after login).
     * POST /api/cart/merge
     */
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        $cart = $this->cartService->mergeGuestCart(
            $request->input('session_id'),
            $request->user()
        );

        return $this->successResponse($cart, 'Cart merged successfully.');
    }
}
