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
        // Bersihkan data akun dengan role mitra_bpn jika ada
        DB::table('users')->where('role', 'mitra_bpn')->delete();

        // Perbarui check constraint PostgreSQL pada tabel users
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS check_user_role");
        DB::statement("ALTER TABLE users ADD CONSTRAINT check_user_role CHECK (role IN ('super_admin', 'operator_kabupaten', 'eksekutif'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS check_user_role");
        DB::statement("ALTER TABLE users ADD CONSTRAINT check_user_role CHECK (role IN ('super_admin', 'operator_kabupaten', 'eksekutif', 'mitra_bpn'))");
    }
};
