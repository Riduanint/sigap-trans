@extends('layouts.admin')

@section('atlas_title', $selectedRegency ? 'Kabupaten ' . $selectedRegency->name : 'Seluruh wilayah Kalsel')
@section('atlas_subtitle', 'Operator ' . ($user->name ?? '-') . ' · NIP ' . ($user->nip ?? '-') . ' · Pemutakhiran data lapangan & pengajuan berkas BAST')
@section('atlas_actions')
    <a class="atlas-button atlas-button--primary" href="{{ route('operator.requests.create') }}"><x-admin.icon name="plus" /> Ajukan draf usulan</a>
@endsection

@section('content')
@php
    $statusLabels = ['clean' => 'Clean & Clear', 'warning' => 'Monitoring', 'critical' => 'Prioritas mediasi'];
    $totalPlacementPop = null;
    $totalHandoverPop = null;
@endphp

{{-- Metrik strip: satu baris ringkas menggantikan 4 kartu MP072 --}}
<div class="atlas-metrics" aria-label="Ringkasan wilayah kerja">
    <div class="atlas-metric"><div><strong>{{ number_format($totalUpt, 0, ',', '.') }}</strong><span>UPT</span><small>{{ $cleanCount }} clean · {{ $warningCount }} monitoring · {{ $criticalCount }} kritis</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($totalPlacementKk, 0, ',', '.') }}</strong><span>KK penempatan</span><small>Tahap arsip lapangan</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($totalHandoverKk, 0, ',', '.') }}</strong><span>KK serah terima</span><small>Desa binaan pemda</small></div></div>
    <div class="atlas-metric"><div><strong>{{ number_format($requestsCount['pending'], 0, ',', '.') }}</strong><span>Draf menunggu verifikasi</span><small><a class="atlas-link" href="{{ route('operator.requests.index', ['status' => 'pending']) }}">{{ $requestsCount['approved'] }} disetujui · {{ $requestsCount['rejected'] }} ditolak →</a></small></div></div>
</div>

<section class="atlas-panel">
    {{-- Toolbar wilayah: pengganti kartu switcher besar --}}
    <form method="GET" action="{{ route('operator.dashboard') }}" class="atlas-filter">
        <div class="atlas-field">
            <label for="op-regency">Cakupan wilayah</label>
            <select id="op-regency" name="regency_id" onchange="this.form.submit()">
                <option value="all" @selected($selectedRegencyId === 'all')>Semua kabupaten Kalsel</option>
                @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}" @selected((string) $selectedRegencyId === (string) $regency->id)>{{ $regency->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="atlas-panel-heading">
        <h2>Daftar UPT {{ $selectedRegency ? 'Kabupaten ' . $selectedRegency->name : 'seluruh wilayah' }}</h2>
        <a class="atlas-link" href="{{ route('operator.upt.index') }}">Buka filter lengkap →</a>
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
                        <td class="tabular-nums">{{ number_format($upt->placement_kk, 0, ',', '.') }} KK</td>
                        <td class="tabular-nums">{{ number_format($upt->handover_kk, 0, ',', '.') }} KK</td>
                        <td><x-admin.status :status="$upt->issue_status" /></td>
                        <td>{{ $upt->shm_status ?: 'Belum tercatat' }}</td>
                        <td>
                            <div class="atlas-inline">
                                <a class="atlas-link" href="{{ route('operator.requests.create', ['upt_id' => $upt->id]) }}">Usulan</a>
                                <a class="atlas-link" href="{{ route('operator.upt.registri', $upt->id) }}">Registri</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">
                                <strong>Belum ada UPT pada cakupan wilayah ini.</strong>
                                <p>Pilih kabupaten lain atau tampilkan seluruh wilayah.</p>
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

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Riwayat draf usulan terbaru</h2>
        <a class="atlas-link" href="{{ route('operator.requests.index') }}">Lihat semua usulan →</a>
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
                @forelse($recentRequests as $req)
                    @php $upt = $req->uptLocation; @endphp
                    <tr>
                        <td>
                            @if($upt)
                                <div class="atlas-identity">
                                    <span class="atlas-code">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->regency?->name ?? '-' }}</span>
                                    <strong>{{ $upt->upt_name }}</strong>
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
                        <td class="atlas-muted" style="white-space: nowrap;">{{ $req->created_at->format('d M Y') }}</td>
                        <td><a class="atlas-link" href="{{ route('operator.requests.show', $req->id) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="atlas-empty">
                                <strong>Belum ada draf usulan.</strong>
                                <p>Mulai dari memilih UPT di tabel daftar, lalu ajukan pemutakhiran datanya.</p>
                                <a class="atlas-link" href="{{ route('operator.requests.create') }}">Ajukan draf usulan →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
