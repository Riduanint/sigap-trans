@extends('layouts.admin')

@section('atlas_subtitle', 'Pantau status verifikasi dan catatan evaluasi dari Super Admin Provinsi. Data diterapkan ke master setelah disetujui.')
@section('atlas_actions')
    <a class="atlas-button" href="{{ route('operator.requests.create-new-upt') }}"><x-admin.icon name="plus" /> Usulkan UPT baru</a>
    <a class="atlas-button atlas-button--primary" href="{{ route('operator.requests.create') }}"><x-admin.icon name="check" /> Ajukan pemutakhiran</a>
@endsection

@section('content')
@php
    $statusTabs = [
        'all' => 'Semua usulan',
        'pending' => 'Diproses',
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
                    <th scope="col" style="text-align: center; width: 110px;">AKSI</th>
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
                            @elseif($req->request_type === 'CREATE_UPT')
                                <div class="atlas-identity">
                                    <span class="atlas-code" style="color: #107C41; font-weight: 700;">+ USULAN UPT BARU · {{ $req->proposed_payload['regency_name'] ?? 'Wilayah' }}</span>
                                    <strong style="color: #107C41;">{{ $req->proposed_payload['upt_name'] ?? 'UPT Baru' }}</strong>
                                    <span class="atlas-muted">Desa: {{ $req->proposed_payload['current_village_name'] ?? 'Belum ditentukan' }}</span>
                                </div>
                            @else
                                <span class="atlas-muted">UPT tidak ditemukan</span>
                            @endif
                        </td>
                        <td>{{ match ($req->request_type) {
                            'CREATE_UPT' => 'Registrasi UPT baru',
                            'REGISTRY_SYNC' => 'Buku registri warga',
                            'DATA_UPDATE' => 'Data lapangan',
                            'LEGAL_ISSUE' => 'Masalah lahan',
                            default => 'Berkas BAST baru',
                        } }}</td>
                        <td class="atlas-reading" style="font-size: 13px;">{{ \Illuminate\Support\Str::limit($req->proposed_payload['submission_note'] ?? '-', 90) }}</td>
                        <td><x-admin.status :status="$req->status" /></td>
                        <td class="atlas-reading" style="font-size: 13px;">{{ $req->reviewer_note ? \Illuminate\Support\Str::limit($req->reviewer_note, 80) : '—' }}</td>
                        <td class="atlas-muted" style="white-space: nowrap;">{{ $req->created_at->format('d M Y') }}<br>{{ $req->created_at->format('H:i') }} WITA</td>
                        <td style="text-align: center;">
                            <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                                <a href="{{ route('operator.requests.show', $req->id) }}" 
                                   class="atlas-action-icon atlas-action-icon--detail" 
                                   title="Lihat Detail Usulan" 
                                   aria-label="Lihat Detail">
                                    <x-admin.icon name="eye" />
                                </a>

                                <a href="{{ route('operator.requests.edit', $req->id) }}" 
                                   class="atlas-action-icon atlas-action-icon--edit" 
                                   title="Edit Usulan" 
                                   aria-label="Edit Usulan">
                                    <x-admin.icon name="edit" />
                                </a>

                                <form action="{{ route('operator.requests.destroy', $req->id) }}" method="POST" class="inline"
                                      data-confirm="Hapus usulan ini secara permanen?"
                                      data-confirm-title="Konfirmasi Hapus Usulan"
                                      data-confirm-type="danger"
                                      data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="atlas-action-icon atlas-action-icon--delete" 
                                            title="Hapus Usulan" 
                                            aria-label="Hapus Usulan">
                                        <x-admin.icon name="trash" />
                                    </button>
                                </form>
                            </div>
                        </td>
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
