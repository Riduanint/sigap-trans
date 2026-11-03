@extends('layouts.admin')
@section('atlas_title', 'Rincian ' . $skp->skp_name)
@section('atlas_subtitle', $skp->kawasan_name . ' · Kabupaten ' . ($skp->regency?->name ?? 'Wilayah'))
@section('atlas_actions')
    <a class="atlas-button" href="{{ route('admin.skp.index') }}">← Kembali ke daftar</a>
    <a class="atlas-button atlas-button--primary" href="{{ route('admin.skp.edit', $skp->id) }}">
        <x-admin.icon name="check" /> Perbarui data
    </a>
@endsection

@section('content')
{{-- Kartu Ringkasan --}}
<div class="atlas-metrics" aria-label="Metrik kawasan">
    <div class="atlas-metric">
        <div>
            <strong>{{ $skp->villages->count() }}</strong>
            <small>Desa definitif tercakup</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong class="tabular-nums">{{ number_format($skp->luas_ha, 2, ',', '.') }}<span> Ha</span></strong>
            <small>Estimasi total luas kawasan</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong class="tabular-nums">{{ number_format($skp->total_population, 0, ',', '.') }}<span> Jiwa</span></strong>
            <small>{{ number_format($skp->villages->sum('population_male'), 0, ',', '.') }} L · {{ number_format($skp->villages->sum('population_female'), 0, ',', '.') }} P</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong class="tabular-nums">{{ number_format($skp->total_family_count, 0, ',', '.') }}<span> KK</span></strong>
            <small>Kepala keluarga terdata</small>
        </div>
    </div>
</div>

