@extends('admin.reports.pdf.layout')

@section('report_title', 'BUKU INDUK MASTER DATA UNIT PEMUKIMAN TRANSMIGRASI (UPT)')
@section('report_subtitle', 'Rekapitulasi Data Historis Kewilayahan, Penempatan Warga, dan Serah Terima Aset')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
    Status Lahan: <strong>{{ $filterStatusLabel ?? 'Semua Status' }}</strong> • 
    Total Data: <strong>{{ $locations->count() }} Lokasi UPT</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- Ringkasan Agregasi Data Terfilter -->
    <table class="summary-box">
        <tr>
            <td style="width: 20%;">
                <span class="summary-label">Total UPT Ditampilkan</span>
                <span class="summary-val">{{ $locations->count() }} Lokasi</span>
            </td>
            <td style="width: 20%;">
                <span class="summary-label">Penempatan Awal</span>
                <span class="summary-val">{{ number_format($locations->sum('placement_kk'), 0, ',', '.') }} KK</span>
                <span style="font-size: 6pt; color: #64748b;">{{ number_format($locations->sum('placement_population'), 0, ',', '.') }} Jiwa</span>
            </td>
            <td style="width: 20%;">
                <span class="summary-label">Serah Terima Pemda</span>
                <span class="summary-val">{{ number_format($locations->sum('handover_kk'), 0, ',', '.') }} KK</span>
                <span style="font-size: 6pt; color: #64748b;">{{ number_format($locations->sum('handover_population'), 0, ',', '.') }} Jiwa</span>
            </td>
            <td style="width: 20%;">
                <span class="summary-label">Rasio Definitif / BAST</span>
                <span class="summary-val">{{ $locations->where('handover_kk', '>', 0)->count() }} UPT Definitif</span>
                <span style="font-size: 6pt; color: #64748b;">{{ $locations->where('handover_kk', '<=', 0)->count() }} UPT Binaan</span>
            </td>
            <td style="width: 20%;">
                <span class="summary-label">Kondisi Lahan</span>
                <span class="summary-val" style="font-size: 8pt;">
                    <span style="color: #065f46;">{{ $locations->where('issue_status', 'clean')->count() }} Clean</span> • 
                    <span style="color: #92400e;">{{ $locations->where('issue_status', 'warning')->count() }} Warn</span> • 
                    <span style="color: #9f1239;">{{ $locations->where('issue_status', 'critical')->count() }} Kritis</span>
                </span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rinci Master Data UPT -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">No. UPT</th>
                <th style="width: 12%; text-align: left;">Kabupaten</th>
                <th style="width: 18%; text-align: left;">Nama UPT Asal</th>
                <th style="width: 16%; text-align: left;">Desa Definitif</th>
                <th style="width: 12%; text-align: left;">Pola Budidaya</th>
                <th style="width: 6%;">Tahun</th>
                <th style="width: 7%;">BAST</th>
                <th style="width: 6%;">Masuk (KK)</th>
                <th style="width: 6%;">Serah (KK)</th>
                <th style="width: 7%;">Status Lahan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $idx => $upt)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left font-bold">Kab. {{ $upt->regency?->name ?? '-' }}</td>
                    <td class="text-left font-bold">{{ $upt->upt_name }}</td>
                    <td class="text-left">{{ $upt->current_village_name ?? '-' }}</td>
                    <td class="text-left" style="font-size: 6pt;">{{ $upt->business_pattern ?? 'Tanaman Pangan' }}</td>
                    <td class="text-center">{{ $upt->placement_year ?? '-' }}</td>
                    <td class="text-center">{{ $upt->handover_year ?? 'Binaan' }}</td>
                    <td class="text-right">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($upt->handover_kk, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($upt->issue_status === 'critical')
                            <span class="badge-status badge-critical">Kritis</span>
                        @elseif($upt->issue_status === 'warning')
                            <span class="badge-status badge-warning">Warning</span>
                        @else
                            <span class="badge-status badge-clean">Clean</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada data UPT yang sesuai dengan kriteria filter pencarian.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="8" class="text-center" style="font-weight: 900;">JUMLAH KUMULATIF</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($locations->sum('placement_kk'), 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($locations->sum('handover_kk'), 0, ',', '.') }}</td>
                <td class="text-center" style="font-weight: 900;">{{ $locations->count() }} UPT</td>
            </tr>
        </tfoot>
    </table>

@endsection
