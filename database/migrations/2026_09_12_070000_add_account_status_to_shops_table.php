<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('account_status')->default('active')->after('verification_status');
            $table->index(['account_status', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['account_status', 'visibility']);
            $table->dropColumn('account_status');
        });
    }
};
