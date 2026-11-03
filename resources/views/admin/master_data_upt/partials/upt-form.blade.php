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
    <div class="atlas-panel-heading">
        <h2>Koordinat peta &amp; poligon wilayah</h2>
        <span class="atlas-muted">Titik WGS84 &amp; Delineasi GeoJSON</span>
    </div>
    <div class="atlas-panel-body" style="display: flex; flex-direction: column; gap: 16px;">
        <div class="atlas-field">
            <label for="coordinates">Titik koordinat lokasi (Pusat UPT)</label>
            @php
                $initialCoords = $old('coordinates');
                if (!$initialCoords && $old('latitude') && $old('longitude')) {
                    $initialCoords = \App\Support\CoordinateParser::toDms((float)$old('latitude'), (float)$old('longitude'));
                }
            @endphp
            <input type="text" name="coordinates" id="coordinates" value="{{ $initialCoords }}" placeholder="Contoh: 2°25'59.9&quot;S 115°40'49.2&quot;E atau -2.918722, 114.435481" autocomplete="off">
            <input type="hidden" name="latitude" id="latitude" value="{{ $old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ $old('longitude') }}">
            <div id="admin_coordinate_feedback" class="atlas-form-hint" style="margin-top: 6px;">
                @if($old('latitude') && $old('longitude'))
                    <span style="color: #107C41; font-weight: 500;">✓ Terdeteksi: Lintang {{ $old('latitude') }}°, Bujur {{ $old('longitude') }}°</span>
                @else
                    <span>Format yang didukung: Derajat Menit Detik (DMS) seperti <code>2°25'59.9"S 115°40'49.2"E</code> atau desimal <code>-2.918722, 114.435481</code>.</span>
                @endif
            </div>
        </div>

        <div class="atlas-field">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label for="polygon_file" style="margin: 0; font-weight: 600;">Berkas poligon batas wilayah (.json, .geojson, .shp)</label>
                <button type="button" id="btn-reset-file" class="atlas-button atlas-button--sm" style="padding: 2px 8px; font-size: 11px; display: none;">Batal / Hapus Berkas</button>
            </div>
            @php
                $initialGeojson = old('polygon_geojson');
                if ($initialGeojson === null && $isEdit && !empty($upt->polygon_geojson)) {
                    $initialGeojson = json_encode($upt->polygon_geojson);
                } elseif (is_array($initialGeojson)) {
                    $initialGeojson = json_encode($initialGeojson);
                }
            @endphp
            <input type="hidden" name="polygon_geojson" id="polygon_geojson" value="{{ $initialGeojson }}">

            <input type="file" name="polygon_file" id="polygon_file" accept=".json,.geojson,.shp,.zip,application/json,application/geo+json" style="display: block; width: 100%; padding: 8px 12px; border: 1px solid var(--atlas-line); border-radius: var(--atlas-radius); background: white; cursor: pointer; font-size: 13px;">

            <div id="admin_geojson_feedback" class="atlas-form-hint" style="margin-top: 6px;">
                @if($isEdit && !empty($upt->polygon_geojson))
                    @php
                        $type = $upt->polygon_geojson['type'] ?? 'Polygon';
                        $ptCount = 0;
                        $countPts = function ($c) use (&$countPts, &$ptCount) {
                            if (is_array($c) && isset($c[0]) && isset($c[1]) && is_numeric($c[0])) { $ptCount++; }
                            elseif (is_array($c)) { foreach ($c as $sub) $countPts($sub); }
                        };
                        $countPts($upt->polygon_geojson['coordinates'] ?? []);
                    @endphp
                    <span style="color: #107C41; font-weight: 500;">✓ Berkas poligon tersimpan: Tipe <strong>{{ $type }}</strong> ({{ $ptCount }} titik koordinat). Pilih berkas baru (.json, .geojson, atau .shp) bila ingin mengganti.</span>
                @else
                    <span>Pilih berkas <code>.json</code>, <code>.geojson</code>, atau <code>.shp</code> (Shapefile) batas wilayah. Bila tidak diunggah, poligon estimasi ±800 m dibuat otomatis dari titik koordinat.</span>
                @endif
            </div>
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

