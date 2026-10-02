@extends('layouts.admin')
@section('atlas_subtitle', 'Rekap KK penempatan, jiwa, dan registri nominal warga per UPT.')
@section('content')
@php
    $activeFilters = collect(request()->only('search', 'regency_id', 'business_pattern', 'placement_year'))->filter(fn ($value) => filled($value) && $value !== 'all');
    $registriRoute = fn ($upt, $stage) => route('admin.upt.registri', ['id' => $upt->id, 'stage' => $stage]);
@endphp
<div class="atlas-metrics" aria-label="Ringkasan penempatan awal">
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_kk'], 0, ',', '.') }}</strong><small>KK penempatan</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_population'], 0, ',', '.') }}</strong><small>Jiwa · rata-rata {{ number_format($stats['avg_pop_per_kk'], 2, ',', '.') }} jiwa/KK</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $stats['upt_count'] }}<span>/ {{ $stats['total_upt'] }}</span></strong><small>UPT dengan KK tercatat</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_nominal_kk'], 0, ',', '.') }}</strong><small>KK terdata di registri warga</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['handover_kk'], 0, ',', '.') }}</strong><small><a class="atlas-link" href="{{ route('admin.handovers.index') }}">Bandingkan serah terima →</a></small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.placements.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="pl-search">Cari lokasi</label><input id="pl-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau UPT-001" type="search"></div>
            <div class="atlas-field"><label for="pl-regency">Kabupaten</label><select id="pl-regency" name="regency_id"><option value="all">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="pl-year">Tahun penempatan</label><input id="pl-year" name="placement_year" value="{{ request('placement_year') }}" placeholder="Contoh: 1982"></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
        <details class="atlas-filter-more" @if(request()->filled('business_pattern') && request('business_pattern') !== 'all') open @endif><summary>Filter lainnya</summary><div class="atlas-field"><label for="pl-pattern">Pola usaha</label><select id="pl-pattern" name="business_pattern"><option value="all">Semua pola</option>@foreach($patterns as $pattern)<option @selected(request('business_pattern') === $pattern)>{{ $pattern }}</option>@endforeach</select></div></details>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.placements.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : $value }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.placements.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">Edit rekap memperbarui angka tanpa memuat ulang halaman</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Tahun masuk</th><th scope="col">KK penempatan</th><th scope="col">Jiwa</th><th scope="col">Jiwa/KK</th><th scope="col">Registri warga</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($uptLocations as $upt)
            @php $ratio = $upt->placement_kk > 0 ? round($upt->placement_population / $upt->placement_kk, 2) : 0; @endphp
            <tr id="row-upt-{{ $upt->id }}">
                <td><x-admin.upt-identity :upt="$upt" /></td>
                <td id="cell-year-{{ $upt->id }}">{{ $upt->placement_year ?: 'Belum tercatat' }}</td>
                <td class="tabular-nums" id="cell-kk-{{ $upt->id }}">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-pop-{{ $upt->id }}">{{ number_format($upt->placement_population, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-ratio-{{ $upt->id }}">{{ number_format($ratio, 2, ',', '.') }}</td>
                <td><a class="atlas-link" href="{{ $registriRoute($upt, 'placement') }}">{{ ($upt->placement_family_cards_count ?? 0) > 0 ? $upt->placement_family_cards_count . ' KK terdata' : 'Buka registri' }}</a></td>
                <td>
                    <div class="atlas-inline">
                        <button type="button" class="atlas-button" data-atlas-dialog-open="atlas-placement-dialog" data-atlas-dialog-focus="#atlas-placement-kk" onclick="fillPlacementDialog({{ $upt->id }}, {{ Illuminate\Support\Js::from('UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' · ' . $upt->upt_name) }}, {{ $upt->placement_kk }}, {{ $upt->placement_population }}, {{ Illuminate\Support\Js::from($upt->placement_year) }})">Edit rekap</button>
                        <a class="atlas-link" href="{{ $registriRoute($upt, 'placement') }}">Registri →</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="atlas-empty"><strong>Tidak ada data yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.placements.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog edit rekap penempatan --}}
