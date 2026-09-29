@extends('layouts.admin')

@section('title', 'Antrean Verifikasi Draf Usulan')
@section('header_title', 'Antrean Verifikasi Draf Usulan Data UPT')
@section('header_subtitle', 'Tinjau, bandingkan data (Diff Check), dan beri persetujuan atas usulan pemutakhiran dari Operator Kabupaten')

@section('content')
<div class="space-y-6">

    <!-- 1. TABS STATUS FILTER & PENCARIAN -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.verification.index') }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ !request()->has('status') ? 'bg-[#1B2632] text-[#EEE9DF] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/40 border border-[#C9C1B1]/60' }}">
                <span>Semua Usulan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request()->has('status') ? 'bg-white/20 text-white' : 'bg-[#C9C1B1]/40 text-[#1B2632]' }}">
                    {{ $counts['all'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'pending']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'pending' ? 'bg-[#FFB162] text-[#1B2632] font-black shadow-ambient-xs' : 'bg-[#FFB162]/20 text-[#8F4E0A] hover:bg-[#FFB162]/30 border border-[#FFB162]/40' }}">
                <span class="w-2 h-2 rounded-full bg-[#8F4E0A] {{ $counts['pending'] > 0 ? 'animate-ping' : '' }}"></span>
                <span>Menunggu Verifikasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'pending' ? 'bg-[#1B2632] text-[#FFB162]' : 'bg-[#FFB162]/40 text-[#8F4E0A]' }} font-black">
                    {{ $counts['pending'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'approved']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'approved' ? 'bg-[#2C3B4D] text-[#EEE9DF] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/40 border border-[#C9C1B1]/60' }}">
                <span>Disetujui</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'approved' ? 'bg-white/20 text-white' : 'bg-[#C9C1B1]/40 text-[#1B2632]' }}">
                    {{ $counts['approved'] }}
                </span>
            </a>

            <a href="{{ route('admin.verification.index', ['status' => 'rejected']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('status') === 'rejected' ? 'bg-[#A35139] text-[#EEE9DF] shadow-ambient-xs' : 'bg-[#A35139]/15 text-[#A35139] hover:bg-[#A35139]/25 border border-[#A35139]/30' }}">
                <span>Ditolak</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'rejected' ? 'bg-white/20 text-white' : 'bg-[#A35139]/20 text-[#A35139]' }}">
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
                    class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                <option value="">-- Semua Kabupaten --</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                        {{ $reg->name }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama UPT..."
                       class="text-xs pl-8 pr-3 py-2 rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                <svg class="w-4 h-4 text-[#2C3B4D]/40 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @if(request()->anyFilled(['status', 'regency_id', 'search']))
                <a href="{{ route('admin.verification.index') }}" class="text-xs font-bold text-[#A35139] hover:underline px-2">
                    Reset
                </a>
            @endif
        </form>

    </div>

    <!-- 2. TABEL DAFTAR PERMOHONAN VERIFIKASI -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#2C3B4D]">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[10px] tracking-wider">
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
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($requests as $index => $req)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-[#2C3B4D]/50 tabular-nums">
                                {{ $requests->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-[#2C3B4D]/70 whitespace-nowrap">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3.5 font-bold text-[#1B2632]">
                                {{ $req->uptLocation->regency->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-extrabold text-[#1B2632]">
                                [No. {{ $req->uptLocation->upt_number }}] {{ $req->uptLocation->upt_name }}
                                <span class="block text-[10px] text-[#2C3B4D]/60 font-normal">
                                    Desa: {{ $req->uptLocation->current_village_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($req->request_type === 'REGISTRY_SYNC')
                                    <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 font-bold text-[10px] border border-purple-300">
                                        Buku Registri Warga
                                    </span>
                                @elseif($req->request_type === 'DATA_UPDATE')
                                    <span class="px-2 py-0.5 rounded-md bg-[#2C3B4D]/10 text-[#2C3B4D] font-bold text-[10px] border border-[#2C3B4D]/20">
                                        Data Lapangan
                                    </span>
                                @elseif($req->request_type === 'LEGAL_ISSUE')
                                    <span class="px-2 py-0.5 rounded-md bg-[#A35139]/15 text-[#A35139] font-bold text-[10px] border border-[#A35139]/30">
                                        Masalah Lahan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-[#FFB162]/20 text-[#8F4E0A] font-bold text-[10px] border border-[#FFB162]/40">
                                        Berkas BAST Baru
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-[#1B2632] block">{{ $req->user->name ?? '-' }}</span>
                                <span class="text-[10px] text-[#2C3B4D]/60 font-mono">{{ $req->user->nip ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#8F4E0A] animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <a href="{{ route('admin.verification.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition shadow-ambient-xs {{ $req->status === 'pending' ? 'bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] border border-[#A35139]' : 'bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] border border-[#C9C1B1]/70' }}">
                                    <svg class="w-3.5 h-3.5 {{ $req->status === 'pending' ? 'text-[#FFB162]' : 'text-[#2C3B4D]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    <span>{{ $req->status === 'pending' ? 'Diff Check & Verifikasi' : 'Lihat Rekam Jejak' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-[#2C3B4D]/60 font-medium">
                                Tidak ada draf usulan dalam antrean saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-[#C9C1B1]/40 bg-[#EEE9DF]/20">
                {{ $requests->links('admin.partials.pagination') }}
            </div>
        @endif
    </div>

</div>
@endsection
