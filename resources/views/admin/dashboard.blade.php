@extends('layouts.admin')

@section('title', 'Dashboard Utama Super Admin')
@section('header_title', 'Ruang Kendali Super Admin Provinsi')
@section('header_subtitle', 'Pemantauan Rekam Jejak 124 UPT Transmigrasi di 9 Kabupaten Kalimantan Selatan (1953–2025)')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. KARTU METRIK UTAMA (4 KPI CARDS DENGAN PALET WARNA RESMI)             -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <!-- Kartu 1: Cakupan Wilayah (Midnight Navy: #0B1849) -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#0B1849] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">CAKUPAN WILAYAH</span>
                <div class="w-8 h-8 rounded-lg bg-[#0B1849]/10 text-[#0B1849] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#0B1849] tracking-tight tabular-nums">{{ $totalUpt }}</div>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                    <span class="text-emerald-700 font-bold">100% Terpetakan</span>
                    <span>• 9 Kabupaten</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Arsip Historis</span>
                <span class="font-bold text-slate-700">1953 – 2025</span>
            </div>
        </div>

        <!-- Kartu 2: Penempatan Awal (Forest Green: #124D1C) -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#124D1C] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">PENEMPATAN AWAL</span>
                <div class="w-8 h-8 rounded-lg bg-[#124D1C]/10 text-[#124D1C] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#124D1C] tracking-tight tabular-nums">{{ number_format($totalPlacementKk, 0, ',', '.') }} <span class="text-base font-bold text-slate-600">KK</span></div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Total Kependudukan: <strong class="text-slate-800">{{ number_format($totalPlacementPop, 0, ',', '.') }}</strong> Jiwa
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Rata-rata Jiwa/KK</span>
                <span class="font-bold text-emerald-800">4,02 Jiwa</span>
            </div>
        </div>

        <!-- Kartu 3: Serah Terima Pemda (Warm Gold: #E4B028) -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-[#E4B028] border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">SERAH TERIMA PEMDA</span>
                <div class="w-8 h-8 rounded-lg bg-[#E4B028]/20 text-[#0B1849] flex items-center justify-center">
                    <svg class="w-4 h-4 text-[#0B1849]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-black text-[#0B1849] tracking-tight tabular-nums">{{ number_format($totalHandoverKk, 0, ',', '.') }} <span class="text-base font-bold text-slate-600">KK</span></div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Keluarga Definitif: <strong class="text-slate-800">{{ number_format($totalHandoverPop, 0, ',', '.') }}</strong> Jiwa
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Pertumbuhan Alami</span>
                <span class="font-bold text-amber-700">+1.241 KK (+2,0%)</span>
            </div>
        </div>

        <!-- Kartu 4: Status Lahan Agraria (Traffic Light) -->
        <div class="bg-white rounded-2xl p-5 border-t-4 border-rose-500 border-x border-b border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">STATUS LAHAN AGRARIA</span>
                <div class="flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <div class="text-2xl font-black text-rose-600 tracking-tight tabular-nums">{{ $criticalCount }} <span class="text-xs font-bold text-slate-600">Prioritas</span></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Sengketa / Kawasan Hutan</div>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-emerald-700 tracking-tight tabular-nums">{{ $cleanCount }}</div>
                    <div class="text-[10px] text-slate-400">Clean & Clear</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Monitoring Tanggul</span>
                <span class="font-bold text-amber-600">{{ $warningCount }} Lokasi</span>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. INTEGRITAS & KESIAPAN DATA 9 KABUPATEN                                 -->
    <!-- (SESUAI WIREFRAME 3.1: TABEL/GRID STATUS KESIAPAN 9 WILAYAH)              -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-black text-[#0B1849] text-sm uppercase tracking-wider flex items-center gap-2">
                    <span>INTEGRITAS & KESIAPAN DATA 9 KABUPATEN:</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Monitoring kelengkapan data spasial, SK penetapan, dan arsip BAST per wilayah binaan di Kalimantan Selatan.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300 shrink-0 shadow-2xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>Status Sinkronisasi: <strong>SELESAI</strong></span>
            </div>
        </div>

        <div class="p-5">
            @php
                // Mapping nama singkatan sesuai wireframe ASCII 3.1
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
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 hover:bg-emerald-50/50 border border-slate-200/70 transition group">
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="font-bold text-[#0B1849] w-24">
                                    {{ $reg->id }}. {{ $shortNames[$reg->id] ?? $reg->name }}
                                </span>
                                <span class="text-slate-400">:</span>
                                <span class="font-black text-slate-800 tabular-nums">
                                    {{ str_pad($reg->upt_locations_count, 2, ' ', STR_PAD_LEFT) }} UPT
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-20 bg-slate-200 rounded-full h-1.5 hidden sm:block overflow-hidden">
                                    <div class="bg-[#124D1C] h-1.5 rounded-full w-full"></div>
                                </div>
                                <span class="text-[11px] font-black font-mono text-emerald-800 bg-emerald-100/90 px-2 py-0.5 rounded border border-emerald-200">
                                    [100%]
                                </span>
                                <a href="{{ route('admin.upt.index', ['regency_id' => $reg->id]) }}" 
                                   title="Lihat data UPT {{ $reg->name }}"
                                   class="text-[10px] font-bold text-[#124D1C] hover:underline opacity-0 group-hover:opacity-100 transition hidden sm:inline">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Kolom Kanan: 6 s.d. 9 + Status Sinkronisasi -->
                <div class="space-y-2.5">
                    @foreach($col2 as $reg)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 hover:bg-emerald-50/50 border border-slate-200/70 transition group">
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="font-bold text-[#0B1849] w-24">
                                    {{ $reg->id }}. {{ $shortNames[$reg->id] ?? $reg->name }}
                                </span>
                                <span class="text-slate-400">:</span>
                                <span class="font-black text-slate-800 tabular-nums">
                                    {{ str_pad($reg->upt_locations_count, 2, ' ', STR_PAD_LEFT) }} UPT
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-20 bg-slate-200 rounded-full h-1.5 hidden sm:block overflow-hidden">
                                    <div class="bg-[#124D1C] h-1.5 rounded-full w-full"></div>
                                </div>
                                <span class="text-[11px] font-black font-mono text-emerald-800 bg-emerald-100/90 px-2 py-0.5 rounded border border-emerald-200">
                                    [100%]
                                </span>
                                <a href="{{ route('admin.upt.index', ['regency_id' => $reg->id]) }}" 
                                   title="Lihat data UPT {{ $reg->name }}"
                                   class="text-[10px] font-bold text-[#124D1C] hover:underline opacity-0 group-hover:opacity-100 transition hidden sm:inline">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <!-- Baris Status Sinkronisasi Sesuai Wireframe -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200 text-xs font-mono">
                        <div class="flex items-center gap-2 font-bold text-emerald-950">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Status Sinkronisasi:</span>
                            <span class="text-[#124D1C] font-black">SELESAI</span>
                        </div>
                        <span class="text-[10px] text-emerald-800 font-sans font-bold">
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
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 bg-gradient-to-r from-rose-50/80 via-white to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-[#0B1849] text-sm uppercase tracking-wider">
                        6 Lokasi Prioritas Khusus & Mediasi Pertanahan
                    </h3>
                    <p class="text-xs text-slate-500">
                        Memerlukan koordinasi teknis bersama Kanwil BPN, Balai Pemantapan Kawasan Hutan (BPKH), dan Pemda Kabupaten.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.upt.index', ['status' => 'critical']) }}" 
               class="text-xs font-extrabold text-rose-700 hover:text-rose-900 flex items-center gap-1 shrink-0">
                <span>Lihat Semua Kasus Prioritas</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0B1849] text-[#EBEDE3] font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3 px-4">No. UPT</th>
                        <th class="py-3 px-4">Kabupaten</th>
                        <th class="py-3 px-4">Nama Satuan Pemukiman</th>
                        <th class="py-3 px-4">Desa Definitif</th>
                        <th class="py-3 px-4">Uraian Permasalahan Lahan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($priorityCases as $case)
                        <tr class="hover:bg-rose-50/40 transition">
                            <td class="py-3.5 px-4 font-mono font-extrabold text-[#0B1849]">
                                UPT-{{ str_pad($case->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                Kab. {{ $case->regency?->name }} ({{ $case->regency?->code_roman }})
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                {{ $case->upt_name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $case->current_village_name }}
                            </td>
                            <td class="py-3.5 px-4 text-rose-800 font-medium">
                                <span class="bg-rose-100 text-rose-900 px-2 py-0.5 rounded text-[11px] font-bold mr-1 inline-block">Kritis:</span>
                                {{ $case->issue_note }}
                            </td>
                            <td class="py-3.5 px-4 text-center shrink-0">
                                <a href="{{ route('admin.upt.edit', $case->id) }}" 
                                   class="bg-[#124D1C] hover:bg-emerald-700 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1 shadow-xs">
                                    <svg class="w-3 h-3 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
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
