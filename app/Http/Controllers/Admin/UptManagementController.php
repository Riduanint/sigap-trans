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
use Illuminate\Support\Facades\Storage;
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

        $patterns = UptLocation::query()->distinct()->orderBy('business_pattern')->pluck('business_pattern');

        return view('admin.master_data_upt.master_data_upt', compact('uptLocations', 'regencies', 'stats', 'patterns'));
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
            'issue_status' => ['required', 'in:clean,warning,critical'],
            'issue_note' => ['nullable', 'string'],
            'shm_status' => ['nullable', 'string', 'max:100'],
            'coordinates' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'polygon_file' => ['nullable', 'file', 'max:10240'],
            'polygon_geojson' => ['nullable'],
        ]);

        if ($request->filled('coordinates')) {
            $parsed = \App\Support\CoordinateParser::parse($request->coordinates);
            if ($parsed) {
                $validated['latitude'] = $parsed['latitude'];
                $validated['longitude'] = $parsed['longitude'];
            }
        }

        $validated['placement_year'] = null;
        $validated['placement_kk'] = 0;
        $validated['placement_population'] = 0;
        $validated['handover_year'] = null;
        $validated['handover_kk'] = 0;
        $validated['handover_population'] = 0;
        $validated['is_verified'] = true;

        // Ekstrak isi berkas poligon (.shp, .zip, .json, .geojson) atau fallback payload GeoJSON
        $polygonGeojson = null;
        if ($request->hasFile('polygon_file')) {
            $polygonGeojson = $this->extractPolygonFromFile($request->file('polygon_file'));
        } elseif ($request->filled('polygon_geojson')) {
            $polygonGeojson = $this->parseGeojsonGeometry($request->input('polygon_geojson'));
        }

        if ($polygonGeojson) {
            $validated['polygon_geojson'] = $polygonGeojson;
            // Jika koordinat titik belum terisi, otomatis gunakan titik tengah poligon
            if (empty($validated['latitude']) || empty($validated['longitude'])) {
                $centroid = $this->calculateGeometryCentroid($polygonGeojson);
                if ($centroid) {
                    $validated['latitude'] = $centroid['lat'];
                    $validated['longitude'] = $centroid['lng'];
                }
            }
        } elseif (!empty($validated['latitude']) && !empty($validated['longitude'])) {
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

        // Update geometri PostGIS jika kolom coordinate_point / polygon_area tersedia
        if (!empty($validated['polygon_geojson']) || (!empty($validated['latitude']) && !empty($validated['longitude']))) {
            try {
                $lat = $validated['latitude'] ?? null;
                $lng = $validated['longitude'] ?? null;
                $geom = $validated['polygon_geojson'] ?? null;

                if ($geom && $lat && $lng) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET coordinate_point = ST_SetSRID(ST_MakePoint(?, ?), 4326),
                            polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)
                        WHERE id = ?
                    ", [$lng, $lat, json_encode($geom), $upt->id]);
                } elseif ($geom) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)
                        WHERE id = ?
                    ", [json_encode($geom), $upt->id]);
                } elseif ($lat && $lng) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET coordinate_point = ST_SetSRID(ST_MakePoint(?, ?), 4326)
                        WHERE id = ?
                    ", [$lng, $lat, $upt->id]);
                }
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
            'upt_name' => ['nullable', 'string', 'max:150'],
            'current_village_name' => ['required', 'string', 'max:150'],
            'business_pattern' => ['required', 'string', 'max:50'],
            'issue_status' => ['required', 'in:clean,warning,critical'],
            'issue_note' => ['nullable', 'string'],
            'shm_status' => ['nullable', 'string', 'max:100'],
            'coordinates' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'polygon_file' => ['nullable', 'file', 'max:10240'],
            'polygon_geojson' => ['nullable'],
        ]);

        if ($request->filled('coordinates')) {
            $parsed = \App\Support\CoordinateParser::parse($request->coordinates);
            if ($parsed) {
                $validated['latitude'] = $parsed['latitude'];
                $validated['longitude'] = $parsed['longitude'];
            }
        }

        // Ekstrak isi berkas poligon (.shp, .zip, .json, .geojson) atau fallback payload GeoJSON
        $polygonGeojson = null;
        if ($request->hasFile('polygon_file')) {
            $polygonGeojson = $this->extractPolygonFromFile($request->file('polygon_file'));
        } elseif ($request->filled('polygon_geojson')) {
            $polygonGeojson = $this->parseGeojsonGeometry($request->input('polygon_geojson'));
        }

        if ($polygonGeojson) {
            $validated['polygon_geojson'] = $polygonGeojson;
            if (empty($validated['latitude']) || empty($validated['longitude'])) {
                $centroid = $this->calculateGeometryCentroid($polygonGeojson);
                if ($centroid) {
                    $validated['latitude'] = $centroid['lat'];
                    $validated['longitude'] = $centroid['lng'];
                }
            }
        } elseif (!empty($validated['latitude']) && !empty($validated['longitude']) && empty($upt->polygon_geojson)) {
            // Hanya buat buffer poligon jika UPT belum memiliki polygon_geojson sebelumnya
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $delta = 0.008;
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

        $oldData = $upt->only([
            'current_village_name', 'business_pattern', 'issue_status', 
            'issue_note', 'shm_status', 'placement_kk', 'handover_kk',
            'latitude', 'longitude', 'polygon_geojson'
        ]);

        $upt->update($validated);

        if (!empty($validated['polygon_geojson']) || (!empty($validated['latitude']) && !empty($validated['longitude']))) {
            try {
                $lat = $validated['latitude'] ?? $upt->latitude;
                $lng = $validated['longitude'] ?? $upt->longitude;
                $geom = $validated['polygon_geojson'] ?? $upt->polygon_geojson;

                if ($geom && $lat && $lng) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET coordinate_point = ST_SetSRID(ST_MakePoint(?, ?), 4326),
                            polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)
                        WHERE id = ?
                    ", [$lng, $lat, json_encode($geom), $upt->id]);
                } elseif ($geom) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET polygon_area = ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)
                        WHERE id = ?
                    ", [json_encode($geom), $upt->id]);
                } elseif ($lat && $lng) {
                    \Illuminate\Support\Facades\DB::statement("
                        UPDATE upt_locations 
                        SET coordinate_point = ST_SetSRID(ST_MakePoint(?, ?), 4326)
                        WHERE id = ?
                    ", [$lng, $lat, $upt->id]);
                }
            } catch (\Throwable $e) {}
        }

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
     * Hapus data UPT dari Master Data
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        if ($request->user()->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin Provinsi yang memiliki otorisasi menghapus data UPT.');
        }

        $upt = UptLocation::with('documents')->findOrFail($id);
        $uptNumber = $upt->upt_number;
        $uptName = $upt->upt_name;

        // Bersihkan berkas fisik digital di storage jika ada
        foreach ($upt->documents as $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                try {
                    Storage::disk('public')->delete($doc->file_path);
                } catch (\Throwable $e) {}
            }
        }

        // Rekam Audit Log Forensik
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_UPT_LOCATION',
                'target_table' => 'upt_locations',
                'target_id' => (string) $upt->id,
                'details' => [
                    'upt_number' => $uptNumber,
                    'upt_name' => $uptName,
                    'regency_id' => $upt->regency_id,
                    'deleted_at' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }

        $upt->delete();

        return redirect()->route('admin.upt.index')
            ->with('success', "Data UPT No. {$uptNumber} ({$uptName}) berhasil dihapus dari Master Data.");
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
        } elseif ($request->user()->role !== 'super_admin') {
            // Public reporting for other roles follows map visibility.
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
        } elseif ($request->user()->role === 'super_admin') {
            $filterRegencyName = 'Seluruh wilayah administrasi';
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

    /**
     * Parsing dan ekstraksi geometri Polygon / MultiPolygon dari payload GeoJSON
     */
    private function parseGeojsonGeometry(mixed $raw): ?array
    {
        if (empty($raw)) {
            return null;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        // FeatureCollection
        if (isset($decoded['type']) && $decoded['type'] === 'FeatureCollection' && !empty($decoded['features'][0]['geometry'])) {
            $decoded = $decoded['features'][0]['geometry'];
        }

        // Feature
        if (isset($decoded['type']) && $decoded['type'] === 'Feature' && isset($decoded['geometry'])) {
            $decoded = $decoded['geometry'];
        }

        if (isset($decoded['type']) && in_array($decoded['type'], ['Polygon', 'MultiPolygon']) && !empty($decoded['coordinates'])) {
            return $decoded;
        }

        return null;
    }

    /**
     * Ekstrak poligon GeoJSON dari berkas unggahan (.json, .geojson, .shp, atau .zip)
     */
    private function extractPolygonFromFile($file): ?array
    {
        if (!$file) return null;

        $fileName = strtolower($file->getClientOriginalName());
        $filePath = $file->getRealPath();

        // 1. Berkas ESRI Shapefile (.shp)
        if (str_ends_with($fileName, '.shp')) {
            $binary = file_get_contents($filePath);
            return $this->parseShapefileToGeoJson($binary);
        }

        // 2. Berkas ZIP berisi Shapefile
        if (str_ends_with($fileName, '.zip')) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if (str_ends_with(strtolower($stat['name']), '.shp')) {
                        $binary = $zip->getFromIndex($i);
                        $zip->close();
                        return $this->parseShapefileToGeoJson($binary);
                    }
                }
                $zip->close();
            }
        }

        // 3. Berkas GeoJSON / JSON
        $content = file_get_contents($filePath);
        $geojson = $this->parseGeojsonGeometry($content);
        if ($geojson) {
            return $geojson;
        }

        // Fallback: coba baca sebagai Shapefile biner jika ekstensi tidak standar
        return $this->parseShapefileToGeoJson($content);
    }

    /**
     * Parsing berkas ESRI Shapefile (.shp) biner menjadi struktur GeoJSON Polygon / MultiPolygon
     */
    private function parseShapefileToGeoJson(string $binaryData): ?array
    {
        $len = strlen($binaryData);
        if ($len < 100) return null;

        $fileCode = unpack('N', substr($binaryData, 0, 4))[1];
        if ($fileCode !== 9994) return null;

        $shapeType = unpack('V', substr($binaryData, 32, 4))[1];
        // 5 = Polygon, 15 = PolygonZ, 25 = PolygonM
        if (!in_array($shapeType, [5, 15, 25])) {
            return null;
        }

        $offset = 100;
        $allPolygons = [];

        while ($offset + 8 <= $len) {
            $recLenWords = unpack('N', substr($binaryData, $offset + 4, 4))[1];
            $recBytes = $recLenWords * 2;
            $offset += 8;

            if ($offset + $recBytes > $len) break;

            $recType = unpack('V', substr($binaryData, $offset, 4))[1];
            if (in_array($recType, [5, 15, 25])) {
                $numParts = unpack('V', substr($binaryData, $offset + 36, 4))[1];
                $numPoints = unpack('V', substr($binaryData, $offset + 40, 4))[1];

                $partsOffset = $offset + 44;
                $parts = [];
                for ($i = 0; $i < $numParts; $i++) {
                    $parts[] = unpack('V', substr($binaryData, $partsOffset + ($i * 4), 4))[1];
                }

                $pointsOffset = $partsOffset + ($numParts * 4);
                $rings = [];
                for ($p = 0; $p < $numParts; $p++) {
                    $startIndex = $parts[$p];
                    $endIndex = ($p + 1 < $numParts) ? $parts[$p + 1] : $numPoints;

                    $ring = [];
                    for ($pt = $startIndex; $pt < $endIndex; $pt++) {
                        $ptOffset = $pointsOffset + ($pt * 16);
                        $x = unpack('d', substr($binaryData, $ptOffset, 8))[1];
                        $y = unpack('d', substr($binaryData, $ptOffset + 8, 8))[1];
                        $ring[] = [round($x, 6), round($y, 6)];
                    }
                    if (!empty($ring)) {
                        if ($ring[0] !== $ring[count($ring) - 1]) {
                            $ring[] = $ring[0];
                        }
                        $rings[] = $ring;
                    }
                }

                if (!empty($rings)) {
                    $allPolygons[] = $rings;
                }
            }

            $offset += $recBytes;
        }

        if (empty($allPolygons)) {
            return null;
        }

        if (count($allPolygons) === 1) {
            return [
                'type' => 'Polygon',
                'coordinates' => $allPolygons[0],
            ];
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $allPolygons,
        ];
    }

    /**
     * Hitung centroid rata-rata dari koordinat poligon GeoJSON
     */
    private function calculateGeometryCentroid(array $geometry): ?array
    {
        if (empty($geometry['coordinates']) || !is_array($geometry['coordinates'])) {
            return null;
        }

        $points = [];
        $extract = function ($c) use (&$extract, &$points) {
            if (is_array($c) && isset($c[0]) && isset($c[1]) && is_numeric($c[0]) && is_numeric($c[1])) {
                $points[] = [(float)$c[0], (float)$c[1]];
            } elseif (is_array($c)) {
                foreach ($c as $sub) {
                    $extract($sub);
                }
            }
        };

        $extract($geometry['coordinates']);

        if (empty($points)) {
            return null;
        }

        $sumLng = 0;
        $sumLat = 0;
        $total = count($points);
        foreach ($points as $p) {
            $sumLng += $p[0];
            $sumLat += $p[1];
        }

        return [
            'lat' => round($sumLat / $total, 6),
            'lng' => round($sumLng / $total, 6),
        ];
    }
}
