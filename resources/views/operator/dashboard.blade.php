@extends('layouts.admin')

@section('title', 'Dasbor Operator - ' . ($regency->name ?? 'Kabupaten'))
@section('header_title', 'Ruang Kendali Operator ' . ($regency->name ?? 'Kabupaten'))
@section('header_subtitle', 'Pusat Pemutakhiran Data Lapangan & Pengajuan Berkas BAST Wilayah ' . ($regency->name ?? ''))

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER IDENTITAS KABUPATEN -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0B1849] via-[#124D1C] to-[#0B1849] p-6 text-white shadow-xl border border-white/10 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-[#E4B028]/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#E4B028]/20 border border-[#E4B028] flex items-center justify-center text-[#E4B028] font-black text-xl shrink-0 shadow-inner">
                    {{ $regency->roman_code ?? 'I' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#E4B028] bg-[#E4B028]/20 px-2.5 py-0.5 rounded-full border border-[#E4B028]/30">
                            Wilayah Kerja Operasional
                        </span>
                        <span class="text-xs text-slate-300">|</span>
                        <span class="text-xs text-slate-300 font-mono">Kode Wilayah: {{ $regency->roman_code ?? 'I' }}</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-white mt-1">
                        {{ $regency->name ?? 'Kabupaten Tapin' }}
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Operator Pelaksana: <span class="font-bold text-white">{{ $user->name }}</span> (NIP: {{ $user->nip ?? '-' }})
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('operator.requests.create') }}" 
                   class="bg-[#E4B028] hover:bg-[#d4a020] text-[#0B1849] font-black text-xs px-4 py-2.5 rounded-xl transition shadow-lg flex items-center gap-2 border border-[#E4B028]">
                    <svg class="w-4 h-4 text-[#0B1849]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Ajukan Draf Usulan Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. METRIK UTAMA KABUPATEN & STATUS USULAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total UPT -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit UPT Lokal</span>
                <span class="p-2 rounded-xl bg-[#0B1849]/10 text-[#0B1849]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#0B1849] tabular-nums">{{ $totalUpt }}</span>
                <span class="text-xs text-slate-500 font-semibold">Lokasi UPT</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">🟢 {{ $cleanCount }} Clean</span>
                <span class="inline-flex items-center gap-1 text-amber-700 font-bold">🟡 {{ $warningCount }} Warning</span>
                <span class="inline-flex items-center gap-1 text-rose-700 font-bold">🔴 {{ $criticalCount }} Kritis</span>
            </div>
        </div>

        <!-- Card 2: Penempatan KK -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Penempatan</span>
                <span class="p-2 rounded-xl bg-[#124D1C]/10 text-[#124D1C]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#124D1C] tabular-nums">{{ number_format($totalPlacementKk, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 font-semibold">KK Warga</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Sejak awal kedatangan penempatan transmigran
            </div>
        </div>

        <!-- Card 3: Serah Terima Pemda -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Serah Terima Pemda</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800 tabular-nums">{{ number_format($totalHandoverKk, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 font-semibold">KK Diserahkan</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Telah menjadi desa otonom binaan Pemda
            </div>
        </div>

        <!-- Card 4: Status Draf Usulan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Draf Usulan</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-600 tabular-nums">{{ $requestsCount['pending'] }}</span>
                <span class="text-xs text-slate-500 font-semibold">Menunggu Verifikasi</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-2">
                <span class="text-emerald-700 font-bold">✓ {{ $requestsCount['approved'] }} Disetujui</span>
                <span>•</span>
                <span class="text-rose-600 font-bold">✕ {{ $requestsCount['rejected'] }} Ditolak</span>
            </div>
        </div>
    </div>

    <!-- 3. TABEL DAFTAR UPT KABUPATEN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-[#0B1849] tracking-tight flex items-center gap-2">
                    <span>Daftar Unit Pemukiman Transmigrasi (UPT)</span>
                    <span class="bg-[#124D1C] text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                        {{ $totalUpt }} Unit
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Klik tombol aksi "Ajukan Pemutakhiran" untuk mengajukan perubahan data lapangan atau berkas BAST.
                </p>
            </div>
            <a href="{{ route('operator.requests.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#124D1C] hover:bg-emerald-800 text-white text-xs font-bold transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Ajukan Usulan Baru</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Nama UPT Historis</th>
                        <th class="px-4 py-3.5">Desa Definitif</th>
                        <th class="px-4 py-3.5">Pola Usaha</th>
                        <th class="px-4 py-3.5 text-center">Penempatan</th>
                        <th class="px-4 py-3.5 text-center">Serah Terima</th>
                        <th class="px-4 py-3.5 text-center">Status Lahan</th>
                        <th class="px-4 py-3.5 text-center">Status SHM</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uptLocations as $upt)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 text-center font-bold text-slate-400 tabular-nums">
                                {{ $upt->upt_number }}
                            </td>
                            <td class="px-4 py-3 font-bold text-[#0B1849]">
                                {{ $upt->upt_name }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-medium">
                                {{ $upt->current_village_name }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10px] border border-slate-200">
                                    {{ $upt->business_pattern }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums">
                                <span class="font-bold text-[#124D1C]">{{ number_format($upt->placement_kk, 0, ',', '.') }} KK</span>
                                <span class="block text-[10px] text-slate-400">Th. {{ $upt->placement_year }}</span>
                            </td>
                            <td class="px-4 py-3 text-center tabular-nums">
                                <span class="font-bold text-slate-800">{{ number_format($upt->handover_kk, 0, ',', '.') }} KK</span>
                                <span class="block text-[10px] text-slate-400">Th. {{ $upt->handover_year ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($upt->issue_status === 'clean')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        🟢 Clean
                                    </span>
                                @elseif($upt->issue_status === 'warning')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        🟡 Warning
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        🔴 Sengketa
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[11px] font-semibold text-slate-600">
                                    {{ $upt->shm_status ?? '100% SHM' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('operator.requests.create', ['upt_id' => $upt->id]) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#124D1C] hover:bg-emerald-800 text-white font-bold text-[11px] transition shadow-xs">
                                    <svg class="w-3 h-3 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span>Ajukan Usulan</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data UPT terdaftar di kabupaten ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. TABEL RIWAYAT DRAF USULAN TERAKHIR OLEH OPERATOR -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-[#0B1849] tracking-tight">
                    Riwayat Pengajuan Draf Pemutakhiran Terbaru
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Memantau alur verifikasi draf oleh Super Admin Provinsi Kalsel
                </p>
            </div>
            <a href="{{ route('operator.requests.index') }}" class="text-xs font-extrabold text-[#124D1C] hover:underline">
                Lihat Semua Usulan &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Nama UPT</th>
                        <th class="px-4 py-3">Jenis Usulan</th>
                        <th class="px-4 py-3">Catatan Pengajuan Operator</th>
                        <th class="px-4 py-3 text-center">Status Verifikasi</th>
                        <th class="px-4 py-3">Catatan Provinsi</th>
                        <th class="px-4 py-3 text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-bold text-[#0B1849]">
                                {{ $req->uptLocation->upt_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($req->request_type === 'DATA_UPDATE')
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 font-bold text-[10px] border border-blue-200">
                                        Data Lapangan
                                    </span>
                                @elseif($req->request_type === 'LEGAL_ISSUE')
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-800 font-bold text-[10px] border border-rose-200">
                                        Masalah Lahan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 font-bold text-[10px] border border-purple-200">
                                        Berkas BAST Baru
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                                {{ $req->proposed_payload['submission_note'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 max-w-xs truncate italic">
                                {{ $req->reviewer_note ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('operator.requests.show', $req->id) }}" 
                                   class="text-[#124D1C] hover:text-emerald-800 font-bold text-xs underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada draf pengajuan usulan. Klik "Ajukan Draf Usulan Baru" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
