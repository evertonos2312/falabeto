<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('client_id')->nullable()->index();
            $table->string('phone_e164')->nullable();
            $table->enum('channel', ['whatsapp']);
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('body')->nullable();
            $table->string('body_snippet', 64)->nullable();
            $table->char('body_hash', 64);
            $table->boolean('llm_used')->default(false);
            $table->string('llm_model', 64)->nullable();
            $table->integer('llm_cost_cents')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->index(['phone_e164', 'created_at']);
            $table->index('body_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
