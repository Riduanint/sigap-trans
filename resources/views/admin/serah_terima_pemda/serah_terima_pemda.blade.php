@extends('layouts.admin')
@section('atlas_subtitle', 'Rekap serah terima pemda, selisih terhadap penempatan, dan arsip BAST per UPT.')
@section('atlas_actions')
    <button type="button" class="atlas-button atlas-button--primary" data-atlas-dialog-open="atlas-create-handover-dialog" data-atlas-dialog-focus="#create-handover-upt-id">
        <x-admin.icon name="plus" /> Daftarkan Serah Terima
    </button>
@endsection
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
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.handovers.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : ($key === 'handover_status' ? ($value === 'handed_over' ? 'Sudah serah terima' : 'Belum serah terima') : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.handovers.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">Edit rekap memperbarui angka tanpa memuat ulang halaman</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Status kelayakan</th><th scope="col">Tahun BAST</th><th scope="col">KK penempatan</th><th scope="col">KK serah terima</th><th scope="col">Selisih</th><th scope="col">Jiwa</th><th scope="col">Arsip BAST</th><th scope="col" style="text-align: center; width: 100px;">AKSI</th></tr></thead><tbody>
        @forelse($uptLocations as $upt)
            @php
                $diff = $upt->handover_kk - $upt->placement_kk;
                $pct = $upt->placement_kk > 0 ? round(($diff / $upt->placement_kk) * 100, 1) : 0;
                $bastDoc = $upt->documents->first();
                $isHandedOver = ($upt->handover_kk > 0 || !empty($upt->handover_year) || $bastDoc);

                $endPlacementYear = null;
                if (!empty($upt->placement_year) && preg_match_all('/\b(19\d\d|20\d\d)\b/', $upt->placement_year, $yearMatches)) {
                    $endPlacementYear = max(array_map('intval', $yearMatches[0]));
                }
                $currentYear = (int) date('Y');
                $eligibleYear = $endPlacementYear ? ($endPlacementYear + 5) : null;
                $isReady = $eligibleYear && ($currentYear >= $eligibleYear);
            @endphp
            <tr id="row-upt-{{ $upt->id }}">
                <td><x-admin.upt-identity :upt="$upt" /></td>
                <td id="cell-status-{{ $upt->id }}">
                    @if($isHandedOver)
                        <span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Sudah serah terima</span>
                    @elseif($isReady)
                        <span class="atlas-status" style="color: #194482; background: #eaf1fb;"><span aria-hidden="true" style="background: #194482;"></span>Siap serah terima</span>
                        <small class="atlas-muted" style="display:block; font-size:11px; margin-top:2px;">Binaan selesai ({{ $eligibleYear }})</small>
                    @elseif($eligibleYear)
                        <span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Masa pembinaan</span>
                        <small class="atlas-muted" style="display:block; font-size:11px; margin-top:2px;">S/d tahun {{ $eligibleYear }}</small>
                    @else
                        <span class="atlas-status"><span aria-hidden="true"></span>Belum terjadwal</span>
                    @endif
                </td>
                <td id="cell-year-{{ $upt->id }}">{{ $upt->handover_year ?: 'Belum tercatat' }}</td>
                <td class="tabular-nums">{{ number_format($upt->placement_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-kk-{{ $upt->id }}">{{ number_format($upt->handover_kk, 0, ',', '.') }}</td>
                <td class="tabular-nums" id="cell-diff-{{ $upt->id }}">{{ $diff > 0 ? '+' . number_format($diff, 0, ',', '.') . ' (+' . $pct . '%)' : ($diff < 0 ? number_format($diff, 0, ',', '.') . ' (' . $pct . '%)' : '0') }}</td>
                <td class="tabular-nums" id="cell-pop-{{ $upt->id }}">{{ number_format($upt->handover_population, 0, ',', '.') }}</td>
                <td>@if($bastDoc)<a class="atlas-link" href="{{ route('admin.documents.download', $bastDoc->id) }}">Unduh BAST</a>@else<span class="atlas-muted">Belum ada</span>@endif</td>
                <td style="text-align: center;">
                    <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                        @if(!$isHandedOver)
                            <button type="button" class="atlas-action-icon atlas-action-icon--create" onclick="openCreateHandoverDialogForUpt({{ $upt->id }})" title="Catat Serah Terima Baru" aria-label="Catat Serah Terima">
                                <x-admin.icon name="plus" />
                            </button>
                        @else
                            <button type="button" class="atlas-action-icon atlas-action-icon--edit" data-atlas-dialog-open="atlas-handover-dialog" data-atlas-dialog-focus="#atlas-handover-kk" onclick="fillHandoverDialog({{ $upt->id }}, {{ Illuminate\Support\Js::from('UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' · ' . $upt->upt_name) }}, {{ $upt->handover_kk }}, {{ $upt->handover_population }}, {{ Illuminate\Support\Js::from($upt->handover_year) }}, {{ $upt->placement_kk }}, {{ Illuminate\Support\Js::from($upt->placement_year) }})" title="Edit Rekap Serah Terima" aria-label="Edit Rekap">
                                <x-admin.icon name="edit" />
                            </button>
                        @endif
                        <a href="{{ $registriRoute($upt, 'handover') }}" class="atlas-action-icon atlas-action-icon--info" title="Buka Registri Warga" aria-label="Registri">
                            <x-admin.icon name="folder" />
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="9"><div class="atlas-empty"><strong>Tidak ada data yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.handovers.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog Daftarkan Serah Terima Pemda Baru --}}
<div class="atlas-dialog" id="atlas-create-handover-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-create-handover-title">
    <div class="atlas-dialog__panel" style="max-width: 520px;">
        <div class="atlas-dialog__head">
            <div>
                <h3 id="atlas-create-handover-title">Daftarkan Serah Terima Pemda</h3>
                <p style="font-size: 12px; color: var(--atlas-muted); margin: 2px 0 0 0;">Catat berita acara serah terima (BAST) dan mutasi kependudukan pemda.</p>
            </div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form id="atlas-create-handover-form" onsubmit="submitCreateHandover(event)" class="atlas-dialog__body">
            <div class="atlas-field">
                <label for="create-handover-upt-id">Pilih Lokasi UPT <span aria-hidden="true">*</span></label>
                <select id="create-handover-upt-id" name="upt_id" required onchange="onSelectHandoverUpt(this)">
                    <option value="">-- Pilih Lokasi UPT --</option>
                    @if(!empty($pendingHandoverUpts) && count($pendingHandoverUpts) > 0)
                        <optgroup label="UPT Belum Serah Terima (Prioritas)">
                            @foreach($pendingHandoverUpts as $u)
                                <option value="{{ $u->id }}" data-placement-kk="{{ $u->placement_kk }}" data-placement-year="{{ $u->placement_year }}" data-handover-kk="{{ $u->handover_kk }}" data-handover-pop="{{ $u->handover_population }}" data-handover-year="{{ $u->handover_year }}">
                                    UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $u->upt_name }} ({{ $u->regency?->name ?? 'Kalsel' }}) - Belum BAST
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(!empty($allEligibleUpts) && count($allEligibleUpts) > 0)
                        <optgroup label="Seluruh Lokasi UPT Berpenempatan">
                            @foreach($allEligibleUpts as $u)
                                <option value="{{ $u->id }}" data-placement-kk="{{ $u->placement_kk }}" data-placement-year="{{ $u->placement_year }}" data-handover-kk="{{ $u->handover_kk }}" data-handover-pop="{{ $u->handover_population }}" data-handover-year="{{ $u->handover_year }}">
                                    UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $u->upt_name }} ({{ $u->regency?->name ?? 'Kalsel' }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div id="create-handover-info" style="display: none; margin-bottom: 12px; padding: 10px 12px; background: #f4f8fd; border-radius: 6px; font-size: 12px; color: var(--atlas-ink); border-left: 3px solid var(--atlas-blue);">
                <strong id="create-handover-info-summary">Penempatan Awal: -</strong>
                <div id="create-handover-info-badge" style="margin-top: 4px; font-size: 11px; color: var(--atlas-muted);">-</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="atlas-field">
                    <label for="create-handover-kk">KK Serah Terima <span aria-hidden="true">*</span></label>
                    <input type="number" id="create-handover-kk" name="handover_kk" required min="0" placeholder="0" oninput="calculateCreateHandoverDiff()">
                </div>
                <div class="atlas-field">
                    <label for="create-handover-pop">Jiwa Definitif <span aria-hidden="true">*</span></label>
                    <input type="number" id="create-handover-pop" name="handover_population" required min="0" placeholder="0">
                </div>
            </div>

            <div class="atlas-field">
                <label for="create-handover-year">Tahun BAST Serah Terima <span aria-hidden="true">*</span></label>
                <input type="text" id="create-handover-year" name="handover_year" maxlength="50" required placeholder="Contoh: 1987 atau 1990">
                <p class="atlas-form-hint">Tahun diterbitkannya dokumen Berita Acara Serah Terima (BAST).</p>
            </div>

            <p class="atlas-form-hint" id="create-handover-diff-text" style="margin-top: 2px;">Selisih terhadap penempatan: 0 KK</p>

            <div class="atlas-dialog__foot" style="padding: 12px 0 0 0; border: 0; background: none; margin-top: 6px;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary" id="atlas-create-handover-submit">Simpan Serah Terima</button>
            </div>
        </form>
    </div>
</div>

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
            <div id="atlas-handover-placement-info" style="margin-bottom: 12px; padding: 10px 12px; background: #f4f8fd; border-radius: 6px; font-size: 12px; color: var(--atlas-ink); border-left: 3px solid var(--atlas-blue);">
                <strong id="atlas-handover-placement-summary">Penempatan Awal: -</strong>
                <div id="atlas-handover-readiness-badge" style="margin-top: 4px; font-size: 11px; color: var(--atlas-muted);">-</div>
            </div>
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
function onSelectHandoverUpt(select) {
    const selected = select.options[select.selectedIndex];
    const infoBox = document.getElementById('create-handover-info');
    const summaryEl = document.getElementById('create-handover-info-summary');
    const badgeEl = document.getElementById('create-handover-info-badge');

    if (!selected || !selected.value) {
        infoBox.style.display = 'none';
        document.getElementById('create-handover-kk').value = '';
        document.getElementById('create-handover-pop').value = '';
        document.getElementById('create-handover-year').value = '';
        document.getElementById('create-handover-diff-text').textContent = 'Selisih terhadap penempatan: 0 KK';
        return;
    }

    const placementKk = parseFloat(selected.dataset.placementKk) || 0;
    const placementYear = selected.dataset.placementYear || '';
    const handoverKk = selected.dataset.handoverKk;
    const handoverPop = selected.dataset.handoverPop;
    const handoverYear = selected.dataset.handoverYear;

    summaryEl.textContent = `Penempatan Awal: ${Number(placementKk).toLocaleString('id-ID')} KK (Tahun: ${placementYear || 'Tidak tercatat'})`;

    if (placementYear) {
        const matches = String(placementYear).match(/\b(19\d\d|20\d\d)\b/g);
        if (matches && matches.length > 0) {
            const maxYear = Math.max(...matches.map(Number));
            const readyYear = maxYear + 5;
            const currentYear = new Date().getFullYear();
            if (currentYear >= readyYear) {
                badgeEl.textContent = `Status: Telah memenuhi syarat serah terima pemda (masa binaan 5 tahun selesai pada ${readyYear}).`;
                badgeEl.style.color = 'var(--atlas-clean)';
            } else {
                badgeEl.textContent = `Status: Masih dalam masa pembinaan hingga tahun ${readyYear}.`;
                badgeEl.style.color = 'var(--atlas-warning)';
            }
        } else {
            badgeEl.textContent = 'Masa pembinaan standar: 5 tahun.';
            badgeEl.style.color = 'var(--atlas-muted)';
        }
    } else {
        badgeEl.textContent = 'Masa pembinaan standar: 5 tahun.';
        badgeEl.style.color = 'var(--atlas-muted)';
    }

    infoBox.style.display = 'block';

    if (handoverKk && Number(handoverKk) > 0) {
        document.getElementById('create-handover-kk').value = handoverKk;
        document.getElementById('create-handover-pop').value = handoverPop || 0;
        document.getElementById('create-handover-year').value = handoverYear || '';
    } else {
        document.getElementById('create-handover-kk').value = '';
        document.getElementById('create-handover-pop').value = '';
        document.getElementById('create-handover-year').value = '';
    }

    calculateCreateHandoverDiff();
}

function calculateCreateHandoverDiff() {
    const select = document.getElementById('create-handover-upt-id');
    const selected = select.options[select.selectedIndex];
    const baseKk = selected ? (parseFloat(selected.dataset.placementKk) || 0) : 0;
    const currentKk = parseFloat(document.getElementById('create-handover-kk').value) || 0;
    const diff = currentKk - baseKk;
    const sign = diff > 0 ? '+' : '';
    const pct = baseKk > 0 ? ((diff / baseKk) * 100).toFixed(1) : '0';
    document.getElementById('create-handover-diff-text').textContent = `Selisih terhadap penempatan: ${sign}${diff.toLocaleString('id-ID')} KK (${sign}${pct}%)`;
}

function openCreateHandoverDialogForUpt(uptId) {
    const select = document.getElementById('create-handover-upt-id');
    if (select) {
        select.value = uptId;
        onSelectHandoverUpt(select);
    }
    if (typeof window.openAtlasDialog === 'function') {
        window.openAtlasDialog('atlas-create-handover-dialog', { focus: '#create-handover-year' });
    } else {
        const dlg = document.getElementById('atlas-create-handover-dialog');
        if (dlg) dlg.setAttribute('open', '');
    }
}

async function submitCreateHandover(event) {
    event.preventDefault();
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Konfirmasi Serah Terima Pemda',
            message: 'Daftarkan data serah terima pemda untuk lokasi UPT terpilih ke sistem?',
            type: 'primary',
            confirmText: 'Ya, Simpan Serah Terima'
        });
        if (!ok) return;
    }

    const submitBtn = document.getElementById('atlas-create-handover-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan…';

    const payload = {
        upt_id: document.getElementById('create-handover-upt-id').value,
        handover_kk: document.getElementById('create-handover-kk').value,
        handover_population: document.getElementById('create-handover-pop').value,
        handover_year: document.getElementById('create-handover-year').value,
    };

    fetch("{{ route('admin.handovers.store') }}", {
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
        submitBtn.textContent = 'Simpan Serah Terima';
    });
}
function fillHandoverDialog(id, title, kk, pop, year, placementKk, placementYear) {
    document.getElementById('atlas-handover-id').value = id;
    document.getElementById('atlas-handover-dialog-upt').textContent = title;
    document.getElementById('atlas-handover-kk').value = kk;
    document.getElementById('atlas-handover-pop').value = pop;
    document.getElementById('atlas-handover-year').value = year || '';
    document.getElementById('atlas-handover-base').value = placementKk;

    const summaryEl = document.getElementById('atlas-handover-placement-summary');
    const badgeEl = document.getElementById('atlas-handover-readiness-badge');
    summaryEl.textContent = `Penempatan Awal: ${Number(placementKk).toLocaleString('id-ID')} KK (Tahun: ${placementYear || 'Tidak tercatat'})`;

    if (placementYear) {
        const matches = String(placementYear).match(/\b(19\d\d|20\d\d)\b/g);
        if (matches && matches.length > 0) {
            const maxYear = Math.max(...matches.map(Number));
            const readyYear = maxYear + 5;
            const currentYear = new Date().getFullYear();
            if (currentYear >= readyYear) {
                badgeEl.textContent = `Status: Telah memenuhi syarat serah terima pemda (masa binaan 5 tahun selesai pada ${readyYear}).`;
                badgeEl.style.color = 'var(--atlas-clean)';
            } else {
                badgeEl.textContent = `Status: Masih dalam masa pembinaan hingga tahun ${readyYear}.`;
                badgeEl.style.color = 'var(--atlas-warning)';
            }
        } else {
            badgeEl.textContent = 'Masa pembinaan standar: 5 tahun.';
            badgeEl.style.color = 'var(--atlas-muted)';
        }
    } else {
        badgeEl.textContent = 'Masa pembinaan standar: 5 tahun.';
        badgeEl.style.color = 'var(--atlas-muted)';
    }

    updateHandoverDiff();
}
function updateHandoverDiff() {
    const kk = parseFloat(document.getElementById('atlas-handover-kk').value) || 0;
    const base = parseFloat(document.getElementById('atlas-handover-base').value) || 0;
    const diff = kk - base;
    const sign = diff > 0 ? '+' : '';
    document.getElementById('atlas-handover-diff').textContent = `Selisih terhadap penempatan: ${sign}${diff.toLocaleString('id-ID')} KK`;
}
async function submitHandoverEdit(event) {
    event.preventDefault();
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Konfirmasi Serah Terima Pemda',
            message: 'Simpan pembaruan data KK dan jiwa serah terima Pemda untuk UPT ini?',
            type: 'warning',
            confirmText: 'Ya, Simpan Rekap'
        });
        if (!ok) return;
    }
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
        if (!data.success) {
            window.showToast ? showToast('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'), 'error') : alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
            return;
        }
        const d = data.data;
        const fmt = n => Number(n).toLocaleString('id-ID');
        const cellKk = document.getElementById(`cell-kk-${id}`);
        const cellPop = document.getElementById(`cell-pop-${id}`);
        const cellYear = document.getElementById(`cell-year-${id}`);
        const cellDiff = document.getElementById(`cell-diff-${id}`);
        if (cellKk) cellKk.textContent = fmt(d.handover_kk);
        if (cellPop) cellPop.textContent = fmt(d.handover_population);
        if (cellYear) cellYear.textContent = d.handover_year || 'Belum tercatat';
        const cellStatus = document.getElementById(`cell-status-${id}`);
        if (cellStatus && (Number(d.handover_kk) > 0 || d.handover_year)) {
            cellStatus.innerHTML = '<span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Sudah serah terima</span>';
        }
        if (cellDiff) {
            const diff = Number(d.growth);
            const pct = d.growth_pct;
            cellDiff.textContent = diff > 0 ? `+${fmt(diff)} (+${pct}%)` : (diff < 0 ? `${fmt(diff)} (${pct}%)` : '0');
        }
        if (typeof window.closeAtlasDialog === 'function') {
            window.closeAtlasDialog('atlas-handover-dialog');
        } else {
            const dlg = document.getElementById('atlas-handover-dialog');
            if (dlg) dlg.removeAttribute('open');
        }
        const successMsg = data.message || 'Rekapitulasi serah terima pemda berhasil diperbarui.';
        window.showToast ? window.showToast(successMsg, 'success') : alert(successMsg);
    })
    .catch((err) => {
        console.error('Submit handover error:', err);
        window.showToast ? window.showToast('Gagal menyimpan data rekap. Periksa koneksi Anda.', 'error') : alert('Gagal menyimpan data rekap. Periksa koneksi Anda.');
    })
    .finally(() => { submit.disabled = false; submit.textContent = 'Simpan rekap'; });
}
</script>
@endpush
@endsection
