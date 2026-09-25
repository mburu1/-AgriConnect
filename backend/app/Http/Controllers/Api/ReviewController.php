<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * List approved reviews for a product.
     * GET /api/products/{productId}/reviews
     */
    public function index(int $productId): JsonResponse
    {
        $reviews = Review::with('user:id,name,avatar_url')
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->latest()
            ->paginate(15);

        return $this->successResponse($reviews);
    }

    /**
     * Submit a review (must have a delivered order with this product).
     * POST /api/products/{productId}/reviews
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:150'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        // Verify purchase
        $order = Order::where('user_id', $request->user()->id)
            ->where('status', 'DELIVERED')
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->first();

        // Check duplicate
        if (Review::where('user_id', $request->user()->id)->where('product_id', $productId)->exists()) {
            throw ValidationException::withMessages(['review' => ['You have already reviewed this product.']]);
        }

        $review = Review::create([
            'user_id'              => $request->user()->id,
            'product_id'           => $productId,
            'farmer_id'            => $product->farmer_id,
            'order_id'             => $order?->id,
            'rating'               => $data['rating'],
            'title'                => $data['title'] ?? null,
            'comment'              => $data['comment'],
            'is_verified_purchase' => $order !== null,
            'status'               => 'APPROVED',
        ]);

        // Update product rating
        $this->updateProductRating($product);

        return $this->successResponse($review->load('user:id,name,avatar_url'), 'Review submitted.', 201);
    }

    private function updateProductRating(Product $product): void
    {
        $avg   = Review::where('product_id', $product->id)->where('status', 'APPROVED')->avg('rating');
        $count = Review::where('product_id', $product->id)->where('status', 'APPROVED')->count();

        $product->update([
            'rating_average' => round($avg ?? 5, 2),
            'rating_count'   => $count,
        ]);
    }
}
