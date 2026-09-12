<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_registration_id')->nullable()->constrained('shop_registrations')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('branch_name');
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_branches');
    }
};
