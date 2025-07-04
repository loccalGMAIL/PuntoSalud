<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('license_number')->unique();
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('schedule')->nullable(); // Horarios de atención
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};