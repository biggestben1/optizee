<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_type',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        if ($this->account_type === 'Supplier') {
            return $this->belongsTo(Supplier::class, 'account_id');
        }
        // Add more account types here as needed
        return null;
    }
}
