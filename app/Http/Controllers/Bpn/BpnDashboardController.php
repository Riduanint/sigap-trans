<?php

namespace App\Http\Controllers\Bpn;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\UptDocument;
use App\Models\UptLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BpnDashboardController extends Controller
{
    /**
     * Tampilkan portal verifikasi pertanahan & SHM Kanwil BPN
     */
    public function index(Request $request): View
    {
        $regencies = Regency::orderBy('id')->get();

        $query = UptLocation::with(['regency', 'documents']);

        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->filled('shm_status')) {
            $query->where('shm_status', $request->shm_status);
        }

        if ($request->filled('issue_status')) {
            $query->where('issue_status', $request->issue_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('upt_name', 'ilike', "%{$search}%")
                  ->orWhere('current_village_name', 'ilike', "%{$search}%");
            });
        }

        $uptLocations = $query->orderBy('upt_number')->paginate(15)->withQueryString();

        // Statistik Pertanahan
        $totalUpt = UptLocation::count();
        $shm100Count = UptLocation::where('shm_status', '100% SHM')->orWhereNull('shm_status')->count();
        $shmPartialCount = UptLocation::where('shm_status', 'Sebagian SHM')->count();
        $shmNoneCount = UptLocation::where('shm_status', 'Belum SHM')->count();
        $criticalLandCount = UptLocation::where('issue_status', 'critical')->count();
        $totalBastFiles = UptDocument::count();

        return view('bpn.dashboard', compact(
            'uptLocations',
            'regencies',
            'totalUpt',
            'shm100Count',
            'shmPartialCount',
            'shmNoneCount',
            'criticalLandCount',
            'totalBastFiles'
        ));
    }
}
