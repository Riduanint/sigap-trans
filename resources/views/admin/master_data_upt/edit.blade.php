@extends('layouts.admin')

@section('title', 'Edit Data UPT ' . $upt->upt_name)
@section('header_title', 'Pemutakhiran Data: UPT-' . str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) . ' (' . $upt->upt_name . ')')
@section('header_subtitle', 'Kabupaten ' . $upt->regency?->name . ' • Formulir Pembaruan Atribut Kependudukan & Status Agraria')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-6">
        
        <form method="POST" action="{{ route('admin.upt.update', $upt->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Informasi Pokok (Readonly) -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/60 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-[#2C3B4D]/70 font-bold block uppercase text-[10px]">Nomor Registrasi</span>
                    <span class="font-extrabold text-[#1B2632] text-sm">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <span class="text-[#2C3B4D]/70 font-bold block uppercase text-[10px]">Kabupaten Binaan</span>
                    <span class="font-extrabold text-[#1B2632] text-sm">Kab. {{ $upt->regency?->name }}</span>
                </div>
                <div>
                    <span class="text-[#2C3B4D]/70 font-bold block uppercase text-[10px]">Nama Satuan Asal</span>
                    <span class="font-extrabold text-[#1B2632] text-sm">{{ $upt->upt_name }}</span>
                </div>
            </div>

            <!-- 1. Identitas Wilayah & Pola Usaha -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                    <span>Identitas Desa Definitif & Pola Budidaya</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="current_village_name" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Nama Desa Definitif Saat Ini <span class="text-[#A35139]">*</span>
                        </label>
                        <input type="text" name="current_village_name" id="current_village_name" 
                               value="{{ old('current_village_name', $upt->current_village_name) }}" required
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent">
                    </div>

                    <div>
                        <label for="business_pattern" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Pola Usaha / Budidaya <span class="text-[#A35139]">*</span>
                        </label>
                        <select name="business_pattern" id="business_pattern" required
                                class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-medium text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                            @foreach(['TPLK', 'TPLB', 'PIRSUS', 'HTI', 'P4HDR'] as $pat)
                                <option value="{{ $pat }}" {{ old('business_pattern', $upt->business_pattern) == $pat ? 'selected' : '' }}>
                                    {{ $pat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Status Hukum & Permasalahan Lahan -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                    <span>Status Agraria & Catatan Lapangan</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="issue_status" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Status Kondisi Lahan & Kawasan <span class="text-[#A35139]">*</span>
                        </label>
                        <select name="issue_status" id="issue_status" required
                                class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-bold text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                            <option value="clean" {{ old('issue_status', $upt->issue_status) == 'clean' ? 'selected' : '' }}>
                                🟢 Clean & Clear (SHM Tuntas, Bebas Sengketa)
                            </option>
                            <option value="warning" {{ old('issue_status', $upt->issue_status) == 'warning' ? 'selected' : '' }}>
                                🟡 Waspada / Monitoring (Kendala Tanggul/Fasum, Perlu Pemantauan)
                            </option>
                            <option value="critical" {{ old('issue_status', $upt->issue_status) == 'critical' ? 'selected' : '' }}>
                                🔴 Kritis / Prioritas Mediasi (Tumpang Tindih Kawasan Hutan/Klaim Pihak Ketiga)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="shm_status" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Status Sertifikasi Hak Milik (SHM)
                        </label>
                        <input type="text" name="shm_status" id="shm_status" 
                               value="{{ old('shm_status', $upt->shm_status) }}" 
                               placeholder="Contoh: SHM Tuntas 100%, SHM 90% Tuntas"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="issue_note" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Uraian Catatan Lapangan / Sengketa Batas
                        </label>
                        <textarea name="issue_note" id="issue_note" rows="3" 
                                  placeholder="Tuliskan uraian kendala batas kawasan hutan, konsesi tambang, atau catatan perkembangan..."
                                  class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">{{ old('issue_note', $upt->issue_note) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 3. Dinamika Demografi & Kependudukan -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#1B2632]"></span>
                    <span>Rekam Jejak Kependudukan (Penempatan & Serah Terima)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="placement_year" class="text-xs font-bold text-[#2C3B4D] block mb-1">Tahun Penempatan</label>
                        <input type="text" name="placement_year" id="placement_year" 
                               value="{{ old('placement_year', $upt->placement_year) }}" required
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="placement_kk" class="text-xs font-bold text-[#2C3B4D] block mb-1">KK Penempatan</label>
                        <input type="number" name="placement_kk" id="placement_kk" 
                               value="{{ old('placement_kk', $upt->placement_kk) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="placement_population" class="text-xs font-bold text-[#2C3B4D] block mb-1">Jiwa Penempatan</label>
                        <input type="number" name="placement_population" id="placement_population" 
                               value="{{ old('placement_population', $upt->placement_population) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="handover_year" class="text-xs font-bold text-[#2C3B4D] block mb-1">Tahun / Tanggal BAST</label>
                        <input type="text" name="handover_year" id="handover_year" 
                               value="{{ old('handover_year', $upt->handover_year) }}"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_kk" class="text-xs font-bold text-[#2C3B4D] block mb-1">KK Diserahkan</label>
                        <input type="number" name="handover_kk" id="handover_kk" 
                               value="{{ old('handover_kk', $upt->handover_kk) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_population" class="text-xs font-bold text-[#2C3B4D] block mb-1">Jiwa Diserahkan</label>
                        <input type="number" name="handover_population" id="handover_population" 
                               value="{{ old('handover_population', $upt->handover_population) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan / Batal -->
            <div class="pt-4 border-t border-[#C9C1B1]/40 flex items-center justify-end gap-3">
                <a href="{{ route('admin.upt.index') }}" 
                   class="bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 text-[#1B2632] font-bold text-xs py-2.5 px-4 rounded-xl border border-[#C9C1B1] transition">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-[#A35139] hover:bg-[#883d28] text-white font-extrabold text-xs py-2.5 px-5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 border border-[#FFB162]/40 cursor-pointer">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Simpan Pembaruan Data</span>
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
