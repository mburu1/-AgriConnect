<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal_amount',
        'delivery_estimate_amount',
        'total_amount',
    ];

    protected $casts = [
        'subtotal_amount'          => 'decimal:2',
        'delivery_estimate_amount' => 'decimal:2',
        'total_amount'             => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculate(): self
    {
        $this->subtotal_amount = $this->items->sum('total_price');
        $this->total_amount    = $this->subtotal_amount + $this->delivery_estimate_amount;
        $this->save();
        return $this;
    }
}
