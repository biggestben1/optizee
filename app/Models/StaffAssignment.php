<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'section_id',
        'assigned_by',
        'shift_type',
        'shift_start',
        'shift_end',
        'start_date',
        'end_date',
        'working_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'working_days' => 'array',
        'is_active' => 'boolean',
    ];

    public const SHIFT_TYPES = [
        'morning' => ['label' => 'Morning', 'start' => '06:00', 'end' => '14:00'],
        'afternoon' => ['label' => 'Afternoon', 'start' => '14:00', 'end' => '22:00'],
        'evening' => ['label' => 'Evening', 'start' => '18:00', 'end' => '02:00'],
        'night' => ['label' => 'Night', 'start' => '22:00', 'end' => '06:00'],
        'full_day' => ['label' => 'Full Day', 'start' => '08:00', 'end' => '20:00'],
        'custom' => ['label' => 'Custom', 'start' => null, 'end' => null],
    ];

    public const DAYS_OF_WEEK = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getShiftLabel(): string
    {
        return self::SHIFT_TYPES[$this->shift_type]['label'] ?? 'Unknown';
    }

    public function getShiftTime(): string
    {
        if ($this->shift_type === 'custom') {
            return $this->shift_start . ' - ' . $this->shift_end;
        }
        
        $shift = self::SHIFT_TYPES[$this->shift_type] ?? null;
        if ($shift) {
            return $shift['start'] . ' - ' . $shift['end'];
        }
        
        return 'N/A';
    }

    public function getWorkingDaysFormatted(): string
    {
        if (empty($this->working_days)) {
            return 'All Days';
        }

        if (count($this->working_days) === 7) {
            return 'All Days';
        }

        return collect($this->working_days)
            ->map(fn($day) => ucfirst(substr($day, 0, 3)))
            ->implode(', ');
    }
}










