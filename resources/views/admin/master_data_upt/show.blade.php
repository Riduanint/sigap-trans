@extends('layouts.admin')
@section('atlas_subtitle', 'Identitas lokasi, riwayat kependudukan, dan dokumen pendukung dalam satu tempat.')
@section('atlas_actions')<a class="atlas-button atlas-button--primary" href="{{ route('admin.upt.edit', $upt->id) }}">Perbarui data UPT</a>@endsection
@section('content')
@php $tab = in_array(request('tab'), ['population', 'land', 'documents']) ? request('tab') : 'overview'; @endphp
<div class="atlas-stack">
    <section class="atlas-panel atlas-detail-intro"><x-admin.upt-identity :upt="$upt" :link="false" /><x-admin.status :status="$upt->issue_status" /></section>
    <nav class="atlas-tabs" aria-label="Bagian profil UPT">
        @foreach(['overview' => 'Ringkasan', 'population' => 'Kependudukan', 'land' => 'Pertanahan', 'documents' => 'Dokumen'] as $key => $label)<a href="{{ route('admin.upt.show', ['id' => $upt->id, 'tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach
    </nav>
    @if($tab === 'overview')
        <div class="atlas-detail-grid">
            <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Identitas wilayah</h2></div><dl class="atlas-panel-body atlas-definition"><div><dt>Kabupaten</dt><dd>{{ $upt->regency?->name }}</dd></div><div><dt>Pola usaha</dt><dd>{{ $upt->business_pattern }}</dd></div><div><dt>Tahun penempatan</dt><dd>{{ $upt->placement_year }}</dd></div><div><dt>Tahun serah terima</dt><dd>{{ $upt->handover_year ?: 'Belum tercatat' }}</dd></div><div><dt>Latitude</dt><dd>{{ $upt->latitude ?? 'Belum tercatat' }}</dd></div><div><dt>Longitude</dt><dd>{{ $upt->longitude ?? 'Belum tercatat' }}</dd></div><div><dt>Publikasi kabupaten</dt><dd>{{ $upt->regency?->is_visible ? 'Tampil di peta publik' : 'Belum dipublikasikan' }}</dd></div><div><dt>Terakhir diperbarui</dt><dd>{{ $upt->updated_at?->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA</dd></div></dl></section>
            <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Konteks lokasi</h2></div><div id="atlas-work-map" class="atlas-map" aria-label="Peta lokasi UPT"></div><div class="atlas-map-status" id="atlas-map-status">Koordinat tercatat pada data UPT.</div><div class="atlas-map-caption"><strong id="atlas-map-name">{{ $upt->upt_name }}</strong><p class="atlas-muted" id="atlas-map-location">{{ $upt->current_village_name }}</p><a id="atlas-map-detail" hidden></a>@if($upt->latitude !== null && $upt->longitude !== null)<a href="{{ route('home', ['upt_id' => $upt->id]) }}" target="_blank" rel="noopener">Buka peta publik ↗</a>@endif</div></section>
            @php $mapData = [['id' => $upt->id, 'upt_number' => $upt->upt_number, 'upt_name' => $upt->upt_name, 'current_village_name' => $upt->current_village_name, 'regency_name' => $upt->regency?->name, 'latitude' => $upt->latitude, 'longitude' => $upt->longitude, 'issue_status' => $upt->issue_status, 'detail_url' => route('admin.upt.show', $upt->id)]]; @endphp
            <script type="application/json" id="atlas-map-data">{!! json_encode($mapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        </div>
    @elseif($tab === 'population')
        <div class="atlas-detail-grid">
            @foreach(['placement' => 'Penempatan awal', 'handover' => 'Serah terima'] as $stage => $label)
                <section class="atlas-panel"><div class="atlas-panel-heading"><h2>{{ $label }}</h2><span class="atlas-muted">{{ $upt->{$stage . '_year'} ?: 'Tahun belum tercatat' }}</span></div><div class="atlas-panel-body"><dl class="atlas-definition"><div><dt>Kepala keluarga</dt><dd>{{ number_format($upt->{$stage . '_kk'}, 0, ',', '.') }} KK</dd></div><div><dt>Penduduk</dt><dd>{{ number_format($upt->{$stage . '_population'}, 0, ',', '.') }} jiwa</dd></div></dl><div class="atlas-inline mt-5"><a class="atlas-button atlas-button--primary" href="{{ route('admin.upt.registri', ['id' => $upt->id, 'stage' => $stage]) }}">Lihat registri warga <x-admin.icon name="arrow" /></a><button type="button" class="atlas-button" onclick="openRegistryModal({{ $upt->id }}, {{ Illuminate\Support\Js::from($upt->upt_name) }}, '{{ $stage }}')">Pengisian & impor</button></div></div></section>
            @endforeach
        </div>
        <p class="atlas-muted">Selisih serah terima–penempatan: {{ number_format($upt->handover_kk - $upt->placement_kk, 0, ',', '.') }} KK. Selisih rekap tidak menunjukkan penyebab perubahan penduduk.</p>
        @include('admin.registri_warga.registri_warga')
    @elseif($tab === 'land')
        <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Pertanahan dan permasalahan</h2><x-admin.status :status="$upt->issue_status" /></div><div class="atlas-panel-body atlas-stack"><dl class="atlas-definition"><div><dt>Status sertifikasi SHM</dt><dd>{{ $upt->shm_status ?: 'Belum tercatat' }}</dd></div><div><dt>Data batas area</dt><dd>{{ $upt->polygon_geojson ? 'Tersedia dalam data UPT' : 'Belum tercatat' }}</dd></div></dl><div><p class="atlas-muted">Catatan permasalahan</p><p class="atlas-reading mt-2">{{ $upt->issue_note ?: 'Belum ada catatan permasalahan.' }}</p></div></div></section>
    @else
        <section class="atlas-panel"><div class="atlas-panel-heading"><h2>Dokumen pendukung</h2><a href="{{ route('admin.documents.index') }}">Kelola arsip →</a></div><div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th>Nama berkas</th><th>Jenis</th><th>Nomor dokumen</th><th>Tindakan</th></tr></thead><tbody>@forelse($upt->documents as $document)<tr><td>{{ $document->file_name }}</td><td>{{ $document->document_type }}</td><td>{{ $document->document_number ?: 'Belum tercatat' }}</td><td><a class="atlas-link" href="{{ route('admin.documents.download', $document->id) }}">Unduh</a></td></tr>@empty<tr><td colspan="4"><div class="atlas-empty">Belum ada dokumen pendukung. Unggah berkas melalui Arsip dokumen.</div></td></tr>@endforelse</tbody></table></div></section>
    @endif
    <a class="atlas-link" href="{{ route('admin.upt.index') }}">← Kembali ke Data UPT</a>
</div>
@endsection
