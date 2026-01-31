<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('client_id')->index();
            $table->enum('type', ['expense', 'income']);
            $table->integer('amount_cents');
            $table->dateTime('occurred_at');
            $table->string('category')->nullable();
            $table->text('description_encrypted');
            $table->text('notes_encrypted')->nullable();
            $table->enum('created_via', ['web', 'whatsapp'])->default('web');
            $table->ulid('source_message_log_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'occurred_at']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
