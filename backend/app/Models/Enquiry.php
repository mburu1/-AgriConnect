<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'subject',
        'category',
        'message',
        'status',
        'internal_notes',
    ];
}
