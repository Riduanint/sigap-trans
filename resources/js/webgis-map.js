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
        this.highlightPolygonLayer = null;
        this.allFeatures = [];
        this.filteredFeatures = [];
        this.regencyLayers = {}; // ID -> L.geoJSON
        this.activeBasemap = 'street';
        this.tileLayers = {};
        this.showPolygons = true;
        this.showRegencyBoundaries = true;
        this.selectedRegencyId = 'all';

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

        // Zoom Control di posisi kanan bawah
        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);
    }

    initBasemaps() {
        // 1. Peta Jalan Modern (Carto Voyager)
        this.tileLayers.street = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd',
        });

        // 2. Citra Satelit Beresolusi Tinggi (Esri World Imagery)
        this.tileLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
        });

        // 3. Label overlay untuk mode satelit agar batas dan nama tempat tetap terbaca
        this.tileLayers.satelliteLabels = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
            maxZoom: 18,
            subdomains: 'abcd',
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

        // Update active pill UI
        document.querySelectorAll('.btn-basemap').forEach(btn => {
            if (btn.dataset.basemap === type) {
                btn.classList.add('bg-emerald-600', 'text-white', 'shadow-md');
                btn.classList.remove('bg-white/80', 'text-slate-700');
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white', 'shadow-md');
                btn.classList.add('bg-white/80', 'text-slate-700');
            }
        });
    }

    initLayers() {
        // Layer Group untuk Poligon Batas Wilayah 9 Kabupaten (Paling bawah agar pin tidak tertutup)
        this.regencyLayerGroup = L.layerGroup().addTo(this.map);

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
                    <div class="font-extrabold text-xs text-white">Kab. ${p.name} (${p.code_roman})</div>
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
            const response = await fetch('/api/upt-locations');
            const data = await response.json();

            if (data.features) {
                this.allFeatures = data.features;
                this.filteredFeatures = [...this.allFeatures];
                this.renderFeatures(this.filteredFeatures);
                this.updateStatisticsDisplay(data.meta);

                // Auto-filter jika ada query param regency dari URL
                const urlParams = new URLSearchParams(window.location.search);
                const regParam = urlParams.get('regency');
                if (regParam) {
                    const regSel = document.getElementById('filter-regency');
                    if (regSel) {
                        regSel.value = regParam;
                        this.applyFilters();
                    }
                }
            }
        } catch (error) {
            console.error('Gagal memuat data WebGIS:', error);
        } finally {
            this.showLoading(false);
        }
    }

    renderFeatures(features) {
        this.clusterGroup.clearLayers();
        this.polygonLayerGroup.clearLayers();

        const bounds = [];

        features.forEach(feature => {
            const coords = feature.geometry.coordinates; // [lng, lat]
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

        // Otomatis sesuaikan zoom jika ada data terfilter
        if (bounds.length > 0) {
            this.map.fitBounds(L.latLngBounds(bounds), {
                padding: [60, 60],
                maxZoom: 12
            });
        }
    }

    createMarker(feature, latLng) {
        const props = feature.properties;
        const status = props.issue_status || 'clean';

        // Custom DivIcon Traffic Light
        const icon = L.divIcon({
            className: `custom-pin pin-${status}`,
            html: `
                <div class="pin-bubble">
                    <span class="pin-bubble-inner">${props.upt_number}</span>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        const marker = L.marker(latLng, { icon: icon });
        marker.feature = feature;

        // Popup Content
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
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">🟢 Clean & Clear</span>`;
        } else if (p.issue_status === 'warning') {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">🟡 Monitoring</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">🔴 Prioritas Mediasi</span>`;
        }

        return `
            <div class="p-4">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5 mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-medium text-slate-500">
                        Kab. ${p.regency_name}
                    </span>
                </div>

                <h4 class="font-extrabold text-slate-900 text-base leading-tight mb-1">
                    ${p.upt_name}
                </h4>
                <p class="text-xs text-slate-500 mb-3 flex items-center gap-1">
                    <span>Desa Definitif:</span>
                    <span class="font-semibold text-slate-700">${p.current_village_name}</span>
                </p>

                <div class="grid grid-cols-2 gap-2 bg-slate-50/90 rounded-xl p-2.5 border border-slate-200/70 mb-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Pola Usaha</span>
                        <span class="font-bold text-slate-800">${p.business_pattern}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Penempatan</span>
                        <span class="font-bold text-slate-800">${p.placement_year} (${p.placement_kk.toLocaleString()} KK)</span>
                    </div>
                    <div class="col-span-2 pt-1 border-t border-slate-200/60 flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">Serah Terima:</span>
                        <span class="font-bold text-slate-800 text-[11px]">${p.handover_year || '-'} (${p.handover_kk.toLocaleString()} KK)</span>
                    </div>
                </div>

                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Status Lahan:</span>
                    ${statusBadge}
                </div>

                <button onclick="window.sigapMap.openDetail(${feature.id})" 
                    class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-bold text-xs py-2 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow">
                    <span>Buka Riwayat Lengkap</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
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
                if (p.regency_name !== regMap[regencyId]) return false;
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

        this.renderFeatures(this.filteredFeatures);
        this.updateDynamicMetrics(this.filteredFeatures);

        // Jika kabupaten spesifik dipilih, zoom tepat ke batas poligon kabupaten tersebut
        if (regencyId !== 'all' && this.regencyLayers[regencyId]) {
            this.map.fitBounds(this.regencyLayers[regencyId].getBounds(), {
                padding: [50, 50],
                maxZoom: 12
            });
        }
    }

    resetFilters() {
        if (document.getElementById('filter-regency')) document.getElementById('filter-regency').value = 'all';
        if (document.getElementById('filter-status')) document.getElementById('filter-status').value = 'all';
        if (document.getElementById('filter-decade')) document.getElementById('filter-decade').value = 'all';
        if (document.getElementById('filter-pattern')) document.getElementById('filter-pattern').value = 'all';
        if (document.getElementById('filter-search')) document.getElementById('filter-search').value = '';

        this.selectedRegencyId = 'all';
        Object.keys(this.regencyLayers).forEach(id => {
            this.resetRegencyPolygonStyle(id);
        });

        this.filteredFeatures = [...this.allFeatures];
        this.renderFeatures(this.filteredFeatures);
        this.updateDynamicMetrics(this.filteredFeatures);

        // Reset view ke Kalsel
        this.map.flyTo(this.defaultCenter, this.defaultZoom, { duration: 1 });
    }

    togglePolygons() {
        this.showPolygons = !this.showPolygons;
        if (this.showPolygons) {
            this.polygonLayerGroup.addTo(this.map);
            this.renderFeatures(this.filteredFeatures);
        } else {
            this.polygonLayerGroup.clearLayers();
        }

        const btn = document.getElementById('btn-toggle-polygon');
        if (btn) {
            btn.classList.toggle('text-emerald-700', this.showPolygons);
            btn.classList.toggle('bg-emerald-50', this.showPolygons);
        }
    }

    toggleRegencyBoundaries() {
        this.showRegencyBoundaries = !this.showRegencyBoundaries;
        if (this.showRegencyBoundaries) {
            this.regencyLayerGroup.addTo(this.map);
        } else {
            this.map.removeLayer(this.regencyLayerGroup);
        }

        const btn = document.getElementById('btn-toggle-regency-boundary');
        if (btn) {
            btn.classList.toggle('text-purple-700', this.showRegencyBoundaries);
            btn.classList.toggle('bg-purple-50', this.showRegencyBoundaries);
            btn.classList.toggle('border-purple-200/70', this.showRegencyBoundaries);
            btn.classList.toggle('bg-white/80', !this.showRegencyBoundaries);
            btn.classList.toggle('text-slate-700', !this.showRegencyBoundaries);
        }
    }

    updateStatisticsDisplay(meta) {
        if (!meta) return;

        const totalEl = document.getElementById('stat-total-upt');
        const placementKkEl = document.getElementById('stat-placement-kk');
        const handoverKkEl = document.getElementById('stat-handover-kk');
        const cleanEl = document.getElementById('stat-clean');
        const warningEl = document.getElementById('stat-warning');
        const criticalEl = document.getElementById('stat-critical');
        const filteredCountEl = document.getElementById('filter-count-badge');

        if (totalEl) totalEl.innerText = meta.total_features || 124;
        if (placementKkEl) placementKkEl.innerText = (meta.total_kk_placement || 63701).toLocaleString();
        if (handoverKkEl) handoverKkEl.innerText = (meta.total_kk_handover || 64942).toLocaleString();
        if (cleanEl) cleanEl.innerText = meta.status_counts?.clean || 111;
        if (warningEl) warningEl.innerText = meta.status_counts?.warning || 7;
        if (criticalEl) criticalEl.innerText = meta.status_counts?.critical || 6;
        if (filteredCountEl) filteredCountEl.innerText = `${meta.total_features} / 124`;
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

    async openDetail(id) {
        const drawer = document.getElementById('upt-detail-drawer');
        const content = document.getElementById('upt-detail-content');
        if (!drawer || !content) return;

        // Buka drawer lebih dulu dengan skeleton/spinner
        drawer.classList.remove('translate-x-full');
        content.innerHTML = `
            <div class="p-8 text-center py-24">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent"></div>
                <p class="mt-3 text-sm text-slate-500 font-medium">Memuat data profil riwayat UPT...</p>
            </div>
        `;

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
        }
    }

    renderDetailContent(data) {
        const content = document.getElementById('upt-detail-content');
        if (!content) return;

        const numFormatted = String(data.upt_number).padStart(3, '0');

        let statusBadge = '';
        if (data.issue_status === 'clean') {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">🟢 Clean & Clear (SHM Tuntas)</span>`;
        } else if (data.issue_status === 'warning') {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">🟡 Perlu Monitoring Berkala</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">🔴 Prioritas Khusus / Mediasi Sengketa</span>`;
        }

        content.innerHTML = `
            <!-- Header Kartu -->
            <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-slate-900 to-slate-800 text-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold tracking-wider uppercase bg-emerald-500 text-slate-950 px-2.5 py-0.5 rounded">
                        REGISTRASI UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-medium text-slate-300">
                        Kabupaten ${data.regency_name} (${data.regency_roman})
                    </span>
                </div>
                <h2 class="text-2xl font-black text-white leading-tight mb-1">
                    ${data.upt_name}
                </h2>
                <div class="flex items-center gap-2 text-sm text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span>Desa Definitif: <strong>${data.current_village_name}</strong></span>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Status Legalitas Lahan -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Status Agraria & Pertanahan</span>
                    <div class="mb-3">${statusBadge}</div>
                    
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/70 text-xs text-slate-600">
                        <strong class="text-slate-800 block mb-1">Catatan Perkembangan Lapangan:</strong>
                        ${data.issue_note || 'Tidak ada catatan permasalahan khusus. Lokasi beroperasi normal.'}
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                        <span>Sertifikasi Tanah:</span>
                        <span class="font-bold text-slate-800">${data.shm_status || 'SHM Tuntas 100%'}</span>
                    </div>
                </div>

                <!-- Perbandingan Dinamika Penempatan vs Penyerahan -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Rekam Jejak Demografi</span>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50/60 rounded-xl p-3.5 border border-emerald-200/80">
                            <span class="text-[11px] font-bold text-emerald-800 uppercase block mb-1">1. Penempatan Awal</span>
                            <div class="text-lg font-black text-emerald-950">${data.placement_kk.toLocaleString()} <span class="text-xs font-normal text-slate-600">KK</span></div>
                            <div class="text-xs text-slate-600 font-medium">${data.placement_population.toLocaleString()} Jiwa</div>
                            <div class="mt-2 text-[11px] text-emerald-700 font-semibold bg-emerald-100/70 px-2 py-0.5 rounded inline-block">
                                Tahun ${data.placement_year}
                            </div>
                        </div>

                        <div class="bg-blue-50/60 rounded-xl p-3.5 border border-blue-200/80">
                            <span class="text-[11px] font-bold text-blue-800 uppercase block mb-1">2. Serah Terima Pemda</span>
                            <div class="text-lg font-black text-blue-950">${data.handover_kk.toLocaleString()} <span class="text-xs font-normal text-slate-600">KK</span></div>
                            <div class="text-xs text-slate-600 font-medium">${data.handover_population.toLocaleString()} Jiwa</div>
                            <div class="mt-2 text-[11px] text-blue-700 font-semibold bg-blue-100/70 px-2 py-0.5 rounded inline-block">
                                ${data.handover_year || 'BAST Tersedia'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Teknis & Pertanian -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-2.5 text-xs">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Pola Budidaya & Pertanian</span>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Pola Usaha:</span>
                        <span class="font-extrabold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">${data.business_pattern}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Titik Centroid (WGS84):</span>
                        <span class="font-mono text-slate-700">${data.latitude.toFixed(5)}, ${data.longitude.toFixed(5)}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Delineasi Spasial PostGIS:</span>
                        <span class="font-semibold text-emerald-700">Poligon Aktif (EPSG:4326)</span>
                    </div>
                </div>

                <!-- Repositori E-Arsip Dokumen BAST -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">E-Arsip BAST & Dokumen SK</span>
                        <span class="text-xs text-slate-400">${data.documents_count} Dokumen</span>
                    </div>

                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-900 mb-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <div>
                                <strong class="block font-bold">Akses Dokumen Fisik Terproteksi:</strong>
                                Berkas scan BAST fisik dan telaah hukum agraria memerlukan autentikasi aparatur dinas untuk verifikasi alas hak.
                            </div>
                        </div>
                    </div>

                    <a href="/login" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs py-2 px-3 rounded-lg text-center block transition">
                        Masuk Akun Kedinasan untuk Unduh Dokumen →
                    </a>
                </div>

                <!-- Tombol Aksi Fokus Peta -->
                <button onclick="window.sigapMap.focusOnMap(${data.latitude}, ${data.longitude}, ${data.id})"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-3 px-4 rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Fokuskan ke Posisi Peta</span>
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

    focusOnMap(lat, lng, uptId) {
        this.closeDetail();
        this.map.flyTo([lat, lng], 14, {
            duration: 1.2
        });

        // Buka popup marker yang bersangkutan setelah terbang
        setTimeout(() => {
            this.clusterGroup.eachLayer(layer => {
                if (layer.feature && layer.feature.id === uptId) {
                    layer.openPopup();
                }
            });
        }, 1300);
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

        // Filter Inputs
        ['filter-regency', 'filter-status', 'filter-decade', 'filter-pattern'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => this.applyFilters());
            }
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
