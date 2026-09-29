<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegencyController extends Controller
{
    /**
     * Menampilkan daftar 9 kabupaten beserta kontrol visibilitas peta WebGIS
     */
    public function index(): View
    {
        $regencies = Regency::withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'placement_population', 'handover_kk', 'handover_population', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        $stats = [
            'total_regencies' => $regencies->count(),
            'visible_regencies' => $regencies->where('is_visible', true)->count(),
            'hidden_regencies' => $regencies->where('is_visible', false)->count(),
            'total_upts' => $regencies->sum('upt_locations_count'),
            'visible_upts' => $regencies->where('is_visible', true)->sum('upt_locations_count'),
        ];

        return view('admin.wilayah_peta_webgis.wilayah_peta_webgis', compact('regencies', 'stats'));
    }

    /**
     * Mengubah status tampil/sembunyi wilayah di peta WebGIS
     */
    public function toggle(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $regency = Regency::findOrFail($id);
        $oldState = (bool) $regency->is_visible;
        $newState = ! $oldState;

        $regency->update([
            'is_visible' => $newState,
        ]);

        AuditLog::log(
            'TOGGLE_REGENCY_VISIBILITY',
            'regencies',
            $regency->id,
            [
                'regency_name' => $regency->name,
                'code_roman' => $regency->code_roman,
                'previous_visibility' => $oldState,
                'current_visibility' => $newState,
                'status_label' => $newState ? 'Ditampilkan' : 'Disembunyikan',
            ]
        );

        $statusMsg = $newState
            ? "Wilayah Kabupaten {$regency->name} berhasil diaktifkan di Peta WebGIS."
            : "Wilayah Kabupaten {$regency->name} berhasil disembunyikan dari Peta WebGIS.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_visible' => $newState,
                'message' => $statusMsg,
                'regency' => [
                    'id' => $regency->id,
                    'name' => $regency->name,
                    'is_visible' => $newState,
                ],
            ]);
        }

        return back()->with('success', $statusMsg);
    }

    /**
     * Aksi masal: Tampilkan atau sembunyikan semua wilayah sekaligus
     */
    public function bulkVisibility(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'action' => 'required|in:show_all,hide_all',
        ]);

        $newVisibility = $request->action === 'show_all';

        Regency::query()->update([
            'is_visible' => $newVisibility,
        ]);

        AuditLog::log(
            'BULK_REGENCY_VISIBILITY',
            'regencies',
            0,
            [
                'action_type' => $request->action,
                'is_visible' => $newVisibility,
                'total_affected' => Regency::count(),
            ]
        );

        $message = $newVisibility
            ? 'Seluruh 9 Kabupaten berhasil diaktifkan dan ditampilkan di Peta WebGIS.'
            : 'Seluruh 9 Kabupaten berhasil disembunyikan dari Peta WebGIS.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_visible' => $newVisibility,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Memperbarui atribut kabupaten seperti warna poligon batas peta
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $regency = Regency::findOrFail($id);

        $validated = $request->validate([
            'map_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ], [
            'map_color.required' => 'Warna poligon wilayah wajib dipilih.',
            'map_color.regex' => 'Format warna harus berupa kode HEX valid (contoh: #8b5cf6).',
        ]);

        $oldColor = $regency->map_color;
        $regency->update([
            'map_color' => $validated['map_color'],
        ]);

        AuditLog::log(
            'UPDATE_REGENCY_COLOR',
            'regencies',
            $regency->id,
            [
                'regency_name' => $regency->name,
                'old_color' => $oldColor,
                'new_color' => $validated['map_color'],
            ]
        );

        $msg = "Warna batas wilayah Kabupaten {$regency->name} berhasil diperbarui menjadi {$validated['map_color']}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'map_color' => $validated['map_color'],
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Ekspor status publikasi spasial wilayah WebGIS ke PDF resmi A4 Portrait
     */
    public function exportPdf(Request $request)
    {
        $regencies = Regency::withCount('uptLocations')
            ->with(['uptLocations' => function ($q) {
                $q->select('id', 'regency_id', 'placement_kk', 'placement_population', 'handover_kk', 'handover_population', 'issue_status');
            }])
            ->orderBy('id')
            ->get();

        $stats = [
            'total_regencies' => $regencies->count(),
            'visible_regencies' => $regencies->where('is_visible', true)->count(),
            'hidden_regencies' => $regencies->where('is_visible', false)->count(),
            'total_upts' => $regencies->sum('upt_locations_count'),
            'visible_upts' => $regencies->where('is_visible', true)->sum('upt_locations_count'),
            'visible_placement_kk' => $regencies->where('is_visible', true)->sum(fn($r) => $r->uptLocations->sum('placement_kk')),
            'visible_handover_kk' => $regencies->where('is_visible', true)->sum(fn($r) => $r->uptLocations->sum('handover_kk')),
        ];

        $signCity = 'Banjarbaru';
        $signDate = date('d F Y');
        $orientation = 'portrait';

        $pdf = Pdf::loadView('admin.reports.pdf.regencies', compact(
            'regencies',
            'stats',
            'signCity',
            'signDate',
            'orientation'
        ))->setPaper('a4', 'portrait');

        $filename = 'Laporan_Status_Publikasi_Wilayah_WebGIS_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
