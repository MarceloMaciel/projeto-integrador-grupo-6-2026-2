<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Primary products directly belonging to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('order')->orderBy('id');
    }

    /**
     * All products associated through the pivot table (many-to-many).
     */
    public function allProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps()
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
