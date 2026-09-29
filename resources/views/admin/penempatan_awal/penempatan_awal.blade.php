@extends('layouts.admin')

@section('title', 'Kelola Penempatan Awal - SIGAP-TRANS Kalsel')

@section('content')
<div class="space-y-6">

    <!-- 1. HEADER HALAMAN -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs">
        <div>
            <div class="flex items-center gap-2.5 text-xs text-[#2C3B4D] mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#1B2632] transition">Dashboard</a>
                <span>/</span>
                <span class="text-[#C9C1B1]">Menu Kelola Data</span>
                <span>/</span>
                <span class="text-[#1B2632] font-bold">Penempatan Awal</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-[#1B2632] tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#FFB162]/20 text-[#1B2632] flex items-center justify-center">
                    <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                <span>Kelola Data Penempatan Awal Transmigrasi</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#2C3B4D]/80 mt-1">
                Pencatatan dan pemutakhiran data KK penempatan, kependudukan jiwa, dan tahun penempatan awal di 124 UPT se-Kalimantan Selatan.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-auto">
            <a href="{{ route('admin.placements.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#A35139] text-white border border-[#FFB162]/40 font-bold text-xs hover:bg-[#883d28] transition shadow-ambient-xs cursor-pointer">
                <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Ekspor CSV Data KK</span>
            </a>
            <a href="{{ route('admin.upt.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#EEE9DF] text-[#1B2632] border border-[#C9C1B1] font-bold text-xs hover:bg-[#C9C1B1]/40 transition">
                <span>Master Data UPT</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <!-- 2. KARTU METRIK KPI RINGKASAN (MP072 MASTER STAT-CARDS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kartu 1: Total KK Penempatan Awal (Flame) -->
        <x-stat-card 
            label="PENEMPATAN AWAL" 
            :value="number_format($stats['total_kk'], 0, ',', '.')" 
            unit="KK"
            variant="flame" 
            :subtext="'Total Kependudukan: ' . number_format($stats['total_population'], 0, ',', '.') . ' Jiwa'" 
            :footerText="'Registri Warga: ' . number_format($stats['total_nominal_kk'] ?? 0, 0, ',', '.') . ' KK Tercatat'">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <!-- Kartu 2: Cakupan UPT Terdata (Abyssal) -->
        <x-stat-card 
            label="CAKUPAN LOKASI UPT" 
            :value="$stats['upt_count'] . ' / ' . $stats['total_upt'] . ' UPT'" 
            variant="abyssal" 
            subtext="100% Terdata Lengkap" 
            footerText="Sebaran Wilayah: 9 Kabupaten se-Kalsel">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <!-- Kartu 3: Tautan Serah Terima Pemda (Slate) -->
        <x-stat-card 
            label="SERAH TERIMA PEMDA" 
            value="64.957" 
            unit="KK"
            variant="slate" 
            subtext="Pertumbuhan Alami: +1.241 KK (+2,0%)" 
            footerText="Bandingkan Data Serah Terima" 
            :link="route('admin.handovers.index')">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- 3. FILTER DAN PENCARIAN -->
    <div class="bg-white p-4 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs">
        <form method="GET" action="{{ route('admin.placements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Input Cari -->
            <div class="lg:col-span-4">
                <label for="search" class="block text-xs font-bold text-[#2C3B4D] mb-1">Cari Nama UPT / Desa / No. UPT</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Ketik nama UPT, desa, atau nomor..."
                           class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-[#C9C1B1] text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent transition shadow-2xs">
                    <svg class="w-4 h-4 text-[#C9C1B1] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Kabupaten -->
            <div class="lg:col-span-3">
                <label for="regency_id" class="block text-xs font-bold text-[#2C3B4D] mb-1">Kabupaten</label>
                <select name="regency_id" id="regency_id" class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                    <option value="all">Semua Kabupaten (9)</option>
                    @foreach($regencies as $reg)
                        <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                            {{ $reg->name }} ({{ $reg->upt_locations_count }} UPT)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Pola Usaha -->
            <div class="lg:col-span-2">
                <label for="business_pattern" class="block text-xs font-bold text-[#2C3B4D] mb-1">Pola Usaha</label>
                <select name="business_pattern" id="business_pattern" class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                    <option value="all">Semua Pola</option>
                    @foreach($patterns as $pat)
                        <option value="{{ $pat }}" {{ request('business_pattern') == $pat ? 'selected' : '' }}>
                            {{ $pat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tahun -->
            <div class="lg:col-span-2">
                <label for="placement_year" class="block text-xs font-bold text-[#2C3B4D] mb-1">Tahun Penempatan</label>
                <input type="text" name="placement_year" id="placement_year" value="{{ request('placement_year') }}" placeholder="Misal: 1982"
                       class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="lg:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 bg-[#1B2632] hover:bg-[#2C3B4D] text-white rounded-xl font-bold text-xs transition shadow-ambient-xs flex items-center justify-center gap-1 cursor-pointer">
                    <span>Filter</span>
                </button>
                @if(request()->anyFilled(['search', 'regency_id', 'business_pattern', 'placement_year']))
                    <a href="{{ route('admin.placements.index') }}" title="Reset Filter" class="p-2 bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 border border-[#C9C1B1] text-[#1B2632] rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. TABEL DATA PENEMPATAN AWAL -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden" id="placement-table-card">
        <div class="p-4 border-b border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#EEE9DF]/30">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#1B2632] uppercase tracking-wider">Tabel Data KK Penempatan Awal</span>
                <span class="bg-[#2C3B4D]/10 text-[#1B2632] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-[#C9C1B1]/40">
                    {{ $uptLocations->total() }} Lokasi
                </span>
            </div>
            <div class="text-xs text-[#2C3B4D]/80">
                Klik tombol <strong class="text-[#1B2632]">"Registri Warga"</strong> atau <strong class="text-[#1B2632]">"Edit Rekap"</strong> pada baris tabel untuk memutakhirkan data.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 text-center">No. UPT</th>
                        <th class="py-3.5 px-4">Kabupaten</th>
                        <th class="py-3.5 px-4">Nama UPT Asal</th>
                        <th class="py-3.5 px-4">Desa Definitif</th>
                        <th class="py-3.5 px-4 text-center">Pola</th>
                        <th class="py-3.5 px-4 text-center">Tahun Masuk</th>
                        <th class="py-3.5 px-4 text-center bg-[#2C3B4D] text-[#FFB162]">KK Penempatan</th>
                        <th class="py-3.5 px-4 text-center">Total Jiwa</th>
                        <th class="py-3.5 px-4 text-center">Jiwa / KK</th>
                        <th class="py-3.5 px-4 text-center">Registri Nominal Warga</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uptLocations as $upt)
                        @php
                            $ratio = $upt->placement_kk > 0 ? round($upt->placement_population / $upt->placement_kk, 2) : 0;
                            $hasCards = ($upt->placement_family_cards_count ?? 0) > 0;
                        @endphp
                        <tr class="hover:bg-[#EEE9DF]/40 transition" id="row-upt-{{ $upt->id }}">
                            <td class="py-3 px-4 font-mono font-extrabold text-[#1B2632] text-center">
                                UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-[#2C3B4D]">
                                {{ $upt->regency?->name }}
                            </td>
                            <td class="py-3 px-4 font-extrabold text-[#1B2632]">
                                {{ $upt->upt_name }}
                            </td>
                            <td class="py-3 px-4 text-[#2C3B4D]/80 font-medium">
                                {{ $upt->current_village_name }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="bg-[#EEE9DF] text-[#1B2632] font-bold px-2 py-0.5 rounded text-[10px] border border-[#C9C1B1]/50">
                                    {{ $upt->business_pattern }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[#2C3B4D]" id="cell-year-{{ $upt->id }}">
                                {{ $upt->placement_year ?: '-' }}
                            </td>
                            <!-- Kolom KK Penempatan Highlight -->
                            <td class="py-3 px-4 text-center font-black text-[#1B2632] text-sm tabular-nums bg-[#EEE9DF]/40" id="cell-kk-{{ $upt->id }}">
                                {{ number_format($upt->placement_kk, 0, ',', '.') }} <span class="text-[10px] font-normal text-[#2C3B4D]/70">KK</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[#1B2632] tabular-nums" id="cell-pop-{{ $upt->id }}">
                                {{ number_format($upt->placement_population, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[#2C3B4D] tabular-nums" id="cell-ratio-{{ $upt->id }}">
                                {{ number_format($ratio, 2, ',', '.') }}
                            </td>
                            <!-- Kolom Indikator Registri Nominal Warga (Opsi C) -->
                            <td class="py-3 px-4 text-center">
                                @if($hasCards)
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FFB162]/20 hover:bg-[#FFB162]/30 text-[#1B2632] font-extrabold text-[11px] border border-[#FFB162]/50 transition shadow-2xs cursor-pointer"
                                            id="badge-nominal-{{ $upt->id }}"
                                            title="Buka Buku Registri Warga">
                                        <svg class="w-3 h-3 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>{{ $upt->placement_family_cards_count }} KK Terdata</span>
                                    </button>
                                @else
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#2C3B4D] border border-[#C9C1B1] font-medium text-[11px] transition cursor-pointer"
                                            id="badge-nominal-{{ $upt->id }}"
                                            title="Klik untuk input/import nominal warga">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#C9C1B1]"></span>
                                        <span>0 KK (Buka Registri)</span>
                                    </button>
                                @endif
                            </td>
                            <!-- Kolom Tombol Aksi -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs transition shadow-ambient-xs cursor-pointer"
                                            title="Buka Buku Registri Warga Transmigran">
                                        <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <span>Registri Warga</span>
                                    </button>
                                    <button type="button"
                                            onclick="openEditKkModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', {{ $upt->placement_kk }}, {{ $upt->placement_population }}, '{{ addslashes($upt->placement_year) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 border border-[#C9C1B1] text-[#1B2632] font-bold text-xs transition cursor-pointer"
                                            title="Ubah Angka Rekapitulasi Cepat">
                                        <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Edit Rekap</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-[#C9C1B1]">
                                Tidak ada data UPT yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 border-t border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-[#2C3B4D]/80 font-medium">
                Showing <strong class="text-[#1B2632]">{{ $uptLocations->firstItem() ?? 0 }}</strong> to <strong class="text-[#1B2632]">{{ $uptLocations->lastItem() ?? 0 }}</strong> of <strong class="text-[#1B2632]">{{ $uptLocations->total() }}</strong> results
            </div>

            <div class="inline-flex items-center bg-white text-[#1B2632] px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-xs border border-[#C9C1B1] self-center sm:self-auto">
                {{ $uptLocations->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>

<!-- 5. MODAL INTERAKTIF: INPUT / EDIT DATA KK PENEMPATAN AWAL -->
<div id="modal-edit-kk" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden p-4 transition-all">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-[#C9C1B1] overflow-hidden transform transition-all">
        <!-- Header Modal -->
        <div class="bg-[#1B2632] text-white p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-[#FFB162]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-[#EEE9DF]">Simpan Data KK Penempatan Awal</h3>
                    <p class="text-[11px] text-[#C9C1B1]" id="modal-upt-title">UPT-001</p>
                </div>
            </div>
            <button type="button" onclick="closeEditKkModal()" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Form Modal -->
        <form id="form-save-kk" onsubmit="submitEditKk(event)" class="p-5 space-y-4">
            <input type="hidden" id="modal-upt-id" name="upt_id">

            <!-- Jumlah KK Penempatan -->
            <div>
                <label for="modal-placement-kk" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Jumlah Kepala Keluarga (KK) Penempatan <span class="text-[#A35139]">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-placement-kk" name="placement_kk" min="0" required
                           oninput="recalculateRatio()"
                           class="w-full text-sm font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-[#C9C1B1]">KK</span>
                </div>
            </div>

            <!-- Jumlah Jiwa / Kependudukan -->
            <div>
                <label for="modal-placement-pop" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Total Kependudukan (Jiwa) <span class="text-[#A35139]">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-placement-pop" name="placement_population" min="0" required
                           oninput="recalculateRatio()"
                           class="w-full text-sm font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-[#C9C1B1]">Jiwa</span>
                </div>
            </div>

            <!-- Tahun Penempatan -->
            <div>
                <label for="modal-placement-year" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Tahun Penempatan Awal <span class="text-[#A35139]">*</span>
                </label>
                <input type="text" id="modal-placement-year" name="placement_year" required
                       placeholder="Misal: 1982 atau 1982/1983"
                       class="w-full text-xs font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
            </div>

            <!-- Kalkulasi Estimasi Rasio -->
            <div class="bg-[#EEE9DF]/60 border border-[#C9C1B1]/60 p-3 rounded-xl flex items-center justify-between text-xs">
                <span class="text-[#2C3B4D] font-medium">Estimasi Rata-rata:</span>
                <span class="font-extrabold text-[#1B2632] tabular-nums" id="modal-ratio-preview">0,00 Jiwa/KK</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-3 border-t border-[#C9C1B1]/40 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditKkModal()"
                        class="px-4 py-2 rounded-xl border border-[#C9C1B1] text-xs font-bold text-[#2C3B4D] hover:bg-[#EEE9DF]/60 transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-modal"
                        class="px-5 py-2 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white text-xs font-bold transition shadow-ambient-xs flex items-center gap-2 border border-[#FFB162]/40 cursor-pointer">
                    <span id="btn-submit-text">Simpan Perubahan KK</span>
                    <svg id="btn-submit-spinner" class="w-3.5 h-3.5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast-notify" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="bg-slate-900 text-white text-xs font-bold px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 border border-slate-700" id="toast-content">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span id="toast-text">Pemberitahuan</span>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toast = document.getElementById('toast-notify');
    const toastText = document.getElementById('toast-text');
    const toastContent = document.getElementById('toast-content');

    if (!toast || !toastText) return;

    toastText.textContent = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3500);
}

function openEditKkModal(id, title, kk, pop, year) {
    document.getElementById('modal-upt-id').value = id;
    document.getElementById('modal-upt-title').textContent = title;
    document.getElementById('modal-placement-kk').value = kk;
    document.getElementById('modal-placement-pop').value = pop;
    document.getElementById('modal-placement-year').value = year || '';

    recalculateRatio();

    document.getElementById('modal-edit-kk').classList.remove('hidden');
}

function closeEditKkModal() {
    document.getElementById('modal-edit-kk').classList.add('hidden');
}

function recalculateRatio() {
    const kk = parseFloat(document.getElementById('modal-placement-kk').value) || 0;
    const pop = parseFloat(document.getElementById('modal-placement-pop').value) || 0;
    const ratio = kk > 0 ? (pop / kk).toFixed(2) : '0,00';
    document.getElementById('modal-ratio-preview').textContent = ratio.replace('.', ',') + ' Jiwa/KK';
}

function submitEditKk(e) {
    e.preventDefault();

    const id = document.getElementById('modal-upt-id').value;
    const kk = document.getElementById('modal-placement-kk').value;
    const pop = document.getElementById('modal-placement-pop').value;
    const year = document.getElementById('modal-placement-year').value;

    const btn = document.getElementById('btn-submit-modal');
    const btnText = document.getElementById('btn-submit-text');
    const spinner = document.getElementById('btn-submit-spinner');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Menyimpan...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch(`/admin/placements/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            placement_kk: kk,
            placement_population: pop,
            placement_year: year,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Perbarui sel tabel langsung (DOM Update)
            const cellKk = document.getElementById(`cell-kk-${id}`);
            const cellPop = document.getElementById(`cell-pop-${id}`);
            const cellYear = document.getElementById(`cell-year-${id}`);
            const cellRatio = document.getElementById(`cell-ratio-${id}`);

            if (cellKk) cellKk.innerHTML = `${Number(data.data.placement_kk).toLocaleString('id-ID')} <span class="text-[10px] font-normal text-slate-500">KK</span>`;
            if (cellPop) cellPop.textContent = Number(data.data.placement_population).toLocaleString('id-ID');
            if (cellYear) cellYear.textContent = data.data.placement_year;
            if (cellRatio) cellRatio.textContent = Number(data.data.ratio).toLocaleString('id-ID', { minimumFractionDigits: 2 });

            // Perbarui KPI atas
            if (data.data.total_placement_kk) {
                const kpiKk = document.getElementById('kpi-total-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_placement_kk).toLocaleString('id-ID')} <span class="text-base font-bold text-slate-600">KK</span>`;
            }
            if (data.data.total_placement_pop) {
                const kpiPop = document.getElementById('kpi-total-pop');
                if (kpiPop) kpiPop.textContent = Number(data.data.total_placement_pop).toLocaleString('id-ID');
            }

            closeEditKkModal();
            showToast(data.message);
        } else {
            alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan sistem'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Gagal menyimpan data KK. Silakan periksa koneksi atau input Anda.');
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Simpan Perubahan KK';
    });
}
</script>

@include('admin.registri_warga.registri_warga')
@endsection
