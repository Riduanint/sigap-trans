<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UptLocation;
use App\Models\UptFamilyCard;
use App\Models\UptChangeRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\FamilyCardManagementController;
use App\Http\Controllers\Admin\VerificationController;
use Illuminate\Support\Facades\Auth;

echo "=== MEMULAI TEST ALUR REKOMENDASI SOLUSI TENGAH REGISTRI WARGA ===\n";

// 1. Ambil user operator dan super admin
$operator = User::where('role', 'operator_kabupaten')->first();
$superAdmin = User::where('role', 'super_admin')->first();

if (!$operator) {
    echo "ERROR: Operator user tidak ditemukan!\n";
    exit(1);
}
if (!$superAdmin) {
    echo "ERROR: Super Admin user tidak ditemukan!\n";
    exit(1);
}

echo "Operator: {$operator->name} (ID: {$operator->id})\n";
echo "Super Admin: {$superAdmin->name} (ID: {$superAdmin->id})\n";

// 2. Pilih salah satu UPT yang punya family card
$uptWithCards = UptFamilyCard::first()?->upt_location_id;
if (!$uptWithCards) {
    $upt = UptLocation::first();
    // Buat 1 dummy KK untuk test
    UptFamilyCard::create([
        'upt_location_id' => $upt->id,
        'stage' => 'placement',
        'head_of_family_name' => 'Warga Percobaan Test',
        'family_members_count' => 4,
        'transmigrant_type' => 'TPA',
        'land_certificate_status' => 'Sudah SHM',
    ]);
    $uptWithCards = $upt->id;
}

$upt = UptLocation::findOrFail($uptWithCards);
echo "Testing pada UPT: [ID: {$upt->id}] {$upt->upt_name} (KK master saat ini: {$upt->placement_kk})\n";

// Bersihkan pending change request sebelumnya untuk UPT ini agar clean test
UptChangeRequest::where('upt_location_id', $upt->id)->where('status', 'pending')->delete();

// Set is_verified = false awalnya
$upt->is_verified = false;
$upt->save();

// 3. Test Operator mengajukan validasi via FamilyCardManagementController@submitValidation
Auth::login($operator);
$familyCtrl = new FamilyCardManagementController();

$submitReq = new Request([
    'stage' => 'placement',
    'submission_note' => 'Catatan test pengesahan registri dari operator lapangan.'
]);

$submitRes = $familyCtrl->submitValidation($submitReq, $upt->id);
$submitData = json_decode($submitRes->getContent(), true);

echo "Hasil Submit Validasi oleh Operator:\n";
print_r($submitData);

if (!($submitData['success'] ?? false)) {
    echo "FAILED: Gagal submit validasi!\n";
    exit(1);
}

$changeRequestId = $submitData['change_request_id'];
$changeRequest = UptChangeRequest::find($changeRequestId);
echo "Berhasil membuat UptChangeRequest #{$changeRequestId} [Tipe: {$changeRequest->request_type}, Status: {$changeRequest->status}]\n";

// 4. Test List UPT mengembalikan status pending_validation
$listReq = new Request(['stage' => 'placement']);
$listRes = $familyCtrl->list($listReq, $upt->id);
$listData = json_decode($listRes->getContent(), true);

echo "Pengecekan Response List Drawer:\n";
echo "- is_verified: " . var_export($listData['upt']['is_verified'], true) . "\n";
echo "- has_pending_validation: " . var_export($listData['upt']['has_pending_validation'], true) . "\n";
echo "- pending_validation_id: " . $listData['upt']['pending_validation_id'] . "\n";
echo "- can_direct_sync: " . var_export($listData['upt']['can_direct_sync'], true) . "\n";

if (!$listData['upt']['has_pending_validation']) {
    echo "FAILED: has_pending_validation harus true!\n";
    exit(1);
}

// 5. Test Super Admin membuka Diff Checker di VerificationController@show
Auth::login($superAdmin);
$verifCtrl = new VerificationController();

$view = $verifCtrl->show($changeRequestId);
$viewData = $view->getData();

echo "Pengecekan Data View Diff Checker VerificationController@show:\n";
echo "- request_type: " . $viewData['changeRequest']->request_type . "\n";
echo "- registrySummary stage: " . ($viewData['registrySummary']['stage'] ?? 'null') . "\n";
echo "- registrySummary recorded_kk: " . ($viewData['registrySummary']['recorded_kk'] ?? 'null') . "\n";
echo "- registryTotalCards: " . $viewData['registryTotalCards'] . "\n";
echo "- sample cards count: " . count($viewData['registrySampleCards']) . "\n";

if (!$viewData['registrySummary']) {
    echo "FAILED: registrySummary tidak ditemukan di view!\n";
    exit(1);
}

// 6. Test Super Admin menyetujui permohonan di VerificationController@approve
$approveReq = new Request([
    'reviewer_note' => 'Disetujui. Data registri warga telah valid sesuai laporan lapangan.'
]);

$approveRes = $verifCtrl->approve($approveReq, $changeRequestId);
$upt->refresh();
$changeRequest->refresh();

echo "Pengecekan Setelah Disetujui (Approved):\n";
echo "- Status Change Request: {$changeRequest->status}\n";
echo "- Reviewer Note: {$changeRequest->reviewer_note}\n";
echo "- UPT placement_kk baru: {$upt->placement_kk}\n";
echo "- UPT placement_population baru: {$upt->placement_population}\n";
echo "- UPT is_verified: " . var_export($upt->is_verified, true) . "\n";

if ($changeRequest->status !== 'approved' || !$upt->is_verified) {
    echo "FAILED: Approval tidak berhasil memperbarui status dan is_verified!\n";
    exit(1);
}

echo "\n>>> SEMUA PENGETESAN ALUR DUAL-LAYER GOVERNANCE BERHASIL 100%! <<<\n";
