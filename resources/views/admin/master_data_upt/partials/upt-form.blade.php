{{-- Partial form UPT bersama untuk versi Atlas (create & edit).
    Variabel yang diharapkan: $upt (null saat create), $regencies.
    Form dibungkus oleh view pemanggil; partial ini hanya mengisi body. --}}
@php
    $isEdit = isset($upt) && $upt !== null;
    $old = fn (string $key, $default = null) => old($key, $isEdit ? $upt->{$key} : $default);
@endphp

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Identitas pokok wilayah UPT</h2></div>
    <div class="atlas-panel-body atlas-form-grid">
        @if(! $isEdit)
            <div class="atlas-field">
                <label for="upt_number">Nomor registrasi UPT <span aria-hidden="true">*</span></label>
                <div class="atlas-input-prefix">
                    <span>UPT-</span>
                    <input type="number" name="upt_number" id="upt_number" min="1" required value="{{ $old('upt_number', $nextUptNumber ?? null) }}">
                </div>
                <p class="atlas-form-hint">Nomor urut berikutnya disarankan otomatis.</p>
            </div>
        @endif
        <div class="atlas-field">
            <label for="upt_name">Nama satuan pemukiman (UPT asal) <span aria-hidden="true">*</span></label>
            <input type="text" name="upt_name" id="upt_name" required maxlength="150" value="{{ $old('upt_name') }}" placeholder="Contoh: Belawang Sp.1">
        </div>
        <div class="atlas-field">
            <label for="current_village_name">Desa definitif saat ini <span aria-hidden="true">*</span></label>
            <input type="text" name="current_village_name" id="current_village_name" required maxlength="150" value="{{ $old('current_village_name') }}" placeholder="Contoh: Desa Bagak">
        </div>
        <div class="atlas-field">
            <label for="regency_id">Kabupaten <span aria-hidden="true">*</span></label>
            <select name="regency_id" id="regency_id" required @if($isEdit) disabled aria-readonly="true" @endif>
                <option value="">— Pilih kabupaten —</option>
                @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}" @selected($old('regency_id') == $regency->id)>{{ $regency->name }}</option>
                @endforeach
            </select>
            @if($isEdit)<input type="hidden" name="regency_id" value="{{ $upt->regency_id }}">@endif
        </div>
        <div class="atlas-field">
            <label for="business_pattern">Pola usaha <span aria-hidden="true">*</span></label>
            <select name="business_pattern" id="business_pattern" required>
                @foreach(['TPLK', 'TPLB', 'PIRSUS', 'HTI', 'P4HDR'] as $pattern)
                    <option value="{{ $pattern }}" @selected($old('business_pattern', $isEdit ? $upt->business_pattern : 'TPLK') === $pattern)>{{ $pattern }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Koordinat peta</h2><span class="atlas-muted">Desimal WGS84</span></div>
    <div class="atlas-panel-body atlas-form-grid">
        <div class="atlas-field">
            <label for="latitude">Latitude</label>
            <input type="number" step="any" name="latitude" id="latitude" value="{{ $old('latitude') }}" placeholder="-2.918722">
            @unless($isEdit)<p class="atlas-form-hint">Bila diisi, poligon area ±800 m dibuat otomatis untuk peta publik.</p>@endunless
        </div>
        <div class="atlas-field">
            <label for="longitude">Longitude</label>
            <input type="number" step="any" name="longitude" id="longitude" value="{{ $old('longitude') }}" placeholder="114.435481">
        </div>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Status lahan & sertifikasi</h2></div>
    <div class="atlas-panel-body atlas-form-grid">
        <div class="atlas-field">
            <label for="issue_status">Kondisi lahan <span aria-hidden="true">*</span></label>
            <select name="issue_status" id="issue_status" required>
                @foreach(['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'] as $value => $label)
                    <option value="{{ $value }}" @selected($old('issue_status', $isEdit ? $upt->issue_status : 'clean') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="atlas-field">
            <label for="shm_status">Status sertifikasi SHM</label>
            <input type="text" name="shm_status" id="shm_status" maxlength="100" value="{{ $old('shm_status', $isEdit ? $upt->shm_status : null) }}" placeholder="Contoh: 100% SHM, Sebagian SHM">
            <p class="atlas-form-hint">Kosongkan bila belum ada data — tampil sebagai “Belum tercatat”.</p>
        </div>
        <div class="atlas-field atlas-field--wide">
            <label for="issue_note">Catatan permasalahan</label>
            <textarea name="issue_note" id="issue_note" rows="3" placeholder="Kendala batas kawasan hutan, konsesi, atau perkembangan mediasi…">{{ $old('issue_note') }}</textarea>
        </div>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Rekap kependudukan</h2><span class="atlas-muted">Angka agregat per tahap</span></div>
    <div class="atlas-panel-body atlas-stack">
        <div>
            <p class="atlas-form-section-title">Penempatan awal</p>
            <div class="atlas-form-grid mt-4">
                <div class="atlas-field">
                    <label for="placement_year">Tahun penempatan <span aria-hidden="true">*</span></label>
                    <input type="text" name="placement_year" id="placement_year" required maxlength="20" value="{{ $old('placement_year', $isEdit ? $upt->placement_year : date('Y')) }}" placeholder="Contoh: 2005 atau 2005/2006">
                </div>
                <div class="atlas-field">
                    <label for="placement_kk">KK penempatan <span aria-hidden="true">*</span></label>
                    <input type="number" name="placement_kk" id="placement_kk" required min="0" value="{{ $old('placement_kk', $isEdit ? $upt->placement_kk : 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="placement_population">Jiwa penempatan <span aria-hidden="true">*</span></label>
                    <input type="number" name="placement_population" id="placement_population" required min="0" value="{{ $old('placement_population', $isEdit ? $upt->placement_population : 0) }}">
                </div>
            </div>
        </div>
        <div>
            <p class="atlas-form-section-title">Serah terima pemda</p>
            <div class="atlas-form-grid mt-4">
                <div class="atlas-field">
                    <label for="handover_year">Tahun BAST</label>
                    <input type="text" name="handover_year" id="handover_year" maxlength="20" value="{{ $old('handover_year', $isEdit ? $upt->handover_year : null) }}" placeholder="Contoh: 2010">
                </div>
                <div class="atlas-field">
                    <label for="handover_kk">KK serah terima</label>
                    <input type="number" name="handover_kk" id="handover_kk" min="0" value="{{ $old('handover_kk', $isEdit ? $upt->handover_kk : 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_population">Jiwa serah terima</label>
                    <input type="number" name="handover_population" id="handover_population" min="0" value="{{ $old('handover_population', $isEdit ? $upt->handover_population : 0) }}">
                </div>
            </div>
        </div>
    </div>
</section>
