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
        this.markers = {}; // ID -> L.marker
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

        // Update active pill UI (Super Admin Theme)
        document.querySelectorAll('.btn-basemap').forEach(btn => {
            const svg = btn.querySelector('svg');
            const isActive = btn.dataset.basemap === type;
            btn.dataset.active = isActive ? 'true' : 'false';
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            if (btn.dataset.basemap === type) {
                btn.className = 'btn-basemap bg-[#1B2632] text-white font-bold text-xs px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-ambient-xs';
                if (svg) {
                    svg.classList.remove('text-[#2C3B4D]');
                    svg.classList.add('text-[#FFB162]');
                }
            } else {
                btn.className = 'btn-basemap bg-white hover:bg-[#EEE9DF]/70 text-[#2C3B4D] font-bold text-xs px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 border border-[#C9C1B1]/40';
                if (svg) {
                    svg.classList.remove('text-[#FFB162]');
                    svg.classList.add('text-[#2C3B4D]');
                }
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
        this.markers[feature.id] = marker;

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
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">🟢 Clean & Clear</span>`;
        } else if (p.issue_status === 'warning') {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">🟡 Waspada / Monitoring</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 animate-pulse">🔴 Kritis / Prioritas Mediasi</span>`;
        }

        return `
            <div class="overflow-hidden rounded-2xl bg-white font-sans text-[#1B2632] shadow-ambient-md border border-[#C9C1B1]/60">
                <div class="bg-[#1B2632] text-white px-4 py-3 flex items-center justify-between border-b border-[#2C3B4D]">
                    <span class="text-[10px] font-black uppercase tracking-wider text-[#FFB162] bg-[#2C3B4D] px-2.5 py-0.5 rounded-md border border-[#FFB162]/30 font-mono">
                        UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-semibold text-[#EEE9DF]/80">
                        Kab. ${p.regency_name}
                    </span>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <h4 class="font-extrabold text-[#1B2632] text-base leading-tight">
                            ${p.upt_name}
                        </h4>
                        <p class="text-xs text-[#2C3B4D]/70 mt-0.5 flex items-center gap-1 font-medium">
                            <span>Desa Definitif:</span>
                            <strong class="text-[#1B2632]">${p.current_village_name}</strong>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 bg-[#EEE9DF]/40 rounded-xl p-2.5 border border-[#C9C1B1]/60 text-xs">
                        <div>
                            <span class="text-[#2C3B4D]/60 block text-[9px] uppercase font-bold">Pola Usaha</span>
                            <span class="font-bold text-[#1B2632]">${p.business_pattern}</span>
                        </div>
                        <div>
                            <span class="text-[#2C3B4D]/60 block text-[9px] uppercase font-bold">Penempatan</span>
                            <span class="font-bold text-[#1B2632]">${p.placement_year} (${p.placement_kk.toLocaleString()} KK)</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-[#C9C1B1]/40 flex items-center justify-between text-[11px]">
                            <span class="text-[#2C3B4D]/70 font-medium">Serah Terima:</span>
                            <strong class="text-[#1B2632] font-mono">${p.handover_year || '-'} (${p.handover_kk.toLocaleString()} KK)</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-[#2C3B4D]/70 font-medium">Status Lahan:</span>
                        ${statusBadge}
                    </div>

                    <button onclick="window.sigapMap.openDetail(${feature.id})"
                        class="w-full bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5 shadow-ambient-xs active:translate-y-0.5">
                        <span>Buka Riwayat Lengkap</span>
                        <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
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

        if (this.highlightPolygonLayer) {
            this.highlightPolygonLayer.clearLayers();
        }

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
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">🟢 Clean & Clear</span>`;
        } else if (data.issue_status === 'warning') {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">🟡 Waspada / Monitoring</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 animate-pulse">🔴 Kritis / Prioritas Mediasi</span>`;
        }

        content.innerHTML = `
            <!-- Header Kartu Detail -->
            <div class="p-6 border-b border-[#2C3B4D] bg-[#1B2632] text-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-black tracking-wider uppercase bg-[#2C3B4D] text-[#FFB162] border border-[#FFB162]/40 px-2.5 py-0.5 rounded font-mono">
                        REGISTRASI UPT-${numFormatted}
                    </span>
                    <span class="text-xs font-semibold text-[#EEE9DF]/80">
                        Kabupaten ${data.regency_name}
                    </span>
                </div>
                <h2 class="text-2xl font-black text-white leading-tight mb-1">
                    ${data.upt_name}
                </h2>
                <div class="flex items-center gap-1.5 text-sm text-[#FFB162]">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span>Desa Definitif: <strong>${data.current_village_name}</strong></span>
                </div>
            </div>

            <div class="p-5 space-y-4 bg-[#EEE9DF]">
                <!-- Status Legalitas Lahan -->
                <div class="bg-white rounded-2xl p-4 border border-[#C9C1B1] shadow-ambient-xs">
                    <span class="text-[10px] font-black text-[#2C3B4D]/75 uppercase tracking-wider block mb-2">Status Agraria & Pertanahan</span>
                    <div class="mb-3">${statusBadge}</div>
                    
                    <div class="bg-[#EEE9DF]/40 rounded-xl p-3 border border-[#C9C1B1]/60 text-xs text-[#1B2632]">
                        <strong class="text-[#1B2632] block mb-1">Catatan Perkembangan Lapangan:</strong>
                        ${data.issue_note || 'Tidak ada catatan permasalahan khusus. Lokasi beroperasi normal.'}
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-[#2C3B4D]/80 pt-2 border-t border-[#C9C1B1]/30">
                        <span>Sertifikasi Tanah:</span>
                        <strong class="text-[#1B2632] font-bold">${data.shm_status || 'SHM Tuntas 100%'}</strong>
                    </div>
                </div>

                <!-- Perbandingan Dinamika Penempatan vs Penyerahan -->
                <div class="bg-white rounded-2xl p-4 border border-[#C9C1B1] shadow-ambient-xs">
                    <span class="text-[10px] font-black text-[#2C3B4D]/75 uppercase tracking-wider block mb-3">Rekam Jejak Demografi</span>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#EEE9DF]/50 rounded-xl p-3 border border-[#C9C1B1]/60">
                            <span class="text-[10px] font-black text-[#1B2632] uppercase block mb-1">1. Penempatan Awal</span>
                            <div class="text-lg font-black text-[#1B2632]">${data.placement_kk.toLocaleString()} <span class="text-xs font-normal text-[#2C3B4D]/70">KK</span></div>
                            <div class="text-xs text-[#2C3B4D]/80 font-medium">${data.placement_population.toLocaleString()} Jiwa</div>
                            <div class="mt-2 text-[10px] text-[#2C3B4D] font-bold bg-[#C9C1B1]/40 px-2 py-0.5 rounded inline-block">
                                Tahun ${data.placement_year}
                            </div>
                        </div>

                        <div class="bg-[#2C3B4D]/10 rounded-xl p-3 border border-[#2C3B4D]/25">
                            <span class="text-[10px] font-black text-[#2C3B4D] uppercase block mb-1">2. Serah Terima Pemda</span>
                            <div class="text-lg font-black text-[#1B2632]">${data.handover_kk.toLocaleString()} <span class="text-xs font-normal text-[#2C3B4D]/70">KK</span></div>
                            <div class="text-xs text-[#2C3B4D]/80 font-medium">${data.handover_population.toLocaleString()} Jiwa</div>
                            <div class="mt-2 text-[10px] text-[#2C3B4D] font-bold bg-[#2C3B4D]/15 px-2 py-0.5 rounded inline-block">
                                ${data.handover_year || 'BAST Tersedia'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Teknis & Pertanian -->
                <div class="bg-white rounded-2xl p-4 border border-[#C9C1B1] shadow-ambient-xs space-y-2.5 text-xs text-[#1B2632]">
                    <span class="text-[10px] font-black text-[#2C3B4D]/75 uppercase tracking-wider block mb-2">Pola Budidaya & Pertanian</span>
                    <div class="flex items-center justify-between py-1 border-b border-[#C9C1B1]/30">
                        <span class="text-[#2C3B4D]/70">Pola Usaha:</span>
                        <span class="font-extrabold text-[#1B2632] bg-[#EEE9DF]/70 px-2.5 py-0.5 rounded-lg border border-[#C9C1B1]/50 font-mono">${data.business_pattern}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#C9C1B1]/30">
                        <span class="text-[#2C3B4D]/70">Titik Centroid (WGS84):</span>
                        <span class="font-mono text-[#1B2632] font-semibold">${data.latitude.toFixed(5)}, ${data.longitude.toFixed(5)}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-[#2C3B4D]/70">Delineasi Spasial PostGIS:</span>
                        <span class="font-bold text-[#1B4D3E] flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Poligon Aktif (EPSG:4326)
                        </span>
                    </div>
                </div>

                <!-- Repositori E-Arsip Dokumen BAST -->
                <div class="bg-white rounded-2xl p-4 border border-[#C9C1B1] shadow-ambient-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black text-[#2C3B4D]/75 uppercase tracking-wider">E-Arsip BAST & Dokumen SK</span>
                        <span class="text-xs font-mono font-bold text-[#1B2632] bg-[#EEE9DF] px-2 py-0.5 rounded">${data.documents_count} Dokumen</span>
                    </div>

                    <div class="p-3 bg-[#FFB162]/15 border border-[#FFB162]/40 rounded-xl text-xs text-[#1B2632] mb-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-[#A35139] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <div>
                                <strong class="block font-bold text-[#A35139]">Akses Dokumen Fisik Terproteksi:</strong>
                                Berkas scan BAST fisik dan telaah hukum agraria memerlukan autentikasi aparatur dinas untuk verifikasi alas hak.
                            </div>
                        </div>
                    </div>

                    <a href="/login" class="w-full bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs py-2.5 px-3 rounded-xl text-center block transition shadow-ambient-xs">
                        Masuk Akun Kedinasan untuk Unduh Dokumen →
                    </a>
                </div>

                <!-- Tombol Aksi Fokus Peta -->
                <button onclick="window.sigapMap.focusOnMap(${data.latitude}, ${data.longitude}, ${data.id})"
                    class="w-full bg-[#1B2632] hover:bg-[#2C3B4D] text-[#FFB162] border border-[#FFB162]/40 font-black text-sm py-3 px-4 rounded-xl shadow-ambient-xs transition flex items-center justify-center gap-2 active:translate-y-0.5">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
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
