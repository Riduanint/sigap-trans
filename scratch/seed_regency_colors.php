<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$colors = [
    1 => '#8b5cf6', // Tapin
    2 => '#06b6d4', // Hulu Sungai Utara
    3 => '#3b82f6', // Balangan
    4 => '#10b981', // Tabalong
    5 => '#f59e0b', // Tanah Laut
    6 => '#ec4899', // Barito Kuala
    7 => '#6366f1', // Kota Baru
    8 => '#14b8a6', // Tanah Bumbu
    9 => '#f97316', // Banjar
];

foreach ($colors as $id => $color) {
    \App\Models\Regency::where('id', $id)->update([
        'is_visible' => true,
        'map_color' => $color,
    ]);
}

echo "Colors and visibility successfully seeded for all regencies!\n";
