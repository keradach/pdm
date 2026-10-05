<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache of every third-party API response. The backend proxy fetches the
 * upstream APIs, persists the raw payload here, and serves the frontend from
 * this table (per TTL). Works on both Oracle (CLOB) and SQLite (TEXT).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_caches', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 255)->unique();
            $table->text('payload')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_caches');
    }
};