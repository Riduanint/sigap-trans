import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

/**
 * WebGIS Engine untuk SIGAP-TRANS Kalsel
 */
class SigapWebGis {
    constructor() {
        this.map = null;
        this.clusterGroup = null;
        this.polygonLayerGroup = null;
        this.regencyLayerGroup = null;
        this.skpLayerGroup = null;
        this.highlightPolygonLayer = null;
        this.allFeatures = [];
        this.filteredFeatures = [];
        this.regencyLayers = {}; // ID -> L.geoJSON
        this.skpLayers = []; // Daftar layer L.geoJSON SKP
        this.markers = {}; // ID -> L.marker
        this.activeBasemap = 'street';
        this.tileLayers = {};
        const urlParams = new URLSearchParams(window.location.search);
        this.targetSkpId = urlParams.get('skp_id') || urlParams.get('skp');
        this.showPolygons = true;
        this.showRegencyBoundaries = true;
        this.showSkpBoundaries = Boolean(this.targetSkpId);
        this.selectedRegencyId = 'all';
        this.isNavigationVisible = true;

        // State loading & debounce
        this.isSkpLoading = false;
        this.lastSkpClickTime = 0;
        this.isUptLoading = false;
        this.lastUptClickTime = 0;

        // Koordinat default Kalimantan Selatan
        this.defaultCenter = [-2.80, 115.35];
        this.defaultZoom = 8.5;
    }

    init() {
        const mapContainer = document.getElementById('sigap-map');
        if (!mapContainer) return;

        this.initMap();
        this.initBasemaps();
        this.initLayers();
        this.bindEvents();
        this.loadInitialData();
        this.loadRegencyBoundaries();
        this.loadSkpBoundaries();
        this.initNavigationToggle();
    }

    initMap() {
        this.map = L.map('sigap-map', {
            center: this.defaultCenter,
            zoom: this.defaultZoom,
            minZoom: 7,
            maxZoom: 18,
            zoomControl: false,
            attributionControl: false,
        });

        // Zoom Control di posisi kanan bawah (terangkat di atas telemetry bar)
        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);

        // Update koordinat kursor live pada telemetry bar
        this.map.on('mousemove', (e) => {
            const coordsEl = document.getElementById('telemetry-coords');
            if (coordsEl) {
                coordsEl.textContent = `${e.latlng.lat.toFixed(5)}, ${e.latlng.lng.toFixed(5)}`;
            }
        });

