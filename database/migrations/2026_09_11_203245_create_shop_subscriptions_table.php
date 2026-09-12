<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_subscriptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shop_id')->constrained();
        $table->foreignId('plan_id')->constrained('subscription_plans');
        $table->date('start_date');
        $table->string('status')->default('active');  // active, expired, cancelled
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_subscriptions');
    }
};
