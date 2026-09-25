<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farmer_id',
        'category_id',
        'title',
        'slug',
        'summary',
        'description',
        'price',
        'compare_at_price',
        'unit_measure',
        'min_order_quantity',
        'max_order_quantity',
        'sku',
        'status',
        'is_organic',
        'is_featured',
        'rating_average',
        'rating_count',
        'view_count',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'compare_at_price'   => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'max_order_quantity' => 'decimal:2',
        'rating_average'     => 'decimal:2',
        'rating_count'       => 'integer',
        'view_count'         => 'integer',
        'is_organic'         => 'boolean',
        'is_featured'        => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getIsInStockAttribute(): bool
    {
        return $this->inventory
            ? $this->inventory->quantity_available > 0
            : false;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->compare_at_price && $this->compare_at_price > $this->price) {
            return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
        }
        return null;
    }
}
