@extends('layouts.admin')
@section('atlas_title', 'Tambah Kawasan & SKP Baru')
@section('atlas_subtitle', 'Daftarkan kawasan transmigrasi, satuan pengembangan (SKP), profil desa, dan batas poligon WebGIS.')
@section('atlas_actions')
    <a class="atlas-button" href="{{ route('admin.skp.index') }}">Batal</a>
@endsection

@section('content')
<form action="{{ route('admin.skp.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;" data-confirm="Simpan data Kawasan & SKP baru ini ke sistem?" data-confirm-title="Konfirmasi Tambah SKP" data-confirm-button="Ya, Simpan SKP">
    @csrf

    @include('admin.kawasan_skp.partials.skp-form')

    <div class="atlas-form-actions" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
        <a class="atlas-button" href="{{ route('admin.skp.index') }}">Batal</a>
        <button type="submit" class="atlas-button atlas-button--primary">
            <x-admin.icon name="check" /> Simpan Kawasan & SKP
        </button>
    </div>
</form>

{{-- Modal diletakkan di luar form utama --}}
@include('admin.kawasan_skp.partials.modal-village-form')
@endsection
