<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Regency;
use App\Models\UptLocation;

$visibleRegs = Regency::where('is_visible', true)->get();
echo 'Visible Regencies (' . $visibleRegs->count() . '): ' . $visibleRegs->pluck('name')->implode(', ') . PHP_EOL;

$visibleUpts = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))->count();
echo 'Visible UPTs count: ' . $visibleUpts . PHP_EOL;

$hiddenUpts = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', false))->count();
echo 'Hidden UPTs count: ' . $hiddenUpts . PHP_EOL;

foreach ($visibleRegs as $r) {
    $count = UptLocation::where('regency_id', $r->id)->count();
    echo "- {$r->name}: {$count} UPT\n";
}
