<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'entry_date',
        'description',
        'user_id',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->reference_number)) {
                $entry->reference_number = 'JV-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }
}
