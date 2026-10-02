<!-- ============================================================== -->
<!-- DRAWER & MODAL BUKU REGISTRI WARGA TRANSMIGRAN (TEMA ATLAS)     -->
<!-- ============================================================== -->
<div id="registry-drawer" class="fixed inset-0 z-50 hidden overflow-hidden" style="z-index: 50;" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 transition-opacity duration-300" style="background: #24374666;" onclick="closeRegistryDrawer()"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-16">
        <div class="w-screen max-w-5xl bg-[#F3F6F8] border-l border-[#D4DEE7] shadow-2xl flex flex-col justify-between">

            <!-- 1. HEADER DRAWER — identitas UPT berpasangan (Atlas) -->
            <div class="px-6 py-5 bg-white border-b border-[#D4DEE7] flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="atlas-status atlas-status--clean" id="reg-badge-stage">
                            <span aria-hidden="true"></span>PENEMPATAN AWAL
                        </span>
                        <span class="atlas-code" id="reg-upt-code">UPT-001</span>
                        <span id="reg-badge-validation" class="hidden"></span>
                    </div>
                    <h2 class="text-xl font-semibold text-[#243746] tracking-tight leading-tight" id="reg-upt-title" style="font-family: 'Barlow Semi Condensed', sans-serif;">
                        Buku Registri Warga Transmigran
                    </h2>
                    <p class="text-[13px] text-[#5A6E7D] mt-1" id="reg-upt-sub">
                        Pencatatan nominal per KK transmigran, asal daerah, kapling pekarangan, dan status SHM.
                    </p>
                </div>
                <button type="button" onclick="closeRegistryDrawer()" class="atlas-icon-button" title="Tutup registri">
                    <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- 2. METRIK & TOOLBAR AKSI -->
            <div class="px-5 py-4 bg-white border-b border-[#D4DEE7] space-y-4">
                <!-- Metrik ringkas -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3 rounded border border-[#D4DEE7] bg-[#F3F6F8]">
                        <div class="atlas-code uppercase">Total KK registri</div>
                        <div class="text-xl font-semibold text-[#243746] tabular-nums mt-0.5" id="reg-stat-kk" style="font-family: 'Barlow Semi Condensed', sans-serif;">0 <span class="text-xs font-normal text-[#5A6E7D]">KK</span></div>
                        <div class="text-[11px] text-[#5A6E7D] mt-0.5" id="reg-stat-rekap-compare">Rekap UPT: 0 KK</div>
                    </div>
                    <div class="p-3 rounded border border-[#D4DEE7] bg-[#F3F6F8]">
                        <div class="atlas-code uppercase">Total jiwa registri</div>
                        <div class="text-xl font-semibold text-[#243746] tabular-nums mt-0.5" id="reg-stat-pop" style="font-family: 'Barlow Semi Condensed', sans-serif;">0 <span class="text-xs font-normal text-[#5A6E7D]">Jiwa</span></div>
                        <div class="text-[11px] text-[#5A6E7D] mt-0.5" id="reg-stat-avg">Rata-rata: 0 Jiwa/KK</div>
                    </div>
                    <div class="p-3 rounded border border-[#D4DEE7] bg-[#F3F6F8]">
                        <div class="atlas-code uppercase">Komposisi transmigran</div>
                        <div class="text-xs font-semibold text-[#243746] mt-1 flex items-center gap-2">
                            <span class="px-1.5 py-0.5 bg-[#DAE7F6] text-[#194482] rounded text-[10px] tabular-nums" id="reg-stat-tpa">TPA: 0 KK</span>
                            <span class="px-1.5 py-0.5 bg-[#FAF3E4] text-[#97620B] rounded text-[10px] tabular-nums" id="reg-stat-tps">TPS: 0 KK</span>
                        </div>
                        <div class="text-[11px] text-[#5A6E7D] mt-1">Penduduk asal vs setempat</div>
                    </div>
                    <div class="p-3 rounded border border-[#D4DEE7] bg-[#F3F6F8]">
                        <div class="atlas-code uppercase">Status Hak Milik (SHM)</div>
                        <div class="text-xl font-semibold text-[#243746] tabular-nums mt-0.5" id="reg-stat-shm" style="font-family: 'Barlow Semi Condensed', sans-serif;">0 <span class="text-xs font-normal text-[#5A6E7D]">SHM</span></div>
                        <div class="text-[11px] text-[#5A6E7D] mt-0.5">Sudah bersertipikat Hak Milik</div>
                    </div>
                </div>

                <!-- Tombol aksi & pencarian -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="openAddCardModal()" class="atlas-button atlas-button--primary" style="min-height: 34px;">
                            <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <span>Tambah 1 KK</span>
                        </button>
                        <button type="button" onclick="openImportModal()" class="atlas-button" style="min-height: 34px;">
                            <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Impor Excel/CSV</span>
                        </button>
                        <a id="btn-export-reg" href="#" target="_blank" class="atlas-button" style="min-height: 34px;">
                            <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Ekspor CSV</span>
                        </a>
                        <button type="button" onclick="syncRegistryToUpt()" id="btn-sync-rekap" class="atlas-button atlas-button--primary" style="min-height: 34px;" title="Perbarui total KK dan Jiwa tabel utama UPT dengan data registri ini">
                            <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sinkronkan ke rekap UPT</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 flex-1 max-w-sm ml-auto">
                        <div class="relative w-full">
                            <input type="text" id="reg-search" oninput="filterRegistryCards()" placeholder="Cari nama, NIK, No KK, blok…" class="w-full pl-8 pr-3 py-1.5 text-[13px] rounded border border-[#D4DEE7] bg-white text-[#243746] focus:outline-none focus:ring-2 focus:ring-[#2457A7]/20 focus:border-[#2457A7]">
                            <svg class="w-4 h-4 text-[#5A6E7D] absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TABEL DAFTAR WARGA (SCROLLABLE BODY) -->
            <div class="flex-1 overflow-y-auto p-5">
                <div class="bg-white rounded border border-[#D4DEE7] overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#EDF2F6] text-[#5A6E7D] font-semibold uppercase text-[10px] tracking-wider border-b border-[#D4DEE7]">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Kepala keluarga</th>
                                <th class="py-3 px-3 text-center">No. KK &amp; NIK</th>
                                <th class="py-3 px-3 text-center">Jiwa</th>
                                <th class="py-3 px-3 text-center">Jenis / asal</th>
                                <th class="py-3 px-3 text-center">Blok kapling</th>
                                <th class="py-3 px-3 text-center">Status SHM</th>
                                <th class="py-3 px-3 text-center">Berkas KK</th>
                                <th class="py-3 px-3">Catatan</th>
                                <th class="py-3 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reg-table-body" class="divide-y divide-[#E5EBF0]">
                            <!-- Populated via Javascript -->
                            <tr>
                                <td colspan="10" class="py-12 text-center text-[#5A6E7D]">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-[3px] border-[#D4DEE7] border-t-[#2457A7] mb-2"></div>
                                    <div>Memuat data registri warga…</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. FOOTER DRAWER -->
            <div class="px-4 py-3 bg-white border-t border-[#D4DEE7] flex items-center justify-between text-[13px] text-[#5A6E7D]">
                <div id="reg-footer-info">
                    Menampilkan 0 data KK transmigran
                </div>
                <button type="button" onclick="closeRegistryDrawer()" class="atlas-button" style="min-height: 34px;">
                    Tutup registri
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL TAMBAH / EDIT 1 DATA KK TRANSMIGRAN (ATLAS DIALOG)        -->
<!-- ============================================================== -->
<div id="modal-card-form" class="atlas-dialog-toggle hidden" role="dialog" aria-modal="true" aria-labelledby="card-form-title">
    <div class="atlas-dialog__panel" style="max-width: 600px;">
        <div class="atlas-dialog__head">
            <div>
                <h3 id="card-form-title">Tambah data KK transmigran</h3>
                <p id="card-form-subtitle">Identitas nominal kepala keluarga, kapling, dan sertipikat</p>
            </div>
            <button type="button" onclick="closeCardFormModal()" class="atlas-icon-button" aria-label="Tutup dialog">
                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="form-family-card" onsubmit="submitCardForm(event)" class="atlas-dialog__body">
            <input type="hidden" id="form-card-id" value="">

            <div class="atlas-form-grid">
                <!-- Nama Lengkap Kepala Keluarga -->
                <div class="atlas-field atlas-field--wide">
                    <label for="form-head-name">Nama kepala keluarga <span aria-hidden="true">*</span></label>
                    <input type="text" id="form-head-name" required placeholder="Contoh: Slamet Riyadi">
                </div>

                <!-- Grid Identitas: No KK & NIK -->
                <div class="atlas-field">
                    <label for="form-kk-number">No. Kartu Keluarga (KK)</label>
                    <input type="text" id="form-kk-number" maxlength="30" placeholder="16 digit No. KK" class="tabular-nums">
                </div>
                <div class="atlas-field">
                    <label for="form-nik">NIK kepala keluarga</label>
                    <input type="text" id="form-nik" maxlength="30" placeholder="16 digit NIK" class="tabular-nums">
                </div>

                <!-- Grid: Jumlah Jiwa & Jenis Transmigran -->
                <div class="atlas-field">
                    <label for="form-members-count">Jumlah jiwa dalam 1 KK <span aria-hidden="true">*</span></label>
                    <input type="number" id="form-members-count" required min="1" max="30" value="1" class="tabular-nums">
                </div>
                <div class="atlas-field">
                    <label for="form-trans-type">Jenis transmigran <span aria-hidden="true">*</span></label>
                    <select id="form-trans-type" required>
                        <option value="TPA">TPA — penduduk asal (luar Kalsel)</option>
                        <option value="TPS">TPS — penduduk setempat (lokal Kalsel)</option>
                    </select>
                </div>

                <!-- Grid: Asal Provinsi & Asal Kabupaten -->
                <div class="atlas-field">
                    <label for="form-origin-province">Asal provinsi</label>
                    <input type="text" id="form-origin-province" placeholder="Contoh: Jawa Tengah">
                </div>
                <div class="atlas-field">
                    <label for="form-origin-regency">Asal kabupaten / kota</label>
                    <input type="text" id="form-origin-regency" placeholder="Contoh: Banyumas">
                </div>

                <!-- Grid: Blok/Kapling & Status SHM -->
                <div class="atlas-field">
                    <label for="form-housing-block">Blok / No. kapling rumah</label>
                    <input type="text" id="form-housing-block" placeholder="Contoh: Blok B No. 12">
                </div>
                <div class="atlas-field">
                    <label for="form-shm-status">Status sertipikat SHM <span aria-hidden="true">*</span></label>
                    <select id="form-shm-status" required>
                        <option value="Sudah SHM">Sudah SHM</option>
                        <option value="Proses BPN">Proses BPN (redistribusi/PTSL)</option>
                        <option value="Belum SHM">Belum SHM</option>
                        <option value="Sengketa">Sengketa / klaim pihak ketiga</option>
                    </select>
                </div>

                <!-- Catatan Tambahan -->
                <div class="atlas-field atlas-field--wide">
                    <label for="form-notes">Catatan / riwayat warga</label>
                    <textarea id="form-notes" rows="2" placeholder="Catatan mutasi, ahli waris, atau keterangan lahan…"></textarea>
                </div>

                <!-- Upload Berkas Scan KK -->
                <div class="atlas-field atlas-field--wide">
                    <label for="form-document-file">Unggah berkas scan KK / KTP / dokumen pendukung <span class="atlas-code normal-case">(opsional — PDF, JPG, PNG maks. 10 MB)</span></label>
                    <input type="file" id="form-document-file" accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileChange(event)" style="font-size: 13px;">
                </div>
            </div>

            <!-- Indikator Berkas Tersimpan (Mode Edit) -->
            <div id="existing-file-container" class="hidden p-3 rounded border border-[#D4DEE7] bg-[#F3F6F8] flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 text-[#2457A7] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <div class="truncate">
                        <span class="font-semibold text-[#243746] block truncate" id="existing-file-name">dokumen_kk.pdf</span>
                        <span class="text-[11px] text-[#5A6E7D]">Berkas saat ini sudah tersimpan di sistem</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-2">
                    <a id="existing-file-link" href="#" target="_blank" class="atlas-link" style="font-size: 12px;">
                        Lihat berkas
                    </a>
                    <button type="button" onclick="markDeleteExistingFile()" class="p-1.5 rounded text-[#B43D3D] hover:bg-[#FBEDED] transition cursor-pointer" title="Hapus berkas dari KK ini">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
            <input type="hidden" id="form-delete-document" value="false">

            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" onclick="closeCardFormModal()" class="atlas-button">Batal</button>
                <button type="submit" id="btn-save-card" class="atlas-button atlas-button--primary">
                    <span id="btn-save-card-text">Simpan data KK</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL IMPORT BANYAK KK (EXCEL / CSV) — ATLAS DIALOG             -->
<!-- ============================================================== -->
<div id="modal-import-cards" class="atlas-dialog-toggle hidden" role="dialog" aria-modal="true" aria-labelledby="modal-import-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div>
                <h3 id="modal-import-title">Impor data registri warga transmigran</h3>
                <p>Unggah berkas Excel/CSV untuk mencatat banyak KK sekaligus</p>
            </div>
            <button type="button" onclick="closeImportModal()" class="atlas-icon-button" aria-label="Tutup dialog">
                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="form-import-cards" onsubmit="submitImportCards(event)" class="atlas-dialog__body">
            <div class="p-4 rounded border border-[#E7D9B4] bg-[#FAF3E4] text-xs text-[#243746] space-y-2">
                <div class="font-semibold flex items-center gap-1.5 text-[#97620B]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Panduan format berkas Excel / CSV:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 pl-1">
                    <li>Gunakan template resmi agar susunan kolom sesuai sistem.</li>
                    <li>Kolom wajib: <strong>nama lengkap kepala keluarga</strong> dan <strong>jumlah jiwa</strong>.</li>
                    <li>Jenis transmigran diisi <strong>TPA</strong> (penduduk asal) atau <strong>TPS</strong> (penduduk setempat).</li>
                </ul>
                <div class="pt-1">
                    <a id="btn-download-template" href="#" target="_blank" class="atlas-button" style="min-height: 32px; font-size: 13px;">
                        <svg class="atlas-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Unduh contoh template CSV / Excel</span>
                    </a>
                </div>
            </div>

            <div class="atlas-field">
                <label for="import-file-input">Pilih berkas (.csv, .xlsx, .xls) <span aria-hidden="true">*</span></label>
                <input type="file" id="import-file-input" required accept=".csv, .xlsx, .xls" style="font-size: 13px;">
            </div>

            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" onclick="closeImportModal()" class="atlas-button">Batal</button>
                <button type="submit" id="btn-submit-import" class="atlas-button atlas-button--primary">
                    <span id="btn-import-text">Unggah &amp; proses impor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL PENGAJUAN PENGESAHAN BUKU REGISTRI KE PROVINSI — ATLAS    -->
<!-- ============================================================== -->
<div id="modal-submit-validation" class="atlas-dialog-toggle hidden" role="dialog" aria-modal="true" aria-labelledby="modal-validation-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div>
                <h3 id="modal-validation-title">Ajukan pengesahan buku registri ke provinsi</h3>
                <p>Verifikasi data nominal warga &amp; sinkronisasi angka master</p>
            </div>
            <button type="button" onclick="closeSubmitValidationModal()" class="atlas-icon-button" aria-label="Tutup dialog">
                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form onsubmit="submitValidationToProvinsi(event)" class="atlas-dialog__body">
            <div class="p-4 rounded border border-[#D4DEE7] bg-[#F3F6F8] text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="atlas-code uppercase">Sasaran unit:</span>
                    <span class="font-semibold text-[#243746]" id="val-modal-upt-name">UPT</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="atlas-code uppercase">Tahapan data:</span>
                    <span class="atlas-status atlas-status--clean" id="val-modal-stage"><span aria-hidden="true"></span>Tahap penempatan awal</span>
                </div>
            </div>

            <div class="p-4 rounded border border-[#E7D9B4] bg-[#FAF3E4] text-xs text-[#243746] leading-relaxed">
                <strong>Alur verifikasi:</strong> setelah diajukan, permohonan ini masuk ke antrean verifikasi Super Admin Provinsi Kalsel. Angka rekap UPT disahkan dan dimutakhirkan setelah disetujui. Anda tetap dapat mengedit data warga di lapangan sewaktu-waktu.
            </div>

            <div class="atlas-field">
                <label for="val-submit-note">Catatan pengantar pengesahan (opsional)</label>
                <textarea id="val-submit-note" rows="3" placeholder="Contoh: Pencatatan data KK warga telah selesai dihimpun dari lapangan bersama aparat desa dan siap disahkan…"></textarea>
            </div>

            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" onclick="closeSubmitValidationModal()" class="atlas-button">Batal</button>
                <button type="submit" id="btn-confirm-submit-val" class="atlas-button atlas-button--primary">
                    <span id="btn-confirm-val-text">Kirim pengajuan ke provinsi</span>
                </button>
            </div>
        </form>
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
        toast.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded shadow-lg text-[13px] font-semibold text-white transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-2 ${isSuccess ? 'bg-[#287451]' : 'bg-[#B43D3D]'}`;
        toast.innerHTML = `<span>${isSuccess ? '✓' : '!'}</span> <span>${message}</span>`;
        setTimeout(() => {
            toast.className = 'fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded shadow-lg text-[13px] font-semibold text-white transition-all duration-300 transform translate-y-10 opacity-0 pointer-events-none flex items-center gap-2';
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
            badgeStage.textContent = 'Serah terima pemda';
            badgeStage.className = 'atlas-status atlas-status--warning';
        }
    } else {
        if (badgeStage) {
            badgeStage.textContent = 'Penempatan awal';
            badgeStage.className = 'atlas-status atlas-status--clean';
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
            <td colspan="10" class="py-12 text-center text-[#5A6E7D]">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-[3px] border-[#D4DEE7] border-t-[#2457A7] mb-2"></div>
                <div>Memuat data registri warga…</div>
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
                tableBody.innerHTML = `<tr><td colspan="10" class="py-8 text-center text-[#B43D3D] font-semibold">Gagal memuat data registri.</td></tr>`;
            }
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="10" class="py-8 text-center text-[#B43D3D] font-semibold">Terjadi kesalahan koneksi server.</td></tr>`;
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
                <td colspan="10" class="py-16 text-center text-[#5A6E7D]">
                    <svg class="w-12 h-12 mx-auto text-[#D4DEE7] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <div class="font-semibold text-[#243746] text-sm">Belum ada data registri nama KK</div>
                    <div class="text-xs max-w-sm mx-auto mt-1">UPT ini baru memiliki data rekap angka agregat. Anda dapat menambahkan 1 KK secara manual atau mengunggah berkas Excel warga.</div>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <button type="button" onclick="openAddCardModal()" class="atlas-button atlas-button--primary" style="min-height: 32px; font-size: 13px;">+ Tambah 1 KK</button>
                        <button type="button" onclick="openImportModal()" class="atlas-button" style="min-height: 32px; font-size: 13px;">Impor file Excel</button>
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
            ? '<span class="atlas-status" style="color:#194482;background:#DAE7F6;"><span aria-hidden="true"></span>TPA (asal)</span>'
            : '<span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>TPS (lokal)</span>';

        let shmBadge = '<span class="atlas-status"><span aria-hidden="true"></span>Belum SHM</span>';
        if (c.land_certificate_status && c.land_certificate_status.toLowerCase().includes('sudah')) {
            shmBadge = '<span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Sudah SHM</span>';
        } else if (c.land_certificate_status && c.land_certificate_status.toLowerCase().includes('proses')) {
            shmBadge = '<span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Proses BPN</span>';
        }

        const origin = [c.origin_province, c.origin_regency].filter(Boolean).join(' — ') || '-';

        let docCell = '<span class="atlas-code">-</span>';
        if (c.document_path) {
            const ext = c.document_name ? c.document_name.split('.').pop().toUpperCase() : 'BERKAS';
            docCell = `
                <a href="/admin/family-cards/${c.id}/document" target="_blank" class="atlas-link" style="font-size:12px;" title="${c.document_name || 'Lihat berkas KK'}">${ext}</a>
            `;
        }

        html += `
            <tr class="hover:bg-[#F8FAFC] transition" id="reg-row-${c.id}">
                <td class="py-2.5 px-3 text-center text-[#5A6E7D] tabular-nums">${idx + 1}</td>
                <td class="py-2.5 px-3 font-semibold text-[#243746]">
                    <div>${c.head_of_family_name}</div>
                </td>
                <td class="py-2.5 px-3 text-center text-[11px] text-[#5A6E7D] tabular-nums">
                    <div>KK: <strong class="text-[#243746]">${c.family_card_number || '-'}</strong></div>
                    <div class="text-[10px]">NIK: ${c.nik || '-'}</div>
                </td>
                <td class="py-2.5 px-3 text-center font-semibold text-[#243746] tabular-nums">
                    ${c.family_members_count} <span class="text-[10px] font-normal text-[#5A6E7D]">jiwa</span>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <div>${typeBadge}</div>
                    <div class="text-[10px] text-[#5A6E7D] mt-0.5">${origin}</div>
                </td>
                <td class="py-2.5 px-3 text-center text-[#243746]">
                    ${c.housing_block || '-'}
                </td>
                <td class="py-2.5 px-3 text-center">
                    ${shmBadge}
                </td>
                <td class="py-2.5 px-3 text-center">
                    ${docCell}
                </td>
                <td class="py-2.5 px-3 text-[#5A6E7D] text-[11px] max-w-xs truncate">
                    ${c.notes || '-'}
                </td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button" onclick="openEditCardModal(${c.id})" class="p-1.5 rounded text-[#2457A7] hover:bg-[#EDF3FB] transition cursor-pointer" title="Edit data KK">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button type="button" onclick="deleteCard(${c.id}, '${addslashes(c.head_of_family_name)}')" class="p-1.5 rounded text-[#B43D3D] hover:bg-[#FBEDED] transition cursor-pointer" title="Hapus data KK">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
    document.getElementById('reg-stat-kk').innerHTML = `${Number(stats.total_kk).toLocaleString('id-ID')} <span class="text-xs font-normal text-[#5A6E7D]">KK</span>`;
    document.getElementById('reg-stat-pop').innerHTML = `${Number(stats.total_jiwa).toLocaleString('id-ID')} <span class="text-xs font-normal text-[#5A6E7D]">Jiwa</span>`;
    document.getElementById('reg-stat-avg').textContent = `Rata-rata: ${Number(stats.avg_jiwa).toLocaleString('id-ID', {minimumFractionDigits: 2})} Jiwa/KK`;
    document.getElementById('reg-stat-tpa').textContent = `TPA: ${stats.tpa_count} KK`;
    document.getElementById('reg-stat-tps').textContent = `TPS: ${stats.tps_count} KK`;
    document.getElementById('reg-stat-shm').innerHTML = `${Number(stats.shm_count).toLocaleString('id-ID')} <span class="text-xs font-normal text-[#5A6E7D]">SHM</span>`;

    if (upt) {
        document.getElementById('reg-stat-rekap-compare').textContent = `Rekap UPT: ${Number(upt.current_aggregate_kk).toLocaleString('id-ID')} KK`;

        // Update status validasi di drawer header
        const valBadge = document.getElementById('reg-badge-validation');
        if (valBadge) {
            valBadge.classList.remove('hidden');
            if (upt.has_pending_validation) {
                valBadge.className = 'atlas-status atlas-status--warning';
                valBadge.innerHTML = `<span aria-hidden="true"></span> Menunggu verifikasi (draf #${upt.pending_validation_id})`;
            } else if (upt.is_verified) {
                valBadge.className = 'atlas-status atlas-status--clean';
                valBadge.innerHTML = `<span aria-hidden="true"></span> Disahkan provinsi`;
            } else {
                valBadge.className = 'atlas-status';
                valBadge.innerHTML = `<span aria-hidden="true"></span> Draf pengisian lapangan`;
            }
        }

        // Update tombol sync / submit validasi
        const btnSync = document.getElementById('btn-sync-rekap');
        if (btnSync) {
            if (upt.can_direct_sync) {
                // Super Admin
                btnSync.className = 'atlas-button atlas-button--primary';
                btnSync.style.minHeight = '34px';
                btnSync.title = 'Sinkronkan langsung jumlah KK & Jiwa dari registri ini ke master UPT';
                btnSync.innerHTML = `
                    <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Sinkronkan ke rekap UPT</span>
                `;
                btnSync.onclick = syncRegistryToUpt;
            } else {
                // Operator Wilayah
                if (upt.has_pending_validation) {
                    btnSync.className = 'atlas-button';
                    btnSync.style.minHeight = '34px';
                    btnSync.title = `Pengajuan pengesahan buku registri ini sedang ditinjau di antrean verifikasi provinsi (draf #${upt.pending_validation_id})`;
                    btnSync.innerHTML = `
                        <span class="atlas-status atlas-status--warning"><span aria-hidden="true"></span>Menunggu verifikasi (#${upt.pending_validation_id})</span>
                    `;
                    btnSync.onclick = function() {
                        alert(`Pengajuan pengesahan buku registri UPT ini sedang ditinjau oleh Super Admin Provinsi Kalsel (draf #${upt.pending_validation_id}). Anda tetap dapat menambahkan atau memperbarui data warga di lapangan.`);
                    };
                } else {
                    btnSync.className = 'atlas-button atlas-button--primary';
                    btnSync.style.minHeight = '34px';
                    btnSync.title = 'Kirimkan buku registri warga ini ke Provinsi untuk disahkan dan disinkronkan ke angka master';
                    btnSync.innerHTML = `
                        <svg class="atlas-icon" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m22 2-7 20-4-9-9-4Z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M22 2 11 13"></path></svg>
                        <span>Ajukan pengesahan buku ke provinsi</span>
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
            badgeRow.textContent = `${stats.total_kk} KK terdata`;
            badgeRow.className = 'atlas-status atlas-status--clean';
        } else {
            badgeRow.textContent = '0 KK (belum diisi)';
            badgeRow.className = 'atlas-status';
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

            if (cellKk) cellKk.innerHTML = `${Number(data.data.count_kk).toLocaleString('id-ID')} <span class="text-[10px] font-normal text-[#5A6E7D]">KK</span>`;
            if (cellPop) cellPop.textContent = Number(data.data.count_pop).toLocaleString('id-ID');
            if (cellRatio && data.data.count_kk > 0) {
                const r = (data.data.count_pop / data.data.count_kk).toFixed(2).replace('.', ',');
                cellRatio.textContent = r;
            }

            // Perbarui KPI atas
            if (currentRegistryStage === 'placement' && data.data.total_all_placement_kk) {
                const kpiKk = document.getElementById('kpi-total-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_all_placement_kk).toLocaleString('id-ID')} <span class="text-base font-semibold text-[#5A6E7D]">KK</span>`;
                const kpiPop = document.getElementById('kpi-total-pop');
                if (kpiPop) kpiPop.textContent = Number(data.data.total_all_placement_pop).toLocaleString('id-ID');
            } else if (currentRegistryStage === 'handover' && data.data.total_all_handover_kk) {
                const kpiKk = document.getElementById('kpi-handover-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_all_handover_kk).toLocaleString('id-ID')} <span class="text-base font-semibold text-[#5A6E7D]">KK</span>`;
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
