<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGAP-TRANS KALSEL - Portal Geospasial Pemukiman Transmigrasi Kalimantan Selatan (1953–2025)</title>
    <meta name="description" content="Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi Provinsi Kalimantan Selatan. Rekam jejak kawasan permukiman transmigrasi sejak 1953 s/d 2025.">

    <!-- Tipografi Atlas: Barlow Semi Condensed (judul) + Source Sans 3 (isi) + JetBrains Mono (telemetri) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/css/atlas.css', 'resources/js/app.js'])
</head>
<body class="webgis-shell bg-[#F3F6F8] text-[#0E2A47] font-sans antialiased overflow-hidden h-screen w-screen flex flex-col select-none">

    <!-- ========================================================================= -->
    <!-- 1. HEADER KEDINASAN RESMI                                                -->
    <!-- ========================================================================= -->
    <header class="h-16 px-4 lg:px-6 flex items-center justify-between z-30 shrink-0 bg-white border-b border-[#D4DEE7] shadow-sm">
        <div class="flex items-center gap-3.5">
            <!-- Brand Mark & Nama Sistem -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity" title="Beranda SIGAP-TRANS">
                <div class="atlas-brand-mark" style="width: 36px; height: 38px;">
                    <svg class="atlas-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 22px; height: 22px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-bold tracking-tight text-[#0E2A47] text-base lg:text-lg leading-none font-['Barlow_Semi_Condensed']">
                            SIGAP-TRANS <span class="text-[#2457A7]">KALSEL</span>
                        </h1>
                        <span class="inline-flex items-center gap-1.5 bg-[#E8F0FA] text-[#0E2A47] border border-[#CBD8E6] text-[10px] font-bold px-2 py-0.5 rounded tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#107C41]"></span>
                            WEBGIS AKTIF
                        </span>
                    </div>
                    <p class="text-[10.5px] text-[#5A6E7D] hidden md:block mt-0.5 font-medium">
                        Disnakertrans Prov. Kalimantan Selatan • Atlas Geospasial Kawasan Transmigrasi (1953–2025)
                    </p>
                </div>
            </a>
        </div>

        <!-- Indikator Metrik Cepat Dinamis (Desktop) -->
        <div class="hidden xl:flex items-center gap-6 text-xs text-[#5A6E7D] border-x border-[#D4DEE7] px-6">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#2457A7]"></span>
                <span>Unit Terfilter: <strong class="text-[#0E2A47] text-sm font-bold tabular-nums" id="stat-total-upt">{{ isset($visibleRegencies) ? $visibleRegencies->sum('upt_locations_count') : 0 }}</strong> <span class="text-[#5A6E7D] text-[11px]">UPT</span></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#107C41]"></span>
                <span>KK Penempatan: <strong class="text-[#0E2A47] text-sm font-bold tabular-nums" id="stat-placement-kk">-</strong> <span class="text-[#5A6E7D] text-[11px]">KK</span></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#D97706]"></span>
                <span>Serah Terima: <strong class="text-[#2457A7] text-sm font-bold tabular-nums" id="stat-handover-kk">-</strong> <span class="text-[#5A6E7D] text-[11px]">KK</span></span>
            </div>
        </div>

        <!-- Aksi Kanan: Toggle Island & Menu Akun -->
        <div class="flex items-center gap-2.5">
            <!-- Tombol Sembunyikan / Tampilkan Panel Vertikal (Hanya Ikon Mata) -->
            <button id="btn-toggle-navigation"
                class="w-9 h-9 bg-white hover:bg-[#F3F6F8] text-[#0E2A47] border border-[#CBD8E6] rounded-md shadow-xs transition flex items-center justify-center p-0 cursor-pointer"
                title="Sembunyikan atau tampilkan panel kontrol filter"
                aria-label="Panel Kontrol"
                aria-expanded="true">
                <!-- Ikon Mata Terbuka -->
                <svg id="icon-nav-eye-open" class="w-4 h-4 text-[#2457A7] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Ikon Mata Tertutup -->
                <svg id="icon-nav-eye-closed" class="w-4 h-4 text-[#5A6E7D] shrink-0 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            </button>

            @auth
                @php
                    $u = auth()->user();
                    $initial = mb_strtoupper(mb_substr($u->name ?? 'A', 0, 1));
                    $dashboardRoute = $u->isOperator() ? 'operator.dashboard' : 'admin.dashboard';
                    $docsRoute = $u->isOperator() ? 'operator.documents.index' : 'admin.documents.index';
                    $roleLabel = match($u->role) {
                        'super_admin' => 'Super Admin · Provinsi',
                        'operator_kabupaten' => 'Operator · ' . ($u->regency?->name ?? 'Wilayah'),
                        'eksekutif' => 'Eksekutif',
                        default => 'Aparatur Dinas'
                    };
                @endphp
                <!-- Menu Akun (Hanya Tampil Jika Login) -->
                <details class="relative" @click.outside="$el.removeAttribute('open')" @keydown.escape.stop="$el.removeAttribute('open')">
                    <summary class="cursor-pointer list-none p-0 flex items-center gap-2 select-none" title="Menu Akun: {{ $u->name }}">
                        <span class="atlas-avatar text-xs font-bold text-[#0E2A47] hover:ring-2 hover:ring-[#2457A7] transition-all shadow-xs" style="width: 34px; height: 34px;">
                            {{ $initial }}
                        </span>
                        <div class="hidden lg:block text-left text-xs leading-tight">
                            <strong class="block text-[#0E2A47] font-semibold max-w-[125px] truncate">{{ $u->name }}</strong>
                            <small class="text-[10px] text-[#5A6E7D] block">{{ $roleLabel }}</small>
                        </div>
                    </summary>
                    <div class="webgis-dropdown-menu absolute right-0 top-[calc(100%+8px)] bg-white border border-[#CBD8E6] rounded-lg shadow-2xl py-1.5 min-w-[240px] z-50 text-xs text-[#0E2A47]" style="background-color: #ffffff !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
                        <div class="px-3.5 py-2.5 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-lg">
                            <strong class="block font-semibold text-[#0E2A47] truncate">{{ $u->name }}</strong>
                            <span class="text-[11px] text-[#5A6E7D] block truncate">{{ $u->email }}</span>
                        </div>
                        <div class="p-1">
                            <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-[#F3F6F8] text-[#0E2A47] font-medium transition">
                                <svg class="w-4 h-4 text-[#2457A7] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h7v7H3V3Zm11 0h7v7h-7V3ZM3 14h7v7H3v-7Zm11 0h7v7h-7v-7Z"></path></svg>
                                <span>Ruang Kerja {{ $u->isOperator() ? 'Operator' : 'Admin' }}</span>
                            </a>
                            <a href="{{ route($docsRoute) }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-[#F3F6F8] text-[#0E2A47] font-medium transition">
                                <svg class="w-4 h-4 text-[#107C41] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7V5a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"></path></svg>
                                <span>Repositori Arsip Dokumen</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-[#F3F6F8] text-[#0E2A47] font-medium transition">
                                <svg class="w-4 h-4 text-[#5A6E7D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7.87a4 4 0 0 1 0 7.75"></path></svg>
                                <span>Profil &amp; Kata Sandi</span>
                            </a>
                        </div>
                        <div class="border-t border-[#E2E8F0] my-1"></div>
                        <div class="p-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ route('peta') }}">
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-rose-600 hover:bg-rose-50 font-medium transition text-left cursor-pointer">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5M21 12H9"></path></svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            @endauth
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. MAP WRAPPER & UNIFIED VERTICAL FLOATING ISLAND                        -->
    <!-- ========================================================================= -->
    <div class="relative flex-1 w-full h-full overflow-hidden bg-[#E7EEF3]">

        <!-- PETA LEAFLET CONTAINER (FULL SCREEN CANVAS) -->
        <div id="sigap-map" class="w-full h-full z-10"></div>

        <!-- LOADING OVERLAY -->
        <div id="map-loader" class="absolute inset-0 bg-[#F8FAFC]/90 z-50 flex flex-col items-center justify-center text-[#0E2A47] transition-opacity duration-300">
            <div class="w-10 h-10 rounded-full border-[3px] border-[#CBD8E6] border-t-[#2457A7] animate-spin"></div>
            <p class="mt-4 text-sm font-bold font-['Barlow_Semi_Condensed']">Memuat Data Spasial Transmigrasi Kalsel…</p>
            <p class="text-xs text-[#5A6E7D] mt-0.5">Mengambil geometri titik koordinat PostGIS EPSG:4326</p>
        </div>

        <!-- ===================================================================== -->
        <!-- 3. UNIFIED VERTICAL FLOATING ISLAND (SATU KOLOM VERTIKAL TERPADU)     -->
        <!-- ===================================================================== -->
        <div id="unified-gis-island" class="absolute top-3 sm:top-4 left-3 sm:left-4 z-30 w-80 sm:w-84 max-w-[calc(100vw-2rem)] max-h-[calc(100%-3.25rem)] flex flex-col min-h-0 transition-all duration-300 ease-in-out">
            <div class="bg-white/95 backdrop-blur-md rounded-lg border border-[#CBD8E6] shadow-lg text-[#0E2A47] overflow-hidden flex flex-col max-h-full min-h-0">
                
                <!-- Strip Judul Pulau Vertikal -->
                <div class="bg-[#EDF2F6] px-3.5 py-2 flex items-center justify-between border-b border-[#D4DEE7] shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#2457A7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <h2 class="text-xs font-bold uppercase tracking-wider font-['Barlow_Semi_Condensed']">Kontrol &amp; Filter Spasial</h2>
                    </div>
                    <span id="filter-count-badge" class="bg-[#DAE7F6] text-[#194482] border border-[#BDCFE4] font-bold text-[11px] px-2 py-0.5 rounded tabular-nums">
                        {{ isset($visibleRegencies) ? $visibleRegencies->sum('upt_locations_count') : 0 }} UPT
                    </span>
                </div>

                <!-- Konten Scrollable di Dalam Pulau Vertikal -->
                <div class="p-3.5 pb-4 space-y-2.5 overflow-y-auto custom-scrollbar text-xs flex-1 min-h-0">
                    
                    <!-- 1. Pencarian Cepat Nama UPT / Desa -->
                    <div>
                        <label for="filter-search" class="text-[10px] font-bold uppercase tracking-wider text-[#5A6E7D] block mb-1">
                            Cari UPT / Desa Definitif
                        </label>
                        <div class="relative">
                            <input type="text" id="filter-search" placeholder="Ketik nama UPT atau desa..."
                                class="w-full bg-white border border-[#CBD8E6] rounded-md px-3 py-1.5 pl-8 text-xs text-[#0E2A47] font-medium focus:outline-none focus:ring-2 focus:ring-[#2457A7]/20 focus:border-[#2457A7] placeholder:text-[#5A6E7D]/50 transition">
                            <svg class="w-3.5 h-3.5 text-[#5A6E7D] absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- 2. Filter Wilayah Kabupaten -->
                    <div>
                        <label for="filter-regency" class="text-[10px] font-bold uppercase tracking-wider text-[#5A6E7D] block mb-1">
                            Kabupaten Tersebar ({{ isset($visibleRegencies) ? $visibleRegencies->count() : 0 }} Wilayah Aktif)
                        </label>
                        <select id="filter-regency" class="w-full bg-white border border-[#CBD8E6] rounded-md px-2.5 py-1.5 text-xs text-[#0E2A47] font-medium focus:outline-none focus:ring-2 focus:ring-[#2457A7]/20 focus:border-[#2457A7] transition">
                            <option value="all">Semua wilayah ({{ isset($visibleRegencies) ? $visibleRegencies->sum('upt_locations_count') : 0 }} UPT)</option>
                            @if(isset($visibleRegencies))
                                @foreach($visibleRegencies as $reg)
                                    <option value="{{ $reg->id }}" {{ request('regency') == $reg->id ? 'selected' : '' }}>
                                        Kab. {{ $reg->name }} ({{ $reg->upt_locations_count }} UPT)
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- 3. Status Kondisi Lahan: Terintegrasi dengan Chips 1-Klik (Pengganti Legenda Terpisah) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#5A6E7D] block">
                                Status Kondisi Lahan (1-Klik)
                            </label>
                        </div>
                        <input type="hidden" id="filter-status" value="all">
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" class="gis-status-chip is-active" data-status="all" title="Tampilkan semua status kondisi lahan">
                                <span class="chip-dot" style="background: #5A6E7D;"></span>
                                <span>Semua</span>
                                <strong id="stat-chip-all" class="tabular-nums ml-auto text-[11px]">-</strong>
                            </button>
                            <button type="button" class="gis-status-chip" data-status="clean" title="Sertifikasi tuntas tanpa sengketa">
                                <span class="chip-dot" style="background: #107C41;"></span>
                                <span>Clean</span>
                                <strong id="stat-chip-clean" class="tabular-nums ml-auto text-[11px] text-[#107C41]">-</strong>
                            </button>
                            <button type="button" class="gis-status-chip" data-status="warning" title="Waspada dalam proses monitoring">
                                <span class="chip-dot" style="background: #D97706;"></span>
                                <span>Waspada</span>
                                <strong id="stat-chip-warning" class="tabular-nums ml-auto text-[11px] text-[#D97706]">-</strong>
                            </button>
                            <button type="button" class="gis-status-chip" data-status="critical" title="Kritis prioritas mediasi">
                                <span class="chip-dot" style="background: #DC2626;"></span>
                                <span>Kritis</span>
                                <strong id="stat-chip-critical" class="tabular-nums ml-auto text-[11px] text-[#DC2626]">-</strong>
                            </button>
                        </div>
                    </div>

                    <!-- 4. Filter Lanjutan (Accordion Progressive Disclosure) -->
                    <details class="gis-accordion group rounded-md border border-[#D4DEE7] bg-[#F8FAFC]">
                        <summary class="flex items-center justify-between px-3 py-2 cursor-pointer font-bold text-[10.5px] text-[#5A6E7D] uppercase tracking-wider select-none hover:text-[#0E2A47] transition">
                            <span>Filter Lanjutan (Era &amp; Pola)</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="p-2.5 pt-1.5 grid grid-cols-2 gap-2 border-t border-[#D4DEE7]/70">
                            <div>
                                <label for="filter-decade" class="text-[9.5px] font-bold uppercase tracking-wider text-[#5A6E7D] block mb-1">Dekade Penempatan</label>
                                <select id="filter-decade" class="w-full bg-white border border-[#CBD8E6] rounded-md px-2 py-1 text-xs text-[#0E2A47] font-medium focus:outline-none focus:ring-1 focus:ring-[#2457A7] transition">
                                    <option value="all">Semua era</option>
                                    <option value="1950">1950-an</option>
                                    <option value="1960">1960-an</option>
                                    <option value="1970">1970-an</option>
                                    <option value="1980">1980-an</option>
                                    <option value="1990">1990-an</option>
                                    <option value="2000">2000-an</option>
                                </select>
                            </div>
                            <div>
                                <label for="filter-pattern" class="text-[9.5px] font-bold uppercase tracking-wider text-[#5A6E7D] block mb-1">Pola Usaha</label>
                                <select id="filter-pattern" class="w-full bg-white border border-[#CBD8E6] rounded-md px-2 py-1 text-xs text-[#0E2A47] font-medium focus:outline-none focus:ring-1 focus:ring-[#2457A7] transition">
                                    <option value="all">Semua pola</option>
                                    <option value="TPLK">TPLK</option>
                                    <option value="TPLB">TPLB</option>
                                    <option value="PIRSUS">PIRSUS</option>
                                    <option value="HTI">HTI</option>
                                    <option value="P4HDR">P4HDR</option>
                                </select>
                            </div>
                        </div>
                    </details>

                    <!-- Garis Pembatas Halus -->
                    <div class="border-t border-[#D4DEE7] my-1"></div>

                    <!-- 5. Kontrol Peta: Basemap Switcher, Lapisan & Aksi Kamera (Terpadu di Pulau Vertikal) -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#5A6E7D] block">
                            Kontrol Tampilan Peta
                        </label>
                        
                        <!-- Basemap Switcher Segmented -->
                        <div class="grid grid-cols-2 gap-1 p-1 bg-[#EDF2F6] rounded-md border border-[#D4DEE7]">
                            <button data-basemap="street" data-active="true" aria-pressed="true" class="btn-basemap bg-[#0E2A47] text-white font-semibold text-xs py-1.5 px-2 rounded transition flex items-center justify-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                <span>Peta Jalan</span>
                            </button>
                            <button data-basemap="satellite" data-active="false" aria-pressed="false" class="btn-basemap bg-transparent hover:bg-white text-[#5A6E7D] font-semibold text-xs py-1.5 px-2 rounded transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#5A6E7D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Citra Satelit</span>
                            </button>
                        </div>

                        <!-- Saklar Lapisan (Layer Toggles) -->
                        <div class="space-y-1 pt-0.5">
                            <button id="btn-toggle-regency-boundary" class="atlas-layer-btn is-active w-full justify-between" aria-pressed="true" title="Tampilkan / sembunyikan batas wilayah kabupaten">
                                <span class="flex items-center gap-2">
                                    <span class="layer-dot"></span>
                                    <span>Batas Wilayah Kabupaten</span>
                                </span>
                                <span class="text-[10px] text-emerald-600 font-bold">AKTIF</span>
                            </button>
                            <button id="btn-toggle-polygon" class="atlas-layer-btn is-active w-full justify-between" aria-pressed="true" title="Tampilkan / sembunyikan poligon delineasi desa UPT">
                                <span class="flex items-center gap-2">
                                    <span class="layer-dot"></span>
                                    <span>Poligon Batas Desa (UPT)</span>
                                </span>
                                <span class="text-[10px] text-emerald-600 font-bold">AKTIF</span>
                            </button>
                            <button id="btn-toggle-skp-boundary" class="atlas-layer-btn w-full justify-between" aria-pressed="false" title="Tampilkan / sembunyikan batas Kawasan SKP">
                                <span class="flex items-center gap-2">
                                    <span class="layer-dot"></span>
                                    <span>Kawasan SKP</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold">NONAKTIF</span>
                            </button>
                        </div>

                        <!-- Tombol Pusatkan Kalsel -->
                        <button id="btn-reset-view" class="w-full bg-[#F8FAFC] hover:bg-[#E8F0FA] text-[#0E2A47] font-semibold text-xs py-1.5 px-3 rounded-md border border-[#CBD8E6] transition flex items-center justify-center gap-2" title="Pusatkan posisi kamera ke seluruh Kalimantan Selatan">
                            <svg class="w-3.5 h-3.5 text-[#2457A7] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="7" stroke-width="1.8"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
                            </svg>
                            <span>Pusatkan Wilayah Kalsel</span>
                        </button>
                    </div>

                    <!-- 6. Tombol Reset Filter -->
                    <div class="pt-1">
                        <button id="btn-reset-filters" class="w-full bg-[#EDF2F6] hover:bg-[#E2E8F0] text-[#5A6E7D] hover:text-[#0E2A47] font-semibold py-1.5 px-3 rounded-md border border-[#CBD8E6] transition flex items-center justify-center gap-1.5 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Reset Semua Filter</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 4. SLIDE-IN DETAIL DRAWER UPT (KANAN)                                 -->
        <!-- ===================================================================== -->
        <div id="upt-detail-drawer"
            class="fixed inset-y-0 right-0 z-40 w-full sm:w-[480px] bg-[#F8FAFC] shadow-2xl border-l border-[#CBD8E6] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">

            <!-- Tombol Tutup Drawer -->
            <button id="btn-close-detail"
                class="absolute top-4 right-4 z-50 w-9 h-9 rounded-md bg-white hover:bg-[#E8F0FA] text-[#0E2A47] border border-[#CBD8E6] flex items-center justify-center transition shadow-xs"
                title="Tutup panel detail">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Wadah Konten Dinamis -->
            <div id="upt-detail-content" class="flex-1 overflow-y-auto custom-scrollbar">
                <!-- Konten dirender oleh JS webgis-map.js -->
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 5. ATLAS TELEMETRY BOTTOM STRIP (BAR KOORDINAT KARTOGRAFIS)           -->
        <!-- ===================================================================== -->
        <div id="gis-telemetry-bar" class="absolute bottom-0 inset-x-0 z-20 h-6 bg-white/95 backdrop-blur-md border-t border-[#D4DEE7] px-4 flex items-center justify-between text-[11px] text-[#5A6E7D]">
            <div class="flex items-center gap-3 font-mono">
                <span>KOORDINAT: <strong id="telemetry-coords" class="text-[#0E2A47] font-semibold">-3.12351, 114.59321</strong></span>
                <span class="text-[#CBD8E6]">•</span>
                <span>ZOOM: <strong id="telemetry-zoom" class="text-[#0E2A47] font-semibold">9</strong></span>
                <span class="text-[#CBD8E6]">•</span>
                <span>CRS: <strong class="text-[#0E2A47]">EPSG:4326</strong></span>
            </div>
            <div class="hidden md:flex items-center gap-2">
                <span>Satu Data Geospasial Transmigrasi Prov. Kalsel</span>
            </div>
            <div class="flex items-center gap-2">
                <span>Disnakertrans Prov. Kalsel</span>
            </div>
        </div>

    </div>

    <!-- Injeksi Konfigurasi Global untuk Peta & Autentikasi -->
    <script>
        window.sigapConfig = {
            isAuthenticated: @json(auth()->check()),
            userRole: @json(auth()->user()?->role),
            loginUrl: @json(route('login')),
            documentsRepoUrl: @json(auth()->check() ? (auth()->user()?->isOperator() ? route('operator.documents.index') : route('admin.documents.index')) : route('login')),
            downloadDocBaseUrl: @json(auth()->check() ? (auth()->user()?->isOperator() ? '/operator/documents' : '/admin/documents') : '/login')
        };
    </script>
</body>
</html>
