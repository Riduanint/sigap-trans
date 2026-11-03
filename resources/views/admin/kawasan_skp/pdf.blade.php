<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Kawasan Transmigrasi & SKP Kalimantan Selatan</title>
    <style>
        @page {
            margin: 1.0cm 1.2cm 1.5cm 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1a202c;
            line-height: 1.35;
        }
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
        }
        .kop-title h1 {
            margin: 2px 0;
            font-size: 13pt;
            font-weight: 900;
            color: #0E2A47;
            text-transform: uppercase;
        }
        .kop-title p {
            margin: 0;
            font-size: 7.5pt;
            color: #4a5568;
        }
        .report-heading {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-heading h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: 900;
            color: #0E2A47;
            text-transform: uppercase;
        }
        .report-heading p {
            margin: 2px 0 0;
            font-size: 8pt;
            color: #4a5568;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 7.5pt;
        }
        .data-table th {
            background-color: #0E2A47;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bg-light { background-color: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 2px;
            background: #e2e8f0;
            color: #1e293b;
        }
        .meta-summary {
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .sign-area {
            margin-top: 20px;
            float: right;
            width: 240px;
            text-align: center;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td class="kop-title">
                <h2>PEMERINTAH PROVINSI KALIMANTAN SELATAN</h2>
                <h1>DINAS TENAGA KERJA DAN TRANSMIGRASI</h1>
                <p>Sistem Informasi Geospasial Administrasi Penataan Wilayah Transmigrasi (SIGAP-TRANS)</p>
            </td>
        </tr>
    </table>

    <div class="report-heading">
        <h3>DAFTAR REKAPITULASI KAWASAN TRANSMIGRASI & SKP SE-KALIMANTAN SELATAN</h3>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WITA · Dokumen Resmi Sistem SIGAP-TRANS</p>
    </div>

    <div class="meta-summary">
        <strong>Total Kawasan/SKP:</strong> {{ $stats['total_skp'] }} Unit &nbsp;|&nbsp;
        <strong>Total Desa Binaan:</strong> {{ $stats['total_villages'] }} Desa &nbsp;|&nbsp;
        <strong>Estimasi Luas Kawasan:</strong> {{ number_format($stats['total_area_ha'], 2, ',', '.') }} Ha &nbsp;|&nbsp;
        <strong>Total Penduduk:</strong> {{ number_format($stats['total_population'], 0, ',', '.') }} Jiwa ({{ number_format($stats['total_family'], 0, ',', '.') }} KK)
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 110px;">Kabupaten</th>
                <th style="width: 140px;">Kawasan Transmigrasi</th>
                <th style="width: 70px;">Satuan SKP</th>
                <th style="width: 70px;" class="text-right">Luas (Ha)</th>
                <th>Desa Binaan & Penduduk</th>
                <th style="width: 75px;" class="text-right">Total Penduduk</th>
                <th style="width: 60px;" class="text-right">Total KK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($skpAreas as $idx => $skp)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $skp->regency?->name ?? '-' }}</td>
                    <td><strong>{{ $skp->kawasan_name }}</strong></td>
                    <td>
                        {{ $skp->skp_name }}
                        @if($skp->unit_code) <span class="badge">U-{{ $skp->unit_code }}</span> @endif
                    </td>
                    <td class="text-right">{{ $skp->luas_ha ? number_format($skp->luas_ha, 2, ',', '.') : '-' }}</td>
                    <td>
                        @if($skp->villages->isNotEmpty())
                            @foreach($skp->villages as $v)
                                <span>• {{ $v->village_name }} ({{ number_format($v->area_km2, 1, ',', '.') }} Km² - {{ number_format($v->population_total, 0, ',', '.') }} jiwa)</span><br>
                            @endforeach
                        @else
                            <em style="color: #94a3b8;">Belum terdata desa</em>
                        @endif
                    </td>
                    <td class="text-right font-bold">{{ number_format($skp->total_population, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($skp->total_family_count, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada data Kawasan SKP</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="sign-area">
        <p>Banjarmasin, {{ now()->translatedFormat('d F Y') }}<br>
        Kepala Bidang Transmigrasi<br>
        Disnakertrans Provinsi Kalimantan Selatan</p>
        <br><br><br>
        <p><strong>........................................................</strong><br>
        NIP. ....................................................</p>
    </div>
</body>
</html>
