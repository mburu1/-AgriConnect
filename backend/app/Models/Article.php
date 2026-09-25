<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'featured_image_url',
        'category',
        'tags',
        'status',
        'published_at',
        'view_count',
    ];

    protected $casts = [
        'tags'         => 'array',
        'published_at' => 'datetime',
        'view_count'   => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
