<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'login_code',
        'is_admin',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getIsAdminAttribute($value): bool
    {
        if ($value) {
            return true;
        }

        return $this->role && $this->role->name === 'director';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    public function currentAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class)->current();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isManager(): bool
    {
        return $this->is_admin || ($this->role && $this->role->name === 'manager');
    }

    public function isSupervisor(): bool
    {
        return $this->role && $this->role->name === 'supervisor';
    }

    public function isCashier(): bool
    {
        return $this->role && $this->role->name === 'cashier';
    }

    public function isStorekeeper(): bool
    {
        return $this->role && $this->role->name === 'storekeeper';
    }

    public function isKitchen(): bool
    {
        return $this->role && $this->role->name === 'kitchen';
    }

    public function isAccountant(): bool
    {
        return $this->role && $this->role->name === 'accountant';
    }

    public function hasRole(string $role): bool
    {
        return $this->role && $this->role->name === $role;
    }

    public function canAccessPOS(): bool
    {
        return $this->isManager() || $this->isSupervisor() || $this->isCashier();
    }

    public function canManageStock(): bool
    {
        return $this->isManager() || $this->isStorekeeper() || $this->isAccountant();
    }

    public function canVoidSales(): bool
    {
        return $this->isManager() || $this->isSupervisor();
    }

    public function canManageCustomers(): bool
    {
        return $this->isManager() || $this->isAccountant();
    }

    public function canManageSuppliers(): bool
    {
        return $this->isManager() || $this->isAccountant();
    }

    public function canViewReports(): bool
    {
        return $this->isManager() || $this->isSupervisor() || $this->isAccountant();
    }

    public function canViewFinancialReports(): bool
    {
        return $this->is_admin || $this->isManager() || $this->isAccountant();
    }

    public function canAccessKitchen(): bool
    {
        return $this->isManager() || $this->isSupervisor() || $this->isKitchen() || $this->isCashier();
    }

    public function getOpenShift(): ?Shift
    {
        return $this->shifts()->open()->first();
    }

    public function hasOpenShift(): bool
    {
        return $this->getOpenShift() !== null;
    }
}
