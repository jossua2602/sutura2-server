<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_subscriptions', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('start_date');
            $table->boolean('payment_grace_enabled')->default(false)->after('status');
            $table->timestamp('payment_grace_until')->nullable()->after('payment_grace_enabled');
        });

        DB::statement('UPDATE shop_subscriptions SET end_date = DATE_ADD(start_date, INTERVAL 1 MONTH) WHERE end_date IS NULL');
    }

    public function down(): void
    {
        Schema::table('shop_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'payment_grace_enabled', 'payment_grace_until']);
        });
    }
};
