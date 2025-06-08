<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 10)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Flight Details
            $table->string('airline_code', 3);
            $table->string('airline_name', 100);
            $table->enum('trip_type', ['oneway', 'round']);
            $table->enum('flight_class', ['ECONOMY', 'PREMIUM_ECONOMY', 'BUSINESS', 'FIRST']);
            
            // Route Information
            $table->string('origin_code', 3);
            $table->string('origin_city', 100);
            $table->string('destination_code', 3);
            $table->string('destination_city', 100);
            
            // Passenger Info
            $table->integer('total_passengers');
            $table->string('lead_passenger_name');
            $table->string('lead_passenger_email');
            $table->string('lead_passenger_phone', 20);
            
            // Pricing
            $table->decimal('base_price_eur', 10, 2);
            $table->decimal('taxes_fees_eur', 10, 2)->default(0);
            $table->decimal('total_price_eur', 10, 2);
            $table->string('currency_used', 3);
            $table->decimal('total_price_local', 15, 2);
            $table->decimal('exchange_rate', 10, 6);
            
            // Miles
            $table->integer('miles_earned');
            
            // Booking Status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            
            // Important Dates
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->timestamp('booking_date')->useCurrent();
            $table->timestamp('payment_date')->nullable();
            
            // Additional Info
            $table->text('special_requests')->nullable();
            $table->string('booking_source', 50)->default('website');
            
            $table->timestamps();
            
            // Indexes
            $table->index('booking_reference');
            $table->index('user_id');
            $table->index('departure_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_bookings');
    }
};