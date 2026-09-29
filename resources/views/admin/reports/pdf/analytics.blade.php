@extends('admin.reports.pdf.layout')

@section('report_title', 'EXECUTIVE POLICY BRIEFING: ANALISIS KEBIJAKAN TRANSMIGRASI')
@section('report_subtitle', 'Analisis Makro Penempatan Historis, Integrasi Wilayah Definitif, dan Pengawalan Agraria')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten Binaan)' }}</strong> • 
    Periode Penempatan: <strong>1953–2025</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- 1. Empat Kartu KPI Strategis Makro -->
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Rasio Kemandirian Definitif</span>
                <span class="summary-val" style="color: #065f46;">58,1%</span>
                <span style="font-size: 6pt; color: #64748b;">72 Definitif vs 52 Binaan</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Pertumbuhan Alami Demografi</span>
                <span class="summary-val" style="color: #0284c7;">+37,1%</span>
                <span style="font-size: 6pt; color: #64748b;">+14.475 KK (Akumulasi BAST)</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Komposisi Transmigran</span>
                <span class="summary-val">TPA: 62% • TPS: 38%</span>
                <span style="font-size: 6pt; color: #64748b;">Penduduk Asal vs Setempat</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Prioritas Mediasi Lintas Sektor</span>
                <span class="summary-val" style="color: #9f1239;">{{ $criticalCases->count() }} Lokasi Kritis</span>
                <span style="font-size: 6pt; color: #64748b;">Kawasan Hutan & ATR/BPN</span>
            </td>
        </tr>
    </table>

    <!-- 2. Tabel Analisis Tren Dekade Historis Penempatan (1950–2025) -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #1B2632; text-transform: uppercase; margin: 10px 0 4px 0;">
        1. Analisis Tren Dekade Penempatan Historis (1950–2025)
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">Dekade / Era</th>
                <th style="width: 12%;">Jumlah UPT</th>
                <th style="width: 18%;">Penempatan (KK)</th>
                <th style="width: 18%;">Penempatan (Jiwa)</th>
                <th style="width: 38%; text-align: left;">Fokus Kebijakan & Karakteristik Era</th>
            </tr>
        </thead>
        <tbody>
            @foreach($decades as $dec)
                <tr>
                    <td class="font-bold text-center">{{ $dec['decade'] }}</td>
                    <td class="text-center font-bold">{{ $dec['count'] }} UPT</td>
                    <td class="text-right">{{ number_format($dec['total_kk'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($dec['total_pop'], 0, ',', '.') }}</td>
                    <td class="text-left" style="font-size: 6pt; color: #475569;">
                        @if($dec['decade'] === '1980-an')
                            <strong>Puncak Transmigrasi (Repelita IV)</strong>: Perluasan masif perkebunan kelapa sawit & karet.
                        @elseif($dec['decade'] === '1990-an')
                            <strong>Pola PIR & TPLK Terpadu</strong>: Fokus kemandirian pangan lahan rawa pasang surut.
                        @elseif($dec['decade'] === '1950-an' || $dec['decade'] === '1960-an')
                            <strong>Fase Perintisan Awal</strong>: Program BRN dan transmigrasi spontan pasca kemerdekaan.
                        @else
                            Program konsolidasi kawasan, sertipikasi tanah, dan revitalisasi pemukiman.
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. Matriks Komparasi 9 Kabupaten -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #1B2632; text-transform: uppercase; margin: 10px 0 4px 0;">
        2. Matriks Komparasi Beban Kewilayahan 9 Kabupaten
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 22%; text-align: left;">Kabupaten</th>
                <th style="width: 10%;">Jumlah UPT</th>
                <th style="width: 14%;">Penempatan (KK)</th>
                <th style="width: 14%;">Serah Terima (KK)</th>
                <th style="width: 12%;">Pertumbuhan</th>
                <th style="width: 24%;">Status Lahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($regencyMatrix as $idx => $rm)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold text-left">Kabupaten {{ $rm['name'] }}</td>
                    <td class="text-center font-bold">{{ $rm['total_upt'] }} UPT</td>
                    <td class="text-right">{{ number_format($rm['total_placement_kk'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($rm['total_handover_kk'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: {{ $rm['growth_kk'] >= 0 ? '#047857' : '#b91c1c' }};">
                        {{ $rm['growth_kk'] >= 0 ? '+' : '' }}{{ number_format($rm['growth_kk'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge-status badge-clean">{{ $rm['clean_count'] }} Clean</span>
                        <span class="badge-status badge-warning">{{ $rm['warning_count'] }} Warn</span>
                        <span class="badge-status badge-critical">{{ $rm['critical_count'] }} Kritis</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 4. Papan 15 Kasus Kritis Mediasi Lintas Sektor -->
    <div style="font-size: 7.5pt; font-weight: bold; color: #9f1239; text-transform: uppercase; margin: 10px 0 4px 0;">
        3. Papan Prioritas 15 Kasus Kritis (Rekomendasi Mediasi BPN / KLHK)
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">No. UPT</th>
                <th style="width: 18%; text-align: left;">Kabupaten</th>
                <th style="width: 22%; text-align: left;">Nama UPT Asal / Desa</th>
                <th style="width: 46%; text-align: left;">Uraian Masalah & Langkah Solusi Mediasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($criticalCases as $cIdx => $case)
                <tr>
                    <td class="text-center">{{ $cIdx + 1 }}</td>
                    <td class="text-center font-bold">UPT-{{ str_pad($case->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left font-bold">Kab. {{ $case->regency?->name ?? '-' }}</td>
                    <td class="text-left">
                        <strong>{{ $case->upt_name }}</strong><br>
                        <span style="font-size: 5.5pt; color: #64748b;">Desa: {{ $case->current_village_name ?? '-' }}</span>
                    </td>
                    <td class="text-left" style="font-size: 5.5pt; color: #991b1b;">
                        {{ $case->issue_note ?: 'Memerlukan penegasan batas pelepasan kawasan hutan (KLHK) dan percepatan penerbitan SHM redistribusi tanah BPN.' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 10px; color: #047857; font-weight: bold;">
                        Tidak ada kasus kritis aktif yang membutuhkan mediasi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection
