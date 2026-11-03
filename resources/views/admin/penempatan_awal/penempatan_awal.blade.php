@extends('layouts.admin')
@section('atlas_subtitle', 'Rekap KK penempatan, jiwa, dan registri nominal warga per UPT.')
@section('atlas_actions')
    <button type="button" class="atlas-button atlas-button--primary" data-atlas-dialog-open="atlas-create-placement-dialog" data-atlas-dialog-focus="#create-upt-id">
        <x-admin.icon name="plus" /> Daftarkan Penempatan Awal
    </button>
@endsection
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
            <div class="atlas-field"><label for="pl-pattern">Pola usaha</label><select id="pl-pattern" name="business_pattern"><option value="all">Semua pola</option>@foreach($patterns as $pattern)<option value="{{ $pattern }}" @selected(request('business_pattern') === $pattern)>{{ $pattern }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="pl-regency">Kabupaten</label><select id="pl-regency" name="regency_id"><option value="all">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="pl-year">Tahun penempatan</label><input id="pl-year" name="placement_year" value="{{ request('placement_year') }}" placeholder="Contoh: 1982"></div>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.placements.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : $value }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.placements.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">Edit rekap memperbarui angka tanpa memuat ulang halaman</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Tahun masuk</th><th scope="col">KK penempatan</th><th scope="col">Jiwa</th><th scope="col">Jiwa/KK</th><th scope="col">Registri warga</th><th scope="col" style="text-align: center; width: 100px;">AKSI</th></tr></thead><tbody>
        @forelse($uptLocations as $upt)
            @php $ratio = $upt->placement_kk > 0 ? round($upt->placement_population / $upt->placement_kk, 2) : 0; @endphp
            <tr id="row-upt-{{ $upt->id }}">
                <td><x-admin.upt-identity :upt="$upt" /></td>
                <td id="cell-year-{{ $upt->id }}">{{ $upt->placement_year ?: 'Belum tercatat' }}</td>
                <td class="tabular-nums" id="cell-kk-{{ $upt->id }}">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-pop-{{ $upt->id }}">{{ number_format($upt->placement_population, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-ratio-{{ $upt->id }}">{{ number_format($ratio, 2, ',', '.') }}</td>
                <td><a class="atlas-link" href="{{ $registriRoute($upt, 'placement') }}">{{ ($upt->placement_family_cards_count ?? 0) > 0 ? $upt->placement_family_cards_count . ' KK terdata' : 'Buka registri' }}</a></td>
                <td style="text-align: center;">
                    <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                        @if($upt->placement_kk <= 0 && empty($upt->placement_year))
                            <button type="button" class="atlas-action-icon atlas-action-icon--create" onclick="openCreatePlacementDialogForUpt({{ $upt->id }})" title="Daftarkan Penempatan Awal" aria-label="Daftarkan">
                                <x-admin.icon name="plus" />
                            </button>
                        @else
                            <button type="button" class="atlas-action-icon atlas-action-icon--edit" data-atlas-dialog-open="atlas-placement-dialog" data-atlas-dialog-focus="#atlas-placement-kk" onclick="fillPlacementDialog({{ $upt->id }}, {{ Illuminate\Support\Js::from('UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' · ' . $upt->upt_name) }}, {{ $upt->placement_kk }}, {{ $upt->placement_population }}, {{ Illuminate\Support\Js::from($upt->placement_year) }})" title="Edit Rekap Penempatan" aria-label="Edit Rekap">
                                <x-admin.icon name="edit" />
                            </button>
                        @endif
                        <a href="{{ $registriRoute($upt, 'placement') }}" class="atlas-action-icon atlas-action-icon--info" title="Buka Registri Warga" aria-label="Registri">
                            <x-admin.icon name="folder" />
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="atlas-empty"><strong>Tidak ada data yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.placements.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog input penempatan awal untuk UPT --}}
<div class="atlas-dialog" id="atlas-create-placement-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-create-placement-title">
    <div class="atlas-dialog__panel" style="max-width: 520px;">
        <div class="atlas-dialog__head">
            <div>
                <h3 id="atlas-create-placement-title">Daftarkan Penempatan Awal UPT</h3>
                <p style="font-size: 12px; color: var(--atlas-muted); margin: 2px 0 0 0;">Lengkapi data kependudukan awal untuk membuka akses serah terima pemda.</p>
            </div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form id="atlas-create-placement-form" onsubmit="submitCreatePlacement(event)" class="atlas-dialog__body">
            <div class="atlas-field">
                <label for="create-upt-id">Pilih Lokasi UPT <span aria-hidden="true">*</span></label>
                <select id="create-upt-id" name="upt_id" required onchange="onSelectPlacementUpt(this)">
                    <option value="">-- Pilih Lokasi UPT --</option>
                    @if(!empty($uptWithoutPlacement) && count($uptWithoutPlacement) > 0)
                        <optgroup label="UPT Baru (Menunggu Penempatan Awal)">
                            @foreach($uptWithoutPlacement as $upt)
                                <option value="{{ $upt->id }}" data-kk="{{ $upt->placement_kk }}" data-pop="{{ $upt->placement_population }}" data-year="{{ $upt->placement_year }}">
                                    UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->upt_name }} ({{ $upt->regency?->name ?? 'Kalsel' }}) - Belum ada data
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(!empty($allUpts) && count($allUpts) > 0)
                        <optgroup label="Daftar Seluruh Lokasi UPT">
                            @foreach($allUpts as $upt)
                                <option value="{{ $upt->id }}" data-kk="{{ $upt->placement_kk }}" data-pop="{{ $upt->placement_population }}" data-year="{{ $upt->placement_year }}">
                                    UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->upt_name }} ({{ $upt->regency?->name ?? 'Kalsel' }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="atlas-field">
                    <label for="create-start-year">Tahun Awal <span aria-hidden="true">*</span></label>
                    <input type="number" id="create-start-year" name="start_year" required min="1945" max="2100" placeholder="Contoh: 1985" oninput="calculateCreateYears()">
                </div>
                <div class="atlas-field">
                    <label for="create-end-year">Tahun Akhir (Opsional)</label>
                    <input type="number" id="create-end-year" name="end_year" min="1945" max="2100" placeholder="Contoh: 1990" oninput="calculateCreateYears()">
                </div>
            </div>

            <div id="create-year-readiness-hint" style="margin-bottom: 12px; padding: 10px 12px; background: #eaf1fb; border-radius: 6px; font-size: 12px; color: var(--atlas-blue-dark); display: none;">
                <strong>Status Kelayakan:</strong> <span id="create-readiness-text">-</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="atlas-field">
                    <label for="create-placement-kk">KK Penempatan <span aria-hidden="true">*</span></label>
                    <input type="number" id="create-placement-kk" name="placement_kk" required min="1" placeholder="0" oninput="calculateCreateRatio()">
                </div>
                <div class="atlas-field">
                    <label for="create-placement-pop">Jiwa Penempatan <span aria-hidden="true">*</span></label>
                    <input type="number" id="create-placement-pop" name="placement_population" required min="1" placeholder="0" oninput="calculateCreateRatio()">
                </div>
            </div>

            <p class="atlas-form-hint" id="create-placement-ratio" style="margin-top: 2px;">Rata-rata: 0,00 jiwa/KK</p>

            <div class="atlas-dialog__foot" style="padding: 12px 0 0 0; border: 0; background: none; margin-top: 6px;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary" id="atlas-create-submit">Simpan Penempatan Awal</button>
            </div>
        </form>
    </div>
</div>

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
            <div class="atlas-field">
                <label for="atlas-placement-year">Tahun / Kisaran Penempatan <span aria-hidden="true">*</span></label>
                <input type="text" id="atlas-placement-year" required maxlength="50" placeholder="Contoh: 1982 atau 1982–1987" oninput="updatePlacementYearHint()">
                <small class="atlas-form-hint" id="atlas-placement-year-hint" style="margin-top: 4px; display: block; color: var(--atlas-blue-dark); font-size: 11px;">Masa pembinaan standar: 5 tahun setelah akhir penempatan.</small>
            </div>
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
function onSelectPlacementUpt(select) {
    const selected = select.options[select.selectedIndex];
    if (!selected || !selected.value) {
        document.getElementById('create-placement-kk').value = '';
        document.getElementById('create-placement-pop').value = '';
        document.getElementById('create-start-year').value = '';
        document.getElementById('create-end-year').value = '';
        document.getElementById('create-year-readiness-hint').style.display = 'none';
        document.getElementById('create-placement-ratio').textContent = 'Rata-rata: 0,00 jiwa/KK';
        return;
    }
    const kk = selected.dataset.kk;
    const pop = selected.dataset.pop;
    const year = selected.dataset.year;

    if (kk && Number(kk) > 0) {
        document.getElementById('create-placement-kk').value = kk;
        document.getElementById('create-placement-pop').value = pop || 0;
        if (year) {
            const matches = String(year).match(/\b(19\d\d|20\d\d)\b/g);
            if (matches && matches.length > 0) {
                document.getElementById('create-start-year').value = matches[0];
                if (matches.length > 1) {
                    document.getElementById('create-end-year').value = matches[matches.length - 1];
                } else {
                    document.getElementById('create-end-year').value = '';
                }
            }
        }
        calculateCreateYears();
        calculateCreateRatio();
    }
}

function openCreatePlacementDialogForUpt(uptId) {
    const select = document.getElementById('create-upt-id');
    if (select) {
        select.value = uptId;
        onSelectPlacementUpt(select);
    }
    if (typeof window.openAtlasDialog === 'function') {
        window.openAtlasDialog('atlas-create-placement-dialog', { focus: '#create-start-year' });
    } else {
        const dlg = document.getElementById('atlas-create-placement-dialog');
        if (dlg) dlg.setAttribute('open', '');
    }
}

function calculateCreateYears() {
    const startVal = parseInt(document.getElementById('create-start-year').value);
    const endVal = parseInt(document.getElementById('create-end-year').value);
    const hintBox = document.getElementById('create-year-readiness-hint');
    const textSpan = document.getElementById('create-readiness-text');

    if (!startVal || isNaN(startVal)) {
        hintBox.style.display = 'none';
        return;
    }

    const effectiveEnd = (endVal && !isNaN(endVal)) ? Math.max(startVal, endVal) : startVal;
    const readyYear = effectiveEnd + 5;
    const currentYear = new Date().getFullYear();

    let statusText = `Masa binaan 5 tahun (${effectiveEnd} s/d ${readyYear}). `;
    if (currentYear >= readyYear) {
        statusText += `UPT akan langsung berstatus SIAP SERAH TERIMA PEMDA karena telah melampaui tahun ${readyYear}.`;
    } else {
        statusText += `UPT berstatus DALAM PEMBINAAN hingga tahun ${readyYear}.`;
    }

    textSpan.textContent = statusText;
    hintBox.style.display = 'block';
}

function calculateCreateRatio() {
    const kk = parseFloat(document.getElementById('create-placement-kk').value) || 0;
    const pop = parseFloat(document.getElementById('create-placement-pop').value) || 0;
    const ratio = kk > 0 ? (pop / kk).toFixed(2).replace('.', ',') : '0,00';
    document.getElementById('create-placement-ratio').textContent = `Rata-rata: ${ratio} jiwa/KK`;
}

async function submitCreatePlacement(event) {
    event.preventDefault();
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Konfirmasi Penempatan Awal',
            message: 'Simpan data penempatan awal untuk UPT terpilih ke sistem?',
            type: 'primary',
            confirmText: 'Ya, Simpan Data'
        });
        if (!ok) return;
    }
    const submitBtn = document.getElementById('atlas-create-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan…';

    const payload = {
        upt_id: document.getElementById('create-upt-id').value,
        start_year: document.getElementById('create-start-year').value,
        end_year: document.getElementById('create-end-year').value,
        placement_kk: document.getElementById('create-placement-kk').value,
        placement_population: document.getElementById('create-placement-pop').value,
    };

    fetch("{{ route('admin.placements.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            window.showToast ? showToast('Gagal menyimpan: ' + (data.message || 'Periksa kembali isian formulir.'), 'error') : alert('Gagal menyimpan: ' + (data.message || 'Periksa kembali isian formulir.'));
            return;
        }
        window.location.reload();
    })
    .catch(() => {
        window.showToast ? showToast('Gagal menghubungi server. Periksa koneksi Anda.', 'error') : alert('Gagal menghubungi server. Periksa koneksi Anda.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Penempatan Awal';
    });
}

