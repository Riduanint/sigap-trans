@extends('layouts.admin')

@section('title', 'Tambah Akun Pengguna Baru')
@section('header_title', 'Tambah Akun Pengguna Baru (RBAC)')
@section('header_subtitle', 'Mendaftarkan aparatur baru untuk Super Admin, Operator Wilayah Kabupaten, Eksekutif, atau Mitra BPN')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-[#0B1849] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Kembali ke Manajemen Pengguna</span>
    </a>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs">
            <div class="font-bold mb-1">Terdapat kesalahan pengisian:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="Contoh: Muhammad Rifani, S.AP"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nomor Induk Pegawai (NIP)
                    </label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                           placeholder="Contoh: 198506152010011012"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Email Dinas <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="Contoh: operator.batola@kalselprov.go.id"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>

                <!-- Kata Sandi -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Kata Sandi Awal <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           placeholder="Minimal 6 karakter"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Peran Hak Akses -->
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Peran Hak Akses (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" id="role" required onchange="toggleRegencyField(this.value)"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                        <option value="operator_kabupaten" {{ old('role') == 'operator_kabupaten' ? 'selected' : '' }}>Operator Wilayah Kabupaten</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin (Provinsi)</option>
                        <option value="eksekutif" {{ old('role') == 'eksekutif' ? 'selected' : '' }}>Eksekutif (Kadis/Kabid)</option>
                        <option value="mitra_bpn" {{ old('role') == 'mitra_bpn' ? 'selected' : '' }}>Mitra Kanwil ATR/BPN</option>
                    </select>
                </div>

                <!-- Wilayah Penugasan -->
                <div id="regency_wrapper">
                    <label for="regency_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Wilayah Kabupaten <span class="text-rose-500">*</span>
                    </label>
                    <select name="regency_id" id="regency_id"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                        <option value="">-- Pilih Wilayah Kabupaten --</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ old('regency_id') == $reg->id ? 'selected' : '' }}>
                                [Wilayah {{ $reg->roman_code }}] {{ $reg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Jabatan Kedinasan -->
                <div>
                    <label for="position" class="block text-xs font-bold text-slate-700 mb-1.5">Jabatan Kedinasan</label>
                    <input type="text" name="position" id="position" value="{{ old('position') }}"
                           placeholder="Contoh: Staf Bidang Transmigrasi Disnakertrans"
                           class="w-full text-xs rounded-xl border-slate-300">
                </div>

                <!-- Nomor HP/WA -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Kontak (HP/WA)</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           placeholder="Contoh: 081234567890"
                           class="w-full text-xs rounded-xl border-slate-300">
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                           class="rounded border-slate-300 text-[#124D1C] focus:ring-[#124D1C]">
                    <span class="text-xs text-slate-700 font-bold">Akun Langsung Aktif dan Siap Digunakan</span>
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-xs transition shadow-md flex items-center gap-2 border border-[#E4B028]/40">
                    <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Simpan Akun Pengguna</span>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
function toggleRegencyField(role) {
    const wrapper = document.getElementById('regency_wrapper');
    const select = document.getElementById('regency_id');
    if (role === 'operator_kabupaten') {
        wrapper.style.display = 'block';
        select.required = true;
    } else {
        wrapper.style.display = 'none';
        select.required = false;
        select.value = '';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    toggleRegencyField(document.getElementById('role').value);
});
</script>
@endsection
