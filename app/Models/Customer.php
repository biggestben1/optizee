<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'credit_limit',
        'credit_balance',
        'credit_enabled',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'credit_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithCredit($query)
    {
        return $query->where('credit_enabled', true);
    }

    public function scopeWithOutstandingBalance($query)
    {
        return $query->where('credit_balance', '>', 0);
    }

    public function getAvailableCredit(): float
    {
        return max(0, $this->credit_limit - $this->credit_balance);
    }

    public function canPurchaseOnCredit(float $amount): bool
    {
        return $this->credit_enabled && $this->getAvailableCredit() >= $amount;
    }

    public function addToBalance(float $amount): void
    {
        $current = (float) $this->credit_balance;
        $this->forceFill([
            'credit_balance' => $current + $amount,
        ])->save();
    }

    public function reduceBalance(float $amount): void
    {
        $current = (float) $this->credit_balance;
        $next = max(0.0, $current - $amount);
        $this->forceFill([
            'credit_balance' => $next,
        ])->save();
    }
}











