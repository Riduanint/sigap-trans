@extends('layouts.admin')

@php
    $subtitle = match($domain) {
        'skp' => 'Visualisasi luas kawasan, satuan pemukiman, dan sebaran desa definitif SKP.',
        'kependudukan' => 'Analisis kependudukan, registri KK, rasio penempatan awal, dan serah terima pemda.',
        'pertanahan' => 'Monitoring sertipikasi SHM transmigrasi dan analisis permasalahan batas/kawasan.',
        'dokumen' => 'Ikhtisar berkas digital, warkah BAST penyerahan, dan SK penetapan menteri/gubernur.',
        default => 'Ikhtisar penyerahan kawasan, tren penempatan per dekade, dan perbandingan kabupaten.',
    };
@endphp

@section('atlas_subtitle', $subtitle)

@section('content')
    {{-- Bilah Navigasi Domain & Filter Kabupaten --}}
    @include('admin.analitik.partials.toolbar')

    {{-- Konten Dinamis Berdasarkan Domain Aktif --}}
    @include('admin.analitik.partials.domain-' . $domain)
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
