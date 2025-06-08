<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3); // EUR, USD, etc.
            $table->string('to_currency', 3); // RWF
            $table->decimal('rate', 10, 4); // 1 EUR = 1000.0000 RWF
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_updated_at');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable(); // Admin notes about the rate change
            $table->timestamps();

            // Indexes
            $table->index(['from_currency', 'to_currency']);
            $table->index(['is_active']);
            $table->unique(['from_currency', 'to_currency']); // Only one active rate per currency pair
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_conversions');
    }
};