@extends('layouts.admin')

@section('title', 'Ajukan Draf Usulan Pemutakhiran UPT')
@section('header_title', 'Formulir Pengajuan Draf Pemutakhiran Data UPT')
@section('header_subtitle', 'Ajukan revisi data kependudukan, status legalitas lahan, atau lampiran berkas BAST untuk diverifikasi Provinsi')

@section('content')
<div class="max-w-5xl mx-auto">

    <!-- Tombol Kembali -->
    <div class="mb-4">
        <a href="{{ route('operator.dashboard') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-[#0B1849] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Dasbor Operator</span>
        </a>
    </div>

    <!-- Alert Edukasi Prosedur Verifikasi -->
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="space-y-1 leading-relaxed">
            <span class="font-extrabold text-sm block text-amber-950">Alur Standar Pengajuan & Verifikasi Draf:</span>
            <p>
                Setiap data yang Anda kirimkan tidak langsung mengubah basis data utama secara permanen, melainkan dicatat sebagai <strong>Draf Usulan (Change Request)</strong>. Super Admin Provinsi akan melakukan komparasi data (<em>diff check</em>) sebelum memberikan persetujuan (<em>approval</em>).
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs">
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
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#0B1849] text-white flex items-center justify-center text-xs">1</span>
                    <span>Sasaran Lokasi UPT & Kategori Pengajuan</span>
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Dropdown UPT -->
                <div>
                    <label for="upt_location_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih Lokasi UPT <span class="text-rose-500">*</span>
                    </label>
                    <select name="upt_location_id" id="upt_location_id" required onchange="loadUptData(this.value)"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                        <option value="">-- Pilih Salah Satu UPT --</option>
                        @foreach($uptLocations as $upt)
                            <option value="{{ $upt->id }}" 
                                    {{ (old('upt_location_id', $selectedUpt->id ?? '') == $upt->id) ? 'selected' : '' }}
                                    data-upt="{{ json_encode($upt) }}">
                                [No. {{ $upt->upt_number }}] {{ $upt->upt_name }} ({{ $upt->current_village_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jenis Usulan -->
                <div>
                    <label for="request_type" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Jenis Usulan <span class="text-rose-500">*</span>
                    </label>
                    <select name="request_type" id="request_type" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
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
                </div>
            </div>

            <!-- Catatan Pengantar Pengajuan (Alasan Usulan) -->
            <div>
                <label for="submission_note" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Uraian Alasan / Pengantar Pengajuan <span class="text-rose-500">*</span>
                </label>
                <textarea name="submission_note" id="submission_note" rows="2" required
                          placeholder="Contoh: Berdasarkan hasil monitoring lapangan Triwulan III 2026, status sertifikasi telah tuntas 100% dan batas desa definitif telah disahkan perda..."
                          class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">{{ old('submission_note') }}</textarea>
            </div>
        </div>

        <!-- KARTU 2: PEMUTAKHIRAN PARAMETER DATA UPT -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#124D1C] text-white flex items-center justify-center text-xs">2</span>
                    <span>Data yang Diusulkan Berubah</span>
                </h3>
                <span class="text-[11px] text-slate-400 italic">Sesuaikan nilai yang ingin diperbarui</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="current_village_name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Desa Definitif Saat Ini</label>
                    <input type="text" name="current_village_name" id="current_village_name" 
                           value="{{ old('current_village_name', $selectedUpt->current_village_name ?? '') }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>

                <div>
                    <label for="business_pattern" class="block text-xs font-bold text-slate-700 mb-1.5">Pola Usaha Budidaya</label>
                    <input type="text" name="business_pattern" id="business_pattern" 
                           value="{{ old('business_pattern', $selectedUpt->business_pattern ?? '') }}"
                           placeholder="Contoh: TPLK, TPLB, PIRSUS Karet"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                </div>

                <div>
                    <label for="shm_status" class="block text-xs font-bold text-slate-700 mb-1.5">Status Sertifikasi SHM</label>
                    <select name="shm_status" id="shm_status" 
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">
                        @php
                            $currentShm = old('shm_status', $selectedUpt->shm_status ?? '100% SHM');
                        @endphp
                        <option value="100% SHM" {{ $currentShm === '100% SHM' ? 'selected' : '' }}>100% SHM (Selesai Penuh)</option>
                        <option value="Sebagian SHM" {{ $currentShm === 'Sebagian SHM' ? 'selected' : '' }}>Sebagian SHM (Tahap Redistribusi)</option>
                        <option value="Belum SHM" {{ $currentShm === 'Belum SHM' ? 'selected' : '' }}>Belum SHM (Proses GTRA)</option>
                    </select>
                </div>
            </div>

            <!-- Bagian Kependudukan (KK & Jiwa) -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-4">
                <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                    Dinamika Kependudukan (Penempatan & Serah Terima)
                </span>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="placement_year" class="block text-[11px] font-bold text-slate-600 mb-1">Tahun Penempatan</label>
                        <input type="text" name="placement_year" id="placement_year" 
                               value="{{ old('placement_year', $selectedUpt->placement_year ?? '') }}"
                               class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label for="placement_kk" class="block text-[11px] font-bold text-slate-600 mb-1">KK Penempatan</label>
                        <input type="number" name="placement_kk" id="placement_kk" 
                               value="{{ old('placement_kk', $selectedUpt->placement_kk ?? 0) }}"
                               class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label for="handover_year" class="block text-[11px] font-bold text-slate-600 mb-1">Tahun Serah Terima</label>
                        <input type="text" name="handover_year" id="handover_year" 
                               value="{{ old('handover_year', $selectedUpt->handover_year ?? '') }}"
                               class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label for="handover_kk" class="block text-[11px] font-bold text-slate-600 mb-1">KK Serah Terima</label>
                        <input type="number" name="handover_kk" id="handover_kk" 
                               value="{{ old('handover_kk', $selectedUpt->handover_kk ?? 0) }}"
                               class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                </div>
            </div>

            <!-- Status Permasalahan Lahan (Traffic Light) -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-4">
                <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                    Indikator Legalitas Lahan Agraria (Traffic Light)
                </span>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @php $stat = old('issue_status', $selectedUpt->issue_status ?? 'clean'); @endphp
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'clean' ? 'bg-emerald-50 border-emerald-500' : 'bg-white border-slate-200 hover:bg-slate-100' }}">
                        <input type="radio" name="issue_status" value="clean" {{ $stat === 'clean' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-900 block">🟢 Clean & Clear</span>
                            <span class="text-[10px] text-emerald-700">Lahan aman & sertifikasi tuntas</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'warning' ? 'bg-amber-50 border-amber-500' : 'bg-white border-slate-200 hover:bg-slate-100' }}">
                        <input type="radio" name="issue_status" value="warning" {{ $stat === 'warning' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="text-xs font-bold text-amber-900 block">🟡 Warning / Perhatian</span>
                            <span class="text-[10px] text-amber-700">Butuh pemeliharaan & monitoring</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $stat === 'critical' ? 'bg-rose-50 border-rose-500' : 'bg-white border-slate-200 hover:bg-slate-100' }}">
                        <input type="radio" name="issue_status" value="critical" {{ $stat === 'critical' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="text-xs font-bold text-rose-900 block">🔴 Prioritas Khusus / Sengketa</span>
                            <span class="text-[10px] text-rose-700">Overlap hutan KPHP / tambang</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label for="issue_note" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Catatan Deskripsi Masalah Lapangan
                    </label>
                    <textarea name="issue_note" id="issue_note" rows="2" 
                              placeholder="Uraikan detail masalah batas kawasan hutan, tumpang tindih perizinan, atau kondisi terkini..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-[#124D1C] focus:ring focus:ring-[#124D1C]/20 shadow-xs">{{ old('issue_note', $selectedUpt->issue_note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- KARTU 3: LAMPIRAN BERKAS DOKUMEN (OPSIONAL / JIKA NEW_BAST) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#E4B028] text-[#0B1849] flex items-center justify-center text-xs font-black">3</span>
                    <span>Lampiran Berkas Pendukung (PDF BAST / SK)</span>
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="document_name" class="block text-xs font-bold text-slate-700 mb-1.5">Judul Dokumen</label>
                    <input type="text" name="document_name" id="document_name" value="{{ old('document_name') }}"
                           placeholder="Contoh: Berita Acara Serah Terima (BAST) Final"
                           class="w-full text-xs rounded-xl border-slate-300">
                </div>
                <div>
                    <label for="document_number" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Register Dokumen</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}"
                           placeholder="Contoh: 560/124/BAST-DISNAKER/2026"
                           class="w-full text-xs rounded-xl border-slate-300">
                </div>
            </div>

            <div>
                <label for="document_file" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Unggah Berkas Scan PDF (Maksimal 20 MB)
                </label>
                <input type="file" name="document_file" id="document_file" accept=".pdf"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0B1849] file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>
        </div>

        <!-- TOMBOL SIMPAN & SUBMIT -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('operator.dashboard') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                Batalkan
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#124D1C] hover:bg-emerald-800 text-white text-xs font-black transition shadow-md flex items-center gap-2 border border-[#E4B028]/50">
                <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Kirim Draf Usulan ke Provinsi</span>
            </button>
        </div>

    </form>
</div>

<script>
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
</script>
@endsection
