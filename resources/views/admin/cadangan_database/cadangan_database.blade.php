@extends('layouts.admin')

@section('title', 'Cadangan Basis Data & Pemeliharaan Server')
@section('header_title', 'Cadangan Basis Data & Pemeliharaan Server')
@section('header_subtitle', 'Pencadangan rutin (backup) database PostgreSQL/PostGIS, pengujian integritas spasial, dan monitoring storage arsip')

@section('content')
<div class="space-y-6">

    <!-- 1. STATUS KESEHATAN ENGINE SPASIAL & SERVER -->
    <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs space-y-4">
        <div class="border-b border-[#C9C1B1]/30 pb-3 flex items-center justify-between">
            <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#FFB162] animate-pulse"></span>
                <span>Status Kesehatan Engine Spasial & Server</span>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30">
                Sistem Operasional Normal
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric 1: DB Engine -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/50">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Basis Data & Geospasial</span>
                <div class="text-sm font-extrabold text-[#1B2632] mt-1">{{ $pgVersion }}</div>
                <div class="text-xs text-[#2C3B4D] font-bold mt-0.5">{{ $postgisVersion }}</div>
            </div>

            <!-- Metric 2: DB Size -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/50">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Kapasitas Basis Data</span>
                <div class="text-xl font-black text-[#1B2632] mt-1">{{ $dbSize }}</div>
                <div class="text-[10px] text-[#2C3B4D]/60 mt-0.5">Alokasi Server: 50 GB</div>
            </div>

            <!-- Metric 3: E-Arsip Storage -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/50">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Storage E-Arsip BAST</span>
                <div class="text-xl font-black text-[#A35139] mt-1">{{ $storageSize }}</div>
                <div class="text-[10px] text-[#2C3B4D]/60 mt-0.5">Alokasi Dokumen PDF: 200 GB</div>
            </div>

            <!-- Metric 4: Auto Backup Schedule -->
            <div class="p-4 rounded-xl bg-[#FFB162]/15 border border-[#FFB162]/40">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#8F4E0A] block">Jadwal Backup Otomatis</span>
                <div class="text-sm font-extrabold text-[#8F4E0A] mt-1">Setiap Hari 23:00 WITA</div>
                <div class="text-[10px] text-[#8F4E0A]/70 mt-0.5">Rotasi Otomatis 30 Hari</div>
            </div>
        </div>

        <!-- Tombol Aksi Pencadangan Manual & Uji Integritas -->
        <div class="pt-2 flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.backup.create') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    <span>💾 Cadangkan Sekarang (Full Backup SQL)</span>
                </button>
            </form>

            <form action="{{ route('admin.backup.test-integrity') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] font-bold text-xs px-4 py-2.5 rounded-xl transition border border-[#C9C1B1]/70 shadow-2xs flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>🔍 Uji Integritas Basis Data Spasial</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 2. DAFTAR ARSIP PENCADANGAN TERAKHIR -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/30 flex items-center justify-between bg-[#EEE9DF]/30">
            <div>
                <h3 class="text-base font-black text-[#1B2632] tracking-tight">
                    Daftar Arsip Berkas Pencadangan (.SQL)
                </h3>
                <p class="text-xs text-[#2C3B4D]/70 mt-0.5">
                    Berkas dump database memuat 124 data UPT, batas wilayah 9 kabupaten, dan koordinat spasial PostGIS
                </p>
            </div>
            <span class="text-xs font-bold text-[#1B2632] bg-[#1B2632]/10 px-3 py-1 rounded-xl border border-[#1B2632]/15">
                {{ count($backups) }} Berkas Tersimpan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#2C3B4D]">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Nama Berkas Cadangan</th>
                        <th class="px-5 py-3.5">Ukuran File</th>
                        <th class="px-5 py-3.5">Waktu Pembuatan (WITA)</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($backups as $index => $b)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <td class="px-5 py-3.5 text-center font-bold text-[#2C3B4D]/50">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-[#1B2632] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path></svg>
                                <span>{{ $b['name'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-[#2C3B4D] tabular-nums">
                                {{ $b['size'] }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-[#2C3B4D]/70">
                                {{ $b['created_at'] }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.backup.download', $b['name']) }}" 
                                       class="px-3 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] font-bold text-[11px] transition shadow-ambient-xs flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh</span>
                                    </a>

                                    <form action="{{ route('admin.backup.destroy', $b['name']) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas cadangan {{ $b['name'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-[#A35139]/15 hover:bg-[#A35139]/25 text-[#A35139] border border-[#A35139]/30 font-bold text-[11px] transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-[#2C3B4D]/60 font-medium">
                                Belum ada berkas pencadangan yang dibuat. Klik tombol "Cadangkan Sekarang" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-[#EEE9DF]/30 border-t border-[#C9C1B1]/30 text-xs text-[#2C3B4D]/75 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#FFB162] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>
                <strong>Catatan Keamanan:</strong> Seluruh berkas cadangan mencakup snapshot PostGIS berstandar WGS84 SRID 4326 dan aman untuk dipulihkan kembali ke server Diskominfo Kalsel kapan saja.
            </span>
        </div>
    </div>

</div>
@endsection
