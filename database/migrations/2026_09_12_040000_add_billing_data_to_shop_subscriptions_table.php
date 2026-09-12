<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_subscriptions', function (Blueprint $table) {
            $table->string('billing_cycle')->default('monthly')->after('plan_id');
            $table->decimal('amount', 10, 2)->default(0)->after('billing_cycle');
        });

        DB::statement('UPDATE shop_subscriptions s INNER JOIN subscription_plans p ON p.id = s.plan_id SET s.amount = p.price WHERE s.amount = 0');
    }

    public function down(): void
    {
        Schema::table('shop_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'amount']);
        });
    }
};
