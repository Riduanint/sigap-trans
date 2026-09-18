<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('regency_id')) {
            $query->whereHas('uptLocation', function ($q) use ($request) {
                $q->where('regency_id', $request->regency_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('uptLocation', function ($q) use ($search) {
                $q->where('upt_name', 'ilike', "%{$search}%")
                  ->orWhere('current_village_name', 'ilike', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        // Hitungan status untuk tab lencana
        $counts = [
            'all' => UptChangeRequest::count(),
            'pending' => UptChangeRequest::where('status', 'pending')->count(),
            'approved' => UptChangeRequest::where('status', 'approved')->count(),
            'rejected' => UptChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.verification.verification', compact('requests', 'regencies', 'counts'));
    }

    /**
     * Tampilkan antarmuka Diff Checker komparasi data aktif vs data usulan
     */
    public function show(int $id): View
    {
        $changeRequest = UptChangeRequest::with(['uptLocation.regency', 'user', 'reviewer'])
            ->findOrFail($id);

        $upt = $changeRequest->uptLocation;
        $payload = $changeRequest->proposed_payload ?? [];

        // Definisi field komparasi
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

        return view('admin.verification.show', compact('changeRequest', 'upt', 'payload', 'comparisonFields'));
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

        $upt = $changeRequest->uptLocation;
        $payload = $changeRequest->proposed_payload ?? [];
        $reviewer = auth()->user();

        DB::transaction(function () use ($changeRequest, $upt, $payload, $reviewer, $request) {
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
            ];

            // Update data master UPT
            $updateData = [];
            foreach (['current_village_name', 'business_pattern', 'placement_year', 'placement_kk', 'placement_population', 'handover_year', 'handover_kk', 'handover_population', 'issue_status', 'issue_note', 'shm_status'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $updateData[$field] = $payload[$field];
                }
            }

            if (! empty($updateData)) {
                $upt->update($updateData);
            }

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

        // Catat ke audit log
        AuditLog::log('REJECT_CHANGE_REQUEST', 'upt_change_requests', $changeRequest->id, [
            'upt_name' => $changeRequest->uptLocation->upt_name,
            'reviewer' => $reviewer->name,
            'rejection_reason' => $request->reviewer_note,
        ]);

        return redirect()->route('admin.verification.index')
            ->with('success', "Draf usulan untuk UPT {$changeRequest->uptLocation->upt_name} telah DITOLAK dengan catatan evaluasi.");
    }
}
