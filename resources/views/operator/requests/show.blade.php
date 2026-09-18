@extends('layouts.admin')

@section('title', 'Rincian Draf Usulan #' . $changeRequest->id)
@section('header_title', 'Rincian Draf Usulan #' . $changeRequest->id)
@section('header_subtitle', 'Pelacakan status verifikasi data UPT ' . ($changeRequest->uptLocation->upt_name ?? ''))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Tombol Navigasi Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.requests.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-[#0B1849] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Riwayat Pengajuan</span>
        </a>

        @if(auth()->user()->isSuperAdmin() && $changeRequest->status === 'pending')
            <a href="{{ route('admin.verification.show', $changeRequest->id) }}" 
               class="px-4 py-2 rounded-xl bg-[#124D1C] text-white text-xs font-bold hover:bg-emerald-800 transition shadow-xs">
                Buka di Ruang Verifikasi Diff Checker &rarr;
            </a>
        @endif
    </div>

    <!-- Banner Status Pengajuan -->
    @if($changeRequest->status === 'pending')
        <div class="rounded-2xl p-5 bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-800 font-bold shrink-0">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-amber-950">Sedang Menunggu Verifikasi Super Admin Provinsi</h3>
                <p class="text-xs text-amber-800 mt-0.5">
                    Draf usulan ini telah terdaftar di antrean verifikasi Provinsi Kalsel sejak {{ $changeRequest->created_at->format('d M Y, H:i') }} WITA.
                </p>
            </div>
        </div>
    @elseif($changeRequest->status === 'approved')
        <div class="rounded-2xl p-5 bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 border border-emerald-300 flex items-center justify-center text-emerald-800 font-bold shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-emerald-950">Draf Usulan Telah Disetujui & Diterapkan ke Master Data</h3>
                <p class="text-xs text-emerald-800 mt-0.5">
                    Diverifikasi oleh <span class="font-bold">{{ $changeRequest->reviewer->name ?? 'Super Admin' }}</span> pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
                </p>
                @if($changeRequest->reviewer_note)
                    <div class="mt-2 p-3 bg-white/70 rounded-xl border border-emerald-200 text-xs text-emerald-950">
                        <strong>Catatan Verifikator:</strong> {{ $changeRequest->reviewer_note }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-2xl p-5 bg-rose-50 border border-rose-300 text-rose-900 flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-300 flex items-center justify-center text-rose-800 font-bold shrink-0">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-rose-950">Draf Usulan Ditolak oleh Verifikator Provinsi</h3>
                <p class="text-xs text-rose-800 mt-0.5">
                    Dievaluasi oleh <span class="font-bold">{{ $changeRequest->reviewer->name ?? 'Super Admin' }}</span> pada {{ $changeRequest->reviewed_at?->format('d M Y, H:i') }} WITA.
                </p>
                <div class="mt-2 p-3 bg-white rounded-xl border border-rose-200 text-xs text-rose-950">
                    <strong>Catatan Evaluasi Penolakan:</strong> {{ $changeRequest->reviewer_note ?? 'Tidak ada catatan khusus.' }}
                </div>
            </div>
        </div>
    @endif

    <!-- Data Detail Usulan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-[#0B1849] uppercase tracking-wider">
                Rincian Usulan Pemutakhiran
            </h3>
            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-[#0B1849] text-white">
                UPT #{{ $changeRequest->uptLocation->upt_number }} - {{ $changeRequest->uptLocation->upt_name }}
            </span>
        </div>

        <div class="p-6 space-y-6">
            <!-- Informasi Operator Pengaju -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold text-[10px] uppercase">Operator Pengaju</span>
                    <span class="font-bold text-slate-800">{{ $changeRequest->user->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold text-[10px] uppercase">Wilayah Kabupaten</span>
                    <span class="font-bold text-[#0B1849]">{{ $changeRequest->uptLocation->regency->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold text-[10px] uppercase">Kategori Usulan</span>
                    <span class="font-bold text-[#124D1C]">{{ $changeRequest->request_type }}</span>
                </div>
            </div>

            <!-- Uraian Alasan Pengajuan -->
            <div>
                <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                    Uraian Catatan Pengantar Operator:
                </h4>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed">
                    {{ $changeRequest->proposed_payload['submission_note'] ?? 'Tidak ada catatan pengantar.' }}
                </div>
            </div>

            <!-- Payload Snapshot Tabel -->
            <div>
                <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                    Parameter Nilai yang Diusulkan:
                </h4>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#0B1849] text-white text-[10px] uppercase font-bold">
                            <tr>
                                <th class="px-4 py-2.5">Parameter Data</th>
                                <th class="px-4 py-2.5">Data di Basis Data Saat Ini</th>
                                <th class="px-4 py-2.5">Nilai yang Diusulkan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
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
                                <tr class="{{ $isChanged ? 'bg-amber-50/50' : '' }}">
                                    <td class="px-4 py-2.5 font-bold text-slate-700">
                                        {{ $label }}
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-500">
                                        {{ $curVal }}
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold {{ $isChanged ? 'text-[#124D1C] font-bold' : 'text-slate-700' }}">
                                        {{ $newVal }}
                                        @if($isChanged)
                                            <span class="ml-1 text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.2 rounded font-extrabold">Revisi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lampiran Berkas Jika Ada -->
            @if(isset($changeRequest->proposed_payload['attached_document']))
                @php $doc = $changeRequest->proposed_payload['attached_document']; @endphp
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                            PDF
                        </div>
                        <div>
                            <div class="text-xs font-bold text-blue-950">{{ $doc['document_name'] ?? 'Berkas Lampiran' }}</div>
                            <div class="text-[11px] text-blue-700">No: {{ $doc['document_number'] ?? '-' }}</div>
                        </div>
                    </div>
                    <a href="{{ Storage::url($doc['file_path']) }}" target="_blank" 
                       class="px-3 py-1.5 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold transition">
                        Buka Dokumen
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
