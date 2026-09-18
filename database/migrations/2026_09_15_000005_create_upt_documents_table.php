<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upt_documents', function (Blueprint $table) {
            $table->id(); // BIGSERIAL PRIMARY KEY
            $table->foreignId('upt_location_id')->constrained('upt_locations')->cascadeOnDelete();
            $table->string('document_type', 50)->comment('BAST, SK_GUBERNUR, SK_MENTERI, BUKU_TANAH');
            $table->string('document_number', 100)->nullable()->comment('Nomor register resmi dokumen, contoh: BAST/503/1977');
            $table->string('file_name', 255)->comment('Nama asli berkas PDF');
            $table->string('file_path', 255)->comment('Path berkas di vault storage server');
            $table->integer('file_size_kb')->comment('Ukuran berkas dalam kilobyte (KB)');
            $table->foreignUuid('uploaded_by')->constrained('users')->restrictOnDelete()->comment('Pengguna yang mengunggah');
            $table->timestamps();

            // Indeks pencarian repositori
            $table->index('upt_location_id');
            $table->index('document_type');
            $table->index('uploaded_by');
        });

        try {
            DB::statement("ALTER TABLE upt_documents ADD CONSTRAINT check_document_type CHECK (document_type IN ('BAST', 'SK_GUBERNUR', 'SK_MENTERI', 'BUKU_TANAH'))");
        } catch (\Throwable $e) {
            // Abaikan jika tidak didukung
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upt_documents');
    }
};
