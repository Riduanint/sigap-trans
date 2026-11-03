@extends('layouts.admin')
@section('atlas_subtitle', 'Status basis data, ukuran arsip, dan berkas cadangan yang dapat diunduh.')
@section('content')
<div class="atlas-metrics" aria-label="Ringkasan cadangan data">
    <div class="atlas-metric"><div><strong style="font-size: 19px;">{{ $pgVersion }}</strong><small>{{ $postgisVersion }}</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $dbSize }}</strong><small>Ukuran basis data terukur</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $storageSize }}</strong><small>Storage arsip dokumen BAST</small></div></div>
    <div class="atlas-metric"><div><strong>{{ count($backups) }}</strong><small>Berkas cadangan tersimpan · jadwal otomatis: belum dijadwalkan di aplikasi</small></div></div>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading">
        <h2>Berkas cadangan (.sql)</h2>
        <div class="atlas-inline">
            <form method="POST" action="{{ route('admin.backup.create') }}" data-confirm="Mulai proses snapshot dan pembuatan berkas cadangan database sekarang?" data-confirm-title="Konfirmasi Buat Cadangan" data-confirm-button="Ya, Buat Cadangan">@csrf
                <button type="submit" class="atlas-button atlas-button--primary">Buat cadangan sekarang</button>
            </form>
            <form method="POST" action="{{ route('admin.backup.test-integrity') }}">@csrf
                <button type="submit" class="atlas-button">Periksa integritas data</button>
            </form>
        </div>
    </div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Nama berkas</th><th scope="col">Ukuran</th><th scope="col">Dibuat (WITA)</th><th scope="col" style="text-align: center; width: 100px;">AKSI</th></tr></thead><tbody>
        @forelse($backups as $b)
            <tr>
                <td><span class="atlas-code">{{ $b['name'] }}</span></td>
                <td class="tabular-nums">{{ $b['size'] }}</td>
                <td class="tabular-nums">{{ $b['created_at'] }}</td>
                <td style="text-align: center;">
                    <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                        <a href="{{ route('admin.backup.download', $b['name']) }}" class="atlas-action-icon atlas-action-icon--download" title="Unduh Berkas Cadangan" aria-label="Unduh">
                            <x-admin.icon name="download" />
                        </a>
                        <form method="POST" action="{{ route('admin.backup.destroy', $b['name']) }}" data-confirm="Hapus berkas cadangan {{ $b['name'] }}? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus Cadangan" data-confirm-type="danger" data-confirm-button="Ya, Hapus Cadangan">
                            @csrf @method('DELETE')
                            <button type="submit" class="atlas-action-icon atlas-action-icon--delete" title="Hapus Berkas Cadangan" aria-label="Hapus">
                                <x-admin.icon name="trash" />
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="atlas-empty"><strong>Belum ada berkas cadangan.</strong><p>Buat cadangan pertama dengan tombol di atas — mencakup data UPT, batas wilayah, dan koordinat spasial.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
</section>
<p class="atlas-muted mt-4">Cadangan memuat snapshot PostGIS (WGS84 · SRID 4326) dan dapat dipulihkan ke server Diskominfo Kalsel.</p>
@endsection
