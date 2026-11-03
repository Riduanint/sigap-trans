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

        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $regencyId = (int) $request->regency_id;
            $query->where(function ($q) use ($regencyId) {
                $q->whereHas('uptLocation', function ($sub) use ($regencyId) {
                    $sub->where('regency_id', $regencyId);
                })->orWhereRaw("(proposed_payload->>'regency_id')::int = ?", [$regencyId]);
            });
        }

        $requests = $query->latest()->paginate(10)->withQueryString();
        $regencies = \App\Models\Regency::where('is_visible', true)->orderBy('name')->get();

        return view('operator.draf_usulan.draf_usulan', compact('requests', 'regencies'));
    }

    /**
     * Tampilkan formulir pengajuan draf pemutakhiran data UPT terdaftar
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->get('mode') === 'new_upt') {
            return redirect()->route('operator.requests.create-new-upt');
        }

        $user = auth()->user();
        $regencies = \App\Models\Regency::where('is_visible', true)->orderBy('name')->get();

        // Hanya UPT di bawah kabupaten aktif
        $uptLocations = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))
            ->with('regency')
            ->orderBy('regency_id')
            ->orderBy('upt_number')
            ->get();

        $selectedUpt = null;
        if ($request->filled('upt_id')) {
            $selectedUpt = $uptLocations->firstWhere('id', (int) $request->upt_id);
        }

        return view('operator.draf_usulan.create-update', compact('uptLocations', 'selectedUpt', 'regencies'));
    }

    /**
     * Tampilkan formulir pengajuan registrasi UPT baru
     */
    public function createNewUpt(Request $request): View
    {
        $regencies = \App\Models\Regency::where('is_visible', true)->orderBy('name')->get();
        $nextUptNumber = (UptLocation::max('upt_number') ?? 0) + 1;

        return view('operator.draf_usulan.create-new-upt', compact('regencies', 'nextUptNumber'));
    }

    /**
     * Simpan draf usulan pemutakhiran data atau registrasi UPT baru ke basis data
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $requestType = $request->input('request_type');

        // 1. Alur Pengajuan Registrasi UPT Baru
        if ($requestType === 'CREATE_UPT') {
            $validated = $request->validate([
                'request_type' => 'required|in:CREATE_UPT',
                'regency_id' => 'required|exists:regencies,id',
                'upt_name' => 'required|string|max:150',
                'current_village_name' => 'required|string|max:150',
                'business_pattern' => 'required|string|max:50',
                'submission_note' => 'required|string|max:1000',
                'upt_number' => 'nullable|integer|min:1',
                'placement_year' => 'nullable|string|max:20',
                'placement_kk' => 'nullable|integer|min:0',
                'placement_population' => 'nullable|integer|min:0',
                'handover_year' => 'nullable|string|max:20',
                'handover_kk' => 'nullable|integer|min:0',
                'handover_population' => 'nullable|integer|min:0',
                'issue_status' => 'nullable|in:clean,warning,critical',
                'issue_note' => 'nullable|string',
                'shm_status' => 'nullable|string|max:100',
                'coordinates' => 'nullable|string|max:100',
                'latitude' => 'nullable|numeric|between:-5.5,-1.0',
                'longitude' => 'nullable|numeric|between:114.0,117.5',
                'document_file' => 'nullable|file|mimes:pdf|max:20480',
                'document_number' => 'nullable|string|max:100',
                'document_name' => 'nullable|string|max:255',
            ], [
                'latitude.between' => 'Titik lintang (latitude) harus berada di wilayah Kalimantan Selatan (antara -5.5 s/d -1.0 desimal).',
                'longitude.between' => 'Titik bujur (longitude) harus berada di wilayah Kalimantan Selatan (antara 114.0 s/d 117.5 desimal).',
            ]);

            $regency = \App\Models\Regency::where('is_visible', true)->findOrFail($validated['regency_id']);
            $suggestedNumber = !empty($validated['upt_number']) ? (int) $validated['upt_number'] : ((UptLocation::max('upt_number') ?? 0) + 1);

            $parsedCoords = null;
            if ($request->filled('coordinates')) {
                $parsedCoords = \App\Support\CoordinateParser::parse($request->coordinates);
                if (!$parsedCoords) {
                    return back()->withInput()->withErrors([
                        'coordinates' => 'Format koordinat tidak dikenali. Gunakan contoh: 2°25\'59.9"S 115°40\'49.2"E atau -2.433306, 115.680333',
                    ]);
                }
                if (!\App\Support\CoordinateParser::isWithinKalsel($parsedCoords['latitude'], $parsedCoords['longitude'])) {
                    return back()->withInput()->withErrors([
                        'coordinates' => 'Titik koordinat harus berada di wilayah Kalimantan Selatan (antara -5.5 s/d -1.0 Lintang, 114.0 s/d 117.5 Bujur).',
                    ]);
                }
            }

            if ($parsedCoords) {
                $lat = $parsedCoords['latitude'];
                $lng = $parsedCoords['longitude'];
            } else {
                $lat = !empty($validated['latitude']) ? (float) $validated['latitude'] : ($regency->latitude ? (float) $regency->latitude : null);
                $lng = !empty($validated['longitude']) ? (float) $validated['longitude'] : ($regency->longitude ? (float) $regency->longitude : null);
            }

            $proposedPayload = [
                'regency_id' => (int) $validated['regency_id'],
                'regency_name' => $regency->name,
                'upt_number' => $suggestedNumber,
                'upt_name' => trim($validated['upt_name']),
                'current_village_name' => trim($validated['current_village_name']),
                'business_pattern' => trim($validated['business_pattern']),
                'submission_note' => trim($validated['submission_note']),
                'placement_year' => $validated['placement_year'] ?? null,
                'placement_kk' => isset($validated['placement_kk']) ? (int) $validated['placement_kk'] : 0,
                'placement_population' => isset($validated['placement_population']) ? (int) $validated['placement_population'] : 0,
                'handover_year' => $validated['handover_year'] ?? null,
                'handover_kk' => isset($validated['handover_kk']) ? (int) $validated['handover_kk'] : 0,
                'handover_population' => isset($validated['handover_population']) ? (int) $validated['handover_population'] : 0,
                'issue_status' => $validated['issue_status'] ?? 'clean',
                'issue_note' => $validated['issue_note'] ?? null,
                'shm_status' => $validated['shm_status'] ?? 'Belum SHM',
                'latitude' => $lat,
                'longitude' => $lng,
                'coordinates_dms' => ($lat && $lng) ? \App\Support\CoordinateParser::toDms($lat, $lng) : null,
            ];

            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $fileName = 'draft_new_upt_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('documents/proposals', $fileName, 'public');

                $proposedPayload['attached_document'] = [
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'document_number' => $request->document_number ?? 'SK-NEW-UPT-' . date('YmdHis'),
                    'document_name' => $request->document_name ?? 'Berkas SK Pembentukan UPT Baru',
                    'file_size_bytes' => $file->getSize(),
                ];
            }

            $changeRequest = UptChangeRequest::create([
                'upt_location_id' => null,
                'user_id' => $user->id,
                'request_type' => 'CREATE_UPT',
                'proposed_payload' => $proposedPayload,
                'status' => 'pending',
            ]);

            AuditLog::log('SUBMIT_NEW_UPT_REQUEST', 'upt_change_requests', $changeRequest->id, [
                'upt_name' => $proposedPayload['upt_name'],
                'regency_name' => $regency->name,
                'request_type' => 'CREATE_UPT',
                'operator_name' => $user->name,
            ]);

            return redirect()->route('operator.requests.index')
                ->with('success', "Usulan registrasi UPT baru \"{$proposedPayload['upt_name']}\" berhasil dikirim ke antrean verifikasi Provinsi!");
        }

        // 2. Alur Pemutakhiran UPT Eksisting
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

        // Muat data UPT yang diajukan (hanya wilayah aktif)
        $upt = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))->findOrFail($validated['upt_location_id']);

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

        return view('operator.draf_usulan.show', compact('changeRequest'));
    }

    /**
     * Tampilkan formulir edit draf usulan
     */
    public function edit(int $id): View|RedirectResponse
    {
        $user = auth()->user();
        $changeRequest = UptChangeRequest::with(['uptLocation.regency', 'user'])
            ->findOrFail($id);

        if ($changeRequest->user_id !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses mengubah usulan ini.');
        }

        $regencies = \App\Models\Regency::where('is_visible', true)->orderBy('name')->get();
        $payload = $changeRequest->proposed_payload ?? [];

        if ($changeRequest->request_type === 'CREATE_UPT') {
            return view('operator.draf_usulan.edit-new-upt', compact('changeRequest', 'payload', 'regencies'));
        }

        $uptLocations = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))
            ->with('regency')
            ->orderBy('regency_id')
            ->orderBy('upt_number')
            ->get();
        $selectedUpt = $changeRequest->uptLocation;

        return view('operator.draf_usulan.edit-update', compact('changeRequest', 'payload', 'uptLocations', 'selectedUpt', 'regencies'));
    }

    /**
     * Perbarui data draf usulan
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $changeRequest = UptChangeRequest::with('uptLocation')->findOrFail($id);

        if ($changeRequest->user_id !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses mengubah usulan ini.');
        }

        if ($changeRequest->request_type === 'CREATE_UPT') {
            $validated = $request->validate([
                'regency_id' => 'required|exists:regencies,id',
                'upt_name' => 'required|string|max:150',
                'current_village_name' => 'required|string|max:150',
                'business_pattern' => 'required|string|max:50',
                'submission_note' => 'required|string|max:1000',
                'upt_number' => 'nullable|integer|min:1',
                'shm_status' => 'nullable|string|max:100',
                'coordinates' => 'nullable|string|max:100',
                'latitude' => 'nullable|numeric|between:-5.5,-1.0',
                'longitude' => 'nullable|numeric|between:114.0,117.5',
            ], [
                'latitude.between' => 'Titik lintang (latitude) harus berada di wilayah Kalimantan Selatan (antara -5.5 s/d -1.0 desimal).',
                'longitude.between' => 'Titik bujur (longitude) harus berada di wilayah Kalimantan Selatan (antara 114.0 s/d 117.5 desimal).',
            ]);

            $regency = \App\Models\Regency::where('is_visible', true)->findOrFail($validated['regency_id']);
            $parsedCoords = null;
            if ($request->filled('coordinates')) {
                $parsedCoords = \App\Support\CoordinateParser::parse($request->coordinates);
                if (!$parsedCoords || !\App\Support\CoordinateParser::isWithinKalsel($parsedCoords['latitude'], $parsedCoords['longitude'])) {
                    return back()->withInput()->withErrors([
                        'coordinates' => 'Titik koordinat harus berada di wilayah Kalimantan Selatan (-5.5 s/d -1.0 Lat, 114.0 s/d 117.5 Lng).',
                    ]);
                }
            }

            if ($parsedCoords) {
                $lat = $parsedCoords['latitude'];
                $lng = $parsedCoords['longitude'];
            } else {
                $lat = !empty($validated['latitude']) ? (float) $validated['latitude'] : ($regency->latitude ? (float) $regency->latitude : null);
                $lng = !empty($validated['longitude']) ? (float) $validated['longitude'] : ($regency->longitude ? (float) $regency->longitude : null);
            }

            $payload = $changeRequest->proposed_payload ?? [];
            $payload['regency_id'] = (int) $validated['regency_id'];
            $payload['regency_name'] = $regency->name;
            if (!empty($validated['upt_number'])) {
                $payload['upt_number'] = (int) $validated['upt_number'];
            }
            $payload['upt_name'] = trim($validated['upt_name']);
            $payload['current_village_name'] = trim($validated['current_village_name']);
            $payload['business_pattern'] = trim($validated['business_pattern']);
            $payload['submission_note'] = trim($validated['submission_note']);
            $payload['shm_status'] = $validated['shm_status'] ?? 'Belum SHM';
            $payload['latitude'] = $lat;
            $payload['longitude'] = $lng;
            $payload['coordinates_dms'] = ($lat && $lng) ? \App\Support\CoordinateParser::toDms($lat, $lng) : null;

            $changeRequest->update([
                'proposed_payload' => $payload,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'reviewer_note' => null,
            ]);

            AuditLog::log('UPDATE_CHANGE_REQUEST', 'upt_change_requests', $changeRequest->id, [
                'upt_name' => $payload['upt_name'],
                'request_type' => 'CREATE_UPT',
                'operator_name' => $user->name,
            ]);

            return redirect()->route('operator.requests.index')->with('success', "Draf pendaftaran UPT '{$payload['upt_name']}' berhasil diperbarui.");
        }

        // Mode Pemutakhiran UPT Terdaftar
        $validated = $request->validate([
            'request_type' => 'required|in:DATA_UPDATE,LEGAL_ISSUE,NEW_BAST',
            'submission_note' => 'required|string|max:1000',
            'current_village_name' => 'nullable|string|max:150',
            'business_pattern' => 'nullable|string|max:50',
            'placement_year' => 'nullable|string|max:20',
            'placement_kk' => 'nullable|integer|min:0',
            'placement_population' => 'nullable|integer|min:0',
            'handover_year' => 'nullable|string|max:20',
            'handover_kk' => 'nullable|integer|min:0',
            'handover_population' => 'nullable|integer|min:0',
            'issue_status' => 'required|in:clean,warning,critical',
            'issue_note' => 'nullable|string',
            'shm_status' => 'nullable|string|max:100',
            'document_file' => 'nullable|file|mimes:pdf|max:20480',
            'document_number' => 'nullable|string|max:100',
            'document_name' => 'nullable|string|max:255',
        ]);

        $upt = $changeRequest->uptLocation;
        $proposedPayload = $changeRequest->proposed_payload ?? [];
        $proposedPayload['submission_note'] = trim($validated['submission_note']);
        $proposedPayload['current_village_name'] = !empty($validated['current_village_name']) ? trim($validated['current_village_name']) : ($upt?->current_village_name);
        $proposedPayload['business_pattern'] = !empty($validated['business_pattern']) ? trim($validated['business_pattern']) : ($upt?->business_pattern);
        $proposedPayload['placement_year'] = $validated['placement_year'] ?? ($upt?->placement_year);
        $proposedPayload['placement_kk'] = isset($validated['placement_kk']) ? (int) $validated['placement_kk'] : ($upt?->placement_kk ?? 0);
        $proposedPayload['placement_population'] = isset($validated['placement_population']) ? (int) $validated['placement_population'] : ($upt?->placement_population ?? 0);
        $proposedPayload['handover_year'] = $validated['handover_year'] ?? ($upt?->handover_year);
        $proposedPayload['handover_kk'] = isset($validated['handover_kk']) ? (int) $validated['handover_kk'] : ($upt?->handover_kk ?? 0);
        $proposedPayload['handover_population'] = isset($validated['handover_population']) ? (int) $validated['handover_population'] : ($upt?->handover_population ?? 0);
        $proposedPayload['issue_status'] = $validated['issue_status'] ?? ($upt?->issue_status ?? 'clean');
        $proposedPayload['issue_note'] = $validated['issue_note'] ?? ($upt?->issue_note);
        $proposedPayload['shm_status'] = $validated['shm_status'] ?? ($upt?->shm_status ?? 'Belum SHM');

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

        $changeRequest->update([
            'request_type' => $validated['request_type'],
            'proposed_payload' => $proposedPayload,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'reviewer_note' => null,
        ]);

        AuditLog::log('UPDATE_CHANGE_REQUEST', 'upt_change_requests', $changeRequest->id, [
            'upt_name' => $upt?->upt_name ?? 'UPT',
            'request_type' => $validated['request_type'],
            'operator_name' => $user->name,
        ]);

        return redirect()->route('operator.requests.index')->with('success', 'Draf usulan pemutakhiran berhasil diperbarui.');
    }

    /**
     * Hapus draf usulan
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $changeRequest = UptChangeRequest::findOrFail($id);

        if ($changeRequest->user_id !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses menghapus usulan ini.');
        }

        // Hapus file dokumen pendukung fisik jika ada
        if (isset($changeRequest->proposed_payload['attached_document']['file_path'])) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($changeRequest->proposed_payload['attached_document']['file_path']);
        }

        $reqType = $changeRequest->request_type;
        $changeRequest->delete();

        AuditLog::log('DELETE_CHANGE_REQUEST', 'upt_change_requests', $id, [
            'request_type' => $reqType,
            'operator_name' => $user->name,
        ]);

        return redirect()->route('operator.requests.index')
            ->with('success', 'Draf usulan berhasil dihapus.');
    }
}
