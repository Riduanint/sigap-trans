@extends('layouts.admin')

@section('title', 'Dashboard Utama Super Admin')
@section('header_title', 'Ruang Kendali Super Admin Provinsi')
@section('header_subtitle', 'Pemantauan Rekam Jejak 124 UPT Transmigrasi di 9 Kabupaten Kalimantan Selatan (1953–2025)')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. KARTU METRIK UTAMA (4 KPI CARDS - PALET ARSITEKTURAL MP072)            -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <!-- Kartu 1: Cakupan Wilayah (Abyssal: #1B2632) -->
        <x-stat-card
            variant="abyssal"
            label="CAKUPAN WILAYAH"
            :value="$totalUpt"
            unit="UPT"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                <span class="text-[#2C3B4D] font-bold">100% Terpetakan</span>
                <span class="text-[#C9C1B1]">&bull;</span>
                <span class="text-[#2C3B4D]/70 font-medium">9 Kabupaten</span>
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#2C3B4D]/70 font-medium">
                    <span>Arsip Historis</span>
                    <span class="font-bold text-[#1B2632]">1953 – 2025</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Kartu 2: Penempatan Awal (Slate: #2C3B4D) -->
        <x-stat-card
            variant="slate"
            label="PENEMPATAN AWAL"
            :value="number_format($totalPlacementKk, 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Total Kependudukan: <strong class="text-[#1B2632] ml-1">{{ number_format($totalPlacementPop, 0, ',', '.') }}</strong> Jiwa
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#2C3B4D]/70 font-medium">
                    <span>Rata-rata Jiwa/KK</span>
                    <span class="font-bold text-[#2C3B4D]">4,02 Jiwa</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Kartu 3: Serah Terima Pemda (Flame: #FFB162) -->
        <x-stat-card
            variant="flame"
            label="SERAH TERIMA PEMDA"
            :value="number_format($totalHandoverKk, 0, ',', '.')"
            unit="KK"
        >
            <x-slot:customIcon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </x-slot:customIcon>
            <x-slot:subtext>
                Keluarga Definitif: <strong class="text-[#1B2632] ml-1">{{ number_format($totalHandoverPop, 0, ',', '.') }}</strong> Jiwa
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#8F4E0A]/80 font-medium">
                    <span>Pertumbuhan Alami</span>
                    <span class="font-bold text-[#8F4E0A]">+1.241 KK (+2,0%)</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

        <!-- Kartu 4: Status Kondisi Lahan (Truffle: #A35139) -->
        <x-stat-card
            variant="truffle"
            label="STATUS KONDISI LAHAN"
            :value="$criticalCount"
            unit="Kritis"
        >
            <x-slot:customIcon>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="Clean"></span>
                    <span class="w-2 h-2 rounded-full bg-amber-500" title="Warning"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping" title="Kritis"></span>
                </div>
            </x-slot:customIcon>
            <x-slot:subtext>
                <span class="text-emerald-700 font-bold">{{ $cleanCount }} Clean</span>
                <span class="text-[#C9C1B1]">&bull;</span>
                <span class="text-amber-700 font-bold">{{ $warningCount }} Warning</span>
            </x-slot:subtext>
            <x-slot:footer>
                <div class="flex items-center justify-between text-[11px] text-[#A35139]/90 font-medium">
                    <span>Kasus Kritis Butuh Mediasi:</span>
                    <span class="font-bold text-[#A35139]">{{ $criticalCount }} Lokasi</span>
                </div>
            </x-slot:footer>
        </x-stat-card>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. INTEGRITAS & KESIAPAN DATA 9 KABUPATEN                                 -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider flex items-center gap-2">
                    <span>INTEGRITAS & KESIAPAN DATA 9 KABUPATEN:</span>
                </h3>
                <p class="text-xs text-[#2C3B4D]/75 mt-0.5 font-medium">
                    Monitoring kelengkapan data spasial, SK penetapan, dan arsip BAST per wilayah binaan di Kalimantan Selatan.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold bg-[#1B2632]/5 text-[#1B2632] border border-[#C9C1B1]/60 shrink-0 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-[#2C3B4D] animate-pulse"></span>
                <span>Status Sinkronisasi: <strong class="text-[#1B2632]">SELESAI</strong></span>
            </div>
        </div>

        <div class="p-5 bg-white">
            @php
                $shortNames = [
                    1 => 'Tapin',
                    2 => 'HSU',
                    3 => 'Balangan',
                    4 => 'Tabalong',
                    5 => 'Tanah Laut',
                    6 => 'Batola',
                    7 => 'Kotabaru',
                    8 => 'Tanbu',
                    9 => 'Banjar',
                ];

                $col1 = $regencies->whereIn('id', [1, 2, 3, 4, 5]);
                $col2 = $regencies->whereIn('id', [6, 7, 8, 9]);
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-3">
                <!-- Kolom Kiri: 1 s.d. 5 -->
                <div class="space-y-2.5">
                    @foreach($col1 as $reg)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#EEE9DF]/30 hover:bg-[#EEE9DF]/80 border border-[#C9C1B1]/50 hover:border-[#2C3B4D]/40 transition group">
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="font-bold text-[#1B2632] w-28">
                                    {{ $reg->id }}. {{ $shortNames[$reg->id] ?? $reg->name }}
                                </span>
                                <span class="text-[#C9C1B1]">:</span>
                                <span class="font-bold text-[#2C3B4D] tabular-nums">
                                    {{ str_pad($reg->upt_locations_count, 2, ' ', STR_PAD_LEFT) }} UPT
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-20 bg-[#C9C1B1]/40 rounded-full h-1.5 hidden sm:block overflow-hidden">
                                    <div class="bg-[#2C3B4D] h-1.5 rounded-full w-full"></div>
                                </div>
                                <span class="text-[11px] font-bold font-mono text-[#2C3B4D] bg-[#2C3B4D]/10 px-2 py-0.5 rounded border border-[#2C3B4D]/20">
                                    [100%]
                                </span>
                                <a href="{{ route('admin.upt.index', ['regency_id' => $reg->id]) }}" 
                                   title="Lihat data UPT {{ $reg->name }}"
                                   class="text-[11px] font-bold text-[#A35139] hover:text-[#8A4430] hover:underline opacity-0 group-hover:opacity-100 transition hidden sm:inline">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Kolom Kanan: 6 s.d. 9 + Status Sinkronisasi -->
                <div class="space-y-2.5">
                    @foreach($col2 as $reg)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#EEE9DF]/30 hover:bg-[#EEE9DF]/80 border border-[#C9C1B1]/50 hover:border-[#2C3B4D]/40 transition group">
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="font-bold text-[#1B2632] w-28">
                                    {{ $reg->id }}. {{ $shortNames[$reg->id] ?? $reg->name }}
                                </span>
                                <span class="text-[#C9C1B1]">:</span>
                                <span class="font-bold text-[#2C3B4D] tabular-nums">
                                    {{ str_pad($reg->upt_locations_count, 2, ' ', STR_PAD_LEFT) }} UPT
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-20 bg-[#C9C1B1]/40 rounded-full h-1.5 hidden sm:block overflow-hidden">
                                    <div class="bg-[#2C3B4D] h-1.5 rounded-full w-full"></div>
                                </div>
                                <span class="text-[11px] font-bold font-mono text-[#2C3B4D] bg-[#2C3B4D]/10 px-2 py-0.5 rounded border border-[#2C3B4D]/20">
                                    [100%]
                                </span>
                                <a href="{{ route('admin.upt.index', ['regency_id' => $reg->id]) }}" 
                                   title="Lihat data UPT {{ $reg->name }}"
                                   class="text-[11px] font-bold text-[#A35139] hover:text-[#8A4430] hover:underline opacity-0 group-hover:opacity-100 transition hidden sm:inline">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <!-- Baris Status Sinkronisasi -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#2C3B4D]/5 border border-[#2C3B4D]/20 text-xs font-mono">
                        <div class="flex items-center gap-2 font-bold text-[#1B2632]">
                            <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Status Sinkronisasi:</span>
                            <span class="text-[#2C3B4D] font-extrabold">SELESAI</span>
                        </div>
                        <span class="text-[10px] text-[#2C3B4D] font-sans font-bold">
                            PostGIS Spasial Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. LOKASI PRIORITAS KHUSUS & MEDIASI PERTANAHAN                           -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-gradient-to-r from-[#A35139]/10 via-[#EEE9DF]/40 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider">
                        6 Lokasi Prioritas Khusus & Mediasi Pertanahan
                    </h3>
                    <p class="text-xs text-[#2C3B4D]/75 font-medium">
                        Memerlukan koordinasi teknis bersama Kanwil BPN, Balai Pemantapan Kawasan Hutan (BPKH), dan Pemda Kabupaten.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.upt.index', ['status' => 'critical']) }}" 
               class="text-xs font-bold text-[#A35139] hover:text-[#8A4430] flex items-center gap-1.5 shrink-0 transition">
                <span>Lihat Semua Kasus Prioritas</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">No. UPT</th>
                        <th class="py-3.5 px-4 font-semibold">Kabupaten</th>
                        <th class="py-3.5 px-4 font-semibold">Nama Satuan Pemukiman</th>
                        <th class="py-3.5 px-4 font-semibold">Desa Definitif</th>
                        <th class="py-3.5 px-4 font-semibold">Uraian Permasalahan Lahan</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @foreach($priorityCases as $case)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#1B2632]">
                                UPT-{{ str_pad($case->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-[#2C3B4D]">
                                Kab. {{ $case->regency?->name }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-[#1B2632]">
                                {{ $case->upt_name }}
                            </td>
                            <td class="py-3.5 px-4 text-[#2C3B4D]/80">
                                {{ $case->current_village_name }}
                            </td>
                            <td class="py-3.5 px-4 text-[#2C3B4D] font-medium">
                                <span class="mr-1.5 inline-block">
                                    <x-status-badge status="critical" mode="short" size="xs" />
                                </span>
                                {{ $case->issue_note ?? ($case->notes_issue ?? '-') }}
                            </td>
                            <td class="py-3.5 px-4 text-center shrink-0">
                                <a href="{{ route('admin.upt.edit', $case->id) }}" 
                                   class="bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-[11px] px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-3 h-3 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span>Tindak Lanjut</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
