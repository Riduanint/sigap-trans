@extends('layouts.admin')

@section('title', 'Monitoring Sertipikasi Tanah & Legalisasi Aset')
@section('header_title', 'Monitoring Sertipikasi Tanah & Agraria (BPN)')
@section('header_subtitle', 'Pemantauan Status SHM, Alas Hak, Progres PTSL, & Mediasi Kawasan Hutan 124 UPT di Kalimantan Selatan')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. KARTU METRIK UTAMA AGRARIA & LEGALISASI TANAH                          -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Kartu 1: 100% SHM -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Status SHM Penuh</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-emerald-700 tabular-nums">
                    {{ $shm100Count }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalUpt }} UPT</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    {{ round(($shm100Count / max(1, $totalUpt)) * 100, 1) }}% Bersertipikat Hak Milik Lengkap
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Legalitas:</span>
                <strong class="text-emerald-800 font-bold">Terbit Sertipikat SHM</strong>
            </div>
        </div>

        <!-- Kartu 2: Sebagian SHM / Proses BPN -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Proses Pendaftaran</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-amber-800 tabular-nums">
                    {{ $shmPartialCount }} <span class="text-xs font-semibold text-slate-400">UPT</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Tahap Pengukuran & Redistribusi Tanah
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Program:</span>
                <strong class="text-amber-900 font-bold">PTSL & TORA Kanwil</strong>
            </div>
        </div>

        <!-- Kartu 3: Belum SHM -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Belum Bersertipikat</span>
                <span class="p-1.5 rounded-lg bg-slate-100 text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-slate-800 tabular-nums">
                    {{ $shmNoneCount }} <span class="text-xs font-semibold text-slate-400">UPT</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Masih Hak Pengelolaan (HPL) / Belum Terbit
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Rekomendasi:</span>
                <strong class="text-slate-700 font-bold">Perlu Pengusulan Baru</strong>
            </div>
        </div>

        <!-- Kartu 4: Kasus Kritis Sengketa / Kawasan Hutan -->
        <div class="p-5 rounded-2xl bg-white border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Lokasi Kritis / Mediasi</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-rose-700 tabular-nums">
                    {{ $criticalLandCount }} <span class="text-xs font-semibold text-slate-400">Lokasi Kritis</span>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-medium">
                    Tumpang Tindih Kawasan Hutan / Klaim Lahan
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Prioritas:</span>
                <strong class="text-rose-700 font-bold">Mediasi Terpadu BPKH & BPN</strong>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. FILTER MULTI-KRITERIA & TOOLBAR EKSPOR                                -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-5 space-y-4">
        <form method="GET" action="{{ route('admin.sertifikat-tanah.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Filter Kabupaten -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1 tracking-wider">Kabupaten</label>
                <select name="regency_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] bg-white text-slate-800 font-bold">
                    <option value="">Semua Kabupaten (9)</option>
                    @foreach($regencies as $reg)
                        <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                            {{ $reg->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status SHM -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1 tracking-wider">Status Sertipikat SHM</label>
                <select name="shm_status" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] bg-white text-slate-800 font-bold">
                    <option value="">Semua Status SHM</option>
                    <option value="100% SHM" {{ request('shm_status') == '100% SHM' ? 'selected' : '' }}>100% SHM (Penuh)</option>
                    <option value="Sebagian SHM" {{ request('shm_status') == 'Sebagian SHM' ? 'selected' : '' }}>Sebagian SHM</option>
                    <option value="Proses BPN" {{ request('shm_status') == 'Proses BPN' ? 'selected' : '' }}>Proses BPN</option>
                    <option value="Belum SHM" {{ request('shm_status') == 'Belum SHM' ? 'selected' : '' }}>Belum SHM</option>
                    <option value="Sengketa" {{ request('shm_status') == 'Sengketa' ? 'selected' : '' }}>Sengketa</option>
                </select>
            </div>

            <!-- Filter Isu / Kendala Lahan -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1 tracking-wider">Status Kondisi Lahan</label>
                <select name="issue_status" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] bg-white text-slate-800 font-bold">
                    <option value="">Semua Status Lahan</option>
                    <option value="clean" {{ request('issue_status') == 'clean' ? 'selected' : '' }}>🟢 Clean & Clear</option>
                    <option value="warning" {{ request('issue_status') == 'warning' ? 'selected' : '' }}>🟡 Waspada / Monitoring</option>
                    <option value="critical" {{ request('issue_status') == 'critical' ? 'selected' : '' }}>🔴 Kritis / Prioritas Mediasi</option>
                </select>
            </div>

            <!-- Pencarian Teks -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1 tracking-wider">Cari Nama UPT / Desa</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama UPT..." class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162]">
                    <svg class="w-4 h-4 text-[#C9C1B1] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-white font-bold text-xs transition shadow-ambient-xs">
                    Filter
                </button>
                @if(request()->anyFilled(['regency_id', 'shm_status', 'issue_status', 'search']))
                    <a href="{{ route('admin.sertifikat-tanah.index') }}" class="p-2 rounded-xl border border-[#C9C1B1] text-slate-600 hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
                <a href="{{ route('admin.sertifikat-tanah.export', request()->query()) }}" class="py-2 px-3 rounded-xl bg-white border border-[#C9C1B1] hover:bg-[#EEE9DF]/60 text-[#1B2632] font-bold text-xs transition flex items-center gap-1.5 shrink-0 shadow-ambient-xs" title="Ekspor Rekap CSV Pertanahan">
                    <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span class="hidden sm:inline">CSV</span>
                </a>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. TABEL MONITORING STATUS HAK MILIK & LEGALITAS LAHAN UPT                -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex items-center justify-between">
            <h3 class="font-bold text-[#1B2632] text-sm uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Daftar Status Sertipikat Tanah & Legalitas Lahan per UPT</span>
            </h3>
            <span class="text-xs text-slate-500">
                Menampilkan {{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} UPT
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] font-bold uppercase text-[10px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-4">Nama UPT & Wilayah</th>
                        <th class="py-3 px-3 text-center">Tahun Penempatan</th>
                        <th class="py-3 px-3 text-center">Kapasitas (KK)</th>
                        <th class="py-3 px-3 text-center">Status Sertipikat SHM</th>
                        <th class="py-3 px-3 text-center">Kategori Isu</th>
                        <th class="py-3 px-4">Catatan Kendala Pertanahan / Hutan</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uptLocations as $idx => $u)
                        @php
                            $shmStatus = $u->shm_status ?? '100% SHM';
                            $issueStatus = $u->issue_status ?? 'clean';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition" id="row-upt-{{ $u->id }}">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">
                                {{ $uptLocations->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#1B2632]/10 text-[#1B2632]">
                                        UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="font-extrabold text-slate-900">{{ $u->upt_name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $u->current_village_name ?? '-' }} &bull; <span class="font-bold text-slate-700">{{ $u->regency?->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                {{ $u->placement_year ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-700">
                                <div><strong class="text-slate-900">{{ number_format($u->placement_kk ?? 0, 0, ',', '.') }}</strong> Awal</div>
                                <div class="text-[10px] text-slate-400">{{ number_format($u->handover_kk ?? 0, 0, ',', '.') }} Serah Terima</div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if(str_contains(strtolower($shmStatus), '100%') || str_contains(strtolower($shmStatus), 'sudah'))
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        100% SHM
                                    </span>
                                @elseif(str_contains(strtolower($shmStatus), 'sebagian') || str_contains(strtolower($shmStatus), 'proses'))
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $shmStatus }}
                                    </span>
                                @elseif(str_contains(strtolower($shmStatus), 'sengketa'))
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                        Sengketa
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-700 border border-slate-200">
                                        Belum SHM
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <x-status-badge :status="$issueStatus" />
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-sm truncate text-[11px]" title="{{ $u->notes_issue }}">
                                {{ $u->notes_issue ?: '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick="openEditLandModal({{ $u->id }}, 'UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }} - {{ addslashes($u->upt_name) }}', '{{ addslashes($shmStatus) }}', '{{ $issueStatus }}', '{{ addslashes($u->notes_issue ?? '') }}')"
                                            class="p-1.5 rounded-lg text-[#1B2632] hover:bg-[#EEE9DF] transition border border-[#C9C1B1]/60 shadow-2xs" 
                                            title="Perbarui Status Pertanahan">
                                        <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <a href="{{ route('admin.upt.show', $u->id) }}" 
                                       class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition border border-[#C9C1B1]/60 shadow-2xs" 
                                       title="Lihat Detail Profil UPT">
                                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                                Tidak ada data UPT yang sesuai dengan kriteria filter pertanahan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div class="p-4 border-t border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <div class="text-xs text-[#2C3B4D]/80 font-medium">
                Menampilkan <strong class="text-[#1B2632]">{{ $uptLocations->firstItem() ?? 0 }}</strong> - <strong class="text-[#1B2632]">{{ $uptLocations->lastItem() ?? 0 }}</strong> dari total <strong class="text-[#1B2632]">{{ $uptLocations->total() }}</strong> data UPT (10 per halaman)
            </div>
            <div class="inline-flex items-center bg-white text-[#1B2632] px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-xs border border-[#C9C1B1] self-center sm:self-auto">
                {{ $uptLocations->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL PEMBARUAN STATUS SERTIPIKAT & KENDALA PERTANAHAN                    -->
<!-- ========================================================================= -->
<div id="modal-land-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity" onclick="closeEditLandModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#C9C1B1]">
            <div class="bg-[#1B2632] text-white p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#EEE9DF]">
                        Pembaruan Status Sertipikat Tanah & Legalitas
                    </h3>
                    <p class="text-xs text-[#C9C1B1] mt-0.5" id="modal-upt-title">
                        UPT-000 - Nama UPT
                    </p>
                </div>
                <button type="button" onclick="closeEditLandModal()" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="form-land-edit" onsubmit="submitLandEdit(event)" class="p-6 space-y-4">
                <input type="hidden" id="edit-upt-id" value="">

                <!-- Pilihan Status Sertipikat SHM -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Status Sertipikat SHM <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-shm-status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                        <option value="100% SHM">100% SHM (Penuh / Selesai)</option>
                        <option value="Sebagian SHM">Sebagian SHM (Sebagian Lahan Usaha/Pekarangan)</option>
                        <option value="Proses BPN">Proses BPN (PTSL / Redistribusi Tanah)</option>
                        <option value="Belum SHM">Belum SHM (Masih HPL / Baru Penempatan)</option>
                        <option value="Sengketa">Sengketa / Klaim Pihak Ketiga</option>
                    </select>
                </div>

                <!-- Pilihan Kategori Isu/Hambatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tingkat Risiko & Isu Kawasan <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-issue-status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] font-bold bg-white focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]">
                        <option value="clean">🟢 Clean & Clear (SHM Tuntas, Bebas Sengketa)</option>
                        <option value="warning">🟡 Waspada / Monitoring (Kendala Tanggul / Administrasi)</option>
                        <option value="critical">🔴 Kritis / Prioritas Mediasi (Tumpang Tindih Kawasan Hutan/Klaim)</option>
                    </select>
                </div>

                <!-- Catatan Hambatan / Koordinasi BPN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Catatan Hambatan / Rekomendasi Mediasi BPN
                    </label>
                    <textarea id="edit-notes-issue" rows="3" placeholder="Contoh: Perlu koordinasi pelepasan kawasan hutan bersama BPKH Wilayah V atau percepatan penerbitan NIB..." class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#C9C1B1] focus:outline-hidden focus:ring-2 focus:ring-[#FFB162]"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditLandModal()" class="px-4 py-2.5 rounded-xl border border-[#C9C1B1] text-slate-700 hover:bg-[#EEE9DF]/60 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-land" class="px-5 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white font-bold text-xs transition shadow-ambient-xs flex items-center gap-2 cursor-pointer">
                        <span id="btn-save-land-text">Simpan Status Pertanahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditLandModal(id, title, shm, issue, notes) {
    document.getElementById('edit-upt-id').value = id;
    document.getElementById('modal-upt-title').textContent = title;
    document.getElementById('edit-shm-status').value = shm || '100% SHM';
    document.getElementById('edit-issue-status').value = issue || 'clean';
    document.getElementById('edit-notes-issue').value = notes || '';

    document.getElementById('modal-land-edit').classList.remove('hidden');
}

function closeEditLandModal() {
    document.getElementById('modal-land-edit').classList.add('hidden');
}

function submitLandEdit(e) {
    e.preventDefault();

    const id = document.getElementById('edit-upt-id').value;
    const btn = document.getElementById('btn-save-land');
    const btnText = document.getElementById('btn-save-land-text');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    btn.disabled = true;
    btnText.textContent = 'Menyimpan...';

    fetch(`/admin/sertifikat-tanah/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            shm_status: document.getElementById('edit-shm-status').value,
            issue_status: document.getElementById('edit-issue-status').value,
            notes_issue: document.getElementById('edit-notes-issue').value.trim(),
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeEditLandModal();
            location.reload();
        } else {
            alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan status pertanahan.');
    })
    .finally(() => {
        btn.disabled = false;
        btnText.textContent = 'Simpan Status Pertanahan';
    });
}
</script>
@endsection
