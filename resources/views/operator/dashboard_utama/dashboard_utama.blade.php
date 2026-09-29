@extends('layouts.admin')

@section('title', 'Dasbor Operator Wilayah - ' . ($selectedRegency->name ?? 'Seluruh Kalsel'))
@section('header_title', 'Ruang Kendali Operator Wilayah ' . ($selectedRegency ? 'Kabupaten ' . $selectedRegency->name : 'Kalimantan Selatan'))
@section('header_subtitle', 'Pusat Pemutakhiran Data Lapangan & Pengajuan Berkas BAST' . ($selectedRegency ? ' Wilayah Kabupaten ' . $selectedRegency->name : ' Lintas 9 Kabupaten'))

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER IDENTITAS OPERATOR WILAYAH (MP072: ABYSSAL #1B2632 + ACCENT AMBER #FFB162) -->
    <div class="rounded-2xl bg-[#1B2632] p-6 text-[#EEE9DF] shadow-ambient border border-[#2C3B4D] relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-[#FFB162]/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FFB162]/20 border border-[#FFB162]/50 flex items-center justify-center text-[#FFB162] shrink-0 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#1B2632] bg-[#FFB162] px-2.5 py-0.5 rounded-full shadow-2xs">
                            Wewenang Lintas Wilayah
                        </span>
                        <span class="text-xs text-[#C9C1B1]">&bull;</span>
                        <span class="text-xs text-[#C9C1B1]">
                            Cakupan: <strong class="text-[#EEE9DF]">{{ $selectedRegency ? 'Kabupaten ' . $selectedRegency->name : '9 Kabupaten Kalimantan Selatan' }}</strong>
                        </span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-[#EEE9DF] mt-1">
                        {{ $selectedRegency ? 'Kabupaten ' . $selectedRegency->name : 'Seluruh Wilayah Kalsel (124 UPT)' }}
                    </h2>
                    <p class="text-xs text-[#C9C1B1] mt-0.5">
                        Operator Pelaksana: <span class="font-bold text-[#EEE9DF]">{{ $user->name }}</span> (NIP: {{ $user->nip ?? '-' }})
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('operator.upt.index') }}" 
                   class="bg-[#2C3B4D] hover:bg-[#3d5066] text-[#EEE9DF] font-extrabold text-xs px-4 py-2.5 rounded-xl transition border border-[#C9C1B1]/30 flex items-center gap-1.5 shadow-ambient-xs">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Tabel Inventarisasi UPT</span>
                </a>
                <a href="{{ route('operator.requests.create') }}" 
                   class="bg-[#FFB162] hover:bg-[#ffa347] text-[#1B2632] font-black text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 border border-[#FFB162]">
                    <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Ajukan Draf Usulan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. QUICK REGENCY SWITCHER BAR (MP072) -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25 flex items-center justify-center font-bold shrink-0">
                <svg class="w-5 h-5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
            </div>
            <div>
                <span class="text-xs font-black text-[#1B2632] uppercase tracking-wider block">Pilih Cakupan Wilayah Kabupaten:</span>
                <span class="text-[11px] text-[#2C3B4D]/70">Anda tidak terpaku pada satu wilayah saja — filter ke seluruh UPT se-Kalsel atau kabupaten spesifik:</span>
            </div>
        </div>
        <div class="w-full sm:w-80">
            <div class="relative">
                <select onchange="window.location.href = '{{ route('operator.dashboard') }}?regency_id=' + this.value"
                        class="w-full text-xs font-bold rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer transition">
                    <option value="all" {{ $selectedRegencyId === 'all' ? 'selected' : '' }}>
                        📍 Semua 9 Kabupaten (124 UPT Kalsel)
                    </option>
                    @foreach($regencies as $r)
                        <option value="{{ $r->id }}" {{ (string)$selectedRegencyId === (string)$r->id ? 'selected' : '' }}>
                            Kabupaten {{ $r->name }} ({{ $r->upt_locations_count }} UPT)
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. METRIK UTAMA KABUPATEN & STATUS USULAN (4 KARTU ARSITEKTURAL MP072) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Card 1: Total UPT (Variant Abyssal) -->
        <x-stat-card
            variant="abyssal"
            label="CAKUPAN UNIT UPT"
            :value="$totalUpt"
            unit="Lokasi"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                <span class="text-emerald-700 font-bold">{{ $cleanCount }} Clean</span>
                <span class="text-[#C9C1B1]">&bull;</span>
                <span class="text-amber-700 font-bold">{{ $warningCount }} Warning</span>
                <span class="text-[#C9C1B1]">&bull;</span>
                <span class="text-rose-700 font-bold">{{ $criticalCount }} Kritis</span>
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#2C3B4D]/70 font-medium">
                    <span>Wilayah Terpilih</span>
                    <span class="font-bold text-[#1B2632]">{{ $selectedRegency ? $selectedRegency->name : '9 Kabupaten Kalsel' }}</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Card 2: Penempatan KK (Variant Slate) -->
        <x-stat-card
            variant="slate"
            label="PENEMPATAN AWAL"
            :value="number_format($totalPlacementKk, 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Total Jiwa Terdata: <strong class="text-[#1B2632] ml-1">{{ number_format($totalPlacementPop ?? ($totalPlacementKk * 4), 0, ',', '.') }}</strong> Jiwa
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#2C3B4D]/70 font-medium">
                    <span>Tahap Penempatan</span>
                    <span class="font-bold text-[#2C3B4D]">Arsip Lapangan</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Card 3: Serah Terima Pemda (Variant Flame) -->
        <x-stat-card
            variant="flame"
            label="SERAH TERIMA PEMDA"
            :value="number_format($totalHandoverKk, 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#8F4E0A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Keluarga Definitif: <strong class="text-[#1B2632] ml-1">{{ number_format($totalHandoverPop ?? ($totalHandoverKk * 4), 0, ',', '.') }}</strong> Jiwa
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#8F4E0A]/80 font-medium">
                    <span>Desa Otonom</span>
                    <span class="font-bold text-[#8F4E0A]">Binaan Pemda</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Card 4: Status Draf Usulan (Variant Truffle) -->
        <x-stat-card
            variant="truffle"
            label="STATUS DRAF USULAN"
            :value="$requestsCount['pending']"
            unit="Pending"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                <span class="text-emerald-700 font-bold">✓ {{ $requestsCount['approved'] }} Disetujui</span>
                <span class="text-[#C9C1B1]">&bull;</span>
                <span class="text-[#A35139] font-bold">✕ {{ $requestsCount['rejected'] }} Ditolak</span>
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#A35139]/90 font-medium">
                    <span>Antrean Verifikasi</span>
                    <span class="font-bold text-[#A35139]">Super Admin Prov.</span>
                </div>
            </x-slot:footer>
        </x-stat-card>
    </div>

    <!-- 4. TABEL DAFTAR UPT WILAYAH (MP072 ARSITEKTURAL) -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-[#1B2632] tracking-tight flex items-center gap-2">
                    <span>Daftar Unit Pemukiman Transmigrasi</span>
                    <span class="bg-[#1B2632] text-[#EEE9DF] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-[#2C3B4D]">
                        {{ $uptLocations->total() }} Unit
                    </span>
                </h3>
                <div class="flex items-center gap-3 text-xs mt-1.5">
                    <span class="text-emerald-700 font-bold">🟢 {{ $cleanCount }} Clean</span>
                    <span class="text-[#C9C1B1]">&bull;</span>
                    <span class="text-amber-700 font-bold">🟡 {{ $warningCount }} Warning</span>
                    <span class="text-[#C9C1B1]">&bull;</span>
                    <span class="text-rose-700 font-bold">🔴 {{ $criticalCount }} Kritis</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('operator.upt.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] text-xs font-bold transition border border-[#C9C1B1]/70 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Buka Filter Lengkap</span>
                </a>
                <a href="{{ route('operator.requests.create') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white text-xs font-bold transition shadow-ambient-xs border border-[#A35139]">
                    <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Ajukan Usulan Baru</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[11px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12 font-semibold">No</th>
                        <th class="py-3.5 px-4 font-semibold">Nama UPT Historis</th>
                        <th class="py-3.5 px-4 font-semibold">Kabupaten</th>
                        <th class="py-3.5 px-4 font-semibold">Desa Definitif</th>
                        <th class="py-3.5 px-4 font-semibold">Pola</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Masuk (KK)</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Serah (KK)</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Status Lahan</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Status SHM</th>
                        <th class="py-3.5 px-4 text-center font-semibold w-36">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($uptLocations as $upt)
                        <tr class="hover:bg-[#EEE9DF]/40 transition" id="upt-row-{{ $upt->id }}">
                            <!-- No UPT -->
                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-[#1B2632]">
                                UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <!-- Nama UPT -->
                            <td class="py-3.5 px-4 font-extrabold text-[#1B2632]">
                                {{ $upt->upt_name }}
                            </td>
                            <!-- Kabupaten -->
                            <td class="py-3.5 px-4 font-bold text-[#2C3B4D]">
                                {{ $upt->regency?->name ?? '-' }}
                            </td>
                            <!-- Desa Definitif -->
                            <td class="py-3.5 px-4 text-[#2C3B4D]/80 font-medium">
                                {{ $upt->current_village_name ?? 'Belum terdefinisi' }}
                            </td>
                            <!-- Pola Usaha -->
                            <td class="py-3.5 px-4">
                                <span class="bg-[#EEE9DF] text-[#1B2632] font-bold px-2 py-0.5 rounded text-[11px] border border-[#C9C1B1]/50">
                                    {{ $upt->business_pattern }}
                                </span>
                            </td>
                            <!-- Penempatan KK -->
                            <td class="py-3.5 px-4 text-center font-bold text-[#1B2632] tabular-nums">
                                {{ number_format($upt->placement_kk, 0, ',', '.') }}
                            </td>
                            <!-- Penyerahan KK -->
                            <td class="py-3.5 px-4 text-center font-bold text-[#1B2632] tabular-nums">
                                {{ number_format($upt->handover_kk, 0, ',', '.') }}
                            </td>
                            <!-- Status Lahan -->
                            <td class="py-3.5 px-4 text-center">
                                <x-status-badge :status="$upt->issue_status" mode="short" size="xs" />
                            </td>
                            <!-- Status SHM -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded bg-[#2C3B4D]/10 text-[#2C3B4D] font-bold text-[10px] border border-[#2C3B4D]/25">
                                    {{ $upt->shm_status ?? '100% SHM' }}
                                </span>
                            </td>
                            <!-- Aksi Cepat -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('operator.requests.create', ['upt_id' => $upt->id]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-[11px] transition shadow-ambient-xs border border-[#1B2632]"
                                       title="Ajukan Pemutakhiran Data">
                                        <svg class="w-3 h-3 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Usulan</span>
                                    </a>
                                    <button type="button" 
                                            onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }} (Kab. {{ addslashes($upt->regency->name ?? '') }})', 'placement')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-[#FFB162] hover:bg-[#ffa347] text-[#1B2632] font-black text-[11px] transition shadow-ambient-xs border border-[#FFB162]/40 cursor-pointer"
                                            title="Buka Registri Warga KK">
                                        <svg class="w-3 h-3 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span>Warga</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-[#2C3B4D]/60 font-medium">
                                Belum ada data UPT terdaftar pada filter wilayah ini.
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

    <!-- 5. TABEL RIWAYAT DRAF USULAN TERAKHIR (MP072) -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="py-3 px-4 sm:px-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="text-sm font-black text-[#1B2632] uppercase tracking-wider">Riwayat Pengajuan Draf Pemutakhiran Terbaru</span>
                <span class="bg-[#1B2632] text-[#EEE9DF] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-[#2C3B4D]">
                    {{ $recentRequests->count() }} Data Terkini
                </span>
            </div>
            <a href="{{ route('operator.requests.index') }}" class="text-xs font-extrabold text-[#A35139] hover:underline flex items-center gap-1 self-start sm:self-auto">
                <span>Lihat Semua Usulan</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[11px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="py-2 px-3 text-center w-12 font-semibold">No</th>
                        <th class="py-2 px-3 font-semibold whitespace-nowrap w-36">Tanggal Pengajuan</th>
                        <th class="py-2 px-3 font-semibold min-w-[200px]">Nama UPT & Wilayah</th>
                        <th class="py-2 px-3 font-semibold whitespace-nowrap w-36">Kategori Usulan</th>
                        <th class="py-2 px-3 font-semibold min-w-[220px] max-w-[260px]">Ringkasan Perubahan</th>
                        <th class="py-2 px-3 text-center font-semibold whitespace-nowrap w-36">Status Verifikasi</th>
                        <th class="py-2 px-3 font-semibold min-w-[180px] max-w-[220px]">Catatan Reviewer Provinsi</th>
                        <th class="py-2 px-3 text-center font-semibold whitespace-nowrap w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <!-- No -->
                            <td class="py-2 px-3 text-center font-mono font-bold text-[#2C3B4D]/60 align-middle">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Tanggal Pengajuan -->
                            <td class="py-2 px-3 font-mono text-[11px] text-[#2C3B4D]/80 whitespace-nowrap align-middle">
                                <div class="font-bold text-[#1B2632] leading-tight">{{ $req->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-[#2C3B4D]/60 leading-tight mt-0.5">{{ $req->created_at->format('H:i') }} WITA</div>
                            </td>

                            <!-- Nama UPT & Wilayah -->
                            <td class="py-2 px-3 align-middle">
                                <div class="font-extrabold text-[#1B2632] text-xs leading-tight">
                                    {{ $req->uptLocation->upt_name ?? '-' }}
                                </div>
                                <div class="text-[10px] text-[#2C3B4D]/70 font-medium leading-tight mt-0.5 flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/20">
                                        Kab. {{ $req->uptLocation->regency->name ?? '-' }}
                                    </span>
                                    @if($req->uptLocation->current_village_name)
                                        <span class="text-[#C9C1B1]">&bull;</span>
                                        <span class="text-[#2C3B4D]/70 text-[10px]">Desa {{ $req->uptLocation->current_village_name }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kategori Usulan -->
                            <td class="py-2 px-3 whitespace-nowrap align-middle">
                                @if($req->request_type === 'REGISTRY_SYNC')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-purple-100 text-purple-900 font-bold text-[10px] border border-purple-300 shadow-2xs whitespace-nowrap">
                                        Buku Registri Warga
                                    </span>
                                @elseif($req->request_type === 'DATA_UPDATE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#2C3B4D]/10 text-[#2C3B4D] font-bold text-[10px] border border-[#2C3B4D]/25 shadow-2xs whitespace-nowrap">
                                        Data Lapangan
                                    </span>
                                @elseif($req->request_type === 'LEGAL_ISSUE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#A35139]/15 text-[#A35139] font-bold text-[10px] border border-[#A35139]/30 shadow-2xs whitespace-nowrap">
                                        Masalah Lahan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#FFB162]/25 text-[#8F4E0A] font-bold text-[10px] border border-[#FFB162]/50 shadow-2xs whitespace-nowrap">
                                        Berkas BAST Baru
                                    </span>
                                @endif
                            </td>

                            <!-- Ringkasan Perubahan (Single line truncated with tooltip) -->
                            <td class="py-2 px-3 text-[#2C3B4D] font-medium align-middle">
                                <div class="text-xs truncate leading-tight" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->proposed_payload['submission_note'] ?? '-' }}">
                                    {{ $req->proposed_payload['submission_note'] ?? '-' }}
                                </div>
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="py-2 px-3 text-center whitespace-nowrap align-middle">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/50 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#8F4E0A] animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30 shadow-2xs">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30 shadow-2xs">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>

                            <!-- Catatan Reviewer Provinsi (Single line truncated with tooltip) -->
                            <td class="py-2 px-3 text-xs align-middle">
                                @if($req->reviewer_note)
                                    <div class="text-[#2C3B4D]/80 italic truncate leading-tight" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->reviewer_note }}">
                                        {{ $req->reviewer_note }}
                                    </div>
                                @else
                                    <span class="text-[#C9C1B1] font-mono">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-2 px-3 text-center whitespace-nowrap align-middle">
                                <a href="{{ route('operator.requests.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] font-bold text-[11px] transition border border-[#C9C1B1]/70 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-4 text-center text-[#2C3B4D]/60 font-medium">
                                Belum ada draf pengajuan usulan. Klik "Ajukan Draf Usulan" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Drawer Registri Warga KK (Opsi C Hibrida) -->
@include('admin.registri_warga.registri_warga')

@endsection
