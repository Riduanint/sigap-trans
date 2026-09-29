@extends('layouts.admin')

@section('title', 'Repositori E-Arsip BAST & Dokumen SK')
@section('header_title', 'Repositori E-Arsip Digital BAST & Dokumen SK')
@section('header_subtitle', 'Pusat penyimpanan digital berkas Berita Acara Serah Terima (BAST), SK Pelepasan Kawasan, dan Warkah Tanah 124 UPT')

@section('content')
<div class="space-y-6" x-data="{ openUploadModal: false }">

    <!-- 1. KARTU STATISTIK DOKUMEN (MP072 ARCHITECTURAL PALETTE) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            label="Total Arsip Digital" 
            :value="number_format($stats['total_docs'], 0, ',', '.')" 
            unit="Berkas"
            variant="abyssal"
            subtext="Repositori digital terpadu">
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            label="Berita Acara (BAST)" 
            :value="number_format($stats['total_bast'], 0, ',', '.')" 
            unit="Berkas"
            variant="slate"
            subtext="Serah terima fisik definitif">
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            label="SK Pelepasan Kawasan" 
            :value="number_format($stats['total_sk'], 0, ',', '.')" 
            unit="Berkas"
            variant="flame"
            subtext="SK Menteri & SK Gubernur">
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#8F4E0A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            label="Buku Tanah / Warkah SHM" 
            :value="number_format($stats['total_buku_tanah'], 0, ',', '.')" 
            unit="Berkas"
            variant="truffle"
            subtext="Alas hak sertipikat warga">
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#A35139]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- 2. FILTER & TOMBOL AKSI UNGGAH -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/60 shadow-ambient-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.documents.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nomor register / nama file..." 
                       class="w-full bg-white border border-[#C9C1B1] focus:border-[#1B2632] focus:ring-2 focus:ring-[#1B2632]/10 rounded-xl px-3.5 py-2 pl-9 text-xs transition text-[#1B2632] font-medium">
                <svg class="w-4 h-4 text-[#C9C1B1] absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <select name="type" class="bg-white border border-[#C9C1B1] focus:border-[#1B2632] focus:ring-2 focus:ring-[#1B2632]/10 rounded-xl px-3.5 py-2 text-xs font-semibold text-[#1B2632] cursor-pointer transition">
                <option value="all">Semua Tipe Berkas</option>
                <option value="BAST" {{ request('type') == 'BAST' ? 'selected' : '' }}>BAST Fisik</option>
                <option value="SK_GUBERNUR" {{ request('type') == 'SK_GUBERNUR' ? 'selected' : '' }}>SK Gubernur</option>
                <option value="SK_MENTERI" {{ request('type') == 'SK_MENTERI' ? 'selected' : '' }}>SK Menteri</option>
                <option value="BUKU_TANAH" {{ request('type') == 'BUKU_TANAH' ? 'selected' : '' }}>Buku Tanah / SHM</option>
            </select>

            <x-btn type="submit" variant="abyssal" size="sm">
                Saring Berkas
            </x-btn>

            @if(request()->filled('search') || (request()->filled('type') && request('type') !== 'all'))
                <x-btn href="{{ route('admin.documents.index') }}" variant="ghost" size="sm">
                    ✕ Reset Filter
                </x-btn>
            @endif
        </form>

        <x-btn @click="openUploadModal = true" variant="truffle" size="sm" class="shrink-0">
            <x-slot:icon>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </x-slot:icon>
            <span>Unggah Berkas BAST Baru</span>
        </x-btn>
    </div>
    {{-- 3. TABEL REPOSITORI DOKUMEN (STANDAR EDITORIAL x-table) --}}
    <x-table headerStyle="ink">
        <x-slot:thead>
            <tr>
                <th class="py-3.5 px-4">Tipe Berkas</th>
                <th class="py-3.5 px-4">No. Registrasi & UPT</th>
                <th class="py-3.5 px-4">Nomor Register Dokumen</th>
                <th class="py-3.5 px-4">Nama Berkas Fisik</th>
                <th class="py-3.5 px-4 text-center">Ukuran</th>
                <th class="py-3.5 px-4">Pengunggah</th>
                <th class="py-3.5 px-4 text-center w-28">Aksi</th>
            </tr>
        </x-slot:thead>

        @forelse($documents as $doc)
            @php
                $badgeVariant = match($doc->document_type) {
                    'BAST' => 'slate',
                    'SK_GUBERNUR', 'SK_MENTERI' => 'flame',
                    'BUKU_TANAH' => 'truffle',
                    default => 'abyssal',
                };
            @endphp
            <tr class="hover:bg-[#EEE9DF]/40 transition-colors">
                <td class="py-3 px-4">
                    <x-badge :variant="$badgeVariant" dot>
                        {{ str_replace('_', ' ', $doc->document_type) }}
                    </x-badge>
                </td>
                <td class="py-3 px-4">
                    <div class="font-extrabold text-[#1B2632]">
                        UPT-{{ str_pad($doc->uptLocation?->upt_number, 3, '0', STR_PAD_LEFT) }}: {{ $doc->uptLocation?->upt_name }}
                    </div>
                    <div class="text-[11px] text-[#1B2632]/60 font-medium">Kab. {{ $doc->uptLocation?->regency?->name ?? '-' }}</div>
                </td>
                <td class="py-3 px-4 font-mono text-[#1B2632]/80 font-bold text-xs">
                    {{ $doc->document_number ?: '-' }}
                </td>
                <td class="py-3 px-4 text-[#1B2632] font-semibold">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-[#A35139]/15 text-[#A35139] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </span>
                        <span class="truncate max-w-xs">{{ $doc->file_name }}</span>
                    </div>
                </td>
                <td class="py-3 px-4 text-center font-mono text-[#1B2632]/70 font-semibold tabular-nums">
                    {{ number_format($doc->file_size_kb) }} KB
                </td>
                <td class="py-3 px-4 text-[#1B2632]/70 text-[11px] font-medium">
                    {{ $doc->uploader?->name ?: 'Super Admin' }}
                </td>
                <td class="py-3 px-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('admin.documents.download', $doc->id) }}" 
                           title="Unduh Berkas PDF"
                           class="w-7 h-7 rounded-lg bg-[#2C3B4D]/10 text-[#2C3B4D] hover:bg-[#2C3B4D] hover:text-white border border-[#2C3B4D]/25 transition flex items-center justify-center shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.documents.destroy', $doc->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip digital ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Dokumen" 
                                    class="w-7 h-7 rounded-lg bg-[#A35139]/15 text-[#A35139] hover:bg-[#A35139] hover:text-white border border-[#A35139]/30 transition flex items-center justify-center shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-16 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-[#C9C1B1]/30 text-[#1B2632]/60 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div class="font-extrabold text-sm text-[#1B2632]">Belum Ada Arsip Digital</div>
                    <div class="text-xs text-[#1B2632]/60 mt-0.5">Belum ada berkas fisik digital yang diunggah ke repositori.</div>
                </td>
            </tr>
        @endforelse

        @if($documents->hasPages())
            <x-slot:pagination>
                <div class="text-xs text-[#1B2632]/60">
                    Menampilkan <strong class="text-[#1B2632] font-bold">{{ $documents->firstItem() ?? 0 }}</strong> - <strong class="text-[#1B2632] font-bold">{{ $documents->lastItem() ?? 0 }}</strong> dari <strong class="text-[#1B2632] font-bold">{{ $documents->total() }}</strong> berkas
                </div>
                <div>
                    {{ $documents->links() }}
                </div>
            </x-slot:pagination>
        @endif
    </x-table>

    <!-- 4. MODAL FORM UNGGAH BERKAS DIGITAL BAST -->
    <div x-show="openUploadModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-[#1B2632]/70 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-ambient-lg border border-[#C9C1B1]"
             @click.outside="openUploadModal = false">
            
            <div class="bg-[#1B2632] text-[#EEE9DF] p-5 flex items-center justify-between border-b border-[#121A23]">
                <div>
                    <h3 class="text-base font-extrabold tracking-tight text-white">
                        Unggah Berkas E-Arsip BAST / Dokumen SK
                    </h3>
                    <p class="text-xs text-[#C9C1B1] mt-0.5">
                        Simpan arsip digital resmi ke basis data UPT
                    </p>
                </div>
                <button @click="openUploadModal = false" class="text-[#C9C1B1] hover:text-white p-1 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.documents.upload') }}" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
                @csrf

                <!-- Pilih UPT Sasaran -->
                <div>
                    <label for="modal_upt" class="font-extrabold text-[#1B2632] uppercase text-[11px] tracking-wider block mb-1.5">
                        Pilih Lokasi UPT Sasaran <span class="text-[#A35139]">*</span>
                    </label>
                    <select name="upt_location_id" id="modal_upt" required 
                            class="w-full bg-white border border-[#C9C1B1] focus:border-[#1B2632] focus:ring-2 focus:ring-[#1B2632]/10 rounded-xl p-2.5 text-xs text-[#1B2632] font-medium">
                        <option value="">-- Pilih salah satu dari 124 UPT --</option>
                        @foreach($uptLocations as $u)
                            <option value="{{ $u->id }}">
                                UPT-{{ str_pad($u->upt_number, 3, '0', STR_PAD_LEFT) }}: {{ $u->upt_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jenis Dokumen -->
                <div>
                    <label for="modal_type" class="font-extrabold text-[#1B2632] uppercase text-[11px] tracking-wider block mb-1.5">
                        Jenis Berkas Dokumen <span class="text-[#A35139]">*</span>
                    </label>
                    <select name="document_type" id="modal_type" required 
                            class="w-full bg-white border border-[#C9C1B1] focus:border-[#1B2632] focus:ring-2 focus:ring-[#1B2632]/10 rounded-xl p-2.5 text-xs font-semibold text-[#1B2632]">
                        <option value="BAST">Berita Acara Serah Terima (BAST Fisik)</option>
                        <option value="SK_GUBERNUR">SK Penetapan Gubernur</option>
                        <option value="SK_MENTERI">SK Pelepasan Kawasan Hutan Menteri</option>
                        <option value="BUKU_TANAH">Buku Tanah / Warkah Sertifikat SHM</option>
                    </select>
                </div>

                <!-- Nomor Register Resmi -->
                <div>
                    <label for="modal_number" class="font-extrabold text-[#1B2632] uppercase text-[11px] tracking-wider block mb-1.5">
                        Nomor Register Resmi Dokumen
                    </label>
                    <input type="text" name="document_number" id="modal_number" 
                           placeholder="Contoh: BAST/503/1977 atau SK.Menhut/245/1990"
                           class="w-full bg-white border border-[#C9C1B1] focus:border-[#1B2632] focus:ring-2 focus:ring-[#1B2632]/10 rounded-xl p-2.5 text-xs text-[#1B2632] font-mono">
                </div>

                <!-- File PDF -->
                <div>
                    <label for="modal_file" class="font-extrabold text-[#1B2632] uppercase text-[11px] tracking-wider block mb-1.5">
                        Berkas Digital Scan (Format PDF) <span class="text-[#A35139]">*</span>
                    </label>
                    <input type="file" name="file" id="modal_file" accept="application/pdf" required 
                           class="w-full bg-white border border-[#C9C1B1] rounded-xl p-2 text-xs text-[#1B2632] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-extrabold file:bg-[#1B2632] file:text-[#EEE9DF] hover:file:bg-[#2C3B4D] cursor-pointer">
                    <span class="text-[10px] text-[#1B2632]/50 mt-1 block font-medium">Ukuran maksimal file: 20 MB.</span>
                </div>

                <!-- Aksi Form -->
                <div class="pt-4 border-t border-[#C9C1B1]/40 flex items-center justify-end gap-2.5">
                    <x-btn type="button" @click="openUploadModal = false" variant="secondary" size="sm">
                        Batal
                    </x-btn>
                    <x-btn type="submit" variant="truffle" size="sm">
                        Simpan ke Repositori
                    </x-btn>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
