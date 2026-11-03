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
        Schema::table('upt_change_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('upt_location_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upt_change_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('upt_location_id')->nullable(false)->change();
        });
    }
};
