<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan Resmi SIGAP-TRANS KALSEL' }}</title>
    <style>
        @page {
            margin: 1.0cm 1.2cm 1.4cm 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: {{ ($orientation ?? 'landscape') === 'portrait' ? '8pt' : '7.5pt' }};
            color: #1a202c;
            line-height: 1.35;
        }

        /* Kop Surat Resmi Kedinasan Pemprov Kalsel */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1B2632;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .kop-title {
            text-align: center;
        }
        .kop-title h2 {
            margin: 0;
            font-size: 10.5pt;
            font-weight: bold;
            color: #1B2632;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-title h1 {
            margin: 2px 0;
            font-size: 13pt;
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
            margin-bottom: 10px;
        }
        .report-heading h3 {
            margin: 0;
            font-size: 10.5pt;
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
            margin: 3px 0 0 0;
            font-size: 7pt;
            color: #718096;
        }

        /* Kotak Ringkasan KPI */
        .summary-box {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            border: 1px solid #C9C1B1;
            background-color: #fcfbf9;
        }
        .summary-box td {
            padding: 5px 8px;
            font-size: 7.5pt;
            border: 0.5px solid #e2e8f0;
            vertical-align: top;
        }
        .summary-label {
            font-size: 6.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-val {
            font-size: 9pt;
            font-weight: 900;
            color: #1B2632;
            display: block;
            margin-top: 1px;
        }

        /* Tabel Data Umum */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1B2632;
            color: #EEE9DF;
            font-weight: bold;
            padding: 5px 4px;
            font-size: 6.5pt;
            text-align: center;
            border: 0.5px solid #1B2632;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .data-table td {
            padding: 3.5px 4px;
            font-size: 6.5pt;
            border: 0.5px solid #cbd5e1;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Utilitas Teks & Warna */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .nowrap { white-space: nowrap; }

        /* Badge Status Kondisi Lahan (Clean, Warning, Kritis) */
        .badge-status {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-clean {
            background-color: #ecfdf5;
            color: #065f46;
            border: 0.5px solid #a7f3d0;
        }
        .badge-warning {
            background-color: #fffbeb;
            color: #92400e;
            border: 0.5px solid #fde68a;
        }
        .badge-critical {
            background-color: #fff1f2;
            color: #9f1239;
            border: 0.5px solid #fecdd3;
        }

        /* Pengesahan */
        .signature-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }

        /* Footer Halaman Otomatis */
        .footer-page-num {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            height: 18px;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 3px;
            clear: both;
        }
        .footer-page-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin: 0;
            padding: 0;
        }
        .footer-page-table td {
            border: none;
            padding: 0;
            font-size: 6.5pt;
            color: #718096;
            background: transparent;
        }
        tr {
            page-break-inside: avoid;
        }
    </style>
    @yield('extra_styles')
</head>
<body>

    <!-- FOOTER HALAMAN OTOMATIS (FIXED POSITION TANPA FLOAT LEAK) -->
    <div class="footer-page-num">
        <table class="footer-page-table">
            <tr>
                <td style="text-align: left;">
                    SIGAP-TRANS KALSEL • Sistem Informasi Geospasial Transmigrasi (Disnakertrans Prov. Kalsel)
                </td>
                <td style="text-align: right;">
                    Dokumen Resmi Kedinasan • Format Baku A4
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <!-- KOP SURAT RESMI KEDINASAN -->
    <table class="kop-table">
        <tr>
            <td class="kop-title">
                <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
                <h1>DINAS TENAGA KERJA DAN TRANSMIGRASI</h1>
                <p>Jalan A. Yani Km. 6 No. 23 Banjarmasin • Telepon: (0511) 3252741 • Laman: disnakertrans.kalselprov.go.id</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN LAPORAN -->
    <div class="report-heading">
        <h3>@yield('report_title')</h3>
        <div class="subtitle">@yield('report_subtitle', 'Provinsi Kalimantan Selatan')</div>
        <div class="metadata">@yield('report_metadata')</div>
    </div>

    <!-- KONTEN SPESIFIK MODUL -->
    @yield('content')

    <!-- LEMBAR PENGESAHAN KEDINASAN -->
    @section('signature')
    <table class="signature-table">
        <tr>
            <td style="width: 55%; vertical-align: top; font-size: 6.5pt; color: #64748b; line-height: 1.3;">
                <div style="border-left: 2px solid #C9C1B1; padding-left: 6px;">
                    <strong>Catatan Dokumen Resmi:</strong><br>
                    1. Dokumen ini dicetak otomatis secara terintegrasi melalui Sistem SIGAP-TRANS Kalsel.<br>
                    2. Data mengikat untuk kebutuhan pelaporan internal kedinasan dan koordinasi lintas instansi.<br>
                    3. Format dokumen distandarisasi untuk ukuran kertas A4.
                </div>
            </td>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <p style="margin: 0 0 2px 0; font-size: 7pt;">{{ $signCity ?? 'Banjarbaru' }}, {{ $signDate ?? date('d F Y') }}</p>
                <p style="margin: 0; font-weight: bold; font-size: 7.5pt; line-height: 1.3;">
                    {{ $signerTitle ?? 'Kepala Bidang Ketransmigrasian' }}<br>
                    Dinas Tenaga Kerja dan Transmigrasi Prov. Kalsel,
                </p>
                <div style="height: 48px;"></div>
                <p style="margin: 0; font-weight: bold; font-size: 8pt; text-decoration: underline;">
                    {{ $signerName ?? 'Hj. Ina Yuliani, S.Sos, M.Si, M.IP' }}
                </p>
                <p style="margin: 1px 0 0 0; font-size: 7pt; color: #4a5568;">
                    NIP. {{ $signerNip ?? '19690729 199010 2 001' }}
                </p>
            </td>
        </tr>
    </table>
    @show

</body>
</html>
