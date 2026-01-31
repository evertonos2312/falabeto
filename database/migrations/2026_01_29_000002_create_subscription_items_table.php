<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('subscription_id')->index();
            $table->enum('item_type', ['plan', 'feature']);
            $table->string('item_code');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->integer('unit_price_cents')->default(0);
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
