<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UptChangeRequest;
use App\Models\UptFamilyCard;
use App\Models\UptLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FamilyCardManagementController extends Controller
{
    /**
     * Ambil daftar kartu keluarga nominal untuk UPT dan tahapan tertentu (JSON)
     */
    public function list(Request $request, int $uptId): JsonResponse
    {
        $upt = UptLocation::with('regency')->findOrFail($uptId);
        $stage = $request->input('stage', 'placement'); // 'placement' atau 'handover'

        $query = UptFamilyCard::where('upt_location_id', $uptId)
            ->where('stage', $stage);

        // Filter pencarian teks (Nama, NIK, No KK, Blok Kapling)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('head_of_family_name', 'ILIKE', "%{$search}%")
                  ->orWhere('family_card_number', 'ILIKE', "%{$search}%")
                  ->orWhere('nik', 'ILIKE', "%{$search}%")
                  ->orWhere('housing_block', 'ILIKE', "%{$search}%")
                  ->orWhere('origin_province', 'ILIKE', "%{$search}%")
                  ->orWhere('origin_regency', 'ILIKE', "%{$search}%");
            });
        }

        // Filter Jenis Transmigran (TPA / TPS)
        if ($type = $request->input('transmigrant_type')) {
            if ($type !== 'all' && $type !== '') {
                $query->where('transmigrant_type', $type);
            }
        }

        // Filter Status SHM
        if ($shm = $request->input('land_certificate_status')) {
            if ($shm !== 'all' && $shm !== '') {
                $query->where('land_certificate_status', $shm);
            }
        }

        $cards = $query->orderBy('housing_block')->orderBy('id')->get();

        // Hitung statistik registri warga UPT ini
        $allStageCards = UptFamilyCard::where('upt_location_id', $uptId)->where('stage', $stage);
        $totalKk = (int) (clone $allStageCards)->count();
        $totalJiwa = (int) (clone $allStageCards)->sum('family_members_count');
        $tpaCount = (int) (clone $allStageCards)->where('transmigrant_type', 'TPA')->count();
        $tpsCount = (int) (clone $allStageCards)->where('transmigrant_type', 'TPS')->count();
        $shmCount = (int) (clone $allStageCards)->where('land_certificate_status', 'ILIKE', '%SHM%')->count();

        // Cek pengajuan validasi buku registri yang berstatus pending
        $pendingValidation = UptChangeRequest::with('user')
            ->where('upt_location_id', $uptId)
            ->where('request_type', 'REGISTRY_SYNC')
            ->where('status', 'pending')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'upt' => [
                'id' => $upt->id,
                'upt_number' => $upt->upt_number,
                'upt_name' => $upt->upt_name,
                'regency' => $upt->regency?->name,
                'village' => $upt->current_village_name,
                'current_aggregate_kk' => $stage === 'handover' ? $upt->handover_kk : $upt->placement_kk,
                'current_aggregate_pop' => $stage === 'handover' ? $upt->handover_population : $upt->placement_population,
                'is_verified' => (bool) $upt->is_verified,
                'has_pending_validation' => $pendingValidation !== null,
                'pending_validation_id' => $pendingValidation?->id,
                'pending_operator_name' => $pendingValidation?->user?->name,
                'pending_validation_date' => $pendingValidation?->created_at?->format('d/m/Y H:i'),
                'can_direct_sync' => auth()->user()?->isSuperAdmin() ?? false,
            ],
            'stage' => $stage,
            'stats' => [
                'total_kk' => $totalKk,
                'total_jiwa' => $totalJiwa,
                'avg_jiwa' => $totalKk > 0 ? round($totalJiwa / $totalKk, 2) : 0,
                'tpa_count' => $tpaCount,
                'tps_count' => $tpsCount,
                'shm_count' => $shmCount,
            ],
            'data' => $cards,
        ]);
    }

    /**
     * Tambah 1 data KK baru
     */
    public function store(Request $request, int $uptId): JsonResponse
    {
        $upt = UptLocation::findOrFail($uptId);

        $validated = $request->validate([
            'stage' => ['required', 'in:placement,handover'],
            'head_of_family_name' => ['required', 'string', 'max:150'],
            'family_card_number' => ['nullable', 'string', 'max:30'],
            'nik' => ['nullable', 'string', 'max:30'],
            'family_members_count' => ['required', 'integer', 'min:1', 'max:30'],
            'transmigrant_type' => ['required', 'string', 'in:TPA,TPS'],
            'origin_province' => ['nullable', 'string', 'max:100'],
            'origin_regency' => ['nullable', 'string', 'max:100'],
            'housing_block' => ['nullable', 'string', 'max:50'],
            'land_certificate_status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // Maksimal 10 MB
        ]);

        $validated['upt_location_id'] = $upt->id;

        // Upload berkas jika ada
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $originalName = $file->getClientOriginalName();
            $safeName = 'kk_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $path = $file->storeAs('documents/family_cards', $safeName, 'public');

            $validated['document_path'] = $path;
            $validated['document_name'] = $originalName;
        }

        unset($validated['document_file']);

        $card = UptFamilyCard::create($validated);

        AuditLog::log(
            'CREATE_FAMILY_CARD',
            'upt_family_cards',
            $card->id,
            [
                'message' => "Menambahkan data KK '{$card->head_of_family_name}' pada UPT-{$upt->upt_number} ({$upt->upt_name}) tahap {$card->stage}" . ($card->document_path ? ' beserta lampiran berkas' : ''),
                'data' => $validated,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Data KK '{$card->head_of_family_name}' berhasil ditambahkan ke registri!",
            'data' => $card,
        ]);
    }

    /**
     * Update data 1 KK
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $card = UptFamilyCard::with('uptLocation')->findOrFail($id);

        $validated = $request->validate([
            'head_of_family_name' => ['required', 'string', 'max:150'],
            'family_card_number' => ['nullable', 'string', 'max:30'],
            'nik' => ['nullable', 'string', 'max:30'],
            'family_members_count' => ['required', 'integer', 'min:1', 'max:30'],
            'transmigrant_type' => ['required', 'string', 'in:TPA,TPS'],
            'origin_province' => ['nullable', 'string', 'max:100'],
            'origin_regency' => ['nullable', 'string', 'max:100'],
            'housing_block' => ['nullable', 'string', 'max:50'],
            'land_certificate_status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // Maksimal 10 MB
            'delete_document' => ['nullable', 'string', 'in:true,false,1,0'],
        ]);

        // Hapus berkas jika diminta
        if (filter_var($request->input('delete_document'), FILTER_VALIDATE_BOOLEAN) && $card->document_path) {
            if (Storage::disk('public')->exists($card->document_path)) {
                Storage::disk('public')->delete($card->document_path);
            }
            $validated['document_path'] = null;
            $validated['document_name'] = null;
        }

        // Simpan berkas baru jika diunggah
        if ($request->hasFile('document_file')) {
            if ($card->document_path && Storage::disk('public')->exists($card->document_path)) {
                Storage::disk('public')->delete($card->document_path);
            }

            $file = $request->file('document_file');
            $originalName = $file->getClientOriginalName();
            $safeName = 'kk_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $path = $file->storeAs('documents/family_cards', $safeName, 'public');

            $validated['document_path'] = $path;
            $validated['document_name'] = $originalName;
        }

        unset($validated['document_file'], $validated['delete_document']);

        $oldData = $card->toArray();
        $card->update($validated);

        AuditLog::log(
            'UPDATE_FAMILY_CARD',
            'upt_family_cards',
            $card->id,
            [
                'message' => "Memperbarui data KK '{$card->head_of_family_name}' pada UPT-{$card->uptLocation?->upt_number} tahap {$card->stage}",
                'old' => $oldData,
                'new' => $validated,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Data KK '{$card->head_of_family_name}' berhasil diperbarui!",
            'data' => $card,
        ]);
    }

    /**
     * Unduh / buka berkas dokumen KK
     */
    public function downloadDocument(int $id): BinaryFileResponse|JsonResponse
    {
        $card = UptFamilyCard::findOrFail($id);

        if (!$card->document_path || !Storage::disk('public')->exists($card->document_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas digital KK tidak ditemukan di vault storage.',
            ], 404);
        }

        return response()->download(
            Storage::disk('public')->path($card->document_path),
            $card->document_name ?? ('dokumen_kk_' . $card->id . '.pdf')
        );
    }

    /**
     * Hapus data 1 KK
     */
    public function destroy(int $id): JsonResponse
    {
        $card = UptFamilyCard::with('uptLocation')->findOrFail($id);
        $cardName = $card->head_of_family_name;
        $uptNumber = $card->uptLocation?->upt_number;
        $stage = $card->stage;

        // Hapus fisik berkas jika ada
        if ($card->document_path && Storage::disk('public')->exists($card->document_path)) {
            Storage::disk('public')->delete($card->document_path);
        }

        $card->delete();

        AuditLog::log(
            'DELETE_FAMILY_CARD',
            'upt_family_cards',
            $id,
            [
                'message' => "Menghapus data KK '{$cardName}' dari UPT-{$uptNumber} tahap {$stage}",
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Data KK '{$cardName}' berhasil dihapus dari registri.",
        ]);
    }

    /**
     * Unduh Template Excel / CSV untuk Import Warga
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $stage = $request->input('stage', 'placement');
        $filename = "template_registri_transmigran_{$stage}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // Header kolom
            fputcsv($handle, [
                'No KK (16 Digit)',
                'NIK Kepala Keluarga (16 Digit)',
                'Nama Lengkap Kepala Keluarga',
                'Jumlah Jiwa',
                'Jenis Transmigran (TPA/TPS)',
                'Asal Provinsi',
                'Asal Kabupaten/Kota',
                'Blok / No Kapling Rumah',
                'Status SHM (Sudah SHM / Proses BPN / Belum SHM)',
                'Catatan Keterangan',
            ]);

            // Baris Contoh 1 (TPA)
            fputcsv($handle, [
                '6301012304850001',
                '6301011508600002',
                'Sutrisno Wibowo',
                '4',
                'TPA',
                'Jawa Tengah',
                'Banyumas',
                'Blok A No. 05',
                'Sudah SHM',
                'Transmigran asal program 1985',
            ]);

            // Baris Contoh 2 (TPS)
            fputcsv($handle, [
                '6301012304850002',
                '6301012010620004',
                'Muhammad Arsyad',
                '3',
                'TPS',
                'Kalimantan Selatan',
                'Banjar',
                'Blok B No. 12',
                'Proses BPN',
                'Penduduk setempat (alokasi lokal)',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Ekspor Data Nominal Warga per UPT ke format CSV
     */
    public function export(Request $request, int $uptId): StreamedResponse
    {
        $upt = UptLocation::with('regency')->findOrFail($uptId);
        $stage = $request->input('stage', 'placement');

        $cards = UptFamilyCard::where('upt_location_id', $uptId)
            ->where('stage', $stage)
            ->orderBy('housing_block')
            ->orderBy('id')
            ->get();

        $stageName = $stage === 'handover' ? 'serah_terima' : 'penempatan_awal';
        $filename = "buku_registri_warga_UPT_{$upt->upt_number}_{$stageName}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($upt, $stage, $cards) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'No UPT',
                'Nama UPT',
                'Kabupaten',
                'Tahapan',
                'No KK',
                'NIK Kepala Keluarga',
                'Nama Kepala Keluarga',
                'Jumlah Jiwa',
                'Jenis (TPA/TPS)',
                'Asal Provinsi',
                'Asal Kabupaten',
                'Blok / No Kapling',
                'Status Sertipikat SHM',
                'Catatan',
            ]);

            $no = 1;
            foreach ($cards as $c) {
                fputcsv($handle, [
                    $no++,
                    'UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT),
                    $upt->upt_name,
                    $upt->regency?->name ?? '-',
                    strtoupper($stage),
                    "'" . $c->family_card_number, // Prefix petik agar angka 16 digit tidak terpotong di Excel
                    "'" . $c->nik,
                    $c->head_of_family_name,
                    $c->family_members_count,
                    $c->transmigrant_type,
                    $c->origin_province,
                    $c->origin_regency,
                    $c->housing_block,
                    $c->land_certificate_status,
                    $c->notes,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import File Excel / CSV Warga Transmigran
     */
    public function import(Request $request, int $uptId): JsonResponse
    {
        $upt = UptLocation::findOrFail($uptId);

        $request->validate([
            'stage' => ['required', 'in:placement,handover'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'], // Max 5MB
        ]);

        $stage = $request->input('stage');
        $file = $request->file('file');
        $path = $file->getRealPath();

        $importedCount = 0;
        $skippedCount = 0;

        // Baca file CSV
        if (($handle = fopen($path, 'r')) !== false) {
            // Lewati UTF-8 BOM jika ada
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Baca baris header
            $header = fgetcsv($handle, 2000, ',');

            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                // Lewati baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                $kk = trim($row[0] ?? '');
                $nik = trim($row[1] ?? '');
                $name = trim($row[2] ?? '');
                $members = (int) trim($row[3] ?? '1');
                $type = strtoupper(trim($row[4] ?? 'TPA'));
                $prov = trim($row[5] ?? '');
                $reg = trim($row[6] ?? '');
                $block = trim($row[7] ?? '');
                $shm = trim($row[8] ?? 'Belum SHM');
                $notes = trim($row[9] ?? '');

                if (empty($name)) {
                    $skippedCount++;
                    continue;
                }

                if (!in_array($type, ['TPA', 'TPS'])) {
                    $type = 'TPA';
                }

                if ($members < 1) {
                    $members = 1;
                }

                UptFamilyCard::create([
                    'upt_location_id' => $upt->id,
                    'stage' => $stage,
                    'family_card_number' => $kk ?: null,
                    'nik' => $nik ?: null,
                    'head_of_family_name' => $name,
                    'family_members_count' => $members,
                    'transmigrant_type' => $type,
                    'origin_province' => $prov ?: null,
                    'origin_regency' => $reg ?: null,
                    'housing_block' => $block ?: null,
                    'land_certificate_status' => $shm ?: 'Belum SHM',
                    'notes' => $notes ?: null,
                ]);

                $importedCount++;
            }
            fclose($handle);
        }

        AuditLog::log(
            'IMPORT_FAMILY_CARDS',
            'upt_family_cards',
            $upt->id,
            [
                'message' => "Import {$importedCount} data KK transmigran pada UPT-{$upt->upt_number} ({$stage})",
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'stage' => $stage,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengimpor {$importedCount} data KK transmigran!" . ($skippedCount > 0 ? " ({$skippedCount} baris dilewati karena nama kosong)." : ""),
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
        ]);
    }

    /**
     * Operator Wilayah mengajukan pengesahan Buku Registri Warga UPT ke Provinsi
     */
    public function submitValidation(Request $request, int $uptId): JsonResponse
    {
        $user = auth()->user();
        $upt = UptLocation::with('regency')->findOrFail($uptId);
        $stage = $request->input('stage', 'placement');

        // Hitung statistik aktual dari registri KK
        $stageCards = UptFamilyCard::where('upt_location_id', $uptId)->where('stage', $stage);
        $totalKk = (int) (clone $stageCards)->count();
        $totalJiwa = (int) (clone $stageCards)->sum('family_members_count');
        $tpaCount = (int) (clone $stageCards)->where('transmigrant_type', 'TPA')->count();
        $tpsCount = (int) (clone $stageCards)->where('transmigrant_type', 'TPS')->count();
        $shmCount = (int) (clone $stageCards)->where('land_certificate_status', 'ILIKE', '%SHM%')->count();

        if ($totalKk === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Buku registri masih kosong (0 KK). Tambahkan minimal 1 data KK sebelum mengajukan pengesahan ke Provinsi.'
            ], 422);
        }

        // Cek apakah sudah ada antrean pending
        $existing = UptChangeRequest::where('upt_location_id', $uptId)
            ->where('request_type', 'REGISTRY_SYNC')
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan pengesahan buku registri UPT ini sudah terdaftar di antrean verifikasi Provinsi (Draf #' . $existing->id . ').'
            ], 422);
        }

        $currentMasterKk = $stage === 'handover' ? $upt->handover_kk : $upt->placement_kk;
        $currentMasterPop = $stage === 'handover' ? $upt->handover_population : $upt->placement_population;
        $deltaKk = $totalKk - $currentMasterKk;

        $note = $request->input('submission_note');
        if (empty($note)) {
            $stageLabel = $stage === 'handover' ? 'Serah Terima Pemda' : 'Penempatan Awal';
            $sign = $deltaKk >= 0 ? '+' : '';
            $note = "Pengajuan pengesahan buku registri warga ({$stageLabel}) dan sinkronisasi angka master UPT {$upt->upt_name}. Terdata {$totalKk} KK ({$sign}{$deltaKk} KK dari angka master saat ini).";
        }

        $proposedPayload = [
            'submission_note' => $note,
            'stage' => $stage,
            'registry_summary' => [
                'stage' => $stage,
                'stage_label' => $stage === 'handover' ? 'Serah Terima Pemda' : 'Penempatan Awal',
                'current_master_kk' => $currentMasterKk,
                'current_master_population' => $currentMasterPop,
                'recorded_kk' => $totalKk,
                'recorded_population' => $totalJiwa,
                'delta_kk' => $deltaKk,
                'tpa_count' => $tpaCount,
                'tps_count' => $tpsCount,
                'shm_count' => $shmCount,
            ],
            ($stage === 'handover' ? 'handover_kk' : 'placement_kk') => $totalKk,
            ($stage === 'handover' ? 'handover_population' : 'placement_population') => $totalJiwa,
        ];

        $changeRequest = UptChangeRequest::create([
            'upt_location_id' => $upt->id,
            'user_id' => $user->id,
            'request_type' => 'REGISTRY_SYNC',
            'proposed_payload' => $proposedPayload,
            'status' => 'pending',
        ]);

        AuditLog::log('SUBMIT_REGISTRY_VALIDATION', 'upt_change_requests', $changeRequest->id, [
            'upt_name' => $upt->upt_name,
            'stage' => $stage,
            'recorded_kk' => $totalKk,
            'delta_kk' => $deltaKk,
            'operator' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Buku Registri Warga UPT {$upt->upt_name} ({$totalKk} KK) berhasil diajukan ke Antrean Verifikasi Provinsi!",
            'change_request_id' => $changeRequest->id,
        ]);
    }

    /**
     * Sinkronisasi angka agregat master UPT dari jumlah KK nominal registri
     * - Jika Super Admin: Sinkronkan langsung ke basis data master
     * - Jika Operator Wilayah: Otomatis dialihkan ke submitValidation (alur Solusi Tengah)
     */
    public function syncAggregate(Request $request, int $uptId): JsonResponse
    {
        $user = auth()->user();

        // Jika operator, gunakan alur pengesahan draf usulan
        if (! $user->isSuperAdmin()) {
            return $this->submitValidation($request, $uptId);
        }

        $upt = UptLocation::findOrFail($uptId);
        $stage = $request->input('stage', 'placement');

        $stageCards = UptFamilyCard::where('upt_location_id', $uptId)->where('stage', $stage);
        $countKk = (int) (clone $stageCards)->count();
        $countPop = (int) (clone $stageCards)->sum('family_members_count');

        if ($countKk === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menyinkronkan: Data registri warga masih kosong (0 KK).'
            ], 422);
        }

        if ($stage === 'handover') {
            $upt->handover_kk = $countKk;
            $upt->handover_population = $countPop;
        } else {
            $upt->placement_kk = $countKk;
            $upt->placement_population = $countPop;
        }
        $upt->is_verified = true;
        $upt->save();

        AuditLog::log('SYNC_AGGREGATE_DIRECT', 'upt_locations', $upt->id, [
            'upt_name' => $upt->upt_name,
            'stage' => $stage,
            'new_kk' => $countKk,
            'new_pop' => $countPop,
            'admin' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Angka rekapitulasi master UPT {$upt->upt_name} berhasil disinkronkan langsung!",
            'data' => [
                'upt_id' => $upt->id,
                'count_kk' => $countKk,
                'count_pop' => $countPop,
                'total_all_placement_kk' => UptLocation::sum('placement_kk'),
                'total_all_placement_pop' => UptLocation::sum('placement_population'),
                'total_all_handover_kk' => UptLocation::sum('handover_kk'),
                'total_all_handover_pop' => UptLocation::sum('handover_population'),
            ]
        ]);
    }
}
