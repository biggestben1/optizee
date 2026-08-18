<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_category_id',
        'room_number',
        'room_name',
        'price_per_night',
        'hourly_rate',
        'status',
        'is_suite_sub_room',
        'parent_suite_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_suite_sub_room' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_category_id');
    }

    public function parentSuite(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'parent_suite_id');
    }

    public function subRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'parent_suite_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function activeBookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class)
            ->whereIn('status', ['confirmed', 'checked-in']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('status', 'checked-in');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isBooked(): bool
    {
        return in_array($this->status, ['booked', 'checked-in']);
    }

    public function getEffectivePrice(): float
    {
        // If room has custom price, use it; otherwise use category price
        if ($this->price_per_night) {
            return (float) $this->price_per_night;
        }
        return (float) ($this->category->price_per_room ?? 0);
    }

    /**
     * Hourly rate for short-stay bookings: room override, else category, else nightly ÷ 24.
     */
    public function getEffectiveHourlyRate(): float
    {
        if ($this->hourly_rate !== null && (float) $this->hourly_rate > 0) {
            return round((float) $this->hourly_rate, 2);
        }

        $category = $this->category;
        if ($category && $category->hourly_rate !== null && (float) $category->hourly_rate > 0) {
            return round((float) $category->hourly_rate, 2);
        }

        return round($this->getEffectivePrice() / 24, 2);
    }

    public function getDisplayName(): string
    {
        if ($this->room_name) {
            return $this->room_number . ' - ' . $this->room_name;
        }
        return $this->room_number;
    }
}
