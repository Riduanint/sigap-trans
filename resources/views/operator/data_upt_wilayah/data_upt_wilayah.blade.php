@extends('layouts.admin')

@section('title', 'Data UPT Seluruh Wilayah - SIGAP-TRANS Kalsel')
@section('header_title', 'Inventarisasi UPT Lintas Wilayah Kabupaten')
@section('header_subtitle', 'Basis data 124 Unit Pemukiman Transmigrasi di 9 Kabupaten se-Kalimantan Selatan')

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER UTAMA WILAYAH OPERATOR (MP072 ARSITEKTURAL) -->
    <div class="rounded-2xl bg-[#1B2632] p-6 text-[#EEE9DF] shadow-ambient border border-[#2C3B4D] relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-[#FFB162]/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FFB162]/20 border border-[#FFB162]/50 flex items-center justify-center text-[#FFB162] shrink-0 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#1B2632] bg-[#FFB162] px-2.5 py-0.5 rounded-full shadow-2xs">
                            Wewenang Lintas Wilayah
                        </span>
                        <span class="text-xs text-[#C9C1B1]">&bull;</span>
                        <span class="text-xs text-[#C9C1B1]">Cakupan: <strong class="text-[#EEE9DF]">9 Kabupaten Se-Kalsel</strong></span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-[#EEE9DF] mt-1">
                        @if($currentRegency)
                            UPT Binaan Kabupaten {{ $currentRegency->name }}
                        @else
                            Seluruh 124 UPT Kalimantan Selatan
                        @endif
                    </h2>
                    <p class="text-xs text-[#C9C1B1] mt-0.5">
                        Operator dapat meninjau data lapangan, mengelola registri nominal KK, dan mengajukan draf usulan pemutakhiran tanpa terbatasi satu wilayah.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('operator.requests.create') }}" 
                   class="bg-[#A35139] hover:bg-[#883d28] text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 border border-[#A35139]">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Ajukan Draf Usulan Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. KPI METRIC CARDS (6 KARTU ARSITEKTURAL MP072) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Card 1: Total UPT -->
        <x-stat-card
            variant="abyssal"
            label="TOTAL UPT"
            :value="$stats['total_upt']"
            unit="Lokasi"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Unit Terdaftar
            </x-slot:subtext>
        </x-stat-card>

        <!-- Card 2: Status Clean -->
        <x-stat-card
            variant="clean"
            label="LAHAN CLEAN"
            :value="$stats['clean_count']"
            unit="Clean"
        >
            <x-slot:customIcon>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </x-slot:customIcon>
            <x-slot:subtext>
                Bebas Sengketa
            </x-slot:subtext>
        </x-stat-card>

        <!-- Card 3: Status Pantau -->
        <x-stat-card
            variant="flame"
            label="LAHAN PANTAU"
            :value="$stats['warning_count']"
            unit="Pantau"
        >
            <x-slot:customIcon>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </x-slot:customIcon>
            <x-slot:subtext>
                Proses Selesai
            </x-slot:subtext>
        </x-stat-card>

        <!-- Card 4: Status Kritis -->
        <x-stat-card
            variant="truffle"
            label="LAHAN KRITIS"
            :value="$stats['critical_count']"
            unit="Kritis"
        >
            <x-slot:customIcon>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
            </x-slot:customIcon>
            <x-slot:subtext>
                Perlu Mediasi
            </x-slot:subtext>
        </x-stat-card>

        <!-- Card 5: KK Penempatan -->
        <x-stat-card
            variant="slate"
            label="PENEMPATAN"
            :value="number_format($stats['total_placement_kk'], 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Kepala Keluarga
            </x-slot:subtext>
        </x-stat-card>

        <!-- Card 6: KK Serah Terima -->
        <x-stat-card
            variant="oatmeal"
            label="SERAH TERIMA"
            :value="number_format($stats['total_handover_kk'], 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Desa Binaan
            </x-slot:subtext>
        </x-stat-card>
    </div>

    <!-- 3. TOOLBAR FILTER & PENCARIAN -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs">
        <form action="{{ route('operator.upt.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Filter Kabupaten -->
            <div class="sm:col-span-1 lg:col-span-4">
                <label for="regency_id" class="block text-[11px] font-bold text-[#2C3B4D] uppercase mb-1">
                    Wilayah Kabupaten:
                </label>
                <div class="relative">
                    <select name="regency_id" id="regency_id" onchange="this.form.submit()"
                            class="w-full text-xs font-bold rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $selectedRegencyId === 'all' ? 'selected' : '' }}>
                            📍 Semua 9 Kabupaten (124 UPT)
                        </option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ (string)$selectedRegencyId === (string)$reg->id ? 'selected' : '' }}>
                                Kabupaten {{ $reg->name }} ({{ $reg->upt_locations_count }} UPT)
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Status Lahan -->
            <div class="sm:col-span-1 lg:col-span-3">
                <label for="status" class="block text-[11px] font-bold text-[#2C3B4D] uppercase mb-1">
                    Status Legalitas Lahan:
                </label>
                <div class="relative">
                    <select name="status" id="status" onchange="this.form.submit()"
                            class="w-full text-xs font-bold rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status Lahan</option>
                        <option value="clean" {{ $selectedStatus === 'clean' ? 'selected' : '' }}>🟢 Clean (Bebas Masalah)</option>
                        <option value="warning" {{ $selectedStatus === 'warning' ? 'selected' : '' }}>🟡 Pantau (Potensi Masalah)</option>
                        <option value="critical" {{ $selectedStatus === 'critical' ? 'selected' : '' }}>🔴 Kritis (Prioritas Mediasi)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Pencarian Kata Kunci -->
            <div class="sm:col-span-2 lg:col-span-3">
                <label for="search" class="block text-[11px] font-bold text-[#2C3B4D] uppercase mb-1">
                    Pencarian Kata Kunci:
                </label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Cari UPT, desa, pola..."
                           class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 pl-8 pr-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                    <svg class="w-4 h-4 text-[#C9C1B1] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Tombol Aksi Filter & Reset -->
            <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" 
                        class="flex-1 bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs py-2 px-3 rounded-xl transition shadow-ambient-xs flex items-center justify-center gap-1.5 h-[38px] cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['regency_id', 'status', 'search']) && (request('regency_id') !== 'all' || request('status') !== 'all' || request('search') !== null))
                    <a href="{{ route('operator.upt.index') }}" 
                       class="px-3 py-2 bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] font-bold text-xs rounded-xl transition border border-[#C9C1B1] flex items-center justify-center h-[38px] shadow-2xs" title="Reset Filter">
                        <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. TABEL DATA INVENTARISASI UPT -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-4 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs text-[#1B2632] uppercase tracking-wider">Daftar Lokasi UPT</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#1B2632] text-[#EEE9DF] border border-[#2C3B4D]">
                    Menampilkan {{ $uptLocations->firstItem() ?? 0 }} - {{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} UPT
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[#2C3B4D]/75 font-medium">Baris:</span>
                <select onchange="window.location.href = '{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value"
                        class="text-xs font-bold rounded-lg border border-[#C9C1B1] py-1 pl-2.5 pr-6 bg-white text-[#1B2632] focus:ring-1 focus:ring-[#FFB162]">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[11px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="px-3.5 py-3.5 text-center w-12 font-semibold">No</th>
                        <th class="px-4 py-3.5 font-semibold">Nama UPT Historis</th>
                        <th class="px-4 py-3.5 font-semibold">Kabupaten & Desa</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Pola Usaha</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Penempatan Awal</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Serah Terima Pemda</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Status Lahan</th>
                        <th class="px-4 py-3.5 text-center font-semibold">SHM (BPN)</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Registri KK</th>
                        <th class="px-4 py-3.5 text-center font-semibold w-36">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($uptLocations as $upt)
                        <tr class="hover:bg-[#EEE9DF]/40 transition" id="upt-row-{{ $upt->id }}">
                            <!-- No UPT -->
                            <td class="px-3.5 py-3 text-center font-bold text-[#2C3B4D]/60 font-mono">
                                {{ $upt->upt_number }}
                            </td>

                            <!-- Nama UPT -->
                            <td class="px-4 py-3 font-extrabold text-[#1B2632]">
                                <div class="text-xs font-black">{{ $upt->upt_name }}</div>
                                <div class="text-[10px] text-[#2C3B4D]/60 font-mono mt-0.5">Kode: UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>

                            <!-- Wilayah Kabupaten & Desa Definitif -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25">
                                    Kab. {{ $upt->regency->name ?? '-' }}
                                </span>
                                <div class="text-xs font-semibold text-[#1B2632] mt-1">
                                    {{ $upt->current_village_name ?? 'Belum terdefinisi' }}
                                </div>
                            </td>

                            <!-- Pola Usaha -->
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-[#EEE9DF] text-[#1B2632] font-bold text-[10px] border border-[#C9C1B1]/50">
                                    {{ $upt->business_pattern ?? 'Non Pola' }}
                                </span>
                            </td>

                            <!-- Penempatan Awal -->
                            <td class="px-4 py-3 text-center tabular-nums">
                                <span class="font-extrabold text-[#1B2632]">{{ number_format($upt->placement_kk, 0, ',', '.') }} KK</span>
                                <div class="text-[10px] text-[#2C3B4D]/60">Th. {{ $upt->placement_year ?? '-' }} | {{ number_format($upt->placement_population, 0, ',', '.') }} Jiwa</div>
                            </td>

                            <!-- Serah Terima -->
                            <td class="px-4 py-3 text-center tabular-nums">
                                <span class="font-extrabold text-[#1B2632]">{{ number_format($upt->handover_kk, 0, ',', '.') }} KK</span>
                                <div class="text-[10px] text-[#2C3B4D]/60">Th. {{ $upt->handover_year ?? '-' }} | {{ number_format($upt->handover_population, 0, ',', '.') }} Jiwa</div>
                            </td>

                            <!-- Status Lahan -->
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :status="$upt->issue_status" mode="short" size="xs" />
                            </td>

                            <!-- Status SHM -->
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded bg-[#2C3B4D]/10 text-[#2C3B4D] font-bold text-[10px] border border-[#2C3B4D]/25">
                                    {{ $upt->shm_status ?? '100% SHM' }}
                                </span>
                            </td>

                            <!-- Registri KK -->
                            <td class="px-4 py-3 text-center">
                                @if($upt->family_cards_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FFB162]/25 text-[#1B2632] border border-[#FFB162]/50">
                                        {{ $upt->family_cards_count }} KK
                                    </span>
                                @else
                                    <span class="text-[10px] text-[#2C3B4D]/60 font-medium">0 KK</span>
                                @endif
                            </td>

                            <!-- Tombol Aksi Cepat -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Ajukan Revisi / Usulan -->
                                    <a href="{{ route('operator.requests.create', ['upt_id' => $upt->id]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-[10px] transition shadow-2xs border border-[#1B2632]"
                                       title="Ajukan Pemutakhiran / Revisi Data UPT">
                                        <svg class="w-3 h-3 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Usulan</span>
                                    </a>

                                    <!-- Tombol Registri Warga KK -->
                                    <button type="button" 
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }} (Kab. {{ addslashes($upt->regency->name ?? '') }})', 'placement')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#FFB162] hover:bg-[#ffa347] text-[#1B2632] font-black text-[10px] transition border border-[#FFB162]/40 cursor-pointer shadow-2xs"
                                            title="Buka Registri Nominal KK Transmigran">
                                        <svg class="w-3 h-3 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span>Warga</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-[#2C3B4D]/60 font-medium">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <svg class="w-8 h-8 text-[#C9C1B1] mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="font-bold text-[#1B2632]">Tidak ada UPT yang cocok</div>
                                    <p class="text-xs text-[#2C3B4D]/70">Silakan sesuaikan filter wilayah kabupaten atau kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (Identik Super Admin MP072) -->
        <div class="p-4 border-t border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-[#2C3B4D]/80 font-medium">
                Showing <strong class="text-[#1B2632]">{{ $uptLocations->firstItem() ?? 0 }}</strong> to <strong
                    class="text-[#1B2632]">{{ $uptLocations->lastItem() ?? 0 }}</strong> of <strong
                    class="text-[#1B2632]">{{ $uptLocations->total() }}</strong> results
            </div>

            <div
                class="inline-flex items-center bg-white text-[#1B2632] px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-xs border border-[#C9C1B1] self-center sm:self-auto">
                {{ $uptLocations->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>

<!-- Drawer Registri Warga KK (Opsi C Hibrida) -->
@include('admin.registri_warga.registri_warga')

@endsection
