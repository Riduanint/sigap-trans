<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptChangeRequest;
use App\Models\UptDocument;
use App\Models\UptLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Tampilkan daftar antrean verifikasi draf usulan
     */
    public function index(Request $request): View
    {
        $regencies = Regency::orderBy('id')->get();

        $query = UptChangeRequest::with(['uptLocation.regency', 'user', 'reviewer']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $regencyId = (int) $request->regency_id;
            $query->where(function ($q) use ($regencyId) {
                $q->whereHas('uptLocation', function ($sub) use ($regencyId) {
                    $sub->where('regency_id', $regencyId);
                })->orWhereRaw("(proposed_payload->>'regency_id')::int = ?", [$regencyId]);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('uptLocation', function ($sub) use ($search) {
                    $sub->where('upt_name', 'ilike', "%{$search}%")
                      ->orWhere('current_village_name', 'ilike', "%{$search}%");
                })->orWhereRaw("proposed_payload->>'upt_name' ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("proposed_payload->>'current_village_name' ILIKE ?", ["%{$search}%"]);
            });
        }

        $requests = ($request->status === 'pending' ? $query->oldest() : $query->latest())->orderBy('id')->paginate(15)->withQueryString();

        // Hitungan status untuk tab lencana
        $counts = [
            'all' => UptChangeRequest::count(),
            'pending' => UptChangeRequest::where('status', 'pending')->count(),
            'approved' => UptChangeRequest::where('status', 'approved')->count(),
            'rejected' => UptChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.verifikasi_draf_usulan.verifikasi_draf_usulan', compact('requests', 'regencies', 'counts'));
    }

    /**
     * Tampilkan antarmuka Diff Checker komparasi data aktif vs data usulan
     */
    public function show(int $id): View
    {
        $changeRequest = UptChangeRequest::with(['uptLocation.regency', 'uptLocation.skpArea', 'user', 'reviewer'])
            ->findOrFail($id);

        $upt = $changeRequest->uptLocation;
        $payload = $changeRequest->proposed_payload ?? [];

        // Definisi field komparasi jika UPT eksisting
        $comparisonFields = [];
        if ($upt) {
            $comparisonFields = [
                'current_village_name' => [
                    'label' => 'Nama Desa Definitif',
                    'current' => $upt->current_village_name,
                    'proposed' => $payload['current_village_name'] ?? $upt->current_village_name,
                ],
                'business_pattern' => [
                    'label' => 'Pola Usaha Budidaya',
                    'current' => $upt->business_pattern,
                    'proposed' => $payload['business_pattern'] ?? $upt->business_pattern,
                ],
                'placement_year' => [
                    'label' => 'Tahun Penempatan',
                    'current' => $upt->placement_year,
                    'proposed' => $payload['placement_year'] ?? $upt->placement_year,
                ],
                'placement_kk' => [
                    'label' => 'KK Penempatan',
                    'current' => $upt->placement_kk,
                    'proposed' => $payload['placement_kk'] ?? $upt->placement_kk,
                ],
                'placement_population' => [
                    'label' => 'Jiwa penempatan',
                    'current' => $upt->placement_population,
                    'proposed' => $payload['placement_population'] ?? $upt->placement_population,
                ],
                'handover_year' => [
                    'label' => 'Tahun Serah Terima',
                    'current' => $upt->handover_year ?? '-',
                    'proposed' => $payload['handover_year'] ?? ($upt->handover_year ?? '-'),
                ],
                'handover_kk' => [
                    'label' => 'KK Serah Terima',
                    'current' => $upt->handover_kk,
                    'proposed' => $payload['handover_kk'] ?? $upt->handover_kk,
                ],
                'handover_population' => [
                    'label' => 'Jiwa serah terima',
                    'current' => $upt->handover_population,
                    'proposed' => $payload['handover_population'] ?? $upt->handover_population,
                ],
                'issue_status' => [
                    'label' => 'Status Legalitas Lahan',
                    'current' => $upt->issue_status,
                    'proposed' => $payload['issue_status'] ?? $upt->issue_status,
                ],
                'issue_note' => [
                    'label' => 'Catatan Permasalahan Lapangan',
                    'current' => $upt->issue_note ?? '(Belum ada catatan)',
                    'proposed' => $payload['issue_note'] ?? '(Belum ada catatan)',
                ],
                'shm_status' => [
                    'label' => 'Status Sertifikasi SHM',
                    'current' => $upt->shm_status ?? '-',
                    'proposed' => $payload['shm_status'] ?? ($upt->shm_status ?? '-'),
                ],
            ];

            foreach ($comparisonFields as $key => &$field) {
                $field['current'] = $upt->{$key};
                $field['proposed'] = array_key_exists($key, $payload) ? $payload[$key] : $upt->{$key};
            }
            unset($field);

            // Tambahkan baris status kawasan SKP
            $comparisonFields['skp_area'] = [
                'label' => 'Kawasan SKP Transmigrasi',
                'current' => $upt->skpArea ? ($upt->skpArea->skp_name . ' (' . $upt->skpArea->kawasan_name . ')') : 'Mandiri (Belum terkait SKP)',
                'proposed' => '(Dapat dikaitkan pada formulir keputusan di bawah)',
            ];
        }

        $registrySummary = null;
        $registrySampleCards = [];
        $registryTotalCards = 0;

        if ($changeRequest->request_type === 'REGISTRY_SYNC' && $upt) {
            $registrySummary = $payload['registry_summary'] ?? null;
            $stage = $payload['stage'] ?? 'placement';
            $cardsQuery = \App\Models\UptFamilyCard::where('upt_location_id', $upt->id)->where('stage', $stage);
            $registryTotalCards = $cardsQuery->count();
            $registrySampleCards = $cardsQuery->orderBy('housing_block')->orderBy('id')->take(8)->get();
        }

        $proposedRegency = null;
        if (!empty($payload['regency_id'])) {
            $proposedRegency = Regency::find($payload['regency_id']);
        }

        $skpAreas = SkpArea::with('regency')
            ->orderBy('regency_id')
            ->orderBy('skp_name')
            ->get();

        return view('admin.verifikasi_draf_usulan.show', compact(
            'changeRequest', 
            'upt', 
            'payload', 
            'comparisonFields',
            'registrySummary',
            'registrySampleCards',
            'registryTotalCards',
            'proposedRegency',
            'skpAreas'
        ));
    }

    /**
     * Setujui draf usulan (Approve) & terapkan perubahan langsung ke data master
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $changeRequest = UptChangeRequest::with('uptLocation')->findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Draf usulan ini telah diproses sebelumnya.');
        }

        $request->validate([
            'skp_id' => 'nullable|integer|exists:skp_areas,id',
            'reviewer_note' => 'nullable|string|max:1000',
        ]);

        $payload = $changeRequest->proposed_payload ?? [];
        $reviewer = auth()->user();
        $selectedSkpId = $request->filled('skp_id') ? (int) $request->skp_id : null;

        // Alur Khusus: Usulan Registrasi UPT Baru
        if ($changeRequest->request_type === 'CREATE_UPT') {
            DB::transaction(function () use ($changeRequest, $payload, $reviewer, $request, $selectedSkpId) {
                $suggestedNumber = isset($payload['upt_number']) && (int) $payload['upt_number'] > 0
                    ? (int) $payload['upt_number']
                    : ((UptLocation::max('upt_number') ?? 0) + 1);

                if (UptLocation::where('upt_number', $suggestedNumber)->exists()) {
                    $suggestedNumber = (UptLocation::max('upt_number') ?? 0) + 1;
                }

                $regModel = Regency::find((int) $payload['regency_id']);
                $uptLat = !empty($payload['latitude']) ? (float) $payload['latitude'] : ($regModel?->latitude ? (float) $regModel->latitude : null);
                $uptLng = !empty($payload['longitude']) ? (float) $payload['longitude'] : ($regModel?->longitude ? (float) $regModel->longitude : null);

                $newUptData = [
                    'regency_id' => (int) $payload['regency_id'],
                    'skp_id' => $selectedSkpId,
                    'upt_number' => $suggestedNumber,
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
                    'latitude' => $uptLat,
                    'longitude' => $uptLng,
                    'is_verified' => true,
                ];

                if (!empty($newUptData['latitude']) && !empty($newUptData['longitude'])) {
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
                }

                $newUpt = UptLocation::create($newUptData);

                if (!empty($newUptData['latitude']) && !empty($newUptData['longitude'])) {
                    try {
                        DB::statement("
                            UPDATE upt_locations 
                            SET coordinate_point = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                                polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(polygon_geojson::text)), 4326)
                            WHERE id = {$newUpt->id}
                        ");
                    } catch (\Throwable $e) {}
                }

                // Sinkronisasikan UPT ke Kawasan SKP & daftar desa binaan SKP
                $this->syncUptWithSkp($newUpt, $selectedSkpId);

                if (isset($payload['attached_document'])) {
                    $doc = $payload['attached_document'];
                    UptDocument::create([
                        'upt_location_id' => $newUpt->id,
                        'document_type' => 'BAST',
                        'document_number' => $doc['document_number'] ?? 'SK-NEW-UPT-' . date('Ymd'),
                        'file_name' => $doc['file_name'] ?? 'Berkas SK Pembentukan UPT Baru',
                        'file_path' => $doc['file_path'],
                        'file_size_kb' => isset($doc['file_size_bytes']) ? (int) round($doc['file_size_bytes'] / 1024) : 0,
                        'uploaded_by' => $changeRequest->user_id,
                    ]);
                }

                $changeRequest->update([
                    'upt_location_id' => $newUpt->id,
                    'status' => 'approved',
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => now(),
                    'reviewer_note' => $request->reviewer_note ?? 'Usulan registrasi UPT baru telah disetujui dan didaftarkan resmi ke master data.',
                ]);

                AuditLog::log('APPROVE_CREATE_UPT', 'upt_locations', $newUpt->id, [
                    'change_request_id' => $changeRequest->id,
                    'upt_number' => $newUpt->upt_number,
                    'upt_name' => $newUpt->upt_name,
                    'reviewer' => $reviewer->name,
                    'skp_id' => $selectedSkpId,
                    'data' => $newUptData,
                ]);
            });

            return redirect()->route('admin.verification.index')
                ->with('success', "Usulan registrasi UPT baru \"{$payload['upt_name']}\" berhasil DISETUJUI dan resmi didaftarkan ke Master Data UPT!");
        }

        // Alur Pemutakhiran UPT Eksisting
        $upt = $changeRequest->uptLocation;

        DB::transaction(function () use ($changeRequest, $upt, $payload, $reviewer, $request, $selectedSkpId) {
            // Snapshot data lama untuk audit log forensik
            $oldData = [
                'current_village_name' => $upt->current_village_name,
                'business_pattern' => $upt->business_pattern,
                'placement_year' => $upt->placement_year,
                'placement_kk' => $upt->placement_kk,
                'handover_year' => $upt->handover_year,
                'handover_kk' => $upt->handover_kk,
                'issue_status' => $upt->issue_status,
                'issue_note' => $upt->issue_note,
                'shm_status' => $upt->shm_status,
                'skp_id' => $upt->skp_id,
            ];

            // Update data master UPT
            $updateData = [];
            foreach (['current_village_name', 'business_pattern', 'placement_year', 'placement_kk', 'placement_population', 'handover_year', 'handover_kk', 'handover_population', 'issue_status', 'issue_note', 'shm_status'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $updateData[$field] = $payload[$field];
                }
            }

            if ($changeRequest->request_type === 'REGISTRY_SYNC') {
                $updateData['is_verified'] = true;
            }

            if (! empty($updateData)) {
                $upt->update($updateData);
            }

            // Sinkronisasikan keterkaitan Kawasan SKP
            $this->syncUptWithSkp($upt, $selectedSkpId);
            $updateData['skp_id'] = $selectedSkpId;

            // Jika terdapat lampiran dokumen BAST dalam payload, buat record di upt_documents
            if (isset($payload['attached_document'])) {
                $doc = $payload['attached_document'];
                UptDocument::create([
                    'upt_location_id' => $upt->id,
                    'document_type' => 'BAST',
                    'document_number' => $doc['document_number'] ?? 'BAST-VERIFIED-' . date('Ymd'),
                    'file_name' => $doc['file_name'] ?? 'Berkas BAST',
                    'file_path' => $doc['file_path'],
                    'file_size_kb' => isset($doc['file_size_bytes']) ? (int) round($doc['file_size_bytes'] / 1024) : 0,
                    'uploaded_by' => $changeRequest->user_id,
                ]);
            }

            // Update status change request
            $changeRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reviewer_note' => $request->reviewer_note ?? 'Disetujui dan telah disinkronkan ke basis data master transmigrasi.',
            ]);

            // Catat ke audit log forensik
            AuditLog::log('APPROVE_CHANGE_REQUEST', 'upt_locations', $upt->id, [
                'change_request_id' => $changeRequest->id,
                'upt_name' => $upt->upt_name,
                'reviewer' => $reviewer->name,
                'skp_id' => $selectedSkpId,
                'before' => $oldData,
                'after' => $updateData,
            ]);
        });

        return redirect()->route('admin.verification.index')
            ->with('success', "Draf usulan untuk UPT {$upt->upt_name} berhasil DISETUJUI. Data master otomatis diperbarui!");
    }

    /**
     * Tolak draf usulan (Reject) dengan catatan revisi
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reviewer_note' => 'required|string|min:5|max:1000',
        ], [
            'reviewer_note.required' => 'Catatan alasan penolakan wajib diisi untuk panduan revisi operator.',
            'reviewer_note.min' => 'Catatan penolakan minimal berisi 5 karakter.',
        ]);

        $changeRequest = UptChangeRequest::with('uptLocation')->findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Draf usulan ini telah diproses sebelumnya.');
        }

        $reviewer = auth()->user();

        $changeRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'reviewer_note' => $request->reviewer_note,
        ]);

        $targetName = $changeRequest->uptLocation?->upt_name ?? ($changeRequest->proposed_payload['upt_name'] ?? 'Usulan UPT Baru');

        // Catat ke audit log
        AuditLog::log('REJECT_CHANGE_REQUEST', 'upt_change_requests', $changeRequest->id, [
            'upt_name' => $targetName,
            'reviewer' => $reviewer->name,
            'rejection_reason' => $request->reviewer_note,
        ]);

        return redirect()->route('admin.verification.index')
            ->with('success', "Draf usulan untuk \"{$targetName}\" telah DITOLAK dengan catatan evaluasi.");
    }

    /**
     * Sinkronisasikan UPT dan desa definitif ke dalam Kawasan SKP
     */
    private function syncUptWithSkp(UptLocation $upt, ?int $skpId): void
    {
        $upt->skp_id = $skpId;
        $upt->save();

        if ($skpId) {
            $skpArea = SkpArea::find($skpId);
            if (! $skpArea) {
                return;
            }

            $upt->loadMissing('regency');
            $villageName = trim($upt->current_village_name ?: $upt->upt_name);
            $pop = (int) ($upt->handover_population ?: $upt->placement_population ?: 0);
            $male = (int) round($pop / 2);
            $female = $pop - $male;
            $kk = (int) ($upt->handover_kk ?: $upt->placement_kk ?: 0);
            $districtName = $upt->regency ? 'Kec. Wilayah ' . $upt->regency->name : 'Kecamatan Terkait';

            // Cari apakah sudah ada data desa binaan terkait UPT ini
            $existingVillage = SkpVillage::where('upt_location_id', $upt->id)->first();

            if (! $existingVillage) {
                // Cari berdasarkan nama desa pada SKP sasaran
                $existingVillage = SkpVillage::where('skp_area_id', $skpId)
                    ->where('village_name', $villageName)
                    ->first();
            }

            if ($existingVillage) {
                $existingVillage->skp_area_id = $skpId;
                $existingVillage->upt_location_id = $upt->id;
                $existingVillage->village_name = $villageName;
                if ($existingVillage->family_count == 0 && $kk > 0) {
                    $existingVillage->family_count = $kk;
                }
                if ($existingVillage->population_total == 0 && $pop > 0) {
                    $existingVillage->population_male = $male;
                    $existingVillage->population_female = $female;
                    $existingVillage->population_total = $pop;
                }
                $existingVillage->save();
            } else {
                SkpVillage::create([
                    'skp_area_id' => $skpId,
                    'upt_location_id' => $upt->id,
                    'district_name' => $districtName,
                    'village_name' => $villageName,
                    'area_km2' => 0,
                    'population_male' => $male,
                    'population_female' => $female,
                    'population_total' => $pop,
                    'family_count' => $kk,
                    'notes' => 'Diintegrasikan otomatis via Verifikasi UPT ' . $upt->upt_name,
                ]);
            }
        } else {
            // Jika dilepas dari SKP (mandiri), putuskan tautan di skp_villages
            SkpVillage::where('upt_location_id', $upt->id)->update([
                'upt_location_id' => null,
            ]);
        }
    }
}
