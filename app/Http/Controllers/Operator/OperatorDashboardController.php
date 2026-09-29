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
        $regencies = \App\Models\Regency::withCount('uptLocations')->orderBy('id')->get();

        $selectedRegencyId = $request->get('regency_id', 'all');

        $uptQuery = UptLocation::with('regency');
        if ($selectedRegencyId !== 'all' && is_numeric($selectedRegencyId)) {
            $uptQuery->where('regency_id', (int) $selectedRegencyId);
            $selectedRegency = $regencies->firstWhere('id', (int) $selectedRegencyId);
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
