<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'purchase_date',
        'user_id',
        'description',
        'items_description',
        'amount',
        'notes',
        'vendor',
        'payment_method',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $purchase->purchase_number = 'OWN-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
