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
            <form method="POST" action="{{ route('admin.backup.create') }}">@csrf
                <button type="submit" class="atlas-button atlas-button--primary">Buat cadangan sekarang</button>
            </form>
            <form method="POST" action="{{ route('admin.backup.test-integrity') }}">@csrf
                <button type="submit" class="atlas-button">Periksa integritas data</button>
            </form>
        </div>
    </div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Nama berkas</th><th scope="col">Ukuran</th><th scope="col">Dibuat (WITA)</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($backups as $b)
            <tr>
                <td><span class="atlas-code">{{ $b['name'] }}</span></td>
                <td class="tabular-nums">{{ $b['size'] }}</td>
                <td class="tabular-nums">{{ $b['created_at'] }}</td>
                <td>
                    <div class="atlas-inline">
                        <a class="atlas-link" href="{{ route('admin.backup.download', $b['name']) }}">Unduh</a>
                        <form method="POST" action="{{ route('admin.backup.destroy', $b['name']) }}" onsubmit="return confirm('Hapus berkas cadangan {{ $b['name'] }}?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="atlas-link" style="color: var(--atlas-critical);">Hapus</button>
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
