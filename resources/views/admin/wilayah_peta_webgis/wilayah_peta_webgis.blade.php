@extends('layouts.admin')
@section('atlas_subtitle', 'Kontrol tampil kabupaten dan warna batas poligon di peta publik WebGIS.')
@section('content')
@php $totalUpt = $regencies->sum('upt_locations_count'); @endphp
<div class="atlas-metrics" aria-label="Ringkasan publikasi wilayah">
    <div class="atlas-metric"><div><strong>{{ $stats['visible_regencies'] }}<span>/ {{ $stats['total_regencies'] }}</span></strong><small>Kabupaten tampil di peta publik · {{ $stats['hidden_regencies'] }} disembunyikan</small></div></div>
    <div class="atlas-metric"><div><strong id="stat-visible-upts">{{ $stats['visible_upts'] }}<span>/ {{ $totalUpt }}</span></strong><small>UPT ikut tampil pada peta</small></div></div>
    <div class="atlas-metric">
        <form method="POST" action="{{ route('admin.regencies.bulk-visibility') }}">@csrf
            <input type="hidden" name="action" value="show_all">
            <button type="submit" class="atlas-button">Tampilkan semua</button>
        </form>
        <form method="POST" action="{{ route('admin.regencies.bulk-visibility') }}" onsubmit="return confirm('Sembunyikan semua wilayah dari peta publik?');">@csrf
            <input type="hidden" name="action" value="hide_all">
            <button type="submit" class="atlas-button">Sembunyikan semua</button>
        </form>
    </div>
</div>

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Daftar kabupaten</h2><span class="atlas-muted">Perubahan tersimpan langsung tanpa memuat ulang</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Kabupaten</th><th scope="col">UPT</th><th scope="col">Kondisi lahan</th><th scope="col">Tampil di peta</th><th scope="col">Warna poligon</th><th scope="col">Pratinjau</th></tr></thead><tbody>
        @foreach($regencies as $reg)
            @php
                $cleanCount = $reg->uptLocations->where('issue_status', 'clean')->count();
                $warningCount = $reg->uptLocations->where('issue_status', 'warning')->count();
                $criticalCount = $reg->uptLocations->where('issue_status', 'critical')->count();
                $color = $reg->map_color ?: '#8b5cf6';
            @endphp
            <tr class="regency-row" data-id="{{ $reg->id }}" data-visible="{{ $reg->is_visible ? '1' : '0' }}">
                <td><div class="atlas-identity"><strong>{{ $reg->name }}</strong><span class="atlas-muted">Ibukota: {{ $reg->capital_city ?: 'Belum tercatat' }}</span></div></td>
                <td class="tabular-nums">{{ $reg->upt_locations_count }}</td>
                <td><span class="atlas-muted">{{ $cleanCount }} clean · {{ $warningCount }} monitoring · {{ $criticalCount }} kritis</span></td>
                <td>
                    <button type="button" class="atlas-button regency-toggle" role="switch" aria-checked="{{ $reg->is_visible ? 'true' : 'false' }}" data-id="{{ $reg->id }}" data-next="{{ $reg->is_visible ? 'false' : 'true' }}">{{ $reg->is_visible ? 'Tampil' : 'Disembunyikan' }}</button>
                </td>
                <td>
                    <div class="atlas-inline">
                        <input type="color" class="regency-color" value="{{ $color }}" data-id="{{ $reg->id }}" style="width: 34px; height: 34px; padding: 2px; border: 1px solid var(--atlas-line); border-radius: 5px; cursor: pointer;" aria-label="Warna poligon {{ $reg->name }}">
                        <button type="button" class="atlas-button regency-color-save" data-id="{{ $reg->id }}">Simpan warna</button>
                    </div>
                </td>
                <td><a class="atlas-link" href="{{ route('home') }}?regency={{ $reg->id }}" target="_blank" rel="noopener">Buka di peta ↗</a></td>
            </tr>
        @endforeach
    </tbody></table></div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function atlasToast(message, ok = true) {
        let toast = document.getElementById('atlas-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'atlas-toast';
            toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:80;padding:12px 18px;border-radius:5px;font-size:14px;font-weight:600;color:#fff;box-shadow:0 8px 24px #24374633;transition:opacity .2s;';
            document.body.appendChild(toast);
        }
        toast.style.background = ok ? 'var(--atlas-clean, #287451)' : 'var(--atlas-critical, #B43D3D)';
        toast.textContent = message;
        toast.style.opacity = '1';
        clearTimeout(toast._t);
        toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 3200);
    }

    function refreshVisibleStats() {
        let visible = 0, upts = 0;
        document.querySelectorAll('.regency-row').forEach(row => {
            if (row.dataset.visible === '1') {
                visible++;
                upts += parseInt(row.querySelector('.tabular-nums').textContent, 10) || 0;
            }
        });
        const el = document.getElementById('stat-visible-upts');
        if (el) el.firstChild.textContent = upts;
    }

    document.querySelectorAll('.regency-toggle').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const next = button.dataset.next === 'true';
            const previous = !next;
            button.disabled = true;
            try {
                const res = await fetch(`/admin/regencies/${id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ _token: csrfToken }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal mengubah visibilitas');
                button.textContent = next ? 'Tampil' : 'Disembunyikan';
                button.setAttribute('aria-checked', next ? 'true' : 'false');
                button.dataset.next = next ? 'false' : 'true';
                const row = button.closest('.regency-row');
                row.dataset.visible = next ? '1' : '0';
                refreshVisibleStats();
                atlasToast(data.message);
            } catch (err) {
                atlasToast(err.message || 'Gagal mengubah visibilitas wilayah.', false);
            } finally {
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('.regency-color-save').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const color = document.querySelector(`.regency-color[data-id="${id}"]`).value;
            button.disabled = true;
            button.textContent = 'Menyimpan…';
            try {
                const res = await fetch(`/admin/regencies/${id}`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ _token: csrfToken, map_color: color }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal menyimpan warna');
                atlasToast(data.message);
            } catch (err) {
                atlasToast('Gagal memperbarui warna poligon.', false);
            } finally {
                button.disabled = false;
                button.textContent = 'Simpan warna';
            }
        });
    });
});
</script>
@endpush
@endsection
