<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->dateTime('appointment_date');
            $table->time('duration')->default('00:30:00');
            $table->foreignId('office_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['scheduled', 'attended', 'cancelled', 'absent'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};