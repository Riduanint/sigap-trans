@extends('layouts.admin')

@section('title', 'Antrean Verifikasi Draf Usulan')
@section('header_title', 'Antrean Verifikasi Draf Usulan Data UPT')
@section('header_subtitle', 'Tinjau, bandingkan data (Diff Check), dan beri persetujuan atas usulan pemutakhiran dari Operator Kabupaten')

@section('content')
<div class="space-y-6">

    <!-- 1. TABS STATUS FILTER & PENCARIAN -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.verification.index') }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ !request()->has('status') ? 'bg-[#0B1849] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Semua Usulan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request()->has('status') ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $counts['all'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'pending']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200' }}">
                <span class="w-2 h-2 rounded-full bg-amber-400 {{ $counts['pending'] > 0 ? 'animate-ping' : '' }}"></span>
                <span>Menunggu Verifikasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-200 text-amber-950 font-black">
                    {{ $counts['pending'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'approved']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'approved' ? 'bg-[#124D1C] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Disetujui</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'approved' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $counts['approved'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'rejected']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Ditolak</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'rejected' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $counts['rejected'] }}
                </span>
            </a>
        </div>

        <!-- Filter Kabupaten & Search -->
        <form action="{{ route('admin.verification.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <select name="regency_id" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                <option value="">-- Semua Kabupaten --</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                        {{ $reg->name }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama UPT..."
                       class="text-xs pl-8 pr-3 py-2 rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @if(request()->anyFilled(['status', 'regency_id', 'search']))
                <a href="{{ route('admin.verification.index') }}" class="text-xs font-bold text-rose-600 hover:underline px-2">
                    Reset
                </a>
            @endif
        </form>

    </div>

    <!-- 2. TABEL DAFTAR PERMOHONAN VERIFIKASI -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Waktu Pengajuan</th>
                        <th class="px-4 py-3.5">Asal Kabupaten</th>
                        <th class="px-4 py-3.5">Nama UPT</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Pengaju (Operator)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $index => $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-slate-400 tabular-nums">
                                {{ $requests->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-[#0B1849]">
                                {{ $req->uptLocation->regency->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                [No. {{ $req->uptLocation->upt_number }}] {{ $req->uptLocation->upt_name }}
                                <span class="block text-[10px] text-slate-400 font-normal">
                                    Desa: {{ $req->uptLocation->current_village_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
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
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-slate-800 block">{{ $req->user->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $req->user->nip ?? '' }}</span>
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
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <a href="{{ route('admin.verification.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition shadow-xs {{ $req->status === 'pending' ? 'bg-[#124D1C] hover:bg-emerald-800 text-white border border-[#E4B028]/40' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                    <svg class="w-3.5 h-3.5 {{ $req->status === 'pending' ? 'text-[#E4B028]' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    <span>{{ $req->status === 'pending' ? 'Diff Check & Verifikasi' : 'Lihat Rekam Jejak' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                Tidak ada draf usulan dalam antrean saat ini.
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
