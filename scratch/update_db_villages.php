<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UptLocation;
use Illuminate\Support\Facades\DB;

$jsonFile = 'scratch/batola_upt_village_boundaries.json';
if (!file_exists($jsonFile)) {
    die("File $jsonFile not found.\n");
}

$data = json_decode(file_get_contents($jsonFile), true);
$updatedCount = 0;

foreach ($data as $uptId => $item) {
    $upt = UptLocation::find($uptId);
    if (!$upt) {
        echo "UPT ID $uptId not found in DB\n";
        continue;
    }

    $upt->polygon_geojson = $item['geometry'];
    // Update centroid coordinate if available and valid
    if (isset($item['centroid'][0]) && isset($item['centroid'][1])) {
        $upt->longitude = $item['centroid'][0];
        $upt->latitude = $item['centroid'][1];
    }
    $upt->save();
    $updatedCount++;
    echo "Updated UPT {$upt->id}: {$upt->upt_name} with Desa '{$item['village_name']}' ({$item['kode_kemendagri']})\n";
}

echo "\nSuccessfully updated $updatedCount UPTs in database with real Kemendagri village polygons!\n";
