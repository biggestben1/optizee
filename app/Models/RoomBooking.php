<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'booking_type',
        'booking_source',
        'room_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'guest_id_upload',
        'user_id',
        'shift_id',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'number_of_nights',
        'hours_stayed',
        'room_price_per_night',
        'hourly_rate',
        'subtotal',
        'discount',
        'tax',
        'service_charge',
        'total',
        'amount_paid',
        'change',
        'payment_method',
        'status',
        'is_credit_booking',
        'is_full_suite_booking',
        'notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_date' => 'date',
        'check_out_time' => 'datetime',
        'room_price_per_night' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change' => 'decimal:2',
        'is_credit_booking' => 'boolean',
        'is_full_suite_booking' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BOOK-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'checked-in']);
    }

    public function scopeCompleted($query)
    {
        // For reports - only include bookings that were actually completed (checked-out)
        return $query->where('status', 'checked-out');
    }

    public function scopeNotCancelled($query)
    {
        // Exclude cancelled bookings from reports and calculations
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['confirmed', 'checked-in']);
    }

    public function canCheckIn(): bool
    {
        return $this->status === 'confirmed' && $this->check_in_date && $this->check_in_date <= today();
    }

    public function canCheckOut(): bool
    {
        return $this->status === 'checked-in' && $this->check_out_date && $this->check_out_date <= today();
    }
}
