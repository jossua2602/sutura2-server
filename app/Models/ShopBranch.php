<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopBranch extends Model
{
    protected $fillable = [
        'shop_registration_id',
        'shop_id',
        'branch_name',
        'address',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ShopRegistration::class, 'shop_registration_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
