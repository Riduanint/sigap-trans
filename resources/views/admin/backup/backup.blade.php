@extends('layouts.admin')

@section('title', 'Cadangan Basis Data & Pemeliharaan Server')
@section('header_title', 'Cadangan Basis Data & Pemeliharaan Server')
@section('header_subtitle', 'Pencadangan rutin (backup) database PostgreSQL/PostGIS, pengujian integritas spasial, dan monitoring storage arsip')

@section('content')
<div class="space-y-6">

    <!-- 1. STATUS KESEHATAN ENGINE SPASIAL & SERVER -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <h3 class="text-sm font-black text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Status Kesehatan Engine Spasial & Server</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                Sistem Operasional Normal
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric 1: DB Engine -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Basis Data & Geospasial</span>
                <div class="text-sm font-extrabold text-[#0B1849] mt-1">{{ $pgVersion }}</div>
                <div class="text-xs text-emerald-700 font-bold mt-0.5">{{ $postgisVersion }}</div>
            </div>

            <!-- Metric 2: DB Size -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kapasitas Basis Data</span>
                <div class="text-xl font-black text-slate-900 mt-1">{{ $dbSize }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Alokasi Server: 50 GB</div>
            </div>

            <!-- Metric 3: E-Arsip Storage -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Storage E-Arsip BAST</span>
                <div class="text-xl font-black text-[#124D1C] mt-1">{{ $storageSize }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Alokasi Dokumen PDF: 200 GB</div>
            </div>

            <!-- Metric 4: Auto Backup Schedule -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jadwal Backup Otomatis</span>
                <div class="text-sm font-extrabold text-amber-900 mt-1">Setiap Hari 23:00 WITA</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Rotasi Otomatis 30 Hari</div>
            </div>
        </div>

        <!-- Tombol Aksi Pencadangan Manual & Uji Integritas -->
        <div class="pt-2 flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.backup.create') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md flex items-center gap-2 border border-[#E4B028]">
                    <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    <span>💾 Cadangkan Sekarang (Full Backup SQL)</span>
                </button>
            </form>

            <form action="{{ route('admin.backup.test-integrity') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs px-4 py-2.5 rounded-xl transition border border-slate-300 shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>🔍 Uji Integritas Basis Data Spasial</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 2. DAFTAR ARSIP PENCADANGAN TERAKHIR -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-[#0B1849] tracking-tight">
                    Daftar Arsip Berkas Pencadangan (.SQL)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Berkas dump database memuat 124 data UPT, batas wilayah 9 kabupaten, dan koordinat spasial PostGIS
                </p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                {{ count($backups) }} Berkas Tersimpan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Nama Berkas Cadangan</th>
                        <th class="px-5 py-3.5">Ukuran File</th>
                        <th class="px-5 py-3.5">Waktu Pembuatan (WITA)</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($backups as $index => $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 text-center font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-[#0B1849] flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path></svg>
                                <span>{{ $b['name'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-700 tabular-nums">
                                {{ $b['size'] }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500">
                                {{ $b['created_at'] }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.backup.download', $b['name']) }}" 
                                       class="px-3 py-1.5 rounded-lg bg-[#0B1849] hover:bg-slate-800 text-white font-bold text-[11px] transition shadow-xs flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh</span>
                                    </a>

                                    <form action="{{ route('admin.backup.destroy', $b['name']) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas cadangan {{ $b['name'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                Belum ada berkas pencadangan yang dibuat. Klik tombol "Cadangkan Sekarang" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>
                <strong>Catatan Keamanan:</strong> Seluruh berkas cadangan mencakup snapshot PostGIS berstandar WGS84 SRID 4326 dan aman untuk dipulihkan kembali ke server Diskominfo Kalsel kapan saja.
            </span>
        </div>
    </div>

</div>
@endsection
