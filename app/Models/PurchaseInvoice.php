<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'expected_delivery_date',
        'total_amount',
        'supplier_name',
        'supplier_phone',
        'supplier_address',
        'notes',
        'items',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'items' => 'array',
    ];

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
