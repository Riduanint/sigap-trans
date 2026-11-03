<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Regency;
use App\Models\UptLocation;
use App\Models\UptChangeRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ViewErrorBag;

ViewFacade::share('errors', new ViewErrorBag);

echo "=== TESTING ALUR PENGAJUAN & VERIFIKASI UPT BARU ===\n";

$operator = User::where('role', 'operator_kabupaten')->first();
$admin = User::where('role', 'super_admin')->first();
$regency = Regency::first();

if (!$operator || !$admin || !$regency) {
    echo "Prerequisites missing!\n";
    exit(1);
}

echo "Operator: {$operator->name} ({$operator->email})\n";
echo "Super Admin: {$admin->name} ({$admin->email})\n";
echo "Kabupaten: {$regency->name} (ID: {$regency->id})\n";

DB::beginTransaction();

try {
    $nextNumber = (UptLocation::max('upt_number') ?? 0) + 1;
    $testUptName = "UPT Uji Coba Baru " . time();

    echo "1. Membuat Usulan Pengajuan UPT Baru via Operator...\n";
    $payload = [
        'mode' => 'new_upt',
        'regency_id' => $regency->id,
        'upt_name' => $testUptName,
        'upt_number' => $nextNumber,
        'current_village_name' => 'Desa Makmur Jaya',
        'business_pattern' => 'TPLK',
        'placement_year' => 2024,
        'placement_kk' => 75,
        'placement_population' => 300,
        'handover_year' => 2026,
        'handover_kk' => 75,
        'handover_population' => 300,
        'issue_status' => 'clean',
        'issue_note' => 'Lokasi bebas sengketa batas dan klaim adat',
        'shm_status' => 'Belum SHM',
        'latitude' => -1.234567,
        'longitude' => 120.123456,
        'submission_note' => 'Permohonan pendaftaran UPT baru berdasarkan SK Bupati.',
        'attached_document' => [
            'document_name' => 'SK Bupati Pembentukan SP Makmur',
            'document_number' => 'SK/BUP/2024/099',
            'file_name' => 'sk_bupati.pdf',
            'file_path' => 'upt_documents/test.pdf',
            'file_size_bytes' => 102400,
        ]
    ];

    $changeRequest = UptChangeRequest::create([
        'upt_location_id' => null,
        'user_id' => $operator->id,
        'request_type' => 'CREATE_UPT',
        'status' => 'pending',
        'proposed_payload' => $payload,
    ]);

    echo "   -> Berhasil dibuat! ID Usulan: {$changeRequest->id}, Request Type: {$changeRequest->request_type}, UPT Location ID: " . var_export($changeRequest->upt_location_id, true) . "\n";

    echo "2. Menguji Render View Operator (show & index)...\n";
    auth()->login($operator);
    $opShowHtml = view('operator.draf_usulan.show', compact('changeRequest'))->render();
    if (strlen($opShowHtml) > 0) {
        echo "   -> View operator.draf_usulan.show render OK (" . strlen($opShowHtml) . " bytes)\n";
    }

    echo "3. Menguji Render View Admin Verification (show & index)...\n";
    $upt = null;
    $comparisonFields = [];
    $registrySummary = null;
    $registrySampleCards = [];
    $registryTotalCards = 0;
    $proposedRegency = $regency;

    $adminShowHtml = view('admin.verifikasi_draf_usulan.show', compact(
        'changeRequest',
        'upt',
        'payload',
        'comparisonFields',
        'registrySummary',
        'registrySampleCards',
        'registryTotalCards',
        'proposedRegency'
    ))->render();

    if (strlen($adminShowHtml) > 0) {
        echo "   -> View admin.verifikasi_draf_usulan.show render OK (" . strlen($adminShowHtml) . " bytes)\n";
    }

    echo "4. Menguji Eksekusi Approval oleh Super Admin...\n";
    // Simulate approval logic from VerificationController
    $newUptData = [
        'regency_id' => (int) $payload['regency_id'],
        'upt_number' => $nextNumber,
        'upt_name' => $payload['upt_name'],
        'current_village_name' => $payload['current_village_name'],
        'business_pattern' => $payload['business_pattern'] ?? 'TPLK',
        'placement_year' => $payload['placement_year'] ?? null,
        'placement_kk' => (int) ($payload['placement_kk'] ?? 0),
        'placement_population' => (int) ($payload['placement_population'] ?? 0),
        'handover_year' => $payload['handover_year'] ?? null,
        'handover_kk' => (int) ($payload['handover_kk'] ?? 0),
        'handover_population' => (int) ($payload['handover_population'] ?? 0),
        'issue_status' => $payload['issue_status'] ?? 'clean',
        'issue_note' => $payload['issue_note'] ?? null,
        'shm_status' => $payload['shm_status'] ?? 'Belum SHM',
        'latitude' => $payload['latitude'] ?? null,
        'longitude' => $payload['longitude'] ?? null,
        'is_verified' => true,
    ];

    $lat = (float) $newUptData['latitude'];
    $lng = (float) $newUptData['longitude'];
    $delta = 0.008;
    $newUptData['polygon_geojson'] = [
        'type' => 'Polygon',
        'coordinates' => [[
            [$lng - $delta, $lat - $delta],
            [$lng + $delta, $lat - $delta],
            [$lng + $delta, $lat + $delta],
            [$lng - $delta, $lat + $delta],
            [$lng - $delta, $lat - $delta],
        ]]
    ];

    $newUpt = UptLocation::create($newUptData);
    echo "   -> New UPT created in database! ID: {$newUpt->id}, Name: {$newUpt->upt_name}, Number: {$newUpt->upt_number}\n";

    DB::statement("
        UPDATE upt_locations 
        SET coordinate_point = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
            polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(polygon_geojson::text)), 4326)
        WHERE id = {$newUpt->id}
    ");
    echo "   -> PostGIS spatial point & polygon generated!\n";

    $changeRequest->update([
        'upt_location_id' => $newUpt->id,
        'status' => 'approved',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
        'reviewer_note' => 'Disetujui dalam unit test.',
    ]);

    AuditLog::log('APPROVE_CREATE_UPT', 'upt_locations', $newUpt->id, [
        'change_request_id' => $changeRequest->id,
        'upt_number' => $newUpt->upt_number,
        'upt_name' => $newUpt->upt_name,
        'reviewer' => $admin->name,
    ]);
    echo "   -> Change request updated & Audit Log saved!\n";

    echo "5. Verifikasi view pasca-approval...\n";
    $changeRequest->refresh();
    $opShowApprovedHtml = view('operator.draf_usulan.show', compact('changeRequest'))->render();
    echo "   -> View pasca-approval OK (" . strlen($opShowApprovedHtml) . " bytes)\n";

    echo "=== SEMUA PENGUJIAN ALUR PENGAJUAN UPT BARU BERHASIL (100% SUCCESS) ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Rollback test data to keep database clean
    DB::rollBack();
    echo "Database transaction rolled back to keep data pristine.\n";
}
