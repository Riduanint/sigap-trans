@extends('layouts.admin')

@section('title', 'Edit Akun Pengguna - ' . $user->name)
@section('header_title', 'Perbarui Akun Pengguna (RBAC)')
@section('header_subtitle', 'Memperbarui profil, peranan hak akses, atau mereset kata sandi aparatur')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2C3B4D] hover:text-[#1B2632] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Kembali ke Manajemen Pengguna</span>
    </a>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-[#A35139]/10 border border-[#A35139]/30 text-[#A35139] text-xs">
            <div class="font-bold mb-1">Terdapat kesalahan pengisian:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Nama Lengkap Beserta Gelar <span class="text-[#A35139]">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                </div>

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Nomor Induk Pegawai (NIP)
                    </label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}"
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Alamat Email Dinas <span class="text-[#A35139]">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                </div>

                <!-- Kata Sandi Baru -->
                <div>
                    <label for="password" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Kata Sandi Baru (Kosongkan jika tidak diubah)
                    </label>
                    <input type="password" name="password" id="password"
                           placeholder="Isi jika ingin mereset kata sandi"
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Peran Hak Akses -->
                <div>
                    <label for="role" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Peran Hak Akses (Role) <span class="text-[#A35139]">*</span>
                    </label>
                    <select name="role" id="role" required onchange="toggleRegencyField(this.value)"
                            class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                        <option value="operator_kabupaten" {{ old('role', $user->role) == 'operator_kabupaten' ? 'selected' : '' }}>Operator Wilayah Kabupaten</option>
                        <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin (Provinsi)</option>
                        <option value="eksekutif" {{ old('role', $user->role) == 'eksekutif' ? 'selected' : '' }}>Eksekutif (Kadis/Kabid)</option>
                        <option value="mitra_bpn" {{ old('role', $user->role) == 'mitra_bpn' ? 'selected' : '' }}>Mitra Kanwil ATR/BPN</option>
                    </select>
                </div>

                <!-- Wilayah Penugasan -->
                <div id="regency_wrapper">
                    <label for="regency_id" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Wilayah Kabupaten <span class="text-[#A35139]">*</span>
                    </label>
                    <select name="regency_id" id="regency_id"
                            class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                        <option value="">-- Pilih Wilayah Kabupaten --</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ old('regency_id', $user->regency_id) == $reg->id ? 'selected' : '' }}>
                                Kabupaten {{ $reg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Jabatan Kedinasan -->
                <div>
                    <label for="position" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Jabatan Kedinasan</label>
                    <input type="text" name="position" id="position" value="{{ old('position', $user->position) }}"
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 text-[#1B2632]">
                </div>

                <!-- Nomor HP/WA -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Nomor Kontak (HP/WA)</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 text-[#1B2632]">
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="rounded border-[#C9C1B1] text-[#1B2632] focus:ring-[#FFB162]">
                    <span class="text-xs text-[#2C3B4D] font-bold">Status Akun Aktif (Dapat Masuk ke Sistem)</span>
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#C9C1B1]/30">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl border border-[#C9C1B1]/70 text-xs font-bold text-[#2C3B4D] hover:bg-[#EEE9DF] transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-bold text-xs transition shadow-ambient-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Simpan Perubahan</span>
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