<div class="atlas-dialog" id="atlas-placement-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-placement-dialog-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div><h3 id="atlas-placement-dialog-title">Edit rekap penempatan</h3><p id="atlas-placement-dialog-upt">UPT</p></div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form id="atlas-placement-form" onsubmit="submitPlacementEdit(event)" class="atlas-dialog__body">
            <input type="hidden" id="atlas-placement-id">
            <div class="atlas-field"><label for="atlas-placement-kk">KK penempatan <span aria-hidden="true">*</span></label><input type="number" id="atlas-placement-kk" required min="0" oninput="updatePlacementRatio()"></div>
            <div class="atlas-field"><label for="atlas-placement-pop">Jiwa penempatan <span aria-hidden="true">*</span></label><input type="number" id="atlas-placement-pop" required min="0" oninput="updatePlacementRatio()"></div>
            <div class="atlas-field"><label for="atlas-placement-year">Tahun penempatan <span aria-hidden="true">*</span></label><input type="text" id="atlas-placement-year" required maxlength="50" placeholder="Contoh: 1982 atau 1982/1983"></div>
            <p class="atlas-form-hint" id="atlas-placement-ratio">Rata-rata: 0,00 jiwa/KK</p>
            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary" id="atlas-placement-submit">Simpan rekap</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function fillPlacementDialog(id, title, kk, pop, year) {
    document.getElementById('atlas-placement-id').value = id;
    document.getElementById('atlas-placement-dialog-upt').textContent = title;
    document.getElementById('atlas-placement-kk').value = kk;
    document.getElementById('atlas-placement-pop').value = pop;
    document.getElementById('atlas-placement-year').value = year || '';
    updatePlacementRatio();
}
function updatePlacementRatio() {
    const kk = parseFloat(document.getElementById('atlas-placement-kk').value) || 0;
    const pop = parseFloat(document.getElementById('atlas-placement-pop').value) || 0;
    const ratio = kk > 0 ? (pop / kk).toFixed(2).replace('.', ',') : '0,00';
    document.getElementById('atlas-placement-ratio').textContent = `Rata-rata: ${ratio} jiwa/KK`;
}
function submitPlacementEdit(event) {
    event.preventDefault();
    const id = document.getElementById('atlas-placement-id').value;
    const submit = document.getElementById('atlas-placement-submit');
    submit.disabled = true;
    submit.textContent = 'Menyimpan…';
    fetch(`/admin/placements/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            placement_kk: document.getElementById('atlas-placement-kk').value,
            placement_population: document.getElementById('atlas-placement-pop').value,
            placement_year: document.getElementById('atlas-placement-year').value,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) { alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan')); return; }
        const d = data.data;
        const fmt = n => Number(n).toLocaleString('id-ID');
        const cellKk = document.getElementById(`cell-kk-${id}`);
        const cellPop = document.getElementById(`cell-pop-${id}`);
        const cellYear = document.getElementById(`cell-year-${id}`);
        const cellRatio = document.getElementById(`cell-ratio-${id}`);
        if (cellKk) cellKk.textContent = fmt(d.placement_kk);
        if (cellPop) cellPop.textContent = fmt(d.placement_population);
        if (cellYear) cellYear.textContent = d.placement_year || 'Belum tercatat';
        if (cellRatio) cellRatio.textContent = Number(d.ratio).toLocaleString('id-ID', { minimumFractionDigits: 2 });
        closeAtlasDialog('atlas-placement-dialog');
        window.showToast ? showToast(d.message) : alert(d.message);
    })
    .catch(() => alert('Gagal menyimpan data rekap. Periksa koneksi Anda.'))
    .finally(() => { submit.disabled = false; submit.textContent = 'Simpan rekap'; });
}
</script>
@endpush
@endsection
