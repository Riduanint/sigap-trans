@php
    $isEdit = isset($skp) && $skp !== null;
    $old = fn (string $key, $default = null) => old($key, $isEdit ? $skp->{$key} : $default);
    $existingVillages = $isEdit ? $skp->villages : collect();
@endphp

@if($isEdit)
    <script>window.currentSkpId = {{ $skp->id }};</script>
@else
    <script>window.currentSkpId = null;</script>
@endif

{{-- Panel 1: Identitas Pokok Kawasan & SKP --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Identitas pokok Kawasan & Satuan Pengembangan (SKP)</h2>
        <span class="atlas-muted">Satuan wilayah transmigrasi tingkat kawasan</span>
    </div>
    <div class="atlas-panel-body atlas-form-grid">
        <div class="atlas-field">
            <label for="regency_id">Kabupaten <span aria-hidden="true">*</span></label>
            <select name="regency_id" id="regency_id" required>
                <option value="">— Pilih kabupaten —</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" @selected($old('regency_id') == $reg->id)>{{ $reg->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="atlas-field">
            <label for="kawasan_name">Nama Kawasan Transmigrasi <span aria-hidden="true">*</span></label>
            <input type="text" name="kawasan_name" id="kawasan_name" required maxlength="150" value="{{ $old('kawasan_name') }}" placeholder="Contoh: Kawasan Transmigrasi Cahaya Baru">
        </div>

        <div class="atlas-field">
            <label for="skp_name">Nama SKP <span aria-hidden="true">*</span></label>
            <input type="text" name="skp_name" id="skp_name" required maxlength="100" value="{{ $old('skp_name') }}" placeholder="Contoh: SKP-B atau SKP P">
        </div>

        <div class="atlas-field">
            <label for="unit_code">Kode Unit SKP</label>
            <input type="text" name="unit_code" id="unit_code" maxlength="50" value="{{ $old('unit_code') }}" placeholder="Contoh: B, P, atau G">
            <p class="atlas-form-hint">Kode abjad singkatan untuk simbol peta.</p>
        </div>

        <div class="atlas-field">
            <label for="luas_ha">Estimasi luas kawasan (Hektar)</label>
            <input type="number" step="0.01" name="luas_ha" id="luas_ha" value="{{ $old('luas_ha') }}" placeholder="Contoh: 12305.50">
        </div>

        <div class="atlas-field">
            <label for="color">Warna batas poligon di WebGIS</label>
            <div class="atlas-inline">
                <input type="color" name="color" id="color" value="{{ $old('color', '#163A63') }}" style="width: 44px; height: 38px; padding: 2px; border: 1px solid var(--atlas-line); border-radius: 4px; cursor: pointer;">
                <span class="atlas-form-hint">Warna lapisan peta publik</span>
            </div>
        </div>

        <div class="atlas-field">
            <label for="sumber_data">Sumber data rujukan</label>
            <input type="text" name="sumber_data" id="sumber_data" maxlength="150" value="{{ $old('sumber_data', 'DPMD & BIG') }}" placeholder="Contoh: DPMD & BIG atau Dokumen BPS">
            <span class="atlas-form-hint">Instansi rujukan data spasial & demografi</span>
        </div>

        <div class="atlas-field atlas-field--wide">
            <label for="keterangan">Keterangan cakupan wilayah</label>
            <textarea name="keterangan" id="keterangan" rows="2" placeholder="Contoh: Satuan Kawasan Pengembangan B mencakup wilayah Kecamatan Mandastana…">{{ $old('keterangan') }}</textarea>
        </div>
    </div>
</section>

{{-- Panel 2: Tabel Profil Desa Binaan (Input 1 per 1 via Modal) --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div>
            <h2>Data desa & kependudukan di kawasan SKP</h2>
            <span class="atlas-muted">Rincian nama desa, luas wilayah, jumlah penduduk L/P, dan kepala keluarga (KK)</span>
        </div>
        <button type="button" class="atlas-button atlas-button--sm atlas-button--primary" onclick="openAddVillageModal()">
            <x-admin.icon name="plus" /> Tambah data desa
        </button>
    </div>

    <div class="atlas-panel-body" style="padding: 0;">
        <div class="atlas-table-scroll">
            <table class="atlas-table" id="table-villages-clean">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 180px;">Nama Desa & Kecamatan</th>
                        <th scope="col" class="tabular-nums" style="text-align: right; width: 110px;">Luas (Km²)</th>
                        <th scope="col" class="tabular-nums" style="text-align: right; width: 140px;">Penduduk (L / P)</th>
                        <th scope="col" class="tabular-nums" style="text-align: right; width: 110px;">Total Jiwa</th>
                        <th scope="col" class="tabular-nums" style="text-align: right; width: 90px;">Jumlah KK</th>
                        <th scope="col" style="min-width: 150px;">Kaitan UPT</th>
                        <th scope="col" style="width: 100px; text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody id="village-clean-tbody">
                    @forelse($existingVillages as $idx => $v)
                        <tr data-id="{{ $v->id }}"
                            data-index="{{ $idx }}"
                            data-district="{{ $v->district_name }}"
                            data-village="{{ $v->village_name }}"
                            data-area="{{ $v->area_km2 }}"
                            data-male="{{ $v->population_male }}"
                            data-female="{{ $v->population_female }}"
                            data-total="{{ $v->population_total }}"
                            data-family="{{ $v->family_count }}"
                            data-upt-id="{{ $v->upt_location_id }}">

                            <input type="hidden" name="villages[{{ $idx }}][id]" value="{{ $v->id }}">
                            <input type="hidden" name="villages[{{ $idx }}][district_name]" value="{{ $v->district_name }}">
                            <input type="hidden" name="villages[{{ $idx }}][village_name]" value="{{ $v->village_name }}">
                            <input type="hidden" name="villages[{{ $idx }}][area_km2]" value="{{ $v->area_km2 }}">
                            <input type="hidden" name="villages[{{ $idx }}][population_male]" value="{{ $v->population_male }}">
                            <input type="hidden" name="villages[{{ $idx }}][population_female]" value="{{ $v->population_female }}">
                            <input type="hidden" name="villages[{{ $idx }}][family_count]" value="{{ $v->family_count }}">
                            <input type="hidden" name="villages[{{ $idx }}][upt_location_id]" value="{{ $v->upt_location_id }}">

                            <td>
                                <strong>{{ $v->village_name }}</strong>
                                <span class="atlas-muted" style="display: block; font-size: 11px;">Kec. {{ $v->district_name }}</span>
                            </td>
                            <td class="tabular-nums" style="text-align: right;">{{ number_format($v->area_km2, 2, ',', '.') }} Km²</td>
                            <td class="tabular-nums" style="text-align: right;">{{ number_format($v->population_male, 0, ',', '.') }} L · {{ number_format($v->population_female, 0, ',', '.') }} P</td>
                            <td class="tabular-nums" style="text-align: right; font-weight: 700; color: #0E2A47;">{{ number_format($v->population_total, 0, ',', '.') }} Jiwa</td>
                            <td class="tabular-nums" style="text-align: right; font-weight: 600; color: #107C41;">{{ number_format($v->family_count, 0, ',', '.') }} KK</td>
                            <td>
                                @if($v->uptLocation)
                                    <span class="atlas-badge" style="font-size: 11px; background: #EFF6FF; color: #1E40AF;">
                                        {{ $v->uptLocation->upt_name }}
                                    </span>
                                @else
                                    <span class="atlas-badge" style="font-size: 11px; background: #F1F5F9; color: #64748B;">
                                        Non-UPT
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                                    <button type="button" class="atlas-action-icon atlas-action-icon--edit" onclick="openEditVillageModal(this)" title="Ubah Data Desa" aria-label="Ubah">
                                        <x-admin.icon name="edit" />
                                    </button>
                                    @if($isEdit)
                                        <button type="button" class="atlas-action-icon atlas-action-icon--delete" onclick="deleteServerVillageRow(this, {{ $v->id }})" title="Hapus Data Desa" aria-label="Hapus">
                                            <x-admin.icon name="trash" />
                                        </button>
                                    @else
                                        <button type="button" class="atlas-action-icon atlas-action-icon--delete" onclick="deleteClientSideVillageRow(this)" title="Hapus Data Desa" aria-label="Hapus">
                                            <x-admin.icon name="trash" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-village-row">
                            <td colspan="7">
                                <div class="atlas-empty" style="padding: 24px;">
                                    <p style="margin: 0 0 8px;">Belum ada data desa di kawasan ini.</p>
                                    <button type="button" class="atlas-button atlas-button--sm atlas-button--primary" onclick="openAddVillageModal()">
                                        + Tambah data desa pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: #F8FAFC; font-weight: 700;">
                        <td>Total Rekapitulasi: <span id="summary-village-count">{{ $existingVillages->count() }} Desa</span></td>
                        <td class="tabular-nums" style="text-align: right;" id="summary-total-area">{{ number_format($existingVillages->sum('area_km2'), 2, ',', '.') }} Km²</td>
                        <td class="tabular-nums" style="text-align: right;">-</td>
                        <td class="tabular-nums" style="text-align: right; color: #0E2A47;" id="summary-total-pop">{{ number_format($existingVillages->sum('population_total'), 0, ',', '.') }} Jiwa</td>
                        <td class="tabular-nums" style="text-align: right; color: #107C41;" id="summary-total-fam">{{ number_format($existingVillages->sum('family_count'), 0, ',', '.') }} KK</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div style="padding: 12px 16px; background-color: #F8FAFC; border-top: 1px solid var(--atlas-line);">
            <p class="atlas-form-hint" style="margin: 0;">Kelola data desa secara fokus 1 per 1 melalui modal tanpa formulir bertumpuk.</p>
        </div>
    </div>
</section>

{{-- Panel 3: Delineasi Poligon Spasial --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Delineasi spasial batas kawasan (GeoJSON)</h2>
        <span class="atlas-muted">Untuk visualisasi poligon interaktif di WebGIS publik</span>
    </div>
    <div class="atlas-panel-body">
        <div class="atlas-field">
            <label for="geojson_file">Unggah berkas batas GeoJSON (.geojson / .json)</label>
            <input type="file" name="geojson_file" id="geojson_file" accept=".geojson,.json">
            <p class="atlas-form-hint">Maksimal 10 MB. Mendukung MultiPolygon / Polygon EPSG:4326.</p>
            @if($isEdit && !empty($skp->polygon_geojson))
                <div style="margin-top: 8px; color: #107C41; font-size: 13px; font-weight: 600;">
                    ✓ Berkas poligon batas kawasan telah tersimpan di sistem. Unggah berkas baru untuk mengganti.
                </div>
            @endif
        </div>
    </div>
</section>
