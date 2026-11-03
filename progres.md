# Progres Perombakan Desain — Atlas Kerja SIGAP-TRANS (Operator · Login · WebGIS)

Referensi: `planning.md` · Tanggal mulai: (diisi saat eksekusi)

**Fase Super Admin (Tahap 1–4): SELESAI 2026-10-02** — unifikasi Atlas sebagai desain tunggal aplikasi selesai; riwayat detail dilihat di riwayat git.

Scope fase ini: perombakan halaman **Login**, antarmuka **Operator Kabupaten**, dan **WebGIS publik** mengikuti identitas visual Atlas Kerja.

Legenda: `[x]` selesai · `[~]` sebagian/QA · `[ ]` belum dikerjakan

---

## 1. Status Ringkas

**SEMUA TAHAP SELESAI (O1–O6).** Atlas Kerja kini menjadi satu-satunya identitas visual aplikasi: Login, Operator, Super Admin, dan WebGIS.
- O2–O4 (antarmuka Operator): **SELESAI**
- O1 (Login): **SELESAI**
- O5 (WebGIS): **SELESAI**
- O6 (Pembersihan): **SELESAI**
- Sisa: QA visual browser manual + `php artisan test` di environment dev utama yang mendukung PostgreSQL (terbukti: kegagalan test 24/25 di environment ini juga terjadi pada kode sebelum perombakan — bukan regresi).

---

## O1 — Login Atlas — SELESAI

- `[x]` Hapus font Inter & palet navy `#0B1849` / hijau `#124D1C` / gold `#E4B028` dari `auth/login.blade.php`
- `[x]` Layout kartu Atlas: putih, border tipis `#D4DEE7`, radius 7px, brand mark biru `#2457A7` + wordmark Barlow
- `[x]` Input + focus ring biru Atlas, tombol `atlas-button--primary`
- `[x]` Panel akun pengujian: gaya Atlas + gate `@unless(app()->environment('production'))`
- `[x]` Class `.atlas-auth-*` di `atlas.css` (top bar, card, brand, body, panel uji, responsif 640px)
- `[~]` QA visual + alur login 4 peran — menunggu pemeriksaan browser

File terkait:
- `resources/views/auth/login.blade.php`
- `resources/css/atlas.css`

---

## O2 — Fondasi Operator — SELESAI

- `[x]` Sidebar: grup menu peran Operator (Ringkasan, Data UPT wilayah, Arsip dokumen, Draf usulan) tanpa Pelaporan/Administrasi Super Admin
- `[x]` Breadcrumb + brand link menunjuk `operator.dashboard` untuk peran Operator; nama halaman per-route operator ditambahkan
- `[x]` `x-stat-card`: cabang MP072 dihapus — semua peran memakai render Atlas
- `[x]` Route `operator.upt.registri` + `OperatorUptController@registri` (delegasi ke `FamilyCardManagementController@page`, guard role middleware)
- `[x]` Helper `User::pendingRequestsCount()` untuk badge sidebar "Draf usulan"

File terkait:
- `resources/views/layouts/admin.blade.php`
- `resources/views/components/stat-card.blade.php`
- `routes/web.php`
- `app/Http/Controllers/Operator/OperatorUptController.php`
- `app/Models/User.php`

---

## O3 — Dashboard Operator — SELESAI

- `[x]` Banner gradien navy+amber → `atlas-page-heading` (judul kabupaten/wilayah + identitas operator) + aksi "Ajukan draf usulan"
- `[x]` Switcher wilayah kartu besar → filter ringkas `atlas-filter` di toolbar panel
- `[x]` 4 kartu stat MP072 → metrik strip `atlas-metrics` (UPT + rincian status, KK penempatan, KK serah terima, draf pending)
- `[x]` Tabel UPT header navy → `atlas-table` terang + identitas UPT berpasangan (kode·kabupaten / nama / Kini: desa)
- `[x]` Fallback SHM `'100% SHM'` → `Belum tercatat`
- `[x]` Emoji status + `animate-pulse` dihapus → `x-admin.status` badge semantik
- `[x]` Drawer registri (`include registri_warga`) dihapus → link ke halaman registri penuh `operator.upt.registri`
- `[x]` Riwayat draf usulan → `atlas-table` + badge semantik verifikasi
- `[~]` QA: filter wilayah, ajukan usulan, buka registri — menunggu pemeriksaan browser

