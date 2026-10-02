@extends('layouts.admin')

@section('atlas_subtitle', 'Pantau status verifikasi dan catatan evaluasi dari Super Admin Provinsi. Data diterapkan ke master setelah disetujui.')
@section('atlas_actions')
    <a class="atlas-button atlas-button--primary" href="{{ route('operator.requests.create') }}"><x-admin.icon name="plus" /> Ajukan draf usulan</a>
@endsection

@section('content')
@php
    $statusTabs = [
        'all' => 'Semua usulan',
        'pending' => 'Menunggu verifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    $activeStatus = request('status', 'all');
@endphp
<nav class="atlas-tabs" aria-label="Status usulan">
    @foreach($statusTabs as $statusKey => $statusLabel)
        <a href="{{ route('operator.requests.index', array_merge(request()->except(['status', 'page']), $statusKey === 'all' ? [] : ['status' => $statusKey])) }}" @if($activeStatus === $statusKey) aria-current="page" @endif>{{ $statusLabel }}</a>
    @endforeach
</nav>

<section class="atlas-panel">
    <form method="GET" action="{{ route('operator.requests.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search">
                <label for="op-req-search">Cari usulan</label>
                <input id="op-req-search" name="search" value="{{ request('search') }}" placeholder="Nama UPT, desa, atau catatan pengantar" type="search">
            </div>
            @if(isset($regencies))
                <div class="atlas-field">
                    <label for="op-req-regency">Kabupaten</label>
                    <select id="op-req-regency" name="regency_id">
                        <option value="all" @selected(! request()->filled('regency_id'))>Semua kabupaten</option>
                        @foreach($regencies as $regency)
                            <option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($activeStatus !== 'all')<input type="hidden" name="status" value="{{ $activeStatus }}">@endif
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    <div class="atlas-panel-heading">
        <h2>{{ number_format($requests->total(), 0, ',', '.') }} usulan</h2>
        <span class="atlas-muted">Pembaruan otomatis disinkronkan ke master data setelah diverifikasi.</span>
    </div>
    <div class="atlas-table-scroll">
        <table class="atlas-table">
            <thead>
                <tr>
                    <th scope="col">UPT diajukan</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Ringkasan perubahan</th>
                    <th scope="col">Status</th>
                    <th scope="col">Catatan verifikator</th>
                    <th scope="col">Diajukan</th>
                    <th scope="col"><span class="sr-only">Tindakan</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php $upt = $req->uptLocation; @endphp
                    <tr>
                        <td>
                            @if($upt)
                                <div class="atlas-identity">
                                    <span class="atlas-code">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->regency?->name ?? '-' }}</span>
                                    <strong>{{ $upt->upt_name }}</strong>
                                    @if($upt->current_village_name)<span class="atlas-muted">Kini: {{ $upt->current_village_name }}</span>@endif
                                </div>
                            @else
                                <span class="atlas-muted">UPT tidak ditemukan</span>
                            @endif
                        </td>
                        <td>{{ match ($req->request_type) {
                            'REGISTRY_SYNC' => 'Buku registri warga',
                            'DATA_UPDATE' => 'Data lapangan',
                            'LEGAL_ISSUE' => 'Masalah lahan',
                            default => 'Berkas BAST baru',
                        } }}</td>
                        <td class="atlas-reading" style="font-size: 13px;">{{ \Illuminate\Support\Str::limit($req->proposed_payload['submission_note'] ?? '-', 90) }}</td>
                        <td><x-admin.status :status="$req->status" /></td>
                        <td class="atlas-reading" style="font-size: 13px;">{{ $req->reviewer_note ? \Illuminate\Support\Str::limit($req->reviewer_note, 80) : '—' }}</td>
                        <td class="atlas-muted" style="white-space: nowrap;">{{ $req->created_at->format('d M Y') }}<br>{{ $req->created_at->format('H:i') }} WITA</td>
                        <td><a class="atlas-link" href="{{ route('operator.requests.show', $req->id) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">
                                <strong>Tidak ada draf usulan yang sesuai filter.</strong>
                                <p>Ajukan pemutakhiran data dari halaman Data UPT wilayah.</p>
                                <a class="atlas-link" href="{{ route('operator.requests.create') }}">Ajukan draf usulan →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="atlas-table-footer">
        <span>{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} dari {{ $requests->total() }} usulan</span>
        {{ $requests->links('admin.partials.pagination') }}
    </div>
</section>
@endsection
