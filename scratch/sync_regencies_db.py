import json, os

with open('public/data/kalsel_regencies.geojson', 'r', encoding='utf-8') as f:
    data = json.load(f)

print(f"Loaded {len(data['features'])} regency features.")

# Let's create an update seeder / artisan script to update regencies table with boundary_polygon
php_script = """<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$geo = json_decode(file_get_contents('public/data/kalsel_regencies.geojson'), true);

foreach ($geo['features'] as $feature) {
    $id = $feature['id'];
    $geom = json_encode($feature['geometry']);
    
    // Update boundary_polygon column and PostGIS geometry
    DB::table('regencies')->where('id', $id)->update([
        'boundary_polygon' => $geom
    ]);
    
    try {
        DB::statement("
            UPDATE regencies 
            SET boundary_polygon = ST_Multi(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326))
            WHERE id = ?
        ", [$geom, $id]);
    } catch (\\Throwable $e) {
        // Fallback jika PostGIS geometry column
    }
    
    echo "Updated Regency $id: " . $feature['properties']['name'] . PHP_EOL;
}
"""

with open('scratch/sync_regencies.php', 'w', encoding='utf-8') as f:
    f.write(php_script)

print("Created scratch/sync_regencies.php")
