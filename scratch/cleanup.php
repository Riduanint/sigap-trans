<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UptLocation;
use App\Models\UptFamilyCard;
use App\Models\UptChangeRequest;

UptChangeRequest::where('id', 6)->delete();
UptFamilyCard::where('head_of_family_name', 'Warga Percobaan Test')->delete();

$upt = UptLocation::find(1);
if ($upt) {
    $upt->placement_kk = 815;
    $upt->placement_population = 3260;
    $upt->is_verified = false;
    $upt->save();
}

echo "Database test records cleaned up successfully.\n";
