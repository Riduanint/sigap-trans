@extends('layouts.admin')

@section('atlas_title', 'Edit draf usulan UPT baru')
@section('atlas_subtitle', 'Perbarui formulir data usulan registrasi UPT sebelum diverifikasi Super Admin.')

@section('content')
<form action="{{ route('operator.requests.update', $changeRequest->id) }}" method="POST" enctype="multipart/form-data" class="atlas-stack"
      data-confirm="Simpan perubahan data usulan UPT baru ini?" 
      data-confirm-title="Konfirmasi Perubahan Draf" 
      data-confirm-button="Ya, Simpan Perubahan">
    @csrf
    @method('PUT')

    @if(isset($errors) && $errors->any())
        <div class="atlas-alert atlas-alert--error" role="alert">
            <strong>Periksa kembali isian berikut.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Identitas Pokok Wilayah Baru --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <div>
                <h3 class="text-[#107C41] flex items-center gap-2">
                    <x-admin.icon name="plus" />
                    <span>Identitas pokok UPT yang diusulkan</span>
                </h3>
                <p class="atlas-muted">Perbarui data primer satuan pemukiman baru.</p>
            </div>
            <span class="atlas-status atlas-status--clean">Edit UPT Baru</span>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="new_regency_id">Kabupaten <span aria-hidden="true">*</span></label>
                    <select name="regency_id" id="new_regency_id" required>
                        <option value="">— Pilih kabupaten —</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" data-lat="{{ $reg->latitude }}" data-lng="{{ $reg->longitude }}" @selected(old('regency_id', $payload['regency_id'] ?? '') == $reg->id)>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                    <p class="atlas-form-hint">Wilayah administratif UPT binaan.</p>
                </div>

                <div class="atlas-field">
                    <label for="new_upt_number">Nomor UPT yang disarankan</label>
                    <div class="atlas-input-prefix">
                        <span>UPT-</span>
                        <input type="number" name="upt_number" id="new_upt_number" min="1" value="{{ old('upt_number', $payload['upt_number'] ?? '') }}">
                    </div>
                    <p class="atlas-form-hint">Nomor identitas usulan UPT.</p>
                </div>

                <div class="atlas-field">
                    <label for="new_upt_name">Nama satuan pemukiman (UPT) <span aria-hidden="true">*</span></label>
                    <input type="text" name="upt_name" id="new_upt_name" required maxlength="150" value="{{ old('upt_name', $payload['upt_name'] ?? '') }}" placeholder="Contoh: Belawang Sp.4 atau Kintap SP-5">
                    <p class="atlas-form-hint">Nama penamaan pemukiman transmigrasi asal.</p>
                </div>

                <div class="atlas-field">
                    <label for="new_current_village_name">Desa definitif saat ini <span aria-hidden="true">*</span></label>
                    <input type="text" name="current_village_name" id="new_current_village_name" required maxlength="150" value="{{ old('current_village_name', $payload['current_village_name'] ?? '') }}" placeholder="Contoh: Desa Makmur Sejahtera">
                    <p class="atlas-form-hint">Nama desa pemerintahan definitif saat ini.</p>
                </div>

                <div class="atlas-field">
                    <label for="new_business_pattern">Pola usaha budidaya <span aria-hidden="true">*</span></label>
                    <input type="text" name="business_pattern" id="new_business_pattern" required maxlength="50" value="{{ old('business_pattern', $payload['business_pattern'] ?? 'TPLK') }}" placeholder="Contoh: TPLK, TPLB, PIRSUS, Tanaman Pangan">
                </div>

                <div class="atlas-field">
                    <label for="new_shm_status">Status sertifikasi SHM</label>
                    <select name="shm_status" id="new_shm_status">
                        @php $curShm = old('shm_status', $payload['shm_status'] ?? 'Belum SHM'); @endphp
                        <option value="Belum SHM" @selected($curShm === 'Belum SHM')>Belum SHM — proses GTRA / inventarisasi</option>
                        <option value="Sebagian SHM" @selected($curShm === 'Sebagian SHM')>Sebagian SHM — redistribusi bertahap</option>
                        <option value="100% SHM" @selected($curShm === '100% SHM')>100% SHM — sertifikasi tuntas</option>
                    </select>
                </div>

                <div class="atlas-field atlas-field--wide">
                    <label for="submission_note_new">Uraian alasan & pengantar pengusulan UPT baru <span aria-hidden="true">*</span></label>
                    <textarea name="submission_note" id="submission_note_new" required rows="3" placeholder="Jelaskan dasar hukum pembentukan, riwayat pemekaran, atau kondisi penetapan kawasan UPT baru ini...">{{ old('submission_note', $payload['submission_note'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    {{-- Koordinat Lokasi Peta --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <div>
                <h3>Titik koordinat lokasi</h3>
                <p class="atlas-muted">Klik pada peta atau masukkan format desimal WGS84</p>
            </div>
        </div>
        <div class="atlas-panel-body">
            <div id="upt-coordinate-picker-map" style="height: 320px; width: 100%; border-radius: 6px; border: 1px solid #D4DEE7; margin-bottom: 10px; z-index: 1;"></div>
            <div class="atlas-inline" style="justify-content: space-between; margin-bottom: 14px; font-size: 0.8rem; color: var(--atlas-muted);">
                <span>💡 Klik pada peta atau geser pin untuk menentukan titik koordinat UPT di Kalimantan Selatan.</span>
                <button type="button" id="btn-reset-map-picker" class="atlas-link" style="font-size: 0.8rem; cursor: pointer; background: none; border: none; padding: 0;">Pusatkan ke Kabupaten</button>
            </div>
            <div class="atlas-field">
                <label for="coordinates_input">Titik koordinat lokasi</label>
                @php
                    $initLat = old('latitude', $payload['latitude'] ?? null);
                    $initLng = old('longitude', $payload['longitude'] ?? null);
                    $initCoords = old('coordinates', ($initLat && $initLng) ? ($payload['coordinates_dms'] ?? \App\Support\CoordinateParser::toDms((float)$initLat, (float)$initLng)) : '');
                @endphp
                <input type="text" name="coordinates" id="coordinates_input" 
                       value="{{ $initCoords }}" 
                       placeholder="Contoh: 2°25'59.9&quot;S 115°40'49.2&quot;E atau -2.433306, 115.680333" 
                       autocomplete="off">
                <input type="hidden" name="latitude" id="new_latitude" value="{{ $initLat }}">
                <input type="hidden" name="longitude" id="new_longitude" value="{{ $initLng }}">
                <div id="coordinate_feedback" class="atlas-form-hint" style="margin-top: 6px;">
                    <span>Format yang didukung: Derajat Menit Detik (DMS) seperti <code>2°25'59.9"S 115°40'49.2"E</code> atau desimal <code>-2.433306, 115.680333</code>. Titik harus berada di wilayah Kalimantan Selatan.</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Tombol Aksi Bawah --}}
    <div class="atlas-inline" style="justify-content: flex-end; gap: 12px;">
        <a href="{{ route('operator.requests.index') }}" class="atlas-button">Batalkan</a>
        <button type="submit" class="atlas-button atlas-button--primary">
            <x-admin.icon name="check" />
            <span>Simpan perubahan usulan</span>
        </button>
    </div>
</form>

<script>
let pickerMap = null;
let pickerMarker = null;

function initPickerMap() {
    const mapContainer = document.getElementById('upt-coordinate-picker-map');
    if (!mapContainer || typeof window.L === 'undefined') return;
    if (pickerMap) {
        pickerMap.invalidateSize();
        return;
    }

    const coordInput = document.getElementById('coordinates_input');
    const latInput = document.getElementById('new_latitude');
    const lngInput = document.getElementById('new_longitude');
    const feedbackEl = document.getElementById('coordinate_feedback');

    const parseCoords = (raw) => {
        if (typeof window.parseCoordinates === 'function') {
            return window.parseCoordinates(raw);
        }
        return null;
    };

    const toDms = (lat, lng) => {
        if (typeof window.toDmsString === 'function') {
            return window.toDmsString(lat, lng);
        }
        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    };

    function renderFeedback(lat, lng) {
        if (!feedbackEl) return;
        const inKalsel = (lat >= -5.5 && lat <= -1.0 && lng >= 114.0 && lng <= 117.5);
        if (inKalsel) {
            feedbackEl.innerHTML = `<span style="color: #107C41; font-weight: 500;">✓ Titik valid: Lintang ${lat}°, Bujur ${lng}° (Wilayah Kalimantan Selatan)</span>`;
        } else {
            feedbackEl.innerHTML = `<span style="color: #C42B1C; font-weight: 500;">⚠️ Peringatan: Lintang ${lat}°, Bujur ${lng}° di luar wilayah Kalimantan Selatan (-5.5 s/d -1.0 Lat, 114.0 s/d 117.5 Lng).</span>`;
        }
    }

    if (coordInput && coordInput.value) {
        const parsed = parseCoords(coordInput.value);
        if (parsed) {
            if (latInput) latInput.value = parsed.latitude;
            if (lngInput) lngInput.value = parsed.longitude;
        }
    }

    let initLat = parseFloat(latInput?.value) || -2.80;
    let initLng = parseFloat(lngInput?.value) || 115.35;
    let hasCoords = !!(latInput?.value && lngInput?.value);
    let initZoom = hasCoords ? 11 : 8;

    pickerMap = window.L.map(mapContainer, { scrollWheelZoom: false }).setView([initLat, initLng], initZoom);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 18,
    }).addTo(pickerMap);

    pickerMarker = window.L.marker([initLat, initLng], { draggable: true }).addTo(pickerMap);

    if (hasCoords) {
        renderFeedback(initLat, initLng);
    }

    function updateFromLocation(lat, lng, syncText = true) {
        const fLat = Number(parseFloat(lat).toFixed(6));
        const fLng = Number(parseFloat(lng).toFixed(6));
        if (latInput) latInput.value = fLat;
        if (lngInput) lngInput.value = fLng;

        if (syncText && coordInput) {
            coordInput.value = toDms(fLat, fLng);
        }

        renderFeedback(fLat, fLng);
    }

    pickerMap.on('click', function(e) {
        pickerMarker.setLatLng(e.latlng);
        updateFromLocation(e.latlng.lat, e.latlng.lng, true);
    });

    pickerMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateFromLocation(pos.lat, pos.lng, true);
    });

    function onCoordInputChange() {
        if (!coordInput) return;
        const val = coordInput.value.trim();
        if (!val) {
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            if (feedbackEl) feedbackEl.innerHTML = '<span>Format yang didukung: Derajat Menit Detik (DMS) seperti <code>2°25\'59.9"S 115°40\'49.2"E</code> atau desimal <code>-2.433306, 115.680333</code>. Titik harus berada di wilayah Kalimantan Selatan.</span>';
            return;
        }

        const parsed = parseCoords(val);
        if (parsed) {
            if (latInput) latInput.value = parsed.latitude;
            if (lngInput) lngInput.value = parsed.longitude;
            pickerMarker.setLatLng([parsed.latitude, parsed.longitude]);
            pickerMap.setView([parsed.latitude, parsed.longitude], Math.max(pickerMap.getZoom(), 11));
            renderFeedback(parsed.latitude, parsed.longitude);
        } else {
            if (feedbackEl) {
                feedbackEl.innerHTML = `<span style="color: #97620B;">Format belum dikenali. Contoh yang didukung: <code>2°25'59.9"S 115°40'49.2"E</code> atau <code>-2.433306, 115.680333</code></span>`;
            }
        }
    }

    if (coordInput) {
        coordInput.addEventListener('input', onCoordInputChange);
        if (coordInput.value) {
            onCoordInputChange();
        }
    }

    const regencySelect = document.getElementById('new_regency_id');
    function flyToRegency() {
        if (!regencySelect || !pickerMap) return;
        const opt = regencySelect.options[regencySelect.selectedIndex];
        if (opt && opt.dataset.lat && opt.dataset.lng) {
            const rLat = parseFloat(opt.dataset.lat);
            const rLng = parseFloat(opt.dataset.lng);
            pickerMap.setView([rLat, rLng], 10);
            if (!coordInput || !coordInput.value) {
                pickerMarker.setLatLng([rLat, rLng]);
                updateFromLocation(rLat, rLng, true);
            }
        }
    }

    if (regencySelect) {
        regencySelect.addEventListener('change', flyToRegency);
    }

    const resetBtn = document.getElementById('btn-reset-map-picker');
    if (resetBtn) {
        resetBtn.addEventListener('click', flyToRegency);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(initPickerMap, 200);
});
</script>
@endsection

