<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE upt_locations DROP CONSTRAINT IF EXISTS check_upt_number");
        DB::statement("ALTER TABLE upt_locations ADD CONSTRAINT check_upt_number CHECK (upt_number >= 1)");

        // Sinkronisasi auto-increment ID sequence dengan data yang ada
        try {
            DB::statement("SELECT setval(pg_get_serial_sequence('upt_locations', 'id'), COALESCE(MAX(id), 1)) FROM upt_locations");
        } catch (\Throwable $e) {
            // Abaikan jika bukan PostgreSQL
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE upt_locations DROP CONSTRAINT IF EXISTS check_upt_number");
        DB::statement("ALTER TABLE upt_locations ADD CONSTRAINT check_upt_number CHECK (upt_number BETWEEN 1 AND 124)");
    }
};
