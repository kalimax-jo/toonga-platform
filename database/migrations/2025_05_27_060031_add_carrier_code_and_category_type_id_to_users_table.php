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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'carrier_code')) {
                $table->string('carrier_code')->nullable()->after('is_approved');
            }

            if (!Schema::hasColumn('users', 'category_type_id')) {
                $table->unsignedBigInteger('category_type_id')->nullable()->after('carrier_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'carrier_code')) {
                $table->dropColumn('carrier_code');
            }

            if (Schema::hasColumn('users', 'category_type_id')) {
                $table->dropColumn('category_type_id');
            }
        });
    }
};
