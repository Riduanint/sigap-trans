@extends('layouts.admin')

@section('title', 'Portal Pertanahan & SHM Transmigrasi - Kanwil BPN Kalsel')
@section('header_title', 'Portal Verifikasi Pertanahan & Hak Atas Tanah (SHM)')
@section('header_subtitle', 'Integrasi Data Geospasial Transmigrasi untuk Percepatan Sertifikasi Hak Milik & GTRA Kanwil BPN Kalsel')

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER IDENTITAS MITRA BPN -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0B1849] via-slate-800 to-amber-900 p-6 text-white shadow-xl border border-white/10 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-[#E4B028]/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#E4B028] text-[#0B1849] flex items-center justify-center font-black text-xl shrink-0 shadow-lg border-2 border-white/30">
                    BPN
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#E4B028] bg-[#E4B028]/20 px-2.5 py-0.5 rounded-full border border-[#E4B028]/30">
                            Mitra Sektoral Pertanahan
                        </span>
                        <span class="text-xs text-slate-300 font-mono">Kementerian ATR / BPN</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-white mt-1">
                        Kantor Wilayah BPN Provinsi Kalimantan Selatan
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Fokus Operasional: Percepatan Sertifikasi Redistribusi Tanah & Gugus Tugas Reforma Agraria (GTRA) 124 UPT
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.excel') }}" 
                   class="bg-[#E4B028] hover:bg-[#d4a020] text-[#0B1849] font-black text-xs px-4 py-2.5 rounded-xl transition shadow-lg flex items-center gap-2 border border-[#E4B028]">
                    <svg class="w-4 h-4 text-[#0B1849]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Unduh Matriks Pertanahan (Excel)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. METRIK KUNCI SERTIFIKASI SHM -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: 100% SHM -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Sertifikasi Tuntas (100% SHM)</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-700 tabular-nums">{{ $shm100Count }}</span>
                <span class="text-xs text-slate-500 font-semibold">UPT Tuntas</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Alas hak SHM warga telah diserahkan penuh
            </div>
        </div>

        <!-- Card 2: Sebagian SHM -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Sertifikasi Bertahap</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-600 tabular-nums">{{ $shmPartialCount }}</span>
                <span class="text-xs text-slate-500 font-semibold">Sebagian SHM</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Dalam proses pengukuran batas bidang kadastral
            </div>
        </div>

        <!-- Card 3: Belum SHM / Kendala Hutan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Prioritas Khusus Reforma Agraria</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-rose-600 tabular-nums">{{ $criticalLandCount }}</span>
                <span class="text-xs text-slate-500 font-semibold">UPT Sengketa Hutan</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Menunggu SK Pelepasan Kawasan Hutan MenLHK
            </div>
        </div>

        <!-- Card 4: E-Arsip Digital BAST -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Dokumen Alas Hak Digital</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#0B1849] tabular-nums">{{ $totalBastFiles }}</span>
                <span class="text-xs text-slate-500 font-semibold">Berkas BAST/SK</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Tersimpan di vault e-arsip Disnakertrans
            </div>
        </div>
    </div>

    <!-- 3. TABEL VERIFIKASI PERTANAHAN & STATUS SHM 124 UPT -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Filter Header -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-[#0B1849] tracking-tight">
                    Matriks Status Legalitas Tanah & Sertifikasi Hak Milik (SHM)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data acuan terintegrasi lintas kementerian untuk validasi batas bidang transmigrasi
                </p>
            </div>

            <form action="{{ route('bpn.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="regency_id" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                    <option value="">-- Semua Kabupaten --</option>
                    @foreach($regencies as $reg)
                        <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                            {{ $reg->name }}
                        </option>
                    @endforeach
                </select>

                <select name="shm_status" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                    <option value="">-- Status SHM --</option>
                    <option value="100% SHM" {{ request('shm_status') == '100% SHM' ? 'selected' : '' }}>100% SHM</option>
                    <option value="Sebagian SHM" {{ request('shm_status') == 'Sebagian SHM' ? 'selected' : '' }}>Sebagian SHM</option>
                    <option value="Belum SHM" {{ request('shm_status') == 'Belum SHM' ? 'selected' : '' }}>Belum SHM</option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama UPT / desa..."
                       class="text-xs px-3 py-2 rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">

                <button type="submit" class="px-3 py-2 rounded-xl bg-[#0B1849] text-white text-xs font-bold">
                    Cari
                </button>

                @if(request()->anyFilled(['regency_id', 'shm_status', 'search']))
                    <a href="{{ route('bpn.dashboard') }}" class="text-xs font-bold text-rose-600 hover:underline px-1">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Nama UPT Historis</th>
                        <th class="px-4 py-3.5">Desa Definitif</th>
                        <th class="px-4 py-3.5">Kabupaten</th>
                        <th class="px-4 py-3.5 text-center">Status SHM</th>
                        <th class="px-4 py-3.5 text-center">Indikator Lahan</th>
                        <th class="px-4 py-3.5">Catatan Permasalahan / Alas Hak</th>
                        <th class="px-4 py-3.5 text-center">Dokumen BAST</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uptLocations as $index => $upt)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-slate-400 tabular-nums">
                                {{ $upt->upt_number }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-[#0B1849]">
                                {{ $upt->upt_name }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 font-medium">
                                {{ $upt->current_village_name }}
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700">
                                {{ $upt->regency->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @php $shm = $upt->shm_status ?? '100% SHM'; @endphp
                                @if($shm === '100% SHM')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ 100% SHM
                                    </span>
                                @elseif($shm === 'Sebagian SHM')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        ⏱ Sebagian SHM
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        ✕ Belum SHM
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($upt->issue_status === 'clean')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700">
                                        🟢 Clean
                                    </span>
                                @elseif($upt->issue_status === 'warning')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700">
                                        🟡 Warning
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 animate-pulse">
                                        🔴 Sengketa
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs truncate">
                                {{ $upt->issue_note ?? 'Hak milik aman, fasos/fasum diserahkan ke Pemda' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($upt->documents->count() > 0)
                                    <a href="{{ route('admin.documents.download', $upt->documents->first()->id) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#0B1849] hover:bg-slate-800 text-white font-bold text-[11px] transition shadow-xs">
                                        <svg class="w-3 h-3 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span>Unduh BAST</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px] italic">Belum Diunggah</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                Tidak ada data UPT yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($uptLocations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $uptLocations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
