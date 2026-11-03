@php
    $handoverRate = round(($definitifUptCount / max(1, $totalUpt)) * 100, 1);
@endphp

<div class="atlas-metrics" aria-label="Ringkasan analitik data UPT">
    <div class="atlas-metric">
        <div>
            <strong>{{ $definitifUptCount }}<span>/ {{ $totalUpt }}</span></strong>
            <small>UPT diserahkan ke Pemda ({{ $handoverRate }}%) · {{ $binaanUptCount }} masih binaan</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalHandoverKk, 0, ',', '.') }}</strong>
            <small>KK serah terima · awal {{ number_format($totalPlacementKk, 0, ',', '.') }} KK</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $handoverGrowthKk >= 0 ? '+' : '' }}{{ number_format($handoverGrowthKk, 0, ',', '.') }}</strong>
            <small>Selisih penempatan–serah terima ({{ $handoverGrowthPct }}%)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ number_format($totalPlacementPop, 0, ',', '.') }}<span>/ {{ number_format($totalHandoverPop, 0, ',', '.') }}</span></strong>
            <small>Jiwa: Penempatan awal / Serah terima</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $cleanCount }}<span>/ {{ $warningCount }}<span>/ {{ $criticalCount }}</span></span></strong>
            <small>Lahan: Clean / Monitoring / Kritis</small>
        </div>
    </div>
</div>

