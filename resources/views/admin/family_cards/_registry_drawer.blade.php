<!-- ============================================================== -->
<!-- DRAWER & MODAL BUKU REGISTRI WARGA TRANSMIGRAN (OPSI C HIBRIDA) -->
<!-- ============================================================== -->
<div id="registry-drawer" class="fixed inset-0 z-50 hidden overflow-hidden" style="z-index: 50;" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop Gelap -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300" onclick="closeRegistryDrawer()"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-16">
        <div class="w-screen max-w-5xl bg-slate-50 border-l border-slate-200 shadow-2xl flex flex-col justify-between">
            
            <!-- 1. HEADER DRAWER -->
            <div class="p-6 bg-[#0B1849] text-white border-b border-slate-800 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#E4B028] text-slate-950" id="reg-badge-stage">
                            PENEMPATAN AWAL
                        </span>
                        <span class="text-xs text-slate-300 font-mono" id="reg-upt-code">UPT-001</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-[#EBEDE3] tracking-tight flex items-center gap-2" id="reg-upt-title">
                        Buku Registri Warga Transmigran
                    </h2>
                    <p class="text-xs text-slate-300 mt-1" id="reg-upt-sub">
                        Pencatatan nominal per KK transmigran, asal daerah, kapling pekarangan, dan status SHM.
                    </p>
                </div>
                <button type="button" onclick="closeRegistryDrawer()" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- 2. SUMMARY KPI & TOOLBAR AKSI -->
            <div class="p-5 bg-white border-b border-slate-200/80 shadow-xs space-y-4">
                <!-- Mini KPI Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100">
                        <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Total KK Registri</div>
                        <div class="text-xl font-black text-emerald-950 tabular-nums mt-0.5" id="reg-stat-kk">0 <span class="text-xs font-normal text-slate-600">KK</span></div>
                        <div class="text-[10px] text-slate-500 mt-0.5" id="reg-stat-rekap-compare">Rekap UPT: 0 KK</div>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100">
                        <div class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">Total Jiwa Registri</div>
                        <div class="text-xl font-black text-blue-950 tabular-nums mt-0.5" id="reg-stat-pop">0 <span class="text-xs font-normal text-slate-600">Jiwa</span></div>
                        <div class="text-[10px] text-slate-500 mt-0.5" id="reg-stat-avg">Rata-rata: 0 Jiwa/KK</div>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-100">
                        <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Komposisi Transmigran</div>
                        <div class="text-xs font-black text-amber-950 mt-1 flex items-center gap-2">
                            <span class="px-1.5 py-0.5 bg-amber-200/60 rounded text-[10px]" id="reg-stat-tpa">TPA: 0 KK</span>
                            <span class="px-1.5 py-0.5 bg-amber-200/60 rounded text-[10px]" id="reg-stat-tps">TPS: 0 KK</span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1">Penduduk Asal vs Setempat</div>
                    </div>
                    <div class="p-3 rounded-xl bg-purple-50/70 border border-purple-100">
                        <div class="text-[11px] font-bold text-purple-800 uppercase tracking-wider">Status Hak Milik (SHM)</div>
                        <div class="text-xl font-black text-purple-950 tabular-nums mt-0.5" id="reg-stat-shm">0 <span class="text-xs font-normal text-slate-600">SHM</span></div>
                        <div class="text-[10px] text-slate-500 mt-0.5">Sudah Bersertipikat Hak Milik</div>
                    </div>
                </div>

                <!-- Action Buttons & Quick Filter Toolbar -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="openAddCardModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#124D1C] hover:bg-[#0E3B15] text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <span>Tambah 1 KK</span>
                        </button>
                        <button type="button" onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 font-bold text-xs transition shadow-xs cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Import Excel/CSV</span>
                        </button>
                        <a id="btn-export-reg" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition shadow-xs">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Export CSV</span>
                        </a>
                        <button type="button" onclick="syncRegistryToUpt()" id="btn-sync-rekap" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs shadow-xs transition cursor-pointer" title="Perbarui total KK dan Jiwa tabel utama UPT dengan data registri ini">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sinkronkan ke Rekap UPT</span>
                        </button>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex items-center gap-2 flex-1 max-w-sm ml-auto">
                        <div class="relative w-full">
                            <input type="text" id="reg-search" oninput="filterRegistryCards()" placeholder="Cari Nama, NIK, No KK, Blok..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TABEL DAFTAR WARGA (SCROLLABLE BODY) -->
            <div class="flex-1 overflow-y-auto p-5">
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Kepala Keluarga</th>
                                <th class="py-3 px-3 text-center">No. KK & NIK</th>
                                <th class="py-3 px-3 text-center">Jiwa</th>
                                <th class="py-3 px-3 text-center">Jenis / Asal</th>
                                <th class="py-3 px-3 text-center">Blok Kapling</th>
                                <th class="py-3 px-3 text-center">Status SHM</th>
                                <th class="py-3 px-3">Catatan</th>
                                <th class="py-3 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reg-table-body" class="divide-y divide-slate-100">
                            <!-- Populated via Javascript -->
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-[#0B1849] mb-2"></div>
                                    <div>Memuat data registri warga...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. FOOTER DRAWER -->
            <div class="p-4 bg-white border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <div id="reg-footer-info">
                    Menampilkan 0 data KK transmigran
                </div>
                <button type="button" onclick="closeRegistryDrawer()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Tutup Registri
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL TAMBAH / EDIT 1 DATA KK TRANSMIGRAN -->
<!-- ============================================================== -->
<div id="modal-card-form" class="fixed inset-0 z-[70] hidden overflow-y-auto" style="z-index: 70;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="closeCardFormModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
            <!-- Modal Header -->
            <div class="bg-[#0B1849] text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#EBEDE3]" id="card-form-title">
                        Tambah Data KK Transmigran
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5" id="card-form-subtitle">
                        Identitas nominal kepala keluarga, kapling, dan sertipikat
                    </p>
                </div>
                <button type="button" onclick="closeCardFormModal()" class="text-slate-300 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form -->
            <form id="form-family-card" onsubmit="submitCardForm(event)" class="p-6 space-y-4">
                <input type="hidden" id="form-card-id" value="">

                <!-- Nama Lengkap Kepala Keluarga -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Kepala Keluarga <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="form-head-name" required placeholder="Contoh: Slamet Riyadi" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                </div>

                <!-- Grid Identitas: No KK & NIK -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            No. Kartu Keluarga (KK)
                        </label>
                        <input type="text" id="form-kk-number" maxlength="30" placeholder="16 Digit No KK" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 font-mono focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            NIK Kepala Keluarga
                        </label>
                        <input type="text" id="form-nik" maxlength="30" placeholder="16 Digit NIK" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 font-mono focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                </div>

                <!-- Grid: Jumlah Jiwa & Jenis Transmigran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Jumlah Jiwa dalam 1 KK <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="form-members-count" required min="1" max="30" value="1" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 font-bold focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Jenis Transmigran <span class="text-red-500">*</span>
                        </label>
                        <select id="form-trans-type" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                            <option value="TPA">TPA - Penduduk Asal (Luar Kalsel)</option>
                            <option value="TPS">TPS - Penduduk Setempat (Lokal Kalsel)</option>
                        </select>
                    </div>
                </div>

                <!-- Grid: Asal Provinsi & Asal Kabupaten -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Asal Provinsi
                        </label>
                        <input type="text" id="form-origin-province" placeholder="Contoh: Jawa Tengah" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Asal Kabupaten / Kota
                        </label>
                        <input type="text" id="form-origin-regency" placeholder="Contoh: Banyumas" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                </div>

                <!-- Grid: Blok/Kapling & Status SHM -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Blok / No. Kapling Rumah
                        </label>
                        <input type="text" id="form-housing-block" placeholder="Contoh: Blok B No. 12" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Status Sertipikat SHM <span class="text-red-500">*</span>
                        </label>
                        <select id="form-shm-status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]">
                            <option value="Sudah SHM">Sudah SHM</option>
                            <option value="Proses BPN">Proses BPN (Redistribusi/PTSL)</option>
                            <option value="Belum SHM">Belum SHM</option>
                            <option value="Sengketa">Sengketa / Klaim Pihak Ketiga</option>
                        </select>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Catatan / Riwayat Warga
                    </label>
                    <textarea id="form-notes" rows="2" placeholder="Catatan mutasi, ahli waris, atau keterangan lahan..." class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-[#0B1849]"></textarea>
                </div>

                <!-- Form Buttons -->
                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeCardFormModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-card" class="px-5 py-2.5 rounded-xl bg-[#124D1C] hover:bg-[#0E3B15] text-white font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <span id="btn-save-card-text">Simpan Data KK</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL IMPORT EXCEL / CSV WARGA TRANSMIGRAN -->
<!-- ============================================================== -->
<div id="modal-import-cards" class="fixed inset-0 z-[70] hidden overflow-y-auto" style="z-index: 70;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="closeImportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
            <!-- Header -->
            <div class="bg-[#0B1849] text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#EBEDE3]">
                        Import Data Registri Warga Transmigran
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Unggah berkas Excel/CSV untuk mencatat banyak KK sekaligus
                    </p>
                </div>
                <button type="button" onclick="closeImportModal()" class="text-slate-300 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Petunjuk & Download Template -->
            <div class="p-6 space-y-4">
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200/80 text-xs text-amber-950 space-y-2">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Panduan Format Berkas Excel / CSV:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 pl-1">
                        <li>Gunakan template resmi agar susunan kolom sesuai sistem.</li>
                        <li>Kolom wajib: <strong>Nama Lengkap Kepala Keluarga</strong> dan <strong>Jumlah Jiwa</strong>.</li>
                        <li>Jenis transmigran diisi <strong>TPA</strong> (Penduduk Asal) atau <strong>TPS</strong> (Penduduk Setempat).</li>
                    </ul>
                    <div class="pt-2">
                        <a id="btn-download-template" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0B1849] hover:bg-[#14266c] text-[#EBEDE3] font-bold text-xs shadow-xs transition">
                            <svg class="w-3.5 h-3.5 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Unduh Contoh Template CSV / Excel</span>
                        </a>
                    </div>
                </div>

                <!-- Form Upload -->
                <form id="form-import-cards" onsubmit="submitImportCards(event)" class="space-y-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Berkas (.csv, .xlsx, .xls) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="import-file-input" required accept=".csv, .xlsx, .xls" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#124D1C]/10 file:text-[#124D1C] hover:file:bg-[#124D1C]/20 border border-slate-300 rounded-xl p-2 cursor-pointer">
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-import" class="px-5 py-2.5 rounded-xl bg-[#124D1C] hover:bg-[#0E3B15] text-white font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                            <span id="btn-import-text">Unggah & Proses Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT REGISTRI WARGA (OPSI C HIBRIDA) -->
<!-- ============================================================== -->
<script>
let currentRegistryUptId = null;
let currentRegistryStage = 'placement'; // 'placement' atau 'handover'
let currentCardsData = [];

/**
 * Buka Drawer Buku Registri Warga untuk UPT dan Tahapan Tertentu
 */
function openRegistryModal(uptId, uptName, stage) {
    currentRegistryUptId = uptId;
    currentRegistryStage = stage;

    const drawer = document.getElementById('registry-drawer');
    const badgeStage = document.getElementById('reg-badge-stage');
    const uptTitle = document.getElementById('reg-upt-title');
    const uptCode = document.getElementById('reg-upt-code');
    const btnExport = document.getElementById('btn-export-reg');
    const btnTemplate = document.getElementById('btn-download-template');
    const searchInput = document.getElementById('reg-search');

    if (searchInput) searchInput.value = '';

    if (stage === 'handover') {
        badgeStage.textContent = 'SERAH TERIMA PEMDA';
        badgeStage.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-500 text-white';
    } else {
        badgeStage.textContent = 'PENEMPATAN AWAL';
        badgeStage.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#E4B028] text-slate-950';
    }

    uptTitle.textContent = uptName;
    uptCode.textContent = `ID #${uptId}`;

    btnExport.href = `/admin/family-cards/upt/${uptId}/export?stage=${stage}`;
    btnTemplate.href = `/admin/family-cards/template/download?stage=${stage}`;

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Ambil Data Registri via AJAX
    fetchRegistryCards();
}

/**
 * Tutup Drawer Registri Warga
 */
function closeRegistryDrawer() {
    const drawer = document.getElementById('registry-drawer');
    drawer.classList.add('hidden');
    document.body.style.overflow = '';
}

/**
 * Request AJAX data registri warga
 */
function fetchRegistryCards() {
    const tableBody = document.getElementById('reg-table-body');
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="py-12 text-center text-slate-400">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-[#0B1849] mb-2"></div>
                <div>Memuat data registri warga...</div>
            </td>
        </tr>
    `;

    fetch(`/admin/family-cards/upt/${currentRegistryUptId}?stage=${currentRegistryStage}`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                currentCardsData = res.data || [];
                updateRegistryStats(res.stats, res.upt);
                renderRegistryTable(currentCardsData);
            } else {
                tableBody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-red-500 font-bold">Gagal memuat data registri.</td></tr>`;
            }
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-red-500 font-bold">Terjadi kesalahan koneksi server.</td></tr>`;
        });
}

/**
 * Render Tabel Warga
 */
function renderRegistryTable(cards) {
    const tableBody = document.getElementById('reg-table-body');
    const footerInfo = document.getElementById('reg-footer-info');

    if (!cards || cards.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="py-16 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <div class="font-bold text-slate-700 text-sm">Belum Ada Data Registri Nama KK</div>
                    <div class="text-xs text-slate-400 max-w-sm mx-auto mt-1">UPT ini baru memiliki data rekap angka agregat. Anda dapat menambahkan 1 KK secara manual atau mengunggah berkas Excel warga.</div>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <button type="button" onclick="openAddCardModal()" class="px-3.5 py-1.5 rounded-xl bg-[#124D1C] text-white text-xs font-bold hover:bg-[#0E3B15] transition shadow-xs cursor-pointer">+ Tambah 1 KK</button>
                        <button type="button" onclick="openImportModal()" class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 text-slate-800 text-xs font-bold hover:bg-slate-50 transition shadow-xs cursor-pointer">Import File Excel</button>
                    </div>
                </td>
            </tr>
        `;
        footerInfo.textContent = 'Menampilkan 0 data KK transmigran';
        return;
    }

    footerInfo.textContent = `Menampilkan ${cards.length} data KK transmigran`;

    let html = '';
    cards.forEach((c, idx) => {
        const typeBadge = c.transmigrant_type === 'TPA'
            ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">TPA (Asal)</span>'
            : '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">TPS (Lokal)</span>';

        let shmBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Belum SHM</span>';
        if (c.land_certificate_status && c.land_certificate_status.toLowerCase().includes('sudah')) {
            shmBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">Sudah SHM</span>';
        } else if (c.land_certificate_status && c.land_certificate_status.toLowerCase().includes('proses')) {
            shmBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">Proses BPN</span>';
        }

        const origin = [c.origin_province, c.origin_regency].filter(Boolean).join(' - ') || '-';

        html += `
            <tr class="hover:bg-slate-50/80 transition" id="reg-row-${c.id}">
                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">${idx + 1}</td>
                <td class="py-2.5 px-3 font-extrabold text-slate-900">
                    <div>${c.head_of_family_name}</div>
                </td>
                <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600">
                    <div>KK: <strong class="text-slate-800">${c.family_card_number || '-'}</strong></div>
                    <div class="text-[10px] text-slate-400">NIK: ${c.nik || '-'}</div>
                </td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-800 tabular-nums">
                    ${c.family_members_count} <span class="text-[10px] font-normal text-slate-400">Jiwa</span>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <div>${typeBadge}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">${origin}</div>
                </td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-700">
                    ${c.housing_block || '-'}
                </td>
                <td class="py-2.5 px-3 text-center">
                    ${shmBadge}
                </td>
                <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-xs truncate">
                    ${c.notes || '-'}
                </td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button" onclick="openEditCardModal(${c.id})" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition cursor-pointer" title="Edit Data KK">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button type="button" onclick="deleteCard(${c.id}, '${addslashes(c.head_of_family_name)}')" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition cursor-pointer" title="Hapus Data KK">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

/**
 * Filter registri kartu di client side
 */
function filterRegistryCards() {
    const keyword = document.getElementById('reg-search').value.toLowerCase().trim();
    if (!keyword) {
        renderRegistryTable(currentCardsData);
        return;
    }

    const filtered = currentCardsData.filter(c => {
        return (c.head_of_family_name && c.head_of_family_name.toLowerCase().includes(keyword)) ||
               (c.family_card_number && c.family_card_number.toLowerCase().includes(keyword)) ||
               (c.nik && c.nik.toLowerCase().includes(keyword)) ||
               (c.housing_block && c.housing_block.toLowerCase().includes(keyword)) ||
               (c.origin_province && c.origin_province.toLowerCase().includes(keyword)) ||
               (c.origin_regency && c.origin_regency.toLowerCase().includes(keyword));
    });

    renderRegistryTable(filtered);
}

/**
 * Perbarui Widget Statistik Mini di Drawer
 */
function updateRegistryStats(stats, upt) {
    if (!stats) return;
    document.getElementById('reg-stat-kk').innerHTML = `${Number(stats.total_kk).toLocaleString('id-ID')} <span class="text-xs font-normal text-slate-600">KK</span>`;
    document.getElementById('reg-stat-pop').innerHTML = `${Number(stats.total_jiwa).toLocaleString('id-ID')} <span class="text-xs font-normal text-slate-600">Jiwa</span>`;
    document.getElementById('reg-stat-avg').textContent = `Rata-rata: ${Number(stats.avg_jiwa).toLocaleString('id-ID', {minimumFractionDigits: 2})} Jiwa/KK`;
    document.getElementById('reg-stat-tpa').textContent = `TPA: ${stats.tpa_count} KK`;
    document.getElementById('reg-stat-tps').textContent = `TPS: ${stats.tps_count} KK`;
    document.getElementById('reg-stat-shm').innerHTML = `${Number(stats.shm_count).toLocaleString('id-ID')} <span class="text-xs font-normal text-slate-600">SHM</span>`;

    if (upt) {
        document.getElementById('reg-stat-rekap-compare').textContent = `Rekap UPT: ${Number(upt.current_aggregate_kk).toLocaleString('id-ID')} KK`;
    }

    // Perbarui juga badge pada baris tabel utama jika ada
    const badgeRow = document.getElementById(`badge-nominal-${currentRegistryUptId}`);
    if (badgeRow) {
        if (stats.total_kk > 0) {
            badgeRow.textContent = `${stats.total_kk} KK Terdata`;
            badgeRow.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800';
        } else {
            badgeRow.textContent = '0 KK (Belum Diisi)';
            badgeRow.className = 'px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500';
        }
    }
}

/**
 * Buka Modal Tambah 1 KK
 */
function openAddCardModal() {
    document.getElementById('card-form-title').textContent = 'Tambah Data KK Transmigran';
    document.getElementById('card-form-subtitle').textContent = 'Pencatatan data warga baru ke dalam buku registri UPT';
    document.getElementById('btn-save-card-text').textContent = 'Simpan Data KK';

    document.getElementById('form-card-id').value = '';
    document.getElementById('form-head-name').value = '';
    document.getElementById('form-kk-number').value = '';
    document.getElementById('form-nik').value = '';
    document.getElementById('form-members-count').value = '1';
    document.getElementById('form-trans-type').value = 'TPA';
    document.getElementById('form-origin-province').value = '';
    document.getElementById('form-origin-regency').value = '';
    document.getElementById('form-housing-block').value = '';
    document.getElementById('form-shm-status').value = 'Sudah SHM';
    document.getElementById('form-notes').value = '';

    document.getElementById('modal-card-form').classList.remove('hidden');
}

/**
 * Buka Modal Edit 1 KK
 */
function openEditCardModal(cardId) {
    const card = currentCardsData.find(c => c.id === cardId);
    if (!card) return;

    document.getElementById('card-form-title').textContent = 'Edit Data KK Transmigran';
    document.getElementById('card-form-subtitle').textContent = `Mengubah data keluarga '${card.head_of_family_name}'`;
    document.getElementById('btn-save-card-text').textContent = 'Perbarui Data KK';

    document.getElementById('form-card-id').value = card.id;
    document.getElementById('form-head-name').value = card.head_of_family_name || '';
    document.getElementById('form-kk-number').value = card.family_card_number || '';
    document.getElementById('form-nik').value = card.nik || '';
    document.getElementById('form-members-count').value = card.family_members_count || 1;
    document.getElementById('form-trans-type').value = card.transmigrant_type || 'TPA';
    document.getElementById('form-origin-province').value = card.origin_province || '';
    document.getElementById('form-origin-regency').value = card.origin_regency || '';
    document.getElementById('form-housing-block').value = card.housing_block || '';
    document.getElementById('form-shm-status').value = card.land_certificate_status || 'Sudah SHM';
    document.getElementById('form-notes').value = card.notes || '';

    document.getElementById('modal-card-form').classList.remove('hidden');
}

function closeCardFormModal() {
    document.getElementById('modal-card-form').classList.add('hidden');
}

/**
 * Submit Simpan / Update Form 1 KK
 */
function submitCardForm(e) {
    e.preventDefault();

    const cardId = document.getElementById('form-card-id').value;
    const isEdit = Boolean(cardId);

    const payload = {
        stage: currentRegistryStage,
        head_of_family_name: document.getElementById('form-head-name').value.trim(),
        family_card_number: document.getElementById('form-kk-number').value.trim() || null,
        nik: document.getElementById('form-nik').value.trim() || null,
        family_members_count: parseInt(document.getElementById('form-members-count').value, 10) || 1,
        transmigrant_type: document.getElementById('form-trans-type').value,
        origin_province: document.getElementById('form-origin-province').value.trim() || null,
        origin_regency: document.getElementById('form-origin-regency').value.trim() || null,
        housing_block: document.getElementById('form-housing-block').value.trim() || null,
        land_certificate_status: document.getElementById('form-shm-status').value,
        notes: document.getElementById('form-notes').value.trim() || null,
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('btn-save-card');
    const btnText = document.getElementById('btn-save-card-text');

    btn.disabled = true;
    btnText.textContent = 'Menyimpan...';

    const url = isEdit ? `/admin/family-cards/${cardId}` : `/admin/family-cards/upt/${currentRegistryUptId}`;
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeCardFormModal();
            fetchRegistryCards();
            showToast(data.message);
        } else {
            alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Gagal menyimpan data KK. Periksa kelengkapan formulir Anda.');
    })
    .finally(() => {
        btn.disabled = false;
        btnText.textContent = isEdit ? 'Perbarui Data KK' : 'Simpan Data KK';
    });
}

/**
 * Hapus 1 KK dari Registri
 */
function deleteCard(cardId, name) {
    if (!confirm(`Apakah Anda yakin ingin menghapus data KK '${name}' dari buku registri?`)) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch(`/admin/family-cards/${cardId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fetchRegistryCards();
            showToast(data.message);
        } else {
            alert('Gagal menghapus: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Gagal menghapus data KK.');
    });
}

/**
 * Buka Modal Import File Excel
 */
function openImportModal() {
    document.getElementById('import-file-input').value = '';
    document.getElementById('modal-import-cards').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('modal-import-cards').classList.add('hidden');
}

/**
 * Submit Import File Excel
 */
function submitImportCards(e) {
    e.preventDefault();

    const fileInput = document.getElementById('import-file-input');
    if (!fileInput.files.length) {
        alert('Pilih file CSV atau Excel terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('stage', currentRegistryStage);
    formData.append('file', fileInput.files[0]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('btn-submit-import');
    const btnText = document.getElementById('btn-import-text');

    btn.disabled = true;
    btnText.textContent = 'Mengimpor Data...';

    fetch(`/admin/family-cards/upt/${currentRegistryUptId}/import`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeImportModal();
            fetchRegistryCards();
            showToast(data.message);
        } else {
            alert('Gagal import: ' + (data.message || 'Format file tidak sesuai'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat memproses file import.');
    })
    .finally(() => {
        btn.disabled = false;
        btnText.textContent = 'Unggah & Proses Import';
    });
}

/**
 * Sinkronkan Total Registri ke Rekap UPT di Tabel Utama
 */
function syncRegistryToUpt() {
    if (!confirm('Apakah Anda ingin menyinkronkan total KK dan Jiwa dari registri ini ke baris rekap UPT di tabel utama?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('btn-sync-rekap');

    btn.disabled = true;

    fetch(`/admin/family-cards/upt/${currentRegistryUptId}/sync-aggregate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            stage: currentRegistryStage
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Perbarui sel tabel utama di background
            const uptId = data.data.upt_id;
            const cellKk = document.getElementById(`cell-kk-${uptId}`);
            const cellPop = document.getElementById(`cell-pop-${uptId}`);
            const cellRatio = document.getElementById(`cell-ratio-${uptId}`);

            if (cellKk) cellKk.innerHTML = `${Number(data.data.count_kk).toLocaleString('id-ID')} <span class="text-[10px] font-normal text-slate-500">KK</span>`;
            if (cellPop) cellPop.textContent = Number(data.data.count_pop).toLocaleString('id-ID');
            if (cellRatio && data.data.count_kk > 0) {
                const r = (data.data.count_pop / data.data.count_kk).toFixed(2).replace('.', ',');
                cellRatio.textContent = r;
            }

            // Perbarui KPI atas
            if (currentRegistryStage === 'placement' && data.data.total_all_placement_kk) {
                const kpiKk = document.getElementById('kpi-total-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_all_placement_kk).toLocaleString('id-ID')} <span class="text-base font-bold text-slate-600">KK</span>`;
                const kpiPop = document.getElementById('kpi-total-pop');
                if (kpiPop) kpiPop.textContent = Number(data.data.total_all_placement_pop).toLocaleString('id-ID');
            } else if (currentRegistryStage === 'handover' && data.data.total_all_handover_kk) {
                const kpiKk = document.getElementById('kpi-handover-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_all_handover_kk).toLocaleString('id-ID')} <span class="text-base font-bold text-slate-600">KK</span>`;
                const kpiPop = document.getElementById('kpi-handover-pop');
                if (kpiPop) kpiPop.textContent = Number(data.data.total_all_handover_pop).toLocaleString('id-ID');
            }

            // Perbarui komparasi di drawer
            document.getElementById('reg-stat-rekap-compare').textContent = `Rekap UPT: ${Number(data.data.count_kk).toLocaleString('id-ID')} KK`;

            showToast(data.message);
        } else {
            alert('Gagal menyinkronkan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Gagal menyinkronkan angka rekap.');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function addslashes(string) {
    return (string + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}
</script>
