@extends('layouts.admin')

@section('title', 'Diff Checker - Verifikasi Draf #' . $changeRequest->id)
@section('header_title', 'Diff Checker & Verifikasi Draf Usulan')
@section('header_subtitle', 'Bandingkan data aktif dengan data usulan baru dari Operator Kabupaten sebelum melakukan approval')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Tombol Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.verification.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2C3B4D] hover:text-[#1B2632] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Antrean Verifikasi</span>
        </a>

        <div class="flex items-center gap-2 text-xs">
            <span class="text-[#2C3B4D]/70">Status Permohonan:</span>
            @if($changeRequest->status === 'pending')
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/50 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-[#8F4E0A] animate-pulse"></span>
                    Menunggu Verifikasi
                </span>
            @elseif($changeRequest->status === 'approved')
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#2C3B4D]/15 text-[#2C3B4D] border border-[#2C3B4D]/30">
                    ✓ Telah Disetujui
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30">
                    ✕ Telah Ditolak
                </span>
            @endif
        </div>
    </div>

    <!-- 1. KARTU INFORMASI PENGAJUAN & CATATAN OPERATOR -->
    <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-[#C9C1B1]/30 pb-5">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Unit UPT Sasaran</span>
                <span class="text-sm font-black text-[#1B2632]">
                    [No. {{ $upt->upt_number }}] {{ $upt->upt_name }}
                </span>
                <span class="block text-xs text-[#2C3B4D]/70">Kabupaten {{ $upt->regency->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Operator Pengaju</span>
                <span class="text-sm font-bold text-[#1B2632]">{{ $changeRequest->user->name ?? '-' }}</span>
                <span class="block text-xs text-[#2C3B4D]/60 font-mono">{{ $changeRequest->user->nip ?? '-' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Waktu Pengajuan</span>
                <span class="text-sm font-bold text-[#1B2632]">{{ $changeRequest->created_at->format('d F Y') }}</span>
                <span class="block text-xs text-[#2C3B4D]/60 font-mono">{{ $changeRequest->created_at->format('H:i:s') }} WITA</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2C3B4D]/60 block">Kategori Usulan</span>
                @if($changeRequest->request_type === 'REGISTRY_SYNC')
                    <span class="inline-block mt-0.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-700 text-white">
                        Buku Registri Warga
                    </span>
                @else
                    <span class="inline-block mt-0.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#1B2632] text-[#EEE9DF]">
                        {{ $changeRequest->request_type }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Uraian Alasan Pengajuan -->
        <div class="mt-4">
            <span class="text-xs font-extrabold text-[#2C3B4D] uppercase tracking-wider block mb-1">
                Catatan Pengantar & Alasan dari Operator:
            </span>
            <div class="p-3.5 rounded-xl bg-[#FFB162]/15 border border-[#FFB162]/40 text-xs text-[#8F4E0A] leading-relaxed font-medium">
                {{ $payload['submission_note'] ?? '(Operator tidak menyertakan uraian pengantar)' }}
            </div>
        </div>
    </div>

    @if($changeRequest->request_type === 'REGISTRY_SYNC' && $registrySummary)
        <!-- 2. KOMPARASI BUKU REGISTRI WARGA (DIFF CHECKER REGISTRI) -->
        <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
            <div class="p-5 bg-[#1B2632] text-[#EEE9DF] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-black text-sm shadow-xs">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black tracking-tight text-[#EEE9DF] uppercase">
                            Komparasi Buku Registri Warga vs Basis Data Master
                        </h3>
                        <p class="text-[11px] text-[#EEE9DF]/70">
                            Pengesahan sinkronisasi angka KK & Jiwa dari data nominal lapangan {{ $registrySummary['stage_label'] ?? '' }}
                        </p>
                    </div>
                </div>
                <div class="text-[11px] font-bold text-emerald-300 bg-emerald-950/60 px-3 py-1 rounded-full border border-emerald-500/40">
                    Otoritas Verifikasi Provinsi
                </div>
            </div>

            <!-- Kartu Metrik Komparasi Side-by-Side -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 border-b border-[#C9C1B1]/40">
                <!-- Sisi Kiri: Angka Master Saat Ini -->
                <div class="p-4 rounded-xl bg-white border border-[#C9C1B1]/60 shadow-2xs">
                    <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">
                        1. Data Master UPT Saat Ini (Sebelum Disahkan)
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Jumlah KK Master</span>
                            <span class="text-2xl font-black text-slate-800">{{ number_format($registrySummary['current_master_kk'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400"> KK</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Jumlah Jiwa Master</span>
                            <span class="text-2xl font-black text-slate-800">{{ number_format($registrySummary['current_master_population'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400"> Jiwa</span>
                        </div>
                    </div>
                    <div class="mt-3 text-[11px] text-slate-400 italic">
                        * Catatan: Data ini merupakan angka rekapitulasi sebelum operator melakukan sensus registri nominal.
                    </div>
                </div>

                <!-- Sisi Kanan: Hasil Pencatatan Registri Lapangan -->
                <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-900">
                            2. Data Hasil Registri Lapangan (Yang Diusulkan)
                        </span>
                        @php
                            $delta = (int)($registrySummary['delta_kk'] ?? 0);
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $delta >= 0 ? 'bg-emerald-200 text-emerald-950' : 'bg-rose-200 text-rose-950' }}">
                            {{ $delta >= 0 ? '+' : '' }}{{ $delta }} KK
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded-lg bg-white border border-emerald-200">
                            <span class="text-[10px] text-emerald-700 font-bold uppercase block">Jumlah KK Registri</span>
                            <span class="text-2xl font-black text-emerald-950">{{ number_format($registrySummary['recorded_kk'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-emerald-700 font-bold"> KK</span>
                        </div>
                        <div class="p-3 rounded-lg bg-white border border-emerald-200">
                            <span class="text-[10px] text-emerald-700 font-bold uppercase block">Jumlah Jiwa Registri</span>
                            <span class="text-2xl font-black text-emerald-950">{{ number_format($registrySummary['recorded_population'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-emerald-700 font-bold"> Jiwa</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-[11px] font-semibold text-emerald-900 flex-wrap">
                        <span>TPA: <strong>{{ $registrySummary['tpa_count'] ?? 0 }} KK</strong></span>
                        <span>•</span>
                        <span>TPS: <strong>{{ $registrySummary['tps_count'] ?? 0 }} KK</strong></span>
                        <span>•</span>
                        <span>SHM: <strong>{{ $registrySummary['shm_count'] ?? 0 }} Sertipikat</strong></span>
                    </div>
                </div>
            </div>

            <!-- Cuplikan Sampel Kartu KK Registri -->
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Cuplikan Entitas KK Terdaftar (Menampilkan {{ count($registrySampleCards) }} dari {{ $registryTotalCards }} KK):</span>
                    </h4>
                    <span class="text-[11px] font-bold text-slate-500 font-mono">Total {{ $registryTotalCards }} KK Tercatat</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 text-[10px] uppercase font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-center w-8">No</th>
                                <th class="px-3 py-2">Kepala Keluarga</th>
                                <th class="px-3 py-2 text-center">No KK / NIK</th>
                                <th class="px-3 py-2 text-center">Jiwa</th>
                                <th class="px-3 py-2 text-center">Jenis & Asal</th>
                                <th class="px-3 py-2 text-center">Blok Kapling</th>
                                <th class="px-3 py-2 text-center">Status SHM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($registrySampleCards as $idx => $card)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-3 py-2 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2 font-bold text-slate-900">{{ $card->head_of_family_name }}</td>
                                    <td class="px-3 py-2 text-center font-mono text-[11px] text-slate-600">
                                        <div>{{ $card->family_card_number ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $card->nik ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-slate-800">{{ $card->family_members_count }} Jiwa</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $card->transmigrant_type === 'TPA' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $card->transmigrant_type }}
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $card->origin_regency ?? $card->origin_province ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center font-medium">{{ $card->housing_block ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ str_contains(strtolower($card->land_certificate_status), 'sudah') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $card->land_certificate_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">Tidak ada kartu terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- 2. TABEL KOMPARASI SIDE-BY-SIDE (DIFF CHECKER STANDAR) -->
        <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
            <div class="p-5 bg-[#1B2632] text-[#EEE9DF] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#FFB162] text-[#1B2632] flex items-center justify-center font-black text-sm shadow-xs">
                        VS
                    </div>
                    <div>
                        <h3 class="text-sm font-black tracking-tight text-[#EEE9DF] uppercase">
                            Lembar Komparasi Data (Visual Diff Checker)
                        </h3>
                        <p class="text-[11px] text-[#EEE9DF]/70">
                            Perubahan parameter ditandai dengan lencana berwarna terracotta pada sisi kanan
                        </p>
                    </div>
                </div>
                <div class="text-[11px] font-bold text-[#FFB162] bg-[#2C3B4D] px-3 py-1 rounded-full border border-[#FFB162]/30">
                    Otoritas Verifikasi Provinsi
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#EEE9DF]/60 text-[#2C3B4D] font-extrabold text-[10px] uppercase tracking-wider border-b border-[#C9C1B1]/60">
                            <th class="px-5 py-3 w-1/4">Parameter Data UPT</th>
                            <th class="px-5 py-3 w-3/8 bg-[#EEE9DF]/40 border-r border-[#C9C1B1]/40">
                                <span class="text-[#2C3B4D]/70">Data Master Saat Ini (Aktif)</span>
                            </th>
                            <th class="px-5 py-3 w-3/8 bg-[#FFB162]/10">
                                <span class="text-[#8F4E0A] font-extrabold">Data Usulan Baru (Operator)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C9C1B1]/30 text-xs">
                        @foreach($comparisonFields as $fieldKey => $field)
                            @php
                                $isDifferent = (trim((string)$field['current']) !== trim((string)$field['proposed']));
                            @endphp
                            <tr class="{{ $isDifferent ? 'bg-[#FFB162]/10' : 'hover:bg-[#EEE9DF]/30' }} transition">
                                <!-- Kolom 1: Nama Field -->
                                <td class="px-5 py-3.5 font-extrabold text-[#1B2632]">
                                    {{ $field['label'] }}
                                    @if($isDifferent)
                                        <span class="block text-[10px] font-bold text-[#A35139] mt-0.5">
                                            ⚡ Terdapat Perubahan
                                        </span>
                                    @endif
                                </td>

                                <!-- Kolom 2: Data Aktif -->
                                <td class="px-5 py-3.5 bg-[#EEE9DF]/20 border-r border-[#C9C1B1]/30 text-[#2C3B4D]/80">
                                    @if($fieldKey === 'issue_status')
                                        <x-status-badge :status="$field['current']" mode="full" size="xs" />
                                    @else
                                        <span class="font-medium">{{ $field['current'] }}</span>
                                    @endif
                                </td>

                                <!-- Kolom 3: Data Usulan Baru -->
                                <td class="px-5 py-3.5 {{ $isDifferent ? 'bg-[#FFB162]/15' : '' }}">
                                    @if($fieldKey === 'issue_status')
                                        <x-status-badge :status="$field['proposed']" mode="full" size="xs" />
                                    @else
                                        <span class="{{ $isDifferent ? 'font-black text-[#A35139]' : 'text-[#2C3B4D]/80' }}">
                                            {{ $field['proposed'] }}
                                        </span>
                                    @endif

                                    @if($isDifferent)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.2 rounded text-[10px] font-black bg-[#A35139] text-[#EEE9DF]">
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
    @endif

    <!-- 3. LAMPIRAN BERKAS BAST (JIKA ADA) -->
    @if(isset($payload['attached_document']))
        @php $doc = $payload['attached_document']; @endphp
        <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs space-y-3">
            <h3 class="text-xs font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                <span>Lampiran Berkas Digital BAST yang Diusulkan:</span>
            </h3>

            <div class="p-4 rounded-xl bg-[#EEE9DF]/40 border border-[#C9C1B1]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2C3B4D]/10 border border-[#2C3B4D]/25 text-[#2C3B4D] flex items-center justify-center font-bold text-xs">
                        PDF
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-[#1B2632]">{{ $doc['document_name'] ?? 'Berkas Scan BAST' }}</div>
                        <div class="text-[11px] text-[#2C3B4D] font-mono">No. Reg: {{ $doc['document_number'] ?? '-' }}</div>
                        <div class="text-[10px] text-[#2C3B4D]/50">Ukuran: {{ isset($doc['file_size_bytes']) ? round($doc['file_size_bytes']/1024, 1) . ' KB' : '-' }}</div>
                    </div>
                </div>

                <a href="{{ Storage::url($doc['file_path']) }}" target="_blank" 
                   class="px-4 py-2 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] text-xs font-bold transition flex items-center gap-2 self-start sm:self-auto shadow-ambient-xs">
                    <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Buka / Pratinjau Dokumen PDF</span>
                </a>
            </div>
            <p class="text-[11px] text-[#2C3B4D]/70 italic">
                * Catatan: Jika draf usulan disetujui, berkas ini akan otomatis didaftarkan ke <strong>Repositori E-Arsip BAST</strong> dinas.
            </p>
        </div>
    @endif

    <!-- 4. PANEL AKSI KEPUTUSAN VERIFIKATOR (APPROVE / REJECT) -->
    @if($changeRequest->status === 'pending')
        <div class="bg-white rounded-2xl p-6 border-2 border-[#C9C1B1] shadow-ambient space-y-5">
            <div class="border-b border-[#C9C1B1]/30 pb-3">
                <h3 class="text-sm font-black text-[#1B2632] uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Keputusan Verifikasi Super Admin</span>
                </h3>
                <p class="text-xs text-[#2C3B4D]/70 mt-0.5">
                    Silakan tentukan persetujuan atau penolakan draf usulan ini. Seluruh aksi dicatat dalam audit log forensik.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- FORM SETUJUI (APPROVE) -->
                <form action="{{ route('admin.verification.approve', $changeRequest->id) }}" method="POST" class="p-5 rounded-xl bg-[#EEE9DF]/60 border border-[#C9C1B1] space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-black text-[#1B2632] uppercase tracking-wider block">
                            Opsi A: Setujui Usulan (Approve)
                        </span>
                        <p class="text-[11px] text-[#2C3B4D]/80 mt-0.5">
                            Data usulan akan langsung menimpa data master UPT dan dokumen pendukung didaftarkan ke e-arsip.
                        </p>
                    </div>

                    <div>
                        <label for="approve_note" class="block text-xs font-bold text-[#2C3B4D] mb-1">
                            Catatan Persetujuan (Opsional)
                        </label>
                        <input type="text" name="reviewer_note" id="approve_note" 
                               value="Data usulan telah sesuai dengan berita acara dan hasil sinkronisasi."
                               class="w-full text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 text-[#1B2632]">
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menyetujui draf usulan ini? Data master UPT akan otomatis dimutakhirkan.')"
                            class="w-full py-2.5 px-4 rounded-xl bg-[#2C3B4D] hover:bg-[#1B2632] text-[#EEE9DF] font-black text-xs transition shadow-ambient-xs flex items-center justify-center gap-2 border border-[#2C3B4D]">
                        <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Setujui & Terapkan Perubahan (Approve)</span>
                    </button>
                </form>

                <!-- FORM TOLAK (REJECT) -->
                <form action="{{ route('admin.verification.reject', $changeRequest->id) }}" method="POST" class="p-5 rounded-xl bg-[#A35139]/10 border border-[#A35139]/30 space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-black text-[#A35139] uppercase tracking-wider block">
                            Opsi B: Tolak Usulan (Reject)
                        </span>
                        <p class="text-[11px] text-[#A35139]/80 mt-0.5">
                            Data master tidak akan diubah. Berikan uraian alasan penolakan agar dapat diperbaiki operator.
                        </p>
                    </div>

                    <div>
                        <label for="reject_note" class="block text-xs font-bold text-[#A35139] mb-1">
                            Alasan / Catatan Penolakan <span class="text-[#A35139]">*</span>
                        </label>
                        <textarea name="reviewer_note" id="reject_note" rows="2" required
                                  placeholder="Contoh: Lampiran BAST belum ditandatangani bupati, mohon lengkapi kembali..."
                                  class="w-full text-xs rounded-xl border-[#A35139]/40 focus:border-[#A35139] focus:ring focus:ring-[#A35139]/20 text-[#1B2632]"></textarea>
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menolak draf usulan ini?')"
                            class="w-full py-2.5 px-4 rounded-xl bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-black text-xs transition shadow-ambient-xs flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak Usulan dengan Catatan (Reject)</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Informasi Riwayat Verifikasi yang Telah Selesai -->
        <div class="bg-white rounded-2xl p-6 border border-[#C9C1B1]/70 shadow-ambient-xs">
            <h3 class="text-xs font-black text-[#2C3B4D] uppercase tracking-wider mb-2">
                Riwayat Rekam Jejak Keputusan Verifikator:
            </h3>
            <div class="p-4 rounded-xl {{ $changeRequest->status === 'approved' ? 'bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25' : 'bg-[#A35139]/10 text-[#A35139] border border-[#A35139]/25' }} text-xs space-y-1">
                <div class="font-bold">
                    Keputusan: {{ $changeRequest->status === 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                </div>
                <div>Verifikator: {{ $changeRequest->reviewer->name ?? 'Super Admin' }} ({{ $changeRequest->reviewer->email ?? '' }})</div>
                <div>Waktu Tindakan: {{ $changeRequest->reviewed_at?->format('d F Y, H:i') }} WITA</div>
                <div class="mt-2 pt-2 border-t {{ $changeRequest->status === 'approved' ? 'border-[#2C3B4D]/20' : 'border-[#A35139]/20' }}">
                    <strong>Catatan Reviewer:</strong> {{ $changeRequest->reviewer_note ?? '-' }}
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
