<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'series', 'issued_at', 'access_key', 'snapshot', 'xml'];

    protected $hidden = ['xml', 'snapshot'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'snapshot' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
