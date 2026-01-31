<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('trial_enabled')->default(true)->after('is_active');
            $table->integer('trial_days')->default(30)->after('trial_enabled');
            $table->integer('sort_order')->default(0)->after('trial_days');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['trial_enabled', 'trial_days', 'sort_order']);
        });
    }
};
