@extends('admin.reports.pdf.layout')

@section('report_title', 'LAPORAN STATUS PUBLIKASI WILAYAH SPASIAL & CAKUPAN WEBGIS')
@section('report_subtitle', 'Kontrol Visibilitas Spasial 9 Kabupaten Binaan pada Peta Interaktif SIGAP-TRANS Kalsel')

@section('report_metadata')
    Total Wilayah: <strong>{{ $stats['total_regencies'] }} Kabupaten</strong> •
    Status Publikasi: <strong>{{ $stats['visible_regencies'] }} Aktif Ditampilkan / {{ $stats['hidden_regencies'] }}
        Disembunyikan</strong> •
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- 1. Ringkasan Statistik Status Visibilitas -->
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Total Wilayah Binaan</span>
                <span class="summary-val">{{ $stats['total_regencies'] }} Kabupaten</span>
                <span style="font-size: 6pt; color: #64748b;">Provinsi Kalimantan Selatan</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Aktif di Peta WebGIS</span>
                <span class="summary-val" style="color: #065f46;">{{ $stats['visible_regencies'] }} Kabupaten</span>
                <span style="font-size: 6pt; color: #047857; font-weight: bold;">Poligon & UPT Terlihat</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Disembunyikan dari Peta</span>
                <span class="summary-val" style="color: #64748b;">{{ $stats['hidden_regencies'] }} Kabupaten</span>
                <span style="font-size: 6pt; color: #64748b;">Dikecualikan dari Peta Publik</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Cakupan UPT Terpublikasi</span>
                <span class="summary-val" style="color: #0284c7;">{{ $stats['visible_upts'] }} / {{ $stats['total_upts'] }}
                    UPT</span>
                <span style="font-size: 6pt; color: #64748b;">Unit Pemukiman Aktif</span>
            </td>
        </tr>
    </table>

    <!-- 2. Tabel Rincian Status Publikasi 9 Kabupaten -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #1B2632; text-transform: uppercase; margin: 10px 0 4px 0;">
        Matriks Status Publikasi & Cakupan UPT Wilayah Administratif
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 24%; text-align: left;">Nama Kabupaten</th>
                <th style="width: 14%;">Status WebGIS</th>
                <th style="width: 10%;">Jumlah UPT</th>
                <th style="width: 14%;">Penempatan (KK)</th>
                <th style="width: 14%;">Serah Terima (KK)</th>
                <th style="width: 10%;">Kondisi Lahan</th>
                <th style="width: 10%;">Warna Peta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($regencies as $idx => $reg)
                @php
                    $clean = $reg->uptLocations->where('issue_status', 'clean')->count();
                    $warning = $reg->uptLocations->where('issue_status', 'warning')->count();
                    $critical = $reg->uptLocations->where('issue_status', 'critical')->count();
                    $placeKk = $reg->uptLocations->sum('placement_kk');
                    $handKk = $reg->uptLocations->sum('handover_kk');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold text-left" style="font-size: 7pt;">
                        Kabupaten {{ $reg->name }}
                    </td>
                    <td class="text-center font-bold">
                        @if($reg->is_visible)
                            <span class="badge-status badge-clean" style="font-size: 5.5pt;">● DITAMPILKAN</span>
                        @else
                            <span class="badge-status"
                                style="background-color: #f1f5f9; color: #64748b; border: 0.5px solid #cbd5e1; font-size: 5.5pt;">DISEMBUNYIKAN</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $reg->upt_locations_count }} UPT</td>
                    <td class="text-right">{{ number_format($placeKk, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($handKk, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-size: 6pt;">
                        <span style="color: #065f46; font-weight: bold;">{{ $clean }}C</span> •
                        <span style="color: #92400e; font-weight: bold;">{{ $warning }}W</span> •
                        <span style="color: #9f1239; font-weight: bold;">{{ $critical }}K</span>
                    </td>
                    <td class="text-center" style="font-size: 6pt;">
                        <span
                            style="display: inline-block; width: 8px; height: 8px; background-color: {{ $reg->map_color ?: '#3B82F6' }}; border: 0.5px solid #64748b; vertical-align: middle; margin-right: 2px;"></span>
                        <span style="font-family: monospace;">{{ strtoupper($reg->map_color ?: '#3B82F6') }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-center font-bold" style="font-size: 6.5pt;">
                    TOTAL CAKUPAN AKTIF TERPUBLIKASI ({{ $stats['visible_regencies'] }} KABUPATEN)
                </td>
                <td class="text-center font-bold">{{ $stats['visible_upts'] }} UPT</td>
                <td class="text-right font-bold">{{ number_format($stats['visible_placement_kk'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($stats['visible_handover_kk'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="font-size: 6pt; color: #64748b;">
                    Dari Total {{ $stats['total_upts'] }} UPT Se-Kalsel
                </td>
            </tr>
        </tfoot>
    </table>

@endsection