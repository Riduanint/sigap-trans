<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptDocument;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LandCertificateController extends Controller
{
    /**
     * Tampilkan monitoring & legalisasi sertipikat tanah UPT (BPN / Agraria)
     */
    public function index(Request $request): View
    {
        $regencies = Regency::orderBy('id')->get();

        $query = UptLocation::with(['regency', 'documents']);

        // Filter Kabupaten
        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        // Filter Status SHM
        if ($request->filled('shm_status')) {
            $status = $request->shm_status;
            if ($status === '100% SHM') {
                $query->where(function ($q) {
                    $q->where('shm_status', '100% SHM')
                      ->orWhere('shm_status', 'Sudah SHM')
                      ->orWhereNull('shm_status');
                });
            } else {
                $query->where('shm_status', $status);
            }
        }

        // Filter Status Isu/Kendala
        if ($request->filled('issue_status')) {
            $query->where('issue_status', $request->issue_status);
        }

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('upt_name', 'ilike', "%{$search}%")
                  ->orWhere('current_village_name', 'ilike', "%{$search}%")
                  ->orWhere('issue_note', 'ilike', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('upt_number', (int) $search);
                } elseif (preg_match('/(?:upt[-_ ]?)(\d+)/i', $search, $matches)) {
                    $q->orWhere('upt_number', (int) $matches[1]);
                }
            });
        }

        $uptLocations = $query->orderBy('upt_number')->paginate(10)->withQueryString();

        // 4 KPI Utama Agraria & Legalisasi Tanah
        $totalUpt = UptLocation::count();
        $shm100Count = UptLocation::where(function ($q) {
            $q->where('shm_status', '100% SHM')
              ->orWhere('shm_status', 'Sudah SHM')
              ->orWhereNull('shm_status');
        })->count();

        $shmPartialCount = UptLocation::where(function ($q) {
            $q->where('shm_status', 'Sebagian SHM')
              ->orWhere('shm_status', 'Proses BPN');
        })->count();

        $shmNoneCount = UptLocation::where('shm_status', 'Belum SHM')->count();
        $criticalLandCount = UptLocation::where('issue_status', 'critical')->count();
        $totalBastFiles = UptDocument::count();

        return view('admin.sertifikat_tanah.sertifikat_tanah', compact(
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

    /**
     * Perbarui status legalitas & catatan pertanahan UPT
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $upt = UptLocation::with('regency')->findOrFail($id);

        $validated = $request->validate([
            'shm_status' => ['required', 'string', 'max:50'],
            'issue_status' => ['required', 'string', 'in:clean,warning,critical'],
            'notes_issue' => ['nullable', 'string', 'max:1000'],
        ]);

        $old = [
            'shm_status' => $upt->shm_status,
            'issue_status' => $upt->issue_status,
            'notes_issue' => $upt->issue_note,
        ];

        $upt->update([
            'shm_status' => $validated['shm_status'],
            'issue_status' => $validated['issue_status'],
            'issue_note' => $validated['notes_issue'] ?? $upt->issue_note,
        ]);

        AuditLog::log(
            'UPDATE_LAND_CERTIFICATE_STATUS',
            'upt_locations',
            $upt->id,
            [
                'message' => "Pembaruan status legalitas sertipikat tanah UPT-{$upt->upt_number} ({$upt->upt_name})",
                'old' => $old,
                'new' => $validated,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Status pertanahan UPT-{$upt->upt_number} ({$upt->upt_name}) berhasil diperbarui!",
            'data' => $upt,
        ]);
    }

    /**
     * Ekspor Data Status Pertanahan UPT ke CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $query = UptLocation::with('regency')->orderBy('upt_number');

        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->filled('shm_status')) {
            $query->where('shm_status', $request->shm_status);
        }

        if ($request->filled('issue_status')) {
            $query->where('issue_status', $request->issue_status);
        }

        $upts = $query->get();

        $filename = "rekap_status_sertipikasi_tanah_transmigrasi_kalsel_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($upts) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'No',
                'No UPT',
                'Nama UPT',
                'Kabupaten',
                'Desa / Kelurahan Saat Ini',
                'Tahun Penempatan',
                'Tahun Serah Terima',
                'KK Penempatan',
                'KK Serah Terima',
                'Status Sertipikat SHM',
                'Kategori Isu',
                'Catatan Hambatan / Sengketa / Koordinasi BPN',
            ]);

            $no = 1;
            foreach ($upts as $u) {
                fputcsv($handle, [
                    $no++,
                    'UPT-' . str_pad($u->upt_number, 3, '0', STR_PAD_LEFT),
                    $u->upt_name,
                    $u->regency?->name ?? '-',
                    $u->current_village_name ?? '-',
                    $u->placement_year ?? '-',
                    $u->handover_year ?? '-',
                    $u->placement_kk ?? 0,
                    $u->handover_kk ?? 0,
                    $u->shm_status ?? '100% SHM',
                    strtoupper($u->issue_status ?? 'clean'),
                    $u->issue_note ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Cetak Laporan Monitoring Sertipikat Tanah & Hak Milik BPN format PDF A4 Landscape
     */
    public function exportPdf(Request $request)
    {
        $query = UptLocation::with(['regency', 'documents']);

        // Filter Kabupaten
        $targetRegency = null;
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
            $targetRegency = Regency::find($request->regency_id);
        } else {
            $query->whereHas('regency', fn($q) => $q->where('is_visible', true));
        }

        // Filter Status SHM
        if ($request->filled('shm_status')) {
            $status = $request->shm_status;
            if ($status === '100% SHM') {
                $query->where(function ($q) {
                    $q->where('shm_status', '100% SHM')
                      ->orWhere('shm_status', 'Sudah SHM')
                      ->orWhereNull('shm_status');
                });
            } else {
                $query->where('shm_status', $status);
            }
        }

        // Filter Status Isu/Kendala
        if ($request->filled('issue_status')) {
            $query->where('issue_status', $request->issue_status);
        }

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('upt_name', 'ilike', "%{$search}%")
                  ->orWhere('current_village_name', 'ilike', "%{$search}%")
                  ->orWhere('issue_note', 'ilike', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('upt_number', (int) $search);
                } elseif (preg_match('/(?:upt[-_ ]?)(\d+)/i', $search, $matches)) {
                    $q->orWhere('upt_number', (int) $matches[1]);
                }
            });
        }

        $locations = $query->orderBy('upt_number')->get();

        if ($targetRegency) {
            $filterRegencyName = "Kabupaten {$targetRegency->name}";
        } else {
            $visibleRegencies = Regency::where('is_visible', true)->orderBy('id')->get();
            if ($visibleRegencies->count() === 9) {
                $filterRegencyName = 'Seluruh Wilayah (9 Kabupaten Binaan)';
            } else {
                $filterRegencyName = "{$visibleRegencies->count()} Kabupaten Terpublikasi (" . $visibleRegencies->pluck('name')->implode(', ') . ')';
            }
        }
        $filterShmLabel = $request->input('shm_status') ?: 'Semua Status SHM';
        $signCity = 'Banjarbaru';
        $signDate = date('d F Y');
        $orientation = 'landscape';

        $pdf = Pdf::loadView('admin.reports.pdf.land_certificates', compact(
            'locations',
            'filterRegencyName',
            'filterShmLabel',
            'signCity',
            'signDate',
            'orientation'
        ))->setPaper('a4', 'landscape');

        $safeName = $targetRegency ? preg_replace('/[^A-Za-z0-9]/', '_', $targetRegency->name) : 'Kalsel';
        $filename = "Laporan_Sertifikasi_Tanah_UPT_{$safeName}_" . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
