<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\VerificationController;
use App\Models\AuditLog;
use App\Models\UptChangeRequest;
use App\Models\UptLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== MENGUJI APPROVAL CONTROLLER LANGSUNG ===" . PHP_EOL;

$superAdmin = User::where('role', 'super_admin')->first();
Auth::login($superAdmin);

$pending = UptChangeRequest::with('uptLocation')->where('status', 'pending')->first();
if (! $pending) {
    echo "Tidak ada draf pending." . PHP_EOL;
    exit;
}

echo "Menguji approve pada draf #{$pending->id} untuk UPT: {$pending->uptLocation->upt_name}" . PHP_EOL;
$controller = app(VerificationController::class);
$request = new Request(['reviewer_note' => 'Disetujui via pengujian sistem forensik.']);

$response = $controller->approve($request, $pending->id);

$pending->refresh();
echo "Status setelah approve: {$pending->status}" . PHP_EOL;
echo "Reviewer: " . ($pending->reviewer->name ?? 'None') . PHP_EOL;
echo "Reviewed at: " . $pending->reviewed_at . PHP_EOL;

$upt = UptLocation::find($pending->upt_location_id);
echo "Data UPT terkini (Desa): " . $upt->current_village_name . PHP_EOL;
echo "Data UPT terkini (SHM): " . $upt->shm_status . PHP_EOL;

$latestAudit = AuditLog::latest('id')->first();
echo "Audit log terakhir: " . $latestAudit->action . " pada " . $latestAudit->target_table . " ID " . $latestAudit->target_id . PHP_EOL;
echo "Detail audit log: " . json_encode($latestAudit->details) . PHP_EOL;

// Sekarang kita kembalikan statusnya ke pending agar user bisa menguji sendiri di UI browser
$pending->update([
    'status' => 'pending',
    'reviewed_by' => null,
    'reviewed_at' => null,
    'reviewer_note' => null,
]);
echo "Status dikembalikan ke 'pending' untuk pengujian manual user di antarmuka UI!" . PHP_EOL;
