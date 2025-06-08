<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miles', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('miles', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->after('user_id')->constrained('flight_bookings')->onDelete('set null');
            }
            if (!Schema::hasColumn('miles', 'type')) {
                $table->enum('type', ['earned', 'redeemed', 'expired'])->default('earned')->after('amount');
            }
            if (!Schema::hasColumn('miles', 'description')) {
                $table->string('description')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('miles', function (Blueprint $table) {
            if (Schema::hasColumn('miles', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
            if (Schema::hasColumn('miles', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('miles', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};