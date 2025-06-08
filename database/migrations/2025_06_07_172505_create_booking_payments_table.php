<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('flight_bookings')->onDelete('cascade');
            
            // Payment Details
            $table->string('payment_method', 50);
            $table->string('payment_provider', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('external_reference', 100)->nullable();
            
            // Amount
            $table->decimal('amount_eur', 10, 2);
            $table->decimal('amount_local', 15, 2);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 10, 6);
            
            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->dateTime('payment_date')->nullable();
            
            // Additional Info
            $table->text('failure_reason')->nullable();
            $table->json('provider_response')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('booking_id');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};