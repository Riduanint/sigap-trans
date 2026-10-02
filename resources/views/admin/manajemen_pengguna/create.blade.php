@extends('layouts.admin')
@section('atlas_subtitle', 'Tambahkan aparatur provinsi, operator kabupaten, eksekutif, atau mitra BPN.')
@section('atlas_actions')<a class="atlas-button" href="{{ route('admin.users.index') }}">Batal</a>@endsection
@section('content')
@if($errors->any())
    <div class="atlas-alert atlas-alert--error" role="alert"><strong>Periksa kembali isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.users.store') }}" class="atlas-stack">
    @csrf
    @include('admin.manajemen_pengguna.partials.user-form', ['user' => null])
    <div class="atlas-table-footer" style="border: 1px solid var(--atlas-line); border-radius: var(--atlas-radius); background: white;">
        <span class="atlas-muted">Akun baru akan langsung aktif dan tercatat pada log audit sistem.</span>
        <button type="submit" class="atlas-button atlas-button--primary">Buat akun baru</button>
    </div>
</form>
@endsection
