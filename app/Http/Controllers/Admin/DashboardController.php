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
        $request->validate(['regency_id' => ['nullable', 'integer', 'exists:regencies,id']]);
        $regencies = Regency::orderBy('name')->get();
        $locations = UptLocation::with('regency')
            ->when($request->filled('regency_id'), fn ($query) => $query->where('regency_id', $request->integer('regency_id')))
            ->orderBy('upt_number')->get();
        $pendingQuery = UptChangeRequest::with(['uptLocation.regency', 'user'])
            ->where('status', 'pending')
            ->when($request->filled('regency_id'), fn ($query) => $query->whereHas('uptLocation', fn ($upt) => $upt->where('regency_id', $request->integer('regency_id'))));
        $pendingApprovals = (clone $pendingQuery)->count();
        $pendingRequests = $pendingQuery->oldest()->orderBy('id')->limit(5)->get();
        $priorityCases = $locations->where('issue_status', 'critical');
        $recentLogs = AuditLog::with('user')->latest()->limit(5)->get();
        // The authenticated workspace includes unpublished regencies as well.
        $mapLocations = $locations->map(fn ($upt) => [
            'id' => $upt->id,
            'upt_number' => $upt->upt_number,
            'upt_name' => $upt->upt_name,
            'current_village_name' => $upt->current_village_name,
            'regency_name' => $upt->regency?->name,
            'issue_status' => $upt->issue_status,
            'latitude' => $upt->latitude,
            'longitude' => $upt->longitude,
            'detail_url' => route('admin.upt.show', $upt->id),
        ])->values();

        return view('admin.dashboard_utama.dashboard_utama', compact('regencies', 'locations', 'pendingApprovals', 'pendingRequests', 'priorityCases', 'recentLogs', 'mapLocations'));
    }

    /**
     * Cetak Ringkasan Eksekutif Dashboard Utama format PDF A4 Portrait
     */
    public function exportPdf(Request $request)
    {
        $request->validate(['regency_id' => ['nullable', 'integer', 'exists:regencies,id']]);
        $visibleRegencies = Regency::query()
            ->when($request->user()->role !== 'super_admin', fn ($query) => $query->where('is_visible', true))
            ->when($request->filled('regency_id'), fn ($query) => $query->where('id', $request->integer('regency_id')))
            ->orderBy('id')->get();
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

        $regencies = Regency::whereIn('id', $visibleRegencyIds)
            ->withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        if ($request->user()->role === 'super_admin') {
            $filterRegencyName = $request->filled('regency_id')
                ? 'Kabupaten ' . $visibleRegencies->first()?->name
                : 'Seluruh wilayah administrasi (' . $visibleRegencies->count() . ' kabupaten)';
        } elseif ($visibleRegencies->count() === 9) {
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
