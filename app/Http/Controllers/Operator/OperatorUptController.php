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
     * Tampilkan data inventarisasi UPT wilayah publikasi yang diaktifkan oleh Super Admin
     */
    public function index(Request $request): View
    {
        $regencies = Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->orderBy('id')
            ->get();
        $visibleRegencyIds = $regencies->pluck('id')->all();

        $query = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))
            ->with('regency')
            ->withCount('familyCards');

        // Filter Kabupaten (Hanya kabupaten yang diaktifkan)
        $rawRegencyId = $request->get('regency_id', 'all');
        $selectedRegencyId = ($rawRegencyId !== 'all' && in_array((int) $rawRegencyId, $visibleRegencyIds, true))
            ? (int) $rawRegencyId
            : 'all';

        if ($selectedRegencyId !== 'all') {
            $query->where('regency_id', $selectedRegencyId);
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

        // Statistik Keseluruhan (dibatasi wilayah aktif atau terfilter jika kabupaten dipilih)
        $baseStatQuery = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true));
        if ($selectedRegencyId !== 'all') {
            $baseStatQuery->where('regency_id', $selectedRegencyId);
        }

        $stats = [
            'total_upt' => (clone $baseStatQuery)->count(),
            'total_placement_kk' => (clone $baseStatQuery)->sum('placement_kk'),
            'total_handover_kk' => (clone $baseStatQuery)->sum('handover_kk'),
            'clean_count' => (clone $baseStatQuery)->where('issue_status', 'clean')->count(),
            'warning_count' => (clone $baseStatQuery)->where('issue_status', 'warning')->count(),
            'critical_count' => (clone $baseStatQuery)->where('issue_status', 'critical')->count(),
        ];

        $currentRegency = ($selectedRegencyId !== 'all')
            ? $regencies->firstWhere('id', $selectedRegencyId)
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
     * Halaman registri warga penuh (varian Atlas) untuk UPT wilayah aktif.
     * Delegasi ke FamilyCardManagementController@page — data, statistik,
     * dan pagination identik dengan halaman registri Super Admin.
     */
    public function registri(Request $request, int $id): \Illuminate\View\View
    {
        // UPT harus berada dalam wilayah aktif; akses registri operator dibatasi peran oleh middleware route.
        UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))
            ->with('regency')
            ->findOrFail($id);

        return app(\App\Http\Controllers\Admin\FamilyCardManagementController::class)
            ->page($request, $id);
    }
}
