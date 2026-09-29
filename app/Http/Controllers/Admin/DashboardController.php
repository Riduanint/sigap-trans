<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptChangeRequest;
use App\Models\UptDocument;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalUpt = UptLocation::count();
        $totalPlacementKk = (int) UptLocation::sum('placement_kk');
        $totalPlacementPop = (int) UptLocation::sum('placement_population');
        $totalHandoverKk = (int) UptLocation::sum('handover_kk');
        $totalHandoverPop = (int) UptLocation::sum('handover_population');

        $cleanCount = UptLocation::where('issue_status', 'clean')->count();
        $warningCount = UptLocation::where('issue_status', 'warning')->count();
        $criticalCount = UptLocation::where('issue_status', 'critical')->count();
        $totalDocuments = UptDocument::count();

        // 6 Kasus Prioritas Mediasi (Pin Merah)
        $priorityCases = UptLocation::with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('regency_id')
            ->get();

        // Distribusi 9 Kabupaten
        $regencies = Regency::withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        // Aktivitas Pengajuan Draf Terbaru & Antrean (Sesuai Wireframe 3.1)
        $recentChangeRequests = UptChangeRequest::with(['uptLocation.regency', 'user.regency'])
            ->latest()
            ->take(5)
            ->get();
        $pendingApprovals = UptChangeRequest::where('status', 'pending')->count();

        // Breakdown Pola Usaha
        $businessPatterns = UptLocation::selectRaw('business_pattern, count(*) as count, sum(placement_kk) as total_kk')
            ->groupBy('business_pattern')
            ->orderByDesc('count')
            ->get();

        // Log Audit Terbaru
        $recentLogs = AuditLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard_utama.dashboard_utama', compact(
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'totalDocuments',
            'priorityCases',
            'regencies',
            'recentChangeRequests',
            'pendingApprovals',
            'businessPatterns',
            'recentLogs'
        ));
    }

    /**
     * Cetak Ringkasan Eksekutif Dashboard Utama format PDF A4 Portrait
     */
    public function exportPdf(Request $request)
    {
        $visibleRegencies = Regency::where('is_visible', true)->orderBy('id')->get();
        $visibleRegencyIds = $visibleRegencies->pluck('id')->toArray();

        $uptQuery = UptLocation::whereIn('regency_id', $visibleRegencyIds);

        $totalUpt = (clone $uptQuery)->count();
        $totalPlacementKk = (int) (clone $uptQuery)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $uptQuery)->sum('placement_population');
        $totalHandoverKk = (int) (clone $uptQuery)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $uptQuery)->sum('handover_population');

        $cleanCount = (clone $uptQuery)->where('issue_status', 'clean')->count();
        $warningCount = (clone $uptQuery)->where('issue_status', 'warning')->count();
        $criticalCount = (clone $uptQuery)->where('issue_status', 'critical')->count();

        $priorityCases = (clone $uptQuery)->with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('regency_id')
            ->get();

        $regencies = Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        if ($visibleRegencies->count() === 9) {
            $filterRegencyName = 'Seluruh Wilayah (9 Kabupaten Binaan Kalsel)';
        } else {
            $filterRegencyName = "{$visibleRegencies->count()} Kabupaten Terpublikasi (" . $visibleRegencies->pluck('name')->implode(', ') . ')';
        }

        $signCity = 'Banjarbaru';
        $signDate = date('d F Y');
        $orientation = 'portrait';

        $pdf = Pdf::loadView('admin.reports.pdf.dashboard', compact(
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'priorityCases',
            'regencies',
            'filterRegencyName',
            'signCity',
            'signDate',
            'orientation'
        ))->setPaper('a4', 'portrait');

        $filename = 'Ringkasan_Eksekutif_Dashboard_Kalsel_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
