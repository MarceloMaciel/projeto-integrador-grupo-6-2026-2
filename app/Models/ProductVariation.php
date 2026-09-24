<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'code',
        'price',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * The product this variation belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Formatted price attribute (e.g. "R$ 50,00").
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->price, 2, ',', '.');
    }

    /**
     * Scope for active variations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered variations.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
