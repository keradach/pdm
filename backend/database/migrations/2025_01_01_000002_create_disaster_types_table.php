<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();   // drought, flood, pest, cold
            $table->string('name_th', 100);
            $table->string('icon', 50)->nullable();
            $table->unsignedTinyInteger('severity_percent')->default(0);
            $table->enum('severity_label', ['normal', 'watch', 'high', 'critical'])
                ->default('normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_types');
    }
};
