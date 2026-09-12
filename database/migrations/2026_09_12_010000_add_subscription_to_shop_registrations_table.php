<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_registrations', function (Blueprint $table) {
            $table->string('subscription_plan')->after('address');
            $table->string('billing_cycle')->after('subscription_plan');
            $table->decimal('subscription_price', 10, 2)->after('billing_cycle');
        });
    }

    public function down(): void
    {
        Schema::table('shop_registrations', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'billing_cycle', 'subscription_price']);
        });
    }
};