{{-- Panel Informasi Pokok --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Profil Satuan Kawasan Pengembangan</h2>
        <span class="atlas-muted">Rincian ketetapan & wilayah administratif</span>
    </div>
    <div class="atlas-panel-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div>
            <span class="atlas-muted" style="font-size: 11px; text-transform: uppercase;">Kawasan Induk</span>
            <strong style="display: block; font-size: 15px; color: #0E2A47; margin-top: 2px;">{{ $skp->kawasan_name }}</strong>
        </div>
        <div>
            <span class="atlas-muted" style="font-size: 11px; text-transform: uppercase;">Satuan SKP & Unit</span>
            <strong style="display: block; font-size: 15px; color: #0E2A47; margin-top: 2px;">
                {{ $skp->skp_name }} @if($skp->unit_code) (Unit {{ $skp->unit_code }}) @endif
            </strong>
        </div>
        <div>
            <span class="atlas-muted" style="font-size: 11px; text-transform: uppercase;">Kabupaten</span>
            <strong style="display: block; font-size: 15px; color: #0E2A47; margin-top: 2px;">{{ $skp->regency?->name ?? '-' }}</strong>
        </div>
        <div>
            <span class="atlas-muted" style="font-size: 11px; text-transform: uppercase;">Sumber Data Wilayah</span>
            <strong style="display: block; font-size: 15px; color: #0E2A47; margin-top: 2px;">{{ $skp->sumber_data ?: '-' }}</strong>
        </div>
        @if($skp->keterangan)
            <div style="grid-column: 1 / -1; padding-top: 8px; border-top: 1px solid var(--atlas-line);">
                <span class="atlas-muted" style="font-size: 11px; text-transform: uppercase;">Catatan Cakupan</span>
                <p style="margin: 4px 0 0; font-size: 14px; color: #243746;">{{ $skp->keterangan }}</p>
            </div>
        @endif
    </div>
</section>

{{-- Panel Tabel Rincian Desa (Sesuai Format Rujukan Gambar) --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Tabel data kependudukan desa di kawasan {{ $skp->skp_name }}</h2>
            <span class="atlas-muted">Data luas wilayah, penduduk laki-laki & perempuan, serta kepala keluarga</span>
        </div>
        <a class="atlas-button atlas-button--sm atlas-button--primary" href="{{ route('admin.skp.edit', $skp->id) }}" style="color: #ffffff !important;">
            <x-admin.icon name="plus" /> Kelola baris desa
        </a>
    </div>

    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Nama Kec/Desa/Kel</th>
                    <th scope="col" class="tabular-nums" style="text-align: right;">Luas (Km²)</th>
                    <th scope="col" class="tabular-nums" style="text-align: right;">Penduduk L</th>
                    <th scope="col" class="tabular-nums" style="text-align: right;">Penduduk P</th>
                    <th scope="col" class="tabular-nums" style="text-align: right;">Jumlah (L+P)</th>
                    <th scope="col" class="tabular-nums" style="text-align: right;">KK</th>
                    <th scope="col">Kaitan UPT</th>
                    <th scope="col" style="text-align: center; width: 70px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skp->villages as $v)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $v->village_name }}</strong>
                                <span class="atlas-muted" style="display: block; font-size: 12px;">Kec. {{ $v->district_name }}</span>
                            </div>
                        </td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($v->area_km2, 2, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($v->population_male, 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($v->population_female, 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right; font-weight: 700; color: #0E2A47;">{{ number_format($v->population_total, 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right; font-weight: 600; color: #107C41;">{{ number_format($v->family_count, 0, ',', '.') }}</td>
                        <td>
                            @if($v->uptLocation)
                                <a href="{{ route('admin.upt.show', $v->uptLocation->id) }}" class="atlas-link" style="font-size: 12px;">
                                    {{ $v->uptLocation->upt_name }} ↗
                                </a>
                            @else
                                <span class="atlas-muted" style="font-size: 12px;">Non-UPT</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center;">
                                <button type="button" class="atlas-action-icon atlas-action-icon--detail" onclick="openVillageModal({{ $v->id }})" title="Lihat Profil Desa" aria-label="Profil Desa">
                                    <x-admin.icon name="eye" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="atlas-empty">
                                <strong>Belum ada desa yang terdaftar di kawasan {{ $skp->skp_name }}.</strong>
                                <p>Klik tombol di bawah untuk menambahkan data desa.</p>
                                <a class="atlas-link" href="{{ route('admin.skp.edit', $skp->id) }}">+ Tambah Data Desa Sekarang →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($skp->villages->isNotEmpty())
                <tfoot>
                    <tr style="background: #F8FAFC; font-weight: 700;">
                        <td>TOTAL KAWASAN ({{ $skp->villages->count() }} Desa)</td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($skp->villages->sum('area_km2'), 2, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($skp->villages->sum('population_male'), 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right;">{{ number_format($skp->villages->sum('population_female'), 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right; color: #0E2A47;">{{ number_format($skp->total_population, 0, ',', '.') }}</td>
                        <td class="tabular-nums" style="text-align: right; color: #107C41;">{{ number_format($skp->total_family_count, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</section>

{{-- Pratinjau WebGIS --}}
<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Visualisasi spasial batas SKP</h2>
        <span class="atlas-muted">Delineasi kawasan transmigrasi pada peta publik WebGIS</span>
    </div>
    <div class="atlas-panel-body">
        @if(!empty($skp->polygon_geojson))
            <p style="margin: 0 0 12px; font-size: 14px; color: #243746;">
                Poligon batas kawasan telah aktif di sistem dengan warna aksen
                <span style="display: inline-block; width: 14px; height: 14px; background: {{ $skp->color ?: '#163A63' }}; vertical-align: middle; border-radius: 3px;"></span>
                <code>{{ $skp->color ?: '#163A63' }}</code>.
            </p>
            <a href="{{ route('peta') }}?regency={{ $skp->regency_id }}&skp_id={{ $skp->id }}" target="_blank" rel="noopener" class="atlas-button atlas-button--primary">
                <x-admin.icon name="external" /> Buka peta WebGIS publik di lokasi ini ↗
            </a>
        @else
            <div class="atlas-empty" style="padding: 24px;">
                <p>Belum ada berkas batas poligon GeoJSON untuk kawasan ini.</p>
                <a href="{{ route('admin.skp.edit', $skp->id) }}" class="atlas-link">Unggah berkas GeoJSON di menu Ubah →</a>
            </div>
        @endif
    </div>
</section>

{{-- Modal Detail Profil Desa --}}
@include('admin.kawasan_skp.partials.modal-village-detail')
@endsection
