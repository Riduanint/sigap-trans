@extends('layouts.admin')
@section('atlas_title', 'Kawasan Transmigrasi & SKP')
@section('atlas_subtitle', 'Kelola Kawasan Transmigrasi, Satuan Kawasan Pengembangan (SKP), profil desa binaan, dan delineasi peta.')
@section('atlas_actions')
    <a class="atlas-button" href="{{ route('admin.skp.pdf') }}" target="_blank">
        <x-admin.icon name="download" /> Ekspor PDF
    </a>
    <a class="atlas-button atlas-button--primary" href="{{ route('admin.skp.create') }}">
        <x-admin.icon name="plus" /> Tambah Kawasan SKP
    </a>
@endsection

@section('content')
{{-- Kartu Ringkasan Metrik --}}
<div class="atlas-metrics" aria-label="Ringkasan Kawasan & SKP">
    <div class="atlas-metric">
        <div>
            <strong>{{ $stats['total_skp'] }}</strong>
            <small>Satuan Kawasan Pengembangan (SKP)</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong>{{ $stats['total_villages'] }}</strong>
            <small>Desa terdata di kawasan binaan</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong class="tabular-nums">{{ number_format($stats['total_area_ha'], 0, ',', '.') }}<span> Ha</span></strong>
            <small>Estimasi total luas kawasan SKP</small>
        </div>
    </div>
    <div class="atlas-metric">
        <div>
            <strong class="tabular-nums">{{ number_format($stats['total_population'], 0, ',', '.') }}<span> Jiwa</span></strong>
            <small>{{ number_format($stats['total_family'], 0, ',', '.') }} Kepala Keluarga (KK)</small>
        </div>
    </div>
</div>

{{-- Panel Tabel Utama --}}
<section class="atlas-panel">
    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.skp.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search">
                <label for="skp-search">Cari kawasan atau desa</label>
                <input id="skp-search" name="search" value="{{ request('search') }}" placeholder="Nama SKP, kawasan, atau desa" type="search">
            </div>
            <div class="atlas-field">
                <label for="skp-regency">Kabupaten</label>
                <select id="skp-regency" name="regency_id">
                    <option value="all">Semua kabupaten</option>
                    @foreach($regencies as $reg)
                        <option value="{{ $reg->id }}" @selected(request('regency_id') == $reg->id)>{{ $reg->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    @php
        $activeFilters = collect(request()->only('search', 'regency_id'))->filter(fn($value) => filled($value) && $value !== 'all');
    @endphp
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">
            @foreach($activeFilters as $key => $value)
                <a href="{{ route('admin.skp.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">
                    {{ $key === 'regency_id' ? ($regencies->firstWhere('id', $value)?->name ?? 'Kabupaten') : $value }} <span aria-hidden="true">×</span>
                </a>
            @endforeach
            <a href="{{ route('admin.skp.index') }}">Hapus semua filter</a>
        </div>
    @endif

    <div class="atlas-panel-heading">
        <h2>{{ number_format($skpAreas->total(), 0, ',', '.') }} kawasan ditemukan</h2>
        <span class="atlas-muted">{{ $stats['total_skp'] }} Satuan Kawasan Pengembangan dalam registrasi provinsi</span>
    </div>

    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col" style="min-width: 220px;">Identitas Kawasan & SKP</th>
                    <th scope="col">Kabupaten</th>
                    <th scope="col" class="tabular-nums">Luas (Ha)</th>
                    <th scope="col" style="min-width: 260px;">Cakupan Desa (Klik untuk Rincian)</th>
                    <th scope="col" class="tabular-nums">Demografi Penduduk</th>
                    <th scope="col">Batas Peta</th>
                    <th scope="col" style="text-align: center; width: 100px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skpAreas as $skp)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <span class="atlas-code">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 2px; background-color: {{ $skp->color ?: '#163A63' }}; vertical-align: middle; margin-right: 4px;" title="Warna batas poligon di WebGIS: {{ $skp->color }}"></span>
                                    {{ $skp->unit_code ? 'UNIT ' . $skp->unit_code . ' · ' : '' }}{{ $skp->regency?->name ?? 'Kabupaten belum tercatat' }}
                                </span>
                                <a href="{{ route('admin.skp.show', $skp->id) }}" style="font-size: 14.5px; font-weight: 700;">{{ $skp->skp_name }}</a>
                                <span class="atlas-muted">{{ $skp->kawasan_name }}</span>
                            </div>
                        </td>
                        <td>{{ $skp->regency?->name ?? 'Belum ditentukan' }}</td>
                        <td class="tabular-nums">
                            @if($skp->luas_ha)
                                <strong>{{ number_format($skp->luas_ha, 2, ',', '.') }}</strong>
                            @else
                                <span class="atlas-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($skp->villages->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                    @foreach($skp->villages as $village)
                                        <button type="button" class="village-chip-btn" onclick="openVillageModal({{ $village->id }})" title="Klik untuk lihat profil kependudukan {{ $village->village_name }}">
                                            <span>{{ $village->village_name }}</span>
                                            <small style="opacity: 0.75;">({{ number_format($village->area_km2, 1, ',', '.') }} Km²)</small>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <span class="atlas-muted" style="font-size: 12px;">Belum ada data desa</span>
                            @endif
                        </td>
                        <td class="tabular-nums">
                            @if($skp->villages->isNotEmpty())
                                <div><strong>{{ number_format($skp->total_population, 0, ',', '.') }}</strong> <small>jiwa</small></div>
                                <span class="atlas-muted" style="font-size: 12px;">{{ number_format($skp->total_family_count, 0, ',', '.') }} KK</span>
                            @else
                                <span class="atlas-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($skp->polygon_geojson))
                                <a href="{{ route('peta') }}?regency={{ $skp->regency_id }}&skp_id={{ $skp->id }}" target="_blank" rel="noopener" class="atlas-link" style="font-size: 12px; color: #107C41; font-weight: 600;" title="Arahkan peta langsung ke poligon {{ $skp->skp_name }}">
                                    ✓ Poligon Aktif ↗
                                </a>
                            @else
                                <span class="atlas-muted" style="font-size: 12px;">Tanpa poligon</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                                <a href="{{ route('admin.skp.edit', $skp->id) }}" class="atlas-action-icon atlas-action-icon--edit" title="Perbarui Kawasan SKP" aria-label="Perbarui">
                                    <x-admin.icon name="edit" />
                                </a>
                                @if(auth()->user()->role === 'super_admin')
                                    <form action="{{ route('admin.skp.destroy', $skp->id) }}" method="POST" class="inline" data-confirm="Hapus kawasan {{ $skp->skp_name }}? Seluruh data desa di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus Kawasan" data-confirm-type="danger" data-confirm-button="Ya, Hapus Kawasan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="atlas-action-icon atlas-action-icon--delete" title="Hapus Kawasan" aria-label="Hapus">
                                            <x-admin.icon name="trash" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">
                                <strong>Kawasan tidak ditemukan.</strong>
                                <p>Coba kata kunci lain atau hapus sebagian filter.</p>
                                <a class="atlas-link" href="{{ route('admin.skp.index') }}">Tampilkan seluruh Kawasan SKP →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="atlas-table-footer">
        <span>{{ $skpAreas->firstItem() ?? 0 }}–{{ $skpAreas->lastItem() ?? 0 }} dari {{ $skpAreas->total() }} kawasan SKP</span>
        {{ $skpAreas->links('admin.partials.pagination') }}
    </div>
</section>

{{-- Modal Detail Profil Desa --}}
@include('admin.kawasan_skp.partials.modal-village-detail')
@endsection
