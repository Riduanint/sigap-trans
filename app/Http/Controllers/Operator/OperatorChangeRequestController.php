<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UptChangeRequest;
use App\Models\UptLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OperatorChangeRequestController extends Controller
{
    /**
     * Tampilkan daftar seluruh draf usulan operator
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = UptChangeRequest::with(['uptLocation.regency', 'reviewer'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        return view('operator.requests.index', compact('requests'));
    }

    /**
     * Tampilkan formulir pengajuan draf usulan baru
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $regencyId = $user->regency_id ?? 1;

        // Hanya UPT dalam kabupaten operator bersangkutan
        $uptLocations = UptLocation::where('regency_id', $regencyId)
            ->orderBy('upt_number')
            ->get();

        $selectedUpt = null;
        if ($request->filled('upt_id')) {
            $selectedUpt = $uptLocations->firstWhere('id', (int) $request->upt_id);
        }

        return view('operator.requests.create', compact('uptLocations', 'selectedUpt'));
    }

    /**
     * Simpan draf usulan pemutakhiran data ke basis data
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $regencyId = $user->regency_id ?? 1;

        $validated = $request->validate([
            'upt_location_id' => 'required|exists:upt_locations,id',
            'request_type' => 'required|in:DATA_UPDATE,LEGAL_ISSUE,NEW_BAST',
            'submission_note' => 'required|string|max:1000',
            
            // Kolom yang dapat diusulkan
            'current_village_name' => 'nullable|string|max:150',
            'business_pattern' => 'nullable|string|max:50',
            'placement_year' => 'nullable|string|max:20',
            'placement_kk' => 'nullable|integer|min:0',
            'placement_population' => 'nullable|integer|min:0',
            'handover_year' => 'nullable|string|max:20',
            'handover_kk' => 'nullable|integer|min:0',
            'handover_population' => 'nullable|integer|min:0',
            'issue_status' => 'nullable|in:clean,warning,critical',
            'issue_note' => 'nullable|string',
            'shm_status' => 'nullable|string|max:100',
            
            // Jika melampirkan berkas BAST/SK
            'document_file' => 'nullable|file|mimes:pdf|max:20480',
            'document_number' => 'nullable|string|max:100',
            'document_name' => 'nullable|string|max:255',
        ]);

        // Verifikasi UPT benar berada di bawah wewenang kabupaten operator
        $upt = UptLocation::findOrFail($validated['upt_location_id']);
        if ($upt->regency_id != $regencyId && ! $user->isSuperAdmin()) {
            abort(403, 'Anda hanya berwenang mengajukan perubahan untuk UPT di wilayah kabupaten Anda.');
        }

        // Susun payload usulan (perubahan yang diajukan)
        $proposedPayload = [
            'submission_note' => $validated['submission_note'],
            'current_village_name' => $validated['current_village_name'] ?? $upt->current_village_name,
            'business_pattern' => $validated['business_pattern'] ?? $upt->business_pattern,
            'placement_year' => $validated['placement_year'] ?? $upt->placement_year,
            'placement_kk' => isset($validated['placement_kk']) ? (int) $validated['placement_kk'] : $upt->placement_kk,
            'placement_population' => isset($validated['placement_population']) ? (int) $validated['placement_population'] : $upt->placement_population,
            'handover_year' => $validated['handover_year'] ?? $upt->handover_year,
            'handover_kk' => isset($validated['handover_kk']) ? (int) $validated['handover_kk'] : $upt->handover_kk,
            'handover_population' => isset($validated['handover_population']) ? (int) $validated['handover_population'] : $upt->handover_population,
            'issue_status' => $validated['issue_status'] ?? $upt->issue_status,
            'issue_note' => $validated['issue_note'] ?? $upt->issue_note,
            'shm_status' => $validated['shm_status'] ?? $upt->shm_status,
        ];

        // Upload berkas pendukung jika ada
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = 'draft_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('documents/proposals', $fileName, 'public');

            $proposedPayload['attached_document'] = [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'document_number' => $request->document_number ?? 'DRAFT-' . date('YmdHis'),
                'document_name' => $request->document_name ?? 'Berkas Usulan BAST',
                'file_size_bytes' => $file->getSize(),
            ];
        }

        // Buat record change request
        $changeRequest = UptChangeRequest::create([
            'upt_location_id' => $upt->id,
            'user_id' => $user->id,
            'request_type' => $validated['request_type'],
            'proposed_payload' => $proposedPayload,
            'status' => 'pending',
        ]);

        // Catat jejak audit forensik
        AuditLog::log('SUBMIT_CHANGE_REQUEST', 'upt_change_requests', $changeRequest->id, [
            'upt_number' => $upt->upt_number,
            'upt_name' => $upt->upt_name,
            'request_type' => $validated['request_type'],
            'operator_name' => $user->name,
        ]);

        return redirect()->route('operator.requests.index')->with('success', "Draf usulan pemutakhiran untuk UPT {$upt->upt_name} berhasil dikirim ke antrean verifikasi Provinsi!");
    }

    /**
     * Tampilkan detail draf usulan
     */
    public function show(int $id): View
    {
        $user = auth()->user();
        $changeRequest = UptChangeRequest::with(['uptLocation.regency', 'user', 'reviewer'])
            ->findOrFail($id);

        if ($changeRequest->user_id !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses melihat usulan ini.');
        }

        return view('operator.requests.show', compact('changeRequest'));
    }
}
