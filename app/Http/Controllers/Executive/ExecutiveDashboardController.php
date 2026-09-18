<?php

namespace App\Http\Controllers\Executive;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\View\View;

class ExecutiveDashboardController extends Controller
{
    /**
     * Tampilkan ruang kendali eksekutif pimpinan
     */
    public function index(): View
    {
        $user = auth()->user();

        // 4 KPI Utama Provinsi
        $totalUpt = UptLocation::count();
        $totalPlacementKk = UptLocation::sum('placement_kk');
        $totalPlacementPop = UptLocation::sum('placement_population');
        $totalHandoverKk = UptLocation::sum('handover_kk');
        $totalHandoverPop = UptLocation::sum('handover_population');

        $cleanCount = UptLocation::where('issue_status', 'clean')->count();
        $warningCount = UptLocation::where('issue_status', 'warning')->count();
        $criticalCount = UptLocation::where('issue_status', 'critical')->count();

        // Ambil 6 kasus prioritas sengketa (Pin Merah) lengkap dengan koordinat spasial
        $criticalCases = UptLocation::with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('upt_number')
            ->get();

        // Agregasi per 9 kabupaten
        $regenciesSummary = Regency::withCount('uptLocations')
            ->withSum('uptLocations as total_placement_kk', 'placement_kk')
            ->withSum('uptLocations as total_handover_kk', 'handover_kk')
            ->orderBy('id')
            ->get();

        return view('executive.dashboard', compact(
            'user',
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'criticalCases',
            'regenciesSummary'
        ));
    }
}
