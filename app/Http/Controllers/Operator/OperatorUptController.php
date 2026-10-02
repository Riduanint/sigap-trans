<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorUptController extends Controller
{
    /**
     * Tampilkan data inventarisasi seluruh 124 UPT di Kalimantan Selatan
     * Operator memiliki wewenang lintas 9 kabupaten dengan filter dinamis
     */
    public function index(Request $request): View
    {
        $regencies = Regency::withCount('uptLocations')->orderBy('id')->get();

        $query = UptLocation::with('regency')->withCount('familyCards');

        // Filter Kabupaten (Semua atau Spesifik)
        $selectedRegencyId = $request->get('regency_id', 'all');
        if ($selectedRegencyId !== 'all' && is_numeric($selectedRegencyId)) {
            $query->where('regency_id', (int) $selectedRegencyId);
        }

        // Filter Status Legalitas Lahan
        $selectedStatus = $request->get('status', 'all');
        if ($selectedStatus !== 'all' && in_array($selectedStatus, ['clean', 'warning', 'critical'], true)) {
            $query->where('issue_status', $selectedStatus);
        }

        // Pencarian Kata Kunci
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('upt_name', 'ILIKE', "%{$search}%")
                  ->orWhere('current_village_name', 'ILIKE', "%{$search}%")
                  ->orWhere('business_pattern', 'ILIKE', "%{$search}%")
                  ->orWhereHas('regency', function ($rq) use ($search) {
                      $rq->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Pengurutan Default: Berdasarkan Wilayah Kabupaten & Nomor UPT
        $query->orderBy('regency_id')->orderBy('upt_number');

        // Paginasi: 10 per halaman
        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $uptLocations = $query->paginate($perPage)->withQueryString();

        // Statistik Keseluruhan (atau terfilter jika kabupaten dipilih)
        $baseStatQuery = UptLocation::query();
        if ($selectedRegencyId !== 'all' && is_numeric($selectedRegencyId)) {
            $baseStatQuery->where('regency_id', (int) $selectedRegencyId);
        }

        $stats = [
            'total_upt' => (clone $baseStatQuery)->count(),
            'total_placement_kk' => (clone $baseStatQuery)->sum('placement_kk'),
            'total_handover_kk' => (clone $baseStatQuery)->sum('handover_kk'),
            'clean_count' => (clone $baseStatQuery)->where('issue_status', 'clean')->count(),
            'warning_count' => (clone $baseStatQuery)->where('issue_status', 'warning')->count(),
            'critical_count' => (clone $baseStatQuery)->where('issue_status', 'critical')->count(),
        ];

        $currentRegency = ($selectedRegencyId !== 'all' && is_numeric($selectedRegencyId))
            ? $regencies->firstWhere('id', (int) $selectedRegencyId)
            : null;

        return view('operator.data_upt_wilayah.data_upt_wilayah', compact(
            'uptLocations',
            'regencies',
            'stats',
            'selectedRegencyId',
            'selectedStatus',
            'currentRegency',
            'perPage'
        ));
    }

    /**
     * Halaman registri warga penuh (varian Atlas) untuk UPT wilayah.
     * Delegasi ke FamilyCardManagementController@page — data, statistik,
     * dan pagination identik dengan halaman registri Super Admin.
     */
    public function registri(Request $request, int $id): \Illuminate\View\View
    {
        // UPT harus ada; akses registri operator dibatasi peran oleh middleware route.
        UptLocation::with('regency')->findOrFail($id);

        return app(\App\Http\Controllers\Admin\FamilyCardManagementController::class)
            ->page($request, $id);
    }
}
