<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== REGENCIES (COUNT: " . App\Models\Regency::count() . ") ===\n";
foreach (App\Models\Regency::all() as $r) {
    echo "ID: {$r->id} | Roman: {$r->code_roman} | Name: {$r->name} | Capital: {$r->capital_city}\n";
}

echo "\n=== USERS (COUNT: " . App\Models\User::count() . ") ===\n";
foreach (App\Models\User::all() as $u) {
    echo "ID (UUID): {$u->id} | Name: {$u->name} | Role: {$u->role} | Regency: {$u->regency_id}\n";
}

echo "\n=== TABLE COLUMNS CHECK ===\n";
$tables = ['regencies', 'users', 'upt_locations', 'upt_change_requests', 'upt_documents', 'audit_logs'];
foreach ($tables as $table) {
    $cols = Illuminate\Support\Facades\Schema::getColumnListing($table);
    echo "Table [{$table}]: " . implode(', ', $cols) . "\n\n";
}
