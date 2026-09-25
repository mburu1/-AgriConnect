<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_available',
        'quantity_reserved',
        'low_stock_threshold',
        'allow_backorders',
        'next_harvest_date',
    ];

    protected $casts = [
        'quantity_available'  => 'decimal:2',
        'quantity_reserved'   => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'allow_backorders'    => 'boolean',
        'next_harvest_date'   => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getQuantityNetAttribute(): float
    {
        return max(0, (float) $this->quantity_available - (float) $this->quantity_reserved);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->getQuantityNetAttribute() <= (float) $this->low_stock_threshold;
    }
}
