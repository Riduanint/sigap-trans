@extends('layouts.admin')

@section('atlas_title', 'Draf usulan #' . $changeRequest->id)
@section('atlas_subtitle', 'Pelacakan status verifikasi untuk ' . ($changeRequest->uptLocation->upt_name ?? 'UPT tidak ditemukan') . '.')

@section('content')
@php $upt = $changeRequest->uptLocation; @endphp
<section class="atlas-panel atlas-detail-intro">
    <div class="atlas-identity">
        @if($upt)
            <span class="atlas-code">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->regency?->name ?? 'Kabupaten belum tercatat' }}</span>
            <strong>{{ $upt->upt_name }}</strong>
            <span class="atlas-muted">Kini: {{ $upt->current_village_name ?: 'Desa belum tercatat' }}</span>
        @else
            <strong>UPT tidak ditemukan</strong>
        @endif
    </div>
    <x-admin.status :status="$changeRequest->status" />
</section>

{{-- Keputusan verifikasi --}}
@if($changeRequest->status === 'pending')
    <div class="atlas-alert" role="status">
        <strong>Menunggu verifikasi Super Admin Provinsi.</strong>
        Draf usulan terdaftar di antrean verifikasi sejak {{ $changeRequest->created_at->format('d M Y, H:i') }} WITA.
    </div>
@elseif($changeRequest->status === 'approved')
    <div class="atlas-alert atlas-alert--success" role="status">
        <strong>Disetujui dan diterapkan ke master data.</strong>
        Diverifikasi oleh {{ $changeRequest->reviewer->name ?? 'Super Admin' }} pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
        @if($changeRequest->reviewer_note)<br><span class="atlas-muted">Catatan verifikator: {{ $changeRequest->reviewer_note }}</span>@endif
    </div>
@else
    <div class="atlas-alert atlas-alert--error" role="alert">
        <strong>Ditolak oleh verifikator provinsi.</strong>
        Dievaluasi oleh {{ $changeRequest->reviewer->name ?? 'Super Admin' }} pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
        <br><span class="atlas-muted">Catatan evaluasi: {{ $changeRequest->reviewer_note ?? 'Tidak ada catatan khusus.' }}</span>
    </div>
@endif

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Rincian usulan</h2>
        @if(auth()->user()->isSuperAdmin() && $changeRequest->status === 'pending')
            <a class="atlas-link" href="{{ route('admin.verification.show', $changeRequest->id) }}">Buka di ruang verifikasi →</a>
        @endif
    </div>
    <div class="atlas-panel-body atlas-stack">

        <dl class="atlas-definition">
            <div><dt>Operator pengaju</dt><dd>{{ $changeRequest->user->name ?? '-' }}</dd></div>
            <div><dt>Kategori usulan</dt><dd>{{ match ($changeRequest->request_type) {
                'REGISTRY_SYNC' => 'Buku registri warga',
                'DATA_UPDATE' => 'Data lapangan',
                'LEGAL_ISSUE' => 'Masalah lahan',
                default => 'Berkas BAST baru',
            } }}</dd></div>
            <div class="atlas-definition" style="grid-template-columns: 1fr; gap: 0;"><dt>Uraian pengantar operator</dt><dd style="font-weight: 400; margin-top: 6px;">{{ $changeRequest->proposed_payload['submission_note'] ?? 'Tidak ada catatan pengantar.' }}</dd></div>
        </dl>

        @if($changeRequest->request_type === 'REGISTRY_SYNC' && isset($changeRequest->proposed_payload['registry_summary']))
            @php $regSummary = $changeRequest->proposed_payload['registry_summary']; @endphp
            <div>
                <p class="atlas-form-section-title" style="margin-bottom: 12px;">Parameter buku registri yang diajukan</p>
                <div class="atlas-metrics" style="margin-bottom: 0;">
                    <div class="atlas-metric"><div><strong>{{ $regSummary['recorded_kk'] ?? 0 }}<span>/ {{ $regSummary['current_master_kk'] ?? 0 }}</span></strong><small>KK registri / master</small></div></div>
                    <div class="atlas-metric"><div><strong>{{ $regSummary['recorded_population'] ?? 0 }}<span>/ {{ $regSummary['current_master_population'] ?? 0 }}</span></strong><small>Jiwa registri / master</small></div></div>
                    <div class="atlas-metric"><div><strong>{{ $regSummary['tpa_count'] ?? 0 }}<span>/ {{ $regSummary['tps_count'] ?? 0 }}</span></strong><small>TPA / TPS</small></div></div>
                    <div class="atlas-metric"><div><strong>{{ $regSummary['shm_count'] ?? 0 }}</strong><small>KK bersertipikat SHM</small></div></div>
                </div>
                @if(isset($regSummary['stage_label']))<p class="atlas-muted" style="margin-top: 10px;">Tahapan data: {{ $regSummary['stage_label'] }}</p>@endif
            </div>
        @else
            <div>
                <p class="atlas-form-section-title" style="margin-bottom: 12px;">Perbandingan nilai usulan dengan data saat ini</p>
                <div class="atlas-table-scroll">
                    <table class="atlas-table">
                        <thead>
                            <tr>
                                <th scope="col">Parameter data</th>
                                <th scope="col">Nilai saat ini</th>
                                <th scope="col">Nilai diusulkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $payload = $changeRequest->proposed_payload;
                                $fields = [
                                    'current_village_name' => 'Nama desa definitif',
                                    'business_pattern' => 'Pola usaha budidaya',
                                    'placement_year' => 'Tahun penempatan',
                                    'placement_kk' => 'KK penempatan',
                                    'handover_year' => 'Tahun serah terima',
                                    'handover_kk' => 'KK serah terima',
                                    'issue_status' => 'Status permasalahan lahan',
                                    'issue_note' => 'Deskripsi masalah lapangan',
                                    'shm_status' => 'Status sertifikasi SHM',
                                ];
                            @endphp
                            @foreach($fields as $key => $label)
                                @php
                                    $curVal = $upt?->$key ?? '-';
                                    $newVal = $payload[$key] ?? '-';
                                    $isChanged = ($curVal != $newVal);
                                @endphp
                                <tr @if($isChanged) class="atlas-diff-changed" @endif>
                                    <td style="font-weight: 600;">{{ $label }}</td>
                                    <td class="atlas-muted">{{ $curVal }}</td>
                                    <td>
                                        {{ $newVal }}
                                        @if($isChanged)<span class="atlas-status atlas-status--warning" style="margin-left: 8px;"><span aria-hidden="true"></span>Revisi</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(isset($changeRequest->proposed_payload['attached_document']))
            @php $doc = $changeRequest->proposed_payload['attached_document']; @endphp
            <div class="atlas-panel" style="box-shadow: none;">
                <div class="atlas-panel-body atlas-inline" style="justify-content: space-between;">
                    <div class="atlas-identity">
                        <strong>{{ $doc['document_name'] ?? 'Berkas lampiran' }}</strong>
                        <span class="atlas-code">No. {{ $doc['document_number'] ?? '-' }}</span>
                    </div>
                    <a class="atlas-button" href="{{ Storage::url($doc['file_path']) }}" target="_blank" rel="noopener"><x-admin.icon name="download" /> Buka dokumen</a>
                </div>
            </div>
        @endif

        <div class="atlas-inline">
            <a class="atlas-button" href="{{ route('operator.requests.index') }}">Kembali ke daftar usulan</a>
        </div>
    </div>
</section>
@endsection
