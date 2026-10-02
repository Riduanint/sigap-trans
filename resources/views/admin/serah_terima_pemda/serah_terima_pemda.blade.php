@extends('layouts.admin')
@section('atlas_subtitle', 'Rekap serah terima pemda, selisih terhadap penempatan, dan arsip BAST per UPT.')
@section('content')
@php
    $activeFilters = collect(request()->only('search', 'regency_id', 'handover_status', 'handover_year'))->filter(fn ($value) => filled($value) && $value !== 'all');
    $registriRoute = fn ($upt, $stage) => route('admin.upt.registri', ['id' => $upt->id, 'stage' => $stage]);
@endphp
<div class="atlas-metrics" aria-label="Ringkasan serah terima">
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_handover_kk'], 0, ',', '.') }}</strong><small>KK serah terima · {{ number_format($stats['total_handover_pop'], 0, ',', '.') }} jiwa</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['handed_over_upts']) }}<span>/ {{ $stats['total_upt'] }}</span></strong><small>UPT sudah diserahkan ke pemda</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_placement_kk'], 0, ',', '.') }}</strong><small>KK penempatan sebagai pembanding</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_nominal_kk'], 0, ',', '.') }}</strong><small>KK terdata di registri warga</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $stats['growth_kk'] >= 0 ? '+' : '' }}{{ number_format($stats['growth_kk'], 0, ',', '.') }}</strong><small>Selisih penempatan–serah terima ({{ $stats['growth_pct'] }}%) — bukan angka pertumbuhan alami</small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.handovers.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="hv-search">Cari lokasi</label><input id="hv-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau UPT-001" type="search"></div>
            <div class="atlas-field"><label for="hv-regency">Kabupaten</label><select id="hv-regency" name="regency_id"><option value="all">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="hv-status">Status penyerahan</label><select id="hv-status" name="handover_status"><option value="all">Semua status</option><option value="handed_over" @selected(request('handover_status') === 'handed_over')>Sudah serah terima</option><option value="pending" @selected(request('handover_status') === 'pending')>Belum serah terima</option></select></div>
            <div class="atlas-field"><label for="hv-year">Tahun BAST</label><input id="hv-year" name="handover_year" value="{{ request('handover_year') }}" placeholder="Contoh: 1987"></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.handovers.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : ($key === 'handover_status' ? ($value === 'handed_over' ? 'Sudah serah terima' : 'Belum serah terima') : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.handovers.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">Edit rekap memperbarui angka tanpa memuat ulang halaman</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Tahun BAST</th><th scope="col">KK penempatan</th><th scope="col">KK serah terima</th><th scope="col">Selisih</th><th scope="col">Jiwa</th><th scope="col">Arsip BAST</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($uptLocations as $upt)
            @php
                $diff = $upt->handover_kk - $upt->placement_kk;
                $pct = $upt->placement_kk > 0 ? round(($diff / $upt->placement_kk) * 100, 1) : 0;
                $bastDoc = $upt->documents->first();
            @endphp
            <tr id="row-upt-{{ $upt->id }}">
                <td><x-admin.upt-identity :upt="$upt" /></td>
                <td id="cell-year-{{ $upt->id }}">{{ $upt->handover_year ?: 'Belum tercatat' }}</td>
                <td class="tabular-nums">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-kk-{{ $upt->id }}">{{ number_format($upt->handover_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-diff-{{ $upt->id }}">{{ $diff > 0 ? '+' . number_format($diff, 0, ',', '.') . ' (+' . $pct . '%)' : ($diff < 0 ? number_format($diff, 0, ',', '.') . ' (' . $pct . '%)' : '0') }}</td>
                <td class="tabular-nums" id="cell-pop-{{ $upt->id }}">{{ number_format($upt->handover_population, 0, ',', '.') }}</td>
                <td>@if($bastDoc)<a class="atlas-link" href="{{ route('admin.documents.download', $bastDoc->id) }}">Unduh BAST</a>@else<span class="atlas-muted">Belum ada</span>@endif</td>
                <td>
                    <div class="atlas-inline">
                        <button type="button" class="atlas-button" data-atlas-dialog-open="atlas-handover-dialog" data-atlas-dialog-focus="#atlas-handover-kk" onclick="fillHandoverDialog({{ $upt->id }}, {{ Illuminate\Support\Js::from('UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' · ' . $upt->upt_name) }}, {{ $upt->handover_kk }}, {{ $upt->handover_population }}, {{ Illuminate\Support\Js::from($upt->handover_year) }}, {{ $upt->placement_kk }})">Edit rekap</button>
                        <a class="atlas-link" href="{{ $registriRoute($upt, 'handover') }}">Registri →</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8"><div class="atlas-empty"><strong>Tidak ada data yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.handovers.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog edit rekap serah terima --}}
<div class="atlas-dialog" id="atlas-handover-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-handover-dialog-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div><h3 id="atlas-handover-dialog-title">Edit rekap serah terima</h3><p id="atlas-handover-dialog-upt">UPT</p></div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form id="atlas-handover-form" onsubmit="submitHandoverEdit(event)" class="atlas-dialog__body">
            <input type="hidden" id="atlas-handover-id">
            <input type="hidden" id="atlas-handover-base">
            <div class="atlas-field"><label for="atlas-handover-kk">KK serah terima <span aria-hidden="true">*</span></label><input type="number" id="atlas-handover-kk" required min="0" oninput="updateHandoverDiff()"></div>
            <div class="atlas-field"><label for="atlas-handover-pop">Jiwa definitif <span aria-hidden="true">*</span></label><input type="number" id="atlas-handover-pop" required min="0"></div>
            <div class="atlas-field"><label for="atlas-handover-year">Tahun BAST</label><input type="text" id="atlas-handover-year" maxlength="50" placeholder="Contoh: 1987"></div>
            <p class="atlas-form-hint" id="atlas-handover-diff">Selisih terhadap penempatan: 0 KK</p>
            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary" id="atlas-handover-submit">Simpan rekap</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function fillHandoverDialog(id, title, kk, pop, year, placementKk) {
    document.getElementById('atlas-handover-id').value = id;
    document.getElementById('atlas-handover-dialog-upt').textContent = title;
    document.getElementById('atlas-handover-kk').value = kk;
    document.getElementById('atlas-handover-pop').value = pop;
    document.getElementById('atlas-handover-year').value = year || '';
    document.getElementById('atlas-handover-base').value = placementKk;
    updateHandoverDiff();
}
function updateHandoverDiff() {
    const kk = parseFloat(document.getElementById('atlas-handover-kk').value) || 0;
    const base = parseFloat(document.getElementById('atlas-handover-base').value) || 0;
    const diff = kk - base;
    const sign = diff > 0 ? '+' : '';
    document.getElementById('atlas-handover-diff').textContent = `Selisih terhadap penempatan: ${sign}${diff.toLocaleString('id-ID')} KK`;
}
function submitHandoverEdit(event) {
    event.preventDefault();
    const id = document.getElementById('atlas-handover-id').value;
    const submit = document.getElementById('atlas-handover-submit');
    submit.disabled = true;
    submit.textContent = 'Menyimpan…';
    fetch(`/admin/handovers/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            handover_kk: document.getElementById('atlas-handover-kk').value,
            handover_population: document.getElementById('atlas-handover-pop').value,
            handover_year: document.getElementById('atlas-handover-year').value,
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
        const cellDiff = document.getElementById(`cell-diff-${id}`);
        if (cellKk) cellKk.textContent = fmt(d.handover_kk);
        if (cellPop) cellPop.textContent = fmt(d.handover_population);
        if (cellYear) cellYear.textContent = d.handover_year || 'Belum tercatat';
        if (cellDiff) {
            const diff = Number(d.growth);
            const pct = d.growth_pct;
            cellDiff.textContent = diff > 0 ? `+${fmt(diff)} (+${pct}%)` : (diff < 0 ? `${fmt(diff)} (${pct}%)` : '0');
        }
        closeAtlasDialog('atlas-handover-dialog');
        window.showToast ? showToast(d.message) : alert(d.message);
    })
    .catch(() => alert('Gagal menyimpan data rekap. Periksa koneksi Anda.'))
    .finally(() => { submit.disabled = false; submit.textContent = 'Simpan rekap'; });
}
</script>
@endpush
@endsection
