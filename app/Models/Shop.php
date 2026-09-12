<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'owner_id',
        'shop_name',
        'address',
        'verification_status',
        'account_status',
        'visibility',
        'rejection_reason',
    ];
}
