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
        Schema::create('skp_villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skp_area_id')->constrained('skp_areas')->cascadeOnDelete();
            $table->foreignId('upt_location_id')->nullable()->constrained('upt_locations')->nullOnDelete();

            $table->string('district_name', 100)->comment('Nama Kecamatan');
            $table->string('village_name', 150)->comment('Nama Desa / Kelurahan');
            $table->decimal('area_km2', 10, 2)->default(0)->comment('Luas Wilayah (Km2)');
            $table->integer('population_male')->default(0)->comment('Penduduk Laki-laki');
            $table->integer('population_female')->default(0)->comment('Penduduk Perempuan');
            $table->integer('population_total')->default(0)->comment('Total Penduduk (L + P)');
            $table->integer('family_count')->default(0)->comment('Jumlah Kepala Keluarga (KK)');
            $table->string('notes', 255)->nullable()->comment('Keterangan / Kode SKP');
            $table->timestamps();

            $table->index('skp_area_id');
            $table->index('district_name');
            $table->index('village_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skp_villages');
    }
};