<section class="atlas-panel" style="background: #F4F7F9; border-left: 4px solid var(--atlas-blue);">
    <div class="atlas-panel-body" style="padding: 16px 20px; display: flex; align-items: flex-start; gap: 14px;">
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 6px; background: #E8F0FA; color: var(--atlas-blue); flex-shrink: 0; margin-top: 2px;">
            <x-admin.icon name="users" />
        </span>
        <div>
            <h3 style="font-size: 14.5px; font-weight: 700; color: #0E2A47; margin-bottom: 4px;">Pemisahan Rekap Kependudukan &amp; Serah Terima</h3>
            <p style="font-size: 13px; line-height: 1.5; color: #4A6072; margin: 0;">
                Data rekap penempatan awal dan serah terima pemda kini dikelola terpisah melalui modul <a href="{{ route('admin.placements.index') }}" class="atlas-link" style="font-weight: 600;">Kependudukan</a>. UPT baru yang didaftarkan dapat dilengkapi penempatan awalnya terlebih dahulu sebelum diproseskan kelayakan serah terima BAST.
            </p>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function() {
    function setupCoordSync() {
        const coordInput = document.getElementById('coordinates');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const feedbackEl = document.getElementById('admin_coordinate_feedback');

        if (!coordInput) return;

        function handleCoordChange() {
            const val = coordInput.value.trim();
            if (!val) {
                if (latInput) latInput.value = '';
                if (lngInput) lngInput.value = '';
                if (feedbackEl) feedbackEl.innerHTML = '<span>Format yang didukung: Derajat Menit Detik (DMS) seperti <code>2°25\'59.9"S 115°40\'49.2"E</code> atau desimal <code>-2.918722, 114.435481</code>.</span>';
                return;
            }

            if (typeof window.parseCoordinates === 'function') {
                const parsed = window.parseCoordinates(val);
                if (parsed) {
                    if (latInput) latInput.value = parsed.latitude;
                    if (lngInput) lngInput.value = parsed.longitude;
                    const inKalsel = (parsed.latitude >= -5.5 && parsed.latitude <= -1.0 && parsed.longitude >= 114.0 && parsed.longitude <= 117.5);
                    if (feedbackEl) {
                        if (inKalsel) {
                            feedbackEl.innerHTML = `<span style="color: #107C41; font-weight: 500;">✓ Terdeteksi: Lintang ${parsed.latitude}°, Bujur ${parsed.longitude}° (Wilayah Kalsel)</span>`;
                        } else {
                            feedbackEl.innerHTML = `<span style="color: #C42B1C; font-weight: 500;">⚠️ Peringatan: Lintang ${parsed.latitude}°, Bujur ${parsed.longitude}° di luar wilayah Kalimantan Selatan.</span>`;
                        }
                    }
                } else {
                    if (feedbackEl) {
                        feedbackEl.innerHTML = `<span style="color: #97620B;">Format belum dikenali. Contoh: <code>2°25'59.9"S 115°40'49.2"E</code> atau <code>-2.433306, 115.680333</code></span>`;
                    }
                }
            }
        }

        coordInput.addEventListener('input', handleCoordChange);
    }

    function parseShpArrayBuffer(buffer) {
        try {
            const view = new DataView(buffer);
            if (view.byteLength < 100) return null;
            const fileCode = view.getInt32(0, false);
            if (fileCode !== 9994) return null;
            const shapeType = view.getInt32(32, true);
            if (![5, 15, 25].includes(shapeType)) return null;

            let offset = 100;
            const allPolygons = [];

            while (offset + 8 <= view.byteLength) {
                const recLenWords = view.getInt32(offset + 4, false);
                const recBytes = recLenWords * 2;
                offset += 8;

                if (offset + recBytes > view.byteLength) break;

                const recType = view.getInt32(offset, true);
                if ([5, 15, 25].includes(recType)) {
                    const numParts = view.getInt32(offset + 36, true);
                    const numPoints = view.getInt32(offset + 40, true);

                    const partsOffset = offset + 44;
                    const parts = [];
                    for (let i = 0; i < numParts; i++) {
                        parts.push(view.getInt32(partsOffset + (i * 4), true));
                    }

                    const pointsOffset = partsOffset + (numParts * 4);
                    const rings = [];
                    for (let p = 0; p < numParts; p++) {
                        const startIndex = parts[p];
                        const endIndex = (p + 1 < numParts) ? parts[p + 1] : numPoints;
                        const ring = [];
                        for (let pt = startIndex; pt < endIndex; pt++) {
                            const ptOffset = pointsOffset + (pt * 16);
                            const x = view.getFloat64(ptOffset, true);
                            const y = view.getFloat64(ptOffset + 8, true);
                            ring.push([Number(x.toFixed(6)), Number(y.toFixed(6))]);
                        }
                        if (ring.length > 0) {
                            if (ring[0][0] !== ring[ring.length - 1][0] || ring[0][1] !== ring[ring.length - 1][1]) {
                                ring.push(ring[0]);
                            }
                            rings.push(ring);
                        }
                    }
                    if (rings.length > 0) {
                        allPolygons.push(rings);
                    }
                }
                offset += recBytes;
            }

            if (allPolygons.length === 0) return null;
            if (allPolygons.length === 1) {
                return { type: 'Polygon', coordinates: allPolygons[0] };
            }
            return { type: 'MultiPolygon', coordinates: allPolygons };
        } catch (e) {
            return null;
        }
    }

    function setupGeojsonSync() {
        const fileInput = document.getElementById('polygon_file');
        const hiddenInput = document.getElementById('polygon_geojson');
        const feedbackEl = document.getElementById('admin_geojson_feedback');
        const resetBtn = document.getElementById('btn-reset-file');
        const coordInput = document.getElementById('coordinates');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const coordFeedback = document.getElementById('admin_coordinate_feedback');

        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) {
                if (resetBtn) resetBtn.style.display = 'none';
                return;
            }

            if (resetBtn) resetBtn.style.display = 'inline-block';

            if (feedbackEl) {
                feedbackEl.innerHTML = `<span style="color: var(--atlas-blue-dark);">⏳ Membaca berkas ${file.name}…</span>`;
            }

            const fileName = file.name.toLowerCase();
            const isShp = fileName.endsWith('.shp');
            const reader = new FileReader();

            function processGeometry(geom, formatLabel) {
                if (!geom || !geom.type || !['Polygon', 'MultiPolygon'].includes(geom.type)) {
                    if (feedbackEl) {
                        feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Berkas <strong>${file.name}</strong> tidak memuat objek Polygon / MultiPolygon yang valid.</span>`;
                    }
                    fileInput.value = '';
                    if (resetBtn) resetBtn.style.display = 'none';
                    return;
                }

                let points = [];
                function extractPts(c) {
                    if (Array.isArray(c) && typeof c[0] === 'number' && typeof c[1] === 'number') {
                        points.push(c);
                    } else if (Array.isArray(c)) {
                        c.forEach(extractPts);
                    }
                }
                extractPts(geom.coordinates);

                if (points.length < 3) {
                    if (feedbackEl) {
                        feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Berkas poligon memerlukan minimal 3 titik koordinat.</span>`;
                    }
                    fileInput.value = '';
                    if (resetBtn) resetBtn.style.display = 'none';
                    return;
                }

                let sumLng = 0, sumLat = 0;
                points.forEach(p => { sumLng += p[0]; sumLat += p[1]; });
                const avgLat = Number((sumLat / points.length).toFixed(6));
                const avgLng = Number((sumLng / points.length).toFixed(6));
                const fileSizeKb = (file.size / 1024).toFixed(1);

                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(geom);
                }

                if (feedbackEl) {
                    feedbackEl.innerHTML = `<span style="color: #107C41; font-weight: 500;">✓ Berkas <strong>${file.name}</strong> (${fileSizeKb} KB, ${formatLabel}) valid: Tipe <strong>${geom.type}</strong> (${points.length} titik koordinat). Titik tengah: ${avgLat}°, ${avgLng}°</span>`;
                }

                if (coordInput && (!coordInput.value || !coordInput.value.trim())) {
                    coordInput.value = `${avgLat}, ${avgLng}`;
                    if (latInput) latInput.value = avgLat;
                    if (lngInput) lngInput.value = avgLng;
                    if (coordFeedback) {
                        coordFeedback.innerHTML = `<span style="color: #107C41; font-weight: 500;">✓ Pusat UPT otomatis dihitung dari poligon ${formatLabel}: ${avgLat}°, ${avgLng}°</span>`;
                    }
                }
            }

            if (isShp) {
                reader.onload = function(event) {
                    try {
                        const geom = parseShpArrayBuffer(event.target.result);
                        if (!geom) {
                            if (feedbackEl) {
                                feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Berkas <strong>${file.name}</strong> bukan ESRI Shapefile Polygon (.shp) yang valid.</span>`;
                            }
                            fileInput.value = '';
                            if (resetBtn) resetBtn.style.display = 'none';
                            return;
                        }
                        processGeometry(geom, 'ESRI Shapefile (.shp)');
                    } catch (err) {
                        if (feedbackEl) {
                            feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Gagal memproses berkas Shapefile: ${err.message}</span>`;
                        }
                        fileInput.value = '';
                        if (resetBtn) resetBtn.style.display = 'none';
                    }
                };
                reader.readAsArrayBuffer(file);
            } else {
                reader.onload = function(event) {
                    try {
                        const content = event.target.result;
                        const parsed = JSON.parse(content);
                        let geom = parsed;
                        if (parsed.type === 'Feature' && parsed.geometry) {
                            geom = parsed.geometry;
                        } else if (parsed.type === 'FeatureCollection' && parsed.features && parsed.features[0] && parsed.features[0].geometry) {
                            geom = parsed.features[0].geometry;
                        }
                        processGeometry(geom, 'GeoJSON');
                    } catch (err) {
                        if (feedbackEl) {
                            feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Berkas tidak dapat dibaca sebagai JSON: ${err.message}</span>`;
                        }
                        fileInput.value = '';
                        if (resetBtn) resetBtn.style.display = 'none';
                    }
                };
                reader.readAsText(file);
            }

            reader.onerror = function() {
                if (feedbackEl) {
                    feedbackEl.innerHTML = `<span style="color: #C42B1C;">⚠️ Gagal membaca berkas.</span>`;
                }
                fileInput.value = '';
                if (resetBtn) resetBtn.style.display = 'none';
            };
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                fileInput.value = '';
                resetBtn.style.display = 'none';
                if (hiddenInput) hiddenInput.value = '';
                if (feedbackEl) {
                    feedbackEl.innerHTML = '<span>Pilih berkas <code>.json</code>, <code>.geojson</code>, atau <code>.shp</code> (Shapefile) batas wilayah. Bila tidak diunggah, poligon estimasi ±800 m dibuat otomatis dari titik koordinat.</span>';
                }
            });
        }
    }

    function init() {
        setupCoordSync();
        setupGeojsonSync();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
