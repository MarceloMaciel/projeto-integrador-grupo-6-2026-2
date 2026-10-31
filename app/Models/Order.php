<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'status',
        'total',
        'payment_method',
        'amount_received',
        'change',
        'consumer_cpf',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'change' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Items sold in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Payment method name (e.g. "Dinheiro").
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return config('fiscal.payment_methods.'.$this->payment_method, $this->payment_method);
    }
}