        // Update zoom level live pada telemetry bar
        this.map.on('zoomend', () => {
            const zoomEl = document.getElementById('telemetry-zoom');
            if (zoomEl) {
                zoomEl.textContent = this.map.getZoom();
            }
        });
    }

    initBasemaps() {
        // 1. Peta Jalan Modern Bersih (Esri World Street Map - Bebas Watermark)
        this.tileLayers.street = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '&copy; Esri &mdash; National Geographic, DeLorme, NAVTEQ',
        });

        // 2. Citra Satelit Beresolusi Tinggi (Esri World Imagery)
        this.tileLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: '&copy; Esri',
        });

        // 3. Label overlay untuk mode satelit agar batas dan nama tempat tetap terbaca (Esri Reference Labels)
        this.tileLayers.satelliteLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: '&copy; Esri',
        });

        // Default: Street
        this.tileLayers.street.addTo(this.map);
    }

    switchBasemap(type) {
        if (type === this.activeBasemap) return;

        if (type === 'satellite') {
            this.map.removeLayer(this.tileLayers.street);
            this.tileLayers.satellite.addTo(this.map);
            this.tileLayers.satelliteLabels.addTo(this.map);
            this.activeBasemap = 'satellite';
        } else {
            this.map.removeLayer(this.tileLayers.satellite);
            this.map.removeLayer(this.tileLayers.satelliteLabels);
            this.tileLayers.street.addTo(this.map);
            this.activeBasemap = 'street';
        }

        // Update active pill UI (Atlas theme)
        document.querySelectorAll('.btn-basemap').forEach(btn => {
            const svg = btn.querySelector('svg');
            const isActive = btn.dataset.basemap === type;
            btn.dataset.active = isActive ? 'true' : 'false';
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            if (btn.dataset.basemap === type) {
                btn.className = 'btn-basemap bg-[#2457A7] text-white font-semibold text-xs px-3.5 py-1.5 rounded-md transition flex items-center gap-1.5';
                if (svg) {
                    svg.classList.remove('text-[#5A6E7D]');
                    svg.classList.add('text-white');
                }
            } else {
                btn.className = 'btn-basemap bg-white hover:bg-[#F3F6F8] text-[#5A6E7D] font-semibold text-xs px-3.5 py-1.5 rounded-md transition flex items-center gap-1.5 border border-[#D4DEE7]';
                if (svg) {
                    svg.classList.remove('text-white');
                    svg.classList.add('text-[#5A6E7D]');
                }
            }
        });
    }

    initLayers() {
        // Layer Group untuk Poligon Batas Wilayah 9 Kabupaten (Paling bawah agar pin tidak tertutup)
        this.regencyLayerGroup = L.layerGroup().addTo(this.map);

        // Layer Group untuk Poligon Cakupan Kawasan Transmigrasi SKP
        this.skpLayerGroup = L.layerGroup().addTo(this.map);

        // Layer Group untuk Poligon Delineasi Kawasan UPT
        this.polygonLayerGroup = L.layerGroup().addTo(this.map);
        this.highlightPolygonLayer = L.layerGroup().addTo(this.map);

        // Marker Cluster dengan Custom Icon
        this.clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 45,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            iconCreateFunction: (cluster) => {
                const markers = cluster.getAllChildMarkers();
                const count = markers.length;
                const hasCritical = markers.some(m => m.feature?.properties?.issue_status === 'critical');

                let clusterClass = 'marker-cluster-custom';
                if (hasCritical) {
                    clusterClass += ' marker-cluster-has-critical';
                }

                return L.divIcon({
                    html: `<div class="marker-cluster-custom-inner">${count}</div>`,
                    className: clusterClass,
                    iconSize: L.point(42, 42)
                });
            }
        });

        this.map.addLayer(this.clusterGroup);

        // Sinkronisasi status tampilan saklar lapisan pada toolbar
        this.updateLayerButtonState('btn-toggle-regency-boundary', this.showRegencyBoundaries);
        this.updateLayerButtonState('btn-toggle-skp-boundary', this.showSkpBoundaries);
        this.updateLayerButtonState('btn-toggle-polygon', this.showPolygons);
    }

    async loadRegencyBoundaries() {
        try {
            let response = await fetch('/api/regencies/boundaries');
            if (!response.ok) {
                response = await fetch('/data/kalsel_regencies.geojson');
            }
            const data = await response.json();

            if (!data.features) return;

            data.features.forEach(feature => {
                const p = feature.properties;
                const regId = String(p.id);
                const color = p.color || '#8b5cf6';

                const layer = L.geoJSON(feature, {
                    style: {
                        color: color,
                        weight: 2,
                        opacity: 0.85,
                        fillColor: color,
                        fillOpacity: 0.20,
                        dashArray: '4, 4'
                    }
                });

                // Tooltip saat kursor diarahkan ke kabupaten
                layer.bindTooltip(`
                    <div class="font-extrabold text-xs text-white">Kabupaten ${p.name}</div>
                    <div class="text-[10px] text-slate-300">Klik untuk menyaring UPT di wilayah ini</div>
                `, {
                    sticky: true,
                    className: 'regency-boundary-tooltip'
                });

                // Efek hover
                layer.on('mouseover', (e) => {
                    if (this.selectedRegencyId === 'all' || this.selectedRegencyId === regId) {
                        const l = e.target;
                        l.setStyle({
                            weight: 3.5,
                            opacity: 1,
                            fillOpacity: 0.38,
                            dashArray: ''
                        });
                    }
                });

                layer.on('mouseout', () => {
                    this.resetRegencyPolygonStyle(regId);
                });

                // Klik poligon kabupaten untuk langsung filter
                layer.on('click', () => {
                    const sel = document.getElementById('filter-regency');
                    if (sel) {
                        sel.value = regId;
                        this.applyFilters();
                    }
                });

                this.regencyLayers[regId] = layer;
                this.regencyLayerGroup.addLayer(layer);
            });
        } catch (error) {
            console.warn('Gagal memuat batas kabupaten:', error);
        }
    }

    async loadSkpBoundaries() {
        try {
            let response = await fetch('/api/skp-locations');
            if (!response.ok) {
                response = await fetch('/api/skp-locations/cahaya-baru');
            }
            if (!response.ok) {
                response = await fetch('/data/skp_cahaya_baru.geojson');
            }
            const data = await response.json();
            if (!data.features) return;

            this.skpLayers = [];
            this.skpLayerGroup.clearLayers();

            data.features.forEach(feature => {
                const p = feature.properties || {};
                const color = p.color || '#163A63';

                const layer = L.geoJSON(feature, {
                    style: {
                        color: color,
                        weight: 2.2,
                        opacity: 0.9,
                        fillColor: color,
                        fillOpacity: 0.18,
                        dashArray: '4, 4'
                    }
                });

                // Tooltip label saat hover
                layer.bindTooltip(`
                    <div class="px-2 py-1 font-semibold text-xs text-[#0E2A47]">
                        <div class="font-bold flex items-center gap-1.5" style="color: ${color}">
                            <span>●</span> ${p.nama}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">${p.kawasan || 'Kawasan Transmigrasi'} · ${p.luas_formatted} Ha</div>
                    </div>
                `, {
                    sticky: true,
                    direction: 'top',
                    className: 'atlas-tooltip-skp'
                });

                // Interaktivitas hover highlight
                layer.on('mouseover', () => {
                    layer.setStyle({
                        weight: 3.5,
                        fillOpacity: 0.35,
                        dashArray: ''
                    });
                });

                layer.on('mouseout', () => {
                    layer.setStyle({
                        weight: 2.2,
                        fillOpacity: 0.18,
                        dashArray: '4, 4'
                    });
                });

                // Klik poligon membuka popup informatif kedinasan
                layer.bindPopup(this.generateSkpPopupHtml(feature));

                layer.feature = feature;
                this.skpLayers.push(layer);
            });

            // Perbarui visibilitas layer sesuai filter wilayah dan status tombol saat ini
            this.updateSkpLayersVisibility();

            // Jika ada target SKP dari URL (?skp_id=X), pusatkan peta langsung ke poligon SKP tersebut
            if (this.targetSkpId) {
                this.showSkpBoundaries = true;
                this.updateSkpLayersVisibility();
                this.updateLayerButtonState('btn-toggle-skp-boundary', true);

                const targetLayer = this.skpLayers.find(l => {
                    const fid = String(l.feature?.id || l.feature?.properties?.id || '');
                    const fName = String(l.feature?.properties?.nama || '').toLowerCase().trim();
                    const targetStr = String(this.targetSkpId).toLowerCase().trim();
                    return fid === targetStr || fName === targetStr;
                });

                if (targetLayer) {
                    setTimeout(() => {
                        this.map.fitBounds(targetLayer.getBounds(), {
                            padding: [60, 60],
                            maxZoom: 14
                        });
                        targetLayer.openPopup();
                    }, 400);
                }
            }
        } catch (error) {
            console.warn('Gagal memuat batas SKP Cahaya Baru:', error);
        }
    }

    generateSkpPopupHtml(feature) {
        const p = feature.properties || {};
        const color = p.color || '#163A63';
        const bboxStr = p.bbox ? JSON.stringify(p.bbox) : 'null';

        return `
            <div class="overflow-hidden rounded-lg bg-white text-[#243746] shadow-md border border-[#D4DEE7] min-w-[270px]">
                <div class="px-4 py-2.5 border-b border-[#D4DEE7] flex items-center justify-between" style="background-color: #F8FAFC;">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded text-white" style="background-color: ${color};">
                        ${p.nama}
                    </span>
                    <span class="text-[11px] font-medium text-[#5A6E7D]">
                        Kab. ${p.kabupaten}
                    </span>
                </div>
                <div class="p-3.5 space-y-2.5">
                    <div>
                        <h4 class="font-bold text-[#0E2A47] text-sm leading-snug">
                            ${p.nama} · ${p.kawasan}
                        </h4>
                        <p class="text-[11px] text-[#5A6E7D] mt-0.5">
                            ${p.keterangan || 'Satuan Kawasan Pengembangan Transmigrasi'}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 bg-[#F3F6F8] rounded-md p-2 border border-[#D4DEE7] text-xs">
                        <div>
                            <span class="text-[#5A6E7D] block text-[9px] uppercase font-semibold">Estimasi Luas</span>
                            <strong class="text-[#107C41] text-xs tabular-nums">${p.luas_formatted} Ha</strong>
                        </div>
                        <div>
                            <span class="text-[#5A6E7D] block text-[9px] uppercase font-semibold">Rujukan DPMD</span>
                            <span class="font-semibold text-[#243746] text-xs">Edisi 2023</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-[#D4DEE7] text-[10px] text-[#5A6E7D]">
                            Sumber data: ${p.sumber || 'DPMD Batola & BIG'}
                        </div>
                    </div>

                    <button onclick="window.sigapMap.openSkpDetail(${p.id || feature.id ? (p.id || feature.id) : `'${p.nama}'`}, this)"
                        class="w-full bg-[#163A63] hover:bg-[#0E2A47] active:scale-[0.98] text-white font-semibold text-xs py-2 px-3 rounded-md transition flex items-center justify-center gap-1.5 shadow-sm">
                        <span>Buka rincian lengkap</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <div class="flex items-center justify-between pt-1 border-t border-[#D4DEE7] text-[11px]">
                        <span class="text-[#5A6E7D]">Aksi peta:</span>
                        <button type="button" onclick="window.sigapMap.fitToFeatureBounds(${bboxStr})" class="text-[#2457A7] hover:text-[#0E2A47] font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Fokuskan kamera</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    fitToFeatureBounds(bbox) {
        if (!bbox || bbox.length !== 4) return;
        // bbox: [xmin, ymin, xmax, ymax] -> [[ymin, xmin], [ymax, xmax]]
        const bounds = L.latLngBounds([[bbox[1], bbox[0]], [bbox[3], bbox[2]]]);
        this.map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 13
        });
    }

    resetRegencyPolygonStyle(regId) {
        const layer = this.regencyLayers[regId];
        if (!layer) return;

        const isSelected = this.selectedRegencyId === regId;
        const isAll = this.selectedRegencyId === 'all';

        // Ambil warna feature
        const feature = layer.getLayers()[0]?.feature;
        const color = feature?.properties?.color || '#8b5cf6';

        if (isSelected) {
            layer.setStyle({
                color: color,
                weight: 3.5,
                opacity: 1,
                fillColor: color,
                fillOpacity: 0.36,
                dashArray: ''
            });
        } else if (isAll) {
            layer.setStyle({
                color: color,
                weight: 2,
                opacity: 0.85,
                fillColor: color,
                fillOpacity: 0.20,
                dashArray: '4, 4'
            });
        } else {
            // Jika satu kabupaten sedang dipilih, redupkan kabupaten lainnya
            layer.setStyle({
                color: color,
                weight: 1,
                opacity: 0.25,
                fillColor: color,
                fillOpacity: 0.04,
                dashArray: '2, 3'
            });
        }
    }

    async loadInitialData() {
        this.showLoading(true);
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const uptIdParam = urlParams.get('upt_id') || urlParams.get('id');
            const searchParam = urlParams.get('search');
            const regParam = urlParams.get('regency');

            const apiUrl = uptIdParam
                ? `/api/upt-locations?upt_id=${encodeURIComponent(uptIdParam)}`
                : '/api/upt-locations';

            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.features) {
                this.allFeatures = data.features;
                this.filteredFeatures = [...this.allFeatures];

                // Cek apakah ada sasaran UPT tertentu dari parameter URL
                const hasTargetUpt = !!uptIdParam || (!!searchParam && this.allFeatures.some(f => {
                    const p = f.properties;
                    const q = searchParam.toLowerCase().trim();
                    return (p.upt_name && p.upt_name.toLowerCase() === q) ||
                           (p.current_village_name && p.current_village_name.toLowerCase() === q);
                }));

                // Jangan fitBounds seluruh Kalsel jika kita akan langsung fokus/terbang ke UPT tertentu
                this.renderFeatures(this.filteredFeatures, !hasTargetUpt);
                this.updateStatisticsDisplay(data.meta);

                // Auto-filter jika ada query param regency dari URL (hanya jika bukan mengarah ke UPT spesifik)
                if (regParam && !uptIdParam) {
                    const regSel = document.getElementById('filter-regency');
                    if (regSel) {
                        regSel.value = regParam;
                        this.applyFilters();
                    }
                }

                // Navigasi langsung ke titik UPT yang dituju
                if (uptIdParam) {
                    setTimeout(() => {
                        this.focusToUpt(uptIdParam);
                    }, 300);
                } else if (searchParam) {
                    const query = searchParam.toLowerCase().trim();
                    const exactMatch = this.allFeatures.find(f => {
                        const p = f.properties;
                        return (p.upt_name && p.upt_name.toLowerCase() === query) ||
                               (p.current_village_name && p.current_village_name.toLowerCase() === query);
                    });

                    if (exactMatch) {
                        setTimeout(() => {
                            this.focusToUpt(exactMatch.id);
                        }, 300);
                    } else {
                        // Jika bukan nama pasti satu UPT, masukkan ke kotak pencarian & terapkan filter
                        const searchEl = document.getElementById('filter-search');
                        if (searchEl) {
                            searchEl.value = searchParam;
                            this.applyFilters();
                        }
                    }
                }

                // Cek apakah ada sasaran SKP tertentu dari parameter URL (?skp_id=X atau ?skp=X)
                const skpIdParam = urlParams.get('skp_id') || urlParams.get('skp');
                if (skpIdParam) {
                    this.targetSkpId = skpIdParam;
                    this.showSkpBoundaries = true;
                    setTimeout(() => {
                        this.toggleSkpBoundaries(true);
                        this.openSkpDetail(skpIdParam, null, true);
                    }, 400);
                }
            }
        } catch (error) {
            console.error('Gagal memuat data WebGIS:', error);
        } finally {
            this.showLoading(false);
        }
    }

    renderFeatures(features, shouldFitBounds = true) {
        this.clusterGroup.clearLayers();
        this.polygonLayerGroup.clearLayers();
        this.markers = {};

        const bounds = [];

        features.forEach(feature => {
            if (!feature.geometry || !feature.geometry.coordinates) return;
            const coords = feature.geometry.coordinates; // [lng, lat]
            if (typeof coords[0] !== 'number' || typeof coords[1] !== 'number') return;
            if (coords[0] === 0 && coords[1] === 0) return;
            // Hanya koordinat yang valid dalam cakupan wilayah Indonesia/Kalimantan
            if (coords[1] < -12 || coords[1] > 8 || coords[0] < 95 || coords[0] > 142) return;

            const latLng = [coords[1], coords[0]];
            bounds.push(latLng);

            const props = feature.properties;
            const marker = this.createMarker(feature, latLng);
            this.clusterGroup.addLayer(marker);

            // Jika poligon delineasi UPT tersedia dan opsi aktif
            if (this.showPolygons && props.polygon_geojson) {
                const polygon = this.createPolygon(feature);
                this.polygonLayerGroup.addLayer(polygon);
            }
        });

        // Otomatis sesuaikan zoom jika ada data terfilter dan diizinkan fitBounds
        if (shouldFitBounds && bounds.length > 0) {
            this.map.fitBounds(L.latLngBounds(bounds), {
                padding: [60, 60],
                maxZoom: 12
            });
        }
    }

    createMarker(feature, latLng) {
        const props = feature.properties;
        const status = props.issue_status || 'clean';

        // Custom DivIcon Traffic Light (Clean Pin Design tanpa mystery number)
        const icon = L.divIcon({
            className: `custom-pin pin-${status}`,
            html: `
                <div class="pin-marker-core">
                    <div class="pin-marker-dot"></div>
                </div>
            `,
            iconSize: [26, 26],
            iconAnchor: [13, 26],
            popupAnchor: [0, -26]
        });

        const marker = L.marker(latLng, { icon: icon });
        marker.feature = feature;
        this.markers[feature.id] = marker;

        // Tooltip Instan saat Hover (Don't Make Me Think)
        const villageText = props.current_village_name ? ` • Desa ${props.current_village_name}` : '';
        marker.bindTooltip(`
            <div class="gis-pin-tooltip">
                <span class="gis-pin-tooltip-title">UPT ${props.upt_name}</span>
                <span class="gis-pin-tooltip-sub">Kab. ${props.regency_name || '-'}${villageText}</span>
            </div>
        `, {
            direction: 'top',
            offset: [0, -26],
            opacity: 0.98,
            className: 'atlas-pin-tooltip-wrapper'
        });

        // Popup Content saat Diklik
        const popupContent = this.generatePopupHtml(feature);
        marker.bindPopup(popupContent, {
            maxWidth: 340,
            className: 'custom-leaflet-popup'
        });

        return marker;
    }

    createPolygon(feature) {
        const props = feature.properties;
        const status = props.issue_status || 'clean';

        let color = '#10b981'; // Green
        if (status === 'warning') color = '#f59e0b'; // Amber
        if (status === 'critical') color = '#ef4444'; // Red

        const polygon = L.geoJSON(props.polygon_geojson, {
            style: {
                color: color,
                weight: 1.5,
                opacity: 0.8,
                fillColor: color,
                fillOpacity: 0.15,
                dashArray: '3, 4'
            }
        });

        if (props.current_village_name || props.upt_name) {
            const vName = props.current_village_name ? `Desa ${props.current_village_name}` : `UPT ${props.upt_name}`;
            polygon.bindTooltip(`
                <div class="px-2 py-1 font-semibold text-xs text-[#0E2A47]">
                    <div class="font-bold flex items-center gap-1.5" style="color: ${color}">
                        <span>●</span> ${vName}
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Batas Desa UPT ${props.upt_name}</div>
                </div>
            `, {
                sticky: true,
                direction: 'top',
                className: 'atlas-tooltip-skp'
            });
        }

        polygon.on('click', () => {
            this.openDetail(feature.id);
        });

        return polygon;
    }

    generatePopupHtml(feature) {
        const p = feature.properties;
        const numFormatted = String(p.upt_number).padStart(3, '0');

        let statusBadge = '';
        if (p.issue_status === 'clean') {
            statusBadge = `<span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Clean &amp; Clear</span>`;
        } else if (p.issue_status === 'warning') {
            statusBadge = `<span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Waspada / Monitoring</span>`;
        } else {
            statusBadge = `<span class="atlas-status atlas-status--critical"><span aria-hidden="true"></span>Kritis / Prioritas Mediasi</span>`;
        }

        return `
            <div class="overflow-hidden rounded-lg bg-white text-[#243746] shadow-md border border-[#D4DEE7]">
                <div class="bg-[#EDF2F6] px-4 py-3 flex items-center justify-between border-b border-[#D4DEE7]">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-[#194482] bg-[#DAE7F6] px-2.5 py-0.5 rounded tabular-nums">
                        UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-medium text-[#5A6E7D]">
                        Kab. ${p.regency_name}
                    </span>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <h4 class="font-semibold text-[#243746] text-base leading-tight">
                            ${p.upt_name}
                        </h4>
                        <p class="text-xs text-[#5A6E7D] mt-0.5 flex items-center gap-1">
                            <span>Desa definitif:</span>
                            <strong class="text-[#243746]">${p.current_village_name}</strong>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 bg-[#F3F6F8] rounded-md p-2.5 border border-[#D4DEE7] text-xs">
                        <div>
                            <span class="text-[#5A6E7D] block text-[9px] uppercase font-semibold">Pola usaha</span>
                            <span class="font-semibold text-[#243746]">${p.business_pattern}</span>
                        </div>
                        <div>
                            <span class="text-[#5A6E7D] block text-[9px] uppercase font-semibold">Penempatan</span>
                            <span class="font-semibold text-[#243746] tabular-nums">${p.placement_year} (${p.placement_kk.toLocaleString()} KK)</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-[#D4DEE7] flex items-center justify-between text-[11px]">
                            <span class="text-[#5A6E7D]">Serah terima:</span>
                            <strong class="text-[#243746] tabular-nums">${p.handover_year || '-'} (${p.handover_kk.toLocaleString()} KK)</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-[#5A6E7D]">Status lahan:</span>
                        ${statusBadge}
                    </div>

                    <button onclick="window.sigapMap.openDetail(${feature.id}, this)"
                        class="w-full bg-[#2457A7] hover:bg-[#194482] active:scale-[0.98] text-white font-semibold text-xs py-2 px-3 rounded-md transition flex items-center justify-center gap-1.5 shadow-sm">
                        <span>Buka riwayat lengkap</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        `;
    }

    applyFilters() {
        const regencyId = document.getElementById('filter-regency')?.value || 'all';
        const status = document.getElementById('filter-status')?.value || 'all';
        const decade = document.getElementById('filter-decade')?.value || 'all';
        const pattern = document.getElementById('filter-pattern')?.value || 'all';
        const search = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();

        // Update selected regency id untuk highlight poligon kabupaten
        this.selectedRegencyId = regencyId;
        Object.keys(this.regencyLayers).forEach(id => {
            this.resetRegencyPolygonStyle(id);
        });

        // Filter UPT
        this.filteredFeatures = this.allFeatures.filter(feature => {
            const p = feature.properties;

            // Filter Kabupaten
            if (regencyId !== 'all') {
                const regMap = {
                    '1': 'Tapin', '2': 'Hulu Sungai Utara', '3': 'Balangan', '4': 'Tabalong',
                    '5': 'Tanah Laut', '6': 'Barito Kuala', '7': 'Kota Baru', '8': 'Tanah Bumbu', '9': 'Banjar'
                };
                const matchesId = p.regency_id && String(p.regency_id) === String(regencyId);
                const matchesName = regMap[regencyId] && p.regency_name === regMap[regencyId];
                if (!matchesId && !matchesName) return false;
            }

            // Filter Status
            if (status !== 'all' && p.issue_status !== status) {
                return false;
            }

            // Filter Pola Usaha
            if (pattern !== 'all' && p.business_pattern !== pattern) {
                return false;
            }

            // Filter Dekade
            if (decade !== 'all') {
                const decNum = parseInt(decade, 10);
                const maxDec = decNum + 9;
                let matchDec = false;
                for (let yr = decNum; yr <= maxDec; yr++) {
                    if (String(p.placement_year).includes(String(yr))) {
                        matchDec = true;
                        break;
                    }
                }
                if (!matchDec) return false;
            }

            // Filter Pencarian Teks
            if (search.length > 0) {
                const nameMatch = (p.upt_name || '').toLowerCase().includes(search);
                const villageMatch = (p.current_village_name || '').toLowerCase().includes(search);
                const regencyMatch = (p.regency_name || '').toLowerCase().includes(search);
                if (!nameMatch && !villageMatch && !regencyMatch) return false;
            }

            return true;
        });

        // Jangan fitBounds ganda: renderFeatures hanya me-render pin dan poligon desa
        this.renderFeatures(this.filteredFeatures, false);
        this.updateDynamicMetrics(this.filteredFeatures);

        // Zoom kamera sesuai filter kabupaten (jika bukan sedang menargetkan SKP atau UPT spesifik)
        const urlParams = new URLSearchParams(window.location.search);
        const hasSpecificTarget = this.targetSkpId || urlParams.get('upt_id') || urlParams.get('id');

        if (!hasSpecificTarget && regencyId !== 'all' && this.regencyLayers[regencyId]) {
            this.map.fitBounds(this.regencyLayers[regencyId].getBounds(), {
                padding: [50, 50],
                maxZoom: 12
            });
        } else if (!hasSpecificTarget && regencyId === 'all' && search.length === 0) {
            // Jika reset atau kembali ke semua wilayah dan tidak sedang cari teks, pusatkan ke Kalsel
            this.map.flyTo(this.defaultCenter, this.defaultZoom, { duration: 0.8 });
        }

        // Sinkronisasi status lapisan Kawasan SKP saat wilayah berubah:
        if (this.targetSkpId) {
            this.showSkpBoundaries = true;
            this.updateSkpLayersVisibility();
            this.updateLayerButtonState('btn-toggle-skp-boundary', true);
        } else {
            const hasSkpInRegency = regencyId === 'all' ||
                this.skpLayers.some(layer => String(layer.feature?.properties?.regency_id) === String(regencyId));
            if (hasSkpInRegency && regencyId !== 'all') {
                this.showSkpBoundaries = true;
            } else if (regencyId !== 'all') {
                this.showSkpBoundaries = false;
            }
            this.updateSkpLayersVisibility();
            this.updateLayerButtonState('btn-toggle-skp-boundary', this.showSkpBoundaries);
        }
    }

    resetFilters() {
        if (document.getElementById('filter-regency')) document.getElementById('filter-regency').value = 'all';
        if (document.getElementById('filter-status')) document.getElementById('filter-status').value = 'all';
        if (document.getElementById('filter-decade')) document.getElementById('filter-decade').value = 'all';
        if (document.getElementById('filter-pattern')) document.getElementById('filter-pattern').value = 'all';
        if (document.getElementById('filter-search')) document.getElementById('filter-search').value = '';

        // Reset status filter chips UI (kembali ke chip 'Semua')
        document.querySelectorAll('.gis-status-chip').forEach(chip => {
            chip.classList.toggle('is-active', chip.dataset.status === 'all');
        });

        this.selectedRegencyId = 'all';
        Object.keys(this.regencyLayers).forEach(id => {
            this.resetRegencyPolygonStyle(id);
        });

        if (this.highlightPolygonLayer) {
            this.highlightPolygonLayer.clearLayers();
        }

        // Matikan lapisan SKP saat reset semua filter
        if (this.showSkpBoundaries) {
            this.toggleSkpBoundaries(false);
        } else {
            this.updateSkpLayersVisibility();
        }

        this.filteredFeatures = [...this.allFeatures];
        this.renderFeatures(this.filteredFeatures, false);
        this.updateDynamicMetrics(this.filteredFeatures);

        // Reset view ke Kalsel
        this.map.flyTo(this.defaultCenter, this.defaultZoom, { duration: 1 });
    }

    updateLayerButtonState(btnId, isActive) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.classList.toggle('is-active', !!isActive);
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        const badge = btn.querySelector('span:last-child');
        if (badge) {
            badge.textContent = isActive ? 'AKTIF' : 'NONAKTIF';
            badge.className = isActive ? 'text-[10px] text-emerald-600 font-bold' : 'text-[10px] text-slate-400 font-semibold';
        }
    }

    togglePolygons(forceState = null) {
        this.showPolygons = forceState !== null ? forceState : !this.showPolygons;
        if (this.showPolygons) {
            this.polygonLayerGroup.addTo(this.map);
            this.renderFeatures(this.filteredFeatures, false);
        } else {
            this.polygonLayerGroup.clearLayers();
        }
        this.updateLayerButtonState('btn-toggle-polygon', this.showPolygons);
    }

    toggleRegencyBoundaries(forceState = null) {
        this.showRegencyBoundaries = forceState !== null ? forceState : !this.showRegencyBoundaries;
        if (this.showRegencyBoundaries) {
            this.regencyLayerGroup.addTo(this.map);
        } else {
            this.map.removeLayer(this.regencyLayerGroup);
        }
        this.updateLayerButtonState('btn-toggle-regency-boundary', this.showRegencyBoundaries);
    }

    updateSkpLayersVisibility() {
        this.skpLayerGroup.clearLayers();
        if (!this.showSkpBoundaries) return;

        this.skpLayers.forEach(layer => {
            const featureRegId = layer.feature?.properties?.regency_id;
            const featureId = layer.feature?.id || layer.feature?.properties?.id;
            // Tampilkan poligon SKP jika filter 'all' atau cocok dengan kabupaten poligon tersebut, ATAU jika ini target SKP yang dipilih
            if (this.selectedRegencyId === 'all' || String(featureRegId) === String(this.selectedRegencyId) || (this.targetSkpId && String(featureId) === String(this.targetSkpId))) {
                this.skpLayerGroup.addLayer(layer);
            }
        });
    }

    toggleSkpBoundaries(forceState = null) {
        this.showSkpBoundaries = forceState !== null ? forceState : !this.showSkpBoundaries;

        // Cek apakah ada data SKP untuk wilayah yang sedang dipilih (atau jika menargetkan SKP tertentu)
        const hasMatchingSkp = this.targetSkpId || this.selectedRegencyId === 'all' ||
            this.skpLayers.some(layer => String(layer.feature?.properties?.regency_id) === String(this.selectedRegencyId));

        if (this.showSkpBoundaries && !hasMatchingSkp) {
            // Jika wilayah yang dipilih tidak memiliki data SKP, jangan aktifkan tombol
            this.showSkpBoundaries = false;
            this.updateLayerButtonState('btn-toggle-skp-boundary', false);
            this.skpLayerGroup.clearLayers();
            return;
        }

        this.updateSkpLayersVisibility();
        this.updateLayerButtonState('btn-toggle-skp-boundary', this.showSkpBoundaries);
    }

    initNavigationToggle() {
        const savedState = localStorage.getItem('sigap-webgis-nav-visible');
        if (savedState === 'false') {
            this.toggleNavigationUI(false);
        }
    }

    toggleNavigationUI(forceState = null) {
        if (forceState !== null) {
            this.isNavigationVisible = forceState;
        } else {
            this.isNavigationVisible = !this.isNavigationVisible;
        }

        const unifiedIsland = document.getElementById('unified-gis-island');
        const btnToggle = document.getElementById('btn-toggle-navigation');
        const eyeOpen = document.getElementById('icon-nav-eye-open');
        const eyeClosed = document.getElementById('icon-nav-eye-closed');

        if (this.isNavigationVisible) {
            if (unifiedIsland) unifiedIsland.classList.remove('is-hidden');
            if (btnToggle) {
                btnToggle.classList.remove('is-collapsed');
                btnToggle.setAttribute('aria-expanded', 'true');
                btnToggle.title = 'Sembunyikan panel kontrol dan filter';
            }
            if (eyeOpen) eyeOpen.classList.remove('hidden');
            if (eyeClosed) eyeClosed.classList.add('hidden');
        } else {
            if (unifiedIsland) unifiedIsland.classList.add('is-hidden');
            if (btnToggle) {
                btnToggle.classList.add('is-collapsed');
                btnToggle.setAttribute('aria-expanded', 'false');
                btnToggle.title = 'Tampilkan panel kontrol dan filter';
            }
            if (eyeOpen) eyeOpen.classList.add('hidden');
            if (eyeClosed) eyeClosed.classList.remove('hidden');
        }

        try {
            localStorage.setItem('sigap-webgis-nav-visible', String(this.isNavigationVisible));
        } catch (e) {
            // LocalStorage might be unavailable
        }

        setTimeout(() => {
            if (this.map) {
                this.map.invalidateSize();
            }
        }, 360);
    }

    updateStatisticsDisplay(meta) {
        if (!meta) return;

        const totalEl = document.getElementById('stat-total-upt');
        const placementKkEl = document.getElementById('stat-placement-kk');
        const handoverKkEl = document.getElementById('stat-handover-kk');
        const filteredCountEl = document.getElementById('filter-count-badge');

        const chipAll = document.getElementById('stat-chip-all');
        const chipClean = document.getElementById('stat-chip-clean');
        const chipWarning = document.getElementById('stat-chip-warning');
        const chipCritical = document.getElementById('stat-chip-critical');

        const total = meta.total_features ?? 0;
        const clean = meta.status_counts?.clean ?? 0;
        const warning = meta.status_counts?.warning ?? 0;
        const critical = meta.status_counts?.critical ?? 0;

        if (totalEl) totalEl.innerText = total.toLocaleString('id-ID');
        if (placementKkEl) placementKkEl.innerText = (meta.total_kk_placement || 0).toLocaleString('id-ID');
        if (handoverKkEl) handoverKkEl.innerText = (meta.total_kk_handover || 0).toLocaleString('id-ID');

        if (filteredCountEl) filteredCountEl.innerText = `${total} UPT Aktif`;

        if (chipAll) chipAll.innerText = total;
        if (chipClean) chipClean.innerText = clean;
        if (chipWarning) chipWarning.innerText = warning;
        if (chipCritical) chipCritical.innerText = critical;
    }

    updateDynamicMetrics(features) {
        const total = features.length;
        const totalPlacementKk = features.reduce((sum, f) => sum + (f.properties.placement_kk || 0), 0);
        const totalHandoverKk = features.reduce((sum, f) => sum + (f.properties.handover_kk || 0), 0);
        const cleanCount = features.filter(f => f.properties.issue_status === 'clean').length;
        const warningCount = features.filter(f => f.properties.issue_status === 'warning').length;
        const criticalCount = features.filter(f => f.properties.issue_status === 'critical').length;

        this.updateStatisticsDisplay({
            total_features: total,
            total_kk_placement: totalPlacementKk,
            total_kk_handover: totalHandoverKk,
            status_counts: {
                clean: cleanCount,
                warning: warningCount,
                critical: criticalCount
            }
        });
    }

    async openDetail(id, btnEl = null) {
        const now = Date.now();
        // Debounce: abaikan klik ganda jika rentang < 400ms atau request sedang berlangsung
        if (this.isUptLoading || (now - this.lastUptClickTime < 400)) {
            return;
        }
        this.lastUptClickTime = now;
        this.isUptLoading = true;

        if (btnEl && btnEl.classList) {
            btnEl.classList.add('opacity-75', 'pointer-events-none');
        }

        const drawer = document.getElementById('upt-detail-drawer');
        const content = document.getElementById('upt-detail-content');
        if (!drawer || !content) {
            this.isUptLoading = false;
            return;
        }

        // Buka drawer lebih dulu dengan skeleton loading terstruktur
        drawer.classList.remove('translate-x-full');
        content.innerHTML = this.getUptSkeletonHtml();

        try {
            const res = await fetch(`/api/upt-locations/${id}`);
            const json = await res.json();
            if (json.success && json.data) {
                this.renderDetailContent(json.data);
            }
        } catch (err) {
            content.innerHTML = `
                <div class="p-8 text-center text-rose-500">
                    <p class="font-bold">Gagal memuat detail data.</p>
                </div>
            `;
        } finally {
            if (btnEl && btnEl.classList) {
                btnEl.classList.remove('opacity-75', 'pointer-events-none');
            }
            setTimeout(() => {
                this.isUptLoading = false;
            }, 300);
        }
    }

    getUptSkeletonHtml() {
        return `
            <div class="animate-pulse flex flex-col h-full">
                <!-- Header Skeleton -->
                <div class="p-6 border-b border-[#D4DEE7] bg-white space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="h-5 w-28 bg-[#CBD8E6] rounded"></div>
                        <div class="h-4 w-32 bg-[#E2E8F0] rounded"></div>
                    </div>
                    <div class="h-7 w-3/4 bg-[#CBD8E6] rounded"></div>
                    <div class="h-4 w-1/2 bg-[#E2E8F0] rounded"></div>
                </div>

                <!-- Body Skeleton -->
                <div class="p-6 space-y-6 flex-1">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                            <div class="h-2.5 w-16 bg-[#CBD8E6] rounded"></div>
                            <div class="h-6 w-20 bg-[#CBD8E6] rounded"></div>
                        </div>
                        <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                            <div class="h-2.5 w-16 bg-[#CBD8E6] rounded"></div>
                            <div class="h-6 w-20 bg-[#CBD8E6] rounded"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-[#CBD8E6] space-y-3 shadow-xs">
                        <div class="h-4 w-36 bg-[#CBD8E6] rounded"></div>
                        <div class="h-3 w-full bg-[#E2E8F0] rounded"></div>
                        <div class="h-3 w-4/5 bg-[#E2E8F0] rounded"></div>
                    </div>
                </div>
            </div>
        `;
    }

    renderDetailContent(data) {
        const content = document.getElementById('upt-detail-content');
        if (!content) return;

        const numFormatted = String(data.upt_number).padStart(3, '0');

        let statusBadge = '';
        if (data.issue_status === 'clean') {
            statusBadge = `<span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Clean &amp; Clear</span>`;
        } else if (data.issue_status === 'warning') {
            statusBadge = `<span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Waspada / Monitoring</span>`;
        } else {
            statusBadge = `<span class="atlas-status atlas-status--critical"><span aria-hidden="true"></span>Kritis / Prioritas Mediasi</span>`;
        }

        content.innerHTML = `
            <!-- Header identitas UPT (berpasangan, gaya Atlas) -->
            <div class="p-6 border-b border-[#D4DEE7] bg-white">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-semibold tracking-wider uppercase text-[#194482] bg-[#DAE7F6] border border-[#BDCFE4] px-2.5 py-0.5 rounded tabular-nums">
                        Registrasi UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-medium text-[#5A6E7D]">
                        Kabupaten ${data.regency_name}
                    </span>
                </div>
                <h2 class="text-2xl font-semibold text-[#243746] leading-tight mb-1" style="font-family: 'Barlow Semi Condensed', sans-serif;">
                    ${data.upt_name}
                </h2>
                <div class="flex items-center gap-1.5 text-sm text-[#5A6E7D]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Zm-5 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path></svg>
                    <span>Kini: <strong class="text-[#243746]">${data.current_village_name}</strong></span>
                </div>
            </div>

            <div class="p-5 space-y-4 bg-[#F3F6F8]">
                <!-- Status Legalitas Lahan -->
                <div class="bg-white rounded-lg p-4 border border-[#D4DEE7]">
                    <span class="text-[10px] font-semibold text-[#5A6E7D] uppercase tracking-wider block mb-2">Status agraria &amp; pertanahan</span>
                    <div class="mb-3">${statusBadge}</div>

                    <div class="bg-[#F3F6F8] rounded-md p-3 border border-[#D4DEE7] text-xs text-[#243746]">
                        <strong class="text-[#243746] block mb-1">Catatan perkembangan lapangan:</strong>
                        ${data.issue_note || 'Tidak ada catatan permasalahan khusus. Lokasi beroperasi normal.'}
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-[#5A6E7D] pt-2 border-t border-[#D4DEE7]">
                        <span>Sertifikasi tanah:</span>
                        <strong class="text-[#243746] font-semibold">${data.shm_status || 'Belum tercatat'}</strong>
                    </div>
                </div>

                <!-- Rekam jejak demografi -->
                <div class="bg-white rounded-lg p-4 border border-[#D4DEE7]">
                    <span class="text-[10px] font-semibold text-[#5A6E7D] uppercase tracking-wider block mb-3">Rekam jejak demografi</span>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#F3F6F8] rounded-md p-3 border border-[#D4DEE7]">
                            <span class="text-[10px] font-semibold text-[#243746] uppercase block mb-1">1. Penempatan awal</span>
                            <div class="text-lg font-semibold text-[#243746] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">${data.placement_kk.toLocaleString()} <span class="text-xs font-normal text-[#5A6E7D]">KK</span></div>
                            <div class="text-xs text-[#5A6E7D]">${data.placement_population.toLocaleString()} jiwa</div>
                            <div class="mt-2 text-[10px] text-[#5A6E7D] font-semibold bg-[#E7EEF3] px-2 py-0.5 rounded inline-block tabular-nums">
                                Tahun ${data.placement_year}
                            </div>
                        </div>

                        <div class="bg-[#F3F6F8] rounded-md p-3 border border-[#D4DEE7]">
                            <span class="text-[10px] font-semibold text-[#243746] uppercase block mb-1">2. Serah terima pemda</span>
                            <div class="text-lg font-semibold text-[#243746] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">${data.handover_kk.toLocaleString()} <span class="text-xs font-normal text-[#5A6E7D]">KK</span></div>
                            <div class="text-xs text-[#5A6E7D]">${data.handover_population.toLocaleString()} jiwa</div>
                            <div class="mt-2 text-[10px] text-[#5A6E7D] font-semibold bg-[#E7EEF3] px-2 py-0.5 rounded inline-block tabular-nums">
                                ${data.handover_year || 'BAST tersedia'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data teknis & pertanian -->
                <div class="bg-white rounded-lg p-4 border border-[#D4DEE7] space-y-2.5 text-xs text-[#243746]">
                    <span class="text-[10px] font-semibold text-[#5A6E7D] uppercase tracking-wider block mb-2">Pola budidaya &amp; pertanian</span>
                    <div class="flex items-center justify-between py-1 border-b border-[#D4DEE7]">
                        <span class="text-[#5A6E7D]">Pola usaha:</span>
                        <span class="font-semibold text-[#243746] bg-[#F3F6F8] px-2.5 py-0.5 rounded border border-[#D4DEE7]">${data.business_pattern}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#D4DEE7]">
                        <span class="text-[#5A6E7D]">Titik centroid (WGS84):</span>
                        <span class="text-[#243746] font-semibold tabular-nums">${data.latitude.toFixed(5)}, ${data.longitude.toFixed(5)}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-[#5A6E7D]">Delineasi spasial PostGIS:</span>
                        <span class="font-semibold text-[#287451] flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#287451]"></span>
                            Poligon aktif (EPSG:4326)
                        </span>
                    </div>
                </div>

                <!-- Repositori e-arsip dokumen BAST -->
                ${(() => {
                    const isAuth = Boolean(window.sigapConfig?.isAuthenticated || data.is_authenticated);
                    const docs = data.documents || [];

                    if (!isAuth) {
                        return `
                            <div class="bg-white rounded-lg p-4 border border-[#D4DEE7]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-semibold text-[#5A6E7D] uppercase tracking-wider">E-arsip BAST &amp; dokumen SK</span>
                                    <span class="text-xs font-semibold text-[#243746] bg-[#E7EEF3] px-2 py-0.5 rounded tabular-nums">${data.documents_count} dokumen</span>
                                </div>

                                <div class="p-3 bg-[#FAF3E4] border border-[#E7D9B4] rounded-md text-xs text-[#243746] mb-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-[#97620B] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <div>
                                            <strong class="block font-semibold text-[#97620B]">Akses dokumen fisik terproteksi:</strong>
                                            Berkas scan BAST fisik dan telaah hukum agraria memerlukan autentikasi aparatur dinas untuk verifikasi alas hak.
                                        </div>
                                    </div>
                                </div>

                                <a href="${window.sigapConfig?.loginUrl || '/login'}" class="w-full bg-[#2457A7] hover:bg-[#194482] text-white font-semibold text-xs py-2.5 px-3 rounded-md text-center block transition shadow-xs">
                                    Masuk akun kedinasan untuk unduh dokumen →
                                </a>
                            </div>
                        `;
                    }

                    const repoUrl = `${window.sigapConfig?.documentsRepoUrl || '/admin/documents'}?search=${encodeURIComponent(data.upt_name)}`;
                    let docsItemsHtml = '';

                    if (docs.length > 0) {
                        const previewDocs = docs.slice(0, 2);
                        docsItemsHtml = previewDocs.map(doc => {
                            const downloadUrl = doc.download_url || `${window.sigapConfig?.downloadDocBaseUrl || '/admin/documents'}/${doc.id}/download`;
                            const sizeText = doc.file_size_kb ? `${Math.round(doc.file_size_kb)} KB` : '';
                            return `
                                <div class="flex items-center justify-between p-2.5 bg-[#F8FAFC] border border-[#D4DEE7] rounded-md text-xs mb-2 hover:bg-[#F3F6F8] transition">
                                    <div class="flex items-center gap-2.5 overflow-hidden mr-2">
                                        <svg class="w-4 h-4 text-[#C42B1C] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <div class="truncate">
                                            <strong class="block text-[#0E2A47] truncate font-semibold" title="${doc.file_name}">${doc.file_name}</strong>
                                            <span class="text-[10px] text-[#5A6E7D]">${doc.document_type || 'Berkas BAST'}${sizeText ? ' · ' + sizeText : ''}</span>
                                        </div>
                                    </div>
                                    <a href="${downloadUrl}" class="shrink-0 inline-flex items-center gap-1 bg-[#107C41] hover:bg-[#0D6334] text-white text-[11px] font-semibold px-2.5 py-1 rounded transition shadow-xs" title="Unduh Berkas">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh</span>
                                    </a>
                                </div>
                            `;
                        }).join('');
                    } else {
                        docsItemsHtml = `
                            <div class="p-3 bg-[#F8FAFC] border border-[#D4DEE7] rounded-md text-xs text-[#5A6E7D] text-center mb-2">
                                Belum ada arsip digital yang tersimpan untuk UPT ini.
                            </div>
                        `;
                    }

                    return `
                        <div class="bg-white rounded-lg p-4 border border-[#D4DEE7]">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-semibold text-[#5A6E7D] uppercase tracking-wider">E-arsip BAST &amp; dokumen SK</span>
                                <span class="text-xs font-semibold text-[#243746] bg-[#E7EEF3] px-2 py-0.5 rounded tabular-nums">${data.documents_count} dokumen</span>
                            </div>

                            ${docsItemsHtml}

                            <a href="${repoUrl}" class="w-full bg-[#2457A7] hover:bg-[#194482] text-white font-semibold text-xs py-2.5 px-3 rounded-md text-center flex items-center justify-center gap-1.5 transition shadow-xs mt-2">
                                <span>Buka Repositori Arsip Dokumen Lengkap</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    `;
                })()}

                <!-- Tombol aksi fokus peta -->
                <button onclick="window.sigapMap.focusOnMap(${data.latitude}, ${data.longitude}, ${data.id})"
                    class="w-full bg-white hover:bg-[#F3F6F8] text-[#2457A7] border border-[#2457A7] font-semibold text-sm py-3 px-4 rounded-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Fokuskan ke posisi peta</span>
                </button>
            </div>
        `;
    }

    closeDetail() {
        const drawer = document.getElementById('upt-detail-drawer');
        if (drawer) {
            drawer.classList.add('translate-x-full');
        }
    }

    async openSkpDetail(id, btnEl = null, autoFocus = false) {
        const now = Date.now();
        // Debounce: abaikan klik ganda jika rentang < 400ms atau request sedang berlangsung
        if (this.isSkpLoading || (now - this.lastSkpClickTime < 400)) {
            return;
        }
        this.lastSkpClickTime = now;
        this.isSkpLoading = true;

        if (btnEl && btnEl.classList) {
            btnEl.classList.add('opacity-75', 'pointer-events-none');
        }

        const drawer = document.getElementById('upt-detail-drawer');
        const content = document.getElementById('upt-detail-content');
        if (!drawer || !content) {
            this.isSkpLoading = false;
            return;
        }

        // Buka drawer dengan layout Skeleton Loading realistis
        drawer.classList.remove('translate-x-full');
        content.innerHTML = this.getSkpSkeletonHtml();

        try {
            const res = await fetch(`/api/skp-locations/${encodeURIComponent(id)}`);
            const json = await res.json();
            if (json.success && json.data) {
                this.renderSkpDetailContent(json.data);

                // Jika autoFocus aktif, pusatkan peta ke poligon atau centroid SKP
                if (autoFocus) {
                    const targetLayer = this.skpLayers.find(l => {
                        const fid = String(l.feature?.id || l.feature?.properties?.id || '');
                        const fName = String(l.feature?.properties?.nama || '').toLowerCase().trim();
                        const targetStr = String(id).toLowerCase().trim();
                        return fid === targetStr || fName === targetStr;
                    });

                    if (targetLayer) {
                        this.map.fitBounds(targetLayer.getBounds(), {
                            padding: [60, 60],
                            maxZoom: 14
                        });
                        targetLayer.openPopup();
                    } else if (json.data.bbox) {
                        this.fitToFeatureBounds(json.data.bbox);
                    } else if (json.data.centroid_lat && json.data.centroid_lng) {
                        this.map.flyTo([json.data.centroid_lat, json.data.centroid_lng], 13, { duration: 1 });
                    }
                }
            } else {
                content.innerHTML = `
                    <div class="p-8 text-center text-rose-500">
                        <p class="font-bold">${json.message || 'Data Kawasan SKP tidak ditemukan.'}</p>
                        <button onclick="window.sigapMap.closeDetail()" class="mt-4 px-4 py-1.5 bg-slate-200 text-slate-700 text-xs rounded font-medium hover:bg-slate-300">Tutup</button>
                    </div>
                `;
            }
        } catch (err) {
            content.innerHTML = `
                <div class="p-8 text-center text-rose-500">
                    <p class="font-bold">Gagal memuat rincian data SKP.</p>
                    <button onclick="window.sigapMap.closeDetail()" class="mt-4 px-4 py-1.5 bg-slate-200 text-slate-700 text-xs rounded font-medium hover:bg-slate-300">Tutup</button>
                </div>
            `;
        } finally {
            if (btnEl && btnEl.classList) {
                btnEl.classList.remove('opacity-75', 'pointer-events-none');
            }
            setTimeout(() => {
                this.isSkpLoading = false;
            }, 300);
        }
    }

    getSkpSkeletonHtml() {
        return `
            <div class="animate-pulse flex flex-col h-full">
                <!-- Header Skeleton -->
                <div class="p-6 border-b border-[#D4DEE7] bg-white space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="h-5 w-24 bg-[#CBD8E6] rounded"></div>
                        <div class="h-4 w-32 bg-[#E2E8F0] rounded"></div>
                    </div>
                    <div class="h-7 w-4/5 bg-[#CBD8E6] rounded"></div>
                    <div class="h-4 w-3/5 bg-[#E2E8F0] rounded"></div>
                    <div class="flex items-center gap-2 pt-2">
                        <div class="h-8 flex-1 bg-[#CBD8E6] rounded-md"></div>
                        <div class="h-8 w-16 bg-[#E2E8F0] rounded-md"></div>
                    </div>
                </div>

                <!-- Body Skeleton -->
                <div class="p-6 space-y-6 flex-1">
                    <!-- Metrics Grid Skeleton -->
                    <div>
                        <div class="h-3.5 w-44 bg-[#CBD8E6] rounded mb-3"></div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                                <div class="h-2.5 w-16 bg-[#CBD8E6] rounded"></div>
                                <div class="h-6 w-24 bg-[#CBD8E6] rounded"></div>
                                <div class="h-2 w-28 bg-[#E2E8F0] rounded"></div>
                            </div>
                            <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                                <div class="h-2.5 w-16 bg-[#CBD8E6] rounded"></div>
                                <div class="h-6 w-16 bg-[#CBD8E6] rounded"></div>
                                <div class="h-2 w-24 bg-[#E2E8F0] rounded"></div>
                            </div>
                            <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                                <div class="h-2.5 w-20 bg-[#CBD8E6] rounded"></div>
                                <div class="h-6 w-28 bg-[#CBD8E6] rounded"></div>
                                <div class="h-2 w-32 bg-[#E2E8F0] rounded"></div>
                            </div>
                            <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] space-y-2 shadow-xs">
                                <div class="h-2.5 w-20 bg-[#CBD8E6] rounded"></div>
                                <div class="h-6 w-20 bg-[#CBD8E6] rounded"></div>
                                <div class="h-2 w-28 bg-[#E2E8F0] rounded"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Village Rows Skeleton -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-3.5 w-48 bg-[#CBD8E6] rounded"></div>
                            <div class="h-4 w-16 bg-[#CBD8E6] rounded"></div>
                        </div>
                        <div class="rounded-lg border border-[#CBD8E6] bg-white divide-y divide-[#E2E8F0] overflow-hidden shadow-xs">
                            <div class="p-3 bg-[#F8FAFC] flex justify-between">
                                <div class="h-3 w-28 bg-[#CBD8E6] rounded"></div>
                                <div class="h-3 w-16 bg-[#CBD8E6] rounded"></div>
                                <div class="h-3 w-16 bg-[#CBD8E6] rounded"></div>
                            </div>
                            <div class="p-3.5 flex items-center justify-between">
                                <div class="space-y-1.5 flex-1">
                                    <div class="h-3.5 w-36 bg-[#CBD8E6] rounded"></div>
                                    <div class="h-2.5 w-24 bg-[#E2E8F0] rounded"></div>
                                </div>
                                <div class="h-4 w-14 bg-[#CBD8E6] rounded"></div>
                            </div>
                            <div class="p-3.5 flex items-center justify-between">
                                <div class="space-y-1.5 flex-1">
                                    <div class="h-3.5 w-32 bg-[#CBD8E6] rounded"></div>
                                    <div class="h-2.5 w-20 bg-[#E2E8F0] rounded"></div>
                                </div>
                                <div class="h-4 w-14 bg-[#CBD8E6] rounded"></div>
                            </div>
                            <div class="p-3.5 flex items-center justify-between">
                                <div class="space-y-1.5 flex-1">
                                    <div class="h-3.5 w-40 bg-[#CBD8E6] rounded"></div>
                                    <div class="h-2.5 w-24 bg-[#E2E8F0] rounded"></div>
                                </div>
                                <div class="h-4 w-14 bg-[#CBD8E6] rounded"></div>
                            </div>
                            <div class="p-3.5 flex items-center justify-between">
                                <div class="space-y-1.5 flex-1">
                                    <div class="h-3.5 w-28 bg-[#CBD8E6] rounded"></div>
                                    <div class="h-2.5 w-20 bg-[#E2E8F0] rounded"></div>
                                </div>
                                <div class="h-4 w-14 bg-[#CBD8E6] rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderSkpDetailContent(data) {
        const content = document.getElementById('upt-detail-content');
        if (!content) return;

        const color = data.color || '#163A63';
        const bboxStr = data.bbox ? JSON.stringify(data.bbox) : 'null';
        const centroidLat = data.centroid_lat || 0;
        const centroidLng = data.centroid_lng || 0;

        let villagesHtml = '';
        if (data.villages && data.villages.length > 0) {
            const villageRows = data.villages.map((v, idx) => {
                const uptBadge = v.upt_name && v.upt_id
                    ? `<button type="button" onclick="window.sigapMap.focusToUpt(${v.upt_id})" class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#194482] bg-[#E8F0FA] hover:bg-[#D4DEE7] px-2 py-0.5 rounded border border-[#CBD8E6] transition" title="Pusatkan peta ke ${v.upt_name}"><span>${v.upt_name}</span><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>`
                    : `<span class="inline-block text-[10px] font-medium text-[#5A6E7D] bg-slate-100 px-2 py-0.5 rounded">Desa Mandiri</span>`;

                return `
                    <tr class="hover:bg-[#F8FAFC] transition">
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-[#0E2A47]">${idx + 1}. ${v.village_name}</div>
                            <div class="text-[10px] text-[#5A6E7D]">Kec. ${v.district_name || '-'}</div>
                        </td>
                        <td class="py-2.5 px-2 text-right tabular-nums font-medium text-[#243746]">
                            ${v.area_km2_formatted || v.area_km2 || '0'}
                        </td>
                        <td class="py-2.5 px-2 text-right">
                            <span class="tabular-nums font-bold text-[#107C41]">${(v.population_total || 0).toLocaleString('id-ID')}</span>
                            <div class="text-[9px] text-[#5A6E7D] tabular-nums">L: ${(v.population_male || 0).toLocaleString('id-ID')} · P: ${(v.population_female || 0).toLocaleString('id-ID')}</div>
                        </td>
                        <td class="py-2.5 px-2 text-right tabular-nums font-semibold text-[#0E2A47]">
                            ${(v.family_count || 0).toLocaleString('id-ID')}
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            ${uptBadge}
                        </td>
                    </tr>
                `;
            }).join('');

            villagesHtml = `
                <div class="overflow-x-auto rounded-lg border border-[#CBD8E6] bg-white shadow-xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#CBD8E6] text-[#5A6E7D] uppercase font-semibold text-[10px]">
                                <th class="py-2.5 px-3">Desa &amp; Kecamatan</th>
                                <th class="py-2.5 px-2 text-right">Luas (Km²)</th>
                                <th class="py-2.5 px-2 text-right">Penduduk</th>
                                <th class="py-2.5 px-2 text-right">KK</th>
                                <th class="py-2.5 px-3 text-center">Status / UPT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            ${villageRows}
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            villagesHtml = `
                <div class="rounded-lg border border-dashed border-[#CBD8E6] p-6 text-center bg-white shadow-xs">
                    <svg class="w-8 h-8 mx-auto text-[#8FA3B5] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h5 class="text-xs font-bold text-[#0E2A47]">Belum Ada Rincian Baris Desa</h5>
                    <p class="text-[11px] text-[#5A6E7D] mt-1 max-w-xs mx-auto">Data rincian nama desa dan statistik kependudukan untuk kawasan ${data.skp_name} belum tercatat di database.</p>
                </div>
            `;
        }

        content.innerHTML = `
            <!-- Header Identitas SKP (Atlas Style) -->
            <div class="p-6 border-b border-[#D4DEE7] bg-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-wider uppercase text-white px-2.5 py-0.5 rounded shadow-xs" style="background-color: ${color};">
                        <span>●</span> ${data.skp_name}
                    </span>
                    <span class="text-xs font-semibold text-[#5A6E7D]">
                        Kabupaten ${data.regency_name || 'Kalimantan Selatan'}
                    </span>
                </div>
                <h2 class="text-2xl font-bold text-[#0E2A47] leading-tight mb-1" style="font-family: 'Barlow Semi Condensed', sans-serif;">
                    ${data.skp_name} · ${data.kawasan_name}
                </h2>
                <p class="text-xs text-[#5A6E7D] leading-relaxed">
                    ${data.keterangan || 'Satuan Kawasan Pengembangan (SKP) Kawasan Transmigrasi'}
                </p>

                <!-- Tombol aksi navigasi cepat -->
                <div class="mt-4 flex items-center gap-2">
                    <button type="button" onclick="window.sigapMap.focusToSkp(${bboxStr}, ${centroidLat}, ${centroidLng}, ${data.id})"
                        class="flex-1 bg-[#163A63] hover:bg-[#0E2A47] text-white font-semibold text-xs py-2 px-3 rounded-md transition flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>Pusatkan Kamera ke SKP</span>
                    </button>
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + window.location.pathname + '?skp_id=' + ${data.id}); (window.showToast ? showToast('Tautan wilayah SKP berhasil disalin!', 'success') : alert('Tautan wilayah SKP disalin!'));"
                        class="bg-[#F8FAFC] hover:bg-[#E8F0FA] text-[#0E2A47] border border-[#CBD8E6] font-semibold text-xs py-2 px-3 rounded-md transition flex items-center justify-center gap-1"
                        title="Salin tautan wilayah SKP ini">
                        <svg class="w-3.5 h-3.5 text-[#2457A7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        <span>Salin</span>
                    </button>
                </div>
            </div>

            <!-- Badan Konten Drawer -->
            ${(() => {
                const isAuth = Boolean(window.sigapConfig?.isAuthenticated || data.is_authenticated);

                if (!isAuth) {
                    return `
                        <div class="p-6 space-y-4">
                            <!-- Kartu Terproteksi Logo Kunci (Tamu / Belum Login) -->
                            <div class="bg-white rounded-lg p-5 border border-[#D4DEE7] shadow-xs">
                                <div class="p-3.5 bg-[#FAF3E4] border border-[#E7D9B4] rounded-md text-xs text-[#243746] mb-4">
                                    <div class="flex items-start gap-2.5">
                                        <svg class="w-4 h-4 text-[#97620B] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <div>
                                            <strong class="block font-semibold text-[#97620B] mb-0.5">Akses rincian wilayah terproteksi:</strong>
                                            Rincian lengkap cakupan desa binaan dan rekapitulasi data demografi Satuan Kawasan Pengembangan (SKP) memerlukan autentikasi aparatur dinas.
                                        </div>
                                    </div>
                                </div>
                                <a href="${window.sigapConfig?.loginUrl || '/login'}" class="w-full bg-[#2457A7] hover:bg-[#194482] text-white font-semibold text-xs py-2.5 px-3 rounded-md text-center block transition shadow-xs">
                                    Masuk akun kedinasan untuk melihat rincian lengkap →
                                </a>
                            </div>

                            <!-- Info Estimasi Luas Tetap Tampil -->
                            <div class="bg-white p-4 rounded-lg border border-[#CBD8E6] shadow-xs flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block">Estimasi Luas Kawasan</span>
                                    <strong class="text-base font-bold text-[#107C41] tabular-nums">${data.luas_formatted} Ha</strong>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block">Status Rujukan</span>
                                    <span class="text-xs font-semibold text-[#0E2A47]">${data.sumber_data || 'DPMD & BIG'}</span>
                                </div>
                            </div>

                            <!-- Keterangan & Sumber Data -->
                            <div class="p-3.5 rounded-lg bg-[#EDF2F6] border border-[#CBD8E6] text-xs text-[#5A6E7D] space-y-1">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-[#2457A7] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <span>Sumber Delineasi: <strong>${data.sumber_data || 'DPMD Batola & BIG'}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                return `
                    <div class="p-6 space-y-6">
                        <!-- 1. Grid Metrik Utama 4 Kolom -->
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#5A6E7D] mb-2.5">
                                Ringkasan Spasial &amp; Kependudukan
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] shadow-xs">
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block mb-1">Estimasi Luas</span>
                                    <strong class="text-lg font-bold text-[#107C41] tabular-nums block">${data.luas_formatted} Ha</strong>
                                    <span class="text-[10px] text-[#5A6E7D]">Total luas kawasan SKP</span>
                                </div>
                                <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] shadow-xs">
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block mb-1">Cakupan Desa</span>
                                    <strong class="text-lg font-bold text-[#0E2A47] tabular-nums block">${data.villages_count} Desa</strong>
                                    <span class="text-[10px] text-[#5A6E7D]">Total desa terdata</span>
                                </div>
                                <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] shadow-xs">
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block mb-1">Total Penduduk</span>
                                    <strong class="text-lg font-bold text-[#0E2A47] tabular-nums block">${(data.total_population || 0).toLocaleString('id-ID')} Jiwa</strong>
                                    <span class="text-[10px] text-[#5A6E7D] tabular-nums">L: ${(data.total_male || 0).toLocaleString('id-ID')} · P: ${(data.total_female || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="bg-white p-3.5 rounded-lg border border-[#CBD8E6] shadow-xs">
                                    <span class="text-[10px] font-semibold uppercase text-[#5A6E7D] block mb-1">Kepala Keluarga</span>
                                    <strong class="text-lg font-bold text-[#0E2A47] tabular-nums block">${(data.total_family_count || 0).toLocaleString('id-ID')} KK</strong>
                                    <span class="text-[10px] text-[#5A6E7D]">Rasio KK di wilayah SKP</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Rincian Desa Binaan -->
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#5A6E7D]">
                                    Daftar Desa dalam Cakupan SKP
                                </h3>
                                <span class="text-[11px] font-bold text-[#107C41] bg-[#E8F5E9] px-2 py-0.5 rounded border border-[#C8E6C9] tabular-nums">
                                    ${data.villages_count} Desa
                                </span>
                            </div>

                            ${villagesHtml}
                        </div>

                        <!-- 3. Keterangan & Sumber Data -->
                        <div class="p-3.5 rounded-lg bg-[#EDF2F6] border border-[#CBD8E6] text-xs text-[#5A6E7D] space-y-1">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#2457A7] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <span>Sumber Delineasi: <strong>${data.sumber_data || 'DPMD Batola & BIG'}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            })()}
                        <div>
                            <strong class="text-[#0E2A47] font-semibold">Rujukan Spasial Kedinasan:</strong>
                            <p class="text-[11px] mt-0.5">${data.sumber_data || 'DPMD & Badan Informasi Geospasial (BIG)'}</p>
                        </div>
                    </div>
                </div>

                <!-- 4. Tombol Bawah Tutup Panel -->
                <div class="pt-2">
                    <button type="button" onclick="window.sigapMap.closeDetail()"
                        class="w-full bg-white hover:bg-[#F3F6F8] text-[#5A6E7D] hover:text-[#0E2A47] font-semibold text-xs py-2.5 px-4 rounded-md border border-[#CBD8E6] transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>Tutup Panel Rincian</span>
                    </button>
                </div>

            </div>
        `;
    }

    focusToSkp(bbox, lat, lng, skpId = null) {
        if (skpId) {
            const layer = this.skpLayers.find(l => {
                const fid = String(l.feature?.id || l.feature?.properties?.id || '');
                const fName = String(l.feature?.properties?.nama || '').toLowerCase().trim();
                const targetStr = String(skpId).toLowerCase().trim();
                return fid === targetStr || fName === targetStr;
            });
            if (layer) {
                this.map.fitBounds(layer.getBounds(), { padding: [60, 60], maxZoom: 14 });
                layer.openPopup();
                return;
            }
        }
        if (bbox && bbox.length === 4) {
            this.fitToFeatureBounds(bbox);
        } else if (lat && lng) {
            this.map.flyTo([lat, lng], 13, { duration: 1 });
        }
    }

    focusToUpt(uptId, options = {}) {
        const feature = this.allFeatures.find(f => String(f.id) === String(uptId));
        if (!feature) {
            console.warn(`UPT ID ${uptId} tidak ditemukan dalam data spasial.`);
            return;
        }

        const coords = feature.geometry.coordinates; // GeoJSON: [lng, lat]
        const latLng = L.latLng(coords[1], coords[0]);
        const marker = this.markers[feature.id];
        const targetZoom = options.zoom || 15;

        // Tutup detail drawer jika sedang terbuka
        this.closeDetail();

        // Highlight polygon delineasi UPT jika data poligon tersedia
        if (this.highlightPolygonLayer) {
            this.highlightPolygonLayer.clearLayers();
            if (feature.properties?.polygon_geojson) {
                const polyHighlight = L.geoJSON(feature.properties.polygon_geojson, {
                    style: {
                        color: '#059669',
                        weight: 3.5,
                        opacity: 0.95,
                        fillColor: '#10b981',
                        fillOpacity: 0.35,
                        dashArray: ''
                    }
                });
                this.highlightPolygonLayer.addLayer(polyHighlight);
            }
        }

        // Buka marker dari klaster bila diperlukan dan arahkan langsung ke titik
        if (marker && this.clusterGroup && this.clusterGroup.hasLayer(marker)) {
            this.clusterGroup.zoomToShowLayer(marker, () => {
                if (this.map.getZoom() < targetZoom) {
                    this.map.flyTo(latLng, targetZoom, { duration: 0.9 });
                    setTimeout(() => {
                        marker.openPopup();
                        this.pulseMarker(marker);
                    }, 1000);
                } else {
                    this.map.panTo(latLng, { animate: true, duration: 0.5 });
                    setTimeout(() => {
                        marker.openPopup();
                        this.pulseMarker(marker);
                    }, 600);
                }
            });
        } else {
            this.map.flyTo(latLng, targetZoom, { duration: 1.2 });
            setTimeout(() => {
                if (marker) {
                    marker.openPopup();
                    this.pulseMarker(marker);
                }
            }, 1300);
        }
    }

    pulseMarker(marker) {
        if (!marker) return;
        const el = marker.getElement ? marker.getElement() : null;
        if (el) {
            el.classList.add('pin-target-pulse');
            setTimeout(() => {
                el.classList.remove('pin-target-pulse');
            }, 4500);
        }
    }

    focusOnMap(lat, lng, uptId) {
        this.focusToUpt(uptId, { zoom: 15 });
    }

    showLoading(isLoading) {
        const loader = document.getElementById('map-loader');
        if (loader) {
            loader.classList.toggle('hidden', !isLoading);
        }
    }

    bindEvents() {
        // Toggle Basemap
        document.querySelectorAll('.btn-basemap').forEach(btn => {
            btn.addEventListener('click', () => {
                this.switchBasemap(btn.dataset.basemap);
            });
        });

        // Reset View Button
        const btnResetView = document.getElementById('btn-reset-view');
        if (btnResetView) {
            btnResetView.addEventListener('click', () => {
                this.map.flyTo(this.defaultCenter, this.defaultZoom, { duration: 1 });
            });
        }

        // Toggle Polygon UPT Button
        const btnTogglePolygon = document.getElementById('btn-toggle-polygon');
        if (btnTogglePolygon) {
            btnTogglePolygon.addEventListener('click', () => {
                this.togglePolygons();
            });
        }

        // Toggle Batas Kabupaten Button
        const btnToggleRegency = document.getElementById('btn-toggle-regency-boundary');
        if (btnToggleRegency) {
            btnToggleRegency.addEventListener('click', () => {
                this.toggleRegencyBoundaries();
            });
        }

        // Toggle Batas Kawasan SKP Button
        const btnToggleSkp = document.getElementById('btn-toggle-skp-boundary');
        if (btnToggleSkp) {
            btnToggleSkp.addEventListener('click', () => {
                this.toggleSkpBoundaries();
            });
        }

        // Toggle Navigasi (Sembunyikan / Tampilkan Navigasi & Legenda)
        const btnToggleNav = document.getElementById('btn-toggle-navigation');
        if (btnToggleNav) {
            btnToggleNav.addEventListener('click', () => {
                this.toggleNavigationUI();
            });
        }

        // Filter Inputs
        ['filter-regency', 'filter-decade', 'filter-pattern'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => this.applyFilters());
            }
        });

        // Filter Status Chips (1-Click Traffic Light Filter)
        document.querySelectorAll('.gis-status-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const status = chip.dataset.status || 'all';
                const hiddenInput = document.getElementById('filter-status');
                if (hiddenInput) {
                    hiddenInput.value = status;
                }
                document.querySelectorAll('.gis-status-chip').forEach(c => c.classList.remove('is-active'));
                chip.classList.add('is-active');
                this.applyFilters();
            });
        });

        const searchEl = document.getElementById('filter-search');
        if (searchEl) {
            let debounceTimer;
            searchEl.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => this.applyFilters(), 300);
            });
        }

        const btnReset = document.getElementById('btn-reset-filters');
        if (btnReset) {
            btnReset.addEventListener('click', () => this.resetFilters());
        }

        // Close Detail Drawer
        const btnCloseDetail = document.getElementById('btn-close-detail');
        if (btnCloseDetail) {
            btnCloseDetail.addEventListener('click', () => this.closeDetail());
        }
    }
}

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    window.sigapMap = new SigapWebGis();
    window.sigapMap.init();
});
