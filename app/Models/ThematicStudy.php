<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThematicStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'term',
        'content',
        'source',
        'access_count',
    ];

    protected $casts = [
        'access_count' => 'integer',
    ];
}
