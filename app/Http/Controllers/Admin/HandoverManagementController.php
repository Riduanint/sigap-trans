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

class HandoverManagementController extends Controller
{
    /**
     * Tampilkan antarmuka manajemen Serah Terima Pemda & Pertumbuhan KK
     */
    public function index(Request $request): View
    {
        $query = UptLocation::with(['regency', 'documents' => function ($q) {
            $q->where('document_type', 'BAST')->latest();
        }])->withCount('handoverFamilyCards');

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

        // Filter Status Serah Terima
        if ($handoverStatus = $request->input('handover_status')) {
            if ($handoverStatus === 'handed_over') {
                $query->where('handover_kk', '>', 0);
            } elseif ($handoverStatus === 'pending') {
                $query->where('handover_kk', '<=', 0);
            }
        }

        // Filter Tahun BAST Serah Terima
        if ($handoverYear = trim($request->input('handover_year', ''))) {
            if ($handoverYear !== 'all' && $handoverYear !== '') {
                $query->where('handover_year', 'ILIKE', "%{$handoverYear}%");
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

        // Agregasi Statistik Serah Terima Pemda & Pertumbuhan KK
        $totalPlacementKk = (int) UptLocation::sum('placement_kk');
        $totalHandoverKk = (int) UptLocation::sum('handover_kk');
        $totalHandoverPop = (int) UptLocation::sum('handover_population');

        $growthKk = $totalHandoverKk - $totalPlacementKk;
        $growthPct = $totalPlacementKk > 0 ? round(($growthKk / $totalPlacementKk) * 100, 1) : 0;
        $handedOverUpts = UptLocation::where('handover_kk', '>', 0)->count();

        $stats = [
            'total_handover_kk' => $totalHandoverKk,
            'total_handover_pop' => $totalHandoverPop,
            'total_placement_kk' => $totalPlacementKk,
            'growth_kk' => $growthKk,
            'growth_pct' => $growthPct,
            'handed_over_upts' => $handedOverUpts,
            'total_upt' => UptLocation::count(),
            'total_nominal_kk' => (int) \App\Models\UptFamilyCard::handover()->count(),
        ];

        $regencies = Regency::withCount('uptLocations')->orderBy('id')->get();
        $years = UptLocation::whereNotNull('handover_year')->where('handover_year', '!=', '')->select('handover_year')->distinct()->orderBy('handover_year')->pluck('handover_year');

        return view('admin.handovers.handovers', compact('uptLocations', 'regencies', 'years', 'stats'));
    }

    /**
     * Simpan / Perbarui Data KK Serah Terima Pemda suatu UPT
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $upt = UptLocation::findOrFail($id);

        $validated = $request->validate([
            'handover_kk' => ['required', 'integer', 'min:0'],
            'handover_population' => ['required', 'integer', 'min:0'],
            'handover_year' => ['nullable', 'string', 'max:50'],
        ]);

        $oldData = [
            'handover_kk' => $upt->handover_kk,
            'handover_population' => $upt->handover_population,
            'handover_year' => $upt->handover_year,
        ];

        $upt->update($validated);

        // Catat di Audit Log
        AuditLog::log(
            'UPDATE_HANDOVER_KK',
            'upt_locations',
            $upt->id,
            [
                'message' => "Memperbarui Data KK Serah Terima Pemda UPT-{$upt->upt_number} ({$upt->upt_name})",
                'old' => $oldData,
                'new' => $validated,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            $growth = $upt->handover_kk - $upt->placement_kk;
            $growthPercent = $upt->placement_kk > 0 ? round(($growth / $upt->placement_kk) * 100, 1) : 0;

            return response()->json([
                'success' => true,
                'message' => "Data KK Serah Terima Pemda UPT-{$upt->upt_number} ({$upt->upt_name}) berhasil disimpan!",
                'data' => [
                    'id' => $upt->id,
                    'upt_number' => $upt->upt_number,
                    'handover_kk' => $upt->handover_kk,
                    'handover_population' => $upt->handover_population,
                    'handover_year' => $upt->handover_year,
                    'growth' => $growth,
                    'growth_pct' => $growthPercent,
                    'total_handover_kk' => (int) UptLocation::sum('handover_kk'),
                    'total_handover_pop' => (int) UptLocation::sum('handover_population'),
                ],
            ]);
        }

        return redirect()->back()->with('success', "Data KK Serah Terima Pemda UPT-{$upt->upt_number} ({$upt->upt_name}) berhasil diperbarui.");
    }

    /**
     * Ekspor Data Serah Terima Pemda ke format CSV
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
            'Content-Disposition' => 'attachment; filename="data_serah_terima_pemda_upt_kalsel_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No. UPT',
                'Kabupaten',
                'Nama UPT Asal',
                'Desa Definitif',
                'Pola Usaha',
                'Tahun BAST Serah Terima',
                'KK Penempatan Awal',
                'KK Serah Terima Pemda',
                'Selisih Pertumbuhan (KK)',
                'Pertumbuhan (%)',
                'Keluarga Definitif (Jiwa)',
                'Status Serah Terima',
            ]);

            foreach ($records as $u) {
                $diff = $u->handover_kk - $u->placement_kk;
                $pct = $u->placement_kk > 0 ? round(($diff / $u->placement_kk) * 100, 1) : 0;
                $status = ($u->handover_kk > 0 || !empty($u->handover_year)) ? 'Sudah Serah Terima' : 'Belum Serah Terima';

                fputcsv($handle, [
                    'UPT-' . str_pad($u->upt_number, 3, '0', STR_PAD_LEFT),
                    $u->regency?->name ?? '-',
                    $u->upt_name,
                    $u->current_village_name,
                    $u->business_pattern,
                    $u->handover_year ?? '-',
                    $u->placement_kk,
                    $u->handover_kk,
                    ($diff >= 0 ? '+' : '') . $diff,
                    $pct . '%',
                    $u->handover_population,
                    $status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
