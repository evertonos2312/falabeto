<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('group', 32)->index();
            $table->string('key', 64)->unique();
            $table->enum('type', ['string', 'int', 'bool', 'json', 'file']);
            $table->longText('value_text')->nullable();
            $table->integer('value_int')->nullable();
            $table->boolean('value_bool')->nullable();
            $table->ulid('updated_by_admin_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
