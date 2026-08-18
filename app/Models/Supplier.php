<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'products_supplied',
        'balance_owed',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'balance_owed' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(SupplierSupply::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithOutstandingBalance($query)
    {
        return $query->where('balance_owed', '>', 0);
    }

    public function addToBalance(float $amount): void
    {
        $this->increment('balance_owed', $amount);
    }

    public function reduceBalance(float $amount): void
    {
        $this->decrement('balance_owed', min($amount, $this->balance_owed));
    }
}











