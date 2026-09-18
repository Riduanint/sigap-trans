<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlacementManagementController extends Controller
{
    /**
     * Tampilkan antarmuka manajemen Penempatan Awal & Rekapitulasi KK
     */
    public function index(Request $request): View
    {
        $query = UptLocation::with('regency')->withCount('placementFamilyCards');

        // Pencarian Nama UPT, Desa Definitif, atau Nomor UPT
        if ($search = trim($request->input('search', ''))) {
            $uptNumber = null;
            if (preg_match('/\b(?:upt[-\s]*)?(\d+)\b/i', $search, $matches)) {
                $uptNumber = (int) $matches[1];
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

        // Filter Kabupaten
        if ($regencyId = $request->input('regency_id')) {
            if ($regencyId !== 'all') {
                $query->where('regency_id', (int) $regencyId);
            }
        }

        // Filter Pola Usaha
        if ($pattern = $request->input('business_pattern')) {
            if ($pattern !== 'all') {
                $query->where('business_pattern', $pattern);
            }
        }

        // Filter Rentang Tahun Penempatan
        if ($placementYear = trim($request->input('placement_year', ''))) {
            if ($placementYear !== 'all' && $placementYear !== '') {
                $query->where('placement_year', 'ILIKE', "%{$placementYear}%");
            }
        }

        // Pagination Per Page Dinamis
        $perPage = $request->input('per_page', 15);
        if ($perPage === 'all' || (int)$perPage >= 124) {
            $perPage = 124;
        } else {
            $perPage = (int)$perPage;
            if ($perPage < 1) $perPage = 15;
        }

        $uptLocations = $query->orderBy('upt_number')->paginate($perPage)->withQueryString();

        // Agregasi Statistik Penempatan Awal
        $totalPlacementKk = (int) UptLocation::sum('placement_kk');
        $totalPlacementPop = (int) UptLocation::sum('placement_population');
        $avgPopPerKk = $totalPlacementKk > 0 ? round($totalPlacementPop / $totalPlacementKk, 2) : 0;
        $totalUptWithPlacement = UptLocation::where('placement_kk', '>', 0)->count();

        $stats = [
            'total_kk' => $totalPlacementKk,
            'total_population' => $totalPlacementPop,
            'avg_pop_per_kk' => $avgPopPerKk,
            'upt_count' => $totalUptWithPlacement,
            'total_upt' => UptLocation::count(),
            'total_nominal_kk' => (int) \App\Models\UptFamilyCard::placement()->count(),
        ];

        $regencies = Regency::withCount('uptLocations')->orderBy('id')->get();
        $patterns = UptLocation::select('business_pattern')->distinct()->orderBy('business_pattern')->pluck('business_pattern');

        return view('admin.placements.placements', compact('uptLocations', 'regencies', 'patterns', 'stats'));
    }

    /**
     * Simpan / Perbarui Data KK Penempatan Awal suatu UPT
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $upt = UptLocation::findOrFail($id);

        $validated = $request->validate([
            'placement_kk' => ['required', 'integer', 'min:0'],
            'placement_population' => ['required', 'integer', 'min:0'],
            'placement_year' => ['required', 'string', 'max:50'],
        ]);

        $oldData = [
            'placement_kk' => $upt->placement_kk,
            'placement_population' => $upt->placement_population,
            'placement_year' => $upt->placement_year,
        ];

        $upt->update($validated);

        // Catat di Audit Log
        AuditLog::log(
            'UPDATE_PLACEMENT_KK',
            'upt_locations',
            $upt->id,
            [
                'message' => "Memperbarui Data KK Penempatan Awal UPT-{$upt->upt_number} ({$upt->upt_name})",
                'old' => $oldData,
                'new' => $validated,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data KK Penempatan Awal UPT-{$upt->upt_number} ({$upt->upt_name}) berhasil disimpan!",
                'data' => [
                    'id' => $upt->id,
                    'upt_number' => $upt->upt_number,
                    'placement_kk' => $upt->placement_kk,
                    'placement_population' => $upt->placement_population,
                    'placement_year' => $upt->placement_year,
                    'ratio' => $upt->placement_kk > 0 ? round($upt->placement_population / $upt->placement_kk, 2) : 0,
                    'total_placement_kk' => (int) UptLocation::sum('placement_kk'),
                    'total_placement_pop' => (int) UptLocation::sum('placement_population'),
                ],
            ]);
        }

        return redirect()->back()->with('success', "Data KK Penempatan Awal UPT-{$upt->upt_number} ({$upt->upt_name}) berhasil diperbarui.");
    }

    /**
     * Ekspor Data Penempatan Awal ke format CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $query = UptLocation::with('regency')->orderBy('upt_number');

        if ($regencyId = $request->input('regency_id')) {
            if ($regencyId !== 'all') {
                $query->where('regency_id', (int) $regencyId);
            }
        }

        $records = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data_penempatan_awal_upt_kalsel_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 agar karakter simbolik terbaca di Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No. UPT',
                'Kabupaten',
                'Nama UPT Asal',
                'Desa Definitif',
                'Pola Usaha',
                'Tahun Penempatan Awal',
                'Jumlah KK Penempatan',
                'Jumlah Jiwa Penempatan',
                'Rata-rata Jiwa/KK',
                'Status Lahan Agraria',
            ]);

            foreach ($records as $u) {
                $ratio = $u->placement_kk > 0 ? round($u->placement_population / $u->placement_kk, 2) : 0;
                fputcsv($handle, [
                    'UPT-' . str_pad($u->upt_number, 3, '0', STR_PAD_LEFT),
                    $u->regency?->name ?? '-',
                    $u->upt_name,
                    $u->current_village_name,
                    $u->business_pattern,
                    $u->placement_year,
                    $u->placement_kk,
                    $u->placement_population,
                    $ratio,
                    strtoupper($u->issue_status),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
