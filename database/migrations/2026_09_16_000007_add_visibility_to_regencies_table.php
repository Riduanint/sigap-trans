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
        Schema::table('regencies', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('capital_city');
            $table->string('map_color', 20)->default('#8b5cf6')->after('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regencies', function (Blueprint $table) {
            $table->dropColumn(['is_visible', 'map_color']);
        });
    }
};
