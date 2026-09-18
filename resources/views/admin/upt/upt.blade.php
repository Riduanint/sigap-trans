@extends('layouts.admin')

@section('title', 'Master Data 124 UPT Transmigrasi')
@section('header_title', 'Master Data 124 Unit Pemukiman Transmigrasi (UPT)')
@section('header_subtitle', 'Pengelolaan basis data induk historis dan status agraria di 9 kabupaten Kalimantan Selatan')

@section('content')
<div class="space-y-6">

    <!-- 1. REKAPITULASI SEBARAN DI 9 KABUPATEN (INTERACTIVE QUICK-FILTER CARDS) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#E4B028]"></span>
                    <h3 class="font-black text-[#0B1849] text-base">
                        Rekapitulasi Sebaran UPT di 9 Kabupaten
                    </h3>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Distribusi jumlah lokasi, penempatan warga, dan serah terima aset ke Pemerintah Daerah. Klik kartu kabupaten untuk memfilter tabel langsung.
                </p>
            </div>
            <div id="quick-cards-header-actions" class="flex items-center gap-2 shrink-0">
                @if(request()->filled('regency_id') && request('regency_id') !== 'all')
                    <a href="{{ route('admin.upt.index', request()->except(['regency_id', 'page'])) }}" 
                       id="btn-clear-regency-filter"
                       class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl border border-rose-200 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <span>✕ Hapus Filter Kabupaten</span>
                    </a>
                @endif
                <span class="text-xs font-extrabold text-[#124D1C] bg-[#124D1C]/10 px-3 py-1.5 rounded-xl border border-[#124D1C]/20">
                    Total 124 Lokasi Definitif
                </span>
            </div>
        </div>

        <div id="regency-cards-grid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                   class="p-4 rounded-xl border transition group relative block cursor-pointer {{ $isSelected ? 'border-2 border-[#124D1C] bg-emerald-50/50 shadow-md ring-2 ring-[#124D1C]/20' : 'border-slate-200/80 bg-slate-50/60 hover:bg-white hover:border-slate-300 hover:shadow-md' }}"
                   title="{{ $isSelected ? 'Klik untuk membatalkan filter' : 'Klik untuk memfilter tabel ke Kabupaten ' . $reg->name }}">
                    
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-extrabold uppercase px-2 py-0.5 rounded transition {{ $isSelected ? 'text-white bg-[#124D1C]' : 'text-[#0B1849] bg-[#0B1849]/10 group-hover:bg-[#0B1849] group-hover:text-white' }}">
                                {{ $reg->code_roman }}. {{ $reg->name }}
                            </span>
                            @if($isSelected)
                                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-200/80 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    ✓ Aktif
                                </span>
                            @endif
                        </div>
                        <span class="text-xs font-black px-2 py-0.5 rounded-full transition {{ $isSelected ? 'text-emerald-900 bg-emerald-200' : 'text-[#124D1C] bg-emerald-100/70 group-hover:bg-emerald-200' }}">
                            {{ $reg->upt_locations_count }} UPT
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Penempatan</span>
                            <span class="font-bold text-slate-800 tabular-nums">{{ number_format($placementKk, 0, ',', '.') }} KK</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Serah Terima</span>
                            <span class="font-bold text-slate-800 tabular-nums">{{ number_format($handoverKk, 0, ',', '.') }} KK</span>
                        </div>
                    </div>

                    <!-- Indikator Status di Kabupaten -->
                    <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                        <span class="text-emerald-700 font-bold">🟢 {{ $cleanCount }} Clean</span>
                        <span class="text-amber-600 font-bold">🟡 {{ $warningCount }} Warning</span>
                        <span class="text-rose-600 font-bold">🔴 {{ $criticalCount }} Kritis</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 2. BAR PENCARIAN & FILTER MULTI-KRITERIA (LIVE AUTO-UPDATE TANPA TOMBOL SARING) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <form id="upt-filter-form" method="GET" action="{{ route('admin.upt.index') }}" onsubmit="return false;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <input type="hidden" name="per_page" id="per_page_input" value="{{ request('per_page', 15) }}">
            
            <!-- Pencarian Teks (Live Debounced) -->
            <div class="sm:col-span-2 lg:col-span-5">
                <label for="search" class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Cari Nama UPT / Desa</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="Ketik nama UPT, desa, atau nomor UPT..." 
                           autocomplete="off"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 pl-9 pr-9 text-xs focus:ring-2 focus:ring-[#124D1C] focus:border-transparent transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    
                    <!-- Tombol Hapus Teks Pencarian (X) -->
                    <button type="button" id="btn-clear-search" 
                            class="{{ request('search') ? '' : 'hidden' }} absolute right-3 top-2.5 text-slate-400 hover:text-slate-700 transition cursor-pointer" 
                            title="Hapus pencarian">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <!-- Indikator Spinner Memuat Data -->
                    <div id="search-spinner" class="hidden absolute right-3 top-2.5">
                        <svg class="animate-spin w-3.5 h-3.5 text-[#124D1C]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filter Kabupaten (Auto-update on change) -->
            <div class="sm:col-span-1 lg:col-span-3">
                <label for="regency_id" class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Kabupaten</label>
                <div class="relative">
                    <select name="regency_id" id="regency_id" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 pr-8 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-[#124D1C] cursor-pointer appearance-none transition">
                        <option value="all">Semua 9 Kabupaten</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                                {{ $reg->code_roman }}. {{ $reg->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Status Agraria (Auto-update on change) -->
            <div class="sm:col-span-1 lg:col-span-3">
                <label for="status" class="text-[11px] font-bold text-slate-500 uppercase block mb-1">Status Lahan</label>
                <div class="relative">
                    <select name="status" id="status" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 pr-8 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-[#124D1C] cursor-pointer appearance-none transition">
                        <option value="all">Semua Status</option>
                        <option value="clean" {{ request('status') == 'clean' ? 'selected' : '' }}>🟢 Clean & Clear</option>
                        <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>🟡 Monitoring</option>
                        <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>🔴 Prioritas Kritis</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Tombol Reset Saja (Tombol Saring Dihilangkan Sesuai Arahan) -->
            <div class="sm:col-span-2 lg:col-span-1">
                <button type="button" id="btn-reset-filters" 
                        class="w-full bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 hover:text-slate-800 font-bold text-xs py-2 px-3 rounded-xl transition flex items-center justify-center gap-1.5 border border-slate-200/80 shadow-2xs h-[38px] cursor-pointer"
                        title="Kembalikan semua filter ke pengaturan awal">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Reset</span>
                </button>
            </div>

        </form>
    </div>

    <!-- 3. TABEL DATA 124 UPT (HEADER NAVY: #0B1849) -->
    <div id="upt-table-card" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-opacity duration-200">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-slate-600">Total Ditemukan:</span>
                    <span class="font-extrabold text-xs text-[#0B1849] bg-[#0B1849]/10 px-2.5 py-0.5 rounded-full">
                        {{ $uptLocations->total() }} dari 124 Lokasi
                    </span>
                </div>

                <!-- Pengatur Jumlah Baris yang Muncul (Input Bebas Murni) -->
                <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                    <label for="per_page_custom" class="text-xs font-bold text-slate-600 whitespace-nowrap">Tampilkan:</label>
                    <div class="inline-flex items-center bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl px-2.5 py-1.5 shadow-2xs focus-within:ring-2 focus-within:ring-[#124D1C] focus-within:border-transparent transition gap-1.5">
                        <input type="number" 
                               id="per_page_custom" 
                               min="1" 
                               max="124" 
                               value="{{ request('per_page', 15) }}" 
                               class="w-12 text-xs font-black text-[#0B1849] bg-transparent text-center focus:outline-none"
                               title="Ketik jumlah baris yang ingin ditampilkan (1 - 124)">
                        <span class="text-xs font-medium text-slate-500 select-none">baris</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs">
                <span class="text-emerald-700 font-bold">🟢 {{ $stats['clean'] }} Clean</span>
                <span class="text-amber-600 font-bold">🟡 {{ $stats['warning'] }} Warning</span>
                <span class="text-rose-600 font-bold">🔴 {{ $stats['critical'] }} Kritis</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0B1849] text-[#EBEDE3] font-bold uppercase text-[11px]">
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
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No UPT -->
                            <td class="py-3.5 px-4 font-mono font-extrabold text-[#0B1849]">
                                UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <!-- Kabupaten -->
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                {{ $upt->regency?->name }} ({{ $upt->regency?->code_roman }})
                            </td>
                            <!-- Nama UPT Asal -->
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                {{ $upt->upt_name }}
                            </td>
                            <!-- Desa Definitif -->
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                {{ $upt->current_village_name }}
                            </td>
                            <!-- Pola Usaha -->
                            <td class="py-3.5 px-4">
                                <span class="bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded text-[11px]">
                                    {{ $upt->business_pattern }}
                                </span>
                            </td>
                            <!-- Penempatan KK -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800 tabular-nums">
                                {{ number_format($upt->placement_kk) }}
                            </td>
                            <!-- Penyerahan KK -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800 tabular-nums">
                                {{ number_format($upt->handover_kk) }}
                            </td>
                            <!-- Status Lahan Badge -->
                            <td class="py-3.5 px-4 text-center">
                                @if($upt->issue_status === 'clean')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        🟢 Clean
                                    </span>
                                @elseif($upt->issue_status === 'warning')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                        🟡 Monitoring
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        🔴 Kritis
                                    </span>
                                @endif
                            </td>
                            <!-- E-Arsip BAST -->
                            <td class="py-3.5 px-4 text-center">
                                @if($upt->documents->count() > 0)
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        {{ $upt->documents->count() }} Dokumen
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium">Belum Diunggah</span>
                                @endif
                            </td>
                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.upt.edit', $upt->id) }}" 
                                       title="Edit Data & Status Lahan" 
                                       class="bg-[#124D1C] hover:bg-emerald-800 text-white p-1.5 rounded-lg transition shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <a href="{{ url('/?search=' . urlencode($upt->upt_name)) }}" target="_blank"
                                       title="Lihat di WebGIS" 
                                       class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-slate-400">
                                Tidak ada data UPT yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (Sesuai Referensi Gambar) -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const regencySelect = document.getElementById('regency_id');
    const statusSelect = document.getElementById('status');
    const perPageInput = document.getElementById('per_page_input');
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
        const perPageVal = perPageInput ? perPageInput.value : '15';

        // Tampilkan/sembunyikan tombol clear search
        if (clearSearchBtn) {
            clearSearchBtn.classList.toggle('hidden', searchVal.length === 0);
        }

        // Susun parameter URL
        const url = new URL('{{ route("admin.upt.index") }}', window.location.origin);
        if (searchVal) url.searchParams.set('search', searchVal);
        if (regencyVal && regencyVal !== 'all') url.searchParams.set('regency_id', regencyVal);
        if (statusVal && statusVal !== 'all') url.searchParams.set('status', statusVal);
        if (perPageVal && perPageVal !== '15') url.searchParams.set('per_page', perPageVal);
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
            const activeId = document.activeElement ? document.activeElement.id : null;
            const selStart = (activeId === 'per_page_custom') ? document.activeElement.selectionStart : null;
            const selEnd = (activeId === 'per_page_custom') ? document.activeElement.selectionEnd : null;

            const newTableCard = doc.getElementById('upt-table-card');
            if (newTableCard && tableCard) {
                tableCard.innerHTML = newTableCard.innerHTML;
            }

            if (activeId === 'per_page_custom') {
                const refreshedInput = document.getElementById('per_page_custom');
                if (refreshedInput) {
                    refreshedInput.focus();
                    if (selStart !== null && selEnd !== null) {
                        refreshedInput.setSelectionRange(selStart, selEnd);
                    }
                }
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
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performLiveFilter(1);
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                performLiveFilter(1);
            }
        });
    }

    // 2. Tombol Clear Search
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
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
        regencySelect.addEventListener('change', function() {
            performLiveFilter(1);
        });
    }

    // 4. Live Change Status Lahan
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            performLiveFilter(1);
        });
    }

    // 5. Tombol Reset Semua Filter
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (regencySelect) regencySelect.value = 'all';
            if (statusSelect) statusSelect.value = 'all';
            if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
            if (perPageInput) perPageInput.value = '15';
            performLiveFilter(1);
        });
    }

    // 6. Handler Khusus Input Bebas Jumlah Baris
    let perPageDebounce = null;

    function applyCustomPerPage(val) {
        let num = parseInt(val, 10);
        if (isNaN(num) || num < 1) num = 15;
        if (num > 124) num = 124;

        if (perPageInput) perPageInput.value = num;

        const customInput = document.getElementById('per_page_custom');
        if (customInput) customInput.value = num;

        performLiveFilter(1);
    }

    window.updatePerPage = function(val) {
        applyCustomPerPage(val);
    };

    // Event delegation untuk Input Bebas Per Page
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'per_page_custom') {
            clearTimeout(perPageDebounce);
            perPageDebounce = setTimeout(() => {
                applyCustomPerPage(e.target.value);
            }, 500);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'per_page_custom') {
            clearTimeout(perPageDebounce);
            applyCustomPerPage(e.target.value);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.target && e.target.id === 'per_page_custom' && e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(perPageDebounce);
            applyCustomPerPage(e.target.value);
        }
    });

    // 7. Event Delegation untuk Klik Kartu Kabupaten, Hapus Filter Kab, dan Link Pagination
    document.addEventListener('click', function(e) {
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
            } catch(err) {
                window.location.href = pageLink.href;
            }
        }
    });
});
</script>
@endsection
