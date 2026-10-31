<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    protected $fillable = [
        'product_variation_id',
        'code',
        'description',
        'ncm',
        'cfop',
        'csosn',
        'cest',
        'unit',
        'quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Copies the menu data as it is at the moment of the sale.
     */
    public static function fromVariation(ProductVariation $variation, int $quantity): self
    {
        $product = $variation->product;

        return new self([
            'product_variation_id' => $variation->id,
            // cProd é obrigatório no XML, mas bebidas e adicionais não têm código no cardápio.
            'code' => $variation->code ?: ($product->code ?: (string) $product->id),
            'description' => self::describe($product->name, $variation->name),
            'ncm' => $product->ncm,
            'cfop' => $product->cfop,
            'csosn' => $product->csosn,
            'cest' => $product->cest,
            'unit' => $product->unit,
            'quantity' => $quantity,
            'unit_price' => $variation->price,
            // Mesmo cálculo do vProd no NfceXmlBuilder, para o total bater com o XML.
            'total' => round($quantity * (float) $variation->price, 2),
        ]);
    }

    /**
     * The order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The menu variation this item was copied from.
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Item name, without a variation that adds nothing ("Cerveja Lata 350ml - Lata 350ml").
     */
    public static function describe(string $productName, ?string $variationName): string
    {
        $isRedundant = blank($variationName)
            || $variationName === 'Padrão'
            || Str::contains($productName, $variationName, ignoreCase: true);

        $description = $isRedundant
            ? $productName
            : "{$productName} - {$variationName}";

        // Tamanho máximo do xProd no layout da NFC-e.
        return Str::limit($description, 120, '');
    }
}
