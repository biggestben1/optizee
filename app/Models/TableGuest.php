<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'guest_name',
        'customer_id',
        'created_by',
        'seated_at',
        'left_at',
        'is_active',
    ];

    protected $casts = [
        'seated_at' => 'datetime',
        'left_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalBill(): float
    {
        return $this->sales()
            ->where('status', 'completed')
            ->sum('total');
    }

    public function leave(): void
    {
        $pendingSales = $this->sales()->where('status', 'pending')->get();
        foreach ($pendingSales as $sale) {
            $sale->items()->delete();
            $sale->delete();
        }

        $this->update([
            'is_active' => false,
            'left_at' => now(),
        ]);
    }
}









