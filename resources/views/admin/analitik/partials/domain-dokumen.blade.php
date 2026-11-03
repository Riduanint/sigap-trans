<div class="atlas-metrics" aria-label="Ringkasan analitik arsip dokumen">
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalDocs, 0, ',', '.') }}</strong>
            <small>Total berkas digital tersimpan di e-Arsip</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalBast, 0, ',', '.') }}</strong>
            <small>Warkah Berita Acara Serah Terima (BAST)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalSk, 0, ',', '.') }}</strong>
            <small>Surat Keputusan (SK Menteri / Gubernur)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalBukuTanah, 0, ',', '.') }}</strong>
            <small>Buku Tanah & Sertipikat Hak Milik</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $totalSizeMb }} <span>MB</span></strong>
            <small>Kapasitas penyimpanan media digital</small>
        </div>
    </div>
</div>

<div class="atlas-detail-grid">
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Komposisi jenis dokumen e-Arsip</h2>
            <span class="atlas-muted">{{ $totalDocs }} Berkas</span>
        </div>
        <div class="atlas-panel-body">
            @if($totalDocs > 0)
                <div style="height: 220px; position: relative;">
                    <canvas id="atlas-docs-type-chart" aria-label="Grafik jenis dokumen" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada berkas dokumen yang tersimpan pada filter ini.</div>
            @endif
            <dl class="atlas-definition mt-5">
                <div><dt>BAST Penyerahan</dt><dd class="text-emerald-700 font-semibold">{{ $totalBast }} Berkas</dd></div>
                <div><dt>SK Penetapan</dt><dd class="text-blue-700 font-semibold">{{ $totalSk }} Berkas</dd></div>
                <div><dt>Buku Tanah / Warkah</dt><dd class="text-amber-700 font-semibold">{{ $totalBukuTanah }} Berkas</dd></div>
            </dl>
        </div>
    </section>

    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Distribusi arsip dokumen per kabupaten</h2>
            <span class="atlas-muted">Total Berkas Digital</span>
        </div>
        <div class="atlas-panel-body">
            @if($regencyDocs->isNotEmpty())
                <div style="height: 260px; position: relative;">
                    <canvas id="atlas-docs-regency-chart" aria-label="Grafik dokumen per kabupaten" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data dokumen kabupaten.</div>
            @endif
        </div>
    </section>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Matriks kelengkapan dokumen BAST & SK per kabupaten</h2>
        <a class="atlas-link" href="{{ route('admin.documents.index') }}">Buka Repositori e-Arsip →</a>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Total UPT</th>
                    <th scope="col">Total Berkas</th>
                    <th scope="col">Berkas BAST</th>
                    <th scope="col">Berkas SK</th>
                    <th scope="col">Buku Tanah</th>
                    <th scope="col">Kapasitas</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regencyDocs as $rd)
                    <tr>
                        <td><strong>{{ $rd->name }}</strong></td>
                        <td class="tabular-nums font-semibold">{{ $rd->upt_count }}</td>
                        <td class="tabular-nums font-bold">{{ $rd->total_docs }}</td>
                        <td class="tabular-nums text-emerald-700 font-medium">{{ $rd->total_bast }}</td>
                        <td class="tabular-nums text-blue-700 font-medium">{{ $rd->total_sk }}</td>
                        <td class="tabular-nums text-amber-700 font-medium">{{ $rd->total_buku_tanah }}</td>
                        <td class="tabular-nums text-slate-500">{{ $rd->size_mb }} MB</td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.documents.index', ['regency_id' => $rd->id]) }}" class="atlas-action-icon atlas-action-icon--detail" title="Lihat Dokumen di {{ $rd->name }}" aria-label="Detail">
                                    <x-admin.icon name="eye" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="atlas-empty">Tidak ada data dokumen wilayah yang sesuai filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Berkas e-Arsip terkini yang diunggah</h2>
        <span class="atlas-muted">10 Berkas Terakhir</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Nama Berkas</th>
                    <th scope="col">UPT & Wilayah</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Nomor Dokumen</th>
                    <th scope="col">Ukuran</th>
                    <th scope="col">Tanggal Unggah</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDocs as $doc)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <strong>{{ $doc->file_name }}</strong>
                                <span class="atlas-muted">Oleh: {{ $doc->uploader?->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($doc->uptLocation)
                                <span class="text-xs font-medium">UPT-{{ $doc->uptLocation->upt_number }} {{ $doc->uptLocation->upt_name }}</span>
                                <span class="atlas-muted block text-xs">{{ $doc->uptLocation->regency?->name ?? '—' }}</span>
                            @else
                                <span class="atlas-muted text-xs">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($doc->document_type) {
                                    'BAST' => 'atlas-status--clean',
                                    'SK_GUBERNUR', 'SK_MENTERI' => 'atlas-status--info',
                                    default => ''
                                };
                            @endphp
                            <span class="atlas-status {{ $badgeClass }}"><span aria-hidden="true"></span>{{ $doc->document_type }}</span>
                        </td>
                        <td class="font-mono text-xs">{{ $doc->document_number ?: '—' }}</td>
                        <td class="tabular-nums text-xs text-slate-500">{{ round($doc->file_size_kb / 1024, 2) }} MB</td>
                        <td class="tabular-nums text-xs text-slate-500">{{ $doc->created_at?->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA</td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.documents.download', $doc->id) }}" class="atlas-action-icon atlas-action-icon--download" title="Unduh Berkas" aria-label="Unduh">
                                    <x-admin.icon name="download" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">Belum ada berkas arsip yang diunggah ke repositori.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: "'Source Sans 3', sans-serif" };

    // Grafik Jenis Dokumen
    const ctxDocType = document.getElementById('atlas-docs-type-chart');
    if (ctxDocType) {
        new Chart(ctxDocType.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['BAST Penyerahan', 'SK Penetapan', 'Buku Tanah / Warkah'],
                datasets: [{
                    data: [{{ $totalBast }}, {{ $totalSk }}, {{ $totalBukuTanah }}],
                    backgroundColor: ['#107C41', '#2457A7', '#D97706'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, color: '#243746', font } },
                    tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.parsed.toLocaleString('id-ID') + ' Berkas' } }
                }
            }
        });
    }

    // Grafik Dokumen per Kabupaten
    const ctxDocReg = document.getElementById('atlas-docs-regency-chart');
    if (ctxDocReg) {
        const labels = {!! json_encode($regencyDocs->pluck('name')) !!};
        const docCounts = {!! json_encode($regencyDocs->pluck('total_docs')) !!};
        new Chart(ctxDocReg.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Berkas Digital',
                    data: docCounts,
                    backgroundColor: '#0E2A47CC',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.y.toLocaleString('id-ID') + ' Berkas' } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#5b7285', font }, grid: { color: '#D4DEE766' } },
                    x: { grid: { display: false }, ticks: { color: '#243746', font } }
                }
            }
        });
    }
});
</script>
@endpush

