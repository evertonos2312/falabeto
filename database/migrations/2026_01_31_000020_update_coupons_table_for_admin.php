<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('name')->nullable()->after('code');
            $table->enum('type', ['percent', 'fixed'])->nullable()->after('name');
            $table->integer('value_int')->nullable()->after('type');
            $table->enum('duration', ['once', 'repeating', 'forever'])->default('once')->after('value_int');
            $table->integer('duration_months')->nullable()->after('duration');
            $table->json('allowed_plan_codes')->nullable()->after('is_active');
            $table->boolean('first_purchase_only')->default(false)->after('allowed_plan_codes');
            $table->ulid('created_by_admin_id')->nullable()->after('first_purchase_only')->index();
        });

        DB::statement("UPDATE coupons SET type = discount_type WHERE type IS NULL");
        DB::statement("UPDATE coupons SET type = 'fixed' WHERE type = 'amount'");
        DB::statement("UPDATE coupons SET value_int = discount_value_int WHERE value_int IS NULL");
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'type',
                'value_int',
                'duration',
                'duration_months',
                'allowed_plan_codes',
                'first_purchase_only',
                'created_by_admin_id',
            ]);
        });
    }
};
