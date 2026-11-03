<div class="atlas-metrics" aria-label="Ringkasan analitik Kawasan SKP">
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalSkp, 0, ',', '.') }}</strong>
            <small>Total Kawasan & Satuan Pemukiman (SKP)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalLuasHa, 2, ',', '.') }} <span>Ha</span></strong>
            <small>Total luas wilayah kawasan SKP</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalVillages, 0, ',', '.') }}</strong>
            <small>Desa definitif terhubung dalam SKP</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalPopulation, 0, ',', '.') }} <span>jiwa</span></strong>
            <small>Total penduduk di desa kawasan SKP</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalFamily, 0, ',', '.') }} <span>KK</span></strong>
            <small>Kepala keluarga tercatat di desa SKP</small>
        </div>
    </div>
</div>

<div class="atlas-detail-grid">
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Luas kawasan SKP per kabupaten</h2>
            <span class="atlas-muted">Satuan Hektar (Ha)</span>
        </div>
        <div class="atlas-panel-body">
            @if($regencySkpData->isNotEmpty())
                <div style="height: 260px; position: relative;">
                    <canvas id="atlas-skp-area-chart" aria-label="Grafik luas kawasan SKP per kabupaten" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data luas kawasan SKP pada filter ini.</div>
            @endif
        </div>
    </section>

    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Sebaran desa per kawasan SKP</h2>
            <span class="atlas-muted">{{ $skpList->count() }} Kawasan SKP</span>
        </div>
        <div class="atlas-panel-body">
            @if($skpList->isNotEmpty())
                <div style="height: 260px; position: relative;">
                    <canvas id="atlas-skp-villages-chart" aria-label="Grafik sebaran desa per kawasan SKP" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data kawasan SKP.</div>
            @endif
        </div>
    </section>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Daftar kawasan & satuan pemukiman (SKP)</h2>
        <a class="atlas-link" href="{{ route('admin.skp.index') }}">Buka manajemen SKP →</a>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Nama Kawasan SKP</th>
                    <th scope="col">Kode Unit</th>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Luas (Ha)</th>
                    <th scope="col">Desa Terhubung</th>
                    <th scope="col">Status Batas</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skpList as $s)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <strong>{{ $s->kawasan_name }}</strong>
                                <span class="atlas-muted">{{ $s->skp_name }}</span>
                            </div>
                        </td>
                        <td class="tabular-nums font-mono text-xs">{{ $s->unit_code ?: '—' }}</td>
                        <td>{{ $s->regency?->name ?? '—' }}</td>
                        <td class="tabular-nums font-semibold">{{ number_format($s->luas_ha, 2, ',', '.') }} Ha</td>
                        <td class="tabular-nums">{{ $s->villages->count() }} desa</td>
                        <td>
                            @if($s->polygon_geojson)
                                <span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Polygon Aktif</span>
                            @else
                                <span class="atlas-status"><span aria-hidden="true"></span>Belum Ada Peta</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.skp.show', $s->id) }}" class="atlas-action-icon atlas-action-icon--detail" title="Lihat Rincian SKP" aria-label="Detail">
                                    <x-admin.icon name="eye" />
                                </a>
                                <a href="{{ route('admin.skp.edit', $s->id) }}" class="atlas-action-icon atlas-action-icon--edit" title="Perbarui SKP" aria-label="Edit">
                                    <x-admin.icon name="edit" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">Tidak ada kawasan SKP yang sesuai dengan kriteria filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Desa definitif terhubung dalam kawasan SKP</h2>
        <span class="atlas-muted">10 desa dengan populasi terbesar</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Desa & Kecamatan</th>
                    <th scope="col">Kawasan SKP</th>
                    <th scope="col">Luas Wilayah</th>
                    <th scope="col">Penduduk (L / P)</th>
                    <th scope="col">Total Jiwa</th>
                    <th scope="col">Jumlah KK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topVillages as $v)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <strong>{{ $v->village_name }}</strong>
                                <span class="atlas-muted">Kec. {{ $v->district_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-xs font-medium">{{ $v->skpArea?->kawasan_name ?? '—' }}</span>
                            <span class="atlas-muted block text-xs">{{ $v->skpArea?->regency?->name ?? '—' }}</span>
                        </td>
                        <td class="tabular-nums">{{ number_format($v->area_km2, 2, ',', '.') }} Km²</td>
                        <td class="tabular-nums text-xs text-slate-500">{{ number_format($v->population_male, 0, ',', '.') }} L · {{ number_format($v->population_female, 0, ',', '.') }} P</td>
                        <td class="tabular-nums font-semibold">{{ number_format($v->population_total, 0, ',', '.') }} Jiwa</td>
                        <td class="tabular-nums text-emerald-700 font-medium">{{ number_format($v->family_count, 0, ',', '.') }} KK</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="atlas-empty">Belum ada data desa terhubung dalam kawasan SKP yang dipilih.</div>
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

    // Grafik Luas SKP per Kabupaten
    const ctxArea = document.getElementById('atlas-skp-area-chart');
    if (ctxArea) {
        const areaLabels = {!! json_encode($regencySkpData->pluck('name')) !!};
        const areaValues = {!! json_encode($regencySkpData->pluck('luas_ha')) !!};
        new Chart(ctxArea.getContext('2d'), {
            type: 'bar',
            data: {
                labels: areaLabels,
                datasets: [{
                    label: 'Luas Kawasan (Ha)',
                    data: areaValues,
                    backgroundColor: '#107C41CC',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.y.toLocaleString('id-ID') + ' Ha' } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#D4DEE766' }, ticks: { color: '#5b7285', font }, border: { display: false } },
                    x: { grid: { display: false }, ticks: { color: '#243746', font }, border: { color: '#D4DEE7' } }
                }
            }
        });
    }

    // Grafik Desa per SKP
    const ctxVillages = document.getElementById('atlas-skp-villages-chart');
    if (ctxVillages) {
        const skpNames = {!! json_encode($skpList->pluck('kawasan_name')) !!};
        const villageCounts = {!! json_encode($skpList->map(fn($s) => $s->villages->count())) !!};
        new Chart(ctxVillages.getContext('2d'), {
            type: 'bar',
            data: {
                labels: skpNames,
                datasets: [{
                    label: 'Jumlah Desa Definitif',
                    data: villageCounts,
                    backgroundColor: '#0E2A47CC',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.x.toLocaleString('id-ID') + ' Desa' } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, color: '#5b7285', font }, grid: { color: '#D4DEE766' } },
                    y: { ticks: { color: '#243746', font }, grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush

