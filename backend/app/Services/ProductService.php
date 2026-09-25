<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Farmer;
use App\Models\Inventory;
use App\Models\ProductImage;
use App\Models\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create a new product with inventory and optional images.
     */
    public function create(Farmer $farmer, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($farmer, $data, $images) {
            $product = $farmer->products()->create([
                'category_id'        => $data['category_id'],
                'title'              => $data['title'],
                'slug'               => $this->generateSlug($data['title']),
                'summary'            => $data['summary'] ?? null,
                'description'        => $data['description'] ?? null,
                'price'              => $data['price'],
                'compare_at_price'   => $data['compare_at_price'] ?? null,
                'unit_measure'       => $data['unit_measure'],
                'min_order_quantity' => $data['min_order_quantity'] ?? 1,
                'max_order_quantity' => $data['max_order_quantity'] ?? null,
                'sku'                => $data['sku'] ?? null,
                'status'             => $data['status'] ?? 'PUBLISHED',
                'is_organic'         => $data['is_organic'] ?? false,
            ]);

            // Initialize inventory
            Inventory::create([
                'product_id'          => $product->id,
                'quantity_available'  => $data['quantity_available'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
                'next_harvest_date'   => $data['next_harvest_date'] ?? null,
            ]);

            // Upload and attach images
            foreach ($images as $i => $image) {
                $url = $this->uploadImage($image, "products/{$product->id}");
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => $url,
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            AuditLog::record('CREATED_PRODUCT', 'products', $product->id, null, $product->toArray());

            return $product->load(['inventory', 'images', 'category']);
        });
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $old = $product->toArray();

            $product->update(array_filter([
                'category_id'        => $data['category_id'] ?? null,
                'title'              => $data['title'] ?? null,
                'slug'               => isset($data['title']) ? $this->generateSlug($data['title']) : null,
                'summary'            => $data['summary'] ?? null,
                'description'        => $data['description'] ?? null,
                'price'              => $data['price'] ?? null,
                'compare_at_price'   => $data['compare_at_price'] ?? null,
                'unit_measure'       => $data['unit_measure'] ?? null,
                'min_order_quantity' => $data['min_order_quantity'] ?? null,
                'max_order_quantity' => $data['max_order_quantity'] ?? null,
                'status'             => $data['status'] ?? null,
                'is_organic'         => $data['is_organic'] ?? null,
                'is_featured'        => $data['is_featured'] ?? null,
            ], fn($v) => $v !== null));

            if (isset($data['quantity_available'])) {
                $product->inventory()->update([
                    'quantity_available'  => $data['quantity_available'],
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? $product->inventory->low_stock_threshold,
                    'next_harvest_date'   => $data['next_harvest_date'] ?? $product->inventory->next_harvest_date,
                ]);
            }

            AuditLog::record('UPDATED_PRODUCT', 'products', $product->id, $old, $product->fresh()->toArray());

            return $product->fresh()->load(['inventory', 'images', 'category']);
        });
    }

    /**
     * Soft-delete a product.
     */
    public function delete(Product $product): void
    {
        $product->delete();
        AuditLog::record('DELETED_PRODUCT', 'products', $product->id);
    }

    /**
     * Adjust stock (positive = restock, negative = deduct).
     */
    public function adjustStock(Product $product, float $delta, string $reason = ''): Inventory
    {
        $inventory = $product->inventory;
        $inventory->quantity_available = max(0, $inventory->quantity_available + $delta);
        $inventory->save();

        AuditLog::record(
            'ADJUSTED_INVENTORY',
            'inventories',
            $inventory->id,
            ['quantity_available' => $inventory->quantity_available - $delta],
            ['quantity_available' => $inventory->quantity_available, 'reason' => $reason]
        );

        return $inventory;
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function generateSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function uploadImage(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');
        return Storage::url($path);
    }
}
