import Alpine from 'alpinejs';
import L from 'leaflet';
window.L = L;

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

let confirmAtlasResolver = null;

export function confirmAtlas(options = {}) {
    return new Promise((resolve) => {
        const dialog = document.getElementById('atlas-confirm-dialog');
        if (!dialog) {
            const fallbackOk = window.confirm(options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?');
            return resolve(fallbackOk);
        }

        const titleEl = document.getElementById('atlas-confirm-title');
        const msgEl = document.getElementById('atlas-confirm-message');
        const iconBox = document.getElementById('atlas-confirm-icon-box');
        const iconSvg = document.getElementById('atlas-confirm-icon');
        const cancelBtn = document.getElementById('atlas-confirm-btn-cancel');
        const proceedBtn = document.getElementById('atlas-confirm-btn-proceed');

        const title = options.title || 'Konfirmasi Tindakan';
        const message = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        const type = options.type || 'primary'; // 'primary', 'clean', 'warning', 'danger'
        const cancelText = options.cancelText || 'Batal';
        const confirmText = options.confirmText || (type === 'danger' ? 'Ya, Hapus Data' : (type === 'clean' ? 'Ya, Setujui' : (type === 'warning' ? 'Ya, Simpan Perubahan' : 'Ya, Lanjutkan')));

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.innerHTML = message;
        if (cancelBtn) cancelBtn.textContent = cancelText;
        if (proceedBtn) {
            proceedBtn.textContent = confirmText;
            proceedBtn.className = 'atlas-button';
            proceedBtn.style.minWidth = '120px';
        }

        // Tampilan varian ikon dan tombol berdasarkan tipe tindakan
        if (iconBox && iconSvg) {
            if (type === 'danger') {
                iconBox.style.background = '#fbeded';
                iconBox.style.color = '#b43d3d';
                iconSvg.innerHTML = '<path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/>';
                if (proceedBtn) proceedBtn.classList.add('atlas-button--danger-solid');
            } else if (type === 'warning') {
                iconBox.style.background = '#faf3e4';
                iconBox.style.color = '#97620b';
                iconSvg.innerHTML = '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3ZM12 9v4m0 4h.01"/>';
                if (proceedBtn) proceedBtn.classList.add('atlas-button--warning');
            } else if (type === 'clean') {
                iconBox.style.background = '#edf6f0';
                iconBox.style.color = '#107c41';
                iconSvg.innerHTML = '<path d="m9 11 3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>';
                if (proceedBtn) proceedBtn.classList.add('atlas-button--clean');
            } else {
                iconBox.style.background = '#eaf1fb';
                iconBox.style.color = '#194482';
                iconSvg.innerHTML = '<path d="M12 16v-4m0-4h.01M22 12A10 10 0 1 1 2 12a10 10 0 0 1 20 0Z"/>';
                if (proceedBtn) proceedBtn.classList.add('atlas-button--primary');
            }
        }

        confirmAtlasResolver = resolve;
        dialog.setAttribute('open', '');

        // Auto-focus:
        // Pada aksi bahaya (danger), fokuskan ke tombol BATAL demi keselamatan data!
        // Pada aksi reguler, fokuskan ke tombol LANJUTKAN.
        if (type === 'danger' && cancelBtn) {
            cancelBtn.focus();
        } else if (proceedBtn) {
            proceedBtn.focus();
        }
    });
}

export function closeConfirmAtlas(result) {
    const dialog = document.getElementById('atlas-confirm-dialog');
    if (dialog) dialog.removeAttribute('open');
    if (typeof confirmAtlasResolver === 'function') {
        const res = confirmAtlasResolver;
        confirmAtlasResolver = null;
        res(result);
    }
}

export function showToast(message, type = 'success') {
    if (!message || message === 'undefined') {
        message = (type === 'error' || type === 'danger' || type === false) ? 'Terjadi kesalahan sistem.' : 'Perubahan data berhasil disimpan.';
    }
    let container = document.getElementById('atlas-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'atlas-toast-container';
        container.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 1000000; display: flex; flex-direction: column; gap: 10px; pointer-events: none; max-width: 440px; width: calc(100% - 48px);';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.role = 'status';
    toast.ariaLive = 'polite';

    const isSuccess = type === 'success' || type === true;
    const isError = type === 'error' || type === false || type === 'danger';
    const isWarning = type === 'warning';

    let bg = '#107C41'; // emerald
    let icon = '<svg style="width: 18px; height: 18px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

    if (isError) {
        bg = '#B43D3D'; // ruby
        icon = '<svg style="width: 18px; height: 18px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    } else if (isWarning) {
        bg = '#D97706'; // amber
        icon = '<svg style="width: 18px; height: 18px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3ZM12 9v4m0 4h.01"/></svg>';
    } else if (type === 'info') {
        bg = '#0E2A47'; // deep sapphire
        icon = '<svg style="width: 18px; height: 18px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
    }

    toast.style.cssText = `display: flex; align-items: center; gap: 12px; background: ${bg}; color: #ffffff; padding: 12px 18px; border-radius: 8px; box-shadow: 0 10px 25px rgba(14, 42, 71, 0.25); font-size: 13.5px; font-weight: 500; pointer-events: auto; transform: translateY(20px); opacity: 0; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; border: 1px solid rgba(255, 255, 255, 0.15);`;
    toast.innerHTML = `${icon}<span style="flex: 1; line-height: 1.4;">${message}</span><button type="button" style="background: none; border: none; color: rgba(255, 255, 255, 0.8); cursor: pointer; padding: 2px; display: flex; align-items: center; justify-content: center; margin-left: 4px;" aria-label="Tutup"><svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;

    const closeBtn = toast.querySelector('button');
    const dismiss = () => {
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 250);
    };
    if (closeBtn) closeBtn.addEventListener('click', dismiss);

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });

    setTimeout(dismiss, 4000);
}

window.openAtlasDialog = openAtlasDialog;
window.closeAtlasDialog = closeAtlasDialog;
window.confirmAtlas = confirmAtlas;
window.closeConfirmAtlas = closeConfirmAtlas;
window.showToast = showToast;

function initAtlasDialogs() {
    // Tombol konfirmasi global
    document.addEventListener('click', (event) => {
        if (event.target.closest('#atlas-confirm-btn-cancel')) {
            closeConfirmAtlas(false);
            return;
        }
        if (event.target.closest('#atlas-confirm-btn-proceed')) {
            closeConfirmAtlas(true);
            return;
        }

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
        if (backdrop) {
            if (backdrop.id === 'atlas-confirm-dialog') {
                closeConfirmAtlas(false);
            } else {
                closeAtlasDialog(backdrop);
            }
        }
    });

    // Escape menutup dialog teratas yang terbuka.
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const confirmDialog = document.getElementById('atlas-confirm-dialog');
        if (confirmDialog && confirmDialog.hasAttribute('open')) {
            closeConfirmAtlas(false);
            return;
        }
        const open = [...document.querySelectorAll('.atlas-dialog[open]')].pop();
        if (open) closeAtlasDialog(open);
    });

    // Intersep submit form yang memiliki atribut data-confirm
    document.addEventListener('submit', function(event) {
        const form = event.target;
        // Pengecualian logout: jangan konfirmasi form logout
        if (form.action && form.action.includes('/logout')) return;

        const confirmMsg = form.dataset.confirm || event.submitter?.dataset?.confirm;
        if (!confirmMsg) return;

        if (form.dataset.atlasConfirmed === 'true') {
            form.removeAttribute('data-atlas-confirmed');
            return; // Lanjutkan pengiriman asli
        }

        event.preventDefault();

        const title = form.dataset.confirmTitle || event.submitter?.dataset?.confirmTitle || 'Konfirmasi Tindakan';
        const methodInput = form.querySelector('input[name="_method"]');
        const isDelete = methodInput && methodInput.value.toUpperCase() === 'DELETE';
        const type = form.dataset.confirmType || event.submitter?.dataset?.confirmType || (isDelete ? 'danger' : 'primary');
        const confirmBtn = form.dataset.confirmButton || event.submitter?.dataset?.confirmButton || (isDelete ? 'Ya, Hapus Data' : 'Ya, Lanjutkan');
        const cancelBtn = form.dataset.confirmCancel || event.submitter?.dataset?.confirmCancel || 'Batal';

        window.confirmAtlas({
            title: title,
            message: confirmMsg,
            type: type,
            confirmText: confirmBtn,
            cancelText: cancelBtn
        }).then(confirmed => {
            if (confirmed) {
                form.dataset.atlasConfirmed = 'true';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(event.submitter || undefined);
                } else {
                    form.submit();
                }
            }
        });
    });

    // Intersep link atau button non-submit dengan data-confirm (misal tombol hapus AJAX atau link)
    document.addEventListener('click', function(event) {
        const trigger = event.target.closest('button[data-confirm]:not([type="submit"]), a[data-confirm]');
        if (!trigger) return;

        if (trigger.dataset.atlasConfirmed === 'true') {
            trigger.removeAttribute('data-atlas-confirmed');
            return;
        }

        event.preventDefault();

        const title = trigger.dataset.confirmTitle || 'Konfirmasi Tindakan';
        const message = trigger.dataset.confirm;
        const type = trigger.dataset.confirmType || 'warning';
        const confirmBtn = trigger.dataset.confirmButton || 'Ya, Lanjutkan';
        const cancelBtn = trigger.dataset.confirmCancel || 'Batal';

        window.confirmAtlas({
            title, message, type, confirmText: confirmBtn, cancelText: cancelBtn
        }).then(confirmed => {
            if (confirmed) {
                if (trigger.tagName === 'A' && trigger.href) {
                    window.location.href = trigger.href;
                } else if (typeof trigger.onclick === 'function') {
                    trigger.dataset.atlasConfirmed = 'true';
                    trigger.onclick();
                }
            }
        });
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

export function toDmsString(lat, lng) {
    if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return '';
    const latDir = lat < 0 ? 'S' : 'N';
    const latAbs = Math.abs(lat);
    let latDeg = Math.floor(latAbs);
    let latMinRem = (latAbs - latDeg) * 60;
    let latMin = Math.floor(latMinRem);
    let latSec = Math.round((latMinRem - latMin) * 60 * 10) / 10;
    if (latSec >= 60) { latMin += 1; latSec = 0; }
    if (latMin >= 60) { latDeg += 1; latMin = 0; }

    const lngDir = lng < 0 ? 'W' : 'E';
    const lngAbs = Math.abs(lng);
    let lngDeg = Math.floor(lngAbs);
    let lngMinRem = (lngAbs - lngDeg) * 60;
    let lngMin = Math.floor(lngMinRem);
    let lngSec = Math.round((lngMinRem - lngMin) * 60 * 10) / 10;
    if (lngSec >= 60) { lngMin += 1; lngSec = 0; }
    if (lngMin >= 60) { lngDeg += 1; lngMin = 0; }

    const latSecStr = latSec.toFixed(1);
    const lngSecStr = lngSec.toFixed(1);

    return `${latDeg}°${latMin}'${latSecStr}"${latDir} ${lngDeg}°${lngMin}'${lngSecStr}"${lngDir}`;
}

export function parseCoordinates(raw) {
    if (!raw || typeof raw !== 'string') return null;
    let str = raw.trim();
    str = str.replace(/^[\(\[\{]+|[\)\]\}]+$/g, '').trim();
    if (!str) return null;

    // 1. Decimal format e.g. -2.433306, 115.680333 or 2.433306 S, 115.680333 E
    const decRegex = /^([+-]?\d+(?:\.\d+)?)\s*([NSns]?)[,\s;]+([+-]?\d+(?:\.\d+)?)\s*([EWew]?)$/;
    const decMatch = str.match(decRegex);
    if (decMatch) {
        let lat = parseFloat(decMatch[1]);
        const latCard = (decMatch[2] || '').toUpperCase();
        let lng = parseFloat(decMatch[3]);
        const lngCard = (decMatch[4] || '').toUpperCase();

        if (latCard === 'S' && lat > 0) lat = -lat;
        if (latCard === 'N' && lat < 0) lat = Math.abs(lat);
        if (lngCard === 'W' && lng > 0) lng = -lng;
        if (lngCard === 'E' && lng < 0) lng = Math.abs(lng);

        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            return {
                latitude: Number(lat.toFixed(6)),
                longitude: Number(lng.toFixed(6)),
                dms: toDmsString(lat, lng)
            };
        }
    }

    // 2. Normalize DMS symbols: 2°25'59.9"S 115°40'49.2"E
    const normalized = str
        .replace(/[’‘`′]/g, "'")
        .replace(/[”“″]/g, '"')
        .replace(/[º]/g, '°');

    const dmsRegex = /(\d+)\s*[°dD\s]\s*(\d+)\s*['mM\s]\s*(\d+(?:\.\d+)?)\s*["sS\s]*([NSnsEWew])?[\s,;]+(\d+)\s*[°dD\s]\s*(\d+)\s*['mM\s]\s*(\d+(?:\.\d+)?)\s*["sS\s]*([NSnsEWew])?/;
    const dmsMatch = normalized.match(dmsRegex);
    if (dmsMatch) {
        const latDeg = parseFloat(dmsMatch[1]);
        const latMin = parseFloat(dmsMatch[2]);
        const latSec = parseFloat(dmsMatch[3]);
        const latDir = (dmsMatch[4] || 'S').toUpperCase();

        const lngDeg = parseFloat(dmsMatch[5]);
        const lngMin = parseFloat(dmsMatch[6]);
        const lngSec = parseFloat(dmsMatch[7]);
        const lngDir = (dmsMatch[8] || 'E').toUpperCase();

        let lat = latDeg + (latMin / 60) + (latSec / 3600);
        let lng = lngDeg + (lngMin / 60) + (lngSec / 3600);

        if (latDir === 'S') lat = -lat;
        if (lngDir === 'W') lng = -lng;

        if (Math.abs(lat) > 90 && Math.abs(lng) <= 90) {
            const t = lat; lat = lng; lng = t;
        }

        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            return {
                latitude: Number(lat.toFixed(6)),
                longitude: Number(lng.toFixed(6)),
                dms: toDmsString(lat, lng)
            };
        }
    }

    return null;
}

window.toDmsString = toDmsString;
window.parseCoordinates = parseCoordinates;
