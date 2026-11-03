@extends('layouts.admin')
@section('atlas_subtitle', 'Identitas lokasi, riwayat kependudukan, dan dokumen pendukung dalam satu tempat.')
@section('atlas_actions')<a class="atlas-button atlas-button--primary" href="{{ route('admin.upt.edit', $upt->id) }}">Perbarui data UPT</a>@endsection
@section('content')
@php $tab = in_array(request('tab'), ['population', 'land', 'documents']) ? request('tab') : 'overview'; @endphp
<div class="atlas-stack">
    <section class="atlas-panel atlas-detail-intro"><x-admin.upt-identity :upt="$upt" :link="false" /><x-admin.status :status="$upt->issue_status" /></section>
    <nav class="atlas-tabs" aria-label="Bagian profil UPT">
        @foreach(['overview' => 'Ringkasan', 'population' => 'Kependudukan', 'land' => 'Pertanahan', 'documents' => 'Dokumen'] as $key => $label)<a href="{{ route('admin.upt.show', ['id' => $upt->id, 'tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach
    </nav>
    @if($tab === 'overview')
        <div class="atlas-detail-grid">
            <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Identitas wilayah</h2></div><dl class="atlas-panel-body atlas-definition"><div><dt>Kabupaten</dt><dd>{{ $upt->regency?->name }}</dd></div><div><dt>Pola usaha</dt><dd>{{ $upt->business_pattern }}</dd></div><div><dt>Tahun penempatan</dt><dd>{{ $upt->placement_year ?: 'Belum tercatat' }}</dd></div><div><dt>Tahun serah terima</dt><dd>{{ $upt->handover_year ?: 'Belum tercatat' }}</dd></div><div><dt>Latitude</dt><dd>{{ $upt->latitude ?? 'Belum tercatat' }}</dd></div><div><dt>Longitude</dt><dd>{{ $upt->longitude ?? 'Belum tercatat' }}</dd></div><div><dt>Publikasi kabupaten</dt><dd>{{ $upt->regency?->is_visible ? 'Tampil di peta publik' : 'Belum dipublikasikan' }}</dd></div><div><dt>Terakhir diperbarui</dt><dd>{{ $upt->updated_at?->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA</dd></div></dl></section>
            <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Konteks lokasi</h2></div><div id="atlas-work-map" class="atlas-map" aria-label="Peta lokasi UPT"></div><div class="atlas-map-status" id="atlas-map-status">Koordinat tercatat pada data UPT.</div><div class="atlas-map-caption"><strong id="atlas-map-name">{{ $upt->upt_name }}</strong><p class="atlas-muted" id="atlas-map-location">{{ $upt->current_village_name }}</p><a id="atlas-map-detail" hidden></a>@if($upt->latitude !== null && $upt->longitude !== null)<a href="{{ route('peta', ['upt_id' => $upt->id]) }}" target="_blank" rel="noopener">Buka peta publik ↗</a>@endif</div></section>
            @php $mapData = [['id' => $upt->id, 'upt_number' => $upt->upt_number, 'upt_name' => $upt->upt_name, 'current_village_name' => $upt->current_village_name, 'regency_name' => $upt->regency?->name, 'latitude' => $upt->latitude, 'longitude' => $upt->longitude, 'issue_status' => $upt->issue_status, 'detail_url' => route('admin.upt.show', $upt->id)]]; @endphp
            <script type="application/json" id="atlas-map-data">{!! json_encode($mapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        </div>
    @elseif($tab === 'population')
        <div class="atlas-detail-grid">
            @foreach(['placement' => 'Penempatan awal', 'handover' => 'Serah terima'] as $stage => $label)
                <section class="atlas-panel">
                    <div class="atlas-panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>{{ $label }}</h2>
                        <span class="atlas-muted" id="profile-year-{{ $stage }}">{{ $upt->{$stage . '_year'} ?: 'Tahun belum tercatat' }}</span>
                    </div>
                    <div class="atlas-panel-body">
                        <dl class="atlas-definition">
                            <div><dt>Kepala keluarga</dt><dd id="profile-stat-{{ $stage }}-kk">{{ number_format($upt->{$stage . '_kk'}, 0, ',', '.') }} KK</dd></div>
                            <div><dt>Penduduk</dt><dd id="profile-stat-{{ $stage }}-pop">{{ number_format($upt->{$stage . '_population'}, 0, ',', '.') }} jiwa</dd></div>
                        </dl>
                        <div class="atlas-inline mt-5">
                            <a class="atlas-button atlas-button--primary" href="{{ route('admin.upt.registri', ['id' => $upt->id, 'stage' => $stage]) }}">
                                Lihat registri warga <x-admin.icon name="arrow" />
                            </a>
                            <button type="button" class="atlas-button" onclick="openRegistryModal({{ $upt->id }}, {{ Illuminate\Support\Js::from($upt->upt_name) }}, '{{ $stage }}')">
                                Pengisian & impor
                            </button>
                            <button type="button" class="atlas-button" onclick="openEditRekapModal('{{ $stage }}')">
                                <x-admin.icon name="edit" /> Edit rekap
                            </button>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
        <p class="atlas-muted" id="profile-diff-kk">Selisih serah terima–penempatan: {{ number_format($upt->handover_kk - $upt->placement_kk, 0, ',', '.') }} KK. Selisih rekap tidak menunjukkan penyebab perubahan penduduk.</p>
        @include('admin.registri_warga.registri_warga')

        {{-- Modal Dialog Edit Rekap Kependudukan (Penempatan Awal & Serah Terima) --}}
        <div class="atlas-dialog" id="atlas-profile-rekap-dialog" role="dialog" aria-modal="true" aria-labelledby="profile-rekap-dialog-title" style="display: none;">
            <div class="atlas-dialog__panel" style="max-width: 480px;">
                <div class="atlas-dialog__head">
                    <div>
                        <h3 id="profile-rekap-dialog-title">Edit Rekap Kependudukan</h3>
                        <p id="profile-rekap-dialog-sub" style="font-size: 12px; color: var(--atlas-muted); margin: 2px 0 0 0;">UPT {{ $upt->upt_name }}</p>
                    </div>
                    <button type="button" class="atlas-icon-button" onclick="closeEditRekapModal()" aria-label="Tutup dialog">
                        <x-admin.icon name="close" />
                    </button>
                </div>
                <form id="profile-rekap-form" onsubmit="submitProfileRekap(event)" class="atlas-dialog__body">
                    <input type="hidden" id="profile-rekap-stage" value="placement">
                    <div class="atlas-field">
                        <label for="profile-rekap-kk" id="profile-rekap-kk-label">Kepala Keluarga (KK) <span aria-hidden="true">*</span></label>
                        <input type="number" id="profile-rekap-kk" required min="0" placeholder="0">
                    </div>
                    <div class="atlas-field">
                        <label for="profile-rekap-pop" id="profile-rekap-pop-label">Jumlah Jiwa Penduduk <span aria-hidden="true">*</span></label>
                        <input type="number" id="profile-rekap-pop" required min="0" placeholder="0">
                    </div>
                    <div class="atlas-field">
                        <label for="profile-rekap-year" id="profile-rekap-year-label">Tahun</label>
                        <input type="text" id="profile-rekap-year" maxlength="50" placeholder="Contoh: 1985 atau 1985/1987">
                    </div>
                    <div class="atlas-dialog__foot" style="padding: 12px 0 0 0; border: 0; background: none; margin-top: 6px; display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="atlas-button" onclick="closeEditRekapModal()">Batal</button>
                        <button type="submit" class="atlas-button atlas-button--primary" id="profile-rekap-submit-btn">Simpan Rekap</button>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
        let currentProfileStage = 'placement';
        let currentPlacementKk = {{ (int) $upt->placement_kk }};
        let currentPlacementPop = {{ (int) $upt->placement_population }};
        let currentPlacementYear = {!! json_encode($upt->placement_year ?: '') !!};
        let currentHandoverKk = {{ (int) $upt->handover_kk }};
        let currentHandoverPop = {{ (int) $upt->handover_population }};
        let currentHandoverYear = {!! json_encode($upt->handover_year ?: '') !!};

        window.onProfileStatsUpdated = function(stage, totalKk, totalJiwa) {
            if (stage === 'placement') {
                currentPlacementKk = totalKk;
                currentPlacementPop = totalJiwa;
            } else {
                currentHandoverKk = totalKk;
                currentHandoverPop = totalJiwa;
            }
        };

        function openEditRekapModal(stage) {
            currentProfileStage = stage;
            const dialog = document.getElementById('atlas-profile-rekap-dialog');
            const title = document.getElementById('profile-rekap-dialog-title');
            const kkInput = document.getElementById('profile-rekap-kk');
            const popInput = document.getElementById('profile-rekap-pop');
            const yearInput = document.getElementById('profile-rekap-year');
            const yearLabel = document.getElementById('profile-rekap-year-label');

            if (stage === 'placement') {
                title.textContent = 'Edit Rekap Penempatan Awal';
                yearLabel.textContent = 'Tahun Penempatan Awal';
                yearInput.placeholder = 'Contoh: 1985 atau 1985/1987';
                kkInput.value = currentPlacementKk;
                popInput.value = currentPlacementPop;
                yearInput.value = currentPlacementYear;
            } else {
                title.textContent = 'Edit Rekap Serah Terima Pemda';
                yearLabel.textContent = 'Tahun BAST Serah Terima';
                yearInput.placeholder = 'Contoh: 1990';
                kkInput.value = currentHandoverKk;
                popInput.value = currentHandoverPop;
                yearInput.value = currentHandoverYear;
            }

            dialog.style.display = 'flex';
        }

        function closeEditRekapModal() {
            const dialog = document.getElementById('atlas-profile-rekap-dialog');
            if (dialog) dialog.style.display = 'none';
        }

        async function submitProfileRekap(e) {
            e.preventDefault();
            const btn = document.getElementById('profile-rekap-submit-btn');
            btn.disabled = true;

            const kk = parseInt(document.getElementById('profile-rekap-kk').value) || 0;
            const pop = parseInt(document.getElementById('profile-rekap-pop').value) || 0;
            const year = document.getElementById('profile-rekap-year').value.trim();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const isPlacement = currentProfileStage === 'placement';
            const url = isPlacement ? '{{ route("admin.placements.update", $upt->id) }}' : '{{ route("admin.handovers.update", $upt->id) }}';

            const bodyPayload = isPlacement ? {
                placement_kk: kk,
                placement_population: pop,
                placement_year: year
            } : {
                handover_kk: kk,
                handover_population: pop,
                handover_year: year
            };

            try {
                const res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(bodyPayload)
                });
                const data = await res.json();
                if (data.success) {
                    if (isPlacement) {
                        currentPlacementKk = kk;
                        currentPlacementPop = pop;
                        currentPlacementYear = year;
                    } else {
                        currentHandoverKk = kk;
                        currentHandoverPop = pop;
                        currentHandoverYear = year;
                    }

                    // Update DOM
                    const kkEl = document.getElementById(`profile-stat-${currentProfileStage}-kk`);
                    const popEl = document.getElementById(`profile-stat-${currentProfileStage}-pop`);
                    const yearEl = document.getElementById(`profile-year-${currentProfileStage}`);
                    if (kkEl) kkEl.textContent = `${Number(kk).toLocaleString('id-ID')} KK`;
                    if (popEl) popEl.textContent = `${Number(pop).toLocaleString('id-ID')} jiwa`;
                    if (yearEl) yearEl.textContent = year || 'Tahun belum tercatat';

                    const diffEl = document.getElementById('profile-diff-kk');
                    if (diffEl) {
                        const diff = currentHandoverKk - currentPlacementKk;
                        diffEl.textContent = `Selisih serah terima–penempatan: ${Number(diff).toLocaleString('id-ID')} KK. Selisih rekap tidak menunjukkan penyebab perubahan penduduk.`;
                    }

                    closeEditRekapModal();
                    if (window.showToast) {
                        window.showToast(data.message || 'Rekap berhasil diperbarui!');
                    }
                } else {
                    alert(data.message || 'Gagal menyimpan rekap.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan rekap.');
            } finally {
                btn.disabled = false;
            }
        }
        </script>
        @endpush
    @elseif($tab === 'land')
        <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Pertanahan dan permasalahan</h2><x-admin.status :status="$upt->issue_status" /></div><div class="atlas-panel-body atlas-stack"><dl class="atlas-definition"><div><dt>Status sertifikasi SHM</dt><dd>{{ $upt->shm_status ?: 'Belum tercatat' }}</dd></div><div><dt>Data batas area</dt><dd>{{ $upt->polygon_geojson ? 'Tersedia dalam data UPT' : 'Belum tercatat' }}</dd></div></dl><div><p class="atlas-muted">Catatan permasalahan</p><p class="atlas-reading mt-2">{{ $upt->issue_note ?: 'Belum ada catatan permasalahan.' }}</p></div></div></section>
    @else
        <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Dokumen pendukung</h2><a class="atlas-link" href="{{ route('admin.documents.index') }}">Kelola arsip →</a></div><div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th>Nama berkas</th><th>Jenis</th><th>Nomor dokumen</th><th style="text-align: center; width: 70px;">AKSI</th></tr></thead><tbody>@forelse($upt->documents as $document)<tr><td>{{ $document->file_name }}</td><td>{{ $document->document_type }}</td><td>{{ $document->document_number ?: 'Belum tercatat' }}</td><td style="text-align: center;"><div class="atlas-actions-cell" style="justify-content: center;"><a class="atlas-action-icon atlas-action-icon--download" href="{{ route('admin.documents.download', $document->id) }}" title="Unduh Berkas" aria-label="Unduh"><x-admin.icon name="download" /></a></div></td></tr>@empty<tr><td colspan="4"><div class="atlas-empty">Belum ada dokumen pendukung. Unggah berkas melalui Arsip dokumen.</div></td></tr>@endforelse</tbody></table></div></section>
    @endif
    <a class="atlas-link" href="{{ route('admin.upt.index') }}">← Kembali ke Data UPT</a>
</div>
@endsection
