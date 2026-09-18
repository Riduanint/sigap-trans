<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Tampilkan rekam jejak forensik aktivitas sistem
     */
    public function index(Request $request): View
    {
        $users = User::orderBy('name')->get();

        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                  ->orWhere('target_table', 'ilike', "%{$search}%")
                  ->orWhere('target_id', 'ilike', "%{$search}%")
                  ->orWhere('ip_address', 'ilike', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        // Ambil daftar unik aksi sistem untuk dropdown filter
        $availableActions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit_logs.audit', compact('logs', 'users', 'availableActions'));
    }

    /**
     * Ekspor rekam jejak audit ke berkas CSV resmi
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $fileName = 'audit_logs_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID Log',
                'Waktu Kejadian (WITA)',
                'Nama Aparatur',
                'Peran Hak Akses',
                'Aksi Sistem',
                'Tabel Sasaran',
                'ID Sasaran',
                'Alamat IP',
                'User Agent / Peramban',
                'Rincian Forensik (JSON)',
            ]);

            $query->latest('created_at')->chunk(200, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                        $log->user->name ?? 'Sistem / Anonim',
                        $log->user->role ?? 'system',
                        $log->action,
                        $log->target_table,
                        $log->target_id,
                        $log->ip_address,
                        $log->user_agent,
                        json_encode($log->details, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}
