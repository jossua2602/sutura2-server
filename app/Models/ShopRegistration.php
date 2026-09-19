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
        'birthday',
        'email',
        'contact_number',
        'address',
        'subscription_plan',
        'billing_cycle',
        'subscription_price',
        'landmark_image_path',
        'proof_document_paths',
        'dti_registration_path',
        'tin_id_path',
        'brgy_clearance_path',
        'government_id_path',
        'government_id_type',
        'payment_method',
        'payment_receipt_path',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'proof_document_paths' => 'array',
            'subscription_price'   => 'decimal:2',
        ];
    }
}
