<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'opened_at',
        'closed_at',
        'closed_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getTotalCashSales(): float
    {
        return $this->sales()
            ->where('payment_method', 'cash')
            ->where('status', 'completed')
            ->sum('amount_paid');
    }

    public function getTotalSales(): float
    {
        return $this->sales()
            ->where('status', 'completed')
            ->sum('total');
    }

    public function close(float $closingCash, ?int $closedBy = null, ?string $notes = null): void
    {
        $expectedCash = $this->opening_cash + $this->getTotalCashSales() + 
                        $this->customerPayments()->where('payment_method', 'cash')->sum('amount');

        $this->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $closingCash - $expectedCash,
            'closed_at' => now(),
            'closed_by' => $closedBy,
            'notes' => $notes,
            'status' => 'closed',
        ]);
    }
}











