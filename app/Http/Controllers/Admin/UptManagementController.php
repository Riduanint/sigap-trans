<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UptManagementController extends Controller
{
    /**
     * Tampilkan daftar master 124 UPT dengan filter dan pencarian
     */
    public function index(Request $request): View
    {
        $query = UptLocation::with(['regency', 'documents']);

        // Filter Kabupaten
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('issue_status', $request->status);
        }

        // Filter Pola Usaha
        if ($request->filled('pattern') && $request->pattern !== 'all') {
            $query->where('business_pattern', $request->pattern);
        }

        // Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            // Cek apakah input murni nomor UPT (contoh: "14", "014", "UPT-014", "upt 14")
            $uptNumber = null;
            if (preg_match('/^(?:upt[-\s]*)?0*([1-9]\d*)$/i', $search, $matchNum)) {
                $uptNumber = (int)$matchNum[1];
            }

            $query->where(function ($q) use ($search, $uptNumber) {
                $q->where('upt_name', 'ILIKE', "%{$search}%")
                  ->orWhere('current_village_name', 'ILIKE', "%{$search}%")
                  ->orWhereHas('regency', function ($rq) use ($search) {
                      $rq->where('name', 'ILIKE', "%{$search}%");
                  });
                if ($uptNumber !== null) {
                    $q->orWhere('upt_number', $uptNumber);
                }
            });
        }

        $perPage = $request->input('per_page', 15);
        if ($perPage === 'all' || (int)$perPage >= 124) {
            $perPage = 124;
        } else {
            $perPage = (int)$perPage;
            if ($perPage < 1) {
                $perPage = 15;
            }
        }

        $uptLocations = $query->orderBy('upt_number')->paginate($perPage)->onEachSide(1)->withQueryString();
        $regencies = Regency::withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        $stats = [
            'total' => UptLocation::count(),
            'clean' => UptLocation::where('issue_status', 'clean')->count(),
            'warning' => UptLocation::where('issue_status', 'warning')->count(),
            'critical' => UptLocation::where('issue_status', 'critical')->count(),
        ];

        return view('admin.upt.upt', compact('uptLocations', 'regencies', 'stats'));
    }

    /**
     * Tampilkan detail komprehensif 1 UPT
     */
    public function show(int $id): View
    {
        $upt = UptLocation::with(['regency', 'documents', 'changeRequests'])->findOrFail($id);
        return view('admin.upt.show', compact('upt'));
    }

    /**
     * Form edit data UPT
     */
    public function edit(int $id): View
    {
        $upt = UptLocation::with('regency')->findOrFail($id);
        $regencies = Regency::orderBy('id')->get();
        return view('admin.upt.edit', compact('upt', 'regencies'));
    }

    /**
     * Simpan pembaruan data UPT
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $upt = UptLocation::findOrFail($id);

        $validated = $request->validate([
            'current_village_name' => ['required', 'string', 'max:150'],
            'business_pattern' => ['required', 'string', 'max:50'],
            'issue_status' => ['required', 'in:clean,warning,critical'],
            'issue_note' => ['nullable', 'string'],
            'shm_status' => ['nullable', 'string', 'max:100'],
            'placement_year' => ['required', 'string', 'max:20'],
            'placement_kk' => ['required', 'integer', 'min:0'],
            'placement_population' => ['required', 'integer', 'min:0'],
            'handover_year' => ['nullable', 'string', 'max:20'],
            'handover_kk' => ['required', 'integer', 'min:0'],
            'handover_population' => ['required', 'integer', 'min:0'],
        ]);

        $oldData = $upt->only([
            'current_village_name', 'business_pattern', 'issue_status', 
            'issue_note', 'shm_status', 'placement_kk', 'handover_kk'
        ]);

        $upt->update($validated);

        // Rekam Audit Log Forensik
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_UPT_LOCATION',
                'target_table' => 'upt_locations',
                'target_id' => (string) $upt->id,
                'details' => [
                    'upt_number' => $upt->upt_number,
                    'upt_name' => $upt->upt_name,
                    'before' => $oldData,
                    'after' => $validated,
                ],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }

        return redirect()->route('admin.upt.index')
            ->with('success', "Data UPT No. {$upt->upt_number} ({$upt->upt_name}) berhasil dimutakhirkan.");
    }
}
