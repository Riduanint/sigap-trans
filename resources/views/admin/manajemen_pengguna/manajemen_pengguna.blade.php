@extends('layouts.admin')

@section('title', 'Manajemen Pengguna (RBAC)')
@section('header_title', 'Manajemen Pengguna & Hak Akses (RBAC)')
@section('header_subtitle', 'Pengelolaan akun bertingkat untuk Super Admin Provinsi, Operator 9 Kabupaten, Pimpinan Eksekutif, dan Mitra Kanwil BPN')

@section('content')
<div class="space-y-6">

    <!-- 1. SUMMARY STATS PERAN PENGGUNA (MP072 PALETTE) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Pengguna (Abyssal) -->
        <div class="relative bg-white p-4 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs hover:shadow-ambient transition overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-[#1B2632]"></div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Total Pengguna</span>
            <div class="text-2xl font-black text-[#1B2632] mt-1">{{ $counts['total'] }} Akun</div>
            <span class="text-[10px] text-[#2C3B4D]/70">Terdaftar di sistem</span>
        </div>

        <!-- Super Admin (Slate) -->
        <div class="relative bg-white p-4 rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs hover:shadow-ambient transition overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-[#2C3B4D]"></div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Super Admin</span>
            <div class="text-2xl font-black text-[#2C3B4D] mt-1">{{ $counts['super_admin'] }} Akun</div>
            <span class="text-[10px] text-[#2C3B4D]/80 font-semibold">Otoritas Provinsi</span>
        </div>

        <!-- Operator Wilayah (Truffle) -->
        <div class="relative bg-white p-4 rounded-2xl border border-[#A35139]/40 shadow-ambient-xs hover:shadow-ambient transition overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-[#A35139]"></div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#A35139]/80 block">Operator Wilayah</span>
            <div class="text-2xl font-black text-[#A35139] mt-1">{{ $counts['operator'] }} Akun</div>
            <span class="text-[10px] text-[#A35139] font-semibold">Disnakertrans Kabupaten</span>
        </div>

        <!-- Eksekutif & BPN (Flame) -->
        <div class="relative bg-white p-4 rounded-2xl border border-[#FFB162]/60 shadow-ambient-xs hover:shadow-ambient transition overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-[#FFB162]"></div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#8F4E0A] block">Eksekutif & BPN</span>
            <div class="text-2xl font-black text-[#8F4E0A] mt-1">{{ $counts['eksekutif'] + $counts['bpn'] }} Akun</div>
            <span class="text-[10px] text-[#8F4E0A]/80 font-semibold">Pemangku Kepentingan</span>
        </div>
    </div>

    <!-- 2. FILTER & AKSI TAMBAH -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            <select name="role" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                <option value="">-- Semua Peran --</option>
                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="operator_kabupaten" {{ request('role') == 'operator_kabupaten' ? 'selected' : '' }}>Operator Kabupaten</option>
                <option value="eksekutif" {{ request('role') == 'eksekutif' ? 'selected' : '' }}>Eksekutif (Kadis/Kabid)</option>
                <option value="mitra_bpn" {{ request('role') == 'mitra_bpn' ? 'selected' : '' }}>Mitra Kanwil ATR/BPN</option>
            </select>

            <select name="regency_id" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                <option value="">-- Semua Wilayah Tugas --</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                        {{ $reg->name }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIP..."
                       class="text-xs pl-8 pr-3 py-2 rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                <svg class="w-4 h-4 text-[#2C3B4D]/40 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <button type="submit" class="px-3 py-2 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] text-xs font-bold shadow-ambient-xs transition">
                Filter
            </button>

            @if(request()->anyFilled(['role', 'regency_id', 'search']))
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#A35139] hover:underline px-1">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('admin.users.create') }}" 
           class="bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 self-start lg:self-auto">
            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>+ Tambah Akun Baru</span>
        </a>
    </div>

    <!-- 3. TABEL PENGGUNA RBAC -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#2C3B4D]">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Nama Aparatur / Pengguna</th>
                        <th class="px-4 py-3.5">Email & NIP</th>
                        <th class="px-4 py-3.5">Peran Hak Akses</th>
                        <th class="px-4 py-3.5">Wilayah Tugas</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($users as $index => $u)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-[#2C3B4D]/50 tabular-nums">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-extrabold text-[#1B2632]">{{ $u->name }}</div>
                                <div class="text-[11px] text-[#2C3B4D]/60">{{ $u->position ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-mono text-[#2C3B4D] font-semibold">{{ $u->email }}</div>
                                <div class="text-[10px] text-[#2C3B4D]/60 font-mono">NIP: {{ $u->nip ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($u->role === 'super_admin')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#1B2632]/10 text-[#1B2632] border border-[#1B2632]/20">
                                        Super Admin
                                    </span>
                                @elseif($u->role === 'operator_kabupaten')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/25">
                                        Operator Wilayah
                                    </span>
                                @elseif($u->role === 'eksekutif')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/40">
                                        Eksekutif (Kadis)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30">
                                        Mitra ATR/BPN
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-medium text-[#2C3B4D]">
                                {{ $u->regency->name ?? 'Pemerintah Provinsi Kalsel' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2C3B4D]"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#EEE9DF] text-[#2C3B4D]/60 border border-[#C9C1B1]/60">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] border border-[#C9C1B1]/60 font-bold text-[11px] transition">
                                        Edit
                                    </a>

                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg {{ $u->is_active ? 'bg-[#FFB162]/20 hover:bg-[#FFB162]/30 text-[#8F4E0A] border border-[#FFB162]/40' : 'bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] border border-[#C9C1B1]/60' }} font-bold text-[11px] transition cursor-pointer">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-[#A35139]/15 hover:bg-[#A35139]/25 text-[#A35139] border border-[#A35139]/30 font-bold text-[11px] transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-[#2C3B4D]/60 font-medium">
                                Tidak ada data pengguna yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-[#C9C1B1]/40 bg-[#EEE9DF]/20">
                {{ $users->links('admin.partials.pagination') }}
            </div>
        @endif
    </div>

</div>
@endsection
