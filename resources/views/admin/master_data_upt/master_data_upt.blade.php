@extends('layouts.admin')

@section('title', 'Master Data UPT Transmigrasi')
@section('header_title', 'Master Data Unit Pemukiman Transmigrasi (UPT)')
@section('header_subtitle', 'Pengelolaan basis data induk historis dan status agraria di 9 kabupaten Kalimantan Selatan')

@section('content')
    <div class="space-y-6">

        <!-- 1. REKAPITULASI SEBARAN DI 9 KABUPATEN (INTERACTIVE QUICK-FILTER CARDS) -->
        <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-6" x-data="{ 
                            isExpanded: (function() { 
                                try { return localStorage.getItem('sigap_upt_cards_open') !== 'false'; } 
                                catch(e) { return true; } 
                            })() 
                        }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between transition-all duration-200 gap-3"
                :class="isExpanded ? 'pb-4 mb-4 border-b border-[#C9C1B1]/40' : 'pb-0 mb-0'">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFB162]"></span>
                        <h3 class="font-black text-[#1B2632] text-base">
                            Rekapitulasi Sebaran UPT di 9 Kabupaten
                        </h3>
                    </div>
                    <p class="text-xs text-[#2C3B4D]/80 mt-1">
                        Distribusi jumlah lokasi, penempatan warga, dan serah terima aset ke Pemerintah Daerah. Klik kartu
                        kabupaten untuk memfilter tabel langsung.
                    </p>
                </div>
                <div id="quick-cards-header-actions" class="flex flex-wrap items-center gap-2 shrink-0">
                    @if(request()->filled('regency_id') && request('regency_id') !== 'all')
                        <a href="{{ route('admin.upt.index', request()->except(['regency_id', 'page'])) }}"
                            id="btn-clear-regency-filter"
                            class="text-xs font-bold text-[#A35139] bg-[#A35139]/10 hover:bg-[#A35139]/20 px-3 py-1.5 rounded-xl border border-[#A35139]/30 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <span>✕ Hapus Filter Kabupaten</span>
                        </a>
                    @endif
                    <span
                        class="text-xs font-extrabold text-[#2C3B4D] bg-[#2C3B4D]/10 px-3 py-1.5 rounded-xl border border-[#C9C1B1]/60">
                        Total {{ $stats['total'] }} Lokasi Definitif
                    </span>
                    <a href="{{ route('admin.upt.create') }}"
                        class="bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-extrabold text-xs px-3.5 py-1.5 rounded-xl transition shadow-ambient-xs flex items-center gap-1.5 border border-[#FFB162]/40">
                        <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span> Tambah UPT</span>
                    </a>

                    <!-- Tombol Dropdown Buka / Tutup Rekapitulasi Kartu Kabupaten -->
                    <button type="button"
                        @click="isExpanded = !isExpanded; try { localStorage.setItem('sigap_upt_cards_open', isExpanded); } catch(e) {}"
                        class="bg-white hover:bg-[#EEE9DF]/60 text-[#1B2632] font-extrabold text-xs px-3 py-1.5 rounded-xl transition shadow-2xs flex items-center gap-1.5 border border-[#C9C1B1] cursor-pointer select-none"
                        id="btn-toggle-regency-cards"
                        :title="isExpanded ? 'Tutup Rekapitulasi Sebaran UPT' : 'Buka Rekapitulasi Sebaran UPT'"
                        aria-controls="regency-cards-grid" :aria-expanded="isExpanded.toString()">
                        <span x-text="isExpanded ? 'Tutup' : 'Buka'">Tutup</span>
                        <svg class="w-3.5 h-3.5 text-[#2C3B4D] transition-transform duration-200"
                            :class="isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="regency-cards-grid" x-show="isExpanded" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($regencies as $reg)
                    @php
                        $isSelected = request('regency_id') == $reg->id;
                        $cleanCount = $reg->uptLocations->where('issue_status', 'clean')->count();
                        $warningCount = $reg->uptLocations->where('issue_status', 'warning')->count();
                        $criticalCount = $reg->uptLocations->where('issue_status', 'critical')->count();
                        $placementKk = $reg->uptLocations->sum('placement_kk');
                        $handoverKk = $reg->uptLocations->sum('handover_kk');
                    @endphp

                    <a href="{{ $isSelected ? route('admin.upt.index', request()->except(['regency_id', 'page'])) : route('admin.upt.index', array_merge(request()->except('page'), ['regency_id' => $reg->id])) }}"
                        data-regency-card-id="{{ $reg->id }}"
                        class="p-4 rounded-xl border transition group relative block cursor-pointer {{ $isSelected ? 'border-2 border-[#1B2632] bg-[#EEE9DF]/40 shadow-ambient-sm ring-2 ring-[#FFB162]/30' : 'border-[#C9C1B1]/70 bg-white hover:border-[#1B2632]/50 hover:shadow-ambient-xs' }}"
                        title="{{ $isSelected ? 'Klik untuk membatalkan filter' : 'Klik untuk memfilter tabel ke Kabupaten ' . $reg->name }}">

                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="text-xs font-extrabold uppercase px-2 py-0.5 rounded transition {{ $isSelected ? 'text-white bg-[#1B2632]' : 'text-[#1B2632] bg-[#1B2632]/10 group-hover:bg-[#1B2632] group-hover:text-white' }}">
                                    {{ $reg->name }}
                                </span>
                                @if($isSelected)
                                    <span
                                        class="text-[10px] font-bold text-[#1B2632] bg-[#FFB162]/30 px-1.5 py-0.5 rounded border border-[#FFB162]/40 flex items-center gap-1">
                                        ✓ Aktif
                                    </span>
                                @endif
                            </div>
                            <span
                                class="text-xs font-black px-2 py-0.5 rounded-full transition {{ $isSelected ? 'text-[#1B2632] bg-[#FFB162]' : 'text-[#2C3B4D] bg-[#C9C1B1]/30 group-hover:bg-[#FFB162] group-hover:text-[#1B2632]' }}">
                                {{ $reg->upt_locations_count }} UPT
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                            <div>
                                <span class="text-[10px] text-[#2C3B4D]/70 block uppercase font-bold">Penempatan</span>
                                <span
                                    class="font-bold text-[#1B2632] tabular-nums">{{ number_format($placementKk, 0, ',', '.') }}
                                    KK</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#2C3B4D]/70 block uppercase font-bold">Serah Terima</span>
                                <span
                                    class="font-bold text-[#1B2632] tabular-nums">{{ number_format($handoverKk, 0, ',', '.') }}
                                    KK</span>
                            </div>
                        </div>

                        <!-- Indikator Status di Kabupaten -->
                        <div class="mt-3 pt-2.5 border-t border-[#C9C1B1]/40 flex items-center justify-between text-[11px]">
                            <span class="text-emerald-700 font-bold">🟢 {{ $cleanCount }} Clean</span>
                            <span class="text-amber-700 font-bold">🟡 {{ $warningCount }} Warning</span>
                            <span class="text-rose-700 font-bold">🔴 {{ $criticalCount }} Kritis</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 2. BAR PENCARIAN & FILTER MULTI-KRITERIA (LIVE AUTO-UPDATE TANPA TOMBOL SARING) -->
        <!-- 2. BAR PENCARIAN & FILTER MULTI-KRITERIA (LIVE AUTO-UPDATE TANPA TOMBOL SARING) -->
        <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs">
            <form id="upt-filter-form" method="GET" action="{{ route('admin.upt.index') }}" onsubmit="return false;"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">

                <!-- Pencarian Teks (Live Debounced) -->
                <div class="sm:col-span-2 lg:col-span-5">
                    <label for="search" class="text-[11px] font-bold text-[#2C3B4D] uppercase block mb-1">Cari Nama UPT /
                        Desa</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Ketik nama UPT, desa, atau nomor UPT..." autocomplete="off"
                            class="w-full bg-white border border-[#C9C1B1] text-[#1B2632] placeholder-[#C9C1B1] rounded-xl px-3 py-2 pl-9 pr-9 text-xs focus:ring-2 focus:ring-[#FFB162] focus:border-transparent transition">
                        <svg class="w-4 h-4 text-[#C9C1B1] absolute left-3 top-2.5 pointer-events-none" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>

                        <!-- Tombol Hapus Teks Pencarian (X) -->
                        <button type="button" id="btn-clear-search"
                            class="{{ request('search') ? '' : 'hidden' }} absolute right-3 top-2.5 text-[#C9C1B1] hover:text-[#1B2632] transition cursor-pointer"
                            title="Hapus pencarian">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- Indikator Spinner Memuat Data -->
                        <div id="search-spinner" class="hidden absolute right-3 top-2.5">
                            <svg class="animate-spin w-3.5 h-3.5 text-[#FFB162]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Filter Kabupaten (Auto-update on change) -->
                <div class="sm:col-span-1 lg:col-span-3">
                    <label for="regency_id"
                        class="text-[11px] font-bold text-[#2C3B4D] uppercase block mb-1">Kabupaten</label>
                    <div class="relative">
                        <select name="regency_id" id="regency_id"
                            class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 pr-8 text-xs font-medium text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] cursor-pointer appearance-none transition">
                            <option value="all">Semua 9 Kabupaten</option>
                            @foreach($regencies as $reg)
                                <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Filter Status Agraria (Auto-update on change) -->
                <div class="sm:col-span-1 lg:col-span-3">
                    <label for="status" class="text-[11px] font-bold text-[#2C3B4D] uppercase block mb-1">Status
                        Lahan</label>
                    <div class="relative">
                        <select name="status" id="status"
                            class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 pr-8 text-xs font-medium text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] cursor-pointer appearance-none transition">
                            <option value="all">Semua Status Lahan</option>
                            <option value="clean" {{ request('status') == 'clean' ? 'selected' : '' }}>🟢 Clean & Clear</option>
                            <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>🟡 Waspada / Monitoring</option>
                            <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>🔴 Kritis / Prioritas Mediasi</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Tombol Reset Saja -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <button type="button" id="btn-reset-filters"
                        class="w-full bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] font-bold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5 border border-[#C9C1B1] shadow-2xs h-[38px] cursor-pointer"
                        title="Kembalikan semua filter ke pengaturan awal">
                        <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- 3. TABEL DATA UPT (HEADER NAVY MP072: #1B2632) -->
        <div id="upt-table-card"
            class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden transition-opacity duration-200">
            <div class="p-5 border-b border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-[#2C3B4D]">Total Ditemukan:</span>
                    <span
                        class="font-extrabold text-xs text-[#1B2632] bg-[#2C3B4D]/10 px-2.5 py-0.5 rounded-full border border-[#C9C1B1]/40">
                        {{ $uptLocations->total() }} dari {{ $stats['total'] }} Lokasi
                    </span>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-emerald-700 font-bold">🟢 {{ $stats['clean'] }} Clean</span>
                        <span class="text-amber-700 font-bold">🟡 {{ $stats['warning'] }} Warning</span>
                        <span class="text-rose-700 font-bold">🔴 {{ $stats['critical'] }} Kritis</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">No. UPT</th>
                            <th class="py-3.5 px-4">Kabupaten</th>
                            <th class="py-3.5 px-4">Nama UPT Asal</th>
                            <th class="py-3.5 px-4">Desa Definitif</th>
                            <th class="py-3.5 px-4">Pola</th>
                            <th class="py-3.5 px-4 text-center">Masuk (KK)</th>
                            <th class="py-3.5 px-4 text-center">Serah (KK)</th>
                            <th class="py-3.5 px-4 text-center">Status Lahan</th>
                            <th class="py-3.5 px-4 text-center">E-Arsip BAST</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($uptLocations as $upt)
                            <tr class="hover:bg-[#EEE9DF]/40 transition">
                                <!-- No UPT -->
                                <td class="py-3.5 px-4 font-mono font-extrabold text-[#1B2632]">
                                    UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <!-- Kabupaten -->
                                <td class="py-3.5 px-4 font-bold text-[#2C3B4D]">
                                    {{ $upt->regency?->name }}
                                </td>
                                <!-- Nama UPT Asal -->
                                <td class="py-3.5 px-4 font-extrabold text-[#1B2632]">
                                    {{ $upt->upt_name }}
                                </td>
                                <!-- Desa Definitif -->
                                <td class="py-3.5 px-4 text-[#2C3B4D]/80 font-medium">
                                    {{ $upt->current_village_name }}
                                </td>
                                <!-- Pola Usaha -->
                                <td class="py-3.5 px-4">
                                    <span
                                        class="bg-[#EEE9DF] text-[#1B2632] font-bold px-2 py-0.5 rounded text-[11px] border border-[#C9C1B1]/50">
                                        {{ $upt->business_pattern }}
                                    </span>
                                </td>
                                <!-- Penempatan KK -->
                                <td class="py-3.5 px-4 text-center font-bold text-[#1B2632] tabular-nums">
                                    {{ number_format($upt->placement_kk) }}
                                </td>
                                <!-- Penyerahan KK -->
                                <td class="py-3.5 px-4 text-center font-bold text-[#1B2632] tabular-nums">
                                    {{ number_format($upt->handover_kk) }}
                                </td>
                                <!-- Status Lahan Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    <x-status-badge :status="$upt->issue_status" />
                                </td>
                                <!-- E-Arsip BAST -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($upt->documents->count() > 0)
                                        <span
                                            class="text-xs font-bold text-[#2C3B4D] bg-[#2C3B4D]/10 px-2 py-0.5 rounded border border-[#C9C1B1]">
                                            {{ $upt->documents->count() }} Dokumen
                                        </span>
                                    @else
                                        <span class="text-[10px] text-[#C9C1B1] font-medium">Belum Diunggah</span>
                                    @endif
                                </td>
                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.upt.show', $upt->id) }}" title="Lihat Detail Profil Komprehensif UPT"
                                            class="bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] p-1.5 rounded-lg border border-[#C9C1B1]/60 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.upt.edit', $upt->id) }}" title="Edit Data & Status Lahan"
                                            class="bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] p-1.5 rounded-lg transition shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('home', ['upt_id' => $upt->id]) }}" target="_blank"
                                            title="Lihat Titik Koordinat di WebGIS"
                                            class="bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#2C3B4D] p-1.5 rounded-lg border border-[#C9C1B1]/60 transition">
                                            <svg class="w-3.5 h-3.5 text-[#1B2632]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-[#C9C1B1]">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search');
            const regencySelect = document.getElementById('regency_id');
            const statusSelect = document.getElementById('status');
            const clearSearchBtn = document.getElementById('btn-clear-search');
            const resetFiltersBtn = document.getElementById('btn-reset-filters');
            const searchSpinner = document.getElementById('search-spinner');
            const tableCard = document.getElementById('upt-table-card');

            let debounceTimer = null;
            let currentAbortController = null;

            // Fungsi Utama: Live Filter via AJAX
            function performLiveFilter(page = 1) {
                const searchVal = (searchInput ? searchInput.value : '').trim();
                const regencyVal = regencySelect ? regencySelect.value : 'all';
                const statusVal = statusSelect ? statusSelect.value : 'all';

                // Tampilkan/sembunyikan tombol clear search
                if (clearSearchBtn) {
                    clearSearchBtn.classList.toggle('hidden', searchVal.length === 0);
                }

                // Susun parameter URL
                const url = new URL('{{ route("admin.upt.index") }}', window.location.origin);
                if (searchVal) url.searchParams.set('search', searchVal);
                if (regencyVal && regencyVal !== 'all') url.searchParams.set('regency_id', regencyVal);
                if (statusVal && statusVal !== 'all') url.searchParams.set('status', statusVal);
                if (page && page > 1) url.searchParams.set('page', page);

                // Batalkan request sebelumnya jika masih berjalan
                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                // Tampilkan loading spinner & redupkan tabel secara halus
                if (searchSpinner) searchSpinner.classList.remove('hidden');
                if (clearSearchBtn && !clearSearchBtn.classList.contains('hidden')) clearSearchBtn.classList.add('hidden');
                if (tableCard) tableCard.classList.add('opacity-50', 'pointer-events-none');

                fetch(url.toString(), {
                    signal: currentAbortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal memuat data');
                        return response.text();
                    })
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // 1. Perbarui Isi Tabel Card (termasuk pagination & counter)
                        const newTableCard = doc.getElementById('upt-table-card');
                        if (newTableCard && tableCard) {
                            tableCard.innerHTML = newTableCard.innerHTML;
                        }

                        // 2. Perbarui Header Quick Cards (tombol reset kabupaten)
                        const newQuickCardsHeader = doc.getElementById('quick-cards-header-actions');
                        const oldQuickCardsHeader = document.getElementById('quick-cards-header-actions');
                        if (newQuickCardsHeader && oldQuickCardsHeader) {
                            oldQuickCardsHeader.innerHTML = newQuickCardsHeader.innerHTML;
                        }

                        // 3. Perbarui Grid 9 Kartu Quick Filter (status highlight aktif)
                        const newCardsGrid = doc.getElementById('regency-cards-grid');
                        const oldCardsGrid = document.getElementById('regency-cards-grid');
                        if (newCardsGrid && oldCardsGrid) {
                            oldCardsGrid.innerHTML = newCardsGrid.innerHTML;
                        }

                        // 4. Sinkronisasi Browser History URL
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            console.error('Error saat live filter UPT:', err);
                        }
                    })
                    .finally(() => {
                        if (searchSpinner) searchSpinner.classList.add('hidden');
                        if (clearSearchBtn && (searchInput ? searchInput.value.trim().length > 0 : false)) {
                            clearSearchBtn.classList.remove('hidden');
                        }
                        if (tableCard) tableCard.classList.remove('opacity-50', 'pointer-events-none');
                    });
            }

            // 1. Live Input Search dengan Debounce 300ms
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        performLiveFilter(1);
                    }, 300);
                });

                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(debounceTimer);
                        performLiveFilter(1);
                    }
                });
            }

            // 2. Tombol Clear Search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function () {
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                    clearSearchBtn.classList.add('hidden');
                    performLiveFilter(1);
                });
            }

            // 3. Live Change Kabupaten
            if (regencySelect) {
                regencySelect.addEventListener('change', function () {
                    performLiveFilter(1);
                });
            }

            // 4. Live Change Status Lahan
            if (statusSelect) {
                statusSelect.addEventListener('change', function () {
                    performLiveFilter(1);
                });
            }

            // 5. Tombol Reset Semua Filter
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (regencySelect) regencySelect.value = 'all';
                    if (statusSelect) statusSelect.value = 'all';
                    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
                    performLiveFilter(1);
                });
            }

            // 6. Event Delegation untuk Klik Kartu Kabupaten, Hapus Filter Kab, dan Link Pagination
            document.addEventListener('click', function (e) {
                // Klik Kartu Kabupaten di atas
                const regencyCard = e.target.closest('[data-regency-card-id]');
                if (regencyCard) {
                    e.preventDefault();
                    const regId = regencyCard.getAttribute('data-regency-card-id');
                    const currentVal = regencySelect ? regencySelect.value : 'all';
                    const targetVal = (currentVal == regId) ? 'all' : regId;
                    if (regencySelect) regencySelect.value = targetVal;
                    performLiveFilter(1);
                    return;
                }

                // Klik Hapus Filter Kabupaten
                const clearRegBtn = e.target.closest('#btn-clear-regency-filter');
                if (clearRegBtn) {
                    e.preventDefault();
                    if (regencySelect) regencySelect.value = 'all';
                    performLiveFilter(1);
                    return;
                }

                // Klik link Pagination di dalam tabel card
                const pageLink = e.target.closest('#upt-table-card nav a, #upt-table-card .pagination a');
                if (pageLink && pageLink.href) {
                    e.preventDefault();
                    try {
                        const linkUrl = new URL(pageLink.href);
                        const pageNum = linkUrl.searchParams.get('page') || 1;
                        performLiveFilter(pageNum);
                    } catch (err) {
                        window.location.href = pageLink.href;
                    }
                }
            });
        });
    </script>
@endsection