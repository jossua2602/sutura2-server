<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'plan_name',
        'price',
        'max_staff',
        'max_branches',
        'perks',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'perks' => 'array',
        ];
    }
}
