<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'cost_price',
        'selling_price',
        'vip_price',
        'stock_quantity',
        'reorder_level',
        'unit',
        'preparation_time',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'vip_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Price charged on VIP Bar POS. Falls back to the normal selling price.
     */
    public function getVipPrice(): float
    {
        return $this->vip_price !== null && (float) $this->vip_price > 0
            ? (float) $this->vip_price
            : (float) $this->selling_price;
    }

    /**
     * Selling price for a given POS area (VIP Bar uses VIP price).
     */
    public function priceForPosArea(?string $area): float
    {
        return $area === 'vip_bar' ? $this->getVipPrice() : (float) $this->selling_price;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where(function($q) {
            $q->whereHas('category', function($categoryQuery) {
                $categoryQuery->where('slug', 'beer');
            })->where('stock_quantity', '<=', 12)
            ->orWhere(function($q2) {
                $q2->whereDoesntHave('category', function($categoryQuery) {
                    $categoryQuery->where('slug', 'beer');
                })->whereColumn('stock_quantity', '<=', 'reorder_level');
            });
        });
    }

    public function isLowStock(): bool
    {
        // Beer products are low stock when stock is 12 or less
        if ($this->category && $this->category->slug === 'beer') {
            return $this->stock_quantity <= 12;
        }
        
        // Other products use reorder_level
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function getProfit(): float
    {
        return $this->selling_price - $this->cost_price;
    }
}






