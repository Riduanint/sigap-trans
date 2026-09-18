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
     * Tampilkan dasbor utama operator kabupaten
     */
    public function index(): View
    {
        $user = auth()->user();
        $regencyId = $user->regency_id ?? 1; // Default ke Tapin jika belum di-set
        $regency = $user->regency ?? \App\Models\Regency::find($regencyId);

        // Ambil daftar UPT spesifik kabupaten operator ini
        $uptLocations = UptLocation::where('regency_id', $regencyId)
            ->orderBy('upt_number')
            ->get();

        // Statistik kependudukan & UPT lokal
        $totalUpt = $uptLocations->count();
        $totalPlacementKk = $uptLocations->sum('placement_kk');
        $totalHandoverKk = $uptLocations->sum('handover_kk');

        $cleanCount = $uptLocations->where('issue_status', 'clean')->count();
        $warningCount = $uptLocations->where('issue_status', 'warning')->count();
        $criticalCount = $uptLocations->where('issue_status', 'critical')->count();

        // Statistik draf pengajuan usulan oleh operator
        $requestsCount = [
            'total' => UptChangeRequest::where('user_id', $user->id)->count(),
            'pending' => UptChangeRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => UptChangeRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected' => UptChangeRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        // Riwayat draf usulan terbaru
        $recentRequests = UptChangeRequest::with(['uptLocation', 'reviewer'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('operator.dashboard', compact(
            'user',
            'regency',
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
