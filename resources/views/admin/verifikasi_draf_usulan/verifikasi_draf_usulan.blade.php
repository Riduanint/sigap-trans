@extends('layouts.admin')
@section('atlas_subtitle', 'Tinjau usulan perubahan dari operator kabupaten sebelum diterapkan ke data UPT.')
@section('content')
<nav class="atlas-tabs" aria-label="Status pengajuan">
    @foreach(['pending' => 'Diproses', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua pengajuan'] as $key => $label)
        <a href="{{ route('admin.verification.index', array_merge(request()->only('search', 'regency_id'), ['status' => $key])) }}" @if(request('status', 'all') === $key) aria-current="page" @endif>{{ $label }} <span class="atlas-count">{{ $counts[$key] }}</span></a>
    @endforeach
</nav>
<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.verification.index') }}" class="atlas-filter">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        <div class="atlas-field atlas-field--search"><label for="verification-search">Cari UPT atau desa</label><input type="search" name="search" id="verification-search" value="{{ request('search') }}" placeholder="Nama UPT atau desa saat ini"></div>
        <div class="atlas-field"><label for="verification-regency">Kabupaten</label><select name="regency_id" id="verification-regency"><option value="">Semua kabupaten</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
        @if(request()->anyFilled(['search', 'regency_id']))<a class="atlas-button" href="{{ route('admin.verification.index', ['status' => request('status', 'all')]) }}">Hapus filter</a>@endif
    </form>
    <div class="atlas-panel-heading"><h2>{{ $requests->total() }} pengajuan</h2><span class="atlas-muted">{{ request('status') === 'pending' ? 'Paling lama menunggu lebih dulu' : 'Pengajuan terbaru lebih dulu' }}</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Lokasi UPT</th><th scope="col">Pengajuan</th><th scope="col">Pengaju</th><th scope="col">Status</th><th scope="col" style="text-align: center; width: 80px;">AKSI</th></tr></thead><tbody>
        @forelse($requests as $proposal)
            <tr>
                <td>
                    @if($proposal->uptLocation)
                        <x-admin.upt-identity :upt="$proposal->uptLocation" />
                    @elseif($proposal->request_type === 'CREATE_UPT')
                        @php
                            $pPayload = $proposal->proposed_payload ?? [];
                            $regName = \App\Models\Regency::find($pPayload['regency_id'] ?? null)?->name ?? 'Kabupaten belum dipilih';
                        @endphp
                        <div class="atlas-identity">
                            <span class="atlas-code"><span class="atlas-status atlas-status--info" style="font-size: 0.68rem; padding: 1px 6px;">+ USULAN UPT BARU</span> · {{ $regName }}</span>
                            <strong>{{ $pPayload['upt_name'] ?? 'Pendaftaran UPT Baru' }}</strong>
                            <span class="atlas-muted">Rencana Desa: {{ $pPayload['current_village_name'] ?? '-' }}</span>
                        </div>
                    @else
                        <span class="atlas-muted">UPT tidak ditemukan</span>
                    @endif
                </td>
                <td>
                    <strong>{{ match($proposal->request_type) { 'CREATE_UPT' => 'Pendaftaran UPT baru', 'REGISTRY_SYNC' => 'Registri warga', 'DATA_UPDATE' => 'Perubahan data', 'LEGAL_ISSUE' => 'Permasalahan lahan', default => 'Dokumen pendukung' } }}</strong>
                    <p class="atlas-muted">{{ $proposal->created_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA</p>
                </td>
                <td>{{ $proposal->user?->name ?? 'Pengguna tidak tersedia' }}</td>
                <td><x-admin.status :status="$proposal->status" /></td>
                <td style="text-align: center;">
                    <div class="atlas-actions-cell" style="justify-content: center;">
                        <a href="{{ route('admin.verification.show', $proposal->id) }}" 
                           class="atlas-action-icon {{ $proposal->status === 'pending' ? 'atlas-action-icon--verify' : 'atlas-action-icon--detail' }}" 
                           title="{{ $proposal->status === 'pending' ? 'Periksa Usulan' : 'Lihat Keputusan' }}" 
                           aria-label="{{ $proposal->status === 'pending' ? 'Periksa' : 'Lihat Keputusan' }}">
                            <x-admin.icon name="{{ $proposal->status === 'pending' ? 'check' : 'eye' }}" />
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="atlas-empty"><strong>Tidak ada pengajuan yang sesuai.</strong><p>Pilih status lain atau hapus filter pencarian.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} dari {{ $requests->total() }} pengajuan</span>{{ $requests->links('admin.partials.pagination') }}</div>
</section>
@endsection
