@extends('layouts.admin')

@section('title', 'Daftar Usulan Pemutakhiran UPT')
@section('header_title', 'Riwayat Pengajuan Draf Pemutakhiran')
@section('header_subtitle', 'Pantau status verifikasi dan catatan evaluasi dari Super Admin Provinsi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Filter -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('operator.requests.index', array_merge(request()->except('status', 'page'))) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ !request()->has('status') ? 'bg-[#1B2632] text-[#EEE9DF] border-[#1B2632] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/50 border-[#C9C1B1]/60' }}">
                Semua Usulan
            </a>
            <a href="{{ route('operator.requests.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ request('status') === 'pending' ? 'bg-[#FFB162] text-[#1B2632] font-black border-[#FFB162] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/50 border-[#C9C1B1]/60' }}">
                Menunggu Review
            </a>
            <a href="{{ route('operator.requests.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ request('status') === 'approved' ? 'bg-[#2C3B4D] text-white border-[#2C3B4D] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/50 border-[#C9C1B1]/60' }}">
                Disetujui
            </a>
            <a href="{{ route('operator.requests.index', array_merge(request()->except('page'), ['status' => 'rejected'])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ request('status') === 'rejected' ? 'bg-[#A35139] text-white border-[#A35139] shadow-ambient-xs' : 'bg-[#EEE9DF] text-[#2C3B4D] hover:bg-[#C9C1B1]/50 border-[#C9C1B1]/60' }}">
                Ditolak
            </a>

            <!-- Filter Wilayah Kabupaten -->
            @if(isset($regencies))
                <div class="ml-2 relative">
                    <select onchange="window.location.href = '{{ request()->fullUrlWithQuery(['regency_id' => '']) }}' + this.value"
                            class="text-xs font-bold rounded-xl border border-[#C9C1B1] bg-white py-1.5 pl-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] appearance-none cursor-pointer shadow-2xs">
                        <option value="all">📍 Semua Wilayah (9 Kab)</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                                Kab. {{ $reg->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#C9C1B1]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            @endif
        </div>

        <a href="{{ route('operator.requests.create') }}" 
           class="bg-[#A35139] hover:bg-[#883d28] text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 border border-[#A35139] self-start md:self-auto">
            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Ajukan Draf Usulan Baru</span>
        </a>
    </div>

    <!-- Tabel Daftar Pengajuan -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="py-3 px-4 sm:px-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="text-sm font-black text-[#1B2632] uppercase tracking-wider">Daftar Draf Usulan Pemutakhiran</span>
                <span class="bg-[#1B2632] text-[#EEE9DF] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border border-[#2C3B4D]">
                    {{ $requests->total() }} Data
                </span>
            </div>
            <div class="text-xs text-[#2C3B4D]/75 font-medium">
                Pembaruan data otomatis disinkronkan ke Master Data setelah diverifikasi oleh Super Admin.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[11px] tracking-wider border-b border-[#2C3B4D]">
                    <tr>
                        <th class="py-2 px-3 text-center w-12 font-semibold">No</th>
                        <th class="py-2 px-3 font-semibold whitespace-nowrap w-36">Tanggal Pengajuan</th>
                        <th class="py-2 px-3 font-semibold min-w-[200px]">Nama UPT & Wilayah</th>
                        <th class="py-2 px-3 font-semibold whitespace-nowrap w-36">Kategori Usulan</th>
                        <th class="py-2 px-3 font-semibold min-w-[220px] max-w-[260px]">Ringkasan Perubahan</th>
                        <th class="py-2 px-3 text-center font-semibold whitespace-nowrap w-36">Status Verifikasi</th>
                        <th class="py-2 px-3 font-semibold min-w-[180px] max-w-[220px]">Catatan Reviewer Provinsi</th>
                        <th class="py-2 px-3 text-center font-semibold whitespace-nowrap w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($requests as $index => $req)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <!-- No -->
                            <td class="py-2 px-3 text-center font-mono font-bold text-[#2C3B4D]/60 align-middle">
                                {{ $requests->firstItem() + $index }}
                            </td>

                            <!-- Tanggal Pengajuan -->
                            <td class="py-2 px-3 font-mono text-[11px] text-[#2C3B4D]/80 whitespace-nowrap align-middle">
                                <div class="font-bold text-[#1B2632] leading-tight">{{ $req->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-[#2C3B4D]/60 leading-tight mt-0.5">{{ $req->created_at->format('H:i') }} WITA</div>
                            </td>

                            <!-- Nama UPT & Wilayah -->
                            <td class="py-2 px-3 align-middle">
                                <div class="font-extrabold text-[#1B2632] text-xs leading-tight">
                                    {{ $req->uptLocation->upt_name ?? '-' }}
                                </div>
                                <div class="text-[10px] text-[#2C3B4D]/70 font-medium leading-tight mt-0.5 flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/20">
                                        Kab. {{ $req->uptLocation->regency->name ?? '-' }}
                                    </span>
                                    @if($req->uptLocation->current_village_name)
                                        <span class="text-[#C9C1B1]">&bull;</span>
                                        <span class="text-[#2C3B4D]/70 text-[10px]">Desa {{ $req->uptLocation->current_village_name }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kategori Usulan -->
                            <td class="py-2 px-3 whitespace-nowrap align-middle">
                                @if($req->request_type === 'REGISTRY_SYNC')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-purple-100 text-purple-900 font-bold text-[10px] border border-purple-300 shadow-2xs whitespace-nowrap">
                                        Buku Registri Warga
                                    </span>
                                @elseif($req->request_type === 'DATA_UPDATE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#2C3B4D]/10 text-[#2C3B4D] font-bold text-[10px] border border-[#2C3B4D]/25 shadow-2xs whitespace-nowrap">
                                        Data Lapangan
                                    </span>
                                @elseif($req->request_type === 'LEGAL_ISSUE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#A35139]/15 text-[#A35139] font-bold text-[10px] border border-[#A35139]/30 shadow-2xs whitespace-nowrap">
                                        Masalah Lahan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#FFB162]/25 text-[#8F4E0A] font-bold text-[10px] border border-[#FFB162]/50 shadow-2xs whitespace-nowrap">
                                        Berkas BAST Baru
                                    </span>
                                @endif
                            </td>

                            <!-- Ringkasan Perubahan (Single line truncated with tooltip) -->
                            <td class="py-2 px-3 text-[#2C3B4D] font-medium align-middle">
                                <div class="text-xs truncate leading-tight" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->proposed_payload['submission_note'] ?? '-' }}">
                                    {{ $req->proposed_payload['submission_note'] ?? '-' }}
                                </div>
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="py-2 px-3 text-center whitespace-nowrap align-middle">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/50 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#8F4E0A] animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30 shadow-2xs">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30 shadow-2xs">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>

                            <!-- Catatan Reviewer Provinsi (Single line truncated with tooltip) -->
                            <td class="py-2 px-3 text-xs align-middle">
                                @if($req->reviewer_note)
                                    <div class="text-[#2C3B4D]/80 italic truncate leading-tight" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->reviewer_note }}">
                                        {{ $req->reviewer_note }}
                                    </div>
                                @else
                                    <span class="text-[#C9C1B1] font-mono">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-2 px-3 text-center whitespace-nowrap align-middle">
                                <a href="{{ route('operator.requests.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] font-bold text-[11px] transition border border-[#C9C1B1]/70 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-4 text-center text-[#2C3B4D]/60 font-medium">
                                Tidak ada draf usulan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (Identik Super Admin MP072) -->
        <div class="py-3 px-4 sm:px-5 border-t border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-[#2C3B4D]/80 font-medium">
                Showing <strong class="text-[#1B2632]">{{ $requests->firstItem() ?? 0 }}</strong> to <strong
                    class="text-[#1B2632]">{{ $requests->lastItem() ?? 0 }}</strong> of <strong
                    class="text-[#1B2632]">{{ $requests->total() }}</strong> results
            </div>

            <div
                class="inline-flex items-center bg-white text-[#1B2632] px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-xs border border-[#C9C1B1] self-center sm:self-auto">
                {{ $requests->links('admin.partials.pagination') }}
            </div>
        </div>
    </div>

</div>
@endsection
