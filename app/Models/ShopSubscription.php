<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSubscription extends Model
{
    protected $fillable = [
        'shop_id',
        'plan_id',
        'billing_cycle',
        'amount',
        'start_date',
        'end_date',
        'status',
        'payment_grace_enabled',
        'payment_grace_until',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'payment_grace_enabled' => 'boolean',
            'payment_grace_until' => 'datetime',
        ];
    }
}
