<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkpManagementController extends Controller
{
    /**
     * Tampilkan daftar Kawasan Transmigrasi & SKP
     */
    public function index(Request $request): View
    {
        $query = SkpArea::with(['regency', 'villages', 'uptLocations']);

        // Filter Kabupaten
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        }

        // Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('skp_name', 'ILIKE', "%{$search}%")
                  ->orWhere('kawasan_name', 'ILIKE', "%{$search}%")
                  ->orWhere('unit_code', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan', 'ILIKE', "%{$search}%")
                  ->orWhereHas('regency', fn($rq) => $rq->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('villages', fn($vq) => $vq->where('village_name', 'ILIKE', "%{$search}%")->orWhere('district_name', 'ILIKE', "%{$search}%"));
            });
        }

        $skpAreas = $query->orderBy('regency_id')->orderBy('skp_name')->paginate(10)->withQueryString();
        $regencies = Regency::where('is_visible', true)->orderBy('name')->get();

        $stats = [
            'total_skp' => SkpArea::count(),
            'total_villages' => SkpVillage::count(),
            'total_area_ha' => (float) SkpArea::sum('luas_ha'),
            'total_population' => (int) SkpVillage::sum('population_total'),
            'total_family' => (int) SkpVillage::sum('family_count'),
        ];

        return view('admin.kawasan_skp.index', compact('skpAreas', 'regencies', 'stats'));
    }

    /**
     * Form tambah SKP baru
     */
    public function create(): View
    {
        $regencies = Regency::where('is_visible', true)->orderBy('name')->get();
        $uptLocations = UptLocation::select('id', 'regency_id', 'upt_number', 'upt_name', 'current_village_name')
            ->orderBy('upt_name')
            ->get();

        return view('admin.kawasan_skp.create', compact('regencies', 'uptLocations'));
    }

    /**
     * Simpan SKP dan baris desa binaan
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'regency_id' => 'required|integer|exists:regencies,id',
            'kawasan_name' => 'required|string|max:150',
            'skp_name' => 'required|string|max:100',
            'unit_code' => 'nullable|string|max:50',
            'luas_ha' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'sumber_data' => 'nullable|string|max:150',
            'geojson_file' => 'nullable|file|max:10240', // max 10MB
            'villages' => 'nullable|array',
            'villages.*.district_name' => 'required_with:villages.*.village_name|string|max:100',
            'villages.*.village_name' => 'required_with:villages.*.district_name|string|max:150',
            'villages.*.area_km2' => 'nullable|numeric|min:0',
            'villages.*.population_male' => 'nullable|integer|min:0',
            'villages.*.population_female' => 'nullable|integer|min:0',
            'villages.*.family_count' => 'nullable|integer|min:0',
            'villages.*.notes' => 'nullable|string|max:255',
            'villages.*.upt_location_id' => 'nullable|integer|exists:upt_locations,id',
        ]);

        $polygonGeojson = null;
        $centroidLng = null;
        $centroidLat = null;

        // Proses unggah file GeoJSON jika ada
        if ($request->hasFile('geojson_file') && $request->file('geojson_file')->isValid()) {
            try {
                $rawContent = file_get_contents($request->file('geojson_file')->getRealPath());
                $parsed = json_decode($rawContent, true);
                if (isset($parsed['type'])) {
                    if ($parsed['type'] === 'FeatureCollection' && !empty($parsed['features'])) {
                        $firstFeature = $parsed['features'][0];
                        $polygonGeojson = $firstFeature['geometry'] ?? null;
                    } elseif ($parsed['type'] === 'Feature') {
                        $polygonGeojson = $parsed['geometry'] ?? null;
                    } elseif (in_array($parsed['type'], ['Polygon', 'MultiPolygon'])) {
                        $polygonGeojson = $parsed;
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan kesalahan parsing GeoJSON
            }
        }

        $skp = SkpArea::create([
            'regency_id' => $validated['regency_id'],
            'kawasan_name' => $validated['kawasan_name'],
            'skp_name' => $validated['skp_name'],
            'unit_code' => $validated['unit_code'] ?? null,
            'luas_ha' => $validated['luas_ha'] ?? null,
            'color' => !empty($validated['color']) ? $validated['color'] : '#163A63',
            'keterangan' => $validated['keterangan'] ?? null,
            'sumber_data' => !empty($validated['sumber_data']) ? $validated['sumber_data'] : 'DPMD & BIG',
            'centroid_lat' => $centroidLat,
            'centroid_lng' => $centroidLng,
            'polygon_geojson' => $polygonGeojson,
            'is_active' => true,
        ]);

        // Simpan baris desa jika ada
        if (!empty($validated['villages']) && is_array($validated['villages'])) {
            foreach ($validated['villages'] as $villageData) {
                if (empty($villageData['village_name'])) continue;

                $male = (int)($villageData['population_male'] ?? 0);
                $female = (int)($villageData['population_female'] ?? 0);
                $total = $male + $female;

                $village = SkpVillage::create([
                    'skp_area_id' => $skp->id,
                    'upt_location_id' => $villageData['upt_location_id'] ?? null,
                    'district_name' => $villageData['district_name'] ?? 'Belum terdata',
                    'village_name' => $villageData['village_name'],
                    'area_km2' => $villageData['area_km2'] ?? 0,
                    'population_male' => $male,
                    'population_female' => $female,
                    'population_total' => $total,
                    'family_count' => $villageData['family_count'] ?? 0,
                    'notes' => $villageData['notes'] ?? $skp->skp_name,
                ]);

                // Hubungkan UPT jika dipilih
                if (!empty($villageData['upt_location_id'])) {
                    UptLocation::where('id', $villageData['upt_location_id'])->update(['skp_id' => $skp->id]);
                }
            }
        }

        AuditLog::log(
            action: 'CREATE_SKP_AREA',
            targetTable: 'skp_areas',
            targetId: $skp->id,
            details: [
                'skp_name' => $skp->skp_name,
                'kawasan_name' => $skp->kawasan_name,
                'regency_id' => $skp->regency_id,
            ]
        );

        return redirect()->route('admin.skp.index')->with('success', "Kawasan {$skp->skp_name} ({$skp->kawasan_name}) berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail rincian SKP dan desa di dalamnya
     */
    public function show(int $id): View
    {
        $skp = SkpArea::with(['regency', 'villages.uptLocation', 'uptLocations'])->findOrFail($id);

        return view('admin.kawasan_skp.show', compact('skp'));
    }

    /**
     * Form ubah SKP
     */
    public function edit(int $id): View
    {
        $skp = SkpArea::with(['villages', 'uptLocations'])->findOrFail($id);
        $regencies = Regency::where('is_visible', true)->orderBy('name')->get();
        $uptLocations = UptLocation::where('regency_id', $skp->regency_id)
            ->select('id', 'regency_id', 'upt_number', 'upt_name', 'current_village_name')
            ->orderBy('upt_name')
            ->get();

        return view('admin.kawasan_skp.edit', compact('skp', 'regencies', 'uptLocations'));
    }

    /**
     * Perbarui data SKP dan desa binaan
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $skp = SkpArea::findOrFail($id);

        $validated = $request->validate([
            'regency_id' => 'required|integer|exists:regencies,id',
            'kawasan_name' => 'required|string|max:150',
            'skp_name' => 'required|string|max:100',
            'unit_code' => 'nullable|string|max:50',
            'luas_ha' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'sumber_data' => 'nullable|string|max:150',
            'geojson_file' => 'nullable|file|max:10240',
            'villages' => 'nullable|array',
            'villages.*.id' => 'nullable|integer',
            'villages.*.district_name' => 'required_with:villages.*.village_name|string|max:100',
            'villages.*.village_name' => 'required_with:villages.*.district_name|string|max:150',
            'villages.*.area_km2' => 'nullable|numeric|min:0',
            'villages.*.population_male' => 'nullable|integer|min:0',
            'villages.*.population_female' => 'nullable|integer|min:0',
            'villages.*.family_count' => 'nullable|integer|min:0',
            'villages.*.notes' => 'nullable|string|max:255',
            'villages.*.upt_location_id' => 'nullable|integer|exists:upt_locations,id',
        ]);

        $updatePayload = [
            'regency_id' => $validated['regency_id'],
            'kawasan_name' => $validated['kawasan_name'],
            'skp_name' => $validated['skp_name'],
            'unit_code' => $validated['unit_code'] ?? null,
            'luas_ha' => $validated['luas_ha'] ?? null,
            'color' => !empty($validated['color']) ? $validated['color'] : '#163A63',
            'keterangan' => $validated['keterangan'] ?? null,
            'sumber_data' => !empty($validated['sumber_data']) ? $validated['sumber_data'] : 'DPMD & BIG',
        ];

        // Jika upload file GeoJSON baru
        if ($request->hasFile('geojson_file') && $request->file('geojson_file')->isValid()) {
            try {
                $rawContent = file_get_contents($request->file('geojson_file')->getRealPath());
                $parsed = json_decode($rawContent, true);
                if (isset($parsed['type'])) {
                    if ($parsed['type'] === 'FeatureCollection' && !empty($parsed['features'])) {
                        $updatePayload['polygon_geojson'] = $parsed['features'][0]['geometry'] ?? null;
                    } elseif ($parsed['type'] === 'Feature') {
                        $updatePayload['polygon_geojson'] = $parsed['geometry'] ?? null;
                    } elseif (in_array($parsed['type'], ['Polygon', 'MultiPolygon'])) {
                        $updatePayload['polygon_geojson'] = $parsed;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $skp->update($updatePayload);

        // Sinkronisasi daftar desa
        $processedVillageIds = [];
        if (!empty($validated['villages']) && is_array($validated['villages'])) {
            foreach ($validated['villages'] as $vData) {
                if (empty($vData['village_name'])) continue;

                $male = (int)($vData['population_male'] ?? 0);
                $female = (int)($vData['population_female'] ?? 0);
                $total = $male + $female;

                $villageAttributes = [
                    'skp_area_id' => $skp->id,
                    'upt_location_id' => $vData['upt_location_id'] ?? null,
                    'district_name' => $vData['district_name'] ?? 'Belum terdata',
                    'village_name' => $vData['village_name'],
                    'area_km2' => $vData['area_km2'] ?? 0,
                    'population_male' => $male,
                    'population_female' => $female,
                    'population_total' => $total,
                    'family_count' => $vData['family_count'] ?? 0,
                    'notes' => $vData['notes'] ?? $skp->skp_name,
                ];

                if (!empty($vData['id'])) {
                    $existing = SkpVillage::where('skp_area_id', $skp->id)->where('id', $vData['id'])->first();
                    if ($existing) {
                        $existing->update($villageAttributes);
                        $processedVillageIds[] = $existing->id;
                        continue;
                    }
                }

                $newVillage = SkpVillage::create($villageAttributes);
                $processedVillageIds[] = $newVillage->id;

                if (!empty($vData['upt_location_id'])) {
                    UptLocation::where('id', $vData['upt_location_id'])->update(['skp_id' => $skp->id]);
                }
            }
        }

        // Hapus desa yang dihapus dari form
        $skp->villages()->whereNotIn('id', $processedVillageIds)->delete();

        AuditLog::log(
            action: 'UPDATE_SKP_AREA',
            targetTable: 'skp_areas',
            targetId: $skp->id,
            details: ['skp_name' => $skp->skp_name]
        );

        return redirect()->route('admin.skp.show', $skp->id)->with('success', "Data {$skp->skp_name} berhasil diperbarui.");
    }

    /**
     * Hapus SKP
     */
    public function destroy(int $id): RedirectResponse
    {
        $skp = SkpArea::findOrFail($id);
        $name = $skp->skp_name;

        // Lepaskan referensi UPT
        UptLocation::where('skp_id', $skp->id)->update(['skp_id' => null]);
        $skp->delete();

        AuditLog::log(
            action: 'DELETE_SKP_AREA',
            targetTable: 'skp_areas',
            targetId: $id,
            details: ['deleted_name' => $name]
        );

        return redirect()->route('admin.skp.index')->with('success', "Data {$name} telah dihapus.");
    }

    /**
     * JSON Endpoint untuk modal detail profil desa saat nama desa diklik dari tabel
     */
    public function villageDetail(int $id): JsonResponse
    {
        $village = SkpVillage::with(['skpArea.regency', 'uptLocation'])->findOrFail($id);

        return response()->json([
            'id' => $village->id,
            'village_name' => $village->village_name,
            'district_name' => $village->district_name,
            'area_km2' => (float) $village->area_km2,
            'area_formatted' => number_format((float)$village->area_km2, 2, ',', '.') . ' Km²',
            'population_male' => (int) $village->population_male,
            'population_female' => (int) $village->population_female,
            'population_total' => (int) $village->population_total,
            'family_count' => (int) $village->family_count,
            'notes' => $village->notes,
            'skp' => [
                'id' => $village->skpArea?->id,
                'name' => $village->skpArea?->skp_name,
                'unit_code' => $village->skpArea?->unit_code,
                'kawasan' => $village->skpArea?->kawasan_name,
                'color' => $village->skpArea?->color ?: '#163A63',
                'regency' => $village->skpArea?->regency?->name,
                'luas_ha' => (float) ($village->skpArea?->luas_ha ?? 0),
            ],
            'upt' => $village->uptLocation ? [
                'id' => $village->uptLocation->id,
                'upt_name' => $village->uptLocation->upt_name,
                'upt_number' => $village->uptLocation->upt_number,
                'pattern' => $village->uptLocation->business_pattern,
                'status' => $village->uptLocation->issue_status,
            ] : null,
        ]);
    }

    /**
     * Simpan 1 desa baru ke SKP
     */
    public function storeVillage(Request $request, int $skpId): JsonResponse
    {
        $skp = SkpArea::findOrFail($skpId);
        $validated = $request->validate([
            'district_name' => 'required|string|max:100',
            'village_name' => 'required|string|max:150',
            'area_km2' => 'nullable|numeric|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'family_count' => 'nullable|integer|min:0',
            'upt_location_id' => 'nullable|integer|exists:upt_locations,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $male = (int)($validated['population_male'] ?? 0);
        $female = (int)($validated['population_female'] ?? 0);
        $total = $male + $female;

        $village = SkpVillage::create([
            'skp_area_id' => $skp->id,
            'upt_location_id' => $validated['upt_location_id'] ?? null,
            'district_name' => $validated['district_name'],
            'village_name' => $validated['village_name'],
            'area_km2' => $validated['area_km2'] ?? 0,
            'population_male' => $male,
            'population_female' => $female,
            'population_total' => $total,
            'family_count' => $validated['family_count'] ?? 0,
            'notes' => $validated['notes'] ?? $skp->skp_name,
        ]);

        if (!empty($validated['upt_location_id'])) {
            UptLocation::where('id', $validated['upt_location_id'])->update(['skp_id' => $skp->id]);
        }

        AuditLog::log('CREATE_SKP_VILLAGE', 'skp_villages', $village->id, [
            'village_name' => $village->village_name,
            'skp_name' => $skp->skp_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Data desa {$village->village_name} berhasil ditambahkan ke {$skp->skp_name}.",
            'village' => $village->load('uptLocation'),
        ]);
    }

    /**
     * Perbarui 1 data desa
     */
    public function updateVillage(Request $request, int $id): JsonResponse
    {
        $village = SkpVillage::with('skpArea')->findOrFail($id);
        $validated = $request->validate([
            'district_name' => 'required|string|max:100',
            'village_name' => 'required|string|max:150',
            'area_km2' => 'nullable|numeric|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'family_count' => 'nullable|integer|min:0',
            'upt_location_id' => 'nullable|integer|exists:upt_locations,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $male = (int)($validated['population_male'] ?? 0);
        $female = (int)($validated['population_female'] ?? 0);
        $total = $male + $female;

        $village->update([
            'upt_location_id' => $validated['upt_location_id'] ?? null,
            'district_name' => $validated['district_name'],
            'village_name' => $validated['village_name'],
            'area_km2' => $validated['area_km2'] ?? 0,
            'population_male' => $male,
            'population_female' => $female,
            'population_total' => $total,
            'family_count' => $validated['family_count'] ?? 0,
            'notes' => $validated['notes'] ?? $village->notes,
        ]);

        if (!empty($validated['upt_location_id'])) {
            UptLocation::where('id', $validated['upt_location_id'])->update(['skp_id' => $village->skp_area_id]);
        }

        AuditLog::log('UPDATE_SKP_VILLAGE', 'skp_villages', $village->id, [
            'village_name' => $village->village_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Data desa {$village->village_name} berhasil diperbarui.",
            'village' => $village->load('uptLocation'),
        ]);
    }

    /**
     * Hapus 1 desa
     */
    public function destroyVillage(int $id): JsonResponse
    {
        $village = SkpVillage::findOrFail($id);
        $name = $village->village_name;
        $village->delete();

        AuditLog::log('DELETE_SKP_VILLAGE', 'skp_villages', $id, ['deleted_name' => $name]);

        return response()->json([
            'success' => true,
            'message' => "Data desa {$name} telah dihapus.",
        ]);
    }

    /**
     * Ekspor Dokumen Resmi PDF Rekapitulasi Kawasan & SKP
     */
    public function exportPdf()
    {
        $skpAreas = SkpArea::with(['regency', 'villages'])->orderBy('regency_id')->orderBy('skp_name')->get();
        $stats = [
            'total_skp' => $skpAreas->count(),
            'total_villages' => SkpVillage::count(),
            'total_area_ha' => (float) $skpAreas->sum('luas_ha'),
            'total_population' => (int) SkpVillage::sum('population_total'),
            'total_family' => (int) SkpVillage::sum('family_count'),
        ];

        $pdf = Pdf::loadView('admin.kawasan_skp.pdf', compact('skpAreas', 'stats'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Kawasan_SKP_Kalsel_' . now()->format('Ymd_His') . '.pdf');
    }
}
