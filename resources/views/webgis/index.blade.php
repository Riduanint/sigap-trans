<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGAP-TRANS KALSEL - Portal Geospasial Pemukiman Transmigrasi Kalimantan Selatan (1953–2025)</title>
    <meta name="description" content="Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi Provinsi Kalimantan Selatan. Memetakan 124 UPT di 9 Kabupaten sejak masa Pra-Pelita (1953) s/d 2025.">

    <!-- Editorial utility type for a quieter, more legible map workspace -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="webgis-shell bg-[#EEE9DF] text-[#1B2632] font-sans antialiased overflow-hidden h-screen w-screen flex flex-col select-none">

    <!-- ========================================================================= -->
    <!-- 1. HEADER UTAMA KEDINASAN (ABYSSAL NAVY: #1B2632 & BURNING FLAME: #FFB162) -->
    <!-- ========================================================================= -->
    <header class="h-16 bg-[#1B2632] text-white border-b border-[#2C3B4D] px-4 lg:px-6 flex items-center justify-between z-30 shrink-0 shadow-ambient-md">
        <div class="flex items-center gap-3.5">
            <!-- Brand Logo Kedinasan -->
            <div class="w-10 h-10 rounded-xl bg-[#2C3B4D] border border-[#FFB162]/40 flex items-center justify-center text-[#FFB162] shadow-ambient-xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-extrabold text-white tracking-tight text-base lg:text-lg leading-none">
                        SIGAP-TRANS <span class="text-[#FFB162]">KALSEL</span>
                    </h1>
                    <span class="bg-[#2C3B4D] text-[#FFB162] border border-[#FFB162]/40 text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                        PUSAT DATA SPASIAL
                    </span>
                </div>
                <p class="text-[10px] text-[#EEE9DF]/70 hidden sm:block mt-0.5 font-medium tracking-wide">
                    Disnakertrans Prov. Kalsel • Rekam Jejak 124 UPT di 9 Kabupaten (1953–2025)
                </p>
            </div>
        </div>

        <!-- Indikator Metrik Cepat di Header (Selaras Panel Super Admin) -->
        <div class="hidden xl:flex items-center gap-7 text-xs text-[#EEE9DF]/80 border-x border-[#2C3B4D] px-6">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-[#FFB162] animate-pulse"></span>
                <span>Total Unit: <strong class="text-white text-sm font-black font-mono" id="stat-total-upt">124</strong> <span class="text-[#EEE9DF]/60 text-[11px]">UPT</span></span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>KK Penempatan: <strong class="text-white text-sm font-black font-mono" id="stat-placement-kk">63.701</strong> <span class="text-[#EEE9DF]/60 text-[11px]">KK</span></span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                <span>Serah Terima: <strong class="text-[#FFB162] text-sm font-black font-mono" id="stat-handover-kk">64.942</strong> <span class="text-[#EEE9DF]/60 text-[11px]">KK</span></span>
            </div>
        </div>

        <!-- Tombol Aksi Autentikasi Kedinasan -->
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-[#FFB162] hover:bg-[#ffa74a] text-[#1B2632] font-black text-xs px-4 py-2 rounded-xl transition shadow-ambient-xs flex items-center gap-1.5 active:translate-y-0.5">
                    <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Ruang Kendali Super Admin</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-[#2C3B4D] hover:bg-[#384a60] text-[#EEE9DF] border border-[#C9C1B1]/30 font-bold text-xs px-4 py-2 rounded-xl transition shadow-ambient-xs flex items-center gap-2 active:translate-y-0.5">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Masuk Kedinasan</span>
                    <span class="text-[#FFB162]">→</span>
                </a>
            @endauth
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. MAP WRAPPER & FLOATING CONTROLS                                       -->
    <!-- ========================================================================= -->
    <div class="relative flex-1 w-full h-full overflow-hidden bg-[#EEE9DF]">

        <!-- PETA LEAFLET CONTAINER -->
        <div id="sigap-map" class="w-full h-full z-10 bg-[#EEE9DF]"></div>

        <!-- LOADING SPINNER MENGAMBANG -->
        <div id="map-loader" class="absolute inset-0 bg-[#1B2632]/75 backdrop-blur-xs z-50 flex flex-col items-center justify-center text-white transition-opacity duration-300">
            <div class="relative w-14 h-14">
                <div class="absolute inset-0 rounded-full border-4 border-[#2C3B4D] border-t-[#FFB162] animate-spin"></div>
                <div class="absolute inset-2.5 rounded-full border-4 border-[#C9C1B1]/20 border-b-[#FFB162] animate-spin" style="animation-direction: reverse;"></div>
            </div>
            <p class="mt-4 text-sm font-extrabold text-white tracking-wide">Memuat Spasial 124 UPT Kalsel...</p>
            <p class="text-xs text-[#EEE9DF]/70 mt-0.5 font-medium">Mengambil geometri titik koordinat PostGIS EPSG:4326</p>
        </div>

        <!-- ===================================================================== -->
        <!-- 3. PANEL FILTER SPASIAL MENGAMBANG (KIRI ATAS)                        -->
        <!-- ===================================================================== -->
        <div class="absolute top-4 left-4 z-20 w-84 max-w-[calc(100vw-2rem)] transition-all duration-300">
            <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-[#C9C1B1] shadow-ambient-md text-[#1B2632] overflow-hidden">
                <!-- Header Filter Card -->
                <div class="bg-[#1B2632] text-white px-4 py-3 flex items-center justify-between border-b border-[#2C3B4D]">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <h2 class="text-xs font-black uppercase tracking-wider text-white">Filter Spasial UPT</h2>
                    </div>
                    <span id="filter-count-badge" class="bg-[#2C3B4D] text-[#FFB162] border border-[#FFB162]/40 font-mono font-black text-[11px] px-2.5 py-0.5 rounded-lg">
                        124 / 124
                    </span>
                </div>

                <div class="p-4 space-y-3 text-xs bg-white">
                    <!-- Pencarian Cepat -->
                    <div>
                        <label for="filter-search" class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D]/80 block mb-1">Cari UPT / Desa</label>
                        <div class="relative">
                            <input type="text" id="filter-search" placeholder="Ketik nama desa / UPT..." 
                                class="w-full bg-[#EEE9DF]/30 border border-[#C9C1B1] rounded-xl px-3 py-2 pl-8 text-xs text-[#1B2632] font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFB162] focus:border-[#FFB162] placeholder:text-[#2C3B4D]/40 transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-[#2C3B4D]/60 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Filter Kabupaten -->
                    <div>
                        <label for="filter-regency" class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D]/80 block mb-1">
                            Kabupaten ({{ isset($visibleRegencies) ? $visibleRegencies->count() : 9 }} Wilayah Aktif)
                        </label>
                        <select id="filter-regency" class="w-full bg-[#EEE9DF]/30 border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFB162] focus:border-[#FFB162] transition shadow-2xs">
                            <option value="all">📍 Semua Wilayah Aktif ({{ isset($visibleRegencies) ? $visibleRegencies->sum('upt_locations_count') : 124 }} UPT)</option>
                            @if(isset($visibleRegencies))
                                @foreach($visibleRegencies as $reg)
                                    <option value="{{ $reg->id }}" {{ request('regency') == $reg->id ? 'selected' : '' }}>
                                        Kabupaten {{ $reg->name }} ({{ $reg->upt_locations_count }} UPT)
                                    </option>
                                @endforeach
                            @else
                                <option value="1">Kabupaten Tapin (9 UPT)</option>
                                <option value="2">Kabupaten Hulu Sungai Utara (6 UPT)</option>
                                <option value="3">Kabupaten Balangan (2 UPT)</option>
                                <option value="4">Kabupaten Tabalong (11 UPT)</option>
                                <option value="5">Kabupaten Tanah Laut (23 UPT)</option>
                                <option value="6">Kabupaten Barito Kuala (20 UPT)</option>
                                <option value="7">Kabupaten Kota Baru (39 UPT)</option>
                                <option value="8">Kabupaten Tanah Bumbu (3 UPT)</option>
                                <option value="9">Kabupaten Banjar (11 UPT)</option>
                            @endif
                        </select>
                    </div>

                    <!-- Filter Status Lahan (Tri-Tier Standard) -->
                    <div>
                        <label for="filter-status" class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D]/80 block mb-1">Status Kondisi Lahan</label>
                        <select id="filter-status" class="w-full bg-[#EEE9DF]/30 border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFB162] focus:border-[#FFB162] transition shadow-2xs">
                            <option value="all">Semua Status Kondisi Lahan</option>
                            <option value="clean">🟢 Clean & Clear (SHM Tuntas)</option>
                            <option value="warning">🟡 Waspada / Monitoring</option>
                            <option value="critical">🔴 Kritis / Prioritas Mediasi</option>
                        </select>
                    </div>

                    <!-- Filter Dekade & Pola Usaha (Grid 2 Kolom) -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="filter-decade" class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D]/80 block mb-1">Dekade Masuk</label>
                            <select id="filter-decade" class="w-full bg-[#EEE9DF]/30 border border-[#C9C1B1] rounded-xl px-2.5 py-2 text-xs text-[#1B2632] font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFB162] focus:border-[#FFB162] transition shadow-2xs">
                                <option value="all">Semua Era</option>
                                <option value="1950">1950-an</option>
                                <option value="1960">1960-an</option>
                                <option value="1970">1970-an</option>
                                <option value="1980">1980-an</option>
                                <option value="1990">1990-an</option>
                                <option value="2000">2000-an</option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-pattern" class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D]/80 block mb-1">Pola Usaha</label>
                            <select id="filter-pattern" class="w-full bg-[#EEE9DF]/30 border border-[#C9C1B1] rounded-xl px-2.5 py-2 text-xs text-[#1B2632] font-semibold focus:outline-none focus:ring-2 focus:ring-[#FFB162] focus:border-[#FFB162] transition shadow-2xs">
                                <option value="all">Semua Pola</option>
                                <option value="TPLK">TPLK</option>
                                <option value="TPLB">TPLB</option>
                                <option value="PIRSUS">PIRSUS</option>
                                <option value="HTI">HTI</option>
                                <option value="P4HDR">P4HDR</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Reset Filter -->
                    <div class="pt-1.5">
                        <button id="btn-reset-filters" class="w-full bg-[#EEE9DF] hover:bg-[#C9C1B1]/60 text-[#1B2632] font-bold py-2 px-3 rounded-xl border border-[#C9C1B1] transition flex items-center justify-center gap-1.5 shadow-2xs text-xs active:translate-y-0.5">
                            <svg class="w-3.5 h-3.5 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Reset Semua Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 4. KONTROL PETA MENGAMBANG (KANAN ATAS)                                -->
        <!-- ===================================================================== -->
        <div class="absolute top-4 right-4 z-20 flex flex-col gap-2 items-end">
            <!-- Basemap Switcher Pill (Matching Super Admin Palette) -->
            <div class="bg-white/95 backdrop-blur-md p-1.5 rounded-2xl border border-[#C9C1B1] shadow-ambient-md flex items-center gap-1">
                <button data-basemap="street" data-active="true" aria-pressed="true" class="btn-basemap bg-[#1B2632] text-white font-bold text-xs px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-ambient-xs">
                    <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    <span>Peta Jalan</span>
                </button>
                <button data-basemap="satellite" data-active="false" aria-pressed="false" class="btn-basemap bg-white hover:bg-[#EEE9DF]/70 text-[#2C3B4D] font-bold text-xs px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 border border-[#C9C1B1]/40">
                    <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Citra Satelit</span>
                </button>
            </div>

            <!-- Tombol Lapisan & Navigasi Cepat (Selaras Super Admin) -->
            <div class="bg-white/95 backdrop-blur-md p-1.5 rounded-2xl border border-[#C9C1B1] shadow-ambient-md flex items-center gap-1.5">
                <button id="btn-toggle-regency-boundary" title="Tampilkan / Sembunyikan Poligon Batas Wilayah 9 Kabupaten"
                    class="bg-white hover:bg-[#EEE9DF]/80 text-[#1B2632] font-bold text-xs px-3 py-1.5 rounded-xl border border-[#C9C1B1] transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    <span>Batas Kabupaten</span>
                </button>
                <button id="btn-toggle-polygon" title="Tampilkan / Sembunyikan Poligon Delineasi Kawasan UPT"
                    class="bg-white hover:bg-[#EEE9DF]/80 text-[#1B2632] font-bold text-xs px-3 py-1.5 rounded-xl border border-[#C9C1B1] transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#1B4D3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="hidden sm:inline">Delineasi UPT</span>
                </button>
                <button id="btn-reset-view" title="Kembalikan Tampilan Peta Se-Kalsel"
                    class="bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-ambient-xs active:translate-y-0.5">
                    <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Pusatkan Kalsel</span>
                </button>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 5. LEGENDA TRAFFIC LIGHT STATUS (KIRI BAWAH)                          -->
        <!-- ===================================================================== -->
        <div class="absolute bottom-6 left-4 z-20 hidden md:block">
            <div class="bg-white/95 backdrop-blur-md rounded-2xl p-3.5 border border-[#C9C1B1] shadow-ambient-md max-w-xs text-xs space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#2C3B4D] block border-b border-[#C9C1B1]/40 pb-1.5 flex items-center justify-between">
                    <span>Indikator Status Kondisi Lahan</span>
                    <span class="text-[9px] text-[#2C3B4D]/60 font-mono">124 UPT</span>
                </span>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        <span class="font-bold text-slate-700">Clean & Clear</span>
                    </div>
                    <span class="font-mono font-black text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" id="stat-clean">111</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                        <span class="font-bold text-slate-700">Waspada / Monitoring</span>
                    </div>
                    <span class="font-mono font-black text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200" id="stat-warning">7</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-xs animate-pulse"></span>
                        <span class="font-bold text-slate-700">Kritis / Prioritas Mediasi</span>
                    </div>
                    <span class="font-mono font-black text-rose-800 bg-rose-50 px-2 py-0.5 rounded border border-rose-200" id="stat-critical">6</span>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 6. SLIDE-IN DETAIL DRAWER KARTU RIWAYAT UPT (KANAN)                   -->
        <!-- ===================================================================== -->
        <div id="upt-detail-drawer" 
            class="fixed inset-y-0 right-0 z-40 w-full sm:w-[480px] bg-[#EEE9DF] shadow-2xl border-l border-[#C9C1B1] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            
            <!-- Tombol Tutup Drawer -->
            <button id="btn-close-detail" 
                class="absolute top-4 right-4 z-50 w-9 h-9 rounded-xl bg-[#2C3B4D] hover:bg-[#A35139] text-white flex items-center justify-center shadow-lg transition border border-[#C9C1B1]/40"
                title="Tutup Panel Detail">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Wadah Konten Dinamis -->
            <div id="upt-detail-content" class="flex-1 overflow-y-auto custom-scrollbar">
                <!-- Konten dirender oleh JS webgis-map.js -->
            </div>
        </div>

    </div>

</body>
</html>
