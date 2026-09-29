@extends('layouts.admin')

@section('title', 'Kelola Serah Terima Pemda - SIGAP-TRANS Kalsel')

@section('content')
<div class="space-y-6">

    <!-- 1. HEADER HALAMAN -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs">
        <div>
            <div class="flex items-center gap-2.5 text-xs text-[#2C3B4D]/70 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#1B2632] transition">Dashboard</a>
                <span>/</span>
                <span class="text-[#2C3B4D]/50">Menu Kelola Data</span>
                <span>/</span>
                <span class="text-[#1B2632] font-bold">Serah Terima Pemda</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-[#1B2632] tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#FFB162]/20 text-[#A35139] flex items-center justify-center shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
                <span>Kelola Data Serah Terima Pemda & Pertumbuhan KK</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#2C3B4D]/75 mt-1">
                Rekapitulasi serah terima pembinaan UPT ke Pemerintah Daerah Kabupaten, perbandingan pertumbuhan KK definitif, dan riwayat berkas BAST.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-auto">
            <a href="{{ route('admin.handovers.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-bold text-xs shadow-ambient-xs transition">
                <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Ekspor CSV Data KK</span>
            </a>
            <a href="{{ route('admin.documents.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] border border-[#C9C1B1]/70 font-bold text-xs transition shadow-2xs">
                <span>E-Arsip BAST</span>
                <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <!-- 2. KARTU METRIK KPI RINGKASAN (MP072 PALETTE) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kartu 1: Total KK Serah Terima Pemda (Flame Accent - Preserves DOM IDs for Live AJAX) -->
        <div class="relative bg-white rounded-2xl p-5 border border-[#FFB162]/60 hover:border-[#FFB162] shadow-ambient-xs hover:shadow-ambient transition-all duration-200 flex flex-col justify-between overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-[#FFB162] opacity-90"></div>
            <div class="flex items-start justify-between gap-3 pt-1">
                <span class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#2C3B4D]/70 block">SERAH TERIMA PEMDA</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/40 shadow-xs transition-transform duration-200 group-hover:scale-105">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-4 pt-1">
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight tabular-nums text-[#8F4E0A] flex items-baseline gap-1.5" id="kpi-handover-kk">
                    {{ number_format($stats['total_handover_kk'], 0, ',', '.') }} <span class="text-xs font-semibold text-[#8F4E0A]/70 uppercase tracking-wider">KK</span>
                </div>
                <div class="text-xs text-[#2C3B4D]/75 mt-1.5 font-medium">
                    Keluarga Definitif: <strong class="text-[#1B2632] font-extrabold tabular-nums" id="kpi-handover-pop">{{ number_format($stats['total_handover_pop'], 0, ',', '.') }}</strong> Jiwa
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#C9C1B1]/40 flex items-center justify-between text-[11px]">
                    <span class="text-[#2C3B4D]/70">Registri Warga</span>
                    <span class="font-extrabold text-[#1B2632] tabular-nums">{{ number_format($stats['total_nominal_kk'] ?? 0, 0, ',', '.') }} KK Tercatat</span>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Rasio Penyerahan ke Pemda (Abyssal) -->
        <x-stat-card 
            label="STATUS PENYERAHAN UPT" 
            :value="$stats['handed_over_upts']" 
            :unit="'/ ' . $stats['total_upt'] . ' UPT'" 
            variant="abyssal" 
            :subtext="'Tingkat Penyerahan: ' . round(($stats['handed_over_upts'] / max(1, $stats['total_upt'])) * 100, 1) . '%'">
            <x-slot:customIcon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:footer>
                <div class="flex items-center justify-between">
                    <span class="text-[#2C3B4D]/70">Pembinaan Pemda</span>
                    <span class="font-bold text-[#1B2632]">Diserahkan Penuh</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Kartu 3: Tautan Penempatan Awal (Slate) -->
        <x-stat-card 
            label="PENEMPATAN AWAL" 
            :value="number_format($stats['total_placement_kk'], 0, ',', '.')" 
            unit="KK" 
            variant="slate" 
            subtext="Total Kependudukan: 256.352 Jiwa">
            <x-slot:customIcon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:footer>
                <div class="flex items-center justify-between">
                    <span class="text-[#2C3B4D]/70">Bandingkan Data</span>
                    <a href="{{ route('admin.placements.index') }}" class="font-bold text-[#A35139] hover:underline flex items-center gap-1">
                        <span>Lihat Penempatan Awal</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </x-slot:footer>
        </x-stat-card>
    </div>

    <!-- 3. FILTER DAN PENCARIAN -->
    <div class="bg-white p-4 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs">
        <form method="GET" action="{{ route('admin.handovers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Input Cari -->
            <div class="lg:col-span-4">
                <label for="search" class="block text-xs font-bold text-[#2C3B4D] mb-1">Cari Nama UPT / Desa / No. UPT</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Ketik nama UPT, desa, atau nomor..."
                           class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
                    <svg class="w-4 h-4 text-[#2C3B4D]/40 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Kabupaten -->
            <div class="lg:col-span-3">
                <label for="regency_id" class="block text-xs font-bold text-[#2C3B4D] mb-1">Kabupaten</label>
                <select name="regency_id" id="regency_id" class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
                    <option value="all">Semua Kabupaten (9)</option>
                    @foreach($regencies as $reg)
                        <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                            {{ $reg->name }} ({{ $reg->upt_locations_count }} UPT)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Serah Terima -->
            <div class="lg:col-span-2">
                <label for="handover_status" class="block text-xs font-bold text-[#2C3B4D] mb-1">Status Penyerahan</label>
                <select name="handover_status" id="handover_status" class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
                    <option value="all">Semua Status</option>
                    <option value="handed_over" {{ request('handover_status') === 'handed_over' ? 'selected' : '' }}>Sudah Serah Terima</option>
                    <option value="pending" {{ request('handover_status') === 'pending' ? 'selected' : '' }}>Belum Serah Terima</option>
                </select>
            </div>

            <!-- Filter Tahun BAST -->
            <div class="lg:col-span-2">
                <label for="handover_year" class="block text-xs font-bold text-[#2C3B4D] mb-1">Tahun BAST</label>
                <input type="text" name="handover_year" id="handover_year" value="{{ request('handover_year') }}" placeholder="Misal: 1987"
                       class="w-full text-xs py-2 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="lg:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] rounded-xl font-bold text-xs transition shadow-ambient-xs flex items-center justify-center gap-1">
                    <span>Filter</span>
                </button>
                @if(request()->anyFilled(['search', 'regency_id', 'handover_status', 'handover_year']))
                    <a href="{{ route('admin.handovers.index') }}" title="Reset Filter" class="p-2 bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] border border-[#C9C1B1]/70 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. TABEL DATA SERAH TERIMA PEMDA -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden" id="handover-table-card">
        <div class="p-4 border-b border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#EEE9DF]/30">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#2C3B4D] uppercase tracking-wider">Tabel Data KK Serah Terima Pemda</span>
                <span class="bg-[#1B2632]/10 text-[#1B2632] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-[#1B2632]/15">
                    {{ $uptLocations->total() }} Lokasi
                </span>
            </div>
            <div class="text-xs text-[#2C3B4D]/75">
                Klik tombol <strong class="text-[#1B2632]">"Kelola KK"</strong> pada baris tabel untuk mengubah atau menyimpan data KK definitif.
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
                        <th class="py-3.5 px-4 text-center">Tahun BAST</th>
                        <th class="py-3.5 px-4 text-center">KK Penempatan</th>
                        <th class="py-3.5 px-4 text-center bg-[#2C3B4D] text-[#FFB162]">KK Serah Terima</th>
                        <th class="py-3.5 px-4 text-center">Pertumbuhan (+/-)</th>
                        <th class="py-3.5 px-4 text-center">Keluarga Definitif</th>
                        <th class="py-3.5 px-4 text-center">E-Arsip BAST</th>
                        <th class="py-3.5 px-4 text-center">Registri Nominal Warga</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($uptLocations as $upt)
                        @php
                            $diff = $upt->handover_kk - $upt->placement_kk;
                            $pct = $upt->placement_kk > 0 ? round(($diff / $upt->placement_kk) * 100, 1) : 0;
                            $bastDoc = $upt->documents->first();
                            $hasCards = ($upt->handover_family_cards_count ?? 0) > 0;
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
                            <td class="py-3 px-4 text-center font-bold text-[#2C3B4D]" id="cell-year-{{ $upt->id }}">
                                {{ $upt->handover_year ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[#2C3B4D]/70 tabular-nums">
                                {{ number_format($upt->placement_kk, 0, ',', '.') }}
                            </td>
                            <!-- Kolom KK Serah Terima Highlight -->
                            <td class="py-3 px-4 text-center font-black text-[#1B2632] text-sm tabular-nums bg-[#FFB162]/10" id="cell-kk-{{ $upt->id }}">
                                {{ number_format($upt->handover_kk, 0, ',', '.') }} <span class="text-[10px] font-normal text-[#2C3B4D]/70">KK</span>
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums font-bold" id="cell-diff-{{ $upt->id }}">
                                @if($diff > 0)
                                    <span class="text-[#2C3B4D] font-extrabold">+{{ number_format($diff, 0, ',', '.') }} (+{{ $pct }}%)</span>
                                @elseif($diff < 0)
                                    <span class="text-[#A35139] font-extrabold">{{ number_format($diff, 0, ',', '.') }} ({{ $pct }}%)</span>
                                @else
                                    <span class="text-[#2C3B4D]/50">0 (0%)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[#1B2632] tabular-nums" id="cell-pop-{{ $upt->id }}">
                                {{ number_format($upt->handover_population, 0, ',', '.') }} <span class="text-[10px] font-normal text-[#2C3B4D]/60">Jiwa</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($bastDoc)
                                    <a href="{{ route('admin.documents.download', $bastDoc->id) }}"
                                       title="Unduh Berkas BAST Digital"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] border border-[#C9C1B1]/70 font-bold text-[10px] transition">
                                        <svg class="w-3 h-3 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span>PDF BAST</span>
                                    </a>
                                @else
                                    <span class="text-[#2C3B4D]/40 text-[11px]">-</span>
                                @endif
                            </td>
                            <!-- Kolom Indikator Registri Nominal Warga -->
                            <td class="py-3 px-4 text-center">
                                @if($hasCards)
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'handover')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FFB162]/20 hover:bg-[#FFB162]/30 text-[#8F4E0A] font-extrabold text-[11px] border border-[#FFB162]/40 transition shadow-2xs cursor-pointer"
                                            id="badge-nominal-{{ $upt->id }}"
                                            title="Buka Buku Registri Warga Serah Terima">
                                        <svg class="w-3 h-3 text-[#8F4E0A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>{{ $upt->handover_family_cards_count }} KK Terdata</span>
                                    </button>
                                @else
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'handover')"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#2C3B4D]/75 border border-[#C9C1B1]/60 font-medium text-[11px] transition cursor-pointer"
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
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'handover')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-xs transition shadow-ambient-xs cursor-pointer"
                                            title="Buka Buku Registri Warga Transmigran Serah Terima">
                                        <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <span>Registri Warga</span>
                                    </button>
                                    <button type="button"
                                            onclick="openEditHandoverModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', {{ $upt->handover_kk }}, {{ $upt->handover_population }}, '{{ addslashes($upt->handover_year) }}', {{ $upt->placement_kk }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] border border-[#C9C1B1]/70 font-bold text-xs transition cursor-pointer"
                                            title="Ubah Angka Rekapitulasi Cepat">
                                        <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Edit Rekap</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-8 text-[#2C3B4D]/60 font-medium">
                                Tidak ada data UPT yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 border-t border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#EEE9DF]/20">
            <div class="text-xs text-[#2C3B4D]/75 font-medium">
                Showing <strong class="text-[#1B2632]">{{ $uptLocations->firstItem() ?? 0 }}</strong> to <strong class="text-[#1B2632]">{{ $uptLocations->lastItem() ?? 0 }}</strong> of <strong class="text-[#1B2632]">{{ $uptLocations->total() }}</strong> results
            </div>

            <div class="inline-flex items-center self-center sm:self-auto">
                {{ $uptLocations->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>

<!-- 5. MODAL INTERAKTIF: INPUT / EDIT DATA KK SERAH TERIMA PEMDA -->
<div id="modal-edit-handover" class="fixed inset-0 z-50 bg-[#1B2632]/60 backdrop-blur-xs flex items-center justify-center hidden p-4 transition-all">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-[#C9C1B1] overflow-hidden transform transition-all">
        <!-- Header Modal -->
        <div class="bg-[#1B2632] text-[#EEE9DF] p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-[#FFB162]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black">Simpan Data KK Serah Terima Pemda</h3>
                    <p class="text-[11px] text-[#EEE9DF]/70" id="modal-upt-title">UPT-001</p>
                </div>
            </div>
            <button type="button" onclick="closeEditHandoverModal()" class="text-[#EEE9DF]/70 hover:text-white p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Form Modal -->
        <form id="form-save-handover" onsubmit="submitEditHandover(event)" class="p-5 space-y-4">
            <input type="hidden" id="modal-upt-id" name="upt_id">
            <input type="hidden" id="modal-placement-base" value="0">

            <!-- Jumlah KK Serah Terima -->
            <div>
                <label for="modal-handover-kk" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Jumlah KK Serah Terima Pemda <span class="text-[#A35139]">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-handover-kk" name="handover_kk" min="0" required
                           oninput="recalculateHandoverDiff()"
                           class="w-full text-sm font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-[#2C3B4D]/60">KK</span>
                </div>
            </div>

            <!-- Jumlah Jiwa / Kependudukan Definitif -->
            <div>
                <label for="modal-handover-pop" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Keluarga Definitif (Total Jiwa) <span class="text-[#A35139]">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-handover-pop" name="handover_population" min="0" required
                           class="w-full text-sm font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-[#2C3B4D]/60">Jiwa</span>
                </div>
            </div>

            <!-- Tahun BAST Serah Terima -->
            <div>
                <label for="modal-handover-year" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                    Tahun BAST Serah Terima Pemda
                </label>
                <input type="text" id="modal-handover-year" name="handover_year"
                       placeholder="Misal: 1987"
                       class="w-full text-xs font-bold text-[#1B2632] py-2.5 px-3 rounded-xl border border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs">
            </div>

            <!-- Kalkulasi Pertumbuhan Alami -->
            <div class="bg-[#FFB162]/15 border border-[#FFB162]/40 p-3 rounded-xl flex items-center justify-between text-xs">
                <span class="text-[#8F4E0A] font-medium">Pertumbuhan dari Penempatan:</span>
                <span class="font-extrabold text-[#8F4E0A] tabular-nums" id="modal-growth-preview">+0 KK (+0,0%)</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-3 border-t border-[#C9C1B1]/40 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditHandoverModal()"
                        class="px-4 py-2 rounded-xl border border-[#C9C1B1]/70 text-xs font-bold text-[#2C3B4D] hover:bg-[#EEE9DF] transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-handover-modal"
                        class="px-5 py-2 rounded-xl bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] text-xs font-bold transition shadow-ambient-xs flex items-center gap-2">
                    <span id="btn-submit-handover-text">Simpan Data Serah Terima</span>
                    <svg id="btn-submit-handover-spinner" class="w-3.5 h-3.5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
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

    if (!toast || !toastText) return;

    toastText.textContent = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3500);
}

function openEditHandoverModal(id, title, handoverKk, pop, year, placementKk) {
    document.getElementById('modal-upt-id').value = id;
    document.getElementById('modal-upt-title').textContent = title;
    document.getElementById('modal-handover-kk').value = handoverKk;
    document.getElementById('modal-handover-pop').value = pop;
    document.getElementById('modal-handover-year').value = year || '';
    document.getElementById('modal-placement-base').value = placementKk;

    recalculateHandoverDiff();

    document.getElementById('modal-edit-handover').classList.remove('hidden');
}

function closeEditHandoverModal() {
    document.getElementById('modal-edit-handover').classList.add('hidden');
}

function recalculateHandoverDiff() {
    const handoverKk = parseFloat(document.getElementById('modal-handover-kk').value) || 0;
    const placementKk = parseFloat(document.getElementById('modal-placement-base').value) || 0;
    const diff = handoverKk - placementKk;
    const pct = placementKk > 0 ? ((diff / placementKk) * 100).toFixed(1) : '0,0';

    const sign = diff > 0 ? '+' : '';
    document.getElementById('modal-growth-preview').textContent = `${sign}${diff.toLocaleString('id-ID')} KK (${sign}${pct.replace('.', ',')}%)`;
}

function submitEditHandover(e) {
    e.preventDefault();

    const id = document.getElementById('modal-upt-id').value;
    const kk = document.getElementById('modal-handover-kk').value;
    const pop = document.getElementById('modal-handover-pop').value;
    const year = document.getElementById('modal-handover-year').value;

    const btn = document.getElementById('btn-submit-handover-modal');
    const btnText = document.getElementById('btn-submit-handover-text');
    const spinner = document.getElementById('btn-submit-handover-spinner');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Menyimpan...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch(`/admin/handovers/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            handover_kk: kk,
            handover_population: pop,
            handover_year: year,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Perbarui sel tabel langsung (DOM Update)
            const cellKk = document.getElementById(`cell-kk-${id}`);
            const cellPop = document.getElementById(`cell-pop-${id}`);
            const cellYear = document.getElementById(`cell-year-${id}`);
            const cellDiff = document.getElementById(`cell-diff-${id}`);

            if (cellKk) cellKk.innerHTML = `${Number(data.data.handover_kk).toLocaleString('id-ID')} <span class="text-[10px] font-normal text-slate-500">KK</span>`;
            if (cellPop) cellPop.innerHTML = `${Number(data.data.handover_population).toLocaleString('id-ID')} <span class="text-[10px] font-normal text-slate-400">Jiwa</span>`;
            if (cellYear) cellYear.textContent = data.data.handover_year || '-';

            if (cellDiff) {
                const diff = data.data.growth;
                const pct = data.data.growth_pct;
                if (diff > 0) {
                    cellDiff.innerHTML = `<span class="text-emerald-700">+${Number(diff).toLocaleString('id-ID')} (+${pct}%)</span>`;
                } else if (diff < 0) {
                    cellDiff.innerHTML = `<span class="text-rose-600">${Number(diff).toLocaleString('id-ID')} (${pct}%)</span>`;
                } else {
                    cellDiff.innerHTML = `<span class="text-slate-400">0 (0%)</span>`;
                }
            }

            // Perbarui KPI atas
            if (data.data.total_handover_kk) {
                const kpiKk = document.getElementById('kpi-handover-kk');
                if (kpiKk) kpiKk.innerHTML = `${Number(data.data.total_handover_kk).toLocaleString('id-ID')} <span class="text-base font-bold text-slate-600">KK</span>`;
            }
            if (data.data.total_handover_pop) {
                const kpiPop = document.getElementById('kpi-handover-pop');
                if (kpiPop) kpiPop.textContent = Number(data.data.total_handover_pop).toLocaleString('id-ID');
            }

            closeEditHandoverModal();
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
        btnText.textContent = 'Simpan Data Serah Terima';
    });
}
</script>

@include('admin.registri_warga.registri_warga')
@endsection
