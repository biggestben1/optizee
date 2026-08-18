<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'capacity',
        'location',
        'status',
        'current_shift_id',
        'served_by',
        'occupied_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'occupied_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function guests(): HasMany
    {
        return $this->hasMany(TableGuest::class);
    }

    public function activeGuests(): HasMany
    {
        return $this->hasMany(TableGuest::class)->where('is_active', true);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function currentShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'current_shift_id');
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function occupy(int $userId, ?int $shiftId = null): void
    {
        $this->update([
            'status' => 'occupied',
            'served_by' => $userId,
            'current_shift_id' => $shiftId,
            'occupied_at' => now(),
        ]);
    }

    public function release(): void
    {
        $this->update([
            'status' => 'available',
            'served_by' => null,
            'current_shift_id' => null,
            'occupied_at' => null,
        ]);
    }

    public function getTotalBill(): float
    {
        return $this->sales()
            ->where('status', 'completed')
            ->whereNull('table_guest_id') // Combined bill
            ->sum('total');
    }

    public function getGuestBills(): array
    {
        return $this->activeGuests->map(function ($guest) {
            return [
                'guest' => $guest,
                'total' => $guest->getTotalBill(),
            ];
        })->toArray();
    }
}









