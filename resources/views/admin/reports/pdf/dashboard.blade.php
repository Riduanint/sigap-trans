@extends('admin.reports.pdf.layout')

@section('report_title', 'RINGKASAN EKSEKUTIF RUANG KENDALI TRANSMIGRASI')
@section('report_subtitle', 'Dashboard Utama Super Admin • Provinsi Kalimantan Selatan')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
    Total Basis Data: <strong>{{ $totalUpt }} Unit Pemukiman Transmigrasi (UPT)</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- 1. Empat Kartu Metrik KPI Utama -->
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Cakupan Wilayah</span>
                <span class="summary-val">{{ $totalUpt }} Lokasi UPT</span>
                <span style="font-size: 6.5pt; color: #475569;">{{ $regencies->count() }} Kabupaten dalam laporan</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Penempatan Awal</span>
                <span class="summary-val">{{ number_format($totalPlacementKk, 0, ',', '.') }} KK</span>
                <span style="font-size: 6.5pt; color: #475569;">{{ number_format($totalPlacementPop, 0, ',', '.') }} Jiwa Warga</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Serah Terima Pemda</span>
                <span class="summary-val">{{ number_format($totalHandoverKk, 0, ',', '.') }} KK</span>
                @php
                    $growthKk = $totalHandoverKk - $totalPlacementKk;
                    $growthPct = $totalPlacementKk > 0 ? round(($growthKk / $totalPlacementKk) * 100, 1) : 0;
                @endphp
                <span style="font-size: 6.5pt; color: #047857; font-weight: bold;">
                    {{ $growthKk >= 0 ? '+' : '' }}{{ number_format($growthKk, 0, ',', '.') }} KK ({{ $growthPct }}%)
                </span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Status Kondisi Lahan</span>
                <span class="summary-val" style="color: #9f1239;">{{ $criticalCount }} Kritis / Mediasi</span>
                <span style="font-size: 6.5pt; color: #475569;">
                    <strong style="color: #065f46;">{{ $cleanCount }} Clean</strong> • 
                    <strong style="color: #92400e;">{{ $warningCount }} Warning</strong>
                </span>
            </td>
        </tr>
    </table>

    <!-- 2. Matriks Agregasi Sebaran 9 Kabupaten -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #1B2632; text-transform: uppercase; margin: 10px 0 4px 0;">
        Matriks Sebaran Transmigrasi per Kabupaten
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 22%; text-align: left;">Kabupaten</th>
                <th style="width: 10%;">Jumlah UPT</th>
                <th style="width: 13%;">Penempatan (KK)</th>
                <th style="width: 13%;">Serah Terima (KK)</th>
                <th style="width: 12%;">Pertumbuhan</th>
                <th style="width: 26%;">Status Lahan (Clean / Warning / Kritis)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totU = 0; $totPk = 0; $totHk = 0; $totCl = 0; $totWr = 0; $totCr = 0;
            @endphp
            @foreach($regencies as $idx => $reg)
                @php
                    $uCount = $reg->upt_locations_count ?? $reg->uptLocations->count();
                    $pKk = $reg->uptLocations->sum('placement_kk');
                    $hKk = $reg->uptLocations->sum('handover_kk');
                    $cClean = $reg->uptLocations->where('issue_status', 'clean')->count();
                    $cWarning = $reg->uptLocations->where('issue_status', 'warning')->count();
                    $cCritical = $reg->uptLocations->where('issue_status', 'critical')->count();
                    $diff = $hKk - $pKk;

                    $totU += $uCount; $totPk += $pKk; $totHk += $hKk;
                    $totCl += $cClean; $totWr += $cWarning; $totCr += $cCritical;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold text-left">Kabupaten {{ $reg->name }}</td>
                    <td class="text-center font-bold">{{ $uCount }} UPT</td>
                    <td class="text-right">{{ number_format($pKk, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($hKk, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: {{ $diff >= 0 ? '#047857' : '#b91c1c' }};">
                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge-status badge-clean">{{ $cClean }} Clean</span>
                        <span class="badge-status badge-warning">{{ $cWarning }} Warning</span>
                        <span class="badge-status badge-critical">{{ $cCritical }} Kritis</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="text-center" style="font-weight: 900;">TOTAL PROVINSI</td>
                <td class="text-center" style="font-weight: 900;">{{ $totU }} UPT</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($totPk, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: 900;">{{ number_format($totHk, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: 900; color: #047857;">
                    +{{ number_format($totHk - $totPk, 0, ',', '.') }}
                </td>
                <td class="text-center" style="font-weight: 900;">
                    <span class="badge-status badge-clean">{{ $totCl }} Clean</span>
                    <span class="badge-status badge-warning">{{ $totWr }} Warning</span>
                    <span class="badge-status badge-critical">{{ $totCr }} Kritis</span>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 3. Papan Prioritas Kasus Kritis Mediasi Lintas Sektor -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #b91c1c; text-transform: uppercase; margin: 12px 0 4px 0;">
        Papan Kasus Kritis & Lokasi Prioritas Mediasi (Kawasan Hutan / ATR-BPN)
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">No. UPT</th>
                <th style="width: 18%; text-align: left;">Kabupaten</th>
                <th style="width: 22%; text-align: left;">Nama UPT Asal / Desa Definitif</th>
                <th style="width: 10%;">Tahun BAST</th>
                <th style="width: 36%; text-align: left;">Uraian Isu Permasalahan & Rekomendasi Mediasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($priorityCases as $pIdx => $case)
                <tr>
                    <td class="text-center">{{ $pIdx + 1 }}</td>
                    <td class="text-center font-bold">UPT-{{ str_pad($case->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left font-bold">Kab. {{ $case->regency?->name ?? '-' }}</td>
                    <td class="text-left">
                        <strong>{{ $case->upt_name }}</strong><br>
                        <span style="font-size: 6pt; color: #475569;">Desa: {{ $case->current_village_name ?? '-' }}</span>
                    </td>
                    <td class="text-center">{{ $case->handover_year ?? $case->placement_year ?? '-' }}</td>
                    <td class="text-left" style="font-size: 6pt; color: #991b1b;">
                        {{ $case->issue_note ?? 'Memerlukan koordinasi batas kawasan hutan dan sertipikasi hak milik transmigran.' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 10px; color: #047857; font-weight: bold;">
                        Seluruh lokasi UPT dalam kondisi Clean & Clear. Tidak ada kasus kritis aktif.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection
