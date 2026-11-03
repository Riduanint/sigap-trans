<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$balanganUpts = App\Models\UptLocation::where('regency_id', 3)->get();
foreach ($balanganUpts as $u) {
    echo "ID: {$u->id} | No: {$u->upt_number} | Nama: {$u->upt_name} | Desa: {$u->current_village_name} | Lat: {$u->latitude} | Lng: {$u->longitude}\n";
}
