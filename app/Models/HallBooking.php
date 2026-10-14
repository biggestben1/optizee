<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'hall_occasion_id',
        'occasion_name',
        'customer_name',
        'customer_phone',
        'customer_email',
        'event_date',
        'start_time',
        'end_time',
        'expected_guests',
        'price',
        'discount',
        'total',
        'amount_paid',
        'change',
        'cash_amount',
        'transfer_amount',
        'pos_amount',
        'payment_method',
        'status',
        'notes',
        'user_id',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'pos_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $prefix = 'HALL-' . date('Ymd') . '-';
                $count = static::where('booking_number', 'like', $prefix . '%')->count();
                $booking->booking_number = $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(HallOccasion::class, 'hall_occasion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function paymentBreakdown(): array
    {
        return [
            'cash' => (float) $this->cash_amount,
            'transfer' => (float) $this->transfer_amount,
            'pos' => (float) $this->pos_amount,
            'credit' => 0.0,
            'unsplit' => 0.0,
        ];
    }
}
