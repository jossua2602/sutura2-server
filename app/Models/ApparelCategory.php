<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApparelCategory extends Model
{
    protected $fillable = [
        'shop_registration_id',
        'shop_id',
        'name',
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
