@extends('admin.reports.pdf.layout')

@section('report_title', 'LAPORAN REKAM JEJAK AUDIT SISTEM (AUDIT TRAIL)')
@section('report_subtitle', 'Catatan Forensik Keamanan, Jejak Pengguna, dan Riwayat Modifikasi Data SIGAP-TRANS')

@section('report_metadata')
    Total Entri Log: <strong>{{ $logs->count() }} Aktivitas Tercatat</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- Ringkasan Statistik Audit Trail -->
    @php
        $countCreate = $logs->filter(fn($l) => str_contains($l->action, 'CREATE') || str_contains($l->action, 'STORE') || str_contains($l->action, 'ADD'))->count();
        $countUpdate = $logs->filter(fn($l) => str_contains($l->action, 'UPDATE') || str_contains($l->action, 'EDIT') || str_contains($l->action, 'SYNC'))->count();
        $countDelete = $logs->filter(fn($l) => str_contains($l->action, 'DELETE') || str_contains($l->action, 'DESTROY') || str_contains($l->action, 'REJECT'))->count();
        $countExport = $logs->filter(fn($l) => str_contains($l->action, 'EXPORT') || str_contains($l->action, 'PRINT') || str_contains($l->action, 'DOWNLOAD'))->count();
    @endphp
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Aktivitas Penambahan (Create)</span>
                <span class="summary-val" style="color: #065f46;">{{ $countCreate }} Aksi</span>
                <span style="font-size: 6pt; color: #64748b;">Entri Data Baru</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Aktivitas Perubahan (Update)</span>
                <span class="summary-val" style="color: #0284c7;">{{ $countUpdate }} Aksi</span>
                <span style="font-size: 6pt; color: #64748b;">Pembaruan & Sinkronisasi</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Aktivitas Penghapusan (Delete)</span>
                <span class="summary-val" style="color: #9f1239;">{{ $countDelete }} Aksi</span>
                <span style="font-size: 6pt; color: #64748b;">Penghapusan / Penolakan</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Ekspor / Cetak Berkas</span>
                <span class="summary-val" style="color: #92400e;">{{ $countExport }} Aksi</span>
                <span style="font-size: 6pt; color: #64748b;">Unduh PDF / Excel / CSV</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rinci Audit Trail -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 13%;">Waktu (WITA)</th>
                <th style="width: 15%; text-align: left;">Nama Pengguna / Pelaksana</th>
                <th style="width: 12%;">Peran (Role)</th>
                <th style="width: 18%; text-align: left;">Tipe Aksi Sistem</th>
                <th style="width: 13%; text-align: left;">Target Modul</th>
                <th style="width: 11%;">Alamat IP</th>
                <th style="width: 15%; text-align: left;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $idx => $log)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold" style="font-size: 6pt;">
                        {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                    </td>
                    <td class="text-left font-bold" style="font-size: 6pt;">
                        {{ $log->user?->name ?? 'Sistem Otomatis' }}
                    </td>
                    <td class="text-center" style="font-size: 5.5pt;">
                        {{ $log->user?->role ?? 'system' }}
                    </td>
                    <td class="text-left font-bold" style="font-size: 6pt;">
                        @if(str_contains($log->action, 'DELETE') || str_contains($log->action, 'DESTROY'))
                            <span style="color: #9f1239;">{{ $log->action }}</span>
                        @elseif(str_contains($log->action, 'UPDATE'))
                            <span style="color: #0284c7;">{{ $log->action }}</span>
                        @elseif(str_contains($log->action, 'CREATE'))
                            <span style="color: #065f46;">{{ $log->action }}</span>
                        @else
                            {{ $log->action }}
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 6pt;">
                        {{ $log->target_table ? $log->target_table . ($log->target_id ? " (#{$log->target_id})" : '') : '-' }}
                    </td>
                    <td class="text-center" style="font-size: 5.5pt; color: #64748b;">
                        {{ $log->ip_address ?: '127.0.0.1' }}
                    </td>
                    <td class="text-left" style="font-size: 5.5pt; color: #475569;">
                        @if(is_array($log->details))
                            {{ Str::limit(json_encode($log->details), 45) }}
                        @else
                            {{ Str::limit($log->details, 45) ?: '-' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada riwayat aktivitas log audit yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-center" style="font-weight: 900;">TOTAL AKTIVITAS TERCATAT</td>
                <td colspan="4" class="text-center" style="font-weight: 900;">{{ $logs->count() }} Catatan Forensik Keamanan</td>
            </tr>
        </tfoot>
    </table>

@endsection
