<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\UptChangeRequest;
use App\Models\UptLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorDashboardController extends Controller
{
    /**
     * Tampilkan dasbor utama operator wilayah (lintas 9 kabupaten)
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $regencies = \App\Models\Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->orderBy('id')
            ->get();
        $visibleRegencyIds = $regencies->pluck('id')->all();

        $rawRegencyId = $request->get('regency_id', 'all');
        $selectedRegencyId = ($rawRegencyId !== 'all' && in_array((int) $rawRegencyId, $visibleRegencyIds, true))
            ? (int) $rawRegencyId
            : 'all';

        $uptQuery = UptLocation::whereHas('regency', fn($q) => $q->where('is_visible', true))
            ->with('regency');

        if ($selectedRegencyId !== 'all') {
            $uptQuery->where('regency_id', $selectedRegencyId);
            $selectedRegency = $regencies->firstWhere('id', $selectedRegencyId);
        } else {
            $selectedRegency = null;
        }

        // Hitung statistik kependudukan & UPT lokal dari seluruh data sesuai filter wilayah
        $allUptLocations = (clone $uptQuery)->get();
        $totalUpt = $allUptLocations->count();
        $totalPlacementKk = $allUptLocations->sum('placement_kk');
        $totalPlacementPop = $allUptLocations->sum('placement_population');
        $totalHandoverKk = $allUptLocations->sum('handover_kk');
        $totalHandoverPop = $allUptLocations->sum('handover_population');

        $cleanCount = $allUptLocations->where('issue_status', 'clean')->count();
        $warningCount = $allUptLocations->where('issue_status', 'warning')->count();
        $criticalCount = $allUptLocations->where('issue_status', 'critical')->count();

        // Paginate 10 per halaman agar identik dengan tabel Super Admin (Palet MP072)
        $uptLocations = $uptQuery->orderBy('regency_id')->orderBy('upt_number')->paginate(10)->withQueryString();

        // Statistik draf pengajuan usulan oleh operator
        $requestsCount = [
            'total' => UptChangeRequest::where('user_id', $user->id)->count(),
            'pending' => UptChangeRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => UptChangeRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected' => UptChangeRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        // Riwayat draf usulan terbaru
        $recentRequests = UptChangeRequest::with(['uptLocation.regency', 'reviewer'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('operator.dashboard_utama.dashboard_utama', compact(
            'user',
            'regencies',
            'selectedRegency',
            'selectedRegencyId',
            'uptLocations',
            'totalUpt',
            'totalPlacementKk',
            'totalHandoverKk',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'requestsCount',
            'recentRequests'
        ));
    }
}
