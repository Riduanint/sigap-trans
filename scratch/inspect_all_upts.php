<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Regency;
use App\Models\UptLocation;

$regencies = Regency::with(['uptLocations' => function($q) {
    $q->orderBy('id');
}])->orderBy('id')->get();

foreach ($regencies as $reg) {
    echo "========================================================\n";
    echo "Regency [{$reg->id}] {$reg->name} (Total: " . count($reg->uptLocations) . " UPTs)\n";
    echo "========================================================\n";
    foreach ($reg->uptLocations as $upt) {
        $hasPoly = !empty($upt->polygon_geojson) ? (is_array($upt->polygon_geojson) ? $upt->polygon_geojson['type'] : 'JSON') : 'NO_POLY';
        echo sprintf("UPT #%03d (ID: %3d) | %-28s | Desa: %-25s | Coord: %8.4f, %8.4f | Poly: %s\n",
            $upt->upt_number,
            $upt->id,
            substr($upt->upt_name, 0, 28),
            substr($upt->current_village_name ?? '-', 0, 25),
            $upt->latitude ?? 0,
            $upt->longitude ?? 0,
            $hasPoly
        );
    }
    echo "\n";
}