function updatePlacementYearHint() {
    const yearVal = document.getElementById('atlas-placement-year').value;
    const hint = document.getElementById('atlas-placement-year-hint');
    if (!yearVal) {
        hint.textContent = 'Masa pembinaan standar: 5 tahun setelah akhir penempatan.';
        return;
    }
    const matches = yearVal.match(/\b(19\d\d|20\d\d)\b/g);
    if (matches && matches.length > 0) {
        const years = matches.map(Number);
        const maxYear = Math.max(...years);
        const readyYear = maxYear + 5;
        const currentYear = new Date().getFullYear();
        if (currentYear >= readyYear) {
            hint.textContent = `Akhir penempatan: ${maxYear} · Masa binaan 5 tahun selesai (${readyYear}) → Siap Serah Terima Pemda.`;
        } else {
            hint.textContent = `Akhir penempatan: ${maxYear} · Masa pembinaan 5 tahun berakhir pada tahun ${readyYear}.`;
        }
    } else {
        hint.textContent = 'Masa pembinaan standar: 5 tahun setelah akhir penempatan.';
    }
}

function fillPlacementDialog(id, title, kk, pop, year) {
    document.getElementById('atlas-placement-id').value = id;
    document.getElementById('atlas-placement-dialog-upt').textContent = title;
    document.getElementById('atlas-placement-kk').value = kk;
    document.getElementById('atlas-placement-pop').value = pop;
    document.getElementById('atlas-placement-year').value = year || '';
    updatePlacementRatio();
    updatePlacementYearHint();
}
function updatePlacementRatio() {
    const kk = parseFloat(document.getElementById('atlas-placement-kk').value) || 0;
    const pop = parseFloat(document.getElementById('atlas-placement-pop').value) || 0;
    const ratio = kk > 0 ? (pop / kk).toFixed(2).replace('.', ',') : '0,00';
    document.getElementById('atlas-placement-ratio').textContent = `Rata-rata: ${ratio} jiwa/KK`;
}
async function submitPlacementEdit(event) {
    event.preventDefault();
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Konfirmasi Perubahan Rekap',
            message: 'Simpan pembaruan angka rekap penempatan awal UPT ini?',
            type: 'warning',
            confirmText: 'Ya, Perbarui'
        });
        if (!ok) return;
    }
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
        if (!data.success) {
            window.showToast ? showToast('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'), 'error') : alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
            return;
        }
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
        if (typeof window.closeAtlasDialog === 'function') {
            window.closeAtlasDialog('atlas-placement-dialog');
        } else {
            const dlg = document.getElementById('atlas-placement-dialog');
            if (dlg) dlg.removeAttribute('open');
        }
        const successMsg = data.message || 'Rekapitulasi penempatan awal berhasil diperbarui.';
        window.showToast ? window.showToast(successMsg, 'success') : alert(successMsg);
    })
    .catch((err) => {
        console.error('Submit error:', err);
        window.showToast ? window.showToast('Gagal menyimpan data rekap. Periksa koneksi Anda.', 'error') : alert('Gagal menyimpan data rekap. Periksa koneksi Anda.');
    })
    .finally(() => { submit.disabled = false; submit.textContent = 'Simpan rekap'; });
}
</script>
@endpush
@endsection
