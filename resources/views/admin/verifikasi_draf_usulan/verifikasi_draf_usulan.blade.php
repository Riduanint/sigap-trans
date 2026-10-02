@extends('layouts.admin')
@section('atlas_subtitle', 'Tinjau usulan perubahan dari operator kabupaten sebelum diterapkan ke data UPT.')
@section('content')
<nav class="atlas-tabs" aria-label="Status pengajuan">
    @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua pengajuan'] as $key => $label)
        <a href="{{ route('admin.verification.index', array_merge(request()->only('search', 'regency_id'), ['status' => $key])) }}" @if(request('status', 'all') === $key) aria-current="page" @endif>{{ $label }} <span class="atlas-count">{{ $counts[$key] }}</span></a>
    @endforeach
</nav>
<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.verification.index') }}" class="atlas-filter">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        <div class="atlas-field atlas-field--search"><label for="verification-search">Cari UPT atau desa</label><input type="search" name="search" id="verification-search" value="{{ request('search') }}" placeholder="Nama UPT atau desa saat ini"></div>
        <div class="atlas-field"><label for="verification-regency">Kabupaten</label><select name="regency_id" id="verification-regency"><option value="">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
        <button class="atlas-button atlas-button--primary">Terapkan</button>
        @if(request()->anyFilled(['search', 'regency_id']))<a class="atlas-button" href="{{ route('admin.verification.index', ['status' => request('status', 'all')]) }}">Hapus filter</a>@endif
    </form>
    <div class="atlas-panel-heading"><h2>{{ $requests->total() }} pengajuan</h2><span class="atlas-muted">{{ request('status') === 'pending' ? 'Paling lama menunggu lebih dulu' : 'Pengajuan terbaru lebih dulu' }}</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Lokasi UPT</th><th scope="col">Pengajuan</th><th scope="col">Pengaju</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($requests as $proposal)
            <tr><td><x-admin.upt-identity :upt="$proposal->uptLocation" /></td><td><strong>{{ match($proposal->request_type) { 'REGISTRY_SYNC' => 'Registri warga', 'DATA_UPDATE' => 'Perubahan data', 'LEGAL_ISSUE' => 'Permasalahan lahan', default => 'Dokumen pendukung' } }}</strong><p class="atlas-muted">{{ $proposal->created_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA</p></td><td>{{ $proposal->user?->name ?? 'Pengguna tidak tersedia' }}</td><td><x-admin.status :status="$proposal->status" /></td><td><a class="atlas-button {{ $proposal->status === 'pending' ? 'atlas-button--primary' : '' }}" href="{{ route('admin.verification.show', $proposal->id) }}">{{ $proposal->status === 'pending' ? 'Periksa perubahan' : 'Lihat keputusan' }}</a></td></tr>
        @empty
            <tr><td colspan="5"><div class="atlas-empty"><strong>Tidak ada pengajuan yang sesuai.</strong><p>Pilih status lain atau hapus filter pencarian.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} dari {{ $requests->total() }} pengajuan</span>{{ $requests->links('admin.partials.pagination') }}</div>
</section>
@endsection
