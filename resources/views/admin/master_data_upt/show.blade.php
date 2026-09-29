@extends('layouts.admin')

@section('title', 'Detail Profil UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' ' . $upt->upt_name)
@section('header_title', 'Profil Komprehensif: UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' (' . $upt->upt_name . ')')
@section('header_subtitle', 'Kabupaten ' . ($upt->regency?->name ?? '-') . ' • Informasi Wilayah, Demografi, Pertanahan, dan Geospasial')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- BREADCRUMB & TOOLBAR AKSI CEPAT                                          -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-[#1B2632] text-white shadow-ambient-sm border border-[#2C3B4D]">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-[#FFB162]/20 text-[#FFB162] border border-[#FFB162]/40 flex items-center justify-center font-mono font-black text-base shrink-0 shadow-inner">
                {{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-[#FFB162] text-[#1B2632] px-2 py-0.5 rounded">
                        UPT DEFINITIF
                    </span>
                    <span class="text-xs text-[#C9C1B1]">|</span>
                    <span class="text-xs text-[#EEE9DF] font-bold">
                        Kabupaten {{ $upt->regency?->name ?? 'Kalimantan Selatan' }}
                    </span>
                    <span class="text-xs text-[#C9C1B1]">|</span>
                    <span class="text-xs text-[#C9C1B1]">
                        Pola: <strong class="text-[#EEE9DF]">{{ $upt->business_pattern ?? 'Umum' }}</strong>
                    </span>
                </div>
                <h2 class="text-xl font-extrabold text-[#EEE9DF] mt-1 tracking-tight">
                    {{ $upt->upt_name }}
                    <span class="text-[#C9C1B1] font-normal text-sm block sm:inline">
                        (Desa {{ $upt->current_village_name ?? '-' }})
                    </span>
                </h2>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.upt.edit', $upt->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#FFB162] hover:bg-[#e69f54] text-[#1B2632] font-black text-xs transition shadow-ambient-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit UPT</span>
            </a>

            <button type="button" 
                    onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-[#EEE9DF] font-bold text-xs transition border border-[#C9C1B1]/40 cursor-pointer shadow-ambient-xs"
                    title="Buka Buku Registri Warga Transmigran">
                <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Registri KK</span>
            </button>

            @if($upt->latitude && $upt->longitude)
                <a href="{{ route('home', ['upt_id' => $upt->id]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-ambient-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Buka WebGIS</span>
                </a>
            @endif

            <a href="{{ route('admin.upt.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#2C3B4D] hover:bg-[#3d5168] text-[#C9C1B1] hover:text-white font-bold text-xs transition border border-[#C9C1B1]/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. KARTU RINGKASAN METRIK (MP072)                                         -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Status SHM Pertanahan -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Status Hak Milik (SHM)</span>
                <span class="p-1.5 rounded-lg bg-[#1B2632]/10 text-[#1B2632]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-xl font-extrabold text-[#1B2632]">
                    @php $shm = $upt->shm_status ?? '100% SHM'; @endphp
                    @if(str_contains($shm, '100%'))
                        <span class="text-emerald-700">{{ $shm }}</span>
                    @elseif(str_contains($shm, 'Sebagian') || str_contains($shm, 'Proses'))
                        <span class="text-amber-700">{{ $shm }}</span>
                    @else
                        <span class="text-slate-700">{{ $shm }}</span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Legalisasi Agraria & PTSL Kanwil BPN
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Tingkat Isu:</span>
                <x-status-badge :status="$upt->issue_status" mode="full" size="xs" />
            </div>
        </div>

        <!-- Penempatan Awal -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Penempatan Awal</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#1B2632] tabular-nums">
                    {{ number_format($upt->placement_kk) }} <span class="text-xs font-semibold text-slate-400">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Total: {{ number_format($upt->placement_population) }} Jiwa
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Tahun Penempatan:</span>
                <strong class="text-[#1B2632] font-bold">{{ $upt->placement_year ?: '-' }}</strong>
            </div>
        </div>

        <!-- Serah Terima Pemda -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Serah Terima Pemda</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#1B2632] tabular-nums">
                    {{ number_format($upt->handover_kk) }} <span class="text-xs font-semibold text-slate-400">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Total: {{ number_format($upt->handover_population) }} Jiwa
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Tahun BAST:</span>
                <strong class="text-[#1B2632] font-bold">{{ $upt->handover_year ?: 'Belum diserahkan' }}</strong>
            </div>
        </div>

        <!-- Pertumbuhan Demografi Alami -->
        @php
            $diffKk = (int) $upt->handover_kk - (int) $upt->placement_kk;
            $diffPop = (int) $upt->handover_population - (int) $upt->placement_population;
        @endphp
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Dinamika Pertumbuhan</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black {{ $diffKk >= 0 ? 'text-emerald-700' : 'text-slate-700' }} tabular-nums">
                    {{ $diffKk >= 0 ? '+' : '' }}{{ number_format($diffKk) }} <span class="text-xs font-semibold text-slate-400">KK</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Selisih Jiwa: {{ $diffPop >= 0 ? '+' : '' }}{{ number_format($diffPop) }} Jiwa
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Status Kawasan:</span>
                <strong class="{{ $upt->handover_year ? 'text-emerald-700' : 'text-amber-700' }} font-bold">
                    {{ $upt->handover_year ? 'Definitif Pemkab' : 'Kawasan Binaan' }}
                </strong>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. GRID KONTEN DETAIL 2 KOLOM                                             -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- KOLOM KIRI: DATA WILAYAH & STATUS AGRARIA (2 SPAN) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- KARTU 1: DATA ADMINISTRATIF & IDENTITAS INDUK -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFB162]"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            Informasi Administratif & Kewilayahan
                        </h3>
                    </div>
                    <span class="text-[11px] font-mono text-[#2C3B4D] bg-[#EEE9DF] px-2.5 py-0.5 rounded border border-[#C9C1B1]/60">
                        ID: {{ $upt->id }}
                    </span>
                </div>

                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-xs">
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Nomor Induk UPT</dt>
                            <dd class="mt-1 font-mono font-black text-sm text-[#1B2632]">
                                UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Kabupaten Pembina</dt>
                            <dd class="mt-1 font-bold text-sm text-[#1B2632]">
                                Kabupaten {{ $upt->regency?->name ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Nama UPT Asal / Sejarah</dt>
                            <dd class="mt-1 font-extrabold text-sm text-[#1B2632]">
                                {{ $upt->upt_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Nama Desa Definitif Saat Ini</dt>
                            <dd class="mt-1 font-bold text-sm text-emerald-800">
                                {{ $upt->current_village_name ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Pola Budidaya / Usaha</dt>
                            <dd class="mt-1">
                                <span class="bg-[#EEE9DF] text-[#1B2632] font-bold px-2.5 py-1 rounded-lg text-xs border border-[#C9C1B1]/50 inline-block">
                                    {{ $upt->business_pattern ?: 'Umum' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">Status Validasi Dokumen</dt>
                            <dd class="mt-1">
                                @if($upt->is_verified)
                                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Terverifikasi Super Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Menunggu Verifikasi
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- KARTU 2: STATUS LEGALITAS PERTANAHAN & CATATAN MEDIASI (BPN) -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#A35139]"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            Status Agraria, SHM & Catatan Hambatan
                        </h3>
                    </div>
                    <a href="{{ route('admin.sertifikat-tanah.index', ['search' => $upt->upt_name]) }}" class="text-xs font-bold text-[#A35139] hover:underline flex items-center gap-1">
                        <span>Buka di Menu Sertipikat Tanah</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Status Sertipikat Hak Milik</span>
                            <div class="text-base font-extrabold text-[#1B2632] mt-1">
                                {{ $upt->shm_status ?? '100% SHM' }}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Pemantauan alas hak tanah warga transmigran di Kantor Pertanahan setempat.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Kategori Risiko & Kendala</span>
                            <div class="mt-1">
                                <x-status-badge :status="$upt->issue_status" mode="full" size="sm" />
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                @if($upt->issue_status === 'clean')
                                    Bebas sengketa, legalitas terverifikasi, dan alas hak SHM aman.
                                @elseif($upt->issue_status === 'warning')
                                    Terkendala tanggul/fasum atau batas administrasi (dalam pemantauan).
                                @else
                                    Tumpang tindih kawasan hutan (KLHK) atau klaim pihak ketiga (butuh mediasi).
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/60">
                        <span class="text-[10px] font-bold text-[#2C3B4D] uppercase tracking-wider block mb-1.5">
                            Catatan Khusus Hambatan Lahan / Rekomendasi Tindak Lanjut Mediasi:
                        </span>
                        <div class="text-xs text-[#1B2632] leading-relaxed font-medium bg-white p-3 rounded-lg border border-[#C9C1B1]/40">
                            {{ $upt->notes_issue ?: ($upt->issue_note ?: 'Tidak ada hambatan atau sengketa khusus yang dilaporkan pada lokasi UPT ini.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- KARTU 3: E-ARSIP BERKAS BAST & DOKUMEN HUKUM -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#1B2632]"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            E-Arsip BAST & Dokumen Legalitas ({{ $upt->documents->count() }})
                        </h3>
                    </div>
                    <a href="{{ route('admin.documents.index') }}" class="text-xs font-bold text-[#2C3B4D] hover:underline flex items-center gap-1">
                        <span>Repositori Berkas</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <div class="p-6">
                    @if($upt->documents->count() > 0)
                        <div class="divide-y divide-slate-100">
                            @foreach($upt->documents as $doc)
                                <div class="py-3 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-[#A35139]/15 text-[#A35139] flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div class="truncate">
                                            <div class="font-bold text-[#1B2632] truncate">
                                                {{ $doc->document_name ?? $doc->title ?? 'Dokumen BAST' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                Tipe: {{ $doc->document_type ?? 'BAST' }} • Diunggah: {{ $doc->created_at?->format('d M Y') ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('admin.documents.download', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            <span>Unduh</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-400">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-xs font-medium">Belum ada dokumen fisik BAST atau sertifikat yang diunggah untuk UPT ini.</p>
                            <a href="{{ route('admin.documents.index') }}" class="inline-block mt-3 text-xs font-bold text-[#A35139] hover:underline">
                                + Unggah Berkas BAST Baru
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: GEOSPASIAL, PETA INTERAKTIF & REGISTRI KELUARGA (1 SPAN) -->
        <div class="space-y-6">

            <!-- KARTU 4: PRATINJAU PETA GEOSPASIAL -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            Koordinat & Peta Spasial
                        </h3>
                    </div>
                    @if($upt->latitude && $upt->longitude)
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            Koordinat Valid
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            Belum Ada Titik
                        </span>
                    @endif
                </div>

                <div class="p-5 space-y-4">
                    <!-- Leaflet Mini Map Container -->
                    @if($upt->latitude && $upt->longitude)
                        <div id="upt-mini-map" class="w-full h-56 rounded-xl border border-[#C9C1B1]/60 shadow-inner z-10"></div>
                        <div class="flex items-center justify-between text-xs font-mono text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <span>Lat: <strong>{{ number_format($upt->latitude, 6) }}</strong></span>
                            <span>Long: <strong>{{ number_format($upt->longitude, 6) }}</strong></span>
                        </div>
                    @else
                        <div class="w-full h-44 rounded-xl bg-slate-100 border border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 p-4 text-center">
                            <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="text-xs font-semibold">Titik koordinat belum ditentukan</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Perbarui melalui menu Edit UPT</span>
                        </div>
                    @endif

                    <div class="pt-2">
                        <a href="{{ route('home', ['upt_id' => $upt->id]) }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs transition shadow-ambient-xs flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span>Eksplorasi di WebGIS Interaktif</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KARTU 5: REGISTRI KARTU KELUARGA TRANSMIGRAN -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFB162]"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            Registri Warga Transmigran
                        </h3>
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Kelola data nominal kartu keluarga, nama kepala keluarga, NIK, asal daerah, dan lampiran dokumen KK untuk UPT ini.
                    </p>

                    <div class="p-3.5 rounded-xl bg-[#EEE9DF]/50 border border-[#C9C1B1]/50 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Beban Penempatan Awal:</span>
                            <strong class="text-[#1B2632] font-black">{{ number_format($upt->placement_kk) }} KK</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Beban Serah Terima Pemda:</span>
                            <strong class="text-[#1B2632] font-black">{{ number_format($upt->handover_kk) }} KK</strong>
                        </div>
                    </div>

                    <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button type="button" 
                                onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'placement')" 
                                class="w-full py-2.5 px-3 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs transition shadow-ambient-xs flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Buka buku registri warga pada tahap penempatan awal">
                            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span>Registri Penempatan</span>
                        </button>
                        <button type="button" 
                                onclick="openRegistryModal({{ $upt->id }}, 'UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($upt->upt_name) }}', 'handover')" 
                                class="w-full py-2.5 px-3 rounded-xl bg-[#A35139] hover:bg-[#8a4430] text-white font-bold text-xs transition shadow-ambient-xs flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Buka buku registri warga pada tahap serah terima pemda">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Registri Serah Terima</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- KARTU 6: RIWAYAT USULAN PERUBAHAN DATA DARI DAERAH -->
            <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
                <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2C3B4D]"></span>
                        <h3 class="font-black text-[#1B2632] text-sm uppercase tracking-wide">
                            Usulan Draf Daerah ({{ $upt->changeRequests->count() }})
                        </h3>
                    </div>
                    <a href="{{ route('admin.verification.index') }}" class="text-xs font-bold text-[#2C3B4D] hover:underline">
                        Verifikasi
                    </a>
                </div>

                <div class="p-5">
                    @if($upt->changeRequests->count() > 0)
                        <div class="divide-y divide-slate-100 text-xs">
                            @foreach($upt->changeRequests->take(3) as $cr)
                                <div class="py-2.5 flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-[#1B2632]">
                                            Usulan Perubahan #{{ $cr->id }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Status: <strong class="uppercase text-amber-700">{{ $cr->status ?? 'Draft' }}</strong> • {{ $cr->created_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.verification.show', $cr->id) }}" class="text-xs text-[#A35139] font-bold hover:underline">
                                        Periksa
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 text-center py-3">
                            Tidak ada antrean usulan perubahan data dari operator kabupaten untuk UPT ini.
                        </p>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>

<!-- LEAFLET JS & CSS UNTUK PRATINJAU PETA SPASIAL -->
@if($upt->latitude && $upt->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ (float) $upt->latitude }};
            const lng = {{ (float) $upt->longitude }};

            const map = L.map('upt-mini-map', {
                center: [lat, lng],
                zoom: 12,
                zoomControl: true,
                attributionControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
            }).addTo(map);

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup("<b>UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</b><br>{{ addslashes($upt->upt_name) }}<br><small>Desa {{ addslashes($upt->current_village_name ?? '') }}</small>").openPopup();

            @if(!empty($upt->polygon_geojson))
                try {
                    const geojson = {!! json_encode($upt->polygon_geojson) !!};
                    if (geojson) {
                        const polyLayer = L.geoJSON(geojson, {
                            style: {
                                color: '#A35139',
                                weight: 2,
                                opacity: 0.8,
                                fillOpacity: 0.25,
                                fillColor: '#FFB162'
                            }
                        }).addTo(map);
                        map.fitBounds(polyLayer.getBounds(), { padding: [20, 20] });
                    }
                } catch(e) {
                    console.error("Gagal memuat poligon geospasial:", e);
                }
            @endif
        });
    </script>
@endif

@include('admin.registri_warga.registri_warga')
@endsection