File terkait:
- `resources/views/operator/dashboard_utama/dashboard_utama.blade.php`

---

## O4 — Halaman Operator Lain — SELESAI

- `[x]` `data_upt_wilayah` → `atlas-table` + toolbar filter (kabupaten/status/baris) + chip aktif + kolom penempatan/serah terima dengan tahun & jiwa
- `[x]` `draf_usulan` index → tab status Atlas (Semua/Menunggu/Disetujui/Ditolak) + filter kabupaten + tabel terang
- `[x]` `draf_usulan` create → form Atlas dikelompokkan 3 bagian (sasaran → perubahan data → lampiran), tanpa emoji, radio traffic-light jadi select
- `[x]` `draf_usulan` show → intro identitas UPT + alert status verifikasi + tabel diff dengan `atlas-diff-changed`; ringkasan registri memakai metrik strip
- `[x]` Halaman registri (`admin/registri_warga/registri`) route-aware: Operator memakai `operator.upt.registri`, link kembali ke Data UPT wilayah
- `[x]` Halaman arsip dokumen (`admin/repositori_e_arsip_bast`) route-aware: Operator memakai `operator.documents.*`, tombol unggah/hapus disembunyikan
- `[~]` QA alur pengajuan end-to-end — menunggu pemeriksaan browser

File terkait:
- `resources/views/operator/data_upt_wilayah/data_upt_wilayah.blade.php`
- `resources/views/operator/draf_usulan/{draf_usulan,create,show}.blade.php`
- `resources/views/admin/registri_warga/registri.blade.php`
- `resources/views/admin/repositori_e_arsip_bast/repositori_e_arsip_bast.blade.php`

---

## Verifikasi Build

- `[x]` `npm run build` sukses (atlas.css 21.91 kB, app.css 99.50 kB setelah pembersihan O6)
- `[x]` `php artisan view:cache` sukses — semua Blade valid
- `[x]` QA statis menyeluruh: login/operator/webgis/pagination/verifikasi bersih dari palet lama, emoji, dan animasi dekoratif
- `[ ]` `php artisan test` di environment dev utama (PostgreSQL) — kegagalan 24/25 di env ini terbukti bukan regresi (uji `git stash`)
- `[ ]` QA browser menyeluruh: login → operator → webgis → verifikasi (checklist di bawah)

---

## O5 — WebGIS — SELESAI

