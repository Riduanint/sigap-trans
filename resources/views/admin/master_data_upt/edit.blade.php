@extends('layouts.admin')
@section('atlas_subtitle', 'Perbarui atribut desa, status lahan, dan rekap kependudukan lokasi ini.')
@section('content')
@if($errors->any())
    <div class="atlas-alert atlas-alert--error" role="alert"><strong>Periksa kembali isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<section class="atlas-panel atlas-detail-intro">
    <x-admin.upt-identity :upt="$upt" :link="false" />
    <x-admin.status :status="$upt->issue_status" />
</section>
<form method="POST" action="{{ route('admin.upt.update', $upt->id) }}" class="atlas-stack">
    @csrf
    @method('PUT')
    @include('admin.master_data_upt.partials.upt-form', ['upt' => $upt])
    <div class="atlas-table-footer" style="border: 1px solid var(--atlas-line); border-radius: var(--atlas-radius); background: white;">
        <a class="atlas-link" href="{{ route('admin.upt.show', $upt->id) }}">← Kembali ke profil UPT</a>
        <button type="submit" class="atlas-button atlas-button--primary">Simpan perubahan</button>
    </div>
</form>
@endsection
