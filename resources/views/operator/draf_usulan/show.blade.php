@extends('layouts.admin')

@section('title', 'Rincian Draf Usulan #' . $changeRequest->id)
@section('header_title', 'Rincian Draf Usulan #' . $changeRequest->id)
@section('header_subtitle', 'Pelacakan status verifikasi data UPT ' . ($changeRequest->uptLocation->upt_name ?? ''))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Tombol Navigasi Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.requests.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2C3B4D] hover:text-[#1B2632] transition">
            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Riwayat Pengajuan</span>
        </a>

        @if(auth()->user()->isSuperAdmin() && $changeRequest->status === 'pending')
            <a href="{{ route('admin.verification.show', $changeRequest->id) }}" 
               class="px-4 py-2 rounded-xl bg-[#1B2632] text-[#EEE9DF] text-xs font-bold hover:bg-[#2C3B4D] transition shadow-ambient-xs border border-[#1B2632]">
                Buka di Ruang Verifikasi Diff Checker &rarr;
            </a>
        @endif
    </div>

    <!-- Banner Status Pengajuan -->
    @if($changeRequest->status === 'pending')
        <div class="rounded-2xl p-5 bg-[#FFB162]/15 border border-[#FFB162]/50 text-[#1B2632] flex items-start gap-4 shadow-ambient-xs">
            <div class="w-10 h-10 rounded-xl bg-[#FFB162]/30 border border-[#FFB162]/60 flex items-center justify-center text-[#8F4E0A] font-bold shrink-0">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-sm text-[#1B2632]">Sedang Menunggu Verifikasi Super Admin Provinsi</h3>
                <p class="text-xs text-[#2C3B4D]/80 mt-0.5">
                    Draf usulan ini telah terdaftar di antrean verifikasi Provinsi Kalsel sejak {{ $changeRequest->created_at->format('d M Y, H:i') }} WITA.
                </p>
            </div>
        </div>
    @elseif($changeRequest->status === 'approved')
        <div class="rounded-2xl p-5 bg-[#2C3B4D]/10 border border-[#2C3B4D]/30 text-[#1B2632] flex items-start gap-4 shadow-ambient-xs">
            <div class="w-10 h-10 rounded-xl bg-[#2C3B4D] text-[#EEE9DF] flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-sm text-[#1B2632]">Draf Usulan Telah Disetujui & Diterapkan ke Master Data</h3>
                <p class="text-xs text-[#2C3B4D]/80 mt-0.5">
                    Diverifikasi oleh <span class="font-bold text-[#1B2632]">{{ $changeRequest->reviewer->name ?? 'Super Admin' }}</span> pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
                </p>
                @if($changeRequest->reviewer_note)
                    <div class="mt-2.5 p-3 bg-white rounded-xl border border-[#C9C1B1]/60 text-xs text-[#2C3B4D]">
                        <strong class="text-[#1B2632]">Catatan Verifikator:</strong> {{ $changeRequest->reviewer_note }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-2xl p-5 bg-[#A35139]/15 border border-[#A35139]/40 text-[#1B2632] flex items-start gap-4 shadow-ambient-xs">
            <div class="w-10 h-10 rounded-xl bg-[#A35139] text-[#EEE9DF] flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-sm text-[#A35139]">Draf Usulan Ditolak oleh Verifikator Provinsi</h3>
                <p class="text-xs text-[#2C3B4D]/80 mt-0.5">
                    Dievaluasi oleh <span class="font-bold text-[#1B2632]">{{ $changeRequest->reviewer->name ?? 'Super Admin' }}</span> pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
                </p>
                <div class="mt-2.5 p-3 bg-white rounded-xl border border-[#A35139]/30 text-xs text-[#2C3B4D]">
                    <strong class="text-[#A35139]">Catatan Evaluasi Penolakan:</strong> {{ $changeRequest->reviewer_note ?? 'Tidak ada catatan khusus.' }}
                </div>
            </div>
        </div>
    @endif

    <!-- Data Detail Usulan -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="p-5 border-b border-[#C9C1B1]/40 bg-[#EEE9DF]/40 flex items-center justify-between">
            <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider">
                Rincian Usulan Pemutakhiran
            </h3>
            <span class="px-3 py-1 rounded-full text-xs font-black bg-[#1B2632] text-[#EEE9DF] border border-[#2C3B4D]">
                UPT #{{ $changeRequest->uptLocation->upt_number }} - {{ $changeRequest->uptLocation->upt_name }}
            </span>
        </div>

        <div class="p-6 space-y-6">
            <!-- Informasi Operator Pengaju -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-[#EEE9DF]/30 border border-[#C9C1B1]/50 text-xs">
                <div>
                    <span class="text-[#2C3B4D]/60 block font-bold text-[10px] uppercase">Operator Pengaju</span>
                    <span class="font-black text-[#1B2632]">{{ $changeRequest->user->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[#2C3B4D]/60 block font-bold text-[10px] uppercase">Wilayah Kabupaten</span>
                    <span class="font-black text-[#1B2632]">{{ $changeRequest->uptLocation->regency->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[#2C3B4D]/60 block font-bold text-[10px] uppercase">Kategori Usulan</span>
                    @if($changeRequest->request_type === 'REGISTRY_SYNC')
                        <span class="font-bold text-purple-800">Buku Registri Warga</span>
                    @elseif($changeRequest->request_type === 'DATA_UPDATE')
                        <span class="font-bold text-[#2C3B4D]">Data Lapangan</span>
                    @elseif($changeRequest->request_type === 'LEGAL_ISSUE')
                        <span class="font-bold text-[#A35139]">Masalah Lahan</span>
                    @else
                        <span class="font-bold text-[#8F4E0A]">Berkas BAST Baru</span>
                    @endif
                </div>
            </div>

            <!-- Uraian Alasan Pengajuan -->
            <div>
                <h4 class="text-xs font-black text-[#1B2632] uppercase tracking-wider mb-2">
                    Uraian Catatan Pengantar Operator:
                </h4>
                <div class="p-4 rounded-xl bg-[#EEE9DF]/20 border border-[#C9C1B1]/50 text-xs text-[#2C3B4D] leading-relaxed">
                    {{ $changeRequest->proposed_payload['submission_note'] ?? 'Tidak ada catatan pengantar.' }}
                </div>
            </div>

            @if($changeRequest->request_type === 'REGISTRY_SYNC' && isset($changeRequest->proposed_payload['registry_summary']))
                @php $regSummary = $changeRequest->proposed_payload['registry_summary']; @endphp
                <!-- Ringkasan Buku Registri -->
                <div>
                    <h4 class="text-xs font-black text-[#1B2632] uppercase tracking-wider mb-2">
                        Parameter Buku Registri yang Diajukan:
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-purple-50/50 border border-purple-200">
                        <div class="p-3 rounded-lg bg-white border border-purple-100">
                            <span class="text-[10px] text-slate-500 font-bold uppercase block">Tahapan Data</span>
                            <span class="text-xs font-black text-purple-950">{{ $regSummary['stage_label'] ?? '-' }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-white border border-purple-100">
                            <span class="text-[10px] text-slate-500 font-bold uppercase block">Total KK Registri</span>
                            <span class="text-base font-black text-purple-950">{{ $regSummary['recorded_kk'] ?? 0 }} KK</span>
                            <span class="text-[10px] text-slate-400 block">Master: {{ $regSummary['current_master_kk'] ?? 0 }} KK</span>
                        </div>
                        <div class="p-3 rounded-lg bg-white border border-purple-100">
                            <span class="text-[10px] text-slate-500 font-bold uppercase block">Total Jiwa Registri</span>
                            <span class="text-base font-black text-purple-950">{{ $regSummary['recorded_population'] ?? 0 }} Jiwa</span>
                            <span class="text-[10px] text-slate-400 block">Master: {{ $regSummary['current_master_population'] ?? 0 }} Jiwa</span>
                        </div>
                        <div class="p-3 rounded-lg bg-white border border-purple-100">
                            <span class="text-[10px] text-slate-500 font-bold uppercase block">Rincian Warga</span>
                            <span class="text-xs font-bold text-slate-700 block">TPA: {{ $regSummary['tpa_count'] ?? 0 }} | TPS: {{ $regSummary['tps_count'] ?? 0 }}</span>
                            <span class="text-[10px] text-emerald-700 font-bold block">{{ $regSummary['shm_count'] ?? 0 }} Bersertipikat SHM</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Payload Snapshot Tabel Standar -->
                <div>
                    <h4 class="text-xs font-black text-[#1B2632] uppercase tracking-wider mb-2">
                        Parameter Nilai yang Diusulkan:
                    </h4>
                    <div class="overflow-x-auto rounded-xl border border-[#C9C1B1]/70">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#1B2632] text-[#EEE9DF] text-[11px] uppercase font-bold tracking-wider border-b border-[#2C3B4D]">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Parameter Data</th>
                                    <th class="px-4 py-3 font-semibold">Data di Basis Data Saat Ini</th>
                                    <th class="px-4 py-3 font-semibold">Nilai yang Diusulkan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C9C1B1]/30">
                                @php
                                    $upt = $changeRequest->uptLocation;
                                    $payload = $changeRequest->proposed_payload;
                                    $fields = [
                                        'current_village_name' => 'Nama Desa Definitif',
                                        'business_pattern' => 'Pola Usaha Budidaya',
                                        'placement_year' => 'Tahun Penempatan',
                                        'placement_kk' => 'KK Penempatan',
                                        'handover_year' => 'Tahun Serah Terima',
                                        'handover_kk' => 'KK Serah Terima',
                                        'issue_status' => 'Status Permasalahan Lahan',
                                        'issue_note' => 'Deskripsi Masalah Lapangan',
                                        'shm_status' => 'Status Sertifikasi SHM',
                                    ];
                                @endphp

                                @foreach($fields as $key => $label)
                                    @php
                                        $curVal = $upt->$key ?? '-';
                                        $newVal = $payload[$key] ?? '-';
                                        $isChanged = ($curVal != $newVal);
                                    @endphp
                                    <tr class="{{ $isChanged ? 'bg-[#FFB162]/10 font-bold' : 'hover:bg-[#EEE9DF]/40' }} transition">
                                        <td class="px-4 py-3 font-bold text-[#1B2632]">
                                            {{ $label }}
                                        </td>
                                        <td class="px-4 py-3 text-[#2C3B4D]/70">
                                            {{ $curVal }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold {{ $isChanged ? 'text-[#1B2632]' : 'text-[#2C3B4D]' }}">
                                            <span>{{ $newVal }}</span>
                                            @if($isChanged)
                                                <span class="ml-1.5 text-[10px] bg-[#FFB162] text-[#1B2632] px-2 py-0.5 rounded-full font-black border border-[#FFB162]/50">
                                                    Revisi
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Lampiran Berkas Jika Ada -->
            @if(isset($changeRequest->proposed_payload['attached_document']))
                @php $doc = $changeRequest->proposed_payload['attached_document']; @endphp
                <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1B2632] text-[#FFB162] flex items-center justify-center font-black text-xs shadow-2xs">
                            PDF
                        </div>
                        <div>
                            <div class="text-xs font-bold text-[#1B2632]">{{ $doc['document_name'] ?? 'Berkas Lampiran' }}</div>
                            <div class="text-[11px] text-[#2C3B4D]/70 font-mono">No: {{ $doc['document_number'] ?? '-' }}</div>
                        </div>
                    </div>
                    <a href="{{ Storage::url($doc['file_path']) }}" target="_blank" 
                       class="px-3.5 py-1.5 rounded-lg bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] text-xs font-bold transition shadow-2xs border border-[#1B2632]">
                        Buka Dokumen
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
