<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'items',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'delivery_address',
        'payment_method',
        'payment_status',
        'order_status',
        'notes',
        'status_history',
    ];

    protected $attributes = [
        'order_status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cash_on_delivery',
        'delivery_fee' => 0.00,
        'discount' => 0.00,
        'items' => [],
        'status_history' => [],
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'subtotal' => 'float',
            'delivery_fee' => 'float',
            'discount' => 'float',
            'total' => 'float',
            'status_history' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
