<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('national_id', 20)->unique();
            $table->string('full_name', 150);
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('amphoe', 100)->nullable();
            $table->string('tambon', 100)->nullable();
            $table->decimal('assistance_amount', 12, 2)->default(0);
            $table->boolean('assistance_paid')->default(false);
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
