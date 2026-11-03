/**
 * Atlas Table Controller: Live Search, Debounce, Skeleton Loading, and SPA-like Paging.
 * SIGAP-TRANS Kalsel.
 *
 * Mengotomatisasi live-filter pada seluruh tabel data bertipe .atlas-panel:
 * - Debounce 380ms pada input pencarian teks (kursor & fokus tetap aktif).
 * - Trigger 50ms saat dropdown filter diubah.
 * - Animasi skeleton loading terukur sesuai jumlah kolom tabel.
 * - Swapping HTML fragment via fetch() & DOMParser tanpa me-reload browser.
 * - Sinkronisasi URL & tombol Back/Forward browser (history.pushState & popstate).
 */

function createSkeletonHtml(columnsCount = 6, rowCount = 6) {
    let rowsHtml = '';
    for (let r = 0; r < rowCount; r++) {
        let cellsHtml = '';
        for (let c = 0; c < columnsCount; c++) {
            if (c === 0) {
                const w1 = [65, 75, 55, 80][r % 4];
                const w2 = [35, 45, 30, 40][r % 4];
                cellsHtml += `
                    <td>
                        <div class="atlas-skeleton-stack">
                            <span class="atlas-skeleton-line" style="width: ${w1}%;"></span>
                            <span class="atlas-skeleton-line" style="width: ${w2}%; height: 10px; opacity: 0.7;"></span>
                        </div>
                    </td>
                `;
            } else if (c === columnsCount - 1) {
                cellsHtml += `
                    <td>
                        <div class="inline-flex gap-2">
                            <span class="atlas-skeleton-btn"></span>
                        </div>
                    </td>
                `;
            } else if (c === 2) {
                cellsHtml += `
                    <td>
                        <span class="atlas-skeleton-pill"></span>
                    </td>
                `;
            } else {
                const w = [50, 70, 45, 60][(r + c) % 4];
                cellsHtml += `
                    <td>
                        <span class="atlas-skeleton-line" style="width: ${w}%;"></span>
                    </td>
                `;
            }
        }
        rowsHtml += `<tr class="atlas-skeleton-row" aria-hidden="true">${cellsHtml}</tr>`;
    }
    return rowsHtml;
}

