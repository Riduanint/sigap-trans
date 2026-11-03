<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\UptLocation;
$ids = [3, 8, 9, 16, 27, 88, 97, 109, 112, 120, 121, 122];
foreach ($ids as $id) {
    $u = UptLocation::find($id);
    if ($u) {
        echo "ID: {$u->id} | Name: {$u->upt_name} | Desa: {$u->current_village_name} | Placement: {$u->placement_year} | Lat: {$u->latitude}, Lng: {$u->longitude}\n";
    }
}

