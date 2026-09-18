@extends('layouts.admin')

@section('title', 'Daftar Usulan Pemutakhiran UPT')
@section('header_title', 'Riwayat Pengajuan Draf Pemutakhiran')
@section('header_subtitle', 'Pantau status verifikasi dan catatan evaluasi dari Super Admin Provinsi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Filter -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('operator.requests.index') }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request()->has('status') ? 'bg-[#0B1849] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Usulan
            </a>
            <a href="{{ route('operator.requests.index', ['status' => 'pending']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Menunggu Review
            </a>
            <a href="{{ route('operator.requests.index', ['status' => 'approved']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'approved' ? 'bg-[#124D1C] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui
            </a>
            <a href="{{ route('operator.requests.index', ['status' => 'rejected']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak
            </a>
        </div>

        <a href="{{ route('operator.requests.create') }}" 
           class="bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md flex items-center gap-2 border border-[#E4B028]/40 self-start md:self-auto">
            <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Ajukan Draf Usulan Baru</span>
        </a>
    </div>

    <!-- Tabel Daftar Pengajuan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Tanggal Pengajuan</th>
                        <th class="px-4 py-3.5">Nama UPT</th>
                        <th class="px-4 py-3.5">Kategori Usulan</th>
                        <th class="px-4 py-3.5">Ringkasan Perubahan</th>
                        <th class="px-4 py-3.5 text-center">Status Verifikasi</th>
                        <th class="px-4 py-3.5">Catatan Reviewer Provinsi</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $index => $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                                {{ $requests->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $req->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-[#0B1849]">
                                {{ $req->uptLocation->upt_name ?? '-' }}
                                <span class="block text-[10px] text-slate-400 font-normal">
                                    {{ $req->uptLocation->current_village_name ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
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
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs truncate">
                                {{ $req->proposed_payload['submission_note'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
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
                            <td class="px-4 py-3.5 text-slate-500 max-w-xs truncate italic">
                                {{ $req->reviewer_note ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <a href="{{ route('operator.requests.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-[11px] transition">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                Tidak ada draf usulan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
