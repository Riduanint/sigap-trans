@extends('layouts.admin')

@section('title', 'Repositori E-Arsip BAST & Dokumen SK')
@section('header_title', 'Repositori E-Arsip Digital BAST & Dokumen SK')
@section('header_subtitle', 'Pusat penyimpanan digital berkas Berita Acara Serah Terima (BAST), SK Pelepasan Kawasan, dan Warkah Tanah 124 UPT')

@section('content')
<div class="space-y-6" x-data="{ openUploadModal: false }">

    <!-- 1. KARTU STATISTIK DOKUMEN & TOMBOL UNGGAH -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Arsip Digital</span>
            <div class="text-2xl font-black text-[#0B1849] mt-1 tabular-nums">{{ $stats['total_docs'] }} <span class="text-xs font-normal text-slate-500">Berkas</span></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Berita Acara (BAST)</span>
            <div class="text-2xl font-black text-[#124D1C] mt-1 tabular-nums">{{ $stats['total_bast'] }} <span class="text-xs font-normal text-slate-500">Berkas</span></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">SK Pelepasan Kawasan</span>
            <div class="text-2xl font-black text-[#E4B028] mt-1 tabular-nums">{{ $stats['total_sk'] }} <span class="text-xs font-normal text-slate-500">Berkas</span></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Buku Tanah / Warkah SHM</span>
            <div class="text-2xl font-black text-blue-700 mt-1 tabular-nums">{{ $stats['total_buku_tanah'] }} <span class="text-xs font-normal text-slate-500">Berkas</span></div>
        </div>
    </div>

    <!-- 2. FILTER & AKSI UNGGAH -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.documents.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari nomor register / nama file..." 
                   class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-[#124D1C]">
            
            <select name="type" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:ring-2 focus:ring-[#124D1C]">
                <option value="all">Semua Tipe Berkas</option>
                <option value="BAST" {{ request('type') == 'BAST' ? 'selected' : '' }}>BAST Fisik</option>
                <option value="SK_GUBERNUR" {{ request('type') == 'SK_GUBERNUR' ? 'selected' : '' }}>SK Gubernur</option>
                <option value="SK_MENTERI" {{ request('type') == 'SK_MENTERI' ? 'selected' : '' }}>SK Menteri</option>
                <option value="BUKU_TANAH" {{ request('type') == 'BUKU_TANAH' ? 'selected' : '' }}>Buku Tanah / SHM</option>
            </select>

            <button type="submit" class="bg-[#0B1849] text-white font-bold text-xs py-2 px-3.5 rounded-xl transition">
                Saring
            </button>
            <a href="{{ route('admin.documents.index') }}" class="text-xs text-slate-500 hover:text-slate-700 p-2">
                Reset
            </a>
        </form>

        <button @click="openUploadModal = true" 
                class="bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-xs py-2.5 px-4 rounded-xl transition shadow-md flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Unggah Berkas BAST Baru</span>
        </button>
    </div>

    <!-- 3. TABEL REPOSITORI DOKUMEN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0B1849] text-[#EBEDE3] font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Tipe Berkas</th>
                        <th class="py-3.5 px-4">No. Registrasi & Kabupaten</th>
                        <th class="py-3.5 px-4">Nomor Register Dokumen</th>
                        <th class="py-3.5 px-4">Nama Berkas Asli</th>
                        <th class="py-3.5 px-4 text-center">Ukuran</th>
                        <th class="py-3.5 px-4">Diunggah Oleh</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($documents as $doc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <span class="bg-[#0B1849]/10 text-[#0B1849] font-extrabold text-[10px] px-2 py-0.5 rounded">
                                    {{ $doc->document_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-extrabold text-slate-900">
                                    UPT-{{ str_pad($doc->uptLocation?->upt_number, 3, '0', STR_PAD_LEFT) }}: {{ $doc->uptLocation?->upt_name }}
                                </div>
                                <div class="text-[11px] text-slate-500">Kab. {{ $doc->uptLocation?->regency?->name }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700 font-semibold">
                                {{ $doc->document_number ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <span class="truncate max-w-xs">{{ $doc->file_name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-600">
                                {{ number_format($doc->file_size_kb) }} KB
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $doc->uploader?->name ?: 'Super Admin' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.documents.download', $doc->id) }}" 
                                       title="Unduh Berkas PDF"
                                       class="bg-[#124D1C] hover:bg-emerald-800 text-white p-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.documents.destroy', $doc->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip digital ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Dokumen" 
                                                class="bg-rose-50 hover:bg-rose-100 text-rose-600 p-1.5 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                Belum ada berkas fisik digital yang diunggah ke repositori.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $documents->links() }}
        </div>
    </div>

    <!-- 4. MODAL FORM UNGGAH BERKAS DIGITAL BAST -->
    <div x-show="openUploadModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
             @click.outside="openUploadModal = false">
            
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-[#0B1849]">
                    Unggah Berkas E-Arsip BAST / Dokumen SK
                </h3>
                <button @click="openUploadModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.documents.upload') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <!-- Pilih UPT Sasaran -->
                <div>
                    <label for="modal_upt" class="font-bold text-slate-700 block mb-1">
                        Pilih Lokasi UPT Sasaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="upt_location_id" id="modal_upt" required 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-[#124D1C]">
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
                    <label for="modal_type" class="font-bold text-slate-700 block mb-1">
                        Jenis Berkas Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <select name="document_type" id="modal_type" required 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-medium focus:ring-2 focus:ring-[#124D1C]">
                        <option value="BAST">Berita Acara Serah Terima (BAST Fisik)</option>
                        <option value="SK_GUBERNUR">SK Penetapan Gubernur</option>
                        <option value="SK_MENTERI">SK Pelepasan Kawasan Hutan Menteri</option>
                        <option value="BUKU_TANAH">Buku Tanah / Warkah Sertifikat SHM</option>
                    </select>
                </div>

                <!-- Nomor Register Resmi -->
                <div>
                    <label for="modal_number" class="font-bold text-slate-700 block mb-1">
                        Nomor Register Resmi Dokumen
                    </label>
                    <input type="text" name="document_number" id="modal_number" 
                           placeholder="Contoh: BAST/503/1977 atau SK.Menhut/245/1990"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-[#124D1C]">
                </div>

                <!-- File PDF -->
                <div>
                    <label for="modal_file" class="font-bold text-slate-700 block mb-1">
                        Berkas Digital Scan (Format PDF) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file" id="modal_file" accept="application/pdf" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs focus:ring-2 focus:ring-[#124D1C]">
                    <span class="text-[10px] text-slate-400 mt-1 block">Ukuran maksimal file: 20 MB.</span>
                </div>

                <!-- Aksi -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="openUploadModal = false" 
                            class="bg-slate-100 text-slate-700 font-bold px-3 py-2 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold px-4 py-2 rounded-xl shadow-md">
                        Simpan ke Repositori
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
