@extends('layouts.admin')

@section('title', 'Kelola Penempatan Awal - SIGAP-TRANS Kalsel')

@section('content')
<div class="space-y-6">

    <!-- 1. HEADER HALAMAN -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#0B1849] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Menu Kelola Data</span>
                <span>/</span>
                <span class="text-[#0B1849] font-bold">Penempatan Awal</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#124D1C]/10 text-[#124D1C] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                <span>Kelola Data Penempatan Awal Transmigrasi</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pencatatan dan pemutakhiran data KK penempatan, kependudukan jiwa, dan tahun penempatan awal di 124 UPT se-Kalimantan Selatan.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-auto">
            <a href="{{ route('admin.placements.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold text-xs hover:bg-emerald-100 transition shadow-xs">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Ekspor CSV Data KK</span>
            </a>
            <a href="{{ route('admin.upt.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                <span>Master 124 UPT</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <!-- 2. KARTU METRIK KPI RINGKASAN (SESUAI CONTOH GAMBAR) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kartu 1: Total KK Penempatan Awal (Forest Green) -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#124D1C] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">PENEMPATAN AWAL</span>
                <div class="w-8 h-8 rounded-lg bg-[#124D1C]/10 text-[#124D1C] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#124D1C] tracking-tight tabular-nums" id="kpi-total-kk">
                    {{ number_format($stats['total_kk'], 0, ',', '.') }} <span class="text-base font-bold text-slate-600">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Total Kependudukan: <strong class="text-slate-800 tabular-nums" id="kpi-total-pop">{{ number_format($stats['total_population'], 0, ',', '.') }}</strong> Jiwa
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Registri Warga (Opsi C)</span>
                <span class="font-extrabold text-[#0B1849] tabular-nums">{{ number_format($stats['total_nominal_kk'] ?? 0, 0, ',', '.') }} KK Tercatat</span>
            </div>
        </div>

        <!-- Kartu 2: Cakupan UPT Terdata -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#0B1849] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">CAKUPAN LOKASI UPT</span>
                <div class="w-8 h-8 rounded-lg bg-[#0B1849]/10 text-[#0B1849] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#0B1849] tracking-tight tabular-nums">
                    {{ $stats['upt_count'] }} <span class="text-base font-bold text-slate-500">/ {{ $stats['total_upt'] }} UPT</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Status: <strong class="text-emerald-700">100% Terdata Lengkap</strong>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Sebaran Wilayah</span>
                <span class="font-bold text-slate-700">9 Kabupaten se-Kalsel</span>
            </div>
        </div>

        <!-- Kartu 3: Tautan Serah Terima Pemda -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#E4B028] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">SERAH TERIMA PEMDA</span>
                <div class="w-8 h-8 rounded-lg bg-[#E4B028]/20 text-[#0B1849] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#0B1849] tracking-tight tabular-nums">
                    64.957 <span class="text-base font-bold text-slate-600">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Pertumbuhan Alami: <strong class="text-emerald-700 font-bold">+1.241 KK (+2,0%)</strong>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Bandingkan Data</span>
                <a href="{{ route('admin.handovers.index') }}" class="font-bold text-[#0B1849] hover:underline flex items-center gap-1">
                    <span>Lihat Serah Terima</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 3. FILTER DAN PENCARIAN -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.placements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Input Cari -->
            <div class="lg:col-span-4">
                <label for="search" class="block text-xs font-bold text-slate-700 mb-1">Cari Nama UPT / Desa / No. UPT</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Ketik nama UPT, desa, atau nomor..."
                           class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Kabupaten -->
            <div class="lg:col-span-3">
                <label for="regency_id" class="block text-xs font-bold text-slate-700 mb-1">Kabupaten</label>
                <select name="regency_id" id="regency_id" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
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
                <label for="business_pattern" class="block text-xs font-bold text-slate-700 mb-1">Pola Usaha</label>
                <select name="business_pattern" id="business_pattern" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
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
                <label for="placement_year" class="block text-xs font-bold text-slate-700 mb-1">Tahun Penempatan</label>
                <input type="text" name="placement_year" id="placement_year" value="{{ request('placement_year') }}" placeholder="Misal: 1982"
                       class="w-full text-xs py-2 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="lg:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 bg-[#0B1849] hover:bg-[#124D1C] text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1">
                    <span>Filter</span>
                </button>
                @if(request()->anyFilled(['search', 'regency_id', 'business_pattern', 'placement_year']))
                    <a href="{{ route('admin.placements.index') }}" title="Reset Filter" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. TABEL DATA PENEMPATAN AWAL -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" id="placement-table-card">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tabel Data KK Penempatan Awal</span>
                <span class="bg-[#124D1C]/10 text-[#124D1C] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full">
                    {{ $uptLocations->total() }} Lokasi
                </span>
            </div>
            <div class="text-xs text-slate-500">
                Klik tombol <strong class="text-[#0B1849]">"Kelola KK"</strong> pada baris tabel untuk mengubah atau menyimpan data KK.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0B1849] text-[#EBEDE3] font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-center">No. UPT</th>
                        <th class="py-3.5 px-4">Kabupaten</th>
                        <th class="py-3.5 px-4">Nama UPT Asal</th>
                        <th class="py-3.5 px-4">Desa Definitif</th>
                        <th class="py-3.5 px-4 text-center">Pola</th>
                        <th class="py-3.5 px-4 text-center">Tahun Masuk</th>
                        <th class="py-3.5 px-4 text-center bg-emerald-950/60 text-emerald-200">KK Penempatan</th>
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
                        <tr class="hover:bg-slate-50/80 transition" id="row-upt-{{ $upt->id }}">
                            <td class="py-3 px-4 font-mono font-extrabold text-[#0B1849] text-center">
                                UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-700">
                                {{ $upt->regency?->name }}
                            </td>
                            <td class="py-3 px-4 font-extrabold text-slate-900">
                                {{ $upt->upt_name }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $upt->current_village_name }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded text-[10px]">
                                    {{ $upt->business_pattern }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700" id="cell-year-{{ $upt->id }}">
                                {{ $upt->placement_year ?: '-' }}
                            </td>
                            <!-- Kolom KK Penempatan Highlight -->
                            <td class="py-3 px-4 text-center font-black text-emerald-800 text-sm tabular-nums bg-emerald-50/40" id="cell-kk-{{ $upt->id }}">
                                {{ number_format($upt->placement_kk, 0, ',', '.') }} <span class="text-[10px] font-normal text-slate-500">KK</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800 tabular-nums" id="cell-pop-{{ $upt->id }}">
                                {{ number_format($upt->placement_population, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-600 tabular-nums" id="cell-ratio-{{ $upt->id }}">
                                {{ number_format($ratio, 2, ',', '.') }}
                            </td>
                            <!-- Kolom Indikator Registri Nominal Warga (Opsi C) -->
                            <td class="py-3 px-4 text-center">
                                @if($hasCards)
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-extrabold text-[11px] transition shadow-2xs cursor-pointer"
                                            id="badge-nominal-{{ $upt->id }}"
                                            title="Buka Buku Registri Warga">
                                        <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>{{ $upt->placement_family_cards_count }} KK Terdata</span>
                                    </button>
                                @else
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-medium text-[11px] transition cursor-pointer"
                                            id="badge-nominal-{{ $upt->id }}"
                                            title="Klik untuk input/import nominal warga">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>0 KK (Buka Registri)</span>
                                    </button>
                                @endif
                            </td>
                            <!-- Kolom Tombol Aksi -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#0B1849] hover:bg-[#14266c] text-[#EBEDE3] font-bold text-xs transition shadow-xs cursor-pointer"
                                            title="Buka Buku Registri Warga Transmigran">
                                        <svg class="w-3.5 h-3.5 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <span>Registri Warga</span>
                                    </button>
                                    <button type="button"
                                            onclick="openEditKkModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', {{ $upt->placement_kk }}, {{ $upt->placement_population }}, '{{ addslashes($upt->placement_year) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition cursor-pointer"
                                            title="Ubah Angka Rekapitulasi Cepat">
                                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Edit Rekap</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-slate-400">
                                Tidak ada data UPT yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-slate-500 font-medium">
                Showing <strong class="text-slate-800">{{ $uptLocations->firstItem() ?? 0 }}</strong> to <strong class="text-slate-800">{{ $uptLocations->lastItem() ?? 0 }}</strong> of <strong class="text-[#0B1849]">{{ $uptLocations->total() }}</strong> results
            </div>

            <div class="inline-flex items-center bg-white text-slate-700 px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-xs border border-slate-200 self-center sm:self-auto" style="background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; color: #334155 !important;">
                {{ $uptLocations->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>

<!-- 5. MODAL INTERAKTIF: INPUT / EDIT DATA KK PENEMPATAN AWAL -->
<div id="modal-edit-kk" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden p-4 transition-all">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <!-- Header Modal -->
        <div class="bg-[#0B1849] text-white p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black">Simpan Data KK Penempatan Awal</h3>
                    <p class="text-[11px] text-slate-300" id="modal-upt-title">UPT-001</p>
                </div>
            </div>
            <button type="button" onclick="closeEditKkModal()" class="text-slate-300 hover:text-white p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Form Modal -->
        <form id="form-save-kk" onsubmit="submitEditKk(event)" class="p-5 space-y-4">
            <input type="hidden" id="modal-upt-id" name="upt_id">

            <!-- Jumlah KK Penempatan -->
            <div>
                <label for="modal-placement-kk" class="block text-xs font-bold text-slate-700 mb-1">
                    Jumlah Kepala Keluarga (KK) Penempatan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-placement-kk" name="placement_kk" min="0" required
                           oninput="recalculateRatio()"
                           class="w-full text-sm font-bold text-emerald-900 py-2.5 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">KK</span>
                </div>
            </div>

            <!-- Jumlah Jiwa / Kependudukan -->
            <div>
                <label for="modal-placement-pop" class="block text-xs font-bold text-slate-700 mb-1">
                    Total Kependudukan (Jiwa) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="modal-placement-pop" name="placement_population" min="0" required
                           oninput="recalculateRatio()"
                           class="w-full text-sm font-bold text-slate-800 py-2.5 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs tabular-nums">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">Jiwa</span>
                </div>
            </div>

            <!-- Tahun Penempatan -->
            <div>
                <label for="modal-placement-year" class="block text-xs font-bold text-slate-700 mb-1">
                    Tahun Penempatan Awal <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="modal-placement-year" name="placement_year" required
                       placeholder="Misal: 1982 atau 1982/1983"
                       class="w-full text-xs font-bold text-slate-800 py-2.5 px-3 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
            </div>

            <!-- Kalkulasi Estimasi Rasio -->
            <div class="bg-emerald-50/70 border border-emerald-200 p-3 rounded-xl flex items-center justify-between text-xs">
                <span class="text-emerald-800 font-medium">Estimasi Rata-rata:</span>
                <span class="font-extrabold text-emerald-950 tabular-nums" id="modal-ratio-preview">0,00 Jiwa/KK</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditKkModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-modal"
                        class="px-5 py-2 rounded-xl bg-[#124D1C] hover:bg-[#0E3B15] text-white text-xs font-bold transition shadow-md flex items-center gap-2">
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

@include('admin.family_cards._registry_drawer')
@endsection
