<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawItem extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'external_id',
        'source_id',
        'hash',
        'data',
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
