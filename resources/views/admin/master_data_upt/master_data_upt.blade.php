@extends('layouts.admin')
@section('atlas_subtitle', 'Temukan lokasi melalui nama UPT lama, desa saat ini, atau nomor registrasi.')
@section('atlas_actions')<a class="atlas-button atlas-button--primary" href="{{ route('admin.upt.create') }}"><x-admin.icon name="plus" /> Tambah UPT</a>@endsection
@section('content')
<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.upt.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="upt-search">Cari lokasi</label><input id="upt-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau UPT-001" type="search"></div>
            <div class="atlas-field"><label for="upt-pattern">Pola usaha</label><select id="upt-pattern" name="pattern"><option value="all">Semua pola</option>@foreach($patterns as $pattern)<option value="{{ $pattern }}" @selected(request('pattern') === $pattern)>{{ $pattern }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="upt-regency">Kabupaten</label><select id="upt-regency" name="regency_id"><option value="all">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="upt-status">Permasalahan lahan</label><select id="upt-status" name="status"><option value="all">Semua status</option>@foreach(['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
        </div>
    </form>
    @php
        $activeFilters = collect(request()->only('search', 'regency_id', 'status', 'pattern'))->filter(fn($value) => filled($value) && $value !== 'all');
        $statusLabels = ['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'];
    @endphp
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.upt.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : ($key === 'status' ? ($statusLabels[$value] ?? $value) : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.upt.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($uptLocations->total(), 0, ',', '.') }} lokasi ditemukan</h2><span class="atlas-muted">{{ $stats['total'] }} UPT dalam registrasi provinsi</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Identitas UPT</th><th scope="col">Pola usaha</th><th scope="col">Permasalahan</th><th scope="col">Sertifikasi SHM</th><th scope="col">Dokumen</th><th scope="col" style="text-align: center; width: 100px;">AKSI</th></tr></thead><tbody>
        @forelse($uptLocations as $upt)
            <tr>
                <td><x-admin.upt-identity :upt="$upt" /></td>
                <td>{{ $upt->business_pattern }}</td>
                <td><x-admin.status :status="$upt->issue_status" /></td>
                <td>{{ $upt->shm_status ?: 'Belum tercatat' }}</td>
                <td><a class="atlas-link" href="{{ route('admin.upt.show', ['id' => $upt->id, 'tab' => 'documents']) }}">{{ $upt->documents->count() }} berkas</a></td>
                <td style="text-align: center;">
                    <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                        <a href="{{ route('admin.upt.edit', $upt->id) }}" class="atlas-action-icon atlas-action-icon--edit" title="Perbarui Data UPT" aria-label="Perbarui">
                            <x-admin.icon name="edit" />
                        </a>
                        @if(auth()->user()->role === 'super_admin')
                            <form action="{{ route('admin.upt.destroy', $upt->id) }}" method="POST" class="inline" data-confirm="Hapus data UPT No. {{ $upt->upt_number }} ({{ $upt->upt_name }})? Seluruh data warga, berkas, dan usulan terkait akan ikut terhapus permanen." data-confirm-title="Konfirmasi Hapus UPT" data-confirm-type="danger" data-confirm-button="Ya, Hapus UPT">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="atlas-action-icon atlas-action-icon--delete" title="Hapus UPT" aria-label="Hapus">
                                    <x-admin.icon name="trash" />
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="atlas-empty"><strong>Lokasi tidak ditemukan.</strong><p>Coba nama desa lain atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.upt.index') }}">Tampilkan seluruh UPT →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $uptLocations->firstItem() ?? 0 }}–{{ $uptLocations->lastItem() ?? 0 }} dari {{ $uptLocations->total() }} lokasi</span>{{ $uptLocations->links('admin.partials.pagination') }}</div>
</section>
@endsection
