<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function getManager(): ?Role
    {
        return static::where('name', 'manager')->first();
    }

    public static function getSupervisor(): ?Role
    {
        return static::where('name', 'supervisor')->first();
    }

    public static function getCashier(): ?Role
    {
        return static::where('name', 'cashier')->first();
    }

    public static function getStorekeeper(): ?Role
    {
        return static::where('name', 'storekeeper')->first();
    }

    public static function getKitchen(): ?Role
    {
        return static::where('name', 'kitchen')->first();
    }
}


