<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->decimal('vendor_amount', 15, 2)->nullable()->after('miles_earned');
            $table->decimal('platform_commission', 10, 2)->nullable()->after('vendor_amount');
            $table->decimal('commission_percentage', 5, 2)->default(10.00)->after('platform_commission');
            $table->string('split_payment_id')->nullable()->after('commission_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('flight_bookings', function (Blueprint $table) {
            $table->dropColumn(['vendor_amount', 'platform_commission', 'commission_percentage', 'split_payment_id']);
        });
    }
};