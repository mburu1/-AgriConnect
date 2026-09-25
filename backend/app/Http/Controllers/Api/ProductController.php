<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ProductService $productService) {}

    /**
     * List published products with filters & pagination.
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['farmer.user', 'primaryImage', 'inventory', 'category'])
            ->where('status', 'PUBLISHED');

        // Filters
        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($farmerId = $request->integer('farmer_id')) {
            $query->where('farmer_id', $farmerId);
        }
        if ($isOrganic = $request->boolean('is_organic', false)) {
            $query->where('is_organic', true);
        }
        if ($min = $request->numeric('min_price')) {
            $query->where('price', '>=', $min);
        }
        if ($max = $request->numeric('max_price')) {
            $query->where('price', '<=', $max);
        }
        if ($search = $request->string('search')) {
            $query->where(fn($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
            );
        }

        // Sort
        match ($request->string('sort', 'latest')) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'rating'      => $query->orderBy('rating_average', 'desc'),
            'popular'     => $query->orderBy('view_count', 'desc'),
            default       => $query->latest(),
        };

        $products = $query->paginate($request->integer('per_page', 20));

        return $this->successResponse($products);
    }

    /**
     * Show a single product by slug and track view.
     * GET /api/products/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with([
            'farmer.user',
            'farmer.primaryLocation.county',
            'images',
            'inventory',
            'category',
            'reviews' => fn($q) => $q->where('status', 'APPROVED')->latest()->limit(10),
        ])->where('slug', $slug)->where('status', 'PUBLISHED')->firstOrFail();

        $product->increment('view_count');

        return $this->successResponse($product);
    }

    /**
     * Create a product (Farmer only).
     * POST /api/farmer/products
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'        => ['required', 'exists:product_categories,id'],
            'title'              => ['required', 'string', 'max:255'],
            'summary'            => ['nullable', 'string', 'max:500'],
            'description'        => ['nullable', 'string'],
            'price'              => ['required', 'numeric', 'min:0.01'],
            'compare_at_price'   => ['nullable', 'numeric', 'gt:price'],
            'unit_measure'       => ['required', 'string', 'max:50'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'max_order_quantity' => ['nullable', 'numeric', 'gt:min_order_quantity'],
            'sku'                => ['nullable', 'string', 'unique:products,sku'],
            'status'             => ['nullable', 'in:DRAFT,PUBLISHED'],
            'is_organic'         => ['boolean'],
            'quantity_available' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold'=> ['nullable', 'numeric', 'min:0'],
            'next_harvest_date'  => ['nullable', 'date', 'after:today'],
            'images'             => ['nullable', 'array', 'max:10'],
            'images.*'           => ['image', 'max:5120'], // 5 MB per image
        ]);

        $farmer  = $request->user()->farmer;
        $images  = $request->file('images', []);
        $product = $this->productService->create($farmer, $data, $images);

        return $this->successResponse($product, 'Product created successfully.', 201);
    }

    /**
     * Update a product (owner only).
     * PUT /api/farmer/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::where('farmer_id', $request->user()->farmer->id)->findOrFail($id);

        $data = $request->validate([
            'category_id'        => ['sometimes', 'exists:product_categories,id'],
            'title'              => ['sometimes', 'string', 'max:255'],
            'summary'            => ['nullable', 'string', 'max:500'],
            'description'        => ['nullable', 'string'],
            'price'              => ['sometimes', 'numeric', 'min:0.01'],
            'compare_at_price'   => ['nullable', 'numeric'],
            'unit_measure'       => ['sometimes', 'string', 'max:50'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'max_order_quantity' => ['nullable', 'numeric'],
            'status'             => ['sometimes', 'in:DRAFT,PUBLISHED,OUT_OF_STOCK,ARCHIVED'],
            'is_organic'         => ['boolean'],
            'is_featured'        => ['boolean'],
            'quantity_available' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold'=> ['nullable', 'numeric', 'min:0'],
            'next_harvest_date'  => ['nullable', 'date'],
        ]);

        $product = $this->productService->update($product, $data);

        return $this->successResponse($product, 'Product updated successfully.');
    }

    /**
     * Soft-delete a product (owner only).
     * DELETE /api/farmer/products/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::where('farmer_id', $request->user()->farmer->id)->findOrFail($id);
        $this->productService->delete($product);

        return $this->successResponse(null, 'Product deleted successfully.');
    }

    /**
     * List all categories.
     * GET /api/categories
     */
    public function categories(): JsonResponse
    {
        $categories = ProductCategory::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse($categories);
    }
}
