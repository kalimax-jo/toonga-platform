<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('flight_bookings')->onDelete('cascade');
            $table->enum('passenger_type', ['adult', 'child', 'infant'])->default('adult');
            
            // Personal Information
            $table->string('title', 10)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->string('nationality', 100);
            
            // Travel Documents
            $table->string('passport_number', 50)->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('passport_country', 100)->nullable();
            
            // Contact (for lead passenger or if different)
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            
            // Special Requirements
            $table->string('meal_preference', 50)->nullable();
            $table->text('special_assistance')->nullable();
            $table->string('frequent_flyer_number', 50)->nullable();
            
            // Seat Assignment (if available)
            $table->string('seat_number', 10)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_passengers');
    }
};