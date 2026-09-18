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

        Schema::create('regencies', function (Blueprint $table) use ($hasPostGis) {
            $table->increments('id'); // SERIAL PRIMARY KEY (INT AUTO)
            $table->string('code_roman', 5)->unique();
            $table->string('name', 100);
            $table->string('capital_city', 100);
            
            if ($hasPostGis) {
                $table->geometry('boundary_polygon', subtype: 'multipolygon', srid: 4326)->nullable();
            } else {
                $table->jsonb('boundary_polygon')->nullable()->comment('Fallback GeoJSON MultiPolygon saat PostGIS belum aktif');
            }

            // Kolom koordinat tambahan untuk centering peta dashboard
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regencies');
    }
};
