<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    public const AREAS = [
        'rooms' => 'Hotel Rooms',
        'hall' => 'Hall Rentals',
        'bar' => 'Bar',
        'kitchen' => 'Kitchen / Restaurant',
        'other' => 'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'pos' => 'POS',
    ];

    protected $fillable = [
        'refund_number',
        'refund_date',
        'area',
        'reference',
        'customer_name',
        'customer_phone',
        'amount',
        'payment_method',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $prefix = 'REF-' . date('Ymd') . '-';
                $count = static::where('refund_number', 'like', $prefix . '%')->count();
                $refund->refund_number = $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAreaLabelAttribute(): string
    {
        return self::AREAS[$this->area] ?? ucfirst($this->area);
    }
}
