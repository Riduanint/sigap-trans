<div class="atlas-metrics" aria-label="Ringkasan analitik kependudukan">
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalPlacementKk, 0, ',', '.') }} <span>KK</span></strong>
            <small>Kepala keluarga penempatan awal</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalPlacementPop, 0, ',', '.') }} <span>jiwa</span></strong>
            <small>Total penduduk penempatan awal</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $avgJiwaPerKk }} <span>jiwa/KK</span></strong>
            <small>Rata-rata kepadatan anggota per keluarga</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalHandoverKk, 0, ',', '.') }} <span>KK</span></strong>
            <small>KK tercatat pada serah terima Pemda</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $growthKk >= 0 ? '+' : '' }}{{ number_format($growthKk, 0, ',', '.') }}</strong>
            <small>Selisih KK penempatan–serah terima ({{ $growthPct }}%)</small>
        </div>
    </div>
</div>

<div class="atlas-detail-grid">
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Komposisi jenis transmigran</h2>
            <span class="atlas-muted">Registri Penempatan</span>
        </div>
        <div class="atlas-panel-body">
            @if($tpaCount + $tpsCount + $otherTypeCount > 0)
                <div style="height: 220px; position: relative;">
                    <canvas id="atlas-demog-type-chart" aria-label="Grafik komposisi TPA dan TPS" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada pengelompokan TPA/TPS tercatat pada registri penempatan.</div>
            @endif
            <dl class="atlas-definition mt-5">
                <div><dt>TPA — Penduduk Asal</dt><dd>{{ number_format($tpaCount, 0, ',', '.') }} KK</dd></div>
                <div><dt>TPS — Penduduk Setempat</dt><dd>{{ number_format($tpsCount, 0, ',', '.') }} KK</dd></div>
                @if($otherTypeCount > 0)
                    <div><dt>Lainnya / Belum Terinci</dt><dd>{{ number_format($otherTypeCount, 0, ',', '.') }} KK</dd></div>
                @endif
            </dl>
        </div>
    </section>

    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Perbandingan KK penempatan vs serah terima</h2>
            <span class="atlas-muted">Per Kabupaten</span>
        </div>
        <div class="atlas-panel-body">
            @if($regencyDemographics->isNotEmpty())
                <div style="height: 260px; position: relative;">
                    <canvas id="atlas-demog-comparison-chart" aria-label="Grafik perbandingan KK per kabupaten" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data demografi kabupaten.</div>
            @endif
        </div>
    </section>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Rekapitulasi demografi per wilayah kabupaten</h2>
        <div class="atlas-inline">
            <a class="atlas-link" href="{{ route('admin.placements.index') }}">Buka Penempatan Awal →</a>
            <span class="atlas-muted">·</span>
            <a class="atlas-link" href="{{ route('admin.handovers.index') }}">Buka Serah Terima →</a>
        </div>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">UPT</th>
                    <th scope="col">KK Penempatan</th>
                    <th scope="col">Jiwa Penempatan</th>
                    <th scope="col">KK Serah Terima</th>
                    <th scope="col">Jiwa Serah Terima</th>
                    <th scope="col">Selisih KK</th>
                    <th scope="col">Kepadatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regencyDemographics as $d)
                    <tr>
                        <td><strong>{{ $d->name }}</strong></td>
                        <td class="tabular-nums">{{ $d->upt_count }}</td>
                        <td class="tabular-nums">{{ number_format($d->placement_kk, 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ number_format($d->placement_pop, 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ number_format($d->handover_kk, 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ number_format($d->handover_pop, 0, ',', '.') }}</td>
                        <td class="tabular-nums font-semibold">{{ $d->growth_kk >= 0 ? '+' : '' }}{{ number_format($d->growth_kk, 0, ',', '.') }}</td>
                        <td class="tabular-nums text-slate-500">{{ $d->avg_pop_per_kk }} jiwa/KK</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="atlas-empty">Tidak ada data demografi yang sesuai filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>UPT dengan jumlah populasi penempatan terbesar</h2>
        <span class="atlas-muted">10 UPT Tertinggi</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Nama UPT</th>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Tahun Penempatan</th>
                    <th scope="col">KK Penempatan</th>
                    <th scope="col">Total Jiwa</th>
                    <th scope="col">Status Penyerahan</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPopulationUpts as $u)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <strong>UPT-{{ $u->upt_number }} {{ $u->upt_name }}</strong>
                                <span class="atlas-muted">{{ $u->current_village_name }}</span>
                            </div>
                        </td>
                        <td>{{ $u->regency?->name ?? '—' }}</td>
                        <td class="tabular-nums">{{ $u->placement_year ?: '—' }}</td>
                        <td class="tabular-nums font-medium">{{ number_format($u->placement_kk, 0, ',', '.') }} KK</td>
                        <td class="tabular-nums font-bold text-slate-800">{{ number_format($u->placement_population, 0, ',', '.') }} Jiwa</td>
                        <td>
                            @if($u->handover_year || $u->handover_kk > 0)
                                <span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Definitif Pemda</span>
                            @else
                                <span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Binaan Provinsi</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.upt.show', ['id' => $u->id, 'tab' => 'population']) }}" class="atlas-action-icon atlas-action-icon--detail" title="Lihat Profil Kependudukan" aria-label="Detail">
                                    <x-admin.icon name="eye" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">Belum ada data UPT yang memenuhi kriteria filter.</div>
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

    // Grafik TPA vs TPS
    const ctxType = document.getElementById('atlas-demog-type-chart');
    if (ctxType) {
        new Chart(ctxType.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['TPA — Penduduk Asal', 'TPS — Penduduk Setempat', 'Lainnya'],
                datasets: [{
                    data: [{{ $tpaCount }}, {{ $tpsCount }}, {{ $otherTypeCount }}],
                    backgroundColor: ['#0E2A47', '#107C41', '#94A3B8'],
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
                    tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.parsed.toLocaleString('id-ID') + ' KK' } }
                }
            }
        });
    }

    // Grafik Perbandingan Penempatan vs Serah Terima
    const ctxComp = document.getElementById('atlas-demog-comparison-chart');
    if (ctxComp) {
        const labels = {!! json_encode($regencyDemographics->pluck('name')) !!};
        const placementData = {!! json_encode($regencyDemographics->pluck('placement_kk')) !!};
        const handoverData = {!! json_encode($regencyDemographics->pluck('handover_kk')) !!};

        new Chart(ctxComp.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'KK Penempatan Awal',
                        data: placementData,
                        backgroundColor: '#0E2A47CC',
                        borderRadius: 4,
                    },
                    {
                        label: 'KK Serah Terima',
                        data: handoverData,
                        backgroundColor: '#107C41CC',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, color: '#243746', font } },
                    tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + c.parsed.y.toLocaleString('id-ID') + ' KK' } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#D4DEE766' }, ticks: { color: '#5b7285', font } },
                    x: { grid: { display: false }, ticks: { color: '#243746', font } }
                }
            }
        });
    }
});
</script>
@endpush

