@extends('layouts.admin')

@section('title', 'Tambah Data UPT Baru')
@section('header_title', 'Pendaftaran Unit Pemukiman Transmigrasi (UPT) Baru')
@section('header_subtitle', 'Penambahan Entitas Lokasi Pemukiman Transmigrasi ke Basis Data Induk Provinsi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs p-6">
        
        <!-- Header Panduan Singkat -->
        <div class="pb-4 mb-6 border-b border-[#C9C1B1]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-black text-[#1B2632] text-base flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFB162]"></span>
                    <span>Formulir Entri Lokasi UPT Baru</span>
                </h3>
                <p class="text-xs text-[#2C3B4D]/80 mt-1">
                    Isi seluruh informasi atribut wilayah, status hukum agraria, dan titik koordinat spasial untuk ditampilkan di peta WebGIS.
                </p>
            </div>
            <a href="{{ route('admin.upt.index') }}" class="text-xs font-bold text-[#1B2632] hover:text-[#1B2632] bg-[#EEE9DF] hover:bg-[#C9C1B1]/40 border border-[#C9C1B1] px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Master Data</span>
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-[#A35139]/10 border border-[#A35139]/30 text-xs text-[#A35139] space-y-1">
                <strong class="font-extrabold block">Terdapat beberapa data yang belum valid:</strong>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.upt.store') }}" class="space-y-6">
            @csrf

            <!-- 1. Identitas Pokok UPT & Wilayah Administratif -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                    <span>1. Identitas Pokok Wilayah UPT</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="upt_number" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Nomor Registrasi UPT <span class="text-[#A35139]">*</span>
                        </label>
                        <div class="flex rounded-xl overflow-hidden border border-[#C9C1B1] bg-white focus-within:ring-2 focus-within:ring-[#FFB162] focus-within:border-transparent transition">
                            <span class="inline-flex items-center px-3 text-xs font-black text-[#2C3B4D] bg-[#EEE9DF] border-r border-[#C9C1B1] select-none">UPT-</span>
                            <input type="number" name="upt_number" id="upt_number" min="1"
                                   value="{{ old('upt_number', $nextUptNumber) }}" required
                                   class="w-full bg-transparent px-3 py-2 text-xs font-extrabold text-[#1B2632] focus:outline-none">
                        </div>
                        <span class="text-[10px] text-[#2C3B4D]/70 mt-1 block">Rekomendasi otomatis nomor urut berikutnya.</span>
                    </div>

                    <div>
                        <label for="regency_id" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Kabupaten <span class="text-[#A35139]">*</span>
                        </label>
                        <select name="regency_id" id="regency_id" required
                                class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-medium text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                            <option value="">-- Pilih Kabupaten --</option>
                            @foreach($regencies as $reg)
                                <option value="{{ $reg->id }}" {{ old('regency_id') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="business_pattern" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Pola Usaha / Budidaya <span class="text-[#A35139]">*</span>
                        </label>
                        <select name="business_pattern" id="business_pattern" required
                                class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-medium text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                            @foreach(['TPLK', 'TPLB', 'PIRSUS', 'HTI', 'P4HDR'] as $pat)
                                <option value="{{ $pat }}" {{ old('business_pattern', 'TPLK') == $pat ? 'selected' : '' }}>
                                    {{ $pat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="upt_name" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Nama Satuan Pemukiman / UPT Asal <span class="text-[#A35139]">*</span>
                        </label>
                        <input type="text" name="upt_name" id="upt_name" 
                               value="{{ old('upt_name') }}" required
                               placeholder="Contoh: Belawang Sp.1, Sungai Danau, dll"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent">
                    </div>

                    <div>
                        <label for="current_village_name" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Nama Desa Definitif Saat Ini <span class="text-[#A35139]">*</span>
                        </label>
                        <input type="text" name="current_village_name" id="current_village_name" 
                               value="{{ old('current_village_name') }}" required
                               placeholder="Contoh: Desa Bagak, Desa Antaran..."
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- 2. Titik Geospasial WebGIS -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                        <span>2. Titik Koordinat Geospasial (Peta WebGIS)</span>
                    </div>
                    <span class="text-[11px] font-medium text-[#2C3B4D]/70">Format Desimal WGS84 (EPSG:4326)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Latitude (Lintang, cth: -2.918722)
                        </label>
                        <input type="number" step="any" name="latitude" id="latitude" 
                               value="{{ old('latitude') }}"
                               placeholder="-2.918722"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-mono text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent">
                    </div>

                    <div>
                        <label for="longitude" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Longitude (Bujur, cth: 114.435481)
                        </label>
                        <input type="number" step="any" name="longitude" id="longitude" 
                               value="{{ old('longitude') }}"
                               placeholder="114.435481"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-mono text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] focus:border-transparent">
                    </div>
                </div>
                <p class="text-[11px] text-[#2C3B4D] bg-[#EEE9DF]/70 p-3 rounded-xl border border-[#C9C1B1]/60">
                    💡 <strong>Info Spasial:</strong> Titik koordinat ini akan langsung dipetakan pada portal peta WebGIS interaktif beserta pembentukan otomatis poligon delineasi kawasan.
                </p>
            </div>

            <!-- 3. Status Hukum & Permasalahan Lahan -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FFB162]"></span>
                    <span>3. Status Agraria & Permasalahan Lahan</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="issue_status" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Status Kondisi Lahan & Kawasan <span class="text-[#A35139]">*</span>
                        </label>
                        <select name="issue_status" id="issue_status" required
                                class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs font-bold text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                            <option value="clean" {{ old('issue_status', 'clean') == 'clean' ? 'selected' : '' }}>
                                🟢 Clean & Clear (SHM Tuntas, Bebas Sengketa)
                            </option>
                            <option value="warning" {{ old('issue_status') == 'warning' ? 'selected' : '' }}>
                                🟡 Waspada / Monitoring (Kendala Tanggul/Fasum, Perlu Pemantauan)
                            </option>
                            <option value="critical" {{ old('issue_status') == 'critical' ? 'selected' : '' }}>
                                🔴 Kritis / Prioritas Mediasi (Tumpang Tindih Kawasan Hutan/Klaim Pihak Ketiga)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="shm_status" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Status Sertifikasi Hak Milik (SHM)
                        </label>
                        <input type="text" name="shm_status" id="shm_status" 
                               value="{{ old('shm_status', 'SHM Tuntas 100%') }}" 
                               placeholder="Contoh: SHM Tuntas 100%, SHM 90% Tuntas"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="issue_note" class="text-xs font-bold text-[#2C3B4D] block mb-1">
                            Uraian Catatan Lapangan / Sengketa Batas
                        </label>
                        <textarea name="issue_note" id="issue_note" rows="3" 
                                  placeholder="Tuliskan uraian kendala batas kawasan hutan, konsesi tambang, atau catatan perkembangan..."
                                  class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">{{ old('issue_note') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 4. Dinamika Demografi & Kependudukan -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-sm text-[#1B2632] pb-2 border-b border-[#C9C1B1]/40 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#1B2632]"></span>
                    <span>4. Rekam Jejak Kependudukan (Penempatan & Serah Terima)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="placement_year" class="text-xs font-bold text-[#2C3B4D] block mb-1">Tahun Penempatan <span class="text-[#A35139]">*</span></label>
                        <input type="text" name="placement_year" id="placement_year" 
                               value="{{ old('placement_year', date('Y')) }}" required
                               placeholder="Contoh: 2005 atau 2005/2006"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="placement_kk" class="text-xs font-bold text-[#2C3B4D] block mb-1">KK Penempatan <span class="text-[#A35139]">*</span></label>
                        <input type="number" name="placement_kk" id="placement_kk" 
                               value="{{ old('placement_kk', 0) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="placement_population" class="text-xs font-bold text-[#2C3B4D] block mb-1">Jiwa Penempatan <span class="text-[#A35139]">*</span></label>
                        <input type="number" name="placement_population" id="placement_population" 
                               value="{{ old('placement_population', 0) }}" required min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="handover_year" class="text-xs font-bold text-[#2C3B4D] block mb-1">Tahun / Tanggal BAST Pemda</label>
                        <input type="text" name="handover_year" id="handover_year" 
                               value="{{ old('handover_year') }}"
                               placeholder="Contoh: 2010 atau 14-08-2010"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_kk" class="text-xs font-bold text-[#2C3B4D] block mb-1">KK Diserahkan</label>
                        <input type="number" name="handover_kk" id="handover_kk" 
                               value="{{ old('handover_kk', 0) }}" min="0"
                               class="w-full bg-white border border-[#C9C1B1] rounded-xl px-3 py-2 text-xs text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_population" class="text-xs font-bold text-[#2C3B4D] block mb-1">Jiwa Diserahkan</label>
                        <input type="number" name="handover_population" id="handover_population" 
                               value="{{ old('handover_population', 0) }}" min="0"
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
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Simpan Data UPT Baru</span>
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
