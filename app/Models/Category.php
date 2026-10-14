<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * Category names containing any of these words are treated as kitchen (food);
     * everything else in Bar n Kitchen is treated as bar.
     */
    public const KITCHEN_KEYWORDS = ['food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter', 'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'department',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParentCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubcategories($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function isParent(): bool
    {
        return $this->parent_id === null;
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }

    public function isKitchenCategory(): bool
    {
        foreach ([$this->name, $this->parent?->name] as $name) {
            if ($name && Str::contains(strtolower($name), self::KITCHEN_KEYWORDS)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 'kitchen', 'bar' or 'hotel' (hotel consumables).
     */
    public function salesArea(): string
    {
        if ($this->department === 'hotel') {
            return 'hotel';
        }

        return $this->isKitchenCategory() ? 'kitchen' : 'bar';
    }

    public static function kitchenCategoryIds(): array
    {
        return static::with('parent')->get()
            ->filter(fn (Category $category) => $category->isKitchenCategory())
            ->pluck('id')
            ->all();
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }
}


