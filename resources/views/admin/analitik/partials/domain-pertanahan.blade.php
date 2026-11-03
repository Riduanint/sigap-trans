@php
    $shmTuntasRate = $totalUpt > 0 ? round(($shmTuntas / $totalUpt) * 100, 1) : 0;
    $cleanRate = $totalUpt > 0 ? round(($cleanCount / $totalUpt) * 100, 1) : 0;
@endphp

<div class="atlas-metrics" aria-label="Ringkasan analitik pertanahan">
    <div class="atlas-metric">
        <div>
            <strong>{{ $shmTuntas }}<span>/ {{ $totalUpt }}</span></strong>
            <small>UPT SHM Tuntas 100% ({{ $shmTuntasRate }}% dari total)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $shmSebagian }}</strong>
            <small>UPT dalam proses / sertipikasi sebagian</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $shmBelum }}</strong>
            <small>UPT belum tersertifikasi / dalam inventarisasi</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $cleanCount }}<span>/ {{ $totalUpt }}</span></strong>
            <small>Lahan Clean & Clear ({{ $cleanRate }}%)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $warningCount + $criticalCount }}</strong>
            <small>Kasus lahan teridentifikasi ({{ $criticalCount }} kritis · {{ $warningCount }} pantau)</small>
        </div>
    </div>
</div>

<div class="atlas-detail-grid">
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Proporsi capaian sertipikasi SHM</h2>
            <span class="atlas-muted">{{ $totalUpt }} UPT</span>
        </div>
        <div class="atlas-panel-body">
            @if($totalUpt > 0)
                <div style="height: 220px; position: relative;">
                    <canvas id="atlas-shm-doughnut-chart" aria-label="Grafik capaian SHM" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data pertanahan UPT.</div>
            @endif
            <dl class="atlas-definition mt-5">
                <div><dt>SHM Tuntas 100%</dt><dd class="text-emerald-700 font-semibold">{{ $shmTuntas }} UPT</dd></div>
                <div><dt>SHM Sebagian / Proses</dt><dd class="text-amber-700 font-semibold">{{ $shmSebagian }} UPT</dd></div>
                <div><dt>Belum Sertifikat</dt><dd class="text-slate-500 font-semibold">{{ $shmBelum }} UPT</dd></div>
            </dl>
        </div>
    </section>

    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h2>Sebaran kondisi permasalahan lahan per kabupaten</h2>
            <span class="atlas-muted">Clean · Monitoring · Kritis</span>
        </div>
        <div class="atlas-panel-body">
            @if($regencyLand->isNotEmpty())
                <div style="height: 260px; position: relative;">
                    <canvas id="atlas-land-issues-chart" aria-label="Grafik kondisi lahan per kabupaten" role="img"></canvas>
                </div>
            @else
                <div class="atlas-empty">Belum ada data lahan kabupaten.</div>
            @endif
        </div>
    </section>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Matriks capaian sertipikasi tanah per kabupaten</h2>
        <a class="atlas-link" href="{{ route('admin.sertifikat-tanah.index') }}">Buka modul Pertanahan & SHM →</a>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Kabupaten</th>
                    <th scope="col">Total UPT</th>
                    <th scope="col">SHM Tuntas</th>
                    <th scope="col">SHM Sebagian</th>
                    <th scope="col">Belum SHM</th>
                    <th scope="col">Lahan Clean</th>
                    <th scope="col">Kasus Berisiko</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regencyLand as $rl)
                    <tr>
                        <td><strong>{{ $rl->name }}</strong></td>
                        <td class="tabular-nums font-semibold">{{ $rl->upt_count }}</td>
                        <td class="tabular-nums text-emerald-700 font-medium">{{ $rl->shm_tuntas }}</td>
                        <td class="tabular-nums text-amber-700 font-medium">{{ $rl->shm_sebagian }}</td>
                        <td class="tabular-nums text-slate-500">{{ $rl->shm_belum }}</td>
                        <td class="tabular-nums text-emerald-600 font-medium">{{ $rl->clean_count }}</td>
                        <td class="tabular-nums">
                            @if($rl->warning_count + $rl->critical_count > 0)
                                <span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>{{ $rl->critical_count }} Kritis · {{ $rl->warning_count }} Pantau</span>
                            @else
                                <span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>0 Masalah</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.sertifikat-tanah.index', ['regency_id' => $rl->id]) }}" class="atlas-action-icon atlas-action-icon--detail" title="Buka Pertanahan di {{ $rl->name }}" aria-label="Detail">
                                    <x-admin.icon name="eye" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="atlas-empty">Tidak ada data pertanahan kabupaten yang memenuhi filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Daftar lokasi kasus lahan prioritas mediasi agraria</h2>
        <span class="atlas-muted">{{ $landIssues->count() }} lokasi teridentifikasi</span>
    </div>
    @forelse($landIssues as $li)
        <div class="atlas-request">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <x-admin.upt-identity :upt="$li" />
                    <x-admin.status :status="$li->issue_status" />
                    <span class="atlas-badge" style="font-size: 11px;">SHM: {{ $li->shm_status ?: 'Belum tercatat' }}</span>
                </div>
                <p class="atlas-reading mt-2" style="font-size: 13px;">
                    {{ $li->issue_note ?: $li->notes_issue ?: 'Tumpang tindih klaim kawasan hutan atau permasalahan batas bidang tanah.' }}
                </p>
            </div>
            <div class="atlas-request-actions">
                <a class="atlas-button" href="{{ route('admin.upt.show', ['id' => $li->id, 'tab' => 'land']) }}">Tinjau Lahan</a>
            </div>
        </div>
    @empty
        <div class="atlas-empty">Tidak ada lokasi dengan catatan masalah lahan pada cakupan filter ini.</div>
    @endforelse
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: "'Source Sans 3', sans-serif" };

    // Grafik Donat SHM
    const ctxShm = document.getElementById('atlas-shm-doughnut-chart');
    if (ctxShm) {
        new Chart(ctxShm.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['SHM Tuntas 100%', 'SHM Sebagian / Proses', 'Belum Sertifikat'],
                datasets: [{
                    data: [{{ $shmTuntas }}, {{ $shmSebagian }}, {{ $shmBelum }}],
                    backgroundColor: ['#107C41', '#D97706', '#94A3B8'],
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

    // Grafik Batang Bertumpuk Isu Lahan
    const ctxIssues = document.getElementById('atlas-land-issues-chart');
    if (ctxIssues) {
        const labels = {!! json_encode($regencyLand->pluck('name')) !!};
        const cleanData = {!! json_encode($regencyLand->pluck('clean_count')) !!};
        const warningData = {!! json_encode($regencyLand->pluck('warning_count')) !!};
        const criticalData = {!! json_encode($regencyLand->pluck('critical_count')) !!};

        new Chart(ctxIssues.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Clean & Clear',
                        data: cleanData,
                        backgroundColor: '#107C41CC',
                        borderRadius: 3,
                    },
                    {
                        label: 'Monitoring',
                        data: warningData,
                        backgroundColor: '#D97706CC',
                        borderRadius: 3,
                    },
                    {
                        label: 'Kritis',
                        data: criticalData,
                        backgroundColor: '#DC2626CC',
                        borderRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { color: '#243746', font } },
                    y: { stacked: true, beginAtZero: true, grid: { color: '#D4DEE766' }, ticks: { color: '#5b7285', font } }
                },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, color: '#243746', font } },
                    tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + c.parsed.y + ' UPT' } }
                }
            }
        });
    }
});
</script>
@endpush

