<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'farm_name',
        'slug',
        'bio',
        'id_number',
        'verification_status',
        'id_document_url',
        'total_farm_size_acres',
        'primary_specialization',
        'rating_average',
        'rating_count',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'total_farm_size_acres' => 'decimal:2',
        'rating_average'        => 'decimal:2',
        'rating_count'          => 'integer',
        'is_featured'           => 'boolean',
        'is_active'             => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(FarmerLocation::class);
    }

    public function primaryLocation(): HasOne
    {
        return $this->hasOne(FarmerLocation::class)->where('is_pickup_point', true)->latestOfMany();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getAvatarAttribute(): ?string
    {
        return $this->user?->avatar_url;
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verification_status === 'VERIFIED';
    }
}
