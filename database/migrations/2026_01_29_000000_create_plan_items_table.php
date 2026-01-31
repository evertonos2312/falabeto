<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('plan_id')->index();
            $table->string('item_code');
            $table->enum('item_type', ['int', 'bool', 'string']);
            $table->integer('value_int')->nullable();
            $table->boolean('value_bool')->nullable();
            $table->string('value_string')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_items');
    }
};
