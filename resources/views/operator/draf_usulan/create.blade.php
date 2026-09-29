@extends('layouts.admin')

@section('title', 'Ajukan Draf Usulan Pemutakhiran UPT')
@section('header_title', 'Formulir Pengajuan Draf Pemutakhiran Data UPT')
@section('header_subtitle', 'Ajukan revisi data kependudukan, status legalitas lahan, atau lampiran berkas BAST untuk diverifikasi Provinsi')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('operator.dashboard') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2C3B4D] hover:text-[#1B2632] transition">
            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Dasbor Operator</span>
        </a>
    </div>

    <!-- Alert Edukasi Prosedur Verifikasi -->
    <div class="p-5 rounded-2xl bg-[#FFB162]/15 border border-[#FFB162]/50 text-[#1B2632] text-xs flex items-start gap-3 shadow-ambient-xs">
        <div class="w-8 h-8 rounded-xl bg-[#FFB162]/30 border border-[#FFB162]/60 flex items-center justify-center text-[#8F4E0A] shrink-0 font-bold mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="space-y-1 leading-relaxed">
            <span class="font-extrabold text-sm block text-[#1B2632]">Alur Standar Pengajuan & Verifikasi Draf Usulan:</span>
            <p class="text-[#2C3B4D]/85">
                Setiap data yang Anda kirimkan tidak langsung mengubah basis data utama secara permanen, melainkan dicatat sebagai <strong>Draf Usulan (Change Request)</strong>. Super Admin Provinsi akan melakukan komparasi data (<em>diff check</em>) sebelum memberikan persetujuan (<em>approval</em>).
            </p>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-[#A35139]/15 border border-[#A35139]/40 text-[#A35139] text-xs">
            <div class="font-bold mb-1">Terdapat kesalahan pengisian formulir:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('operator.requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- KARTU 1: PILIH UPT & JENIS USULAN -->
        <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs space-y-5">
            <div class="border-b border-[#C9C1B1]/40 pb-3.5 flex items-center justify-between">
                <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1B2632] text-[#EEE9DF] flex items-center justify-center text-xs font-black">1</span>
                    <span>Sasaran Lokasi UPT & Kategori Pengajuan</span>
                </h3>
                <span class="text-[11px] font-semibold text-[#2C3B4D]/70">Langkah 1 dari 3</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Dropdown UPT Berdasarkan Kabupaten -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="upt_location_id" class="block text-xs font-bold text-[#2C3B4D]">
                            Pilih Lokasi UPT (Lintas 9 Kabupaten) <span class="text-[#A35139]">*</span>
                        </label>
                        <span class="text-[10px] text-[#2C3B4D]/60 font-medium">124 UPT Tersedia</span>
                    </div>

                    <!-- Filter Cepat Kabupaten -->
                    <div class="mb-2 relative">
                        <select id="filter_regency_select" onchange="filterUptByRegency(this.value)"
                                class="w-full text-[11px] rounded-xl border border-[#C9C1B1] bg-[#EEE9DF]/40 py-2 pl-3 pr-8 text-[#1B2632] font-semibold focus:ring-2 focus:ring-[#FFB162] appearance-none cursor-pointer">
                            <option value="all">📍 Semua 9 Kabupaten (Tampilkan Semua)</option>
                            @foreach($regencies as $reg)
                                <option value="{{ $reg->id }}" {{ ($selectedUpt && $selectedUpt->regency_id == $reg->id) ? 'selected' : '' }}>
                                    Kabupaten {{ $reg->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <div class="relative">
                        <select name="upt_location_id" id="upt_location_id" required onchange="loadUptData(this.value)"
                                class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 pl-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer">
                            <option value="">-- Pilih Salah Satu UPT --</option>
                            @foreach($regencies as $reg)
                                @php
                                    $rUpts = $uptLocations->where('regency_id', $reg->id);
                                @endphp
                                @if($rUpts->isNotEmpty())
                                    <optgroup label="Kabupaten {{ $reg->name }} ({{ $rUpts->count() }} UPT)" data-regency-id="{{ $reg->id }}">
                                        @foreach($rUpts as $upt)
                                            <option value="{{ $upt->id }}" 
                                                    {{ (old('upt_location_id', $selectedUpt->id ?? '') == $upt->id) ? 'selected' : '' }}
                                                    data-regency="{{ $upt->regency_id }}"
                                                    data-upt="{{ json_encode($upt) }}">
                                                [No. {{ $upt->upt_number }}] {{ $upt->upt_name }} ({{ $upt->current_village_name ?? 'Desa belum terdefinisi' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Jenis Usulan -->
                <div>
                    <label for="request_type" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Jenis Usulan <span class="text-[#A35139]">*</span>
                    </label>
                    <div class="relative">
                        <select name="request_type" id="request_type" required
                                class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 pl-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer">
                            <option value="DATA_UPDATE" {{ old('request_type') == 'DATA_UPDATE' ? 'selected' : '' }}>
                                📊 DATA_UPDATE - Pemutakhiran Data Lapangan / Kependudukan / Desa
                            </option>
                            <option value="LEGAL_ISSUE" {{ old('request_type') == 'LEGAL_ISSUE' ? 'selected' : '' }}>
                                ⚖️ LEGAL_ISSUE - Pelaporan Sengketa / Overlap Kawasan Hutan / Tambang
                            </option>
                            <option value="NEW_BAST" {{ old('request_type') == 'NEW_BAST' ? 'selected' : '' }}>
                                📁 NEW_BAST - Pengajuan Berkas Berita Acara Serah Terima (BAST) Baru
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Pengantar Pengajuan (Alasan Usulan) -->
            <div>
                <label for="submission_note" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                    Uraian Alasan / Pengantar Pengajuan <span class="text-[#A35139]">*</span>
                </label>
                <textarea name="submission_note" id="submission_note" rows="2" required
                          placeholder="Contoh: Berdasarkan hasil monitoring lapangan Triwulan III 2026, status sertifikasi telah tuntas 100% dan batas desa definitif telah disahkan perda..."
                          class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white p-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">{{ old('submission_note') }}</textarea>
            </div>
        </div>

        <!-- KARTU 2: PEMUTAKHIRAN PARAMETER DATA UPT -->
        <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs space-y-5">
            <div class="border-b border-[#C9C1B1]/40 pb-3.5 flex items-center justify-between">
                <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#2C3B4D] text-[#EEE9DF] flex items-center justify-center text-xs font-black">2</span>
                    <span>Data yang Diusulkan Berubah</span>
                </h3>
                <span class="text-[11px] text-[#2C3B4D]/70 italic">Sesuaikan nilai yang ingin diperbarui</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="current_village_name" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Nama Desa Definitif Saat Ini</label>
                    <input type="text" name="current_village_name" id="current_village_name" 
                           value="{{ old('current_village_name', $selectedUpt->current_village_name ?? '') }}"
                           class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                </div>

                <div>
                    <label for="business_pattern" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Pola Usaha Budidaya</label>
                    <input type="text" name="business_pattern" id="business_pattern" 
                           value="{{ old('business_pattern', $selectedUpt->business_pattern ?? '') }}"
                           placeholder="Contoh: TPLK, TPLB, PIRSUS Karet"
                           class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                </div>

                <div>
                    <label for="shm_status" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Status Sertifikasi SHM</label>
                    <div class="relative">
                        <select name="shm_status" id="shm_status" 
                                class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 pl-3 pr-8 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162] shadow-2xs appearance-none cursor-pointer">
                            @php
                                $currentShm = old('shm_status', $selectedUpt->shm_status ?? '100% SHM');
                            @endphp
                            <option value="100% SHM" {{ $currentShm === '100% SHM' ? 'selected' : '' }}>100% SHM (Selesai Penuh)</option>
                            <option value="Sebagian SHM" {{ $currentShm === 'Sebagian SHM' ? 'selected' : '' }}>Sebagian SHM (Tahap Redistribusi)</option>
                            <option value="Belum SHM" {{ $currentShm === 'Belum SHM' ? 'selected' : '' }}>Belum SHM (Proses GTRA)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-[#C9C1B1]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Kependudukan (KK & Jiwa) -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/30 border border-[#C9C1B1]/50 space-y-4">
                <span class="text-xs font-black text-[#1B2632] uppercase tracking-wider block">
                    Dinamika Kependudukan (Penempatan & Serah Terima)
                </span>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="placement_year" class="block text-[11px] font-bold text-[#2C3B4D] mb-1">Tahun Penempatan</label>
                        <input type="text" name="placement_year" id="placement_year" 
                               value="{{ old('placement_year', $selectedUpt->placement_year ?? '') }}"
                               class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="placement_kk" class="block text-[11px] font-bold text-[#2C3B4D] mb-1">KK Penempatan</label>
                        <input type="number" name="placement_kk" id="placement_kk" 
                               value="{{ old('placement_kk', $selectedUpt->placement_kk ?? 0) }}"
                               class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_year" class="block text-[11px] font-bold text-[#2C3B4D] mb-1">Tahun Serah Terima</label>
                        <input type="text" name="handover_year" id="handover_year" 
                               value="{{ old('handover_year', $selectedUpt->handover_year ?? '') }}"
                               class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                    <div>
                        <label for="handover_kk" class="block text-[11px] font-bold text-[#2C3B4D] mb-1">KK Serah Terima</label>
                        <input type="number" name="handover_kk" id="handover_kk" 
                               value="{{ old('handover_kk', $selectedUpt->handover_kk ?? 0) }}"
                               class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] focus:ring-2 focus:ring-[#FFB162]">
                    </div>
                </div>
            </div>

            <!-- Status Permasalahan Lahan (Traffic Light) -->
            <div class="p-4 rounded-xl bg-[#EEE9DF]/30 border border-[#C9C1B1]/50 space-y-4">
                <span class="text-xs font-black text-[#1B2632] uppercase tracking-wider block">
                    Indikator Legalitas Lahan Agraria (Traffic Light)
                </span>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @php $stat = old('issue_status', $selectedUpt->issue_status ?? 'clean'); @endphp
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'clean' ? 'bg-emerald-50/80 border-emerald-500 shadow-2xs' : 'bg-white border-[#C9C1B1] hover:bg-[#EEE9DF]/50' }}">
                        <input type="radio" name="issue_status" value="clean" {{ $stat === 'clean' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-950 block">🟢 Clean & Clear</span>
                            <span class="text-[10px] text-emerald-800">Bebas sengketa & legalitas tuntas</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'warning' ? 'bg-[#FFB162]/20 border-[#FFB162] shadow-2xs' : 'bg-white border-[#C9C1B1] hover:bg-[#EEE9DF]/50' }}">
                        <input type="radio" name="issue_status" value="warning" {{ $stat === 'warning' ? 'checked' : '' }} class="text-[#8F4E0A] focus:ring-[#FFB162]">
                        <div>
                            <span class="text-xs font-bold text-[#8F4E0A] block">🟡 Waspada / Monitoring</span>
                            <span class="text-[10px] text-[#8F4E0A]/80">Kendala tanggul/fasum & pemantauan</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'critical' ? 'bg-[#A35139]/15 border-[#A35139] shadow-2xs' : 'bg-white border-[#C9C1B1] hover:bg-[#EEE9DF]/50' }}">
                        <input type="radio" name="issue_status" value="critical" {{ $stat === 'critical' ? 'checked' : '' }} class="text-[#A35139] focus:ring-[#A35139]">
                        <div>
                            <span class="text-xs font-bold text-[#A35139] block">🔴 Kritis / Prioritas Mediasi</span>
                            <span class="text-[10px] text-[#A35139]/80">Tumpang tindih hutan / klaim lahan</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label for="issue_note" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                        Catatan Deskripsi Masalah Lapangan
                    </label>
                    <textarea name="issue_note" id="issue_note" rows="2" 
                              placeholder="Uraikan detail masalah batas kawasan hutan, tumpang tindih perizinan, atau kondisi terkini..."
                              class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white p-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">{{ old('issue_note', $selectedUpt->issue_note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- KARTU 3: LAMPIRAN BERKAS DOKUMEN (OPSIONAL / JIKA NEW_BAST) -->
        <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs space-y-4">
            <div class="border-b border-[#C9C1B1]/40 pb-3.5 flex items-center justify-between">
                <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#FFB162] text-[#1B2632] flex items-center justify-center text-xs font-black">3</span>
                    <span>Lampiran Berkas Pendukung (PDF BAST / SK)</span>
                </h3>
                <span class="text-[11px] font-semibold text-[#2C3B4D]/70">Langkah 3 dari 3</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="document_name" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Judul Dokumen</label>
                    <input type="text" name="document_name" id="document_name" value="{{ old('document_name') }}"
                           placeholder="Contoh: Berita Acara Serah Terima (BAST) Final"
                           class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                </div>
                <div>
                    <label for="document_number" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">Nomor Register Dokumen</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}"
                           placeholder="Contoh: 560/124/BAST-DISNAKER/2026"
                           class="w-full text-xs rounded-xl border border-[#C9C1B1] bg-white py-2 px-3 text-[#1B2632] placeholder-[#C9C1B1] focus:ring-2 focus:ring-[#FFB162] shadow-2xs">
                </div>
            </div>

            <div>
                <label for="document_file" class="block text-xs font-bold text-[#2C3B4D] mb-1.5">
                    Unggah Berkas Scan PDF (Maksimal 20 MB)
                </label>
                <input type="file" name="document_file" id="document_file" accept=".pdf"
                       class="w-full text-xs text-[#2C3B4D]/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1B2632] file:text-[#EEE9DF] hover:file:bg-[#2C3B4D] cursor-pointer">
            </div>
        </div>

        <!-- TOMBOL SIMPAN & SUBMIT -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('operator.dashboard') }}" 
               class="px-5 py-2.5 rounded-xl border border-[#C9C1B1] bg-[#EEE9DF] text-[#1B2632] hover:bg-[#C9C1B1]/40 text-xs font-bold transition shadow-2xs">
                Batalkan
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#A35139] hover:bg-[#883d28] text-white text-xs font-black transition shadow-ambient-xs flex items-center gap-2 border border-[#A35139] cursor-pointer">
                <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Kirim Draf Usulan ke Provinsi</span>
            </button>
        </div>

    </form>
</div>

<script>
function filterUptByRegency(regencyId) {
    const select = document.getElementById('upt_location_id');
    const optgroups = select.querySelectorAll('optgroup');
    
    optgroups.forEach(group => {
        const groupRegencyId = group.getAttribute('data-regency-id');
        if (regencyId === 'all' || groupRegencyId === regencyId) {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });
}

function loadUptData(uptId) {
    if (!uptId) return;
    const select = document.getElementById('upt_location_id');
    const option = select.options[select.selectedIndex];
    const dataStr = option.getAttribute('data-upt');
    if (!dataStr) return;

    try {
        const upt = JSON.parse(dataStr);
        document.getElementById('current_village_name').value = upt.current_village_name || '';
        document.getElementById('business_pattern').value = upt.business_pattern || '';
        document.getElementById('placement_year').value = upt.placement_year || '';
        document.getElementById('placement_kk').value = upt.placement_kk || 0;
        document.getElementById('handover_year').value = upt.handover_year || '';
        document.getElementById('handover_kk').value = upt.handover_kk || 0;
        document.getElementById('issue_note').value = upt.issue_note || '';
        
        if (upt.shm_status) {
            document.getElementById('shm_status').value = upt.shm_status;
        }

        // Radio issue_status
        const radios = document.getElementsByName('issue_status');
        for (let r of radios) {
            if (r.value === upt.issue_status) {
                r.checked = true;
            }
        }
    } catch (e) {
        console.error('Error parsing UPT data:', e);
    }
}

// Inisialisasi awal jika ada UPT terpilih dari URL
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('upt_location_id');
    if (select.value) {
        loadUptData(select.value);
    }
});
</script>
@endsection
