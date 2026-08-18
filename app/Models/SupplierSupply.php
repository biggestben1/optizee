<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierSupply extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'reference_number',
        'total_amount',
        'amount_paid',
        'balance',
        'payment_type',
        'items_description',
        'supply_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'supply_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supply) {
            if (empty($supply->reference_number)) {
                $supply->reference_number = 'SUP-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}











