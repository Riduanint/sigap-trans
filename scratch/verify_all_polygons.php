<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UptLocation;

$upts = UptLocation::orderBy('id')->get();
$boxCount = 0;
$realPolyCount = 0;
$batolaCount = 0;

foreach ($upts as $u) {
    $geom = $u->polygon_geojson;
    if (!$geom || !isset($geom['coordinates'])) {
        echo "UPT {$u->id} has NO POLYGON!\n";
        continue;
    }

    $numPoints = 0;
    if ($geom['type'] === 'Polygon') {
        $numPoints = count($geom['coordinates'][0]);
    } elseif ($geom['type'] === 'MultiPolygon') {
        foreach ($geom['coordinates'] as $poly) {
            $numPoints += count($poly[0]);
        }
    }

    if ($u->regency_id == 6) {
        $batolaCount++;
    }

    if ($numPoints <= 5) {
        $boxCount++;
        echo "WARNING: UPT {$u->id} ({$u->upt_name}) still has BOX polygon ({$numPoints} pts)\n";
    } else {
        $realPolyCount++;
    }
}

echo "\nVerification Summary:\n";
echo "Total UPTs checked: " . count($upts) . "\n";
echo "Real Organic Village Polygons: $realPolyCount / 124\n";
echo "Synthetic Box Polygons: $boxCount\n";
echo "Barito Kuala UPTs: $batolaCount\n";

