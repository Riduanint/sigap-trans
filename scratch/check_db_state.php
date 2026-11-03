<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LAST 5 UPT LOCATIONS ===\n";
$upts = App\Models\UptLocation::with('regency')->latest('id')->take(5)->get();
foreach ($upts as $u) {
    echo "ID: {$u->id} | No: {$u->upt_number} | Nama: {$u->upt_name} | Kab: " . ($u->regency->name ?? 'NULL') . " (is_visible: " . var_export($u->regency?->is_visible, true) . ") | Lat: {$u->latitude} | Lng: {$u->longitude} | Verified: {$u->is_verified}\n";
    echo "  Polygon GeoJSON: " . ($u->polygon_geojson ? "YES" : "NULL") . "\n";
    echo "  Point DB: " . (is_null($u->coordinate_point) ? "NULL" : "SET") . "\n";
}

echo "\n=== LAST 5 CHANGE REQUESTS ===\n";
$reqs = App\Models\UptChangeRequest::latest('id')->take(5)->get();
foreach ($reqs as $r) {
    echo "ID: {$r->id} | Type: {$r->request_type} | Status: {$r->status} | UPT ID: " . var_export($r->upt_location_id, true) . "\n";
    echo "  Payload: " . json_encode($r->proposed_payload) . "\n";
}
