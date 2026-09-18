@extends('layouts.admin')

@section('title', 'Diff Checker - Verifikasi Draf #' . $changeRequest->id)
@section('header_title', 'Diff Checker & Verifikasi Draf Usulan')
@section('header_subtitle', 'Bandingkan data aktif dengan data usulan baru dari Operator Kabupaten sebelum melakukan approval')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Tombol Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.verification.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-[#0B1849] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Antrean Verifikasi</span>
        </a>

        <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-500">Status Permohonan:</span>
            @if($changeRequest->status === 'pending')
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Verifikasi
                </span>
            @elseif($changeRequest->status === 'approved')
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                    ✓ Telah Disetujui
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-900 border border-rose-300">
                    ✕ Telah Ditolak
                </span>
            @endif
        </div>
    </div>

    <!-- 1. KARTU INFORMASI PENGAJUAN & CATATAN OPERATOR -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-slate-100 pb-5">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Unit UPT Sasaran</span>
                <span class="text-sm font-black text-[#0B1849]">
                    [No. {{ $upt->upt_number }}] {{ $upt->upt_name }}
                </span>
                <span class="block text-xs text-slate-500">{{ $upt->regency->name ?? '-' }} (Wilayah {{ $upt->regency->roman_code ?? 'I' }})</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Operator Pengaju</span>
                <span class="text-sm font-bold text-slate-800">{{ $changeRequest->user->name ?? '-' }}</span>
                <span class="block text-xs text-slate-500 font-mono">{{ $changeRequest->user->nip ?? '-' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Waktu Pengajuan</span>
                <span class="text-sm font-bold text-slate-800">{{ $changeRequest->created_at->format('d F Y') }}</span>
                <span class="block text-xs text-slate-500 font-mono">{{ $changeRequest->created_at->format('H:i:s') }} WITA</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kategori Usulan</span>
                <span class="inline-block mt-0.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#0B1849] text-white">
                    {{ $changeRequest->request_type }}
                </span>
            </div>
        </div>

        <!-- Uraian Alasan Pengajuan -->
        <div class="mt-4">
            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block mb-1">
                Catatan Pengantar & Alasan dari Operator Kabupaten:
            </span>
            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-amber-950 leading-relaxed font-medium">
                {{ $payload['submission_note'] ?? '(Operator tidak menyertakan uraian pengantar)' }}
            </div>
        </div>
    </div>

    <!-- 2. TABEL KOMPARASI SIDE-BY-SIDE (DIFF CHECKER) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 bg-gradient-to-r from-[#0B1849] to-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#E4B028] text-[#0B1849] flex items-center justify-center font-black text-sm">
                    VS
                </div>
                <div>
                    <h3 class="text-sm font-black tracking-tight text-white uppercase">
                        Lembar Komparasi Data (Visual Diff Checker)
                    </h3>
                    <p class="text-[11px] text-slate-300">
                        Perubahan parameter ditandai dengan lencana berwarna hijau pada sisi kanan
                    </p>
                </div>
            </div>
            <div class="text-[11px] font-bold text-[#E4B028] bg-[#E4B028]/20 px-3 py-1 rounded-full border border-[#E4B028]/30">
                Otoritas Verifikasi Provinsi
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200">
                        <th class="px-5 py-3 w-1/4">Parameter Data UPT</th>
                        <th class="px-5 py-3 w-3/8 bg-slate-50 border-r border-slate-200">
                            <span class="text-slate-500">Data Master Saat Ini (Aktif)</span>
                        </th>
                        <th class="px-5 py-3 w-3/8 bg-emerald-50/50">
                            <span class="text-[#124D1C]">Data Usulan Baru (Operator)</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($comparisonFields as $fieldKey => $field)
                        @php
                            $isDifferent = (trim((string)$field['current']) !== trim((string)$field['proposed']));
                        @endphp
                        <tr class="{{ $isDifferent ? 'bg-amber-50/40' : 'hover:bg-slate-50/50' }} transition">
                            <!-- Kolom 1: Nama Field -->
                            <td class="px-5 py-3.5 font-extrabold text-slate-800">
                                {{ $field['label'] }}
                                @if($isDifferent)
                                    <span class="block text-[10px] font-bold text-amber-700 mt-0.5">
                                        ⚡ Terdapat Perubahan
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 2: Data Aktif -->
                            <td class="px-5 py-3.5 bg-slate-50/60 border-r border-slate-200 text-slate-600">
                                @if($fieldKey === 'issue_status')
                                    @if($field['current'] === 'clean')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">🟢 Clean & Clear</span>
                                    @elseif($field['current'] === 'warning')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">🟡 Warning</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">🔴 Prioritas Sengketa</span>
                                    @endif
                                @else
                                    <span class="font-medium">{{ $field['current'] }}</span>
                                @endif
                            </td>

                            <!-- Kolom 3: Data Usulan Baru -->
                            <td class="px-5 py-3.5 {{ $isDifferent ? 'bg-emerald-50/70' : '' }}">
                                @if($fieldKey === 'issue_status')
                                    @if($field['proposed'] === 'clean')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">🟢 Clean & Clear</span>
                                    @elseif($field['proposed'] === 'warning')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">🟡 Warning</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">🔴 Prioritas Sengketa</span>
                                    @endif
                                @else
                                    <span class="{{ $isDifferent ? 'font-black text-[#124D1C]' : 'text-slate-600' }}">
                                        {{ $field['proposed'] }}
                                    </span>
                                @endif

                                @if($isDifferent)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.2 rounded text-[10px] font-black bg-[#124D1C] text-white">
                                        REVISI BARU
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. LAMPIRAN BERKAS BAST (JIKA ADA) -->
    @if(isset($payload['attached_document']))
        @php $doc = $payload['attached_document']; @endphp
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3">
            <h3 class="text-xs font-black text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                <span>Lampiran Berkas Digital BAST yang Diusulkan:</span>
            </h3>

            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 border border-blue-300 text-blue-700 flex items-center justify-center font-bold text-xs">
                        PDF
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-blue-950">{{ $doc['document_name'] ?? 'Berkas Scan BAST' }}</div>
                        <div class="text-[11px] text-blue-700 font-mono">No. Reg: {{ $doc['document_number'] ?? '-' }}</div>
                        <div class="text-[10px] text-slate-400">Ukuran: {{ isset($doc['file_size_bytes']) ? round($doc['file_size_bytes']/1024, 1) . ' KB' : '-' }}</div>
                    </div>
                </div>

                <a href="{{ Storage::url($doc['file_path']) }}" target="_blank" 
                   class="px-4 py-2 rounded-xl bg-[#0B1849] hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-2 self-start sm:self-auto">
                    <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>Buka / Pratinjau Dokumen PDF</span>
                </a>
            </div>
            <p class="text-[11px] text-slate-500 italic">
                * Catatan: Jika draf usulan disetujui, berkas ini akan otomatis didaftarkan ke <strong>Repositori E-Arsip BAST</strong> dinas.
            </p>
        </div>
    @endif

    <!-- 4. PANEL AKSI KEPUTUSAN VERIFIKATOR (APPROVE / REJECT) -->
    @if($changeRequest->status === 'pending')
        <div class="bg-white rounded-2xl p-6 border-2 border-[#124D1C]/40 shadow-lg space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-[#0B1849] uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#124D1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Keputusan Verifikasi Super Admin</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Silakan tentukan persetujuan atau penolakan draf usulan ini. Seluruh aksi dicatat dalam audit log forensik.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- FORM SETUJUI (APPROVE) -->
                <form action="{{ route('admin.verification.approve', $changeRequest->id) }}" method="POST" class="p-5 rounded-xl bg-emerald-50/70 border border-emerald-300 space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-black text-emerald-950 uppercase tracking-wider block">
                            Opsi A: Setujui Usulan (Approve)
                        </span>
                        <p class="text-[11px] text-emerald-800 mt-0.5">
                            Data usulan akan langsung menimpa data master UPT dan dokumen pendukung didaftarkan ke e-arsip.
                        </p>
                    </div>

                    <div>
                        <label for="approve_note" class="block text-xs font-bold text-emerald-900 mb-1">
                            Catatan Persetujuan (Opsional)
                        </label>
                        <input type="text" name="reviewer_note" id="approve_note" 
                               value="Data usulan telah sesuai dengan berita acara dan hasil sinkronisasi."
                               class="w-full text-xs rounded-xl border-emerald-300 focus:border-emerald-600 focus:ring focus:ring-emerald-200">
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menyetujui draf usulan ini? Data master UPT akan otomatis dimutakhirkan.')"
                            class="w-full py-2.5 px-4 rounded-xl bg-[#124D1C] hover:bg-emerald-800 text-white font-black text-xs transition shadow-md flex items-center justify-center gap-2 border border-[#E4B028]">
                        <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Setujui & Terapkan Perubahan (Approve)</span>
                    </button>
                </form>

                <!-- FORM TOLAK (REJECT) -->
                <form action="{{ route('admin.verification.reject', $changeRequest->id) }}" method="POST" class="p-5 rounded-xl bg-rose-50/70 border border-rose-300 space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-black text-rose-950 uppercase tracking-wider block">
                            Opsi B: Tolak Usulan (Reject)
                        </span>
                        <p class="text-[11px] text-rose-800 mt-0.5">
                            Data master tidak akan diubah. Berikan uraian alasan penolakan agar dapat diperbaiki operator.
                        </p>
                    </div>

                    <div>
                        <label for="reject_note" class="block text-xs font-bold text-rose-900 mb-1">
                            Alasan / Catatan Penolakan <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="reviewer_note" id="reject_note" rows="2" required
                                  placeholder="Contoh: Lampiran BAST belum ditandatangani bupati, mohon lengkapi kembali..."
                                  class="w-full text-xs rounded-xl border-rose-300 focus:border-rose-600 focus:ring focus:ring-rose-200"></textarea>
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menolak draf usulan ini?')"
                            class="w-full py-2.5 px-4 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-black text-xs transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak Usulan dengan Catatan (Reject)</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Informasi Riwayat Verifikasi yang Telah Selesai -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                Riwayat Rekam Jejak Keputusan Verifikator:
            </h3>
            <div class="p-4 rounded-xl {{ $changeRequest->status === 'approved' ? 'bg-emerald-50 text-emerald-950 border border-emerald-200' : 'bg-rose-50 text-rose-950 border border-rose-200' }} text-xs space-y-1">
                <div class="font-bold">
                    Keputusan: {{ $changeRequest->status === 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                </div>
                <div>Verifikator: {{ $changeRequest->reviewer->name ?? 'Super Admin' }} ({{ $changeRequest->reviewer->email ?? '' }})</div>
                <div>Waktu Tindakan: {{ $changeRequest->reviewed_at?->format('d F Y, H:i') }} WITA</div>
                <div class="mt-2 pt-2 border-t {{ $changeRequest->status === 'approved' ? 'border-emerald-200' : 'border-rose-200' }}">
                    <strong>Catatan Reviewer:</strong> {{ $changeRequest->reviewer_note ?? '-' }}
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
