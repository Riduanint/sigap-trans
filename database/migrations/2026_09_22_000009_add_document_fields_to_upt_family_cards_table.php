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
        Schema::table('upt_family_cards', function (Blueprint $table) {
            $table->string('document_path', 255)->nullable()->after('notes')->comment('Path berkas scan KK / dokumen pendukung di public storage');
            $table->string('document_name', 255)->nullable()->after('document_path')->comment('Nama asli berkas yang diunggah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upt_family_cards', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'document_name']);
        });
    }
};
