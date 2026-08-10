<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->foreignId('disaster_type_id')->constrained('disaster_types')->cascadeOnDelete();
            $table->date('report_month'); // used to build the trend chart, e.g. first day of month
            $table->decimal('damaged_area_rai', 12, 2)->default(0);
            $table->unsignedInteger('farmers_affected')->default(0);
            $table->enum('status', ['pending', 'assessing', 'assisted'])->default('pending');
            $table->timestamps();

            $table->index(['province_id', 'report_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_reports');
    }
};
