<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UptLocationsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Unduh Laporan Rekapitulasi 124 UPT format Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $regencyId = $request->filled('regency_id') && $request->regency_id !== 'all' ? (int) $request->regency_id : null;
        $status = $request->filled('status') && $request->status !== 'all' ? $request->status : null;

        // Audit Log
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'EXPORT_EXCEL_REPORT',
                'target_table' => 'upt_locations',
                'target_id' => 'ALL',
                'details' => ['regency_id' => $regencyId, 'status' => $status],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            //
        }

        $fileName = 'Rekapitulasi_124_UPT_Transmigrasi_Kalsel_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new UptLocationsExport($regencyId, $status), $fileName);
    }

    /**
     * Cetak Laporan Resmi Eksekutif format PDF Berstandar Kedinasan
     */
    public function exportPdf(Request $request)
    {
        $query = UptLocation::with('regency')->orderBy('upt_number');

        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('issue_status', $request->status);
        }

        $locations = $query->get();
        $totalPlacementKk = (int) $locations->sum('placement_kk');
        $totalPlacementPop = (int) $locations->sum('placement_population');
        $totalHandoverKk = (int) $locations->sum('handover_kk');
        $totalHandoverPop = (int) $locations->sum('handover_population');

        $regencies = Regency::withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        // Audit Log
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'PRINT_PDF_REPORT',
                'target_table' => 'upt_locations',
                'target_id' => 'ALL',
                'details' => ['count' => $locations->count()],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            //
        }

        $pdf = Pdf::loadView('admin.reports.pdf_template', compact(
            'locations',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'regencies'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Resmi_Rekapitulasi_UPT_Kalsel.pdf');
    }
}
