<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_date',
        'category',
        'description',
        'items_description',
        'amount',
        'payment_method',
        'vendor',
        'notes',
        'user_id',
        'receipt_number',
        'is_direct_cost',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'is_direct_cost' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public static function getCategories(): array
    {
        return [
            'bar' => 'Bar Expenses',
            'hotel' => 'Hotel Expenses',
            'kitchen' => 'Kitchen',
            'electricity' => 'Electricity',
            'water' => 'Water',
            'internet' => 'Internet',
            'maintenance' => 'Maintenance',
            'supplies' => 'Supplies',
            'staff' => 'Staff Expenses',
            'marketing' => 'Marketing',
            'insurance' => 'Insurance',
            'rent' => 'Rent',
            'other' => 'Other',
        ];
    }
}