<div class="atlas-detail-grid">
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Tren penempatan KK per dekade</h2>
            <span class="atlas-muted">1950–2025</span>
        </div>
        <div class="atlas-panel-body">
            <div style="height: 260px; position: relative;">
                <canvas id="atlas-decade-chart" aria-label="Grafik tren penempatan KK per dekade" role="img"></canvas>
            </div>
            <div class="atlas-definition mt-5">
                <div><dt>1970-an (Pelita)</dt><dd>{{ number_format($decadesData['1970–1979']['kk'] ?? 0, 0, ',', '.') }} KK</dd></div>
                <div><dt>Puncak 1980-an</dt><dd>{{ number_format($decadesData['1980–1989']['kk'] ?? 0, 0, ',', '.') }} KK</dd></div>
                <div><dt>1990-an</dt><dd>{{ number_format($decadesData['1990–1999']['kk'] ?? 0, 0, ',', '.') }} KK</dd></div>
                <div><dt>2000–2025</dt><dd>{{ number_format(($decadesData['2000–2009']['kk'] ?? 0) + ($decadesData['2010–2025']['kk'] ?? 0), 0, ',', '.') }} KK</dd></div>
            </div>
        </div>
    </section>

    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Pola usaha pengembangan UPT</h2>
            <span class="atlas-muted">{{ count($patterns) }} Pola Usaha</span>
        </div>
        <div class="atlas-panel-body">
            @if(count($patterns) > 0)
                <div style="height: 220px; position: relative;">
                    <canvas id="atlas-pattern-chart" aria-label="Grafik pola usaha UPT" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data pola usaha tercatat.</div>
            @endif
            <dl class="atlas-definition mt-5">
                @foreach($patterns as $patternName => $pCount)
                    <div>
                        <dt>{{ $patternName ?: 'Lainnya / Belum Tercatat' }}</dt>
                        <dd>{{ number_format($pCount, 0, ',', '.') }} UPT</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Perbandingan kinerja wilayah kabupaten</h2>
        <span class="atlas-muted">{{ $regenciesData->count() }} kabupaten · {{ number_format($regenciesData->sum('upt_locations_count'), 0, ',', '.') }} UPT</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">UPT</th>
                    <th scope="col">KK penempatan</th>
                    <th scope="col">KK serah terima</th>
                    <th scope="col">Selisih</th>
                    <th scope="col">Kondisi lahan</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regenciesData as $r)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <strong>{{ $r->name }}</strong>
                                <span class="atlas-muted">Ibukota: {{ $r->capital_city ?? 'Belum tercatat' }}</span>
                            </div>
                        </td>
                        <td class="tabular-nums">{{ $r->upt_locations_count ?? 0 }}</td>
                        <td class="tabular-nums">{{ number_format($r->total_placement_kk ?? 0, 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ number_format($r->total_handover_kk ?? 0, 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ $r->growth_kk >= 0 ? '+' : '' }}{{ number_format($r->growth_kk, 0, ',', '.') }}</td>
                        <td>
                            <span class="atlas-muted">{{ $r->clean_count }} clean · {{ $r->warning_count }} monitoring · {{ $r->critical_count }} kritis</span>
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.upt.index', ['regency_id' => $r->id]) }}" class="atlas-action-icon atlas-action-icon--detail" title="Lihat UPT di {{ $r->name }}" aria-label="Lihat UPT">
                                    <x-admin.icon name="eye" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">Tidak ada data wilayah kabupaten yang sesuai dengan filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Lokasi kritis — prioritas mediasi agraria</h2>
        <a class="atlas-link" href="{{ route('admin.sertifikat-tanah.index', ['issue_status' => 'critical']) }}">Buka pertanahan →</a>
    </div>
    @forelse($criticalCases as $c)
        <div class="atlas-request">
            <div>
                <x-admin.upt-identity :upt="$c" />
                <p class="atlas-reading mt-2" style="font-size: 13px;">{{ $c->issue_note ?: $c->notes_issue ?: 'Tumpang tindih klaim kawasan hutan / permasalahan tapal batas bidang tanah warga.' }}</p>
            </div>
            <div class="atlas-request-actions">
                <a class="atlas-button" href="{{ route('admin.upt.show', ['id' => $c->id, 'tab' => 'land']) }}">Tinjau</a>
            </div>
        </div>
    @empty
        <div class="atlas-empty">Tidak ada lokasi berstatus kritis saat ini pada cakupan wilayah yang dipilih.</div>
    @endforelse
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: "'Source Sans 3', sans-serif" };

    // Grafik Tren Dekade
    const ctxDecade = document.getElementById('atlas-decade-chart');
    if (ctxDecade) {
        new Chart(ctxDecade.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['1950–59', '1960–69', '1970–79', '1980–89', '1990–99', '2000–09', '2010–25'],
                datasets: [{
                    label: 'KK ditempatkan',
                    data: [
                        {{ $decadesData['1950–1959']['kk'] ?? 0 }},
                        {{ $decadesData['1960–1969']['kk'] ?? 0 }},
                        {{ $decadesData['1970–1979']['kk'] ?? 0 }},
                        {{ $decadesData['1980–1989']['kk'] ?? 0 }},
                        {{ $decadesData['1990–1999']['kk'] ?? 0 }},
                        {{ $decadesData['2000–2009']['kk'] ?? 0 }},
                        {{ $decadesData['2010–2025']['kk'] ?? 0 }}
                    ],
                    backgroundColor: '#0E2A47CC',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.y.toLocaleString('id-ID') + ' KK' } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#D4DEE766' }, ticks: { color: '#5b7285', font }, border: { display: false } },
                    x: { grid: { display: false }, ticks: { color: '#243746', font }, border: { color: '#D4DEE7' } }
                }
            }
        });
    }

    // Grafik Pola Usaha
    const ctxPattern = document.getElementById('atlas-pattern-chart');
    if (ctxPattern) {
        const patternLabels = {!! json_encode(array_keys($patterns)) !!};
        const patternValues = {!! json_encode(array_values($patterns)) !!};
        new Chart(ctxPattern.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: patternLabels,
                datasets: [{
                    data: patternValues,
                    backgroundColor: ['#0E2A47', '#107C41', '#2457A7', '#D97706', '#0891B2', '#64748B'],
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
                    tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.parsed.toLocaleString('id-ID') + ' UPT' } }
                }
            }
        });
    }
});
</script>
@endpush

