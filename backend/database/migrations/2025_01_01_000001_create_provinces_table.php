<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name_th', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('region', 50)->nullable();
            $table->enum('risk_level', ['normal', 'watch', 'high', 'critical'])
                ->default('normal');
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->unsignedInteger('amphoe_affected')->default(0);
            $table->unsignedInteger('tambon_affected')->default(0);
            $table->unsignedInteger('farmers_affected')->default(0);
            $table->decimal('damaged_area_rai', 12, 2)->default(0);
            $table->timestamp('data_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
