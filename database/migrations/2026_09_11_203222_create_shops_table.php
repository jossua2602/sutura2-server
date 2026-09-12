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
       Schema::create('shops', function (Blueprint $table) {
        $table->id();
        $table->foreignId('owner_id')->constrained('users');
        $table->string('shop_name');
        $table->text('address')->nullable();
        $table->string('verification_status')->default('pending'); // pending, verified, rejected
        $table->string('visibility')->default('hidden');            // public, hidden, featured
        $table->text('rejection_reason')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
