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
        Schema::create('upt_family_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upt_location_id')
                ->constrained('upt_locations')
                ->cascadeOnDelete();
            
            // Tahapan: 'placement' (Penempatan Awal) atau 'handover' (Serah Terima Pemda)
            $table->string('stage', 20)->default('placement');
            
            // Data Identitas KK
            $table->string('family_card_number', 30)->nullable()->comment('Nomor KK 16 Digit');
            $table->string('nik', 30)->nullable()->comment('NIK Kepala Keluarga 16 Digit');
            $table->string('head_of_family_name', 150)->comment('Nama Lengkap Kepala Keluarga');
            $table->unsignedSmallInteger('family_members_count')->default(1)->comment('Jumlah Jiwa dalam 1 KK');
            
            // Asal & Klasifikasi
            $table->string('transmigrant_type', 20)->default('TPA')->comment('TPA (Penduduk Asal) atau TPS (Penduduk Setempat)');
            $table->string('origin_province', 100)->nullable()->comment('Provinsi Asal');
            $table->string('origin_regency', 100)->nullable()->comment('Kabupaten Asal');
            
            // Tata Ruang & Legalitas Tanah
            $table->string('housing_block', 50)->nullable()->comment('Nomor / Blok Rumah Kapling');
            $table->string('land_certificate_status', 50)->default('Belum SHM')->comment('Status SHM / Legalitas Lahan');
            
            $table->text('notes')->nullable()->comment('Keterangan Tambahan');
            $table->timestamps();

            // Indeks untuk kecepatan filter & rekapitulasi
            $table->index(['upt_location_id', 'stage'], 'idx_family_cards_upt_stage');
            $table->index('transmigrant_type');
            $table->index('land_certificate_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upt_family_cards');
    }
};
