@extends('layouts.admin')

@section('atlas_subtitle', $currentRegency
    ? 'Inventarisasi UPT Binaan Kabupaten ' . $currentRegency->name
    : 'Inventarisasi seluruh UPT Kalimantan Selatan — tinjau data lapangan, catat registri, dan ajukan pemutakhiran.')
@section('atlas_actions')
    <a class="atlas-button atlas-button--primary" href="{{ route('operator.requests.create') }}"><x-admin.icon name="plus" /> Ajukan draf usulan</a>
@endsection

@section('content')
<section class="atlas-panel">
    <form method="GET" action="{{ route('operator.upt.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search">
                <label for="op-upt-search">Cari lokasi</label>
                <input id="op-upt-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau pola usaha" type="search">
            </div>
            <div class="atlas-field">
                <label for="op-upt-regency">Kabupaten</label>
                <select id="op-upt-regency" name="regency_id">
                    <option value="all" @selected($selectedRegencyId === 'all')>Semua kabupaten</option>
                    @foreach($regencies as $regency)
                        <option value="{{ $regency->id }}" @selected((string) $selectedRegencyId === (string) $regency->id)>{{ $regency->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="atlas-field">
                <label for="op-upt-status">Permasalahan lahan</label>
                <select id="op-upt-status" name="status">
                    <option value="all" @selected($selectedStatus === 'all')>Semua status</option>
                    @foreach(['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="atlas-field">
                <label for="op-upt-perpage">Baris per halaman</label>
                <select id="op-upt-perpage" name="per_page">
                    @foreach([10, 25, 50] as $option)
                        <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @php
        $activeFilters = collect(request()->only('search', 'regency_id', 'status'))->filter(fn ($value) => filled($value) && $value !== 'all');
        $statusLabels = ['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'];
    @endphp
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">
            @foreach($activeFilters as $key => $value)
                <a href="{{ route('operator.upt.index', request()->except([$key, 'page', 'per_page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : ($key === 'status' ? ($statusLabels[$value] ?? $value) : $value) }} <span aria-hidden="true">×</span></a>
            @endforeach
            <a href="{{ route('operator.upt.index') }}">Hapus semua filter</a>
        </div>
    @endif
    <div class="atlas-panel-heading">
        <h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2>
        <span class="atlas-muted">{{ number_format($stats['total_upt'], 0, ',', '.') }} UPT · {{ number_format($stats['total_placement_kk'], 0, ',', '.') }} KK penempatan · {{ number_format($stats['total_handover_kk'], 0, ',', '.') }} KK serah terima</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">Identitas UPT</th>
                    <th scope="col">Pola usaha</th>
                    <th scope="col">Penempatan</th>
                    <th scope="col">Serah terima</th>
                    <th scope="col">Permasalahan</th>
                    <th scope="col">Sertifikasi SHM</th>
                    <th scope="col">Registri</th>
                    <th scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uptLocations as $upt)
                    <tr>
                        <td>
                            <div class="atlas-identity">
                                <span class="atlas-code">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->regency?->name ?? 'Kabupaten belum tercatat' }}</span>
                                <strong>{{ $upt->upt_name }}</strong>
                                <span class="atlas-muted">Kini: {{ $upt->current_village_name ?: 'Desa belum tercatat' }}</span>
                            </div>
                        </td>
                        <td>{{ $upt->business_pattern ?: 'Belum tercatat' }}</td>
                        <td class="tabular-nums">{{ number_format($upt->placement_kk, 0, ',', '.') }} KK<br><span class="atlas-muted">{{ $upt->placement_year ?: 'Tahun belum tercatat' }} · {{ number_format($upt->placement_population, 0, ',', '.') }} jiwa</span></td>
                        <td class="tabular-nums">{{ number_format($upt->handover_kk, 0, ',', '.') }} KK<br><span class="atlas-muted">{{ $upt->handover_year ?: 'Tahun belum tercatat' }} · {{ number_format($upt->handover_population, 0, ',', '.') }} jiwa</span></td>
                        <td><x-admin.status :status="$upt->issue_status" /></td>
                        <td>{{ $upt->shm_status ?: 'Belum tercatat' }}</td>
                        <td>
                            @if(($upt->family_cards_count ?? 0) > 0)
                                <a class="atlas-link" href="{{ route('operator.upt.registri', $upt->id) }}">{{ number_format($upt->family_cards_count, 0, ',', '.') }} KK terdata</a>
                            @else
                                <a class="atlas-link" href="{{ route('operator.upt.registri', $upt->id) }}">Belum terdata</a>
                            @endif
                        </td>
                        <td><a class="atlas-link" href="{{ route('operator.requests.create', ['upt_id' => $upt->id]) }}">Ajukan usulan</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="atlas-empty">
                                <strong>Lokasi tidak ditemukan.</strong>
                                <p>Coba nama desa lain atau hapus sebagian filter.</p>
                                <a class="atlas-link" href="{{ route('operator.upt.index') }}">Tampilkan seluruh UPT →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="atlas-table-footer">
        <span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>
        {{ $uptLocations->links('admin.partials.pagination') }}
    </div>
</section>
@endsection
