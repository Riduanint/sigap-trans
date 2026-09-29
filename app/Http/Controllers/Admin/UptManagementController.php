<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $uptLocations = $query->orderBy('upt_number')->paginate(10)->onEachSide(1)->withQueryString();
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

        return view('admin.master_data_upt.master_data_upt', compact('uptLocations', 'regencies', 'stats'));
    }

    /**
     * Form tambah data UPT baru
     */
    public function create(): View
    {
        $regencies = Regency::orderBy('id')->get();
        $nextUptNumber = (UptLocation::max('upt_number') ?? 0) + 1;
        return view('admin.master_data_upt.create', compact('regencies', 'nextUptNumber'));
    }

    /**
     * Simpan data UPT baru ke database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'regency_id' => ['required', 'exists:regencies,id'],
            'upt_number' => ['required', 'integer', 'min:1', 'unique:upt_locations,upt_number'],
            'upt_name' => ['required', 'string', 'max:150'],
            'current_village_name' => ['required', 'string', 'max:150'],
            'business_pattern' => ['required', 'string', 'max:50'],
            'placement_year' => ['required', 'string', 'max:20'],
            'placement_kk' => ['required', 'integer', 'min:0'],
            'placement_population' => ['required', 'integer', 'min:0'],
            'handover_year' => ['nullable', 'string', 'max:20'],
            'handover_kk' => ['nullable', 'integer', 'min:0'],
            'handover_population' => ['nullable', 'integer', 'min:0'],
            'issue_status' => ['required', 'in:clean,warning,critical'],
            'issue_note' => ['nullable', 'string'],
            'shm_status' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $validated['handover_kk'] = $validated['handover_kk'] ?? 0;
        $validated['handover_population'] = $validated['handover_population'] ?? 0;
        $validated['is_verified'] = true;

        // Jika koordinat diisi, buat buffer poligon default (~800m) jika tidak disediakan
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $delta = 0.008; // ~800 meter
            $validated['polygon_geojson'] = [
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

        $upt = UptLocation::create($validated);

        // Update geometri PostGIS jika kolom coordinate_point tersedia
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            try {
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE upt_locations 
                    SET coordinate_point = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                        polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(polygon_geojson::text)), 4326)
                    WHERE id = {$upt->id}
                ");
            } catch (\Throwable $e) {
                // Lewati jika PostGIS tidak aktif
            }
        }

        // Rekam Audit Log Forensik
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_UPT_LOCATION',
                'target_table' => 'upt_locations',
                'target_id' => (string) $upt->id,
                'details' => [
                    'upt_number' => $upt->upt_number,
                    'upt_name' => $upt->upt_name,
                    'data' => $validated,
                ],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }

        return redirect()->route('admin.upt.index')
            ->with('success', "Data UPT No. {$upt->upt_number} ({$upt->upt_name}) berhasil ditambahkan ke Master Data.");
    }

    /**
     * Tampilkan detail komprehensif 1 UPT
     */
    public function show(int $id): View
    {
        $upt = UptLocation::with(['regency', 'documents', 'changeRequests'])->findOrFail($id);
        return view('admin.master_data_upt.show', compact('upt'));
    }

    /**
     * Form edit data UPT
     */
    public function edit(int $id): View
    {
        $upt = UptLocation::with('regency')->findOrFail($id);
        $regencies = Regency::orderBy('id')->get();
        return view('admin.master_data_upt.edit', compact('upt', 'regencies'));
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

    /**
     * Cetak Buku Induk Master Data 124 UPT format PDF A4 Landscape
     */
    public function exportPdf(Request $request)
    {
        $query = UptLocation::with(['regency', 'documents']);

        // Filter Kabupaten
        $targetRegency = null;
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
            $targetRegency = Regency::find($request->regency_id);
        } else {
            // Sinkronisasi dengan kontrol visibilitas Wilayah Peta WebGIS
            $query->whereHas('regency', function ($q) {
                $q->where('is_visible', true);
            });
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

        $locations = $query->orderBy('upt_number')->get();

        if ($targetRegency) {
            $filterRegencyName = "Kabupaten {$targetRegency->name}";
        } else {
            $visibleRegencies = Regency::where('is_visible', true)->orderBy('id')->get();
            if ($visibleRegencies->count() === 9) {
                $filterRegencyName = 'Seluruh Wilayah (9 Kabupaten Binaan)';
            } else {
                $filterRegencyName = "{$visibleRegencies->count()} Kabupaten Terpublikasi (" . $visibleRegencies->pluck('name')->implode(', ') . ')';
            }
        }
        $filterStatusLabel = match ($request->input('status')) {
            'clean' => 'Clean & Clear',
            'warning' => 'Waspada / Monitoring',
            'critical' => 'Kritis / Prioritas Mediasi',
            default => 'Semua Status Lahan',
        };

        $signCity = 'Banjarbaru';
        $signDate = date('d F Y');
        $orientation = 'landscape';

        $pdf = Pdf::loadView('admin.reports.pdf.master_upt', compact(
            'locations',
            'filterRegencyName',
            'filterStatusLabel',
            'signCity',
            'signDate',
            'orientation'
        ))->setPaper('a4', 'landscape');

        $safeName = $targetRegency ? preg_replace('/[^A-Za-z0-9]/', '_', $targetRegency->name) : 'Kalsel';
        $filename = "Buku_Induk_Master_Data_UPT_{$safeName}_" . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
