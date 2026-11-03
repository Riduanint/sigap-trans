<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = new App\Http\Controllers\Api\UptLocationApiController();
$req = Illuminate\Http\Request::create('/api/upt-locations', 'GET');
$res = $ctrl->index($req);
$data = json_decode($res->getContent(), true);

echo "Total features returned: " . count($data['features']) . "\n";
echo "Meta total features: " . ($data['meta']['total_features'] ?? '-') . "\n";

$found = false;
foreach ($data['features'] as $f) {
    if ($f['id'] == 128 || $f['properties']['upt_name'] === 'Manarap') {
        echo "FOUND UPT 128 / Manarap!\n";
        echo "Coordinates: " . json_encode($f['geometry']['coordinates']) . "\n";
        echo "Properties: " . json_encode($f['properties']) . "\n";
        $found = true;
    }
}
if (!$found) {
    echo "NOT FOUND in /api/upt-locations!\n";
}
