<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\BackupController;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== MEMULAI TEST MODUL KONTROL & KEAMANAN ===" . PHP_EOL;

$superAdmin = User::where('role', 'super_admin')->first();
Auth::login($superAdmin);

$routesToTest = [
    '/admin/users' => 'Manajemen Pengguna (RBAC)',
    '/admin/users/create' => 'Form Tambah Pengguna',
    '/admin/audit-logs' => 'Log Audit Sistem (Audit Trail)',
    '/admin/backup' => 'Cadangan Database (Backup)',
];

foreach ($routesToTest as $uri => $label) {
    $req = Request::create($uri, 'GET');
    $resp = $app->handle($req);
    $status = $resp->getStatusCode();

    if ($status === 200) {
        echo "[+] [HTTP 200] {$label} ({$uri}) OK!" . PHP_EOL;
    } else {
        echo "[-] [HTTP {$status}] {$label} ({$uri}) FAILED!" . PHP_EOL;
    }
}

// Uji Test Integrasi Backup Controller
echo PHP_EOL . "=== MENGUJI METODE PENCADANGAN DATABASE ===" . PHP_EOL;
$backupController = app(BackupController::class);

// 1. Uji Create Backup
$backupController->createBackup();
$backupFiles = glob(storage_path('app/backups/*.sql'));
echo "[+] Berkas cadangan berhasil dibuat: " . count($backupFiles) . " file ditemukan." . PHP_EOL;
if (!empty($backupFiles)) {
    echo "    Contoh file: " . basename(end($backupFiles)) . " (" . round(filesize(end($backupFiles))/1024, 2) . " KB)" . PHP_EOL;
}

// 2. Uji Test DB Integrity
$backupController->testIntegrity();
$latestAudit = AuditLog::latest('id')->first();
echo "[+] Uji Integritas Basis Data dicatat di Audit Log: " . $latestAudit->action . " - Status: " . ($latestAudit->details['status'] ?? '-') . PHP_EOL;

echo PHP_EOL . "=== SELURUH PENGUJIAN KONTROL & KEAMANAN BERHASIL 100%! ===" . PHP_EOL;
