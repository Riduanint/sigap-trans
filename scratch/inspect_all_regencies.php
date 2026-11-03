<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Support\Facades\Schema;

echo "Columns in upt_locations:\n";
$cols = Schema::getColumnListing('upt_locations');
echo implode(', ', $cols) . "\n\n";

echo "Regencies and UPT counts:\n";
$regencies = Regency::withCount('uptLocations')->orderBy('id')->get();
foreach ($regencies as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | Code: " . ($r->code ?? $r->kemendagri_code ?? 'null') . " | UPTs: {$r->upt_locations_count}\n";
}

echo "\nTotal UPTs in DB: " . UptLocation::count() . "\n";

