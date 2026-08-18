<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'value',
        'purchase_date',
        'serial_number',
        'location',
        'description',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'value' => 'decimal:2',
    ];
}
