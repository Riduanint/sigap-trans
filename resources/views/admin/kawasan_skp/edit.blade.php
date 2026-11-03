@extends('layouts.admin')
@section('atlas_title', 'Perbarui Kawasan ' . $skp->skp_name)
@section('atlas_subtitle', $skp->kawasan_name . ' · Kabupaten ' . ($skp->regency?->name ?? 'Wilayah'))
@section('atlas_actions')
    <a class="atlas-button" href="{{ route('admin.skp.show', $skp->id) }}">Lihat rincian</a>
    <a class="atlas-button" href="{{ route('admin.skp.index') }}">Batal</a>
@endsection

@section('content')
<form action="{{ route('admin.skp.update', $skp->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;" data-confirm="Simpan seluruh perubahan data Kawasan & SKP ini?" data-confirm-title="Konfirmasi Perubahan SKP" data-confirm-type="warning" data-confirm-button="Ya, Simpan Perubahan">
    @csrf
    @method('PUT')

    @include('admin.kawasan_skp.partials.skp-form')

    <div class="atlas-form-actions" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
        <a class="atlas-button" href="{{ route('admin.skp.index') }}">Batal</a>
        <button type="submit" class="atlas-button atlas-button--primary">
            <x-admin.icon name="check" /> Simpan Perubahan
        </button>
    </div>
</form>

{{-- Modal diletakkan di luar form utama --}}
@include('admin.kawasan_skp.partials.modal-village-form')
@endsection
