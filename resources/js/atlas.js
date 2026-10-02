import Alpine from 'alpinejs';
import L from 'leaflet';

Alpine.data('atlasShell', () => ({
    collapsed: false,
    menuOpen: false,
    mobile: window.innerWidth < 1024,
    init() {
        try { this.collapsed = localStorage.getItem('atlas-nav-collapsed') === 'true'; } catch {}
        this.mediaQuery = window.matchMedia('(max-width: 1023px)');
        this.onViewportChange = (event) => { this.mobile = event.matches; this.menuOpen = false; };
        this.mediaQuery.addEventListener('change', this.onViewportChange);
    },
    destroy() { this.mediaQuery.removeEventListener('change', this.onViewportChange); },
    toggleNavigation() {
        if (this.mobile) {
            this.menuOpen = !this.menuOpen;
            if (this.menuOpen) this.$nextTick(() => this.$refs.sidebar.querySelector('a').focus());
        } else {
            this.collapsed = !this.collapsed;
            try { localStorage.setItem('atlas-nav-collapsed', this.collapsed); } catch {}
            window.dispatchEvent(new Event('atlas:resize'));
        }
    },
    closeMenu() { this.menuOpen = false; this.$nextTick(() => this.$refs.menuButton.focus()); },
    trapMenu(event) {
        if (!this.mobile || !this.menuOpen) return;
        const focusable = [...this.$refs.sidebar.querySelectorAll('a, button, summary')].filter(el => el.getClientRects().length);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    },
}));

function initAtlasMap() {
    const container = document.getElementById('atlas-work-map');
    const payload = document.getElementById('atlas-map-data');
    if (!container || !payload) return;
    const locations = JSON.parse(payload.textContent);
    const status = document.getElementById('atlas-map-status');
    const map = L.map(container, { scrollWheelZoom: false }).setView([-2.8, 115.35], 7);
    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 18,
    }).addTo(map);
    tiles.on('tileerror', () => { status.textContent = 'Peta dasar gagal dimuat. Data lokasi tetap dapat dibuka dari daftar.'; });
    const colours = { clean: '#287451', warning: '#97620b', critical: '#b43d3d' };
    const markers = new Map();
    const valid = locations.filter(item => item.latitude !== null && item.longitude !== null && Number.isFinite(Number(item.latitude)) && Number.isFinite(Number(item.longitude)));
    const select = (id, zoom = true) => {
        const location = locations.find(item => String(item.id) === String(id));
        if (!location) return;
        document.getElementById('atlas-map-name').textContent = location.upt_name;
        document.getElementById('atlas-map-location').textContent = `UPT-${String(location.upt_number).padStart(3, '0')} · ${location.regency_name} · Kini: ${location.current_village_name || 'Belum tercatat'}`;
        const link = document.getElementById('atlas-map-detail');
        link.href = location.detail_url;
        link.hidden = false;
        markers.forEach((marker, key) => marker.setStyle({ weight: key === location.id ? 3 : 1.5, radius: key === location.id ? 9 : 5 }));
        if (markers.has(location.id)) {
            status.textContent = 'Lokasi terpilih · Koordinat tercatat';
            if (zoom) map.setView(markers.get(location.id).getLatLng(), 11, { animate: false });
        } else {
            status.textContent = 'Koordinat belum tersedia untuk UPT ini.';
            if (valid.length) map.fitBounds(valid.map(item => [item.latitude, item.longitude]), { padding: [25, 25], maxZoom: 10 });
        }
    };
    valid.forEach(location => {
        const marker = L.circleMarker([location.latitude, location.longitude], { radius: 5, weight: 1.5, color: '#ffffff', fillColor: colours[location.issue_status] || '#5a6e7d', fillOpacity: .95 }).addTo(map);
        const label = document.createElement('span');
        label.textContent = location.upt_name;
        marker.bindTooltip(label).on('click', () => select(location.id));
        markers.set(location.id, marker);
    });
    if (valid.length) map.fitBounds(valid.map(item => [item.latitude, item.longitude]), { padding: [25, 25], maxZoom: 10 });
    else status.textContent = 'Belum ada koordinat yang dapat ditampilkan.';
    document.querySelectorAll('[data-atlas-location]').forEach(button => button.addEventListener('click', () => {
        select(button.dataset.atlasLocation);
        if (window.innerWidth < 1200) container.scrollIntoView({ behavior: 'auto', block: 'center' });
    }));
    const initial = document.querySelector('[data-atlas-location]');
    if (initial) select(initial.dataset.atlasLocation, false);
    new ResizeObserver(() => map.invalidateSize()).observe(container);
    window.addEventListener('atlas:resize', () => map.invalidateSize());
}

document.addEventListener('DOMContentLoaded', initAtlasMap);

/* ---------------------------------------------------------------
   Dialog Atlas: delegasi global untuk semua .atlas-dialog.
   Buka: elemen dengan [data-atlas-dialog-open="<id>"].
   Tutup: [data-atlas-dialog-close], Escape, atau klik backdrop.
   Termasuk focus-trap + restore fokus pemicu (aksesibilitas).
   --------------------------------------------------------------- */
const atlasDialogState = new Map(); // id -> { trigger }

function atlasFocusables(dialog) {
    return [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
        .filter(el => el.getClientRects().length && el.getAttribute('aria-hidden') !== 'true');
}

function openAtlasDialog(id, { focus } = {}) {
    const dialog = document.getElementById(id);
    if (!dialog) return;
    dialog.setAttribute('open', '');
    dialog.dispatchEvent(new CustomEvent('atlas:dialog-opened', { bubbles: true }));
    const focusables = atlasFocusables(dialog);
    const target = (focus && dialog.querySelector(focus)) || focusables[0] || dialog;
    target.focus();
}

function closeAtlasDialog(id) {
    const dialog = typeof id === 'string' ? document.getElementById(id) : (id.closest?.('.atlas-dialog') ?? null);
    if (!dialog) return;
    dialog.removeAttribute('open');
    const state = atlasDialogState.get(dialog.id);
    if (state?.trigger?.isConnected) state.trigger.focus();
    atlasDialogState.delete(dialog.id);
}

function initAtlasDialogs() {
    // Buka: catat pemicu agar fokus bisa dipulihkan saat tutup.
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-atlas-dialog-open]');
        if (trigger) {
            const id = trigger.dataset.atlasDialogOpen;
            atlasDialogState.set(id, { trigger });
            openAtlasDialog(id, { focus: trigger.dataset.atlasDialogFocus || null });
            return;
        }
        const closer = event.target.closest('[data-atlas-dialog-close]');
        if (closer) { closeAtlasDialog(closer); return; }
        // Klik backdrop: dialog adalah layer penuh; panel adalah anak di dalamnya.
        const backdrop = event.target.classList?.contains('atlas-dialog') ? event.target : null;
        if (backdrop) closeAtlasDialog(backdrop);
    });

    // Escape menutup dialog teratas yang terbuka.
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const open = [...document.querySelectorAll('.atlas-dialog[open]')].pop();
        if (open) closeAtlasDialog(open);
    });

    // Focus-trap di dalam dialog yang sedang terbuka.
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') return;
        const dialog = event.target.closest?.('.atlas-dialog[open]');
        if (!dialog) return;
        const focusables = atlasFocusables(dialog);
        if (!focusables.length) return;
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        else if (!dialog.contains(document.activeElement)) { event.preventDefault(); first.focus(); }
    });
}

document.addEventListener('DOMContentLoaded', initAtlasDialogs);