- `[x]` Header putih border tipis + brand Atlas (mark biru + wordmark) + tombol aksi biru `#2457A7`
- `[x]` Panel filter mengambang Atlas terang: strip judul `#EDF2F6`, input putih focus biru, badge hitung `#DAE7F6` (semua id/hook JS **tidak berubah**)
- `[x]` Legenda + basemap switcher + kontrol layer: panel putih border tipis radius 7px, warna status semantik Atlas (`#287451`/`#97620B`/`#B43D3D`)
- `[x]` Drawer detail UPT: header identitas berpasangan (kode·kabupaten / nama / Kini: desa) + badge `atlas-status` + kartu terang
- `[x]` Popup marker: gaya Atlas, badge tanpa emoji, tombol aksi biru
- `[x]` Loading overlay: spinner tunggal di kanvas terang (hapus spinner ganda + blur navy)
- `[x]` Emoji filter option (📍🟢🟡🔴) dihapus dari select
- `[x]` Font Barlow Semi Condensed + Source Sans 3 (ganti DM Sans + IBM Plex Mono)
- `[x]` Variabel `--gis-*` di `app.css` diselaraskan token Atlas (`--gis-blue` #2457A7 dst.) + `.atlas-status` scoped untuk shell webgis
- `[x]` Marker cluster & popup Leaflet: biru Atlas (inner #2457A7, kritis #B43D3D), radius 7px, tooltip Source Sans 3
- `[x]` `switchBasemap` di `webgis-map.js`: class tombol aktif/nonaktif diselaraskan Atlas
- `[~]` QA: filter live, marker cluster, drawer fetch API tetap berfungsi — menunggu pemeriksaan browser

File terkait:
- `resources/views/webgis/index.blade.php`
- `resources/css/app.css` (blok `.webgis-shell`, cluster, popup, tooltip)
- `resources/js/webgis-map.js` (render popup & drawer — logika/filter tidak diubah)

---

## O6 — Pembersihan & QA Akhir — SELESAI

- `[x]` Audit menyeluruh: palet `brand.*` MP072 & alias `sigap-*` — **0 pemakaian** di view/JS
- `[x]` `tailwind.config.js`: hapus `brand.*` MP072 + alias `sigap-*`; font default `Plus Jakarta Sans/Inter` → `Source Sans 3`; `status.*` & `shadow-ambient-*` dipertahankan (masih dipakai template PDF & drawer registri warga Super Admin)
- `[x]` `admin/partials/pagination.blade.php` direstyling Atlas (dipakai 12 tabel aktif; label "Preview" → "Sebelumnya", tanpa navy/rounded-2xl)
- `[x]` `admin/verifikasi_draf_usulan/show.blade.php` direstyling Atlas penuh (halaman diff checker Super Admin): header panel terang, badge emoji → `x-admin.status`, tabel diff biru Atlas, form keputusan hijau/merah semantik
- `[x]` Hapus 5 komponen mati: `btn`, `page-header`, `table`, `badge`, `status-badge` (0 pemakaian)
- `[x]` Hapus wrapper `operator/dashboard.blade.php` (controller langsung render `dashboard_utama`)
- `[x]` Drawer registri warga (`registri_warga.blade.php`) **direstyling menyeluruh ke Atlas** (tindak lanjut QA browser): drawer (header identitas berpasangan + metrik ringkas + tombol Atlas), modal tambah/edit KK (`atlas-dialog` + `atlas-form-grid`), modal impor, modal pengesahan, badge/baris tabel & tombol dinamis di JS, toast, spinner — seluruh logika JS, id elemen, dan endpoint `/admin/family-cards/*` tidak berubah; varian dialog baru `.atlas-dialog-toggle` (buka via toggle class `hidden`, kompatibel mekanisme JS registri)
- `[x]` `npm run build` sukses (app.css menyusut 101.15 → 99.50 kB setelah token mati dibuang)
- `[x]` `php artisan view:cache` sukses — semua Blade valid
- `[x]` `php artisan test`: 24/25 gagal di environment ini — **terbukti gagal juga pada kode sebelum perombakan** (uji `git stash`), penyebab: `pg_available_extensions` tidak tersedia di SQLite; wajib diulang di environment dev utama (PostgreSQL)
- `[x]` QA statis 6/6 PASS: MP072 bersih dari pagination & verifikasi, tidak ada rujukan komponen mati, tailwind bersih, font default benar, drawer registri utuh
- `[x]` **Fix bug QA browser**: token `--atlas-*` semula hanya didefinisikan di `.atlas-shell`, sehingga tombol/brand di halaman login (class `atlas-auth`) transparan — definisi token dipindahkan ke `:root` (global, berlaku untuk shell + login + webgis)

File terkait:
- `tailwind.config.js`
- `resources/views/admin/partials/pagination.blade.php`
- `resources/views/admin/verifikasi_draf_usulan/show.blade.php`
- `resources/views/components/{btn,page-header,table,badge,status-badge}.blade.php` (dihapus)
- `resources/views/operator/dashboard.blade.php` (dihapus)

---

## 2. Keputusan Desain (terkonfirmasi user)

- Login & WebGIS ikut palet Atlas; WebGIS hanya merombak "perabot" sekitar peta, peta/spasial tidak disentuh.
- WebGIS: panel terang (bukan full match semantik).
- Registri warga Operator: pindah ke halaman penuh Atlas, drawer dihapus dari dashboard.
- Panel akun pengujian login: dipertahankan, gaya Atlas, hanya tampil bila `APP_ENV != production`.
- Dashboard Operator: dirombak menyeluruh (struktur + visual), bukan sekadar ganti warna.
- Prinsip anti "AI Slop": tanpa emoji sebagai ikon, tanpa animasi dekoratif, tanpa angka/klaim statis, fallback `Belum tercatat`, identitas UPT berpasangan konsisten.

---

## 3. Checklist QA Browser (sebelum rilis)

**Login (peran apa pun)**
1. Kanvas terang + kartu putih border tipis + brand mark biru; panel akun uji muncul di non-production.
2. Login gagal → pesan error tampil di bawah input; login sukses → diarahkan sesuai peran.

**Operator Kabupaten (`operator.tapin@…`)**
3. Sidebar: Ringkasan / Data UPT wilayah / Arsip dokumen / Draf usulan (badge pending) — tanpa menu Super Admin.
4. Dashboard: page-heading kabupaten, metrik strip, filter wilayah, tabel UPT terang, riwayat usulan.
5. Data UPT wilayah: filter + chip aktif + pagination Atlas ("Sebelumnya/Berikutnya").
6. Registri: klik link Registri → halaman penuh Atlas, tab tahap, filter, ekspor CSV.
7. Ajukan usulan: form 3 bagian, pilih UPT → data terisi otomatis, kirim → muncul di riwayat.
8. Arsip dokumen: daftar tampil, tombol unggah/hapus disembunyikan, unduh berfungsi.

**Super Admin**
9. Verifikasi → detail usulan: header panel terang, badge tanpa emoji, tabel diff biru Atlas, Setujui/Tolak berfungsi.
10. Semua tabel: pagination gaya Atlas konsisten.

**WebGIS publik (incognito)**
11. Header putih + brand Atlas; metrik live terisi angka.
12. Filter live: kabupaten/status/dekade/pola/pencarian mengubah marker; badge hitung 124/124 ikut.
13. Klik marker → popup Atlas; "Buka riwayat lengkap" → drawer identitas berpasangan + badge status.
14. Basemap switch & tombol Pusatkan Kalsel berfungsi; cluster biru Atlas.
15. Fokus peta dari drawer menutup drawer dan menerbangkan peta ke titik.

**Umum**
16. Toggle collapse sidebar + drawer mobile (≤1023px).
17. Ekspor PDF/CSV per halaman mengikuti filter aktif.

---

## O7 — Single Coordinate Input (DMS & Desimal) — SELESAI

- `[x]` **Core Backend Parser** (`app/Support/CoordinateParser.php`):
  - Mendukung input DMS dengan simbol (`2°25'59.9"S 115°40'49.2"E`), DMS dengan kurung `(2°25'59.9"S 115°40'49.2"E)`, DMS berjarak spasi, desimal standar (`-2.433306, 115.680333`), desimal berkardinal (`2.433306 S, 115.680333 E`).
  - Validasi koordinat teritorial Kalimantan Selatan (`-5.5` s/d `-1.0` Lat, `114.0` s/d `117.5` Lng).
  - Konversi dua arah desimal ↔ format DMS standar.
- `[x]` **Frontend JS Parser & Sinkronisasi** (`resources/js/atlas.js`):
  - Parser fleksibel client-side dengan pembersihan tanda kurung pembungkus.
  - Expose `window.parseCoordinates` dan `window.toDmsString`.
- `[x]` **Form Operator Pengajuan UPT Baru** (`resources/views/operator/draf_usulan/create.blade.php`):
  - Mengganti 2 kolom input menjadi 1 kolom input tunggal dengan hidden fields `latitude` & `longitude`.
  - Real-time feedback badge warna status (hijau saat valid di Kalsel, merah saat di luar Kalsel, kuning saat belum lengkap).
  - Sinkronisasi dua arah: mengetik teks menggerakkan pin peta; mengklik atau menggeser pin di Leaflet langsung mengisi input dengan format DMS.
- `[x]` **Form Admin Kelola Master Data UPT** (`resources/views/admin/master_data_upt/partials/upt-form.blade.php`):
  - Mengganti 2 kolom input menjadi 1 kolom input tunggal dengan parser instan dan preview feedback koordinat.
- `[x]` **Backend Controllers**:
  - `OperatorChangeRequestController.php` & `UptManagementController.php` otomatis memparsing input `coordinates`, memvalidasi wilayah Kalsel, meregenerasi buffer poligon peta publik, dan menyimpan format DMS.
- `[x]` **Unit Tests**:
  - `scratch/test_single_coordinate_flow.php` (PHP) & `scratch/test_js_parser.js` (Node.js) 100% PASS pada seluruh skenario.

---

## O8 — Tombol Hapus UPT di Master Data UPT — SELESAI

- `[x]` **Tombol Tindakan Hapus** (`resources/views/admin/master_data_upt/master_data_upt.blade.php`):
  - Menambahkan tombol form `DELETE` pada kolom "Tindakan" bersebelahan dengan tautan "Perbarui" di dalam kontainer `.atlas-inline`.
  - Dilengkapi dialog konfirmasi native yang eksplisit dan informatif (`onsubmit="return confirm(...)`") untuk mencegah penghapusan tidak disengaja (sesuai POLA).
  - Tampil khusus untuk pengguna dengan peran `super_admin`.
- `[x]` **Rute Backend** (`routes/web.php`):
  - Menambahkan `Route::delete('/upt/{id}', [UptManagementController::class, 'destroy'])->name('upt.destroy')->whereNumber('id');`.
- `[x]` **Controller & Audit Log** (`app/Http/Controllers/Admin/UptManagementController.php`):
  - Implementasi metode `destroy(Request $request, int $id)`.
  - Pengecekan otorisasi `super_admin`.
  - Pembersihan berkas fisik dokumen digital di storage (`Storage::disk('public')`).
  - Pencatatan rekaman audit forensik `DELETE_UPT_LOCATION` di tabel `audit_logs`.
  - Flash notification berhasil: `Data UPT No. {upt_number} ({upt_name}) berhasil dihapus dari Master Data.`
- `[x]` **Verifikasi Otomatis**:
  - Script pengujian `scratch/test_upt_destroy.php` 100% PASS.

---

## O9 — Sinkronisasi Data UPT Operator Sesuai Wilayah Publikasi Super Admin — SELESAI

- `[x]` **Operator Data UPT Wilayah** (`app/Http/Controllers/Operator/OperatorUptController.php`):
  - Mengisolasi query kabupaten ke `Regency::where('is_visible', true)`.
  - Mengisolasi query tabel UPT dan kalkulasi metrik ringkasan ke UPT yang berada di kabupaten aktif (`whereHas('regency', fn($q) => $q->where('is_visible', true))`).
  - Sanitasi parameter query `regency_id` untuk mencegah manipulasi URL kabupaten nonaktif.
  - Membatasi akses registri warga (`registri()`) hanya pada UPT di wilayah aktif.
- `[x]` **Dashboard Operator** (`app/Http/Controllers/Operator/OperatorDashboardController.php`):
  - Dropdown wilayah dan metrik ringkasan kependudukan/UPT hanya mengagregasi kabupaten yang diaktifkan oleh Super Admin.
- `[x]` **Pengajuan Draf Usulan** (`app/Http/Controllers/Operator/OperatorChangeRequestController.php`):
  - Pilihan registrasi UPT baru dan pemutakhiran UPT eksisting hanya memuat kabupaten & UPT yang aktif.
  - Validasi backend `store()` menolak pengajuan atau pemutakhiran pada wilayah nonaktif.
- `[x]` **Repositori E-Arsip Dokumen** (`app/Http/Controllers/Admin/DocumentRepositoryController.php`):
  - Operator hanya dapat meninjau dan mengunduh berkas BAST/SK dari UPT di wilayah yang aktif.
- `[x]` **Blade Views**:
  - `data_upt_wilayah.blade.php`: Subtitle dan dropdown beradaptasi dinamis dengan jumlah kabupaten aktif.
  - `dashboard_utama.blade.php`: Header dan filter cakupan wilayah beradaptasi dinamis.
- `[x]` **Verifikasi Otomatis**:
  - `scratch/test_operator_visible_regencies.php` 100% PASS (5/5 tes lulus: isolasi query 25 UPT, sanitasi parameter, agregasi dashboard, form usulan, dan responsivitas saat Super Admin melakukan toggle visibilitas wilayah).

---

## O10 — Modul Master Kawasan Transmigrasi & SKP (Profil Desa & Demografi) — SELESAI

- `[x]` **Database Migrations & Skema**:
  - `skp_areas`: Penyimpanan master Kawasan & Satuan Kawasan Pengembangan (SKP), luas Ha, warna poligon, sumber data, dan jsonb polygon.
  - `skp_villages`: Penyimpanan profil desa dalam kawasan SKP (Kecamatan, Nama Desa, Luas Km², Penduduk L/P/Total terhitung otomatis, dan Jumlah KK) sesuai tabel rujukan BPS/DPMD.
  - Relasi `skp_id` pada `upt_locations` untuk asosiasi desa definitif UPT ke kawasan SKP.
- `[x]` **Models & Relasi Eloquent**:
  - `SkpArea.php`: Relasi `regency()`, `villages()`, `uptLocations()`, agregator kependudukan, dan method `toGeoJsonFeature()`.
  - `SkpVillage.php`: Relasi `skpArea()`, `uptLocation()`, dan auto-calc total penduduk.
  - `UptLocation.php` & `Regency.php`: Penambahan relasi `skpArea()` dan `skpAreas()`.
- `[x]` **Data Seeding Eksisting**:
  - `SkpAreaSeeder.php`: Mengimpor 8 SKP (termasuk SKP P, E, G, B, C) dan 14 desa Kecamatan Mandastana lengkap dengan demografi riil dari tabel rujukan (Bangkit Baru, Antasan Segera, Terantang, Tanipah, Puntik Luar, Puntik Dalam, Tabing Rimbah, Pantai Hambawang, Tatah Alayung, Puntik Tengah, Lokrawa, Sei Ramania, Karang Indah, Karang Bunga).
- `[x]` **Navigasi Sidebar Admin**:
  - Menu baru **"Kawasan & SKP"** di kelompok **DATA WILAYAH** persis di bawah "Data UPT".
- `[x]` **Antarmuka CRUD Super Admin (`resources/views/admin/kawasan_skp/`)**:
  - `index.blade.php`: Tabel daftar SKP, filter kabupaten, pencarian, metrik agregat kawasan, dan daftar chip desa yang dapat diklik.
  - `partials/modal-village-detail.blade.php`: Modal pop-up profil desa saat nama desa di tabel diklik (menampilkan luas Km², rincian penduduk L/P/Total, KK, rasio gender, dan bagian SKP mana).
  - `partials/skp-form.blade.php`: Form 3 panel Atlas (Identitas SKP, tabel bersih desa dengan tombol "+ Tambah data desa (1 per 1)", input sumber data, dan unggah GeoJSON poligon batas).
  - `partials/modal-village-form.blade.php`: Modal input/edit data desa 1 per 1 dengan auto-hitung Total Jiwa (L+P) dan pencegahan layout overflow.
  - `create.blade.php` & `edit.blade.php`: Halaman pembungkus form create/edit.
  - `show.blade.php`: Rincian lengkap SKP, tabel kependudukan desa per SKP, dan pratinjau status poligon.
  - `pdf.blade.php`: Template dokumen cetak resmi PDF rekapitulasi Kawasan & SKP se-Kalsel.
- `[x]` **Backend & API Controller**:
  - `SkpManagementController.php`: CRUD lengkap, upload GeoJSON parsing, sync baris desa, endpoint JSON `villageDetail()`, ekspor PDF, dan penanganan aman null/optional fallback `sumber_data` & `color`.
  - `UptLocationApiController.php`: Endpoint dinamis `/api/skp-locations` untuk menyajikan FeatureCollection poligon SKP dari database ke peta publik WebGIS.
- `[x]` **WebGIS (`webgis-map.js`)**:
  - Fetch batas SKP diarahkan ke endpoint dinamis `/api/skp-locations`.




