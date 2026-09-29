@extends('admin.reports.pdf.layout')

@section('report_title', 'LAPORAN MONITORING SERTIPIKASI TANAH & ALAS HAK TRANSMIGRASI')
@section('report_subtitle', 'Pengawalan Program Redistribusi Tanah, Legalitas Hak Milik (SHM), dan Mediasi Lintas Sektor BPN / KLHK')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
    Status SHM: <strong>{{ $filterShmLabel ?? 'Semua Status SHM' }}</strong> • 
    Total Data: <strong>{{ $locations->count() }} Lokasi UPT</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- Ringkasan Statistik Status Sertipikat Tanah -->
    @php
        $countShm100 = $locations->where('shm_status', '100% SHM')->count() + $locations->where('shm_status', 'Sudah SHM')->count() + $locations->whereNull('shm_status')->count();
        $countShmPartial = $locations->where('shm_status', 'Sebagian SHM')->count() + $locations->where('shm_status', 'Proses BPN')->count();
        $countShmNone = $locations->where('shm_status', 'Belum SHM')->count();
        $countCritical = $locations->where('issue_status', 'critical')->count();
    @endphp
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">100% SHM (Tuntas)</span>
                <span class="summary-val" style="color: #065f46;">{{ $countShm100 }} Lokasi</span>
                <span style="font-size: 6pt; color: #64748b;">Alas Hak SHM Lengkap</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Sebagian SHM / Proses BPN</span>
                <span class="summary-val" style="color: #0284c7;">{{ $countShmPartial }} Lokasi</span>
                <span style="font-size: 6pt; color: #64748b;">Tahap Pengukuran / PTSL</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Belum SHM (Masih HPL)</span>
                <span class="summary-val" style="color: #92400e;">{{ $countShmNone }} Lokasi</span>
                <span style="font-size: 6pt; color: #64748b;">Menunggu Pelepasan Hak</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Lokasi Kritis / Mediasi</span>
                <span class="summary-val" style="color: #9f1239;">{{ $countCritical }} Lokasi</span>
                <span style="font-size: 6pt; color: #64748b;">Tumpang Tindih / Sengketa</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rinci Legalitas Pertanahan -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">No. UPT</th>
                <th style="width: 13%; text-align: left;">Kabupaten</th>
                <th style="width: 17%; text-align: left;">Nama UPT Asal</th>
                <th style="width: 15%; text-align: left;">Desa Definitif</th>
                <th style="width: 12%;">Status SHM</th>
                <th style="width: 8%;">Kondisi Lahan</th>
                <th style="width: 25%; text-align: left;">Catatan Hambatan Agraria & Rekomendasi Mediasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $idx => $upt)
                @php
                    $shmLabel = $upt->shm_status ?? '100% SHM';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left font-bold">Kab. {{ $upt->regency?->name ?? '-' }}</td>
                    <td class="text-left font-bold">{{ $upt->upt_name }}</td>
                    <td class="text-left">{{ $upt->current_village_name ?? '-' }}</td>
                    <td class="text-center font-bold" style="font-size: 6pt;">
                        @if($shmLabel === '100% SHM' || $shmLabel === 'Sudah SHM')
                            <span style="color: #065f46;">100% SHM</span>
                        @elseif($shmLabel === 'Sebagian SHM')
                            <span style="color: #0284c7;">Sebagian SHM</span>
                        @elseif($shmLabel === 'Proses BPN')
                            <span style="color: #b45309;">Proses BPN</span>
                        @else
                            <span style="color: #dc2626;">{{ $shmLabel }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($upt->issue_status === 'critical')
                            <span class="badge-status badge-critical">Kritis</span>
                        @elseif($upt->issue_status === 'warning')
                            <span class="badge-status badge-warning">Warning</span>
                        @else
                            <span class="badge-status badge-clean">Clean</span>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 6pt; color: {{ $upt->issue_status === 'critical' ? '#991b1b' : '#334155' }};">
                        {{ $upt->issue_note ?: ($upt->issue_status === 'clean' ? 'Bebas sengketa, legalitas hak milik selesai.' : 'Dalam pemantauan berkala.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada data legalitas pertanahan yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-center" style="font-weight: 900;">TOTAL LOKASI DIPANTAU</td>
                <td class="text-center" style="font-weight: 900;">{{ $locations->count() }} UPT</td>
                <td class="text-center" style="font-weight: 900;">
                    <span style="color: #9f1239;">{{ $countCritical }} Kritis</span>
                </td>
                <td class="text-left" style="font-size: 6pt; color: #64748b;">
                    Sinkronisasi berkala bersama Kantor Pertanahan ATR/BPN se-Kalimantan Selatan.
                </td>
            </tr>
        </tfoot>
    </table>

@endsection
