<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('miles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // miles earned
            $table->decimal('payment_value', 10, 2); // value paid (RWF)
            $table->string('source')->nullable(); // e.g. "flight"
            $table->string('reference')->nullable(); // tx_ref
            $table->timestamp('earned_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miles');
    }
};
