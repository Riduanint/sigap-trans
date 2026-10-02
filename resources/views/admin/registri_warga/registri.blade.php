@extends('layouts.admin')
@section('atlas_subtitle', 'Buku registri nominal per KK: identitas warga, asal daerah, kapling, dan status SHM.')
@section('content')
@php
    $stageLabels = ['placement' => 'Penempatan awal', 'handover' => 'Serah terima'];
    $otherStage = $stage === 'placement' ? 'handover' : 'placement';
    $rekapDiff = $stats['total_kk'] - $stats['rekap_kk'];
    // Operator memakai route modulnya sendiri; tab tahap & filter tetap bekerja sama.
    $isOperatorView = auth()->user()?->isOperator() ?? false;
    $registriRoute = $isOperatorView ? 'operator.upt.registri' : 'admin.upt.registri';
    $rekapRoute = $isOperatorView ? 'operator.upt.index' : ['admin.upt.show', ['id' => $upt->id, 'tab' => 'population']];
    $rekapLabel = $isOperatorView ? 'Kembali ke Data UPT wilayah →' : 'Rekap kependudukan UPT →';
@endphp
<section class="atlas-panel atlas-detail-intro">
    <x-admin.upt-identity :upt="$upt" :link="false" />
    <span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Tahap: {{ $stageLabels[$stage] }}</span>
</section>
<nav class="atlas-tabs" aria-label="Tahap registri">
    @foreach($stageLabels as $stageKey => $stageLabel)
        <a href="{{ route($registriRoute, ['id' => $upt->id, 'stage' => $stageKey]) }}" @if($stage === $stageKey) aria-current="page" @endif>Registri {{ $stageLabel }}</a>
    @endforeach
    <a href="{{ is_array($rekapRoute) ? route(...$rekapRoute) : route($rekapRoute) }}">{{ $rekapLabel }}</a>
</nav>

<div class="atlas-metrics" aria-label="Ringkasan registri">
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_kk'], 0, ',', '.') }}</strong><small>KK terdata · rekap UPT {{ number_format($stats['rekap_kk'], 0, ',', '.') }} KK ({{ $rekapDiff >= 0 ? '+' : '' }}{{ number_format($rekapDiff, 0, ',', '.') }})</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_jiwa'], 0, ',', '.') }}</strong><small>Jiwa · rata-rata {{ number_format($stats['avg_jiwa'], 2, ',', '.') }} jiwa/KK</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $stats['tpa_count'] }}<span>/ {{ $stats['tps_count'] }}</span></strong><small>TPA (penduduk asal) / TPS (setempat)</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['shm_count'], 0, ',', '.') }}</strong><small>KK sudah bersertipikat SHM</small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route($registriRoute, $upt->id) }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="rg-search">Cari warga</label><input id="rg-search" name="search" value="{{ request('search') }}" placeholder="Nama, NIK, nomor KK, atau blok kapling" type="search"></div>
            <div class="atlas-field"><label for="rg-type">Jenis transmigran</label><select id="rg-type" name="transmigrant_type"><option value="all">Semua jenis</option><option value="TPA" @selected(request('transmigrant_type') === 'TPA')>TPA — penduduk asal</option><option value="TPS" @selected(request('transmigrant_type') === 'TPS')>TPS — penduduk setempat</option></select></div>
            <div class="atlas-field"><label for="rg-shm">Status SHM</label><select id="rg-shm" name="land_certificate_status"><option value="all">Semua status</option>@foreach(['Sudah SHM', 'Proses SHM', 'Belum SHM'] as $shmOption)<option value="{{ $shmOption }}" @selected(request('land_certificate_status') === $shmOption)>{{ $shmOption }}</option>@endforeach</select></div>
            <input type="hidden" name="stage" value="{{ $stage }}">
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @php $activeFilters = collect(request()->only('search', 'transmigrant_type', 'land_certificate_status'))->filter(fn ($value) => filled($value) && $value !== 'all'); @endphp
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route($registriRoute, array_merge([$upt->id], ['stage' => $stage], request()->except([$key, 'page']))) }}" aria-label="Hapus filter {{ $key }}">{{ $value }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route($registriRoute, ['id' => $upt->id, 'stage' => $stage]) }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($cards->total(), 0, ',', '.') }} KK terdata</h2>
        <div class="atlas-inline">
            <a class="atlas-button" href="{{ route('admin.family-cards.export', ['uptId' => $upt->id, 'stage' => $stage]) }}" target="_blank" rel="noopener"><x-admin.icon name="download" /> Ekspor CSV</a>
            <a class="atlas-button" href="{{ route('admin.family-cards.template.download', ['stage' => $stage]) }}" target="_blank" rel="noopener">Template impor</a>
        </div>
    </div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Kepala keluarga</th><th scope="col">No. KK & NIK</th><th scope="col">Jiwa</th><th scope="col">Jenis</th><th scope="col">Blok kapling</th><th scope="col">Status SHM</th><th scope="col">Berkas KK</th></tr></thead><tbody>
        @forelse($cards as $card)
            <tr>
                <td><div class="atlas-identity"><strong>{{ $card->head_of_family_name }}</strong>@if($card->origin_province || $card->origin_regency)<span class="atlas-muted">Asal: {{ trim(($card->origin_province ?? '') . ($card->origin_regency ? ' — ' . $card->origin_regency : '')) }}</span>@endif</div></td>
                <td class="tabular-nums">{{ $card->family_card_number ?: 'Belum tercatat' }}<br><span class="atlas-muted">{{ $card->nik ?: 'NIK belum tercatat' }}</span></td>
                <td class="tabular-nums">{{ $card->family_members_count }}</td>
                <td><span class="atlas-status atlas-status--{{ $card->transmigrant_type === 'TPA' ? 'warning' : 'neutral' }}"><span aria-hidden="true"></span>{{ $card->transmigrant_type }}</span></td>
                <td>{{ $card->housing_block ?: 'Belum tercatat' }}</td>
                <td>{{ $card->land_certificate_status ?: 'Belum tercatat' }}</td>
                <td>@if($card->document_name)<a class="atlas-link" href="{{ route('admin.family-cards.document', $card->id) }}">Unduh ({{ strtoupper(pathinfo($card->document_name, PATHINFO_EXTENSION)) }})</a>@else<span class="atlas-muted">Belum ada</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="atlas-empty"><strong>Belum ada data registri pada tahap ini.</strong><p>UPT ini baru memiliki rekap angka agregat. Catat KK lewat registri lama atau impor berkas Excel.</p><a class="atlas-link" href="{{ is_array($rekapRoute) ? route(...$rekapRoute) : route($rekapRoute) }}">{{ $rekapLabel }}</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $cards->firstItem() ?? 0 }}–{{ $cards->lastItem() ?? 0 }} dari {{ $cards->total() }} KK terdata</span>{{ $cards->links('admin.partials.pagination') }}</div>
</section>
<p class="atlas-muted mt-4">Selisih registri terhadap rekap UPT memakai penjelasan selisih, bukan pertumbuhan alami — lihat catatan di halaman rekap kependudukan UPT.</p>
@endsection
