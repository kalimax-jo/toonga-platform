<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->unsignedTinyInteger('commission')->default(20); // Toonga’s % cut
        $table->boolean('is_approved')->default(false);         // Sales reviewer flag
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('commission');
        $table->dropColumn('is_approved');
    });
}
};
