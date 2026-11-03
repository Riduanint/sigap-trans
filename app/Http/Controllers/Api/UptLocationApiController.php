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
        $query = UptLocation::with('regency');

        // Jika ada upt_id tertentu, pastikan UPT tersebut tetap dimuat meskipun kabupatennya sedang nonaktif
        if ($request->filled('upt_id')) {
            $uptId = (int) $request->upt_id;
            $query->where(function ($q) use ($uptId) {
                $q->whereHas('regency', fn($rq) => $rq->where('is_visible', true))
                  ->orWhere('id', $uptId);
            });
        } else {
            $query->whereHas('regency', fn($q) => $q->where('is_visible', true));
        }

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

        $isAuth = auth()->check();
        $isOperator = $isAuth && auth()->user()->isOperator();

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
                'is_authenticated' => $isAuth,
                'documents_count' => $upt->documents->count(),
                'documents' => $upt->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'document_number' => $doc->document_number,
                    'file_name' => $doc->file_name,
                    'file_size_kb' => $doc->file_size_kb,
                    'download_url' => $isAuth ? route($isOperator ? 'operator.documents.download' : 'admin.documents.download', $doc->id) : null,
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

    /**
     * Batas poligon GeoJSON Kawasan Transmigrasi SKP se-Kalsel (Dinamis dari database)
     */
    public function skpLocations(Request $request): JsonResponse
    {
        $query = \App\Models\SkpArea::with(['regency', 'villages'])
            ->where('is_active', true)
            ->whereNotNull('polygon_geojson');

        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        }

        $skps = $query->get();

        // Jika data database belum ada poligonnya dan request ke Batola, fallback ke file statis
        if ($skps->isEmpty() && (! $request->filled('regency_id') || (int)$request->regency_id === 6)) {
            return $this->skpCahayaBaru();
        }

        $features = $skps->map(fn($skp) => $skp->toGeoJsonFeature())->values();

        return response()->json([
            'type' => 'FeatureCollection',
            'name' => 'SKP_Kawasan_Transmigrasi_Kalsel',
            'features' => $features,
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Batas poligon GeoJSON Kawasan Transmigrasi SKP Cahaya Baru (Barito Kuala)
     */
    public function skpCahayaBaru(): JsonResponse
    {
        $geojsonPath = public_path('data/skp_cahaya_baru.geojson');

        if (! file_exists($geojsonPath)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => [],
            ], 404);
        }

        $geojson = json_decode(file_get_contents($geojsonPath), true);

        return response()->json($geojson, 200, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Detail 1 SKP Kawasan Transmigrasi beserta cakupan seluruh desa dan agregat kependudukan
     */
    public function showSkp(string|int $id): JsonResponse
    {
        $skp = \App\Models\SkpArea::with(['regency', 'villages.uptLocation', 'uptLocations'])
            ->where(function ($query) use ($id) {
                if (is_numeric($id)) {
                    $query->where('id', (int) $id);
                } else {
                    $query->where('unit_code', $id)
                          ->orWhere('skp_name', $id);
                }
            })
            ->first();

        if (! $skp) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kawasan SKP tidak ditemukan.',
            ], 404);
        }

        // Jika data SKP ini belum memiliki relasi desa langsung (misal akibat beda format nama SKP B vs SKP-B),
        // cari desa dari SKP dengan unit_code dan kabupaten yang sama
        $villagesCollection = $skp->villages;
        if ($villagesCollection->isEmpty() && ! empty($skp->unit_code)) {
            $sisterSkp = \App\Models\SkpArea::with('villages.uptLocation')
                ->where('regency_id', $skp->regency_id)
                ->where('unit_code', $skp->unit_code)
                ->where('id', '!=', $skp->id)
                ->whereHas('villages')
                ->first();

            if ($sisterSkp) {
                $villagesCollection = $sisterSkp->villages;
            }
        }

        $villages = $villagesCollection->map(function ($village) {
            return [
                'id' => $village->id,
                'village_name' => $village->village_name,
                'district_name' => $village->district_name,
                'area_km2' => (float) $village->area_km2,
                'area_km2_formatted' => number_format((float) $village->area_km2, 2, ',', '.'),
                'population_male' => (int) $village->population_male,
                'population_female' => (int) $village->population_female,
                'population_total' => (int) $village->population_total,
                'family_count' => (int) $village->family_count,
                'notes' => $village->notes,
                'upt_id' => $village->upt_location_id,
                'upt_name' => $village->uptLocation?->upt_name,
            ];
        })->values();

        // Cari bounding box poligon SKP
        $polygon = $skp->polygon_geojson;
        if (empty($polygon) && ! empty($skp->unit_code)) {
            $sisterWithPoly = \App\Models\SkpArea::where('regency_id', $skp->regency_id)
                ->where('unit_code', $skp->unit_code)
                ->whereNotNull('polygon_geojson')
                ->first();
            if ($sisterWithPoly) {
                $polygon = $sisterWithPoly->polygon_geojson;
            }
        }

        $bbox = null;
        if (! empty($polygon)) {
            $geometry = $polygon['geometry'] ?? $polygon;
            $coords = $geometry['coordinates'] ?? [];
            if (! empty($coords)) {
                $minLng = PHP_INT_MAX; $minLat = PHP_INT_MAX;
                $maxLng = -PHP_INT_MAX; $maxLat = -PHP_INT_MAX;

                $flatten = function ($arr) use (&$flatten, &$minLng, &$minLat, &$maxLng, &$maxLat) {
                    if (isset($arr[0]) && is_numeric($arr[0]) && isset($arr[1]) && is_numeric($arr[1])) {
                        $minLng = min($minLng, (float) $arr[0]);
                        $maxLng = max($maxLng, (float) $arr[0]);
                        $minLat = min($minLat, (float) $arr[1]);
                        $maxLat = max($maxLat, (float) $arr[1]);
                    } else {
                        foreach ($arr as $item) {
                            if (is_array($item)) {
                                $flatten($item);
                            }
                        }
                    }
                };

                $flatten($coords);
                if ($minLng !== PHP_INT_MAX && $minLat !== PHP_INT_MAX) {
                    $bbox = [$minLng, $minLat, $maxLng, $maxLat];
                }
            }
        }

        $isAuth = auth()->check();

        if (! $isAuth) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $skp->id,
                    'skp_name' => $skp->skp_name,
                    'unit_code' => $skp->unit_code,
                    'kawasan_name' => $skp->kawasan_name,
                    'regency_id' => $skp->regency_id,
                    'regency_name' => $skp->regency?->name,
                    'luas_ha' => (float) $skp->luas_ha,
                    'luas_formatted' => number_format((float) $skp->luas_ha, 2, ',', '.'),
                    'color' => $skp->color ?: '#163A63',
                    'keterangan' => $skp->keterangan,
                    'sumber_data' => $skp->sumber_data ?: 'DPMD & BIG',
                    'centroid_lat' => (float) $skp->centroid_lat,
                    'centroid_lng' => (float) $skp->centroid_lng,
                    'bbox' => $bbox,
                    'is_authenticated' => false,
                ],
            ]);
        }

        $totalMale = (int) $villagesCollection->sum('population_male');
        $totalFemale = (int) $villagesCollection->sum('population_female');
        $totalPop = (int) $villagesCollection->sum('population_total');
        $totalKk = (int) $villagesCollection->sum('family_count');
        $totalVillageKm2 = (float) $villagesCollection->sum('area_km2');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $skp->id,
                'skp_name' => $skp->skp_name,
                'unit_code' => $skp->unit_code,
                'kawasan_name' => $skp->kawasan_name,
                'regency_id' => $skp->regency_id,
                'regency_name' => $skp->regency?->name,
                'luas_ha' => (float) $skp->luas_ha,
                'luas_formatted' => number_format((float) $skp->luas_ha, 2, ',', '.'),
                'color' => $skp->color ?: '#163A63',
                'keterangan' => $skp->keterangan,
                'sumber_data' => $skp->sumber_data ?: 'DPMD & BIG',
                'centroid_lat' => (float) $skp->centroid_lat,
                'centroid_lng' => (float) $skp->centroid_lng,
                'total_population' => $totalPop,
                'total_male' => $totalMale,
                'total_female' => $totalFemale,
                'total_family_count' => $totalKk,
                'total_village_area_km2' => $totalVillageKm2,
                'total_village_area_km2_formatted' => number_format($totalVillageKm2, 2, ',', '.'),
                'villages_count' => $villages->count(),
                'villages' => $villages,
                'bbox' => $bbox,
                'is_authenticated' => true,
            ],
        ]);
    }
}


