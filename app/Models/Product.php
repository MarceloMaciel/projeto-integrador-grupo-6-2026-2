<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
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
     * Primary category of this product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * All categories this product is associated with.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }

    /**
     * Portions and price variations of this product.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->orderBy('order')->orderBy('id');
    }

    /**
     * Scope for active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered products.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Find product by cardápio code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Get lowest price among variations.
     */
    public function getMinPriceAttribute(): ?float
    {
        return $this->variations->min('price');
    }

    /**
     * Get highest price among variations.
     */
    public function getMaxPriceAttribute(): ?float
    {
        return $this->variations->max('price');
    }

    /**
     * Formatted price range string (e.g. "R$ 38,00 - R$ 50,00" or "R$ 50,00").
     */
    public function getFormattedPriceRangeAttribute(): string
    {
        $min = $this->min_price;
        $max = $this->max_price;

        if ($min === null) {
            return 'R$ 0,00';
        }

        if ($min === $max) {
            return 'R$ ' . number_format($min, 2, ',', '.');
        }

        return 'R$ ' . number_format($min, 2, ',', '.') . ' - R$ ' . number_format($max, 2, ',', '.');
    }
}
