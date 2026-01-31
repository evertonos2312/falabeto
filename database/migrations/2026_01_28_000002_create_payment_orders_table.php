<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('client_id')->index();
            $table->ulid('plan_id')->index();
            $table->unsignedInteger('amount_cents');
            $table->string('status');
            $table->string('provider')->default('stripe_mock');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
