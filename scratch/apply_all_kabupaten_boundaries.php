<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UptLocation;
use Illuminate\Support\Facades\DB;

$jsonFile = __DIR__ . '/all_kabupaten_upt_village_boundaries.json';
if (!file_exists($jsonFile)) {
    die("File $jsonFile not found.\n");
}

$data = json_decode(file_get_contents($jsonFile), true);
$updatedCount = 0;
$skippedBatola = 0;

echo "Starting database update for all Kabupaten UPT boundaries...\n";

foreach ($data as $uptId => $item) {
    $upt = UptLocation::find($uptId);
    if (!$upt) {
        echo "UPT ID $uptId not found in DB!\n";
        continue;
    }

    // Protection: Strictly do not touch Barito Kuala (regency_id = 6 or ID 52..71)
    if ($upt->regency_id == 6 || ($upt->id >= 52 && $upt->id <= 71)) {
        $skippedBatola++;
        continue;
    }

    $upt->polygon_geojson = $item['geometry'];
    if (isset($item['centroid'][0]) && isset($item['centroid'][1])) {
        $upt->longitude = $item['centroid'][0];
        $upt->latitude = $item['centroid'][1];
    }

    $upt->save();
    $updatedCount++;
    echo sprintf(
        "Updated UPT #%03d (ID: %3d) '%s' => Desa '%s' (%s) [Centroid: %.5f, %.5f]\n",
        $upt->upt_number,
        $upt->id,
        $upt->upt_name,
        $item['matched_village'],
        $item['kode_kemendagri'],
        $upt->latitude,
        $upt->longitude
    );
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "DATABASE UPDATE COMPLETE!\n";
echo "Total UPTs updated: $updatedCount\n";
echo "Batola UPTs skipped/preserved: $skippedBatola\n";
echo str_repeat('=', 80) . "\n";

