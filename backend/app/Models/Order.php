<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'farmer_id',
        'subtotal_amount',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'recipient_name',
        'recipient_phone',
        'county_id',
        'delivery_address',
        'delivery_instructions',
        'dispatched_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal_amount'  => 'decimal:2',
        'shipping_fee'     => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'dispatched_at'    => 'datetime',
        'delivered_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, ['PENDING', 'PAYMENT_PENDING', 'PAID', 'PROCESSING']);
    }

    public static function generateOrderNumber(): string
    {
        $year      = now()->format('Y');
        $sequence  = str_pad(self::whereYear('created_at', $year)->count() + 1, 4, '0', STR_PAD_LEFT);
        return "AGC-{$year}-{$sequence}";
    }
}
