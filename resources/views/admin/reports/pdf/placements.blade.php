@extends('admin.reports.pdf.layout')

@section('report_title', 'LAPORAN REALISASI PENEMPATAN AWAL WARGA TRANSMIGRAN')
@section('report_subtitle', 'Rekapitulasi Penempatan Kepala Keluarga (KK), Jiwa, Pola Usaha, dan Kapasitas Daya Tampung')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
    Total Data: <strong>{{ $locations->count() }} Lokasi UPT</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- Ringkasan Statistik Penempatan -->
    @php
        $totalKk = $locations->sum('placement_kk');
        $totalPop = $locations->sum('placement_population');
        $totalCap = $locations->sum('capacity_kk') ?: $totalKk;
        $avgRatio = $totalKk > 0 ? round($totalPop / $totalKk, 2) : 0;
    @endphp
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Total KK Penempatan</span>
                <span class="summary-val">{{ number_format($totalKk, 0, ',', '.') }} KK</span>
                <span style="font-size: 6pt; color: #64748b;">Dari {{ $locations->count() }} Lokasi UPT</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Total Jiwa Penempatan</span>
                <span class="summary-val">{{ number_format($totalPop, 0, ',', '.') }} Jiwa</span>
                <span style="font-size: 6pt; color: #64748b;">Penduduk Awal Transmigrasi</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Rata-rata Jiwa per KK</span>
                <span class="summary-val">{{ $avgRatio }} Jiwa/KK</span>
                <span style="font-size: 6pt; color: #64748b;">Rasio Kepadatan Keluarga</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Rentang Periode Historis</span>
                <span class="summary-val">1953 – 2025</span>
                <span style="font-size: 6pt; color: #64748b;">Transmigrasi Terencana Kalsel</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rinci Penempatan Awal -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">No. UPT</th>
                <th style="width: 13%; text-align: left;">Kabupaten</th>
                <th style="width: 18%; text-align: left;">Nama UPT Asal</th>
                <th style="width: 16%; text-align: left;">Desa Definitif</th>
                <th style="width: 13%; text-align: left;">Pola Usaha</th>
                <th style="width: 6%;">Tahun</th>
                <th style="width: 7%;">Daya Tampung</th>
                <th style="width: 6%;">KK Masuk</th>
                <th style="width: 6%;">Jiwa Masuk</th>
                <th style="width: 5%;">Jiwa/KK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $idx => $upt)
                @php
                    $ratio = $upt->placement_kk > 0 ? round($upt->placement_population / $upt->placement_kk, 1) : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left font-bold">Kab. {{ $upt->regency?->name ?? '-' }}</td>
                    <td class="text-left font-bold">{{ $upt->upt_name }}</td>
                    <td class="text-left">{{ $upt->current_village_name ?? '-' }}</td>
                    <td class="text-left" style="font-size: 6pt;">{{ $upt->business_pattern ?? 'Tanaman Pangan' }}</td>
                    <td class="text-center font-bold">{{ $upt->placement_year ?? '-' }}</td>
                    <td class="text-right">{{ $upt->capacity_kk ? number_format($upt->capacity_kk, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($upt->placement_population, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $ratio }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada data penempatan awal yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="8" class="text-center" style="font-weight: 900;">TOTAL KUMULATIF PENEMPATAN</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($totalKk, 0, ',', '.') }} KK</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($totalPop, 0, ',', '.') }} Jiwa</td>
                <td class="text-center" style="font-weight: 900;">{{ $avgRatio }}</td>
            </tr>
        </tfoot>
    </table>

@endsection
