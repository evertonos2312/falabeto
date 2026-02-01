<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->json('meta_json')->nullable()->after('redeemed_at');
            $table->index(['coupon_id', 'redeemed_at']);
            $table->index(['client_id', 'redeemed_at']);
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropUnique('coupon_redemptions_coupon_id_client_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->unique(['coupon_id', 'client_id']);
            $table->dropIndex(['coupon_id', 'redeemed_at']);
            $table->dropIndex(['client_id', 'redeemed_at']);
            $table->dropColumn('meta_json');
        });
    }
};
