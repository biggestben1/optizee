<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_per_room',
        'hourly_rate',
        'full_suite_price',
        'total_rooms',
        'is_suite',
        'is_active',
    ];

    protected $casts = [
        'price_per_room' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'full_suite_price' => 'decimal:2',
        'is_suite' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function activeRooms(): HasMany
    {
        return $this->hasMany(Room::class)->where('is_active', true);
    }

    public function availableRooms(): HasMany
    {
        return $this->hasMany(Room::class)
            ->where('is_active', true)
            ->where('status', 'available');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSuites($query)
    {
        return $query->where('is_suite', true);
    }
}
