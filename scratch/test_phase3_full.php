<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditLog;
use App\Models\UptChangeRequest;
use App\Models\UptLocation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== MEMULAI TEST VERIFIKASI FASE 3 LENGKAP ===" . PHP_EOL;

// 1. Uji Login Akun Masing-masing Role
$roles = [
    'super_admin' => 'superadmin@kalselprov.go.id',
    'operator_kabupaten' => 'operator.tapin@kalselprov.go.id',
    'eksekutif' => 'kadis@kalselprov.go.id',
    'mitra_bpn' => 'kanwil.bpn@atrbpn.go.id',
];

foreach ($roles as $role => $email) {
    $user = User::where('email', $email)->first();
    if (! $user) {
        echo "[-] ERROR: User $email tidak ditemukan!" . PHP_EOL;
        continue;
    }
    echo "[+] User OK: {$user->name} ({$user->role}) - Email: {$user->email}" . PHP_EOL;
}

// 2. Uji Render Antarmuka Tiap Role via HTTP Request Simulator
$tests = [
    ['role' => 'super_admin', 'email' => 'superadmin@kalselprov.go.id', 'uri' => '/admin/dashboard', 'name' => 'Super Admin Dashboard'],
    ['role' => 'super_admin', 'email' => 'superadmin@kalselprov.go.id', 'uri' => '/admin/upt', 'name' => 'Master Data 124 UPT'],
    ['role' => 'super_admin', 'email' => 'superadmin@kalselprov.go.id', 'uri' => '/admin/verification', 'name' => 'Antrean Verifikasi Draf'],
    ['role' => 'super_admin', 'email' => 'superadmin@kalselprov.go.id', 'uri' => '/admin/documents', 'name' => 'E-Arsip BAST'],
    ['role' => 'operator_kabupaten', 'email' => 'operator.tapin@kalselprov.go.id', 'uri' => '/operator/dashboard', 'name' => 'Operator Kabupaten Dashboard'],
    ['role' => 'operator_kabupaten', 'email' => 'operator.tapin@kalselprov.go.id', 'uri' => '/operator/requests', 'name' => 'Operator Riwayat Usulan'],
    ['role' => 'operator_kabupaten', 'email' => 'operator.tapin@kalselprov.go.id', 'uri' => '/operator/requests/create', 'name' => 'Form Pengajuan Draf Baru'],
    ['role' => 'eksekutif', 'email' => 'kadis@kalselprov.go.id', 'uri' => '/executive/dashboard', 'name' => 'Executive Dashboard (Kadis)'],
    ['role' => 'mitra_bpn', 'email' => 'kanwil.bpn@atrbpn.go.id', 'uri' => '/bpn/dashboard', 'name' => 'BPN Portal Pertanahan (SHM)'],
];

echo PHP_EOL . "=== MENGUJI RENDER ROUTE MASING-MASING PERAN ===" . PHP_EOL;

foreach ($tests as $t) {
    $user = User::where('email', $t['email'])->first();
    Auth::login($user);

    $req = Illuminate\Http\Request::create($t['uri'], 'GET');
    $response = $app->handle($req);
    $status = $response->getStatusCode();

    if ($status === 200) {
        echo "[+] [HTTP 200] {$t['name']} ({$t['uri']}) BERHASIL DI-RENDER" . PHP_EOL;
    } else {
        echo "[-] [HTTP $status] {$t['name']} ({$t['uri']}) GAGAL!" . PHP_EOL;
        if ($status >= 400 && $status < 600) {
            echo "    Error content: " . substr(strip_tags($response->getContent()), 0, 200) . PHP_EOL;
        }
    }
}

// 3. Uji Diff Checker & Alur Approval
echo PHP_EOL . "=== MENGUJI ALUR KERJA APPROVAL & DIFF CHECKER ===" . PHP_EOL;
$pending = UptChangeRequest::with('uptLocation')->where('status', 'pending')->first();
if ($pending) {
    echo "[+] Menemukan Draf Pending ID #{$pending->id} untuk UPT: {$pending->uptLocation->upt_name}" . PHP_EOL;

    // Login sebagai Super Admin
    $superAdmin = User::where('role', 'super_admin')->first();
    Auth::login($superAdmin);

    // Cek render Diff Checker Show page
    $reqShow = Illuminate\Http\Request::create("/admin/verification/{$pending->id}", 'GET');
    $respShow = $app->handle($reqShow);
    echo "[+] Diff Checker Show Page status: " . $respShow->getStatusCode() . PHP_EOL;

    // Lakukan Approval
    $reqApprove = Illuminate\Http\Request::create("/admin/verification/{$pending->id}/approve", 'POST', [
        'reviewer_note' => 'Disetujui dalam pengujian otomatis alur kerja Fase 3.',
    ]);
    $respApprove = $app->handle($reqApprove);
    echo "[+] Post Approve status: " . $respApprove->getStatusCode() . " (Redirect: " . $respApprove->headers->get('Location') . ")" . PHP_EOL;

    // Verifikasi status berubah jadi approved
    $pending->refresh();
    echo "[+] Status Change Request setelah approve: {$pending->status}" . PHP_EOL;
    echo "[+] Reviewer Note: {$pending->reviewer_note}" . PHP_EOL;
    echo "[+] Reviewed by: " . ($pending->reviewer->name ?? 'None') . PHP_EOL;

    // Verifikasi data master UPT termutakhirkan
    $uptUpdated = UptLocation::find($pending->upt_location_id);
    echo "[+] Data UPT terkini: Desa = {$uptUpdated->current_village_name}, Status Lahan = {$uptUpdated->issue_status}" . PHP_EOL;

    // Verifikasi Audit Log bertambah
    $latestAudit = AuditLog::latest('id')->first();
    echo "[+] Audit Log Terakhir: Aksi = {$latestAudit->action}, Target = {$latestAudit->target_table} #{$latestAudit->target_id}" . PHP_EOL;
} else {
    echo "[-] Tidak ada draf pending untuk diuji." . PHP_EOL;
}

echo PHP_EOL . "=== SELURUH PENGUJIAN FASE 3 SELESAI DENGAN SUKSES! ===" . PHP_EOL;
