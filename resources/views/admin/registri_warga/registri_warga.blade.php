<!-- ============================================================== -->
<!-- DRAWER & MODAL BUKU REGISTRI WARGA TRANSMIGRAN (OPSI C HIBRIDA) -->
<!-- ============================================================== -->
<div id="registry-drawer" class="fixed inset-0 z-50 hidden overflow-hidden" style="z-index: 50;" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop Gelap -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300" onclick="closeRegistryDrawer()"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-16">
        <div class="w-screen max-w-5xl bg-slate-50 border-l border-slate-200 shadow-2xl flex flex-col justify-between">
            
            <!-- 1. HEADER DRAWER -->
            <div class="p-6 bg-[#1B2632] text-white border-b border-[#2C3B4D] flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#FFB162] text-[#1B2632]" id="reg-badge-stage">
                            PENEMPATAN AWAL
                        </span>
                        <span class="text-xs text-[#C9C1B1] font-mono" id="reg-upt-code">UPT-001</span>
                        <span id="reg-badge-validation" class="hidden"></span>
                    </div>
                    <h2 class="text-xl font-extrabold text-[#EEE9DF] tracking-tight flex items-center gap-2" id="reg-upt-title">
                        Buku Registri Warga Transmigran
                    </h2>
                    <p class="text-xs text-[#C9C1B1] mt-1" id="reg-upt-sub">
                        Pencatatan nominal per KK transmigran, asal daerah, kapling pekarangan, dan status SHM.
                    </p>
                </div>
                <button type="button" onclick="closeRegistryDrawer()" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-[#EEE9DF] hover:text-white transition">
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
                        <button type="button" onclick="openAddCardModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs shadow-ambient-xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <span>Tambah 1 KK</span>
                        </button>
                        <button type="button" onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#C9C1B1] hover:bg-[#EEE9DF]/60 text-[#1B2632] font-bold text-xs transition shadow-ambient-xs cursor-pointer">
                            <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Import Excel/CSV</span>
                        </button>
                        <a id="btn-export-reg" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#C9C1B1] hover:bg-[#EEE9DF]/60 text-[#2C3B4D] font-bold text-xs transition shadow-ambient-xs">
                            <svg class="w-4 h-4 text-[#C9C1B1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Export CSV</span>
                        </a>
                        <button type="button" onclick="syncRegistryToUpt()" id="btn-sync-rekap" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#FFB162] hover:bg-[#ffa347] text-[#1B2632] font-extrabold text-xs shadow-ambient-xs transition cursor-pointer" title="Perbarui total KK dan Jiwa tabel utama UPT dengan data registri ini">
                            <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sinkronkan ke Rekap UPT</span>
                        </button>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex items-center gap-2 flex-1 max-w-sm ml-auto">
                        <div class="relative w-full">
                            <input type="text" id="reg-search" oninput="filterRegistryCards()" placeholder="Cari Nama, NIK, No KK, Blok..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                            <svg class="w-4 h-4 text-[#C9C1B1] absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TABEL DAFTAR WARGA (SCROLLABLE BODY) -->
            <div class="flex-1 overflow-y-auto p-5">
                <div class="bg-white rounded-xl border border-[#C9C1B1]/80 shadow-ambient-xs overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[10px] tracking-wider border-b border-[#2C3B4D]">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Kepala Keluarga</th>
                                <th class="py-3 px-3 text-center">No. KK & NIK</th>
                                <th class="py-3 px-3 text-center">Jiwa</th>
                                <th class="py-3 px-3 text-center">Jenis / Asal</th>
                                <th class="py-3 px-3 text-center">Blok Kapling</th>
                                <th class="py-3 px-3 text-center">Status SHM</th>
                                <th class="py-3 px-3 text-center">Berkas KK</th>
                                <th class="py-3 px-3">Catatan</th>
                                <th class="py-3 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reg-table-body" class="divide-y divide-slate-100">
                            <!-- Populated via Javascript -->
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#C9C1B1] border-t-[#FFB162] mb-2"></div>
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

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-[#C9C1B1]">
            <!-- Modal Header -->
            <div class="bg-[#1B2632] text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#EEE9DF]" id="card-form-title">
                        Tambah Data KK Transmigran
                    </h3>
                    <p class="text-xs text-[#C9C1B1] mt-0.5" id="card-form-subtitle">
                        Identitas nominal kepala keluarga, kapling, dan sertipikat
                    </p>
                </div>
                <button type="button" onclick="closeCardFormModal()" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg">
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
                    <input type="text" id="form-head-name" required placeholder="Contoh: Slamet Riyadi" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                </div>

                <!-- Grid Identitas: No KK & NIK -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            No. Kartu Keluarga (KK)
                        </label>
                        <input type="text" id="form-kk-number" maxlength="30" placeholder="16 Digit No KK" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-mono focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            NIK Kepala Keluarga
                        </label>
                        <input type="text" id="form-nik" maxlength="30" placeholder="16 Digit NIK" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-mono focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>

                <!-- Grid: Jumlah Jiwa & Jenis Transmigran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Jumlah Jiwa dalam 1 KK <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="form-members-count" required min="1" max="30" value="1" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-bold focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Jenis Transmigran <span class="text-red-500">*</span>
                        </label>
                        <select id="form-trans-type" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
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
                        <input type="text" id="form-origin-province" placeholder="Contoh: Jawa Tengah" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Asal Kabupaten / Kota
                        </label>
                        <input type="text" id="form-origin-regency" placeholder="Contoh: Banyumas" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>

                <!-- Grid: Blok/Kapling & Status SHM -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Blok / No. Kapling Rumah
                        </label>
                        <input type="text" id="form-housing-block" placeholder="Contoh: Blok B No. 12" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Status Sertipikat SHM <span class="text-red-500">*</span>
                        </label>
                        <select id="form-shm-status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
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
                    <textarea id="form-notes" rows="2" placeholder="Catatan mutasi, ahli waris, atau keterangan lahan..." class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]"></textarea>
                </div>

                <!-- Upload Berkas Scan KK / Dokumen Pendukung -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Unggah Berkas Scan KK / KTP / Dokumen Pendukung <span class="text-[10px] font-normal text-slate-400 normal-case">(Opsional - PDF, JPG, PNG maks. 10MB)</span>
                    </label>
                    
                    <div class="relative">
                        <input type="file" id="form-document-file" accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileChange(event)" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1B2632] file:text-white hover:file:bg-[#2C3B4D] border border-[#C9C1B1] rounded-xl p-2 cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-[#FFB162] bg-white">
                    </div>

                    <!-- Indikator Berkas Tersimpan (Mode Edit) -->
                    <div id="existing-file-container" class="hidden mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="p-1.5 rounded-lg bg-amber-100 text-[#A35139] shrink-0">
                                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </span>
                            <div class="truncate">
                                <span class="font-bold text-slate-700 block truncate" id="existing-file-name">dokumen_kk.pdf</span>
                                <span class="text-[10px] text-slate-400">Berkas saat ini sudah tersimpan di sistem</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ml-2">
                            <a id="existing-file-link" href="#" target="_blank" class="px-2.5 py-1 rounded-lg bg-white border border-[#C9C1B1] hover:bg-slate-100 text-[#1B2632] font-bold text-[11px] inline-flex items-center gap-1 transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span>Lihat Berkas</span>
                            </a>
                            <button type="button" onclick="markDeleteExistingFile()" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition cursor-pointer" title="Hapus Berkas dari KK ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="form-delete-document" value="false">
                </div>

                <!-- Form Buttons -->
                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeCardFormModal()" class="px-4 py-2.5 rounded-xl border border-[#C9C1B1] text-slate-700 hover:bg-[#EEE9DF]/60 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-card" class="px-5 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white font-bold text-xs transition shadow-ambient-xs flex items-center gap-2 cursor-pointer">
                        <span id="btn-save-card-text">Simpan Data KK</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL IMPORT BANYAK KK (EXCEL / CSV) -->
<!-- ============================================================== -->
<div id="modal-import-cards" class="fixed inset-0 z-[70] hidden overflow-y-auto" style="z-index: 70;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="closeImportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#C9C1B1]">
            <!-- Header -->
            <div class="bg-[#1B2632] text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#EEE9DF]">
                        Import Data Registri Warga Transmigran
                    </h3>
                    <p class="text-xs text-[#C9C1B1] mt-0.5">
                        Unggah berkas Excel/CSV untuk mencatat banyak KK sekaligus
                    </p>
                </div>
                <button type="button" onclick="closeImportModal()" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg">
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
                        <a id="btn-download-template" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs shadow-ambient-xs transition">
                            <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
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
                        <input type="file" id="import-file-input" required accept=".csv, .xlsx, .xls" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#A35139]/10 file:text-[#A35139] hover:file:bg-[#A35139]/20 border border-[#C9C1B1] rounded-xl p-2 cursor-pointer">
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2.5 rounded-xl border border-[#C9C1B1] text-slate-700 hover:bg-[#EEE9DF]/60 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-import" class="px-5 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white font-bold text-xs transition shadow-ambient-xs flex items-center gap-2 cursor-pointer">
                            <span id="btn-import-text">Unggah & Proses Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL PENGAJUAN PENGESAHAN BUKU REGISTRI KE PROVINSI (OPERATOR) -->
<!-- ============================================================== -->
<div id="modal-submit-validation" class="fixed inset-0 z-[70] hidden overflow-y-auto" style="z-index: 70;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="closeSubmitValidationModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#C9C1B1]">
            <!-- Header -->
            <div class="bg-[#1B2632] text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-[#EEE9DF]">
                            Ajukan Pengesahan Buku Registri ke Provinsi
                        </h3>
                        <p class="text-xs text-[#C9C1B1]">
                            Verifikasi data nominal warga & sinkronisasi angka master
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeSubmitValidationModal()" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <form onsubmit="submitValidationToProvinsi(event)" class="p-6 space-y-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 uppercase tracking-wider font-bold text-[10px]">Sasaran Unit:</span>
                        <span class="font-extrabold text-slate-800" id="val-modal-upt-name">UPT</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 uppercase tracking-wider font-bold text-[10px]">Tahapan Data:</span>
                        <span class="font-bold text-[#A35139]" id="val-modal-stage">Tahap Penempatan Awal</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 leading-relaxed">
                    <strong>Catatan Alur Verifikasi:</strong> Setelah diajukan, permohonan ini akan masuk ke antrean <em>Diff Checker</em> Super Admin Provinsi Kalsel. Angka rekap UPT akan disahkan dan dimutakhirkan setelah disetujui. Anda tetap dapat mengedit data warga di lapangan sewaktu-waktu.
                </div>

                <div>
                    <label for="val-submit-note" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan Pengantar Pengesahan (Opsional)
                    </label>
                    <textarea id="val-submit-note" rows="3" placeholder="Contoh: Pencatatan data KK warga telah selesai dihimpun dari lapangan bersama aparat desa dan siap disahkan..." class="w-full text-xs rounded-xl border border-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] focus:outline-hidden p-3 text-slate-800"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeSubmitValidationModal()" class="px-4 py-2.5 rounded-xl border border-[#C9C1B1] text-slate-700 hover:bg-[#EEE9DF]/60 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-confirm-submit-val" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-ambient-xs flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span id="btn-confirm-val-text">Kirim Pengajuan ke Provinsi</span>
                    </button>
                </div>
            </form>
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

// Fallback showToast jika tidak disediakan oleh layout induk
if (typeof window.showToast !== 'function') {
    window.showToast = function(message, isSuccess = true) {
        let toast = document.getElementById('registri-toast-box');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'registri-toast-box';
            document.body.appendChild(toast);
        }
        toast.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-2xl text-xs font-bold text-white transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-2 ${isSuccess ? 'bg-[#124D1C]' : 'bg-rose-700'}`;
        toast.innerHTML = `<span>${isSuccess ? '✓' : '⚠'}</span> <span>${message}</span>`;
        setTimeout(() => {
            toast.className = 'fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-2xl text-xs font-bold text-white transition-all duration-300 transform translate-y-10 opacity-0 pointer-events-none flex items-center gap-2';
        }, 3500);
    };
}

/**
 * Buka Drawer Buku Registri Warga untuk UPT dan Tahapan Tertentu
 */
function openRegistryModal(uptId, uptName, stage) {
    currentRegistryUptId = uptId;
    currentRegistryStage = stage || 'placement';

    const drawer = document.getElementById('registry-drawer');
    if (!drawer) {
        console.error('Element #registry-drawer tidak ditemukan!');
        return;
    }

    const badgeStage = document.getElementById('reg-badge-stage');
    const uptTitle = document.getElementById('reg-upt-title');
    const uptCode = document.getElementById('reg-upt-code');
    const btnExport = document.getElementById('btn-export-reg');
    const btnTemplate = document.getElementById('btn-download-template');
    const searchInput = document.getElementById('reg-search');

    if (searchInput) searchInput.value = '';

    if (currentRegistryStage === 'handover') {
        if (badgeStage) {
            badgeStage.textContent = 'SERAH TERIMA PEMDA';
            badgeStage.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/40';
        }
    } else {
        if (badgeStage) {
            badgeStage.textContent = 'PENEMPATAN AWAL';
            badgeStage.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25';
        }
    }

    if (uptTitle) uptTitle.textContent = uptName;
    if (uptCode) uptCode.textContent = `ID #${uptId}`;

    if (btnExport) btnExport.href = `/admin/family-cards/upt/${uptId}/export?stage=${currentRegistryStage}`;
    if (btnTemplate) btnTemplate.href = `/admin/family-cards/template/download?stage=${currentRegistryStage}`;

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Ambil Data Registri via AJAX
    fetchRegistryCards();
}

/**
 * Buka Drawer Buku Registri Warga (Alias Kompatibilitas)
 */
function openRegistryDrawer(uptId, uptName, regencyOrStage, maybeStage) {
    let stage = 'placement';
    let displayName = uptName;

    if (maybeStage) {
        stage = maybeStage;
        if (regencyOrStage) {
            displayName = `${uptName} (${regencyOrStage})`;
        }
    } else if (regencyOrStage === 'handover' || regencyOrStage === 'placement') {
        stage = regencyOrStage;
    } else if (regencyOrStage) {
        displayName = `${uptName} (${regencyOrStage})`;
    }

    openRegistryModal(uptId, displayName, stage);
}

// Pastikan fungsi dapat diakses secara global di window
window.openRegistryModal = openRegistryModal;
window.openRegistryDrawer = openRegistryDrawer;
window.closeRegistryDrawer = closeRegistryDrawer;

/**
 * Tutup Drawer Registri Warga
 */
function closeRegistryDrawer() {
    const drawer = document.getElementById('registry-drawer');
    if (drawer) {
        drawer.classList.add('hidden');
    }
    document.body.style.overflow = '';
}

/**
 * Request AJAX data registri warga
 */
function fetchRegistryCards() {
    const tableBody = document.getElementById('reg-table-body');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" class="py-12 text-center text-[#2C3B4D]/60 font-medium">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#C9C1B1]/40 border-t-[#1B2632] mb-2"></div>
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
                tableBody.innerHTML = `<tr><td colspan="10" class="py-8 text-center text-red-500 font-bold">Gagal memuat data registri.</td></tr>`;
            }
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="10" class="py-8 text-center text-red-500 font-bold">Terjadi kesalahan koneksi server.</td></tr>`;
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
                <td colspan="10" class="py-16 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <div class="font-bold text-slate-700 text-sm">Belum Ada Data Registri Nama KK</div>
                    <div class="text-xs text-slate-400 max-w-sm mx-auto mt-1">UPT ini baru memiliki data rekap angka agregat. Anda dapat menambahkan 1 KK secara manual atau mengunggah berkas Excel warga.</div>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <button type="button" onclick="openAddCardModal()" class="px-3.5 py-1.5 rounded-xl bg-[#A35139] text-white text-xs font-bold hover:bg-[#883d28] transition shadow-ambient-xs cursor-pointer">+ Tambah 1 KK</button>
                        <button type="button" onclick="openImportModal()" class="px-3.5 py-1.5 rounded-xl bg-white border border-[#C9C1B1] text-[#1B2632] text-xs font-bold hover:bg-[#EEE9DF]/60 transition shadow-ambient-xs cursor-pointer">Import File Excel</button>
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

        let docCell = '<span class="text-slate-300 font-mono text-[11px]">-</span>';
        if (c.document_path) {
            const ext = c.document_name ? c.document_name.split('.').pop().toUpperCase() : 'BERKAS';
            docCell = `
                <a href="/admin/family-cards/${c.id}/document" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-[#1B2632] font-extrabold text-[10px] shadow-2xs transition" title="${c.document_name || 'Lihat Berkas KK'}">
                    <svg class="w-3.5 h-3.5 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>${ext}</span>
                </a>
            `;
        }

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
                <td class="py-2.5 px-3 text-center">
                    ${docCell}
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

        // Update status validasi di drawer header
        const valBadge = document.getElementById('reg-badge-validation');
        if (valBadge) {
            valBadge.classList.remove('hidden');
            if (upt.has_pending_validation) {
                valBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/50';
                valBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-[#8F4E0A] animate-pulse"></span> Menunggu Verifikasi (Draf #${upt.pending_validation_id})`;
            } else if (upt.is_verified) {
                valBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
                valBadge.innerHTML = `<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Disahkan Provinsi`;
            } else {
                valBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-700/60 text-[#C9C1B1] border border-slate-600';
                valBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draf Pengisian Lapangan`;
            }
        }

        // Update tombol sync / submit validasi
        const btnSync = document.getElementById('btn-sync-rekap');
        if (btnSync) {
            if (upt.can_direct_sync) {
                // Super Admin
                btnSync.className = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#FFB162] hover:bg-[#ffa347] text-[#1B2632] font-extrabold text-xs shadow-ambient-xs transition cursor-pointer';
                btnSync.title = 'Sinkronkan langsung jumlah KK & Jiwa dari registri ini ke master UPT';
                btnSync.innerHTML = `
                    <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Sinkronkan ke Rekap UPT</span>
                `;
                btnSync.onclick = syncRegistryToUpt;
            } else {
                // Operator Wilayah
                if (upt.has_pending_validation) {
                    btnSync.className = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 font-bold text-xs shadow-ambient-xs transition cursor-pointer';
                    btnSync.title = `Pengajuan pengesahan buku registri ini sedang ditinjau di antrean verifikasi provinsi (Draf #${upt.pending_validation_id})`;
                    btnSync.innerHTML = `
                        <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                        <span>Menunggu Verifikasi (#${upt.pending_validation_id})</span>
                    `;
                    btnSync.onclick = function() {
                        alert(`Pengajuan pengesahan buku registri UPT ini sedang ditinjau oleh Super Admin Provinsi Kalsel (Draf #${upt.pending_validation_id}). Anda tetap dapat menambahkan atau memperbarui data warga di lapangan.`);
                    };
                } else {
                    btnSync.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-ambient-xs transition cursor-pointer';
                    btnSync.title = 'Kirimkan buku registri warga ini ke Provinsi untuk disahkan dan disinkronkan ke angka master';
                    btnSync.innerHTML = `
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Ajukan Pengesahan Buku ke Provinsi</span>
                    `;
                    btnSync.onclick = openSubmitValidationModal;
                }
            }
        }
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

    // Reset input berkas
    const fileInput = document.getElementById('form-document-file');
    if (fileInput) fileInput.value = '';
    const existingFileContainer = document.getElementById('existing-file-container');
    if (existingFileContainer) existingFileContainer.classList.add('hidden');
    document.getElementById('form-delete-document').value = 'false';

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

    // Reset input berkas & tampilkan berkas tersimpan jika ada
    const fileInput = document.getElementById('form-document-file');
    if (fileInput) fileInput.value = '';
    document.getElementById('form-delete-document').value = 'false';

    const existingFileContainer = document.getElementById('existing-file-container');
    if (card.document_path) {
        document.getElementById('existing-file-name').textContent = card.document_name || 'Berkas KK Digital';
        document.getElementById('existing-file-link').href = `/admin/family-cards/${card.id}/document`;
        existingFileContainer.classList.remove('hidden');
    } else {
        existingFileContainer.classList.add('hidden');
    }

    document.getElementById('modal-card-form').classList.remove('hidden');
}

function closeCardFormModal() {
    document.getElementById('modal-card-form').classList.add('hidden');
}

/**
 * Tandai berkas lama untuk dihapus pada saat edit
 */
function markDeleteExistingFile() {
    if (confirm('Apakah Anda yakin ingin menghapus berkas lampiran pada KK ini?')) {
        document.getElementById('form-delete-document').value = 'true';
        document.getElementById('existing-file-container').classList.add('hidden');
    }
}

/**
 * Tangani perubahan file input
 */
function handleFileChange(e) {
    if (e.target.files && e.target.files.length > 0) {
        document.getElementById('form-delete-document').value = 'false';
    }
}

/**
 * Submit Simpan / Update Form 1 KK (Multipart FormData)
 */
function submitCardForm(e) {
    e.preventDefault();

    const cardId = document.getElementById('form-card-id').value;
    const isEdit = Boolean(cardId);

    const formData = new FormData();
    if (isEdit) {
        formData.append('_method', 'PUT');
    }
    formData.append('stage', currentRegistryStage);
    formData.append('head_of_family_name', document.getElementById('form-head-name').value.trim());
    formData.append('family_card_number', document.getElementById('form-kk-number').value.trim());
    formData.append('nik', document.getElementById('form-nik').value.trim());
    formData.append('family_members_count', document.getElementById('form-members-count').value || '1');
    formData.append('transmigrant_type', document.getElementById('form-trans-type').value);
    formData.append('origin_province', document.getElementById('form-origin-province').value.trim());
    formData.append('origin_regency', document.getElementById('form-origin-regency').value.trim());
    formData.append('housing_block', document.getElementById('form-housing-block').value.trim());
    formData.append('land_certificate_status', document.getElementById('form-shm-status').value);
    formData.append('notes', document.getElementById('form-notes').value.trim());

    const fileInput = document.getElementById('form-document-file');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('document_file', fileInput.files[0]);
    }

    const deleteDocInput = document.getElementById('form-delete-document');
    if (deleteDocInput && deleteDocInput.value === 'true') {
        formData.append('delete_document', 'true');
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('btn-save-card');
    const btnText = document.getElementById('btn-save-card-text');

    btn.disabled = true;
    btnText.textContent = 'Menyimpan...';

    const url = isEdit ? `/admin/family-cards/${cardId}` : `/admin/family-cards/upt/${currentRegistryUptId}`;

    fetch(url, {
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

/**
 * Buka Modal Pengajuan Pengesahan Buku Registri (Operator)
 */
function openSubmitValidationModal() {
    if (!currentRegistryUptId) return;
    if (!currentCardsData || currentCardsData.length === 0) {
        alert('Buku registri masih kosong (0 KK). Tambahkan minimal 1 data KK sebelum mengajukan pengesahan ke Provinsi.');
        return;
    }
    
    const uptTitle = document.getElementById('reg-upt-title')?.textContent || '';
    const modalUptName = document.getElementById('val-modal-upt-name');
    if (modalUptName) modalUptName.textContent = uptTitle;

    const modalStage = document.getElementById('val-modal-stage');
    if (modalStage) {
        modalStage.textContent = currentRegistryStage === 'handover' ? 'Tahap Serah Terima Pemda' : 'Tahap Penempatan Awal';
    }

    const noteInput = document.getElementById('val-submit-note');
    if (noteInput) noteInput.value = '';

    document.getElementById('modal-submit-validation').classList.remove('hidden');
}

function closeSubmitValidationModal() {
    const modal = document.getElementById('modal-submit-validation');
    if (modal) modal.classList.add('hidden');
}

function submitValidationToProvinsi(e) {
    if (e) e.preventDefault();
    if (!currentRegistryUptId) return;

    const note = document.getElementById('val-submit-note')?.value.trim() || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('btn-confirm-submit-val');
    const btnText = document.getElementById('btn-confirm-val-text');

    if (btn) btn.disabled = true;
    if (btnText) btnText.textContent = 'Mengirimkan...';

    fetch(`/admin/family-cards/upt/${currentRegistryUptId}/submit-validation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            stage: currentRegistryStage,
            submission_note: note
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeSubmitValidationModal();
            fetchRegistryCards();
            showToast(data.message);
        } else {
            alert('Gagal mengajukan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat mengirim pengajuan pengesahan.');
    })
    .finally(() => {
        if (btn) btn.disabled = false;
        if (btnText) btnText.textContent = 'Kirim Pengajuan ke Provinsi';
    });
}

window.openSubmitValidationModal = openSubmitValidationModal;
window.closeSubmitValidationModal = closeSubmitValidationModal;
window.submitValidationToProvinsi = submitValidationToProvinsi;

function addslashes(string) {
    return (string + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}
</script>
