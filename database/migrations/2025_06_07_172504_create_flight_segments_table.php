<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('flight_bookings')->onDelete('cascade');
            $table->enum('segment_type', ['outbound', 'return']);
            $table->integer('segment_order');
            
            // Flight Details
            $table->string('flight_number', 10);
            $table->string('aircraft_code', 10)->nullable();
            $table->string('operating_airline', 3)->nullable();
            
            // Route
            $table->string('departure_airport', 3);
            $table->string('departure_city', 100);
            $table->dateTime('departure_time');
            $table->string('departure_terminal', 10)->nullable();
            
            $table->string('arrival_airport', 3);
            $table->string('arrival_city', 100);
            $table->dateTime('arrival_time');
            $table->string('arrival_terminal', 10)->nullable();
            
            // Timing
            $table->integer('flight_duration'); // minutes
            $table->integer('layover_duration')->nullable(); // minutes
            
            // Seating
            $table->string('cabin_class', 20);
            $table->string('booking_class', 5);
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('booking_id');
            $table->index('departure_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_segments');
    }
};