@extends('layouts.admin')
@section('atlas_subtitle', 'Perbarui profil, peran, atau reset kata sandi akun ini.')
@section('content')
@if($errors->any())
    <div class="atlas-alert atlas-alert--error" role="alert"><strong>Periksa kembali isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<section class="atlas-panel atlas-detail-intro">
    <div class="atlas-identity">
        <span class="atlas-code">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</span>
        <strong>{{ $user->name }}</strong>
        <span class="atlas-muted">{{ $user->email }} · {{ $user->regency?->name ?: 'Pemprov Kalsel' }}</span>
    </div>
    <span class="atlas-status atlas-status--{{ $user->is_active ? 'clean' : 'neutral' }}"><span aria-hidden="true"></span>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
</section>
<form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="atlas-stack">
    @csrf
    @method('PUT')
    @include('admin.manajemen_pengguna.partials.user-form', ['user' => $user])
    <div class="atlas-table-footer" style="border: 1px solid var(--atlas-line); border-radius: var(--atlas-radius); background: white;">
        <a class="atlas-link" href="{{ route('admin.users.index') }}">← Kembali ke Pengguna</a>
        <button type="submit" class="atlas-button atlas-button--primary">Simpan perubahan</button>
    </div>
</form>
@endsection
