<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_registrations', function (Blueprint $table) {
            $table->string('dti_registration_path')->nullable()->after('proof_document_paths');
            $table->string('tin_id_path')->nullable()->after('dti_registration_path');
            $table->string('brgy_clearance_path')->nullable()->after('tin_id_path');
            $table->string('government_id_path')->nullable()->after('brgy_clearance_path');
            $table->string('government_id_type')->nullable()->after('government_id_path');
            $table->string('payment_method')->nullable()->after('government_id_type');
            $table->string('payment_receipt_path')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('shop_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'dti_registration_path',
                'tin_id_path',
                'brgy_clearance_path',
                'government_id_path',
                'government_id_type',
                'payment_method',
                'payment_receipt_path',
            ]);
        });
    }
};

