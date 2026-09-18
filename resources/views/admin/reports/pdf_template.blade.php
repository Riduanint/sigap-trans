<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi 124 UPT Transmigrasi Kalsel</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1a202c;
            line-height: 1.3;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #0B1849;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .kop-title {
            text-align: center;
        }
        .kop-title h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0B1849;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-title h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: 900;
            color: #124D1C;
            text-transform: uppercase;
        }
        .kop-title p {
            margin: 0;
            font-size: 8pt;
            color: #4a5568;
        }
        .report-heading {
            text-align: center;
            margin-bottom: 14px;
        }
        .report-heading h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            color: #0B1849;
            text-transform: uppercase;
        }
        .report-heading p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            color: #718096;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .summary-box td {
            padding: 5px 8px;
            font-size: 8pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #0B1849;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            font-size: 7.5pt;
            text-align: center;
            border: 0.5px solid #0B1849;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4px 4px;
            font-size: 7.5pt;
            border: 0.5px solid #cbd5e1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f1f5f9; }
        
        .status-clean { color: #047857; font-weight: bold; }
        .status-warning { color: #b45309; font-weight: bold; }
        .status-critical { color: #b91c1c; font-weight: bold; }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT KEDINASAN -->
    <table class="kop-table">
        <tr>
            <td class="kop-title">
                <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
                <h1>DINAS TENAGA KERJA DAN TRANSMIGRASI</h1>
                <p>Jalan A. Yani Km. 6 No. 23 Banjarmasin • Telepon: (0511) 3252741 • Laman: disnakertrans.kalselprov.go.id</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div class="report-heading">
        <h3>REKAPITULASI DATA HISTORIS 124 UNIT PEMUKIMAN TRANSMIGRASI (UPT)</h3>
        <p>Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi (SIGAP-TRANS KALSEL 1953–2025)</p>
    </div>

    <!-- RINGKASAN PROVINSI -->
    <table class="summary-box">
        <tr>
            <td><strong>Total Cakupan:</strong> {{ count($locations) }} Lokasi UPT (9 Kabupaten)</td>
            <td><strong>Penempatan Awal:</strong> {{ number_format($totalPlacementKk) }} KK / {{ number_format($totalPlacementPop) }} Jiwa</td>
            <td><strong>Serah Terima Pemda:</strong> {{ number_format($totalHandoverKk) }} KK / {{ number_format($totalHandoverPop) }} Jiwa</td>
            <td><strong>Pertumbuhan Alami:</strong> +{{ number_format($totalHandoverKk - $totalPlacementKk) }} KK</td>
        </tr>
    </table>

    <!-- TABEL DATA 124 UPT -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 45px;">KODE</th>
                <th style="width: 70px;">KABUPATEN</th>
                <th>NAMA UPT ASAL</th>
                <th>DESA DEFINITIF</th>
                <th style="width: 40px;">POLA</th>
                <th style="width: 50px;">MASUK</th>
                <th style="width: 45px;">KK IN</th>
                <th style="width: 45px;">JIWA IN</th>
                <th style="width: 55px;">SERAH</th>
                <th style="width: 45px;">KK OUT</th>
                <th style="width: 45px;">JIWA OUT</th>
                <th style="width: 55px;">STATUS</th>
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
                <td colspan="7" class="text-right" style="padding-right: 8px;">TOTAL SE-PROVINSI KALIMANTAN SELATAN:</td>
                <td class="text-center font-bold">{{ number_format($totalPlacementKk) }}</td>
                <td class="text-center font-bold">{{ number_format($totalPlacementPop) }}</td>
                <td></td>
                <td class="text-center font-bold">{{ number_format($totalHandoverKk) }}</td>
                <td class="text-center font-bold">{{ number_format($totalHandoverPop) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 65%;"></td>
            <td style="width: 35%; text-align: center;">
                <p style="margin-bottom: 2px;">Banjarmasin, {{ date('d F Y') }}</p>
                <p style="margin-top: 0; font-weight: bold;">Kepala Bidang Ketransmigrasian<br>Disnakertrans Prov. Kalsel,</p>
                <div style="height: 50px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">Hj. Ina Yuliani, S.Sos, M.Si, M.IP</p>
                <p style="margin: 2px 0 0 0; font-size: 8pt; color: #4a5568;">NIP. 19690729 199010 2 001</p>
            </td>
        </tr>
    </table>

</body>
</html>
