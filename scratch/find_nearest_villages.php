<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\UptLocation;

$cacheDir = __DIR__ . '/cache_boundaries';
$allFiles = glob("$cacheDir/*.sql");
$allVillages = [];

foreach ($allFiles as $f) {
    $content = file_get_contents($f);
    $pattern = "/\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)/";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $allVillages[] = [
            'kode' => $m[1],
            'nama' => $m[2],
            'lat' => (float)$m[3],
            'lng' => (float)$m[4],
        ];
    }
}

function dist($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos(min(max($dist, -1.0), 1.0));
    $dist = rad2deg($dist);
    return $dist * 60 * 1.1515 * 1.609344; // in km
}

$ids = [3, 8, 9, 16, 27, 88, 97, 109, 112, 120, 121, 122];
foreach ($ids as $id) {
    $u = UptLocation::find($id);
    if (!$u || !$u->latitude || !$u->longitude) continue;
    
    // Sort all villages by distance
    $sorted = $allVillages;
    usort($sorted, function($a, $b) use ($u) {
        $d1 = dist($u->latitude, $u->longitude, $a['lat'], $a['lng']);
        $d2 = dist($u->latitude, $u->longitude, $b['lat'], $b['lng']);
        return $d1 <=> $d2;
    });

    echo "UPT #{$u->upt_number} (ID {$u->id}) '{$u->upt_name}' [Desa: '{$u->current_village_name}'] at [{$u->latitude}, {$u->longitude}]:\n";
    for ($i = 0; $i < 3; $i++) {
        $v = $sorted[$i];
        $d = round(dist($u->latitude, $u->longitude, $v['lat'], $v['lng']), 2);
        echo "   -> {$v['nama']} ({$v['kode']}) - dist: {$d} km\n";
    }
    echo "\n";
}

