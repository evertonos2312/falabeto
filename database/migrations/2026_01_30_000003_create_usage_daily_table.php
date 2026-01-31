<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_daily', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('client_id')->index();
            $table->date('date');
            $table->integer('messages_in')->default(0);
            $table->timestamps();

            $table->unique(['client_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_daily');
    }
};