export function initAtlasTables() {
    const panels = document.querySelectorAll('.atlas-panel');

    panels.forEach((panel) => {
        const form = panel.querySelector('form');
        const table = panel.querySelector('.atlas-table');
        const tableScroll = panel.querySelector('.atlas-table-scroll');

        // Hanya inisialisasi panel yang memiliki form filter dan tabel data
        if (!form || !table) return;

        // Cegah inisialisasi ganda
        if (panel.dataset.atlasTableInitialized === 'true') return;
        panel.dataset.atlasTableInitialized = 'true';

        // Pastikan loading-bar ada di atas tabel
        if (tableScroll && !tableScroll.querySelector('.atlas-table-loading-bar')) {
            const bar = document.createElement('div');
            bar.className = 'atlas-table-loading-bar';
            bar.setAttribute('aria-hidden', 'true');
            tableScroll.prepend(bar);
        }

        // Tambahkan spinner indikator pada search field jika ada
        const searchField = form.querySelector('.atlas-field--search');
        if (searchField && !searchField.querySelector('.atlas-search-spinner')) {
            const spinner = document.createElement('div');
            spinner.className = 'atlas-search-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            searchField.appendChild(spinner);
        }

        let debounceTimer = null;
        let activeAbortController = null;

        const getColumnCount = () => {
            const ths = table.querySelectorAll('thead th');
            return ths.length || 6;
        };

        let previousTbodyHtml = '';
        const showLoading = () => {
            panel.setAttribute('data-atlas-loading', 'true');
            table.setAttribute('aria-busy', 'true');
            if (searchField) {
                searchField.setAttribute('data-loading', 'true');
            }

            const tbody = table.querySelector('tbody');
            if (tbody) {
                if (!previousTbodyHtml) {
                    previousTbodyHtml = tbody.innerHTML;
                }
                tbody.innerHTML = createSkeletonHtml(getColumnCount(), 6);
            }
        };

        const hideLoading = () => {
            panel.removeAttribute('data-atlas-loading');
            table.removeAttribute('aria-busy');
            if (searchField) {
                searchField.removeAttribute('data-loading');
            }
        };

        // Fungsi utama fetch data dan swap elemen
        const executeFetch = async (targetUrl, updateHistory = true) => {
            // Batalkan request yang sedang berjalan jika ada
            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();

            showLoading();

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml',
                    },
                    signal: activeAbortController.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');

                // 1. Perbarui TBODY
                const newTable = doc.querySelector('.atlas-table');
                const currentTbody = table.querySelector('tbody');
                if (newTable && currentTbody) {
                    const newTbody = newTable.querySelector('tbody');
                    if (newTbody) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                    }
                }

                // 2. Perbarui HEADING (misal: "X lokasi ditemukan")
                const newHeading = doc.querySelector('.atlas-panel-heading');
                const currentHeading = panel.querySelector('.atlas-panel-heading');
                if (newHeading && currentHeading) {
                    currentHeading.innerHTML = newHeading.innerHTML;
                }

                // 3. Perbarui FOOTER & PAGINATION
                const newFooter = doc.querySelector('.atlas-table-footer');
                const currentFooter = panel.querySelector('.atlas-table-footer');
                if (newFooter && currentFooter) {
                    currentFooter.innerHTML = newFooter.innerHTML;
                } else if (newFooter && !currentFooter) {
                    panel.appendChild(newFooter);
                }

                // 4. Perbarui FILTER CHIPS jika ada
                const newChips = doc.querySelector('.atlas-filter-chips');
                const currentChips = panel.querySelector('.atlas-filter-chips');
                if (newChips && currentChips) {
                    currentChips.innerHTML = newChips.innerHTML;
                } else if (newChips && !currentChips) {
                    // Masukkan chips setelah form
                    form.after(newChips);
                } else if (!newChips && currentChips) {
                    currentChips.remove();
                }

                // 5. Perbarui METRICS STRIP (jika halaman memiliki rekap .atlas-metrics di luar panel)
                const newMetrics = doc.querySelector('.atlas-metrics');
                const currentMetrics = document.querySelector('.atlas-metrics');
                if (newMetrics && currentMetrics) {
                    currentMetrics.innerHTML = newMetrics.innerHTML;
                }

                // 6. Sinkronkan nilai kontrol form jika dipicu oleh pagination / chips click
                syncFormInputsFromUrl(form, targetUrl);

                // 7. Update URL di Address Bar
                if (updateHistory) {
                    window.history.pushState({ atlasUrl: targetUrl }, '', targetUrl);
                }
                // Reset snapshot tabel sebelumnya jika sukses
                previousTbodyHtml = '';
            } catch (err) {
                if (err.name === 'AbortError') {
                    // Permintaan dibatalkan karena ada ketikan baru, abaikan
                    return;
                }
                console.error('[Atlas Table] Gagal memuat data:', err);
                // Kembalikan isi tabel sebelumnya jika fetch gagal agar tidak stuck skeleton
                const tbody = table.querySelector('tbody');
                if (tbody && previousTbodyHtml) {
                    tbody.innerHTML = previousTbodyHtml;
                    previousTbodyHtml = '';
                }
            } finally {
                hideLoading();
            }
        };

        // Buat URL dari data form saat ini
        const buildUrlFromForm = () => {
            const action = form.getAttribute('action') || window.location.pathname;
            const url = new URL(action, window.location.origin);
            const formData = new FormData(form);

            // Bersihkan parameter 'page' saat filter baru diterapkan
            for (const [key, value] of formData.entries()) {
                const trimmed = typeof value === 'string' ? value.trim() : value;
                if (trimmed !== '' && trimmed !== 'all') {
                    url.searchParams.set(key, trimmed);
                }
            }

            return url.toString();
        };

        // Listener Debounce Input Teks (Pencarian, Tahun, dll)
        const textInputs = form.querySelectorAll('input[type="search"], input[name="search"], input[type="text"]:not([readonly])');
        textInputs.forEach((input) => {
            input.addEventListener('input', () => {
                if (searchField) {
                    searchField.setAttribute('data-loading', 'true');
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const targetUrl = buildUrlFromForm();
                    executeFetch(targetUrl);
                }, 380);
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    const targetUrl = buildUrlFromForm();
                    executeFetch(targetUrl);
                }
            });
        });

        // Listener Dropdown Select (Kabupaten, Status, Pola Usaha, Baris per halaman)
        const selectInputs = form.querySelectorAll('select');
        selectInputs.forEach((select) => {
            select.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const targetUrl = buildUrlFromForm();
                    executeFetch(targetUrl);
                }, 50);
            });
        });

        // Listener Input Tanggal / Flatpickr
        const dateFilterInputs = form.querySelectorAll('input[type="date"], input.atlas-datepicker, input[data-atlas-datepicker]');
        dateFilterInputs.forEach((dateInput) => {
            dateInput.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const targetUrl = buildUrlFromForm();
                    executeFetch(targetUrl);
                }, 50);
            });
        });

        // Cegah submit bawaan browser pada form (misal klik tombol Terapkan)
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(debounceTimer);
            const targetUrl = buildUrlFromForm();
            executeFetch(targetUrl);
        });

        // Delegasi Klik Paginasi & Filter Chips di dalam Panel
        panel.addEventListener('click', (e) => {
            // Klik link pagination (.atlas-table-footer a)
            const paginationLink = e.target.closest('.atlas-table-footer a');
            if (paginationLink && paginationLink.href) {
                e.preventDefault();
                executeFetch(paginationLink.href);
                // Scroll halus ke atas tabel jika tabel panjang
                tableScroll?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                return;
            }

            // Klik link filter chip (.atlas-filter-chips a)
            const chipLink = e.target.closest('.atlas-filter-chips a');
            if (chipLink && chipLink.href) {
                e.preventDefault();
                executeFetch(chipLink.href);
                return;
            }
        });

        // Dukungan Tombol Back / Forward di Browser
        window.addEventListener('popstate', (e) => {
            executeFetch(window.location.href, false);
        });
    });
}

/**
 * Memastikan nilai input form tetap selaras dengan parameter URL
 * saat pengguna mengklik chip penghapus filter atau tombol pagination.
 */
function syncFormInputsFromUrl(form, urlString) {
    try {
        const url = new URL(urlString, window.location.origin);
        const params = url.searchParams;

        // Sync inputs
        form.querySelectorAll('input[name]').forEach((input) => {
            const val = params.get(input.name);
            const nextVal = val !== null ? val : '';
            input.value = nextVal;
            if (input._flatpickr) {
                if (nextVal) {
                    input._flatpickr.setDate(nextVal, false);
                } else {
                    input._flatpickr.clear(false);
                }
            }
        });

        // Sync selects
        form.querySelectorAll('select[name]').forEach((select) => {
            const val = params.get(select.name);
            select.value = val !== null ? val : 'all';
        });
    } catch {}
}

// Inisialisasi otomatis saat DOM siap
document.addEventListener('DOMContentLoaded', initAtlasTables);
