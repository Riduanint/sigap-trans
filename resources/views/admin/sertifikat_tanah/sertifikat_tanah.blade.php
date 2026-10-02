@extends('layouts.admin')
@section('atlas_subtitle', 'Status sertipikat SHM, kategori isu lahan, dan catatan mediasi per UPT.')
@section('content')
@php
    $activeFilters = collect(request()->only('search', 'regency_id', 'shm_status', 'issue_status'))->filter(fn ($value) => filled($value) && $value !== 'all' && $value !== '');
@endphp
<div class="atlas-metrics" aria-label="Ringkasan pertanahan">
    <div class="atlas-metric"><div><strong>{{ $shm100Count }}<span>/ {{ $totalUpt }}</span></strong><small>UPT 100% SHM</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $shmPartialCount }}</strong><small>Sebagian SHM / proses BPN</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $shmNoneCount }}</strong><small>Belum bersertipikat</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $criticalLandCount }}</strong><small>Lokasi kritis — prioritas mediasi</small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.sertifikat-tanah.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="lc-search">Cari lokasi</label><input id="lc-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau UPT-001" type="search"></div>
            <div class="atlas-field"><label for="lc-regency">Kabupaten</label><select id="lc-regency" name="regency_id"><option value="">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="lc-shm">Status sertipikat</label><select id="lc-shm" name="shm_status"><option value="">Semua status</option>@foreach(['100% SHM', 'Sebagian SHM', 'Proses BPN', 'Belum SHM', 'Sengketa'] as $shmOption)<option value="{{ $shmOption }}" @selected(request('shm_status') === $shmOption)>{{ $shmOption }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="lc-issue">Kondisi lahan</label><select id="lc-issue" name="issue_status"><option value="">Semua kondisi</option>@foreach(['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'] as $value => $label)<option value="{{ $value }}" @selected(request('issue_status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.sertifikat-tanah.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : ($key === 'issue_status' ? ['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'][$value] : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.sertifikat-tanah.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">Status SHM kosong tampil sebagai “Belum tercatat”, bukan 100%</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Sertipikat SHM</th><th scope="col">Kondisi lahan</th><th scope="col">Kapasitas (KK)</th><th scope="col">Catatan kendala</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($uptLocations as $u)
            <tr id="row-upt-{{ $u->id }}">
                <td><x-admin.upt-identity :upt="$u" /><span class="atlas-muted">Penempatan {{ $u->placement_year ?: 'belum tercatat' }}</span></td>
                <td>{{ $u->shm_status ?: 'Belum tercatat' }}</td>
                <td><x-admin.status :status="$u->issue_status ?? 'clean'" /></td>
                <td class="tabular-nums">{{ number_format($u->placement_kk ?? 0, 0, ',', '.') }} awal<br><span class="atlas-muted">{{ number_format($u->handover_kk ?? 0, 0, ',', '.') }} serah terima</span></td>
                <td>@if($u->notes_issue)<span title="{{ $u->notes_issue }}">{{ \Illuminate\Support\Str::limit($u->notes_issue, 90) }}</span>@else<span class="atlas-muted">Belum ada catatan</span>@endif</td>
                <td>
                    <div class="atlas-inline">
                        <button type="button" class="atlas-button" data-atlas-dialog-open="atlas-land-dialog" data-atlas-dialog-focus="#atlas-land-shm" onclick="fillLandDialog({{ $u->id }}, {{ Illuminate\Support\Js::from('UPT-' . str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) . ' · ' . $u->upt_name) }}, {{ Illuminate\Support\Js::from($u->shm_status ?: '') }}, {{ Illuminate\Support\Js::from($u->issue_status ?? 'clean') }}, {{ Illuminate\Support\Js::from($u->notes_issue ?? '') }})">Perbarui</button>
                        <a class="atlas-link" href="{{ route('admin.upt.show', ['id' => $u->id, 'tab' => 'land']) }}">Detail →</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="atlas-empty"><strong>Tidak ada lokasi yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.sertifikat-tanah.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog pembaruan status pertanahan --}}
<div class="atlas-dialog" id="atlas-land-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-land-dialog-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div><h3 id="atlas-land-dialog-title">Perbarui status pertanahan</h3><p id="atlas-land-dialog-upt">UPT</p></div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form id="atlas-land-form" onsubmit="submitLandEdit(event)" class="atlas-dialog__body">
            <input type="hidden" id="atlas-land-id">
            <div class="atlas-field">
                <label for="atlas-land-shm">Status sertipikat SHM <span aria-hidden="true">*</span></label>
                <select id="atlas-land-shm" required>@foreach(['100% SHM', 'Sebagian SHM', 'Proses BPN', 'Belum SHM', 'Sengketa'] as $shmOption)<option value="{{ $shmOption }}">{{ $shmOption }}</option>@endforeach</select>
            </div>
            <div class="atlas-field">
                <label for="atlas-land-issue">Kondisi lahan <span aria-hidden="true">*</span></label>
                <select id="atlas-land-issue" required>@foreach(['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
            </div>
            <div class="atlas-field">
                <label for="atlas-land-notes">Catatan kendala / rekomendasi mediasi</label>
                <textarea id="atlas-land-notes" rows="3" placeholder="Contoh: koordinasi pelepasan kawasan hutan bersama BPKH…"></textarea>
            </div>
            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary" id="atlas-land-submit">Simpan status</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function fillLandDialog(id, title, shm, issue, notes) {
    document.getElementById('atlas-land-id').value = id;
    document.getElementById('atlas-land-dialog-upt').textContent = title;
    document.getElementById('atlas-land-shm').value = shm || 'Belum SHM';
    document.getElementById('atlas-land-issue').value = issue || 'clean';
    document.getElementById('atlas-land-notes').value = notes || '';
}
function submitLandEdit(event) {
    event.preventDefault();
    const id = document.getElementById('atlas-land-id').value;
    const submit = document.getElementById('atlas-land-submit');
    submit.disabled = true;
    submit.textContent = 'Menyimpan…';
    fetch(`/admin/sertifikat-tanah/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            shm_status: document.getElementById('atlas-land-shm').value,
            issue_status: document.getElementById('atlas-land-issue').value,
            notes_issue: document.getElementById('atlas-land-notes').value.trim(),
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) { alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan')); return; }
        closeAtlasDialog('atlas-land-dialog');
        location.reload();
    })
    .catch(() => alert('Terjadi kesalahan saat menyimpan status pertanahan.'))
    .finally(() => { submit.disabled = false; submit.textContent = 'Simpan status'; });
}
</script>
@endpush
@endsection
