@extends('admin.reports.pdf.layout')

@section('report_title', 'BUKU REGISTER E-ARSIP DOKUMEN BAST & LEGALITAS')
@section('report_subtitle', 'Repositori Arsip Digital Berita Acara Serah Terima, SK Penetapan, dan Dokumen Pertanahan')

@section('report_metadata')
    Cakupan Wilayah: <strong>{{ $filterRegencyName ?? 'Seluruh Wilayah (9 Kabupaten)' }}</strong> • 
    Jenis Dokumen: <strong>{{ $filterTypeLabel ?? 'Semua Jenis Berkas' }}</strong> • 
    Total Data: <strong>{{ $documents->count() }} Berkas Arsip</strong> • 
    Waktu Cetak: {{ $signDate ?? date('d F Y') }} WITA
@endsection

@section('content')

    <!-- Ringkasan Statistik E-Arsip -->
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">
                <span class="summary-label">Total Berkas Digital</span>
                <span class="summary-val">{{ $documents->count() }} Dokumen</span>
                <span style="font-size: 6pt; color: #64748b;">Tersimpan di Repositori E-Arsip</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Berkas Berita Acara (BAST)</span>
                <span class="summary-val" style="color: #065f46;">{{ $documents->where('document_type', 'BAST')->count() }} Berkas</span>
                <span style="font-size: 6pt; color: #64748b;">Serah Terima Aset Pemda</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">SK Penetapan / Pelepasan</span>
                <span class="summary-val" style="color: #0284c7;">{{ $documents->whereIn('document_type', ['SK_GUBERNUR', 'SK_MENTERI'])->count() }} Berkas</span>
                <span style="font-size: 6pt; color: #64748b;">SK Gubernur / Menteri</span>
            </td>
            <td style="width: 25%;">
                <span class="summary-label">Buku Tanah / Sertipikat</span>
                <span class="summary-val" style="color: #92400e;">{{ $documents->where('document_type', 'BUKU_TANAH')->count() }} Berkas</span>
                <span style="font-size: 6pt; color: #64748b;">Warkah Agraria / HPL</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Register Dokumen -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 22%; text-align: left;">Nomor Dokumen / Surat</th>
                <th style="width: 16%;">Jenis Dokumen</th>
                <th style="width: 20%; text-align: left;">UPT Sasaran</th>
                <th style="width: 16%; text-align: left;">Kabupaten</th>
                <th style="width: 11%;">Tanggal Dokumen</th>
                <th style="width: 11%;">Ukuran Berkas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $idx => $doc)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold text-left" style="font-size: 6pt;">{{ $doc->document_number ?: '-' }}</td>
                    <td class="text-center font-bold" style="font-size: 6pt;">
                        @if($doc->document_type === 'BAST')
                            <span style="color: #065f46;">BAST Pemda</span>
                        @elseif($doc->document_type === 'BUKU_TANAH')
                            <span style="color: #92400e;">Buku Tanah</span>
                        @elseif(str_contains($doc->document_type, 'SK'))
                            <span style="color: #0284c7;">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                        @else
                            {{ $doc->document_type ?: 'Lainnya' }}
                        @endif
                    </td>
                    <td class="text-left font-bold" style="font-size: 6pt;">
                        {{ $doc->uptLocation?->upt_name ?? 'Umum / Terpadu' }}
                        @if($doc->uptLocation?->upt_number)
                            <span style="font-size: 5.5pt; color: #64748b;">(UPT-{{ str_pad($doc->uptLocation->upt_number, 3, '0', STR_PAD_LEFT) }})</span>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 6pt;">{{ $doc->uptLocation?->regency?->name ? 'Kab. ' . $doc->uptLocation->regency->name : '-' }}</td>
                    <td class="text-center" style="font-size: 6pt;">{{ $doc->document_date ? date('d/m/Y', strtotime($doc->document_date)) : '-' }}</td>
                    <td class="text-center" style="font-size: 5.5pt; color: #64748b;">
                        {{ $doc->file_size ? number_format($doc->file_size / 1024, 0) . ' KB' : 'Digital' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada arsip dokumen yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-center" style="font-weight: 900;">TOTAL BERKAS TERVERIFIKASI</td>
                <td colspan="2" class="text-center" style="font-weight: 900;">{{ $documents->count() }} Dokumen Fisik/Digital</td>
            </tr>
        </tfoot>
    </table>

@endsection
