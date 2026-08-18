<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'pr_date',
        'requested_by',
        'approved_by',
        'description',
        'items_description',
        'amount',
        'status',
        'notes',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'pr_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pr) {
            if (empty($pr->pr_number)) {
                $pr->pr_number = 'PR-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function approve(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);
    }

    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'rejection_reason' => $reason,
        ]);
    }
}
