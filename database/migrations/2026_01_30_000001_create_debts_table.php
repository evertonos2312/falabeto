<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('client_id')->index();
            $table->text('creditor_name_encrypted');
            $table->integer('amount_cents');
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->text('notes_encrypted')->nullable();
            $table->enum('created_via', ['web', 'whatsapp'])->default('web');
            $table->ulid('source_message_log_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'due_date']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
