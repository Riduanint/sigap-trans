@extends('layouts.admin')

@section('title', 'Manajemen Pengguna (RBAC)')
@section('header_title', 'Manajemen Pengguna & Hak Akses (RBAC)')
@section('header_subtitle', 'Pengelolaan akun bertingkat untuk Super Admin Provinsi, Operator 9 Kabupaten, Pimpinan Eksekutif, dan Mitra Kanwil BPN')

@section('content')
<div class="space-y-6">

    <!-- 1. SUMMARY STATS PERAN PENGGUNA -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Pengguna</span>
            <div class="text-2xl font-black text-[#0B1849] mt-1">{{ $counts['total'] }} Akun</div>
            <span class="text-[10px] text-slate-500">Terdaftar di sistem</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Super Admin</span>
            <div class="text-2xl font-black text-blue-900 mt-1">{{ $counts['super_admin'] }} Akun</div>
            <span class="text-[10px] text-blue-700 font-semibold">Otoritas Provinsi</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Operator Wilayah</span>
            <div class="text-2xl font-black text-[#124D1C] mt-1">{{ $counts['operator'] }} Akun</div>
            <span class="text-[10px] text-[#124D1C] font-semibold">Disnakertrans Kabupaten</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Eksekutif & BPN</span>
            <div class="text-2xl font-black text-amber-700 mt-1">{{ $counts['eksekutif'] + $counts['bpn'] }} Akun</div>
            <span class="text-[10px] text-amber-700 font-semibold">Pemangku Kepentingan</span>
        </div>
    </div>

    <!-- 2. FILTER & AKSI TAMBAH -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            <select name="role" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                <option value="">-- Semua Peran --</option>
                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="operator_kabupaten" {{ request('role') == 'operator_kabupaten' ? 'selected' : '' }}>Operator Kabupaten</option>
                <option value="eksekutif" {{ request('role') == 'eksekutif' ? 'selected' : '' }}>Eksekutif (Kadis/Kabid)</option>
                <option value="mitra_bpn" {{ request('role') == 'mitra_bpn' ? 'selected' : '' }}>Mitra Kanwil ATR/BPN</option>
            </select>

            <select name="regency_id" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                <option value="">-- Semua Wilayah Tugas --</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" {{ request('regency_id') == $reg->id ? 'selected' : '' }}>
                        {{ $reg->name }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIP..."
                       class="text-xs pl-8 pr-3 py-2 rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <button type="submit" class="px-3 py-2 rounded-xl bg-[#0B1849] text-white text-xs font-bold shadow-xs">
                Filter
            </button>

            @if(request()->anyFilled(['role', 'regency_id', 'search']))
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-rose-600 hover:underline px-1">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('admin.users.create') }}" 
           class="bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md flex items-center gap-2 border border-[#E4B028]/40 self-start lg:self-auto">
            <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>+ Tambah Akun Baru</span>
        </a>
    </div>

    <!-- 3. TABEL PENGGUNA RBAC -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $index => $u)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-slate-400 tabular-nums">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-extrabold text-[#0B1849]">{{ $u->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $u->position ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-mono text-slate-700 font-semibold">{{ $u->email }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">NIP: {{ $u->nip ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($u->role === 'super_admin')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-300">
                                        Super Admin
                                    </span>
                                @elseif($u->role === 'operator_kabupaten')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        Operator Wilayah
                                    </span>
                                @elseif($u->role === 'eksekutif')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 border border-purple-300">
                                        Eksekutif (Kadis)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                        Mitra ATR/BPN
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-700">
                                {{ $u->regency->name ?? 'Pemerintah Provinsi Kalsel' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                        Edit
                                    </a>

                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg {{ $u->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' }} font-bold text-[11px] transition">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                Tidak ada data pengguna yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
