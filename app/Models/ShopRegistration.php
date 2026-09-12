<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRegistration extends Model
{
    protected $fillable = [
        'shop_name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact_number',
        'address',
        'subscription_plan',
        'billing_cycle',
        'subscription_price',
        'landmark_image_path',
        'proof_document_paths',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'proof_document_paths' => 'array',
            'subscription_price' => 'decimal:2',
        ];
    }
}
