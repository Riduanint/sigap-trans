<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UptLocation;
use App\Models\Regency;

$regencies = Regency::all();
foreach ($regencies as $reg) {
    $firstUpt = UptLocation::where('regency_id', $reg->id)->first();
    if ($firstUpt) {
        $type = $firstUpt->polygon_geojson['type'] ?? 'N/A';
        $pts = 0;
        if ($type === 'Polygon') {
            $pts = count($firstUpt->polygon_geojson['coordinates'][0]);
        } elseif ($type === 'MultiPolygon') {
            $pts = count($firstUpt->polygon_geojson['coordinates'][0][0]);
        }
        echo sprintf(
            "Regency [%2d] %-20s | Sample UPT: %-25s | Centroid: [%.4f, %.4f] | Type: %-12s | Points: %d\n",
            $reg->id,
            $reg->name,
            $firstUpt->upt_name,
            $firstUpt->latitude,
            $firstUpt->longitude,
            $type,
            $pts
        );
    }
}

