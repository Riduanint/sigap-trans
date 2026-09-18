<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UptLocationApiController extends Controller
{
    /**
     * Mengambil daftar lokasi UPT dalam format GeoJSON FeatureCollection
     */
    public function index(Request $request): JsonResponse
    {
        $query = UptLocation::with('regency')
            ->whereHas('regency', fn($q) => $q->where('is_visible', true));

        // Filter Kabupaten
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        }

        // Filter Status Lahan (clean, warning, critical)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('issue_status', $request->status);
        }

        // Filter Pola Usaha
        if ($request->filled('business_pattern') && $request->business_pattern !== 'all') {
            $patterns = is_array($request->business_pattern) ? $request->business_pattern : [$request->business_pattern];
            $query->whereIn('business_pattern', $patterns);
        }

        // Filter Dekade Penempatan (e.g. 1950, 1960, 1970, 1980, 1990, 2000)
        if ($request->filled('decade') && $request->decade !== 'all') {
            $decade = (int) $request->decade;
            $nextDecade = $decade + 9;
            $query->where(function ($q) use ($decade, $nextDecade) {
                for ($yr = $decade; $yr <= $nextDecade; $yr++) {
                    $q->orWhere('placement_year', 'LIKE', "%{$yr}%");
                }
            });
        }

        // Pencarian Teks (Nama UPT / Desa Definitif)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('upt_name', 'ILIKE', "%{$search}%")
                  ->orWhere('current_village_name', 'ILIKE', "%{$search}%")
                  ->orWhereHas('regency', function ($rq) use ($search) {
                      $rq->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $locations = $query->orderBy('upt_number')->get();

        $features = $locations->map(function ($upt) {
            $feature = $upt->toGeoJsonFeature();
            // Tambahkan polygon jika ada untuk visualisasi delineasi
            if ($upt->polygon_geojson) {
                $feature['properties']['has_polygon'] = true;
                $feature['properties']['polygon_geojson'] = $upt->polygon_geojson;
            }
            return $feature;
        });

        // Hitung statistik dinamis berdasarkan hasil query yang difilter
        $meta = [
            'total_features' => $locations->count(),
            'total_kk_placement' => (int) $locations->sum('placement_kk'),
            'total_pop_placement' => (int) $locations->sum('placement_population'),
            'total_kk_handover' => (int) $locations->sum('handover_kk'),
            'total_pop_handover' => (int) $locations->sum('handover_population'),
            'status_counts' => [
                'clean' => $locations->where('issue_status', 'clean')->count(),
                'warning' => $locations->where('issue_status', 'warning')->count(),
                'critical' => $locations->where('issue_status', 'critical')->count(),
            ],
        ];

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
            'meta' => $meta,
        ]);
    }

    /**
     * Detail 1 UPT untuk kartu profil komprehensif
     */
    public function show(int $id): JsonResponse
    {
        $upt = UptLocation::with(['regency', 'documents'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $upt->id,
                'upt_number' => $upt->upt_number,
                'upt_name' => $upt->upt_name,
                'current_village_name' => $upt->current_village_name,
                'regency_id' => $upt->regency_id,
                'regency_name' => $upt->regency?->name,
                'regency_roman' => $upt->regency?->code_roman,
                'business_pattern' => $upt->business_pattern,
                'placement_year' => $upt->placement_year,
                'placement_kk' => $upt->placement_kk,
                'placement_population' => $upt->placement_population,
                'handover_year' => $upt->handover_year,
                'handover_kk' => $upt->handover_kk,
                'handover_population' => $upt->handover_population,
                'issue_status' => $upt->issue_status,
                'issue_note' => $upt->issue_note,
                'is_verified' => $upt->is_verified,
                'latitude' => $upt->latitude,
                'longitude' => $upt->longitude,
                'shm_status' => $upt->shm_status,
                'polygon_geojson' => $upt->polygon_geojson,
                'documents_count' => $upt->documents->count(),
                'documents' => $upt->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'document_number' => $doc->document_number,
                    'file_name' => $doc->file_name,
                    'file_size_kb' => $doc->file_size_kb,
                ]),
            ],
        ]);
    }

    /**
     * Daftar Kabupaten aktif untuk filter & navigasi cepat zoom
     */
    public function regencies(): JsonResponse
    {
        $regencies = Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->orderBy('id')
            ->get()
            ->map(function ($regency) {
                return [
                    'id' => $regency->id,
                    'code_roman' => $regency->code_roman,
                    'name' => $regency->name,
                    'capital_city' => $regency->capital_city,
                    'map_color' => $regency->map_color,
                    'latitude' => $regency->latitude,
                    'longitude' => $regency->longitude,
                    'upt_count' => $regency->upt_locations_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $regencies,
        ]);
    }

    /**
     * Batas poligon GeoJSON kabupaten yang disetel tampil oleh Super Admin
     */
    public function boundaries(): JsonResponse
    {
        $visibleRegencies = Regency::where('is_visible', true)->get()->keyBy('id');
        $geojsonPath = public_path('data/kalsel_regencies.geojson');

        if (! file_exists($geojsonPath)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => [],
            ]);
        }

        $geojson = json_decode(file_get_contents($geojsonPath), true);
        $features = [];

        foreach ($geojson['features'] ?? [] as $feature) {
            $id = $feature['properties']['id'] ?? $feature['id'] ?? null;
            if (isset($visibleRegencies[$id])) {
                $reg = $visibleRegencies[$id];
                $feature['properties']['color'] = $reg->map_color ?: ($feature['properties']['color'] ?? '#8b5cf6');
                $feature['properties']['name'] = $reg->name;
                $feature['properties']['code_roman'] = $reg->code_roman;
                $features[] = $feature;
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Statistik agregat utama se-Kalimantan Selatan (wilayah aktif)
     */
    public function statistics(): JsonResponse
    {
        $baseUpt = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true));

        $totalUpt = (clone $baseUpt)->count();
        $totalPlacementKk = (int) (clone $baseUpt)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $baseUpt)->sum('placement_population');
        $totalHandoverKk = (int) (clone $baseUpt)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $baseUpt)->sum('handover_population');

        $statusClean = (clone $baseUpt)->where('issue_status', 'clean')->count();
        $statusWarning = (clone $baseUpt)->where('issue_status', 'warning')->count();
        $statusCritical = (clone $baseUpt)->where('issue_status', 'critical')->count();

        // Agregat per kabupaten aktif
        $perRegency = Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'placement_population', 'handover_kk', 'handover_population', 'issue_status');
            }])
            ->orderBy('id')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'code_roman' => $reg->code_roman,
                    'name' => $reg->name,
                    'upt_count' => $reg->upt_locations_count,
                    'placement_kk' => (int) $reg->uptLocations->sum('placement_kk'),
                    'handover_kk' => (int) $reg->uptLocations->sum('handover_kk'),
                    'clean_count' => $reg->uptLocations->where('issue_status', 'clean')->count(),
                    'warning_count' => $reg->uptLocations->where('issue_status', 'warning')->count(),
                    'critical_count' => $reg->uptLocations->where('issue_status', 'critical')->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'province' => [
                'total_upt' => $totalUpt,
                'total_placement_kk' => $totalPlacementKk,
                'total_placement_population' => $totalPlacementPop,
                'total_handover_kk' => $totalHandoverKk,
                'total_handover_population' => $totalHandoverPop,
                'clean_count' => $statusClean,
                'warning_count' => $statusWarning,
                'critical_count' => $statusCritical,
            ],
            'per_regency' => $perRegency,
        ]);
    }
}
