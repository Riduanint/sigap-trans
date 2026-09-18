<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Toggle Balangan (ID 3) off
\App\Models\Regency::where('id', 3)->update(['is_visible' => false]);

$client = new \GuzzleHttp\Client(['base_uri' => 'http://127.0.0.1:8000']);
$resBoundaries = json_decode($client->get('/api/regencies/boundaries')->getBody(), true);
$resUpts = json_decode($client->get('/api/upt-locations')->getBody(), true);

echo "Boundaries count when Balangan (ID 3) hidden: " . count($resBoundaries['features']) . PHP_EOL;
echo "UPT count when Balangan (ID 3) hidden: " . $resUpts['meta']['total_features'] . PHP_EOL;

// Reset Balangan back to true
\App\Models\Regency::where('id', 3)->update(['is_visible' => true]);

$resBoundariesReset = json_decode($client->get('/api/regencies/boundaries')->getBody(), true);
$resUptsReset = json_decode($client->get('/api/upt-locations')->getBody(), true);

echo "Boundaries count after reset: " . count($resBoundariesReset['features']) . PHP_EOL;
echo "UPT count after reset: " . $resUptsReset['meta']['total_features'] . PHP_EOL;
