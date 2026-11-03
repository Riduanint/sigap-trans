<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SIGAP-TRANS KALSEL — Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi</title>
    <meta name="description"
        content="Portal resmi Satu Data geospasial, registri kependudukan, dan repositori arsip permukiman transmigrasi Provinsi Kalimantan Selatan (1953–2025). Disnakertrans Prov. Kalsel.">
    <meta name="keywords"
        content="SIGAP-TRANS, Transmigrasi Kalimantan Selatan, WebGIS Kalsel, UPT Kalsel, Disnakertrans Kalsel, Peta Transmigrasi, BAST Transmigrasi, Satu Data Kalsel">
    <meta name="author" content="Dinas Tenaga Kerja dan Transmigrasi Provinsi Kalimantan Selatan">

    <!-- Open Graph / Meta Sosial -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="SIGAP-TRANS KALSEL — Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi">
    <meta property="og:description"
        content="Portal resmi data spasial, perkembangan permukiman, dan arsip transmigrasi Kalimantan Selatan sejak 1953.">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- Typography: Barlow Semi Condensed untuk Display Otoritatif & Source Sans 3 untuk Teks Data -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700;800&family=Source+Sans+3:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/css/atlas.css', 'resources/js/app.js'])
</head>

<body class="landing">

    <header class="landing-header" id="landing-header">
        <div class="landing-container">
            <div class="landing-header-inner">
                <!-- Brand Mark & Identitas Instansi -->
                <a href="{{ route('home') }}" class="landing-brand" title="Portal Resmi SIGAP-TRANS Kalsel">
                    <div class="atlas-brand-mark" style="width: 40px; height: 42px;">
                        <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            style="width: 24px; height: 24px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"></path>
                        </svg>
                    </div>
                    <div class="landing-brand-text">
                        <strong>SIGAP-TRANS <span>KALSEL</span></strong>
                        <small>Disnakertrans Prov. Kalimantan Selatan</small>
                    </div>
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="landing-hero" id="beranda">
            <div class="landing-container">
                <div class="landing-hero-grid">
                    <div class="landing-hero-content">
                        <!-- Civic Institutional Metadata Bar -->
                        <div class="landing-civic-meta reveal reveal-up">
                            <span class="landing-civic-badge">
                                <span class="landing-pulse-dot"></span>
                                SATU DATA GEOSPASIAL PROV. KALSEL
                            </span>
                            <span class="landing-civic-divider">|</span>
                            <span class="landing-civic-sub">Basis Data Historis 1953–2025</span>
                        </div>

                        <h1 class="landing-hero-title reveal reveal-up reveal-delay-1">
                            Atlas Geospasial Administrasi &amp; Persebaran Transmigrasi
                        </h1>

                        <p class="landing-hero-desc reveal reveal-up reveal-delay-2">
                            Akses resmi data geospasial Unit Permukiman Transmigrasi (UPT), legalitas hak tanah BAST & SHM,
                            serta rekam jejak demografi warga transmigran di seluruh wilayah Provinsi Kalimantan Selatan.
                        </p>

                        <!-- Primary Action Group -->
                        <div class="landing-hero-cta reveal reveal-up reveal-delay-3">
                            <a href="{{ route('peta') }}" class="landing-btn-hero" id="btn-hero-peta">
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="width: 19px; height: 19px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"></path>
                                </svg>
                                <span>Buka Peta Interaktif WebGIS</span>
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>

                            <a href="#kapabilitas" class="landing-btn-ghost">
                                <span>Pelajari Modul Data</span>
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Quick District Jump: Langsung filter kabupaten tanpa mikir -->
                        @if(isset($regencies) && $regencies->count() > 0)
                            <div class="landing-district-jump reveal reveal-up reveal-delay-4">
                                <div class="landing-district-jump-label">
                                    <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        style="width: 14px; height: 14px; color: var(--atlas-blue);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>Eksplorasi Langsung per Wilayah:</span>
                                </div>
                                <div class="landing-district-pills">
                                    @foreach($regencies as $reg)
                                        <a href="{{ route('peta', ['regency' => $reg->id]) }}" class="landing-district-pill"
                                            title="Buka peta filter Kabupaten {{ $reg->name }}">
                                            <strong>{{ $reg->name }}</strong>
                                            <span>{{ $reg->upt_locations_count }} UPT</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Cartographic Visual Viewport -->
                    <div class="landing-hero-visual reveal reveal-right reveal-delay-2">
                        <div class="landing-carto-card">
                            <div class="landing-carto-card-header">
                                <div class="landing-carto-card-title">
                                    <span class="landing-carto-indicator"></span>
                                    <span>INDEKS SPASIAL KALSEL • DISNAKERTRANS</span>
                                </div>
                                <span class="landing-carto-coords">CRS: EPSG:4326</span>
                            </div>

                            <div class="landing-hero-img-wrap">
                                <img src="{{ asset('images/landing/hero_map.jpg') }}"
                                    alt="Visualisasi Spasial Kawasan Permukiman Transmigrasi Kalimantan Selatan"
                                    class="landing-hero-img" width="800" height="533" loading="eager">
                                <div class="landing-carto-overlay-badge">
                                    <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        style="width: 15px; height: 15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                                        </path>
                                    </svg>
                                    <span>Pemetaan Poligon & Titik Koordinat Presisi</span>
                                </div>
                            </div>

                            <div class="landing-carto-card-footer">
                                <div class="landing-carto-meta-item">
                                    <small>DATUM GEODETIK</small>
                                    <strong>WGS 84 / UTM 50S</strong>
                                </div>
                                <div class="landing-carto-meta-item">
                                    <small>STANDARDISASI</small>
                                    <strong>Badan Informasi Geospasial (BIG)</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-registry-section" id="statistik" aria-label="Ringkasan Data Registri">
            <div class="landing-container">
                <div class="landing-registry-panel reveal reveal-up">
                    <div class="landing-registry-topline">
                        <div class="landing-registry-status">
                            <span class="landing-status-dot"></span>
                            <span class="landing-registry-status-text">STATUS DATA TERVERIFIKASI TINGKAT KABUPATEN/KOTA</span>
                        </div>
                        <div class="landing-registry-switcher" role="tablist" aria-label="Pilihan Data Statistik">
                            <button type="button" class="landing-switch-btn active" data-slide="0" role="tab" aria-selected="true" aria-label="Lihat Data UPT">
                                <span class="landing-switch-indicator"></span>
                                <span>Rekapitulasi UPT</span>
                            </button>
                            <button type="button" class="landing-switch-btn" data-slide="1" role="tab" aria-selected="false" aria-label="Lihat Data Kawasan SKP">
                                <span class="landing-switch-indicator"></span>
                                <span>Kawasan & SKP</span>
                            </button>
                        </div>
                        <div class="landing-registry-source">
                            Sumber: Bidang Transmigrasi Disnakertrans Prov. Kalsel
                        </div>
                    </div>

                    <div class="landing-registry-slides">
                        <!-- Slide 0: Unit Permukiman Transmigrasi (UPT) -->
                        <div class="landing-registry-slide active" data-slide="0">
                            <div class="landing-registry-grid">
                                <!-- Kolom 1: Total UPT -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num" data-target="{{ $totalUpt }}">{{ $totalUpt }}</span>
                                        <span class="landing-registry-unit">Lokasi</span>
                                    </div>
                                    <div class="landing-registry-label">Unit Permukiman (UPT)</div>
                                    <p class="landing-registry-desc">Kawasan transmigrasi tercatat dalam registri resmi Kalimantan Selatan</p>
                                </div>

                                <!-- Kolom 2: Kabupaten Tersebar -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num" data-target="{{ $totalKabupaten }}">{{ $totalKabupaten }}</span>
                                        <span class="landing-registry-unit">Kabupaten</span>
                                    </div>
                                    <div class="landing-registry-label">Sebaran Wilayah Terpublikasi</div>
                                    <p class="landing-registry-desc">Wilayah administratif yang diverifikasi aktif dan sinkron</p>
                                </div>

                                <!-- Kolom 3: Kepala Keluarga (KK) -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num" data-target="{{ $totalKK }}">{{ number_format($totalKK, 0, ',', '.') }}</span>
                                        <span class="landing-registry-unit">KK</span>
                                    </div>
                                    <div class="landing-registry-label">Kepala Keluarga Transmigran</div>
                                    <p class="landing-registry-desc">Warga penempatan tercatat dalam buku registri kependudukan</p>
                                </div>

                                <!-- Kolom 4: Rentang Historis Penempatan -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num" style="letter-spacing: -0.02em;">1953–2025</span>
                                    </div>
                                    <div class="landing-registry-label">72+ Tahun Rekam Jejak</div>
                                    <p class="landing-registry-desc">Arsip Berita Acara Serah Terima (BAST) & warkah legalitas</p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 1: Satuan Kawasan Pengembangan (SKP) -->
                        <div class="landing-registry-slide" data-slide="1">
                            <div class="landing-registry-grid">
                                <!-- Kolom 1: Total SKP -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num landing-num-emerald" data-target="{{ $totalSkp }}">{{ $totalSkp }}</span>
                                        <span class="landing-registry-unit">Kawasan</span>
                                    </div>
                                    <div class="landing-registry-label">Satuan Kawasan (SKP)</div>
                                    <p class="landing-registry-desc">Zona kawasan transmigrasi aktif tercatat sesuai batas delineasi spasial</p>
                                </div>

                                <!-- Kolom 2: Total Desa SKP -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num landing-num-emerald" data-target="{{ $totalDesaSkp }}">{{ $totalDesaSkp }}</span>
                                        <span class="landing-registry-unit">Desa</span>
                                    </div>
                                    <div class="landing-registry-label">Desa dalam Kawasan SKP</div>
                                    <p class="landing-registry-desc">Desa administratif yang tercakup dalam wilayah delineasi kawasan SKP</p>
                                </div>

                                <!-- Kolom 3: Total Luas (Ha) -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num landing-num-emerald" data-target="{{ round($totalLuasHa) }}">{{ number_format($totalLuasHa, 0, ',', '.') }}</span>
                                        <span class="landing-registry-unit">Ha</span>
                                    </div>
                                    <div class="landing-registry-label">Total Luas Delineasi SKP</div>
                                    <p class="landing-registry-desc">Hektar luas kawasan terpetakan berdasarkan standardisasi DPMD & BIG</p>
                                </div>

                                <!-- Kolom 4: Total Penduduk SKP -->
                                <div class="landing-registry-cell">
                                    <div class="landing-registry-num-wrap">
                                        <span class="landing-registry-num landing-num-emerald" data-target="{{ $totalPendudukSkp }}">{{ number_format($totalPendudukSkp, 0, ',', '.') }}</span>
                                        <span class="landing-registry-unit">Jiwa</span>
                                    </div>
                                    <div class="landing-registry-label">Penduduk di Wilayah SKP</div>
                                    <p class="landing-registry-desc">Estimasi total jiwa penduduk desa yang berada dalam cakupan kawasan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-editorial-section" id="tentang">
            <div class="landing-container">
                <div class="landing-editorial-grid">
                    <div class="landing-editorial-content reveal reveal-left">
                        <span class="landing-section-eyebrow">Tata Kelola Satu Peta</span>
                        <h2 class="landing-editorial-title">Penyatuan Arsip Sejarah & Pemetaan Presisi 7 Dekade Transmigrasi</h2>
                        <p class="landing-editorial-lead">
                            Program transmigrasi di Kalimantan Selatan telah berlangsung sejak awal era kemerdekaan 1953. 
                            <strong>SIGAP-TRANS</strong> hadir sebagai terobosan digitalisasi untuk mengatasi fragmentasi data spasial dan warkah hukum yang tersebar.
                        </p>
                        <p class="landing-editorial-body">
                            Melalui sistem ini, Disnakertrans menyatukan titik koordinat presisi, batas wilayah desa definitif, 
                            dokumen Berita Acara Serah Terima (BAST), serta riwayat sertifikasi tanah warga ke dalam satu basis data terverifikasi yang akuntabel dan mudah diakses.
                        </p>

                        <div class="landing-editorial-badges">
                            <div class="landing-badge-item">
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Mendukung Kebijakan Satu Peta (One Map Policy)</span>
                            </div>
                            <div class="landing-badge-item">
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Verifikasi Berjenjang Operator Kabupaten & Provinsi</span>
                            </div>
                        </div>
                    </div>

                    <div class="landing-editorial-pillars">
                        <!-- Pilar 1 -->
                        <div class="landing-pillar-row reveal reveal-right reveal-delay-1">
                            <div class="landing-pillar-num">01</div>
                            <div class="landing-pillar-body">
                                <h3>Standarisasi Geospasial Geocoding</h3>
                                <p>Setiap UPT dipetakan dengan koordinat latitude-longitude tervalidasi dan batas poligon wilayah berstandar Badan Informasi Geospasial.</p>
                            </div>
                        </div>

                        <!-- Pilar 2 -->
                        <div class="landing-pillar-row reveal reveal-right reveal-delay-2">
                            <div class="landing-pillar-num">02</div>
                            <div class="landing-pillar-body">
                                <h3>Kepastian Hukum & Monitoring SHM</h3>
                                <p>Pemantauan status hak atas tanah dan percepatan penerbitan Sertipikat Hak Milik (SHM) bersinergi dengan Kantor Pertanahan / ATR-BPN.</p>
                            </div>
                        </div>

                        <!-- Pilar 3 -->
                        <div class="landing-pillar-row reveal reveal-right reveal-delay-3">
                            <div class="landing-pillar-num">03</div>
                            <div class="landing-pillar-body">
                                <h3>Penyelamatan E-Arsip Dokumen Sejarah</h3>
                                <p>Digitalisasi warkah BAST penyerahan urusan transmigrasi ke pemerintah daerah agar terproteksi dari resiko kerusakan fisik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-showcase-section" id="kapabilitas">
            <div class="landing-container">
                <div class="landing-section-header reveal reveal-up">
                    <span class="landing-section-eyebrow">Kapabilitas Sistem</span>
                    <h2 class="landing-section-title">Infrastruktur Informasi Spasial & Legalitas Pertanahan</h2>
                    <p class="landing-section-desc">
                        Dirancang khusus untuk memenuhi standar keandalan analisis pimpinan, operasional staf dinas kabupaten, 
                        serta transparansi layanan publik.
                    </p>
                </div>

                <div class="landing-showcase-grid">
                    <!-- Featured Card: WebGIS Flagship (Lebar & Menonjol) -->
                    <div class="landing-showcase-flagship reveal reveal-up reveal-delay-1">
                        <div class="landing-showcase-tag">LAYANAN UTAMA</div>
                        <div class="landing-showcase-content">
                            <div class="landing-showcase-icon-box">
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 26px; height: 26px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"></path>
                                </svg>
                            </div>
                            <h3>Portal WebGIS Interaktif Multi-Layer</h3>
                            <p>
                                Visualisasi spasial berkecepatan tinggi dengan integrasi citra satelit resolusi tinggi, batas administratif kabupaten/kota, 
                                penelusuran nama desa definitif, serta filter dinamis tanpa reload halaman.
                            </p>
                            <div class="landing-flagship-chips">
                                <span>Layer Citra Satelit</span>
                                <span>Batas Rupa Bumi BIG</span>
                                <span>Marker Koordinat UPT</span>
                                <span>Filter Kabupaten Cepat</span>
                            </div>
                        </div>
                        <div class="landing-flagship-action">
                            <a href="{{ route('peta') }}" class="landing-btn-hero" style="width: 100%; justify-content: center;">
                                <span>Jelajahi Peta Sekarang</span>
                                <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Side Card 1: Repositori E-Arsip Dokumen BAST -->
                    <div class="landing-showcase-card reveal reveal-up reveal-delay-2">
                        <div class="landing-showcase-icon-box" style="background: #eef7f2; color: #107c41;">
                            <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <h4>Repositori E-Arsip Dokumen BAST</h4>
                        <p>
                            Penyimpanan aman warkah Berita Acara Serah Terima pembinaan dari pemerintah pusat ke daerah, SK penetapan lokasi menteri/gubernur, 
                            serta arsip historis yang dapat diakses antar-instansi terkait.
                        </p>
                        <div class="landing-card-footer-meta">
                            <span>Format: PDF Warkah Resmi</span>
                            <span>Akses: Terverifikasi</span>
                        </div>
                    </div>

                    <!-- Side Card 2: Registri Demografi & Riwayat Penempatan -->
                    <div class="landing-showcase-card reveal reveal-up reveal-delay-3">
                        <div class="landing-showcase-icon-box" style="background: #e8f0fa; color: #0e2a47;">
                            <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <h4>Registri Kependudukan & Demografi</h4>
                        <p>
                            Pencatatan data kepala keluarga (KK) penempatan, pola usaha transmigrasi (tanaman pangan/perkebunan), 
                            asal daerah penempatan, hingga proses transformasi UPT menjadi desa definitif mandiri.
                        </p>
                        <div class="landing-card-footer-meta">
                            <span>Cakupan: 1953 – 2025</span>
                            <span>Integrasi: Dukcapil & BPS</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-container">
            <div class="landing-footer-grid reveal reveal-up">
                <!-- Identitas Kedinasan -->
                <div class="landing-footer-brand">
                    <div class="landing-footer-logo-row">
                        <div class="atlas-brand-mark" style="width: 36px; height: 38px; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">
                            <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 22px; height: 22px; color: #ffffff;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"></path>
                            </svg>
                        </div>
                        <div>
                            <strong>SIGAP-TRANS KALSEL</strong>
                            <small>Sistem Informasi Geospasial Administrasi &amp; Persebaran Transmigrasi</small>
                        </div>
                    </div>
                    <p class="landing-footer-desc">
                        Sistem resmi Pemerintah Provinsi Kalimantan Selatan untuk menjamin keterpaduan, 
                        akuntabilitas, dan keterbukaan informasi penataan kawasan transmigrasi sejak 1953 hingga 2025.
                    </p>
                    <div class="landing-footer-address">
                        <strong>Dinas Tenaga Kerja dan Transmigrasi Provinsi Kalimantan Selatan</strong>
                        <span>Bidang Transmigrasi — Jl. Jenderal Ahmad Yani Km. 6, Banjarmasin / Banjarbaru, Kalimantan Selatan</span>
                    </div>
                </div>

                <!-- Tautan Pintas & Akses Petugas -->
                <div class="landing-footer-links-group">
                    <div class="landing-footer-col">
                        <h5>Akses Navigasi</h5>
                        <ul>
                            <li><a href="{{ route('home') }}">Beranda Utama</a></li>
                            <li><a href="{{ route('peta') }}">Peta Interaktif WebGIS</a></li>
                            <li><a href="#tentang">Tata Kelola Satu Peta</a></li>
                            <li><a href="#kapabilitas">Infrastruktur Data</a></li>
                        </ul>
                    </div>
                    <div class="landing-footer-col">
                        <h5>Ruang Kedinasan</h5>
                        <ul>
                            <li><a href="{{ route('peta') }}">Pencarian UPT</a></li>
                            <li><a href="#tentang">Warkah &amp; Kebijakan Satu Peta</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="landing-footer-bottom reveal reveal-up reveal-delay-2">
                <span>© {{ date('Y') }} Dinas Tenaga Kerja dan Transmigrasi Provinsi Kalimantan Selatan.</span>
                <span>Pemerintah Provinsi Kalimantan Selatan</span>
            </div>
        </div>
    </footer>

    <!-- Script Ringan: Header Scroll, Tabular Counter, Motion Switcher & Bidirectional Scroll Reveal -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Header scroll effect
            const header = document.getElementById('landing-header');
            if (header) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 20) {
                        header.classList.add('is-scrolled');
                    } else {
                        header.classList.remove('is-scrolled');
                    }
                }, { passive: true });
            }

            // 2. Bidirectional Scroll-Reveal Motion
            const reveals = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                        } else {
                            // Bi-directional: trigger out-animation when scrolling past
                            entry.target.classList.remove('is-revealed');
                        }
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '10px 0px -10px 0px'
                });

                reveals.forEach(el => revealObserver.observe(el));
            } else {
                reveals.forEach(el => el.classList.add('is-revealed'));
            }

            // 3. Counter Animation Function (Gentle ease-out)
            function animateCounter(el, target) {
                const duration = 1400; // ms
                const start = performance.now();
                const initial = 0;

                function update(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    // easeOutQuart
                    const ease = 1 - Math.pow(1 - progress, 4);
                    const current = Math.floor(initial + (target - initial) * ease);
                    el.textContent = current.toLocaleString('id-ID');

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        el.textContent = target.toLocaleString('id-ID');
                    }
                }

                requestAnimationFrame(update);
            }

            function triggerSlideCounters(slideEl) {
                if (!slideEl) return;
                const nums = slideEl.querySelectorAll('.landing-registry-num[data-target]');
                nums.forEach(el => {
                    const target = parseInt(el.getAttribute('data-target') || '0', 10);
                    if (target > 0) {
                        animateCounter(el, target);
                    }
                });
            }

            // Initial counter animation when registry panel first enters viewport
            const registryPanel = document.querySelector('.landing-registry-panel');
            let initialCountDone = false;
            if (registryPanel && 'IntersectionObserver' in window) {
                const panelObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !initialCountDone) {
                            initialCountDone = true;
                            const activeSlide = document.querySelector('.landing-registry-slide.active');
                            triggerSlideCounters(activeSlide);
                        }
                    });
                }, { threshold: 0.2 });

                panelObserver.observe(registryPanel);
            }

            // 4. Statistical Panel Carousel Motion (UPT <-> SKP)
            const switchBtns = document.querySelectorAll('.landing-switch-btn');
            const slides = document.querySelectorAll('.landing-registry-slide');
            const statusText = document.querySelector('.landing-registry-status-text');
            let currentSlide = 0;
            let slideInterval = null;

            const statusLabels = [
                'STATUS DATA TERVERIFIKASI TINGKAT KABUPATEN/KOTA',
                'STATUS DELINEASI KAWASAN SPASIAL SKP KALSEL'
            ];

            function goToSlide(index, manual = false) {
                if (index === currentSlide && !manual) return;

                slides.forEach((s, idx) => {
                    s.classList.toggle('active', idx === index);
                });

                switchBtns.forEach((btn, idx) => {
                    const isActive = (idx === index);
                    btn.classList.toggle('active', isActive);
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                if (statusText && statusLabels[index]) {
                    statusText.textContent = statusLabels[index];
                }

                currentSlide = index;
                triggerSlideCounters(slides[index]);
            }

            function startSlideTimer() {
                stopSlideTimer();
                slideInterval = setInterval(() => {
                    const next = (currentSlide + 1) % slides.length;
                    goToSlide(next);
                }, 8500);
            }

            function stopSlideTimer() {
                if (slideInterval) {
                    clearInterval(slideInterval);
                    slideInterval = null;
                }
            }

            switchBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetIdx = parseInt(btn.getAttribute('data-slide') || '0', 10);
                    goToSlide(targetIdx, true);
                    startSlideTimer(); // restart cycle
                });
            });

            if (registryPanel) {
                registryPanel.addEventListener('mouseenter', stopSlideTimer);
                registryPanel.addEventListener('mouseleave', startSlideTimer);
            }

            if (slides.length > 1) {
                startSlideTimer();
            }
        });
    </script>
</body>

</html>
