<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UptLocation;
use Illuminate\Support\Facades\DB;

$upt = UptLocation::find(128);
if (!$upt) {
    echo "UPT 128 tidak ditemukan!\n";
    exit(1);
}

echo "Memperbaiki koordinat UPT {$upt->id} ({$upt->upt_name})...\n";
echo "Sebelum: Lat = {$upt->latitude}, Lng = {$upt->longitude}\n";

$lat = -2.399049;
$lng = 115.693862;
$delta = 0.008;

$polygon = [
    'type' => 'Polygon',
    'coordinates' => [[
        [$lng - $delta, $lat - $delta],
        [$lng + $delta, $lat - $delta],
        [$lng + $delta, $lat + $delta],
        [$lng - $delta, $lat + $delta],
        [$lng - $delta, $lat - $delta],
    ]]
];

$upt->update([
    'latitude' => $lat,
    'longitude' => $lng,
    'polygon_geojson' => $polygon,
]);

try {
    DB::statement("
        UPDATE upt_locations 
        SET coordinate_point = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
            polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(polygon_geojson::text)), 4326)
        WHERE id = {$upt->id}
    ");
    echo "PostGIS coordinate_point & polygon_area updated successfully!\n";
} catch (\Throwable $e) {
    echo "PostGIS update warning: " . $e->getMessage() . "\n";
}

$upt->refresh();
echo "Sesudah: Lat = {$upt->latitude}, Lng = {$upt->longitude}\n";
echo "Polygon coordinates: " . json_encode($upt->polygon_geojson['coordinates']) . "\n";
