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
        $availPostgis = DB::select("SELECT 1 FROM pg_available_extensions WHERE name = 'postgis'");
        $hasPostGis = !empty($availPostgis);

        if ($hasPostGis) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        }

        Schema::create('upt_locations', function (Blueprint $table) use ($hasPostGis) {
            $table->id(); // BIGSERIAL PRIMARY KEY
            $table->unsignedInteger('regency_id');
            $table->foreign('regency_id')->references('id')->on('regencies')->restrictOnDelete();

            $table->integer('upt_number')->unique();
            $table->string('upt_name', 150);
            $table->string('current_village_name', 150);
            $table->string('business_pattern', 50)->comment('Pola: TPLK, TPLB, PIRSUS, HTI, P4HDR, dll');
            $table->string('placement_year', 20);
            $table->integer('placement_kk')->default(0);
            $table->integer('placement_population')->default(0);
            $table->string('handover_year', 20)->nullable();
            $table->integer('handover_kk')->default(0);
            $table->integer('handover_population')->default(0);
            
            $table->string('issue_status', 20)->default('clean');
            $table->text('issue_note')->nullable();
            $table->boolean('is_verified')->default(true);

            // Kolom geospasial native PostGIS
            if ($hasPostGis) {
                $table->geometry('coordinate_point', subtype: 'point', srid: 4326)->nullable();
                $table->geometry('polygon_area', subtype: 'multipolygon', srid: 4326)->nullable();
            }

            // Kolom koordinat standar untuk fleksibilitas API/Leaflet.js
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->jsonb('polygon_geojson')->nullable();
            $table->string('shm_status')->nullable()->comment('Status legalitas SHM untuk pendukung');

            $table->timestamps();

            // Indeks atribut & komposit sesuai dokumen PDF
            $table->index(['regency_id', 'issue_status'], 'idx_upt_regency_status');
            $table->index('business_pattern');
            $table->index('is_verified');
        });

        // Check constraint sesuai DDL dokumen PDF
        DB::statement("ALTER TABLE upt_locations ADD CONSTRAINT check_upt_number CHECK (upt_number BETWEEN 1 AND 124)");
        DB::statement("ALTER TABLE upt_locations ADD CONSTRAINT check_issue_status CHECK (issue_status IN ('clean', 'warning', 'critical'))");

        // Indeks spasial GiST jika PostGIS aktif
        if ($hasPostGis) {
            try {
                DB::statement('CREATE INDEX idx_upt_point_gist ON upt_locations USING GIST (coordinate_point)');
                DB::statement('CREATE INDEX idx_upt_polygon_gist ON upt_locations USING GIST (polygon_area)');
            } catch (\Throwable $e) {
                // Abaikan jika indeks spasial gagal
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upt_locations');
    }
};
