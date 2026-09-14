DOKUMEN PROPOSAL & BAHAN KONSULTASI

Modernisasi Data Transmigrasi Kalsel Melalui Platform WebGIS Interaktif

Perancangan Sistem Pemetaan & Monitoring Rekam Jejak UPT Sejak Masa Pra-Pelita (1953) s/d 2025 di Kalimantan Selatan

Target Audien: Dinas / Kabid Transmigrasi

Cakupan Data: 124 UPT di 9 Kabupaten Se-Kalsel

Format: PRD Berorientasi Pengguna

BAGIAN 1: PEMBUKA & ELEVATOR PITCH

Dalam kurun waktu lebih dari 70 tahun penyelenggaraan transmigrasi di Kalimantan Selatan (1953–2025), Pemerintah telah mengawal penempatan sebanyak 63.701 KK (256.307 Jiwa) dan menyerahkan 64.942 KK (262.871 Jiwa) kepada Pemerintah Daerah yang tersebar di  Unit Pemukiman Transmigrasi (UPT). Inisiatif ini dirancang untuk merevolusi tata kelola arsip dan pelaporan kedinasan menjadi sistem geospasial interaktif yang dapat diakses instan.

BAGIAN 2: POTRET MASALAH SAAT INI (PAIN POINTS LAPANGAN)

Berdasarkan evaluasi operasional pengelolaan data transmigrasi di dinas, terdapat empat kendala utama yang dihadapi:

KONDISI SAAT INI (TANTANGAN OPERASIONAL)

DAMPAK BAGI DINAS & PENGAMBILAN KEPUTUSAN PIMPINAN

1. Arsip Terpencar & Rawan Rusak:Data penyerahan UPT lintas dekade (sejak 1953) tersebar dalam file Excel lepas dan bundel map kertas fisik di gudang arsip.

Pencarian data memerlukan waktu lama; dokumen Berita Acara Serah Terima (BAST) dan SK Pelepasan rawan terselip atau rusak fisik.

2. Perubahan Nama Wilayah (Disorientasi Data):Banyak lokasi yang di dokumen lama bernama 'SP' (Satuan Pemukiman), saat ini telah mekar menjadi desa definitif bahkan ibukota kecamatan.

Sulit mencocokkan riwayat asal-usul tanah UPT dengan nama desa definitif saat ini ketika ada permohonan klarifikasi batas wilayah atau legalitas tanah warga.

3. Sengketa & Legalitas Lahan Lambat Terpantau:Status sertifikasi (SHM) dan potensi tumpang tindih kawasan (hutan, tambang, perkebunan swasta) belum terpetakan statusnya secara visual.

Pimpinan sulit mendeteksi secara cepat mana UPT yang sudah Clean & Clear dan mana lokasi yang membutuhkan koordinasi atau mediasi lintas instansi.

4. Laporan Eksekutif Masih Manual:Setiap kali pimpinan membutuhkan bahan rapat pimpinan daerah, staf harus menyusun dan menghitung manual dari tabel mentah.

Penyajian data untuk koordinasi tingkat provinsi atau audiensi kementerian kurang responsif dan belum berbasis peta visual modern.

BAGIAN 3: SOLUSI YANG DITAWARKAN

Membangun platform WebGIS & Portal Pemetaan Transmigrasi Kalsel dengan rekomendasi nama kedinasan: SIGAP-TRANS KALSEL (Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi) atau GEOTRANS-KALSEL. Platform ini mengintegrasikan titik peta geografis riil di 9 kabupaten, riwayat lengkap UPT, status hukum/sertifikasi terkini, dan repositori digital e-arsip BAST.

BAGIAN 4: DETAIL PRODUK (PRD DALAM PERSPEKTIF PENGGUNA)

1. Layar Peta Interaktif Se-Kalsel (Interactive Map)

Pimpinan atau staf membuka browser dan langsung melihat peta wilayah Kalimantan Selatan dengan sebaran titik lokasi UPT di 9 kabupaten.

Dilengkapi fitur marker clustering agar peta rapi saat di-zoom out, serta pilihan basemap Peta Jalan dan Citra Satelit untuk melihat tutupan lahan kebun warga secara nyata.

2. Jendela Informasi Instan (Kartu Riwayat UPT Sekali Klik)

Saat pin UPT diklik, muncul kartu pop-up ringkas berisi: nama UPT lama vs nama desa saat ini, pola usaha budidaya (PIR Sawit, Pasang Surut, TPH, Padi Rawa), tahun masuk vs serah terima, dinamika angka KK/Jiwa, serta catatan perkembangan lapangan.

3. Lampu Indikator Permasalahan Lahan (Traffic Light Alert)

