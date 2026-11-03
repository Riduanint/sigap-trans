<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$regs = App\Models\Regency::withCount('uptLocations')->orderBy('id')->get();
foreach ($regs as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | is_visible: " . var_export($r->is_visible, true) . " | Lat: {$r->latitude} | Lng: {$r->longitude}\n";
}
