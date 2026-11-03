@extends('layouts.admin')

@section('atlas_title', 'Edit draf usulan pemutakhiran UPT')
@section('atlas_subtitle', 'Perbarui usulan perubahan data lapangan UPT sebelum diverifikasi Super Admin.')

@section('content')
<form action="{{ route('operator.requests.update', $changeRequest->id) }}" method="POST" enctype="multipart/form-data" class="atlas-stack"
      data-confirm="Simpan perubahan draf usulan pemutakhiran ini?" 
      data-confirm-title="Konfirmasi Perubahan Usulan" 
      data-confirm-button="Ya, Simpan Perubahan">
    @csrf
    @method('PUT')

    @if(isset($errors) && $errors->any())
        <div class="atlas-alert atlas-alert--error" role="alert">
            <strong>Periksa kembali isian berikut.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Bagian 1: Sasaran & kategori --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <div>
                <h3>Sasaran lokasi & kategori pengajuan</h3>
                <p class="atlas-muted">UPT terdaftar yang diajukan pemutakhirannya.</p>
            </div>
            <span class="atlas-status atlas-status--clean">Edit Pemutakhiran</span>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field atlas-field--wide">
                    <label>Lokasi UPT yang diajukan</label>
                    <input type="text" readonly disabled class="bg-[#F8FAFC]"
                           value="UPT-{{ str_pad($selectedUpt?->upt_number ?? 0, 3, '0', STR_PAD_LEFT) }} · {{ $selectedUpt?->upt_name }} ({{ $selectedUpt?->regency?->name }})">
                    <input type="hidden" name="upt_location_id" value="{{ $selectedUpt?->id }}">
                </div>
                <div class="atlas-field">
                    <label for="request_type">Jenis usulan pemutakhiran <span aria-hidden="true">*</span></label>
                    <select name="request_type" id="request_type" required>
                        @php $curType = old('request_type', $changeRequest->request_type); @endphp
                        <option value="DATA_UPDATE" @selected($curType === 'DATA_UPDATE')>Pemutakhiran data lapangan / kependudukan / desa</option>
                        <option value="LEGAL_ISSUE" @selected($curType === 'LEGAL_ISSUE')>Pelaporan sengketa / tumpang tindih kawasan</option>
                        <option value="NEW_BAST" @selected($curType === 'NEW_BAST')>Pengajuan berkas BAST baru</option>
                    </select>
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="submission_note">Uraian alasan / pengantar pengajuan <span aria-hidden="true">*</span></label>
                    <textarea name="submission_note" id="submission_note" rows="3" required placeholder="Contoh: hasil monitoring lapangan triwulan III menunjukkan sertifikasi telah tuntas dan batas desa definitif telah disahkan.">{{ old('submission_note', $payload['submission_note'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian 2: Data yang diusulkan berubah --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <div>
                <h3>Data yang diusulkan berubah</h3>
                <p class="atlas-muted">Sesuaikan nilai yang ingin diperbarui sesuai kondisi lapangan terkini.</p>
            </div>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="current_village_name">Nama desa definitif</label>
                    <input type="text" name="current_village_name" id="current_village_name" value="{{ old('current_village_name', $payload['current_village_name'] ?? $selectedUpt?->current_village_name ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="business_pattern">Pola usaha budidaya</label>
                    <input type="text" name="business_pattern" id="business_pattern" value="{{ old('business_pattern', $payload['business_pattern'] ?? $selectedUpt?->business_pattern ?? '') }}" placeholder="Contoh: TPLK, TPLB, PIRSUS Karet">
                </div>
                <div class="atlas-field">
                    <label for="shm_status">Status sertifikasi SHM</label>
                    <select name="shm_status" id="shm_status">
                        @php $currentShm = old('shm_status', $payload['shm_status'] ?? $selectedUpt?->shm_status ?? ''); @endphp
                        <option value="100% SHM" @selected($currentShm === '100% SHM')>100% SHM — selesai penuh</option>
                        <option value="Sebagian SHM" @selected($currentShm === 'Sebagian SHM')>Sebagian SHM — tahap redistribusi</option>
                        <option value="Belum SHM" @selected($currentShm === 'Belum SHM')>Belum SHM — proses GTRA</option>
                        @if(! in_array($currentShm, ['100% SHM', 'Sebagian SHM', 'Belum SHM'], true) && $currentShm !== '')
                            <option value="{{ $currentShm }}" selected>{{ $currentShm }} — nilai tercatat saat ini</option>
                        @endif
                    </select>
                </div>
            </div>

            <p class="atlas-form-section-title" style="margin: 20px 0 12px;">Dinamika kependudukan</p>
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="placement_year">Tahun penempatan</label>
                    <input type="text" name="placement_year" id="placement_year" value="{{ old('placement_year', $payload['placement_year'] ?? $selectedUpt?->placement_year ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="placement_kk">KK penempatan</label>
                    <input type="number" name="placement_kk" id="placement_kk" min="0" value="{{ old('placement_kk', $payload['placement_kk'] ?? $selectedUpt?->placement_kk ?? 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="placement_population">Jiwa penempatan</label>
                    <input type="number" name="placement_population" id="placement_population" min="0" value="{{ old('placement_population', $payload['placement_population'] ?? $selectedUpt?->placement_population ?? 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_year">Tahun serah terima</label>
                    <input type="text" name="handover_year" id="handover_year" value="{{ old('handover_year', $payload['handover_year'] ?? $selectedUpt?->handover_year ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_kk">KK serah terima</label>
                    <input type="number" name="handover_kk" id="handover_kk" min="0" value="{{ old('handover_kk', $payload['handover_kk'] ?? $selectedUpt?->handover_kk ?? 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_population">Jiwa serah terima</label>
                    <input type="number" name="handover_population" id="handover_population" min="0" value="{{ old('handover_population', $payload['handover_population'] ?? $selectedUpt?->handover_population ?? 0) }}">
                </div>
            </div>

            <p class="atlas-form-section-title" style="margin: 20px 0 12px;">Permasalahan lahan</p>
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="issue_status">Status permasalahan</label>
                    <select name="issue_status" id="issue_status">
                        @php $stat = old('issue_status', $payload['issue_status'] ?? $selectedUpt?->issue_status ?? 'clean'); @endphp
                        <option value="clean" @selected($stat === 'clean')>Clean & Clear — bebas sengketa</option>
                        <option value="warning" @selected($stat === 'warning')>Monitoring — kendala tanggul / fasum</option>
                        <option value="critical" @selected($stat === 'critical')>Prioritas mediasi — tumpang tindih / klaim</option>
                    </select>
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="issue_note">Catatan deskripsi masalah lapangan</label>
                    <textarea name="issue_note" id="issue_note" rows="3" placeholder="Uraikan detail batas kawasan hutan, tumpang tindih perizinan, atau kondisi terkini.">{{ old('issue_note', $payload['issue_note'] ?? $selectedUpt?->issue_note ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian 3: Lampiran berkas pendukung --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <div>
                <h3>Lampiran berkas pendukung</h3>
                <p class="atlas-muted">Opsional · SK Pembentukan, Berita Acara, atau Peta Batas (PDF maks 20 MB)</p>
            </div>
        </div>
        <div class="atlas-panel-body">
            @if(isset($payload['attached_document']))
                <div class="atlas-alert" style="margin-bottom: 14px;" role="status">
                    <span>Berkas terlampir saat ini: <strong>{{ $payload['attached_document']['document_name'] ?? 'Dokumen Pendukung' }}</strong> ({{ $payload['attached_document']['document_number'] ?? '-' }}). Unggah file baru di bawah jika ingin mengganti.</span>
                </div>
            @endif
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="document_name">Judul dokumen</label>
                    <input type="text" name="document_name" id="document_name" value="{{ old('document_name', $payload['attached_document']['document_name'] ?? '') }}" placeholder="Contoh: SK Penetapan Lokasi / BAST UPT">
                </div>
                <div class="atlas-field">
                    <label for="document_number">Nomor register dokumen</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number', $payload['attached_document']['document_number'] ?? '') }}" placeholder="Contoh: 560/125/SK-BUP/2026">
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="document_file">Ganti Berkas PDF (Opsional)</label>
                    <input type="file" name="document_file" id="document_file" accept=".pdf">
                </div>
            </div>
        </div>
    </section>

    {{-- Tombol Aksi Bawah --}}
    <div class="atlas-inline" style="justify-content: flex-end; gap: 12px;">
        <a href="{{ route('operator.requests.index') }}" class="atlas-button">Batalkan</a>
        <button type="submit" class="atlas-button atlas-button--primary">
            <x-admin.icon name="check" />
            <span>Simpan perubahan usulan</span>
        </button>
    </div>
</form>
@endsection

