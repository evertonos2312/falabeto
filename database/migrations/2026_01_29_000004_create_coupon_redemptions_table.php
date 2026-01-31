<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('coupon_id')->index();
            $table->ulid('client_id')->index();
            $table->ulid('subscription_id')->nullable()->index();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->unique(['coupon_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
