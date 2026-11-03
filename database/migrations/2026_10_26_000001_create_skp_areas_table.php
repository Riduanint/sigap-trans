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
        Schema::create('skp_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('regency_id');
            $table->foreign('regency_id')->references('id')->on('regencies')->restrictOnDelete();

            $table->string('kawasan_name', 150)->comment('Nama Kawasan Transmigrasi');
            $table->string('skp_name', 100)->comment('Nama SKP misal: SKP-B, SKP P');
            $table->string('unit_code', 50)->nullable()->comment('Kode Unit SKP');
            $table->decimal('luas_ha', 12, 2)->nullable()->comment('Luas Area dalam Hektar');
            $table->string('color', 20)->default('#163A63')->comment('Warna Poligon Peta');
            $table->text('keterangan')->nullable();
            $table->string('sumber_data', 150)->nullable()->default('DPMD & BIG');
            $table->decimal('centroid_lat', 10, 8)->nullable();
            $table->decimal('centroid_lng', 11, 8)->nullable();
            $table->jsonb('polygon_geojson')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['regency_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skp_areas');
    }
};
