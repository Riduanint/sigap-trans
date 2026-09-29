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
     * Cetak Laporan Resmi Eksekutif format PDF Berstandar Kedinasan (Modular Sections)
     */
    public function exportPdf(Request $request)
    {
        // 1. Identifikasi Seksi yang Dipilih
        $allSections = ['sec_kop', 'sec_kpi', 'sec_regency', 'sec_critical', 'sec_upt_table', 'sec_signature'];
        $sections = $request->input('sections', $allSections);
        if (is_string($sections)) {
            $sections = explode(',', $sections);
        }
        $sections = array_filter(array_map('trim', (array) $sections));
        if (empty($sections)) {
            $sections = $allSections;
        }

        // 2. Filter Wilayah & Status Lahan
        $regencyId = $request->filled('regency_id') && $request->regency_id !== 'all' ? (int) $request->regency_id : null;
        $status = $request->filled('status') && $request->status !== 'all' ? $request->status : null;

        $targetRegency = $regencyId ? Regency::find($regencyId) : null;
        $filterRegencyName = $targetRegency ? "Kabupaten {$targetRegency->name}" : 'Seluruh Wilayah (9 Kabupaten)';
        $filterStatusLabel = match ($status) {
            'clean' => 'Clean & Clear',
            'warning' => 'Waspada / Monitoring',
            'critical' => 'Kritis / Prioritas Mediasi',
            default => 'Semua Status Lahan',
        };

        // 3. Query Agregasi Statistik Provinsi / Wilayah Terpilih
        $baseQuery = UptLocation::query();
        if ($regencyId) {
            $baseQuery->where('regency_id', $regencyId);
        }
        if ($status) {
            $baseQuery->where('issue_status', $status);
        }

        $totalUpt = (clone $baseQuery)->count();
        $totalPlacementKk = (int) (clone $baseQuery)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $baseQuery)->sum('placement_population');
        $totalHandoverKk = (int) (clone $baseQuery)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $baseQuery)->sum('handover_population');
        $growthKk = $totalHandoverKk - $totalPlacementKk;
        $growthPop = $totalHandoverPop - $totalPlacementPop;

        $cleanCount = (clone $baseQuery)->where('issue_status', 'clean')->count();
        $warningCount = (clone $baseQuery)->where('issue_status', 'warning')->count();
        $criticalCount = (clone $baseQuery)->where('issue_status', 'critical')->count();

        // 4. Data Tabel Rinci UPT (Hanya jika seksi diaktifkan)
        $locations = collect();
        if (in_array('sec_upt_table', $sections)) {
            $uptQuery = UptLocation::with('regency')->orderBy('upt_number');
            if ($regencyId) {
                $uptQuery->where('regency_id', $regencyId);
            }
            if ($status) {
                $uptQuery->where('issue_status', $status);
            }
            $locations = $uptQuery->get();
        }

        // 5. Data Kasus Kritis (Hanya jika seksi diaktifkan)
        $criticalCases = collect();
        if (in_array('sec_critical', $sections)) {
            $critQuery = UptLocation::with('regency')->where('issue_status', 'critical')->orderBy('upt_number');
            if ($regencyId) {
                $critQuery->where('regency_id', $regencyId);
            }
            $criticalCases = $critQuery->get();
        }

        // 6. Data Matriks Agregasi Kabupaten (Hanya jika seksi diaktifkan)
        $regencies = collect();
        if (in_array('sec_regency', $sections)) {
            $regQuery = Regency::where('is_visible', true)
                ->withCount('uptLocations')
                ->with(['uptLocations' => function ($q) {
                    $q->select('id', 'regency_id', 'placement_kk', 'handover_kk', 'issue_status');
                }])
                ->orderBy('id');
            if ($regencyId) {
                $regQuery->where('id', $regencyId);
            }
            $regencies = $regQuery->get();
        }

        // 7. Pengaturan Tata Letak Dokumen (Orientasi & Ukuran Kertas)
        $orientation = strtolower($request->input('orientation', 'landscape'));
        if (! in_array($orientation, ['landscape', 'portrait'])) {
            $orientation = 'landscape';
        }

        $paperSize = strtolower($request->input('paper_size', 'a4'));
        if ($paperSize === 'folio' || $paperSize === 'f4') {
            // Folio / F4 (215mm x 330mm = 609.45pt x 935.43pt)
            $paper = [0, 0, 609.45, 935.43];
        } else {
            $paper = 'a4';
        }

        // 8. Data Penandatangan Kedinasan
        $signerTitle = $request->input('signer_title', 'Kepala Bidang Ketransmigrasian');
        $signerName = $request->input('signer_name', 'Hj. Ina Yuliani, S.Sos, M.Si, M.IP');
        $signerNip = $request->input('signer_nip', '19690729 199010 2 001');
        $signCity = $request->input('sign_city', 'Banjarmasin');
        $signDate = $request->input('sign_date', date('d F Y'));

        // 9. Audit Log
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'PRINT_PDF_MODULAR_REPORT',
                'target_table' => 'upt_locations',
                'target_id' => 'ALL',
                'details' => [
                    'sections' => $sections,
                    'regency_id' => $regencyId,
                    'status' => $status,
                    'orientation' => $orientation,
                    'paper_size' => $paperSize,
                ],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            // Abaikan kesalahan pencatatan log
        }

        // 10. Generate PDF dengan DomPDF
        $pdf = Pdf::loadView('admin.reports.pdf_template', compact(
            'sections',
            'locations',
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'growthKk',
            'growthPop',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'regencies',
            'criticalCases',
            'orientation',
            'paperSize',
            'signerTitle',
            'signerName',
            'signerNip',
            'signCity',
            'signDate',
            'filterRegencyName',
            'filterStatusLabel'
        ))->setPaper($paper, $orientation);

        $safeRegName = $targetRegency ? preg_replace('/[^A-Za-z0-9]/', '_', $targetRegency->name) : 'Kalsel';
        $filename = "Laporan_SIGAP_TRANS_{$safeRegName}_" . date('Ymd_His') . '.pdf';

        if ($request->input('action') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
