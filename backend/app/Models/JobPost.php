<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'department',
        'location_type',
        'county',
        'employment_type',
        'description',
        'requirements',
        'deadline_date',
        'is_active',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'is_active'     => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_post_id');
    }
}
