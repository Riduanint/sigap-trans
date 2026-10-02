@extends('layouts.admin')

@section('atlas_title', 'Ajukan draf usulan')
@section('atlas_subtitle', 'Usulan Anda tidak langsung mengubah master data — Super Admin Provinsi memeriksa perubahan sebelum persetujuan.')

@section('content')
<form action="{{ route('operator.requests.store') }}" method="POST" enctype="multipart/form-data" class="atlas-stack">
    @csrf

    @if(isset($errors) && $errors->any())
        <div class="atlas-alert atlas-alert--error" role="alert">
            <strong>Periksa kembali isian berikut.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Bagian 1: Sasaran & kategori --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h3>Sasaran lokasi & kategori pengajuan</h3>
            <span class="atlas-muted">Langkah 1 dari 3</span>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field atlas-field--wide">
                    <label for="filter_regency_select">Saring kabupaten</label>
                    <select id="filter_regency_select" onchange="filterUptByRegency(this.value)">
                        <option value="all">Semua kabupaten</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" @selected($selectedUpt && $selectedUpt->regency_id == $reg->id)>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="upt_location_id">Lokasi UPT <span aria-hidden="true">*</span></label>
                    <select name="upt_location_id" id="upt_location_id" required onchange="loadUptData(this.value)">
                        <option value="">— Pilih lokasi UPT —</option>
                        @foreach($regencies as $reg)
                            @php $rUpts = $uptLocations->where('regency_id', $reg->id); @endphp
                            @if($rUpts->isNotEmpty())
                                <optgroup label="{{ $reg->name }} ({{ $rUpts->count() }} UPT)" data-regency-id="{{ $reg->id }}">
                                    @foreach($rUpts as $upt)
                                        <option value="{{ $upt->id }}"
                                                @selected(old('upt_location_id', $selectedUpt->id ?? '') == $upt->id)
                                                data-regency="{{ $upt->regency_id }}"
                                                data-upt="{{ json_encode($upt) }}">
                                            UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->upt_name }} ({{ $upt->current_village_name ?: 'Desa belum tercatat' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p class="atlas-form-hint">Pilih UPT terlebih dulu — data terkini otomatis terisi pada bagian 2.</p>
                </div>
                <div class="atlas-field">
                    <label for="request_type">Jenis usulan <span aria-hidden="true">*</span></label>
                    <select name="request_type" id="request_type" required>
                        <option value="DATA_UPDATE" @selected(old('request_type') === 'DATA_UPDATE')>Pemutakhiran data lapangan / kependudukan / desa</option>
                        <option value="LEGAL_ISSUE" @selected(old('request_type') === 'LEGAL_ISSUE')>Pelaporan sengketa / tumpang tindih kawasan</option>
                        <option value="NEW_BAST" @selected(old('request_type') === 'NEW_BAST')>Pengajuan berkas BAST baru</option>
                    </select>
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="submission_note">Uraian alasan / pengantar pengajuan <span aria-hidden="true">*</span></label>
                    <textarea name="submission_note" id="submission_note" rows="3" required placeholder="Contoh: hasil monitoring lapangan triwulan III 2026 menunjukkan sertifikasi telah tuntas dan batas desa definitif telah disahkan.">{{ old('submission_note') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian 2: Data yang diusulkan berubah --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h3>Data yang diusulkan berubah</h3>
            <span class="atlas-muted">Sesuaikan nilai yang ingin diperbarui</span>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="current_village_name">Nama desa definitif</label>
                    <input type="text" name="current_village_name" id="current_village_name" value="{{ old('current_village_name', $selectedUpt->current_village_name ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="business_pattern">Pola usaha budidaya</label>
                    <input type="text" name="business_pattern" id="business_pattern" value="{{ old('business_pattern', $selectedUpt->business_pattern ?? '') }}" placeholder="Contoh: TPLK, TPLB, PIRSUS Karet">
                </div>
                <div class="atlas-field">
                    <label for="shm_status">Status sertifikasi SHM</label>
                    <select name="shm_status" id="shm_status">
                        @php $currentShm = old('shm_status', $selectedUpt->shm_status ?? ''); @endphp
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
                    <input type="text" name="placement_year" id="placement_year" value="{{ old('placement_year', $selectedUpt->placement_year ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="placement_kk">KK penempatan</label>
                    <input type="number" name="placement_kk" id="placement_kk" value="{{ old('placement_kk', $selectedUpt->placement_kk ?? 0) }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_year">Tahun serah terima</label>
                    <input type="text" name="handover_year" id="handover_year" value="{{ old('handover_year', $selectedUpt->handover_year ?? '') }}">
                </div>
                <div class="atlas-field">
                    <label for="handover_kk">KK serah terima</label>
                    <input type="number" name="handover_kk" id="handover_kk" value="{{ old('handover_kk', $selectedUpt->handover_kk ?? 0) }}">
                </div>
            </div>

            <p class="atlas-form-section-title" style="margin: 20px 0 12px;">Permasalahan lahan</p>
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="issue_status">Status permasalahan</label>
                    <select name="issue_status" id="issue_status">
                        @php $stat = old('issue_status', $selectedUpt->issue_status ?? 'clean'); @endphp
                        <option value="clean" @selected($stat === 'clean')>Clean & Clear — bebas sengketa</option>
                        <option value="warning" @selected($stat === 'warning')>Monitoring — kendala tanggul / fasum</option>
                        <option value="critical" @selected($stat === 'critical')>Prioritas mediasi — tumpang tindih / klaim</option>
                    </select>
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="issue_note">Catatan deskripsi masalah lapangan</label>
                    <textarea name="issue_note" id="issue_note" rows="3" placeholder="Uraikan detail batas kawasan hutan, tumpang tindih perizinan, atau kondisi terkini.">{{ old('issue_note', $selectedUpt->issue_note ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian 3: Lampiran --}}
    <section class="atlas-panel">
        <div class="atlas-panel-heading">
            <h3>Lampiran berkas pendukung</h3>
            <span class="atlas-muted">Opsional · wajib untuk usulan BAST baru</span>
        </div>
        <div class="atlas-panel-body">
            <div class="atlas-form-grid">
                <div class="atlas-field">
                    <label for="document_name">Judul dokumen</label>
                    <input type="text" name="document_name" id="document_name" value="{{ old('document_name') }}" placeholder="Contoh: Berita Acara Serah Terima (BAST) Final">
                </div>
                <div class="atlas-field">
                    <label for="document_number">Nomor register dokumen</label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}" placeholder="Contoh: 560/124/BAST-DISNAKER/2026">
                </div>
                <div class="atlas-field atlas-field--wide">
                    <label for="document_file">Berkas scan PDF (maksimal 20 MB)</label>
                    <input type="file" name="document_file" id="document_file" accept=".pdf">
                </div>
            </div>
        </div>
    </section>

    <div class="atlas-inline" style="justify-content: flex-end;">
        <a href="{{ route('operator.requests.index') }}" class="atlas-button">Batalkan</a>
        <button type="submit" class="atlas-button atlas-button--primary"><x-admin.icon name="check" /> Kirim draf usulan ke provinsi</button>
    </div>
</form>

<script>
function filterUptByRegency(regencyId) {
    const select = document.getElementById('upt_location_id');
    const optgroups = select.querySelectorAll('optgroup');
    optgroups.forEach(group => {
        group.style.display = (regencyId === 'all' || group.getAttribute('data-regency-id') === regencyId) ? '' : 'none';
    });
}

function loadUptData(uptId) {
    if (!uptId) return;
    const select = document.getElementById('upt_location_id');
    const dataStr = select.options[select.selectedIndex].getAttribute('data-upt');
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
        if (upt.shm_status) document.getElementById('shm_status').value = upt.shm_status;
        if (upt.issue_status) document.getElementById('issue_status').value = upt.issue_status;
    } catch (e) {
        console.error('Gagal membaca data UPT:', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('upt_location_id');
    if (select.value) loadUptData(select.value);
});
</script>
@endsection
