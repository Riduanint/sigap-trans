@extends('layouts.admin')

@section('title', 'Wilayah Peta WebGIS')
@section('header_title', 'Pengaturan Wilayah Peta WebGIS')
@section('header_subtitle', 'Kontrol visibilitas 9 kabupaten se-Kalimantan Selatan pada antarmuka publik WebGIS interaktif beserta kustomisasi warna batas wilayah spasial')

@section('content')
<div class="space-y-6">

    <!-- 1. NOTIFIKASI SUKSES / ERROR -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between shadow-xs animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="font-bold text-xs text-emerald-950">Berhasil Disimpan</div>
                    <div class="text-xs text-emerald-800">{{ session('success') }}</div>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 text-xs font-bold px-2 py-1">✕</button>
        </div>
    @endif

    <!-- 2. KPI SUMMARY CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kabupaten -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-[#0B1849] shrink-0 font-black text-lg border border-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Wilayah</span>
                <div class="text-2xl font-black text-[#0B1849] mt-0.5" id="stat-total">{{ $stats['total_regencies'] }} Kab</div>
                <span class="text-[10px] text-slate-500">Provinsi Kalimantan Selatan</span>
            </div>
        </div>

        <!-- Wilayah Aktif Ditampilkan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-[#124D1C] shrink-0 font-black text-lg border border-emerald-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Aktif di Peta WebGIS</span>
                <div class="text-2xl font-black text-emerald-800 mt-0.5 flex items-center gap-2">
                    <span id="stat-visible">{{ $stats['visible_regencies'] }}</span>
                    <span class="text-xs font-bold text-slate-400">/ 9 Wilayah</span>
                </div>
                <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Poligon & UPT Terlihat
                </span>
            </div>
        </div>

        <!-- Wilayah Disembunyikan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 font-black text-lg border border-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Disembunyikan</span>
                <div class="text-2xl font-black text-slate-700 mt-0.5" id="stat-hidden">{{ $stats['hidden_regencies'] }} Kab</div>
                <span class="text-[10px] text-slate-500">Dikecualikan dari Peta</span>
            </div>
        </div>

        <!-- UPT Tampil di Peta -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0 font-black text-lg border border-amber-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Titik UPT Ditampilkan</span>
                <div class="text-2xl font-black text-amber-800 mt-0.5 flex items-center gap-1.5">
                    <span id="stat-visible-upts">{{ $stats['visible_upts'] }}</span>
                    <span class="text-xs font-bold text-slate-400">/ 124 UPT</span>
                </div>
                <span class="text-[10px] text-amber-600 font-semibold">Tampil pada Peta Publik</span>
            </div>
        </div>
    </div>

    <!-- 3. BAR AKSI KONTROL CEPAT -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Pencarian Instan Wilayah -->
        <div class="relative flex-1 max-w-md">
            <input type="text" id="regency-search" placeholder="Cari nama kabupaten atau kode romawi..."
                   class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <!-- Tombol Aksi Masal & Pratinjau Peta -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Tampilkan Semua -->
            <form action="{{ route('admin.regencies.bulk-visibility') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="show_all">
                <button type="submit"
                        class="px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/80 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Tampilkan Semua (9)</span>
                </button>
            </form>

            <!-- Sembunyikan Semua -->
            <form action="{{ route('admin.regencies.bulk-visibility') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyembunyikan semua wilayah dari Peta WebGIS?');">
                @csrf
                <input type="hidden" name="action" value="hide_all">
                <button type="submit"
                        class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                    <span>Sembunyikan Semua</span>
                </button>
            </form>

            <!-- Link Buka Peta WebGIS -->
            <a href="{{ route('home') }}" target="_blank"
               class="px-4 py-2 text-xs font-bold rounded-xl bg-[#0B1849] text-white hover:bg-[#0B1849]/90 border border-slate-800 transition flex items-center gap-1.5 shadow-md">
                <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                <span>Buka Peta WebGIS</span>
            </a>
        </div>
    </div>

    <!-- 4. GRID 9 KARTU KABUPATEN -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="regencies-grid">
        @foreach($regencies as $reg)
            @php
                $cleanCount = $reg->uptLocations->where('issue_status', 'clean')->count();
                $warningCount = $reg->uptLocations->where('issue_status', 'warning')->count();
                $criticalCount = $reg->uptLocations->where('issue_status', 'critical')->count();
                $totalKk = $reg->uptLocations->sum('placement_kk');
                $totalPop = $reg->uptLocations->sum('placement_population');
                $color = $reg->map_color ?: '#8b5cf6';
            @endphp

            <div class="regency-card bg-white rounded-2xl border transition duration-200 overflow-hidden shadow-xs hover:shadow-md flex flex-col justify-between {{ $reg->is_visible ? 'border-slate-200/90' : 'border-slate-200/50 bg-slate-50/50 opacity-80' }}"
                 data-id="{{ $reg->id }}"
                 data-name="{{ strtolower($reg->name) }}"
                 data-roman="{{ strtolower($reg->code_roman) }}"
                 data-visible="{{ $reg->is_visible ? '1' : '0' }}"
                 data-upt-count="{{ $reg->upt_locations_count }}">

                <!-- Header Warna Wilayah -->
                <div>
                    <!-- Aksen Garis Atas Sesuai Warna Batas Spasial -->
                    <div class="h-2.5 w-full transition-colors duration-300" id="card-stripe-{{ $reg->id }}" style="background-color: {{ $color }};"></div>

                    <div class="p-5">
                        <!-- Baris Atas: Kode Romawi, Nama, & Toggle Switch -->
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider text-white shadow-xs"
                                          id="card-badge-{{ $reg->id }}"
                                          style="background-color: {{ $color }};">
                                        Kab. {{ $reg->code_roman }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-mono">ID: #{{ $reg->id }}</span>
                                </div>
                                <h3 class="text-base font-extrabold text-[#0B1849] mt-1.5 flex items-center gap-1.5">
                                    Kabupaten {{ $reg->name }}
                                </h3>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Ibukota: <strong>{{ $reg->capital_city }}</strong></span>
                                </div>
                            </div>

                            <!-- TOGGLE SWITCH VISIBILITAS -->
                            <div class="flex flex-col items-end">
                                <button type="button"
                                        class="toggle-visibility-btn relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#124D1C] focus:ring-offset-2 {{ $reg->is_visible ? 'bg-[#124D1C]' : 'bg-slate-300' }}"
                                        role="switch"
                                        aria-checked="{{ $reg->is_visible ? 'true' : 'false' }}"
                                        data-id="{{ $reg->id }}"
                                        title="{{ $reg->is_visible ? 'Klik untuk sembunyikan dari WebGIS' : 'Klik untuk tampilkan di WebGIS' }}">
                                    <span class="sr-only">Status Tampil</span>
                                    <span aria-hidden="true"
                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $reg->is_visible ? 'translate-x-5' : 'translate-x-0' }}">
                                    </span>
                                </button>
                                <span class="status-label text-[10px] font-bold mt-1 {{ $reg->is_visible ? 'text-emerald-700' : 'text-slate-400' }}" id="status-label-{{ $reg->id }}">
                                    {{ $reg->is_visible ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                        </div>

                        <!-- Info Metrik Spasial & Demografi -->
                        <div class="grid grid-cols-3 gap-2 my-4 p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400">Total UPT</div>
                                <div class="text-sm font-black text-[#0B1849] mt-0.5">{{ $reg->upt_locations_count }} UPT</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400">Penempatan KK</div>
                                <div class="text-sm font-black text-[#124D1C] mt-0.5">{{ number_format($totalKk, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400">Total Jiwa</div>
                                <div class="text-sm font-black text-[#0B1849] mt-0.5">{{ number_format($totalPop, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Status Agraria 3 Warna UPT -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 font-medium">Distribusi Status Legalitas:</span>
                                <span class="font-mono text-slate-400 text-[10px]">{{ $reg->upt_locations_count }} Titik</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1.5 text-center text-[10px] font-bold">
                                <div class="px-2 py-1 rounded bg-emerald-100/70 text-emerald-800 border border-emerald-200/60" title="{{ $cleanCount }} UPT Clean & Clear">
                                    🟢 {{ $cleanCount }} Clean
                                </div>
                                <div class="px-2 py-1 rounded bg-amber-100/70 text-amber-800 border border-amber-200/60" title="{{ $warningCount }} UPT Monitoring Berkala">
                                    🟡 {{ $warningCount }} Warning
                                </div>
                                <div class="px-2 py-1 rounded bg-rose-100/70 text-rose-800 border border-rose-200/60" title="{{ $criticalCount }} UPT Prioritas Sengketa">
                                    🔴 {{ $criticalCount }} Sengketa
                                </div>
                            </div>
                        </div>

                        <!-- Pengaturan Warna Batas Poligon Spasial -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label for="color-picker-{{ $reg->id }}" class="text-[11px] font-bold text-slate-600 block">
                                    Warna Poligon:
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" id="color-picker-{{ $reg->id }}"
                                           value="{{ $color }}"
                                           data-id="{{ $reg->id }}"
                                           class="color-picker-input w-6 h-6 rounded cursor-pointer border border-slate-300 p-0"
                                           title="Pilih warna batas poligon peta untuk {{ $reg->name }}">
                                    <span class="font-mono text-xs text-slate-600 font-semibold" id="color-hex-{{ $reg->id }}">{{ $color }}</span>
                                </div>
                            </div>
                            <button type="button"
                                    class="save-color-btn text-[10px] font-bold text-slate-500 hover:text-[#124D1C] bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded transition"
                                    data-id="{{ $reg->id }}">
                                Simpan Warna
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Card: Pratinjau WebGIS & Koordinat -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div class="text-[10px] text-slate-400 font-mono">
                        {{ number_format($reg->latitude, 4) }}, {{ number_format($reg->longitude, 4) }}
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('home') }}?regency={{ $reg->id }}" target="_blank"
                           class="inline-flex items-center gap-1 text-[11px] font-bold text-[#124D1C] hover:text-[#0B1849] bg-white px-2.5 py-1 rounded-lg border border-slate-200 hover:border-slate-300 transition shadow-2xs">
                            <span>Buka di Peta</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

<!-- TOAST ALERT CONTAINER -->
<div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm"></div>

<!-- JAVASCRIPT LOGIC INTERAKTIF -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // 1. Toast Notification Helper
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const bgClass = type === 'success' ? 'bg-[#0B1849] border-emerald-500' : 'bg-rose-900 border-rose-500';
        toast.className = `${bgClass} text-white px-4 py-3 rounded-xl border-l-4 shadow-xl text-xs flex items-center justify-between gap-3 transition transform translate-y-2 opacity-0 duration-200`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <span>${type === 'success' ? '✅' : '⚠️'}</span>
                <span class="font-medium">${message}</span>
            </div>
            <button class="text-white/60 hover:text-white font-bold ml-2">✕</button>
        `;

        toast.querySelector('button').onclick = () => toast.remove();
        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);

        // Auto dismiss after 3.5s
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // 2. Update KPI Stats Dom
    function recalculateStats() {
        const cards = document.querySelectorAll('.regency-card');
        let visibleCount = 0;
        let hiddenCount = 0;
        let visibleUpts = 0;

        cards.forEach(card => {
            const isVis = card.getAttribute('data-visible') === '1';
            const upts = parseInt(card.getAttribute('data-upt-count') || '0', 10);
            if (isVis) {
                visibleCount++;
                visibleUpts += upts;
            } else {
                hiddenCount++;
            }
        });

        const statVis = document.getElementById('stat-visible');
        const statHid = document.getElementById('stat-hidden');
        const statVisUpts = document.getElementById('stat-visible-upts');

        if (statVis) statVis.textContent = visibleCount;
        if (statHid) statHid.textContent = `${hiddenCount} Kab`;
        if (statVisUpts) statVisUpts.textContent = visibleUpts;
    }

    // 3. Toggle Visibility Handler
    document.querySelectorAll('.toggle-visibility-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const id = button.getAttribute('data-id');
            const card = document.querySelector(`.regency-card[data-id="${id}"]`);
            const knob = button.querySelector('span:not(.sr-only)');
            const label = document.getElementById(`status-label-${id}`);

            // Current state
            const currentChecked = button.getAttribute('aria-checked') === 'true';
            const nextChecked = !currentChecked;

            // Optimistic UI update
            button.setAttribute('aria-checked', nextChecked ? 'true' : 'false');
            button.className = button.className.replace(nextChecked ? 'bg-slate-300' : 'bg-[#124D1C]', nextChecked ? 'bg-[#124D1C]' : 'bg-slate-300');
            if (knob) {
                knob.className = knob.className.replace(nextChecked ? 'translate-x-0' : 'translate-x-5', nextChecked ? 'translate-x-5' : 'translate-x-0');
            }
            if (label) {
                label.textContent = nextChecked ? 'Ditampilkan' : 'Disembunyikan';
                label.className = `status-label text-[10px] font-bold mt-1 ${nextChecked ? 'text-emerald-700' : 'text-slate-400'}`;
            }
            if (card) {
                card.setAttribute('data-visible', nextChecked ? '1' : '0');
                if (nextChecked) {
                    card.classList.remove('border-slate-200/50', 'bg-slate-50/50', 'opacity-80');
                    card.classList.add('border-slate-200/90');
                } else {
                    card.classList.add('border-slate-200/50', 'bg-slate-50/50', 'opacity-80');
                    card.classList.remove('border-slate-200/90');
                }
            }
            recalculateStats();

            // Send AJAX PATCH
            try {
                const response = await fetch(`/admin/regencies/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _token: csrfToken
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.message || `HTTP ${response.status}: Gagal memproses permintaan`);
                }

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    throw new Error(result.message || 'Gagal mengubah visibilitas');
                }
            } catch (err) {
                console.error('Toggle visibility error:', err);
                showToast(err.message || 'Gagal mengubah visibilitas wilayah. Silakan coba lagi.', 'error');
                // Revert UI on error
                button.setAttribute('aria-checked', currentChecked ? 'true' : 'false');
                button.className = button.className.replace(nextChecked ? 'bg-[#124D1C]' : 'bg-slate-300', currentChecked ? 'bg-[#124D1C]' : 'bg-slate-300');
                if (knob) {
                    knob.className = knob.className.replace(nextChecked ? 'translate-x-5' : 'translate-x-0', currentChecked ? 'translate-x-5' : 'translate-x-0');
                }
                if (card) card.setAttribute('data-visible', currentChecked ? '1' : '0');
                recalculateStats();
            }
        });
    });

    // 4. Color Picker Live Update & Save
    document.querySelectorAll('.color-picker-input').forEach(picker => {
        picker.addEventListener('input', (e) => {
            const id = picker.getAttribute('data-id');
            const color = e.target.value;
            const hexText = document.getElementById(`color-hex-${id}`);
            const stripe = document.getElementById(`card-stripe-${id}`);
            const badge = document.getElementById(`card-badge-${id}`);

            if (hexText) hexText.textContent = color;
            if (stripe) stripe.style.backgroundColor = color;
            if (badge) badge.style.backgroundColor = color;
        });
    });

    document.querySelectorAll('.save-color-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const picker = document.getElementById(`color-picker-${id}`);
            const color = picker ? picker.value : null;
            if (!color) return;

            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            try {
                const response = await fetch(`/admin/regencies/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        _token: csrfToken,
                        map_color: color 
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.message || `HTTP ${response.status}: Gagal menyimpan warna`);
                }

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    throw new Error(result.message || 'Gagal menyimpan warna');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal memperbarui warna wilayah spasial.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Simpan Warna';
            }
        });
    });

    // 5. Client-Side Live Search Filter
    const searchInput = document.getElementById('regency-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.regency-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const roman = card.getAttribute('data-roman') || '';
                if (name.includes(term) || roman.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
