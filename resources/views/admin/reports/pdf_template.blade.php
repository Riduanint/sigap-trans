<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Resmi SIGAP-TRANS KALSEL</title>
    <style>
        @page {
            margin: 1.0cm 1.2cm 1.5cm 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: {{ ($orientation ?? 'landscape') === 'portrait' ? '8pt' : '8.5pt' }};
            color: #1a202c;
            line-height: 1.35;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1B2632;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .kop-title {
            text-align: center;
        }
        .kop-title h2 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            color: #1B2632;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-title h1 {
            margin: 2px 0;
            font-size: 13.5pt;
            font-weight: 900;
            color: #2C3B4D;
            text-transform: uppercase;
        }
        .kop-title p {
            margin: 0;
            font-size: 7.5pt;
            color: #4a5568;
        }

        /* Judul Laporan */
        .report-heading {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-heading h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: 900;
            color: #1B2632;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .report-heading .subtitle {
            margin: 2px 0 0 0;
            font-size: 8pt;
            color: #4a5568;
            font-weight: bold;
        }
        .report-heading .metadata {
            margin: 4px 0 0 0;
            font-size: 7.5pt;
            color: #718096;
        }

        /* Seksi Header */
        .section-header {
            margin-top: 14px;
            margin-bottom: 6px;
            padding: 4px 8px;
            background-color: #1B2632;
            color: #EEE9DF;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Kotak Ringkasan Eksekutif */
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            border: 1px solid #C9C1B1;
            background-color: #fcfbf9;
        }
        .summary-box td {
            padding: 6px 10px;
            font-size: 7.5pt;
            border: 0.5px solid #e2e8f0;
            vertical-align: top;
        }
        .summary-val {
            font-size: 9pt;
            font-weight: bold;
            color: #1B2632;
            display: block;
            margin-top: 2px;
        }

        /* Tabel Data Umum */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table th {
            background-color: #1B2632;
            color: #EEE9DF;
            font-weight: bold;
            padding: 5px 4px;
            font-size: 7pt;
            text-align: center;
            border: 0.5px solid #1B2632;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4px 4px;
            font-size: 7pt;
            border: 0.5px solid #cbd5e1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f8fafc; }
        .bg-highlight { background-color: #fef2f2; }
        
        /* Status Badges */
        .status-clean { color: #047857; font-weight: bold; }
        .status-warning { color: #b45309; font-weight: bold; }
        .status-critical { color: #b91c1c; font-weight: bold; }

        /* Pengesahan */
        .signature-table {
            width: 100%;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        /* Footer Halaman */
        .footer-page-num {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            height: 18px;
            font-size: 7pt;
            color: #718096;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 3px;
        }
        .footer-page-num .left { float: left; }
        .footer-page-num .right { float: right; }
    </style>
</head>
<body>

    <!-- FOOTER HALAMAN OTOMATIS -->
    <div class="footer-page-num">
        <span class="left">SIGAP-TRANS KALSEL • Sistem Informasi Geospasial Transmigrasi (Disnakertrans Prov. Kalsel)</span>
        <span class="right">Dokumen Resmi Kedinasan • Halaman Otomatis</span>
    </div>

    <!-- 1. KOP SURAT RESMI KEDINASAN -->
    @if(in_array('sec_kop', $sections ?? []))
        <table class="kop-table">
            <tr>
                <td class="kop-title">
                    <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
                    <h1>DINAS TENAGA KERJA DAN TRANSMIGRASI</h1>
                    <p>Jalan A. Yani Km. 6 No. 23 Banjarmasin • Telepon: (0511) 3252741 • Laman: disnakertrans.kalselprov.go.id</p>
                </td>
            </tr>
        </table>
    @endif

    <!-- JUDUL DOKUMEN LAPORAN -->
    <div class="report-heading">
        @if(in_array('sec_upt_table', $sections ?? []))
            <h3>REKAPITULASI DATA HISTORIS 124 UNIT PEMUKIMAN TRANSMIGRASI (UPT)</h3>
        @else
            <h3>RINGKASAN EKSEKUTIF KEBIJAKAN & AGREGASI PERSEBARAN TRANSMIGRASI</h3>
        @endif
        <div class="subtitle">Provinsi Kalimantan Selatan (Periode Penempatan 1953–2025)</div>
        <div class="metadata">
            Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
            Status Lahan: <strong>{{ $filterStatusLabel ?? 'Semua Status Lahan' }}</strong> • 
            Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF & INDIKATOR KUNCI (KPI) -->
    @if(in_array('sec_kpi', $sections ?? []))
        <table class="summary-box">
            <tr>
                <td style="width: 25%;">
                    <span style="color: #4a5568;">Total Cakupan Kawasan:</span>
                    <span class="summary-val">{{ $totalUpt ?? count($locations ?? []) }} Satuan Pemukiman UPT</span>
                    <span style="font-size: 6.5pt; color: #718096;">Persebaran di 9 Kabupaten Binaan</span>
                </td>
                <td style="width: 25%;">
                    <span style="color: #4a5568;">Penempatan Awal Historis:</span>
                    <span class="summary-val">{{ number_format($totalPlacementKk ?? 0) }} KK / {{ number_format($totalPlacementPop ?? 0) }} Jiwa</span>
                    <span style="font-size: 6.5pt; color: #718096;">Periode Masuk 1953 s/d 2025</span>
                </td>
                <td style="width: 25%;">
                    <span style="color: #4a5568;">Serah Terima Pemda (BAST):</span>
                    <span class="summary-val">{{ number_format($totalHandoverKk ?? 0) }} KK / {{ number_format($totalHandoverPop ?? 0) }} Jiwa</span>
                    <span style="font-size: 6.5pt; color: #047857; font-weight: bold;">
                        +{{ number_format(($growthKk ?? 0)) }} KK (Pertumbuhan Alami)
                    </span>
                </td>
                <td style="width: 25%;">
                    <span style="color: #4a5568;">Status Legalitas & Kawasan:</span>
                    <span class="summary-val" style="font-size: 8pt;">
                        <span class="status-clean">🟢 {{ $cleanCount ?? 0 }} Clean</span> • 
                        <span class="status-warning">🟡 {{ $warningCount ?? 0 }} Warning</span> • 
                        <span class="status-critical">🔴 {{ $criticalCount ?? 0 }} Kritis</span>
                    </span>
                    <span style="font-size: 6.5pt; color: #718096;">Berdasarkan Koordinasi ATR/BPN</span>
                </td>
            </tr>
        </table>
    @endif

    <!-- 3. MATRIKS KOMPARASI 9 KABUPATEN -->
    @if(in_array('sec_regency', $sections ?? []) && isset($regencies) && $regencies->isNotEmpty())
        <div class="section-header">I. MATRIKS AGREGASI & DEMOGRAFI PER WILAYAH KABUPATEN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">NO</th>
                    <th>NAMA KABUPATEN</th>
                    <th style="width: 80px;">IBUKOTA</th>
                    <th style="width: 55px;">JUMLAH UPT</th>
                    <th style="width: 75px;">KK PENEMPATAN</th>
                    <th style="width: 75px;">KK SERAH (BAST)</th>
                    <th style="width: 65px;">PERTUMBUHAN</th>
                    <th style="width: 110px;">STATUS KAWASAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($regencies as $idx => $r)
                    @php
                        $uptCount = $r->upt_locations_count ?? 0;
                        $pIn = (int) ($r->uptLocations->sum('placement_kk') ?? 0);
                        $pOut = (int) ($r->uptLocations->sum('handover_kk') ?? 0);
                        $growth = $pOut - $pIn;
                        $cClean = $r->uptLocations->where('issue_status', 'clean')->count();
                        $cWarn = $r->uptLocations->where('issue_status', 'warning')->count();
                        $cCrit = $r->uptLocations->where('issue_status', 'critical')->count();
                    @endphp
                    <tr class="{{ $idx % 2 == 1 ? 'bg-gray' : '' }}">
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="font-bold">Kabupaten {{ $r->name }}</td>
                        <td class="text-center">{{ $r->capital_city ?: '-' }}</td>
                        <td class="text-center font-bold">{{ $uptCount }} UPT</td>
                        <td class="text-right">{{ number_format($pIn) }} KK</td>
                        <td class="text-right font-bold">{{ number_format($pOut) }} KK</td>
                        <td class="text-center font-bold {{ $growth >= 0 ? 'status-clean' : 'status-critical' }}">
                            {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth) }} KK
                        </td>
                        <td class="text-center">
                            <span class="status-clean">{{ $cClean }} Clean</span> / 
                            <span class="status-warning">{{ $cWarn }} Warn</span> / 
                            <span class="status-critical">{{ $cCrit }} Kritis</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 4. PAPAN KASUS KRITIS PRIORITAS MEDIASI -->
    @if(in_array('sec_critical', $sections ?? []) && isset($criticalCases) && $criticalCases->isNotEmpty())
        <div class="section-header" style="background-color: #991b1b;">
            II. DAFTAR PRIORITAS KASUS KRITIS / PERMASALAHAN LAHAN (PIN MERAH)
        </div>
        <table class="data-table">
            <thead>
                <tr style="background-color: #991b1b;">
                    <th style="width: 25px;">NO</th>
                    <th style="width: 50px;">KODE</th>
                    <th style="width: 90px;">KABUPATEN</th>
                    <th style="width: 120px;">NAMA UPT ASAL</th>
                    <th style="width: 100px;">DESA DEFINITIF</th>
                    <th>URAIAN HAMBATAN AGRARIA & REKOMENDASI MEDIASI</th>
                    <th style="width: 80px;">STATUS SHM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criticalCases as $idx => $case)
                    <tr class="bg-highlight">
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">UPT-{{ str_pad($case->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $case->regency?->name }}</td>
                        <td class="font-bold">{{ $case->upt_name }}</td>
                        <td>{{ $case->current_village_name }}</td>
                        <td>
                            <strong style="color: #991b1b;">Permasalahan:</strong> 
                            {{ $case->issue_note ?: ($case->notes_issue ?: 'Perlu mediasi tapal batas dan kawasan hutan.') }}
                        </td>
                        <td class="text-center font-bold" style="color: #991b1b;">
                            {{ $case->shm_status ?: 'Kendala SHM' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 5. TABEL RINCI DATA UPT TRANSMIGRASI -->
    @if(in_array('sec_upt_table', $sections ?? []) && isset($locations) && $locations->isNotEmpty())
        <div class="section-header">
            III. LAMPIRAN TEKNIS: RINCIAN HISTORIS {{ count($locations) }} UNIT PEMUKIMAN TRANSMIGRASI
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;">NO</th>
                    <th style="width: 45px;">KODE</th>
                    <th style="width: 75px;">KABUPATEN</th>
                    <th>NAMA UPT ASAL</th>
                    <th>DESA DEFINITIF</th>
                    <th style="width: 38px;">POLA</th>
                    <th style="width: 40px;">MASUK</th>
                    <th style="width: 42px;">KK IN</th>
                    <th style="width: 42px;">JIWA IN</th>
                    <th style="width: 45px;">SERAH</th>
                    <th style="width: 42px;">KK OUT</th>
                    <th style="width: 42px;">JIWA OUT</th>
                    <th style="width: 50px;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $idx => $loc)
                    <tr class="{{ $idx % 2 == 1 ? 'bg-gray' : '' }}">
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">UPT-{{ str_pad($loc->upt_number, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $loc->regency?->name }}</td>
                        <td class="font-bold">{{ $loc->upt_name }}</td>
                        <td>{{ $loc->current_village_name }}</td>
                        <td class="text-center">{{ $loc->business_pattern }}</td>
                        <td class="text-center">{{ $loc->placement_year }}</td>
                        <td class="text-center font-bold">{{ number_format($loc->placement_kk) }}</td>
                        <td class="text-center">{{ number_format($loc->placement_population) }}</td>
                        <td class="text-center">{{ $loc->handover_year ?: '-' }}</td>
                        <td class="text-center font-bold">{{ number_format($loc->handover_kk) }}</td>
                        <td class="text-center">{{ number_format($loc->handover_population) }}</td>
                        <td class="text-center">
                            @if($loc->issue_status === 'clean')
                                <span class="status-clean">CLEAN</span>
                            @elseif($loc->issue_status === 'warning')
                                <span class="status-warning">WARNING</span>
                            @else
                                <span class="status-critical">KRITIS</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <!-- BARIS TOTAL AKUMULASI -->
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="7" class="text-right" style="padding-right: 8px;">TOTAL KESELURUHAN DILAMPIRKAN:</td>
                    <td class="text-center font-bold">{{ number_format($locations->sum('placement_kk')) }}</td>
                    <td class="text-center font-bold">{{ number_format($locations->sum('placement_population')) }}</td>
                    <td></td>
                    <td class="text-center font-bold">{{ number_format($locations->sum('handover_kk')) }}</td>
                    <td class="text-center font-bold">{{ number_format($locations->sum('handover_population')) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- 6. LEMBAR PENGESAHAN / TANDA TANGAN KEDINASAN -->
    @if(in_array('sec_signature', $sections ?? []))
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <div style="font-size: 7.5pt; color: #4a5568; line-height: 1.4;">
                        <strong>Catatan Dokumen:</strong><br>
                        1. Data historis bersumber dari BAST Penyerahan Pembinaan Transmigrasi Disnakertrans Prov. Kalsel.<br>
                        2. Koordinasi status sengketa dan legalisasi SHM diperbarui berkala bersama Kantor Wilayah BPN Prov. Kalsel.
                    </div>
                </td>
                <td style="width: 40%; text-align: center;">
                    <p style="margin-bottom: 2px;">{{ $signCity ?? 'Banjarmasin' }}, {{ $signDate ?? date('d F Y') }}</p>
                    <p style="margin-top: 0; font-weight: bold; line-height: 1.3;">
                        {{ $signerTitle ?? 'Kepala Bidang Ketransmigrasian' }}<br>
                        Dinas Tenaga Kerja dan Transmigrasi Prov. Kalsel,
                    </p>
                    <div style="height: 52px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $signerName ?? 'Hj. Ina Yuliani, S.Sos, M.Si, M.IP' }}
                    </p>
                    <p style="margin: 2px 0 0 0; font-size: 7.5pt; color: #4a5568;">
                        NIP. {{ $signerNip ?? '19690729 199010 2 001' }}
                    </p>
                </td>
            </tr>
        </table>
    @endif

</body>
</html>
