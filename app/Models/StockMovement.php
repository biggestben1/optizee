<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'supplier_supply_id',
        'purchase_invoice_id',
        'sale_id',
        'type',
        'quantity',
        'quantity_ordered',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reason',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplierSupply(): BelongsTo
    {
        return $this->belongsTo(SupplierSupply::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }
}











