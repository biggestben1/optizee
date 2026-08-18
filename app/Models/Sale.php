<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'shift_id',
        'customer_id',
        'table_id',
        'table_guest_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
        'change',
        'payment_method',
        'status',
        'kitchen_status',
        'kitchen_ready_at',
        'prepared_by',
        'is_credit_sale',
        'notes',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change' => 'decimal:2',
        'is_credit_sale' => 'boolean',
        'voided_at' => 'datetime',
        'kitchen_ready_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->invoice_number)) {
                // Generate invoice number with retry logic to avoid duplicates
                $datePrefix = date('Ymd');
                $basePrefix = "INV-{$datePrefix}-";
                
                // Get the highest number used today
                $lastInvoice = static::where('invoice_number', 'like', "{$basePrefix}%")
                    ->orderBy('invoice_number', 'desc')
                    ->value('invoice_number');
                
                $nextNumber = 1;
                if ($lastInvoice) {
                    // Extract the number from the last invoice (e.g., "INV-20260101-0004" -> 4)
                    preg_match('/' . preg_quote($basePrefix, '/') . '(\d+)/', $lastInvoice, $matches);
                    if (isset($matches[1])) {
                        $nextNumber = (int)$matches[1] + 1;
                    }
                }
                
                // Find the next available invoice number
                $maxAttempts = 1000;
                $attempt = 0;
                
                do {
                    $invoiceNumber = $basePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                    
                    // Check if this invoice number already exists
                    $exists = static::where('invoice_number', $invoiceNumber)->exists();
                    
                    if (!$exists) {
                        $sale->invoice_number = $invoiceNumber;
                        break;
                    }
                    
                    $nextNumber++;
                    $attempt++;
                    
                    // Safety check to prevent infinite loop
                    if ($attempt >= $maxAttempts) {
                        // Fallback: use timestamp-based unique suffix
                        $sale->invoice_number = $basePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-' . time();
                        break;
                    }
                } while ($attempt < $maxAttempts);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function tableGuest(): BelongsTo
    {
        return $this->belongsTo(TableGuest::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    public function scopeCredit($query)
    {
        return $query->where('is_credit_sale', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeKitchenPending($query)
    {
        return $query->whereIn('kitchen_status', ['pending', 'preparing']);
    }

    public function scopeKitchenReady($query)
    {
        return $query->where('kitchen_status', 'ready');
    }

    public function markAsReady(int $userId): void
    {
        $this->update([
            'kitchen_status' => 'ready',
            'kitchen_ready_at' => now(),
            'prepared_by' => $userId,
        ]);
    }

    public function markAsPreparing(?int $userId = null): void
    {
        $this->update([
            'kitchen_status' => 'preparing',
            'prepared_by' => $userId ?? auth()->id(),
        ]);
    }

    public function markAsServed(): void
    {
        $this->update([
            'kitchen_status' => 'served',
        ]);
    }

    public function getProfit(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->quantity;
        });
    }

    public function void(int $userId, string $reason): void
    {
        $this->update([
            'status' => 'voided',
            'voided_by' => $userId,
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);

        // Return items to stock
        foreach ($this->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }

        // If credit sale, reduce customer balance
        if ($this->is_credit_sale && $this->customer) {
            $this->customer->reduceBalance($this->total);
        }
    }
}