Pimpinan dapat mengenali kondisi legalitas UPT seketika melalui kode warna pin pada peta:

• PIN HIJAU (Clean & Clear): Sertifikasi SHM tuntas 100%, fasos/fasum telah diserahterimakan penuh ke Pemda.

• PIN KUNING (Monitoring Berkala): Lahan aman, butuh pemeliharaan infrastruktur penunjang (tanggul/drainase/jalan).

• PIN MERAH (Prioritas Khusus): Kendala batas kawasan hutan (HPT/KPHP) atau izin usaha swasta butuh mediasi pimpinan.

4. Panel Saring Cepat & E-Arsip Digital

Memungkinkan penyaringan data dalam 2 kali klik (berdasarkan kabupaten, dekade penempatan 1950-an s/d 2000-an, atau status pin merah).

Setiap detail UPT dilengkapi tombol unduh file PDF scan Berita Acara Serah Terima (BAST) dan SK Pelepasan Kawasan untuk pembuktian audit hukum instan.

5. Ruang Kendali Pimpinan & Cetak Laporan 1-Klik

Dashboard menyajikan akumulasi provinsi secara otomatis (Total 63.701 KK Penempatan & 64.942 KK Penyerahan). Dilengkapi tombol cetak laporan resmi berformat PDF berstandar dinas serta ekspor data hasil filter ke file Excel (.xlsx).

BAGIAN 5: FLOWCHART ALUR KERJA SISTEM (SISTEMIK & OPERASIONAL)

Flowchart berikut mengilustrasikan alur transformasi data dari dokumen arsip manual menjadi visualisasi geospasial interaktif hingga pemanfaatan oleh pimpinan dan pemangku kepentingan:

Gambar 1: Flowchart Alur Pemrosesan Data dan Interaksi Pengguna WebGIS Transmigrasi Kalsel

Penjelasan Tahapan Flowchart:

1. Tahap Input Data: Pengumpulan data tabular 124 UPT di 9 kabupaten, data spasial (koordinat dan batas desa dari BIG), serta dokumen scan BAST fisik.

2. Tahap Basis Data Spasial: Validasi nama desa hasil pemekaran, penyimpanan ke PostgreSQL/PostGIS, dan pengarsipan digital BAST.

3. Tahap WebGIS Engine: Leaflet.js memproses visualisasi peta, mengklasifikasi status warna permasalahan (Hijau, Kuning, Merah), dan menghitung statistik agregat dinamis.

4. Tahap Pengguna & Aksi: Pimpinan memantau dashboard untuk keputusan cepat, Kanwil BPN melakukan percepatan SHM, serta staf mencetak laporan PDF/Excel resmi.

BAGIAN 6: MANFAAT LANGSUNG BAGI DINAS & PIMPINAN

1. Penyelamatan Aset Historis 72 Tahun: Menjaga rekam jejak pembangunan transmigrasi Pemprov Kalsel sejak 1953 tetap aman dalam format digital terpusat yang tidak lapuk oleh waktu.

2. Mendukung Program SPBE & Satu Data Indonesia: Bukti nyata inovasi digital Disnakertrans Kalsel dalam penyelenggaraan Sistem Informasi Geospasial kedinasan.

3. Kecepatan & Akurasi Pengambilan Keputusan: Pimpinan dapat merespons permohonan data dari Gubernur, DPRD, atau Kementerian dalam hitungan menit berbasis data geospasial valid.

4. Harmonisasi Sengketa Pertanahan dengan BPN: Mempercepat verifikasi batas eks-transmigrasi untuk program sertifikasi redistribusi tanah bersama BPN.

BAGIAN 7: ROADMAP PENGERJAAN (4 TAHAP PRAKTIS)

TAHAPAN

FOKUS AKTIVITAS KERJA

OUTPUT NYATA YANG DIHASILKAN

Pondasi Data

Migrasi data tabel 124 UPT di 9 kabupaten, verifikasi nama desa terkini, dan pengikatan titik koordinat pusat (centroid).

Database master transmigrasi Kalsel siap pakai & terverifikasi.

Modul WebGIS

Pembangunan peta interaktif Leaflet.js, kartu popup riwayat UPT, panel filter spasial, dan indikator warna status permasalahan.

Prototipe WebGIS interaktif yang dapat diakses di komputer/tablet.

Integrasi E-Arsip

Digitalisasi/unggah sampel berkas BAST/SK penyerahan, pembuatan dashboard statistik pimpinan, dan uji coba internal dinas.

Sistem terintegrasi penuh dengan fitur unduh dokumen dan cetak laporan.

Finalisasi

Pelatihan singkat staf operator, presentasi final, dan peluncuran resmi portal.

Sistem resmi beroperasi (Go-Live) dan diserahterimakan ke dinas.