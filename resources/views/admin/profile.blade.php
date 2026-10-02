@extends('layouts.admin')
@section('atlas_subtitle', 'Perbarui identitas akun dan kata sandi untuk akses ruang kerja.')
@section('content')
<div class="atlas-stack">
    <section class="atlas-panel atlas-panel-body"><div class="max-w-xl">@include('profile.partials.update-profile-information-form')</div></section>
    <section class="atlas-panel atlas-panel-body"><div class="max-w-xl">@include('profile.partials.update-password-form')</div></section>
    <section class="atlas-panel atlas-panel-body"><div class="max-w-xl">@include('profile.partials.delete-user-form')</div></section>
</div>
@endsection
