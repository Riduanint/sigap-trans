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
            $table->foreign('regency_id')
                ->references('id')
                ->on('regencies')
                ->nullOnDelete();

            $table->string('phone', 25)->nullable()->after('regency_id');
            $table->string('position')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['regency_id']);
            $table->dropColumn(['phone', 'position']);
        });
    }
};
