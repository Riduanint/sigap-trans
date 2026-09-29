@extends('layouts.admin')

@section('title', 'Analitik Kebijakan & Tren Transmigrasi')
@section('header_title', 'Ruang Kendali Visual & Analisis Kebijakan')
@section('header_subtitle', 'Sintesis Makro, Tren Dekade (1953–2025), & Matriks Komparasi Performa 9 Kabupaten Kalimantan Selatan')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- TOOLBAR AKSI ATAS & PERINGATAN STRATEGIS                                 -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-[#1B2632] text-white shadow-ambient-sm border border-[#2C3B4D]">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-[#FFB162]/20 text-[#FFB162] border border-[#FFB162]/40 flex items-center justify-center font-black text-xl shrink-0 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-[#FFB162] text-[#1B2632] px-2 py-0.5 rounded">
                        STRATEGIS & KEBIJAKAN
                    </span>
                    <span class="text-xs text-[#C9C1B1]">|</span>
                    <span class="text-xs text-[#C9C1B1] font-mono">124 UPT Terdata</span>
                </div>
                <h2 class="text-lg font-extrabold text-[#EEE9DF] mt-0.5 tracking-tight">
                    Ikhtisar Perkembangan & Evaluasi Penyerahan Kawasan
                </h2>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.analitik.pdf') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#8A4430] text-white font-bold text-xs shadow-ambient-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Executive Briefing (PDF)</span>
            </a>
            <a href="{{ route('admin.reports.excel') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-[#EEE9DF] font-bold text-xs transition border border-[#C9C1B1]/30">
                <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Unduh Data Excel</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. KARTU METRIK KPI MAKRO KEBIJAKAN                                      -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Kartu 1: Rasio Serah Terima UPT -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Definitif vs Binaan</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#1B2632] tabular-nums">
                    {{ $definitifUptCount }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalUpt }} UPT</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    {{ round(($definitifUptCount / max(1, $totalUpt)) * 100, 1) }}% Sudah Diserahkan ke Pemda
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Masih Binaan:</span>
                <strong class="text-amber-700 font-bold">{{ $binaanUptCount }} UPT</strong>
            </div>
        </div>

        <!-- Kartu 2: Pertumbuhan Alami KK -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Dinamika Kependudukan</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#1B2632] tabular-nums">
                    {{ number_format($totalHandoverKk, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Awal: {{ number_format($totalPlacementKk, 0, ',', '.') }} KK
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Pertumbuhan:</span>
                <strong class="text-emerald-700 font-bold">+{{ number_format($handoverGrowthKk, 0, ',', '.') }} KK (+{{ $handoverGrowthPct }}%)</strong>
            </div>
        </div>

        <!-- Kartu 3: Komposisi Transmigran -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Asal vs Lokal</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#1B2632] tabular-nums">
                    {{ number_format($tpaKk, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">TPA</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Penduduk Asal Luar Kalsel (Jawa, Bali, NTB)
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Penduduk Setempat (TPS):</span>
                <strong class="text-blue-800 font-bold">{{ number_format($tpsKk, 0, ',', '.') }} KK</strong>
            </div>
        </div>

        <!-- Kartu 4: Status Kondisi Lahan -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Status Kondisi Lahan</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-emerald-700 tabular-nums">
                    {{ $cleanCount }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalUpt }} Clean</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Bebas Sengketa & Legalitas Clear
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Perhatian / Mediasi:</span>
                <div>
                    <strong class="text-rose-700 font-bold">{{ $criticalCount }} Kritis</strong>
                    <span class="text-slate-400">/</span>
                    <strong class="text-amber-700 font-bold">{{ $warningCount }} Warning</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. GRAFIK TREN HISTORIS TRANSMIGRASI PER DEKADE (1950 - 2025)             -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Line/Bar Chart: Tren Kependudukan per Dekade -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        <span>Tren Penempatan KK Transmigran per Dekade (1950–2025)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Volume migrasi program pemerintah dari era Pelita awal hingga era revitalisasi kawasan
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-[#1B2632] text-white">
                    Histori 7 Dekade
                </span>
            </div>

            <div class="h-72 w-full relative">
                <canvas id="decadeChart"></canvas>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-100 text-center text-xs">
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] text-slate-400 font-bold">ERA 1970-AN (PELITA)</div>
                    <div class="font-extrabold text-[#1B2632] mt-0.5">{{ number_format($decadesData['1970–1979']['kk'] ?? 0, 0, ',', '.') }} KK</div>
                </div>
                <div class="p-2 rounded-xl bg-[#FFB162]/10 border border-[#FFB162]/30">
                    <div class="text-[10px] text-amber-800 font-bold">PUNCAK 1980-AN</div>
                    <div class="font-extrabold text-[#A35139] mt-0.5">{{ number_format($decadesData['1980–1989']['kk'] ?? 0, 0, ',', '.') }} KK</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] text-slate-400 font-bold">ERA 1990-AN</div>
                    <div class="font-extrabold text-[#1B2632] mt-0.5">{{ number_format($decadesData['1990–1999']['kk'] ?? 0, 0, ',', '.') }} KK</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] text-slate-400 font-bold">ERA 2000–2025</div>
                    <div class="font-extrabold text-[#1B2632] mt-0.5">{{ number_format(($decadesData['2000–2009']['kk'] ?? 0) + ($decadesData['2010–2025']['kk'] ?? 0), 0, ',', '.') }} KK</div>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart: Komposisi Transmigran & Legalitas SHM -->
        <div class="transmigration-type-panel bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-6 space-y-5 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-4">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                    <span>Proporsi Jenis Transmigran</span>
                </h3>

                <div class="h-48 w-full relative mt-4">
                    <canvas id="typeDoughnutChart"></canvas>
                </div>
            </div>

            <div class="transmigration-type-legend space-y-2 pt-2 border-t border-slate-100 text-xs">
                <div class="transmigration-type-legend-row transmigration-type-legend-row--tpa flex items-center justify-between p-2 rounded-xl font-medium">
                    <span class="flex items-center gap-2 font-bold">
                        <span class="transmigration-type-swatch transmigration-type-swatch--tpa w-3 h-3 rounded-full"></span>
                        <span>TPA (Penduduk Asal Luar Kalsel)</span>
                    </span>
                    <strong class="font-mono">{{ number_format($tpaKk, 0, ',', '.') }} KK</strong>
                </div>
                <div class="transmigration-type-legend-row transmigration-type-legend-row--tps flex items-center justify-between p-2 rounded-xl font-medium">
                    <span class="flex items-center gap-2 font-bold">
                        <span class="transmigration-type-swatch transmigration-type-swatch--tps w-3 h-3 rounded-full"></span>
                        <span>TPS (Penduduk Setempat/Lokal)</span>
                    </span>
                    <strong class="font-mono">{{ number_format($tpsKk, 0, ',', '.') }} KK</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. MATRIKS KOMPARASI PERFORMA 9 KABUPATEN SE-KALSEL                      -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Matriks Evaluasi Kinerja & Beban Serah Terima 9 Kabupaten</span>
                </h3>
                <p class="text-xs text-[#2C3B4D]/75 mt-0.5 font-medium">
                    Komparasi agregat KK penempatan vs serah terima serta tingkat risiko legalitas agraria di setiap daerah
                </p>
            </div>
            <div class="text-xs font-mono font-bold text-[#1B2632] bg-white px-3 py-1.5 rounded-xl border border-[#C9C1B1]">
                Total: 124 UPT
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[10px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-4">Kabupaten</th>
                        <th class="py-3 px-3 text-center">Jumlah UPT</th>
                        <th class="py-3 px-4 text-right">KK Penempatan</th>
                        <th class="py-3 px-4 text-right">KK Serah Terima</th>
                        <th class="py-3 px-3 text-center">Pertumbuhan</th>
                        <th class="py-3 px-4 text-center">Status Lahan (Clean / Warning / Kritis)</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($regencies as $idx => $r)
                        @php
                            $uptCount = $r->upt_locations_count ?? 0;
                            $pPlacement = (int) ($r->total_placement_kk ?? 0);
                            $pHandover = (int) ($r->total_handover_kk ?? 0);
                            $growth = $r->growth_kk ?? ($pHandover - $pPlacement);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-extrabold text-slate-900">
                                <div>{{ $r->name }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">Ibukota: {{ $r->capital_city ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-black text-slate-800 font-mono text-xs">
                                    {{ $uptCount }} UPT
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-700">
                                {{ number_format($pPlacement, 0, ',', '.') }} KK
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#1B2632]">
                                {{ number_format($pHandover, 0, ',', '.') }} KK
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-[11px]">
                                @if($growth >= 0)
                                    <span class="text-emerald-700 font-bold">+{{ number_format($growth, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-rose-600 font-bold">{{ number_format($growth, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1" title="Clean & Clear">
                                        <span>🟢</span> {{ $r->clean_count ?? 0 }} Clean
                                    </span>
                                    @if(($r->warning_count ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 inline-flex items-center gap-1" title="Waspada / Monitoring">
                                            <span>🟡</span> {{ $r->warning_count }} Warning
                                        </span>
                                    @endif
                                    @if(($r->critical_count ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 inline-flex items-center gap-1 animate-pulse" title="Kritis / Mediasi">
                                            <span>🔴</span> {{ $r->critical_count }} Kritis
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('admin.upt.index', ['regency_id' => $r->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-[#C9C1B1] hover:bg-[#EEE9DF]/60 text-[#1B2632] font-bold text-[11px] transition shadow-2xs">
                                    <span>Detail</span>
                                    <svg class="w-3 h-3 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. KASUS PRIORITAS MEDIASI PERTANAHAN & REKOMENDASI KEBIJAKAN            -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-gradient-to-r from-[#A35139]/10 via-[#EEE9DF]/40 to-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider">
                        Rekomendasi Kebijakan Lahan Kritis & Mediasi Lintas Sektoral
                    </h3>
                    <p class="text-xs text-[#2C3B4D]/75 font-medium">
                        Daftar lokasi yang memerlukan koordinasi intensif bersama Dinas Kehutanan, Balai Pemantapan Kawasan Hutan (BPKH), dan Kanwil ATR/BPN
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.sertifikat-tanah.index') }}" class="text-xs font-bold text-[#A35139] hover:underline flex items-center gap-1 shrink-0">
                <span>Buka Monitoring Sertipikat</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($criticalCases as $c)
                <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-800 font-mono">
                                UPT-{{ str_pad($c->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <h4 class="text-sm font-black text-slate-900">{{ $c->upt_name }}</h4>
                            <span class="text-xs text-slate-400 font-mono">({{ $c->regency?->name }})</span>
                            <x-status-badge status="critical" mode="short" size="xs" />
                        </div>
                        <p class="text-xs text-slate-600">
                            {{ $c->issue_note ?? ($c->notes_issue ?? 'Tumpang tindih klaim kawasan hutan / permasalahan tapal batas bidang tanah warga.') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                            {{ $c->shm_status ?? 'Proses BPN' }}
                        </span>
                        <a href="{{ route('admin.upt.show', $c->id) }}" class="px-3 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs shadow-ambient-xs transition">
                            Tinjau Lokasi
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs font-medium">
                    Tidak ada lokasi berstatus kritis saat ini.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Inisialisasi Chart.js untuk Tren Dekade & Komposisi -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chart Dekade (Line + Bar Hybrid)
    const ctxDecade = document.getElementById('decadeChart').getContext('2d');
    const decadeLabels = ['1950–59', '1960–69', '1970–79', '1980–89', '1990–99', '2000–09', '2010–25'];
    const decadeKk = [
        {{ $decadesData['1950–1959']['kk'] ?? 0 }},
        {{ $decadesData['1960–1969']['kk'] ?? 0 }},
        {{ $decadesData['1970–1979']['kk'] ?? 0 }},
        {{ $decadesData['1980–1989']['kk'] ?? 0 }},
        {{ $decadesData['1990–1999']['kk'] ?? 0 }},
        {{ $decadesData['2000–2009']['kk'] ?? 0 }},
        {{ $decadesData['2010–2025']['kk'] ?? 0 }}
    ];

    new Chart(ctxDecade, {
        type: 'bar',
        data: {
            labels: decadeLabels,
            datasets: [{
                label: 'Jumlah KK Ditempatkan',
                data: decadeKk,
                backgroundColor: [
                    'rgba(44, 59, 77, 0.75)',
                    'rgba(44, 59, 77, 0.75)',
                    'rgba(255, 177, 98, 0.85)',
                    'rgba(163, 81, 57, 0.95)', // Puncak 80-an
                    'rgba(44, 59, 77, 0.85)',
                    'rgba(27, 38, 50, 0.85)',
                    'rgba(255, 177, 98, 0.75)'
                ],
                borderColor: '#1B2632',
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y.toLocaleString('id-ID') + ' KK Transmigran';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(201, 193, 177, 0.2)'
                    },
                    ticks: {
                        font: { size: 10, family: 'monospace' },
                        callback: function(val) {
                            return val.toLocaleString('id-ID') + ' KK';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, weight: 'bold' }
                    }
                }
            }
        }
    });

    // 2. Chart Komposisi Transmigran (Doughnut)
    const ctxType = document.getElementById('typeDoughnutChart').getContext('2d');
    new Chart(ctxType, {
        type: 'doughnut',
        data: {
            labels: ['TPA (Penduduk Asal)', 'TPS (Penduduk Setempat)'],
            datasets: [{
                data: [{{ $tpaKk }}, {{ $tpsKk }}],
                backgroundColor: ['#24515A', '#D6A557'],
                borderColor: ['#ffffff', '#ffffff'],
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 10, weight: 'bold' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.parsed.toLocaleString('id-ID') + ' KK';
                        }
                    }
                }
            },
            cutout: '68%'
        }
    });
});
</script>
@endsection
