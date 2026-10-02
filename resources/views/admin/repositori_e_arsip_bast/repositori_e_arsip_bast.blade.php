@extends('layouts.admin')
@section('atlas_subtitle', 'Berkas BAST, SK pelepasan kawasan, dan buku tanah per UPT.')
@section('content')
@php
    $typeLabels = ['BAST' => 'BAST', 'SK_GUBERNUR' => 'SK Gubernur', 'SK_MENTERI' => 'SK Menteri', 'BUKU_TANAH' => 'Buku tanah'];
    $activeFilters = collect(request()->only('search', 'regency_id', 'type'))->filter(fn ($value) => filled($value) && $value !== 'all');
    // Operator memakai modul dokumennya sendiri (tanpa unggah/hapus); Super Admin punya akses penuh.
    $isOperatorView = auth()->user()?->isOperator() ?? false;
    $canManage = ! $isOperatorView;
    $docsIndex = $isOperatorView ? 'operator.documents.index' : 'admin.documents.index';
    $docsDownload = $isOperatorView ? 'operator.documents.download' : 'admin.documents.download';
    $uptLinkRoute = $isOperatorView ? 'operator.upt.index' : 'admin.upt.show';
    $uptLinkParams = fn ($uptId) => $isOperatorView ? [] : ['id' => $uptId, 'tab' => 'documents'];
@endphp
@if($canManage)
@section('atlas_actions')<button type="button" class="atlas-button atlas-button--primary" data-atlas-dialog-open="atlas-upload-dialog" data-atlas-dialog-focus="#doc-upload-upt"><x-admin.icon name="plus" /> Unggah arsip</button>@endsection
@endif
<div class="atlas-metrics" aria-label="Ringkasan arsip">
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_docs'], 0, ',', '.') }}</strong><small>Total arsip digital</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_bast'], 0, ',', '.') }}</strong><small>Berita acara (BAST)</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_sk'], 0, ',', '.') }}</strong><small>SK Gubernur & Menteri</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($stats['total_buku_tanah'], 0, ',', '.') }}</strong><small>Buku tanah / warkah SHM</small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route($docsIndex) }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="doc-search">Cari arsip</label><input id="doc-search" name="search" value="{{ request('search') }}" placeholder="Nomor register, nama berkas, atau UPT" type="search"></div>
            <div class="atlas-field"><label for="doc-type">Jenis berkas</label><select id="doc-type" name="type"><option value="all">Semua jenis</option>@foreach($typeLabels as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="doc-regency">Kabupaten</label><select id="doc-regency" name="regency_id"><option value="all">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route($docsIndex, request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'type' ? ($typeLabels[$value] ?? $value) : ($key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route($docsIndex) }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($documents->total(), 0, ',', '.') }} berkas ditemukan</h2><span class="atlas-muted">Unggahan PDF maksimal 20 MB per berkas</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Jenis & lokasi</th><th scope="col">Nomor register</th><th scope="col">Nama berkas</th><th scope="col">Ukuran</th><th scope="col">Pengunggah</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($documents as $doc)
            <tr>
                <td><div class="atlas-identity"><span class="atlas-code">{{ $typeLabels[$doc->document_type] ?? $doc->document_type }}</span><a href="{{ route($uptLinkRoute, $uptLinkParams($doc->uptLocation?->id)) }}">UPT-{{ str_pad($doc->uptLocation?->upt_number ?? 0, 3, '0', STR_PAD_LEFT) }} · {{ $doc->uptLocation?->upt_name }}</a><span class="atlas-muted">{{ $doc->uptLocation?->regency?->name ?? 'Kabupaten belum tercatat' }}</span></div></td>
                <td>{{ $doc->document_number ?: 'Belum tercatat' }}</td>
                <td><span title="{{ $doc->file_name }}">{{ \Illuminate\Support\Str::limit($doc->file_name, 42) }}</span></td>
                <td class="tabular-nums">{{ number_format($doc->file_size_kb, 0, ',', '.') }} KB</td>
                <td>{{ $doc->uploader?->name ?: 'Super Admin' }}</td>
                <td>
                    <div class="atlas-inline">
                        <a class="atlas-link" href="{{ route($docsDownload, $doc->id) }}">Unduh</a>
                        @if($canManage)
                        <form method="POST" action="{{ route('admin.documents.destroy', $doc->id) }}" onsubmit="return confirm('Hapus arsip digital ini? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="atlas-link" style="color: var(--atlas-critical);">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="atlas-empty"><strong>Belum ada arsip digital.</strong>@if($canManage)<p>Unggah berkas BAST, SK, atau buku tanah pertama Anda.</p><button type="button" class="atlas-link" data-atlas-dialog-open="atlas-upload-dialog" data-atlas-dialog-focus="#doc-upload-upt">Unggah arsip →</button>@else<p>Arsip akan tampil di sini setelah diunggah oleh Super Admin Provinsi.</p>@endif</div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $documents->firstItem() ?? 0 }}–{{ $documents->lastItem() ?? 0 }} dari {{ $documents->total() }} berkas</span>{{ $documents->links('admin.partials.pagination') }}</div>
</section>

@if($canManage)
{{-- Dialog unggah arsip --}}
<div class="atlas-dialog" id="atlas-upload-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-upload-dialog-title">
    <div class="atlas-dialog__panel">
        <div class="atlas-dialog__head">
            <div><h3 id="atlas-upload-dialog-title">Unggah arsip digital</h3><p>Simpan berkas resmi ke registrasi UPT</p></div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <form method="POST" action="{{ route('admin.documents.upload') }}" enctype="multipart/form-data" class="atlas-dialog__body">
            @csrf
            <div class="atlas-field">
                <label for="doc-upload-upt">Lokasi UPT <span aria-hidden="true">*</span></label>
                <select id="doc-upload-upt" name="upt_location_id" required>
                    <option value="">— Pilih UPT —</option>
                    @foreach($uptLocations as $u)
                        <option value="{{ $u->id }}">UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $u->upt_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="atlas-field">
                <label for="doc-upload-type">Jenis berkas <span aria-hidden="true">*</span></label>
                <select id="doc-upload-type" name="document_type" required>@foreach(['BAST' => 'Berita Acara Serah Terima (BAST)', 'SK_GUBERNUR' => 'SK Penetapan Gubernur', 'SK_MENTERI' => 'SK Pelepasan Kawasan Hutan (Menteri)', 'BUKU_TANAH' => 'Buku Tanah / Warkah Sertipikat SHM'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
            </div>
            <div class="atlas-field">
                <label for="doc-upload-number">Nomor register resmi</label>
                <input type="text" id="doc-upload-number" name="document_number" maxlength="100" placeholder="Contoh: BAST/503/1977">
            </div>
            <div class="atlas-field">
                <label for="doc-upload-file">Berkas PDF <span aria-hidden="true">*</span></label>
                <input type="file" id="doc-upload-file" name="file" accept="application/pdf" required>
                <p class="atlas-form-hint">Ukuran maksimal 20 MB.</p>
            </div>
            <div class="atlas-dialog__foot" style="padding: 0; border: 0; background: none;">
                <button type="button" class="atlas-button" data-atlas-dialog-close>Batal</button>
                <button type="submit" class="atlas-button atlas-button--primary">Simpan ke arsip</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
