@extends('layouts.admin')

@section('title', 'Diff Checker - Verifikasi Draf #' . $changeRequest->id)
@section('header_title', 'Diff Checker & Verifikasi Draf Usulan')
@section('header_subtitle', 'Bandingkan data aktif dengan data usulan baru dari Operator Kabupaten sebelum melakukan approval')
@section('atlas_subtitle', 'Bandingkan usulan operator dengan data UPT saat ini, lalu tentukan keputusan.')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Tombol Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.verification.index') }}"
           class="atlas-link inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke antrean verifikasi</span>
        </a>

        <div class="flex items-center gap-2 text-xs">
            <span class="text-[#5A6E7D]">Status permohonan:</span>
            <x-admin.status :status="$changeRequest->status" />
        </div>
    </div>

    <!-- 1. KARTU INFORMASI PENGAJUAN & CATATAN OPERATOR -->
    <div class="atlas-panel p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-[#D4DEE7] pb-5">
            <div>
                <span class="atlas-code uppercase block">Unit UPT sasaran</span>
                <span class="text-[15px] font-semibold text-[#243746]">
                    UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->upt_name }}
                </span>
                <span class="block text-[13px] text-[#5A6E7D]">Kabupaten {{ $upt->regency->name ?? '-' }}</span>
            </div>
            <div>
                <span class="atlas-code uppercase block">Operator pengaju</span>
                <span class="text-[15px] font-semibold text-[#243746]">{{ $changeRequest->user->name ?? '-' }}</span>
                <span class="block text-[13px] text-[#5A6E7D] tabular-nums">NIP {{ $changeRequest->user->nip ?? '-' }}</span>
            </div>
            <div>
                <span class="atlas-code uppercase block">Waktu pengajuan</span>
                <span class="text-[15px] font-semibold text-[#243746]">{{ $changeRequest->created_at->format('d F Y') }}</span>
                <span class="block text-[13px] text-[#5A6E7D] tabular-nums">{{ $changeRequest->created_at->format('H:i:s') }} WITA</span>
            </div>
            <div>
                <span class="atlas-code uppercase block">Kategori usulan</span>
                @if($changeRequest->request_type === 'REGISTRY_SYNC')
                    <span class="atlas-status atlas-status--warning mt-0.5"><span aria-hidden="true"></span>Buku registri warga</span>
                @else
                    <span class="atlas-status mt-0.5"><span aria-hidden="true"></span>{{ $changeRequest->request_type === 'DATA_UPDATE' ? 'Data lapangan' : ($changeRequest->request_type === 'LEGAL_ISSUE' ? 'Masalah lahan' : 'Berkas BAST baru') }}</span>
                @endif
            </div>
        </div>

        <!-- Uraian Alasan Pengajuan -->
        <div class="mt-4">
            <span class="text-[13px] font-semibold text-[#243746] block mb-1">
                Catatan pengantar & alasan dari operator:
            </span>
            <div class="atlas-alert" style="margin-bottom: 0;">
                {{ $payload['submission_note'] ?? '(Operator tidak menyertakan uraian pengantar)' }}
            </div>
        </div>
    </div>

    @if($changeRequest->request_type === 'REGISTRY_SYNC' && $registrySummary)
        <!-- 2. KOMPARASI BUKU REGISTRI WARGA (DIFF CHECKER REGISTRI) -->
        <div class="atlas-panel">
            <div class="atlas-panel-heading">
                <div>
                    <h3>Komparasi buku registri warga vs basis data master</h3>
                    <p style="font-size: 13px; color: var(--atlas-muted); margin: 2px 0 0;">
                        Pengesahan sinkronisasi angka KK &amp; jiwa dari data nominal lapangan {{ $registrySummary['stage_label'] ?? '' }}
                    </p>
                </div>
                <span class="atlas-status atlas-status--clean"><span aria-hidden="true"></span>Otoritas verifikasi provinsi</span>
            </div>

            <!-- Kartu Metrik Komparasi Side-by-Side -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-[#F3F6F8] border-b border-[#D4DEE7]">
                <!-- Sisi Kiri: Angka Master Saat Ini -->
                <div class="p-4 rounded-md bg-white border border-[#D4DEE7]">
                    <div class="atlas-code uppercase mb-2">
                        1. Data master UPT saat ini (sebelum disahkan)
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded bg-[#F3F6F8] border border-[#D4DEE7]">
                            <span class="atlas-code uppercase block">Jumlah KK master</span>
                            <span class="text-2xl font-semibold text-[#243746] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">{{ number_format($registrySummary['current_master_kk'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-[#5A6E7D]"> KK</span>
                        </div>
                        <div class="p-3 rounded bg-[#F3F6F8] border border-[#D4DEE7]">
                            <span class="atlas-code uppercase block">Jumlah jiwa master</span>
                            <span class="text-2xl font-semibold text-[#243746] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">{{ number_format($registrySummary['current_master_population'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-[#5A6E7D]"> Jiwa</span>
                        </div>
                    </div>
                    <div class="mt-3 text-[12px] text-[#5A6E7D] italic">
                        * Data ini merupakan angka rekapitulasi sebelum operator melakukan sensus registri nominal.
                    </div>
                </div>

                <!-- Sisi Kanan: Hasil Pencatatan Registri Lapangan -->
                <div class="p-4 rounded-md bg-[#EDF6F0] border border-[#B7D9C6]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-[#287451]">
                            2. Data hasil registri lapangan (yang diusulkan)
                        </span>
                        @php
                            $delta = (int)($registrySummary['delta_kk'] ?? 0);
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $delta >= 0 ? 'bg-[#DAE7F6] text-[#194482]' : 'bg-[#FBEDED] text-[#8F2D2D]' }} tabular-nums">
                            {{ $delta >= 0 ? '+' : '' }}{{ $delta }} KK
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded bg-white border border-[#B7D9C6]">
                            <span class="atlas-code uppercase block" style="color: #287451;">Jumlah KK registri</span>
                            <span class="text-2xl font-semibold text-[#287451] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">{{ number_format($registrySummary['recorded_kk'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-[#287451]"> KK</span>
                        </div>
                        <div class="p-3 rounded bg-white border border-[#B7D9C6]">
                            <span class="atlas-code uppercase block" style="color: #287451;">Jumlah jiwa registri</span>
                            <span class="text-2xl font-semibold text-[#287451] tabular-nums" style="font-family: 'Barlow Semi Condensed', sans-serif;">{{ number_format($registrySummary['recorded_population'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-xs text-[#287451]"> Jiwa</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-[12px] font-semibold text-[#287451] flex-wrap">
                        <span>TPA: <strong>{{ $registrySummary['tpa_count'] ?? 0 }} KK</strong></span>
                        <span aria-hidden="true">·</span>
                        <span>TPS: <strong>{{ $registrySummary['tps_count'] ?? 0 }} KK</strong></span>
                        <span aria-hidden="true">·</span>
                        <span>SHM: <strong>{{ $registrySummary['shm_count'] ?? 0 }} sertipikat</strong></span>
                    </div>
                </div>
            </div>

            <!-- Cuplikan Sampel Kartu KK Registri -->
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-[13px] font-semibold text-[#243746] uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#287451]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Cuplikan entitas KK terdaftar (menampilkan {{ count($registrySampleCards) }} dari {{ $registryTotalCards }} KK):</span>
                    </h4>
                    <span class="atlas-code">Total {{ $registryTotalCards }} KK tercatat</span>
                </div>

                <div class="overflow-x-auto rounded border border-[#D4DEE7]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#EDF2F6] text-[#5A6E7D] text-[10px] uppercase font-semibold border-b border-[#D4DEE7]">
                            <tr>
                                <th class="px-3 py-2 text-center w-8">No</th>
                                <th class="px-3 py-2">Kepala keluarga</th>
                                <th class="px-3 py-2 text-center">No KK / NIK</th>
                                <th class="px-3 py-2 text-center">Jiwa</th>
                                <th class="px-3 py-2 text-center">Jenis &amp; asal</th>
                                <th class="px-3 py-2 text-center">Blok kapling</th>
                                <th class="px-3 py-2 text-center">Status SHM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5EBF0] text-[#243746]">
                            @forelse($registrySampleCards as $idx => $card)
                                <tr class="hover:bg-[#F8FAFC] transition">
                                    <td class="px-3 py-2 text-center text-[#5A6E7D] tabular-nums">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#243746]">{{ $card->head_of_family_name }}</td>
                                    <td class="px-3 py-2 text-center text-[11px] text-[#5A6E7D] tabular-nums">
                                        <div>{{ $card->family_card_number ?? '-' }}</div>
                                        <div class="text-[10px]">{{ $card->nik ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center font-semibold tabular-nums">{{ $card->family_members_count }} jiwa</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $card->transmigrant_type === 'TPA' ? 'bg-[#DAE7F6] text-[#194482]' : 'bg-[#FAF3E4] text-[#97620B]' }}">
                                            {{ $card->transmigrant_type }}
                                        </span>
                                        <div class="text-[10px] text-[#5A6E7D] mt-0.5">{{ $card->origin_regency ?? $card->origin_province ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center">{{ $card->housing_block ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ str_contains(strtolower($card->land_certificate_status), 'sudah') ? 'bg-[#EDF6F0] text-[#287451]' : 'bg-[#EEF2F5] text-[#5A6E7D]' }}">
                                            {{ $card->land_certificate_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-[#5A6E7D]">Tidak ada kartu terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- 2. TABEL KOMPARASI SIDE-BY-SIDE (DIFF CHECKER STANDAR) -->
        <div class="atlas-panel">
            <div class="atlas-panel-heading">
                <div>
                    <h3>Perbandingan data UPT</h3>
                    <p style="font-size: 13px; color: var(--atlas-muted); margin: 2px 0 0;">
                        Nilai yang berubah diberi penanda pada kolom usulan.
                    </p>
                </div>
                <span class="atlas-status"><span aria-hidden="true"></span>Otoritas verifikasi provinsi</span>
            </div>

            <div class="overflow-x-auto" x-data="{ onlyChanges: false }">
                <label class="atlas-review-toolbar"><input type="checkbox" x-model="onlyChanges"> Hanya tampilkan perubahan</label>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#EDF2F6] text-[#5A6E7D] font-semibold text-[10px] uppercase tracking-wider border-b border-[#D4DEE7]">
                            <th class="px-5 py-3 w-1/4">Parameter data UPT</th>
                            <th class="px-5 py-3 bg-[#E7EEF3] border-r border-[#D4DEE7]">
                                Data master saat ini (aktif)
                            </th>
                            <th class="px-5 py-3 bg-[#EDF3FB]">
                                <span class="text-[#194482] font-semibold">Data usulan baru (operator)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5EBF0] text-xs">
                        @foreach($comparisonFields as $fieldKey => $field)
                            @php
                                $isDifferent = (trim((string)$field['current']) !== trim((string)$field['proposed']));
                            @endphp
                            <tr x-show="!onlyChanges || {{ $isDifferent ? 'true' : 'false' }}" class="{{ $isDifferent ? 'atlas-diff-changed bg-[#EDF3FB]/60' : 'hover:bg-[#F8FAFC]' }} transition">
                                <!-- Kolom 1: Nama Field -->
                                <td class="px-5 py-3.5 font-semibold text-[#243746]">
                                    {{ $field['label'] }}
                                    @if($isDifferent)
                                        <span class="block text-[10px] font-semibold text-[#B43D3D] mt-0.5">
                                            Diubah
                                        </span>
                                    @endif
                                </td>

                                <!-- Kolom 2: Data Aktif -->
                                <td class="px-5 py-3.5 bg-[#F3F6F8] border-r border-[#D4DEE7] text-[#5A6E7D]">
                                    @if($fieldKey === 'issue_status')
                                        <span class="atlas-status atlas-status--{{ ['clean' => 'clean', 'warning' => 'warning', 'critical' => 'critical'][$field['current']] ?? 'neutral' }}"><span aria-hidden="true"></span>{{ $field['current'] ?: 'Belum tercatat' }}</span>
                                    @else
                                        <span>{{ $field['current'] === null || $field['current'] === '' ? 'Belum tercatat' : $field['current'] }}</span>
                                    @endif
                                </td>

                                <!-- Kolom 3: Data Usulan Baru -->
                                <td class="px-5 py-3.5 {{ $isDifferent ? 'bg-[#EDF3FB]' : '' }}">
                                    @if($fieldKey === 'issue_status')
                                        <span class="atlas-status atlas-status--{{ ['clean' => 'clean', 'warning' => 'warning', 'critical' => 'critical'][$field['proposed']] ?? 'neutral' }}"><span aria-hidden="true"></span>{{ $field['proposed'] ?: 'Belum tercatat' }}</span>
                                    @else
                                        <span class="{{ $isDifferent ? 'font-semibold text-[#243746]' : 'text-[#5A6E7D]' }}">
                                            {{ $field['proposed'] === null || $field['proposed'] === '' ? 'Dikosongkan' : $field['proposed'] }}
                                        </span>
                                    @endif

                                    @if($isDifferent)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-[#2457A7] text-white">
                                            Usulan
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if(collect($comparisonFields)->every(fn($field) => trim((string)$field['current']) === trim((string)$field['proposed'])))
                            <tr x-show="onlyChanges" x-cloak><td colspan="3" class="p-5 text-center text-[#5A6E7D]">Tidak ada perubahan atribut UPT. Periksa dokumen atau registri yang dilampirkan.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 3. LAMPIRAN BERKAS BAST (JIKA ADA) -->
    @if(isset($payload['attached_document']))
        @php $doc = $payload['attached_document']; @endphp
        <div class="atlas-panel p-6 space-y-3">
            <h3 class="text-[13px] font-semibold text-[#243746] uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#2457A7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                <span>Lampiran berkas digital BAST yang diusulkan:</span>
            </h3>

            <div class="p-4 rounded-md bg-[#F3F6F8] border border-[#D4DEE7] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-[#E7EEF3] border border-[#D4DEE7] text-[#5A6E7D] flex items-center justify-center font-semibold text-xs">
                        PDF
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-[#243746]">{{ $doc['document_name'] ?? 'Berkas scan BAST' }}</div>
                        <div class="text-[11px] text-[#5A6E7D] tabular-nums">No. reg: {{ $doc['document_number'] ?? '-' }}</div>
                        <div class="text-[10px] text-[#5A6E7D] tabular-nums">Ukuran: {{ isset($doc['file_size_bytes']) ? round($doc['file_size_bytes']/1024, 1) . ' KB' : '-' }}</div>
                    </div>
                </div>

                <a href="{{ Storage::url($doc['file_path']) }}" target="_blank"
                   class="atlas-button self-start sm:self-auto">
                    <x-admin.icon name="download" />
                    <span>Buka / pratinjau dokumen PDF</span>
                </a>
            </div>
            <p class="text-[11px] text-[#5A6E7D] italic">
                * Jika draf usulan disetujui, berkas ini akan otomatis didaftarkan ke <strong>Repositori E-Arsip BAST</strong> dinas.
            </p>
        </div>
    @endif

    <!-- 4. PANEL AKSI KEPUTUSAN VERIFIKATOR (APPROVE / REJECT) -->
    @if($changeRequest->status === 'pending')
        <div class="atlas-panel p-6 space-y-5" style="border-left: 3px solid var(--atlas-blue);">
            <div class="border-b border-[#D4DEE7] pb-3">
                <h3 class="text-[15px] font-semibold text-[#243746]">
                    Keputusan verifikasi Super Admin
                </h3>
                <p class="text-[13px] text-[#5A6E7D] mt-0.5">
                    Tentukan persetujuan atau penolakan draf usulan ini. Seluruh aksi dicatat dalam log aktivitas.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- FORM SETUJUI (APPROVE) -->
                <form action="{{ route('admin.verification.approve', $changeRequest->id) }}" method="POST" class="p-5 rounded-md bg-[#EDF6F0] border border-[#B7D9C6] space-y-4">
                    @csrf
                    <div>
                        <span class="text-[13px] font-semibold text-[#287451] block">
                            Setujui perubahan
                        </span>
                        <p class="text-[12px] text-[#5A6E7D] mt-0.5">
                            Data usulan akan langsung menimpa data master UPT dan dokumen pendukung didaftarkan ke e-arsip.
                        </p>
                    </div>

                    <div>
                        <label for="approve_note" class="block text-xs font-semibold text-[#243746] mb-1">
                            Catatan persetujuan (opsional)
                        </label>
                        <input type="text" name="reviewer_note" id="approve_note"
                               value="{{ old('reviewer_note') }}" placeholder="Catatan hasil pemeriksaan, jika diperlukan"
                               class="w-full text-xs rounded border-[#D4DEE7] focus:border-[#2457A7] focus:ring focus:ring-[#2457A7]/20 text-[#243746]">
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menyetujui draf usulan ini? Data master UPT akan otomatis dimutakhirkan.')"
                            class="w-full py-2.5 px-4 rounded bg-[#287451] hover:bg-[#1f5a3e] text-white font-semibold text-xs transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 13l4 4L19 7"></path></svg>
                        <span>Setujui perubahan</span>
                    </button>
                </form>

                <!-- FORM TOLAK (REJECT) -->
                <form action="{{ route('admin.verification.reject', $changeRequest->id) }}" method="POST" class="p-5 rounded-md bg-[#FBEDED] border border-[#E7BFBF] space-y-4">
                    @csrf
                    <div>
                        <span class="text-[13px] font-semibold text-[#B43D3D] block">
                            Tolak usulan
                        </span>
                        <p class="text-[12px] text-[#5A6E7D] mt-0.5">
                            Data master tidak akan diubah. Berikan uraian alasan penolakan agar dapat diperbaiki operator.
                        </p>
                    </div>

                    <div>
                        <label for="reject_note" class="block text-xs font-semibold text-[#B43D3D] mb-1">
                            Alasan / catatan penolakan <span aria-hidden="true">*</span>
                        </label>
                        <textarea name="reviewer_note" id="reject_note" rows="2" required
                                  placeholder="Contoh: Lampiran BAST belum ditandatangani bupati, mohon lengkapi kembali..."
                                  class="w-full text-xs rounded border-[#E7BFBF] focus:border-[#B43D3D] focus:ring focus:ring-[#B43D3D]/20 text-[#243746]">{{ old('reviewer_note') }}</textarea>
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin menolak draf usulan ini?')"
                            class="w-full py-2.5 px-4 rounded bg-[#B43D3D] hover:bg-[#98302f] text-white font-semibold text-xs transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak usulan</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Informasi Riwayat Verifikasi yang Telah Selesai -->
        <div class="atlas-panel p-6">
            <h3 class="text-[13px] font-semibold text-[#243746] uppercase tracking-wider mb-2">
                Riwayat keputusan verifikator:
            </h3>
            <div class="p-4 rounded-md {{ $changeRequest->status === 'approved' ? 'bg-[#EDF6F0] text-[#287451] border border-[#B7D9C6]' : 'bg-[#FBEDED] text-[#B43D3D] border border-[#E7BFBF]' }} text-xs space-y-1">
                <div class="font-semibold">
                    Keputusan: {{ $changeRequest->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                </div>
                <div>Verifikator: {{ $changeRequest->reviewer->name ?? 'Super Admin' }} ({{ $changeRequest->reviewer->email ?? '' }})</div>
                <div>Waktu tindakan: {{ $changeRequest->reviewed_at?->format('d F Y, H:i') }} WITA</div>
                <div class="mt-2 pt-2 border-t {{ $changeRequest->status === 'approved' ? 'border-[#B7D9C6]' : 'border-[#E7BFBF]' }}">
                    <strong>Catatan reviewer:</strong> {{ $changeRequest->reviewer_note ?? '-' }}
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
