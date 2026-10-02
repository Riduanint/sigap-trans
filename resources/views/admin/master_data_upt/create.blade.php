@extends('layouts.admin')
@section('atlas_subtitle', 'Daftarkan unit pemukiman transmigrasi baru ke registrasi induk provinsi.')
@section('atlas_actions')<a class="atlas-button" href="{{ route('admin.upt.index') }}">Batal</a>@endsection
@section('content')
@if($errors->any())
    <div class="atlas-alert atlas-alert--error" role="alert"><strong>Periksa kembali isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.upt.store') }}" class="atlas-stack">
    @csrf
    @include('admin.master_data_upt.partials.upt-form', ['upt' => null])
    <div class="atlas-table-footer" style="border: 1px solid var(--atlas-line); border-radius: var(--atlas-radius); background: white;">
        <span class="atlas-muted">Data tersimpan langsung ke registrasi induk dan tercatat di log aktivitas.</span>
        <button type="submit" class="atlas-button atlas-button--primary">Simpan data UPT</button>
    </div>
</form>
@endsection
