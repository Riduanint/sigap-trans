@extends('layouts.admin')
@section('atlas_subtitle', 'Rekam jejak perubahan data, persetujuan verifikasi, unggahan arsip, dan ekspor.')
@section('content')
@php $activeFilters = collect(request()->only('action', 'user_id', 'start_date', 'end_date', 'search'))->filter(fn ($value) => filled($value)); @endphp
<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.audit-logs.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="al-search">Cari log</label><input id="al-search" name="search" value="{{ request('search') }}" placeholder="Aksi, tabel, ID target, atau IP" type="search"></div>
            <div class="atlas-field"><label for="al-action">Aksi</label><select id="al-action" name="action"><option value="">Semua aksi</option>@foreach($availableActions as $act)<option value="{{ $act }}" @selected(request('action') === $act)>{{ $act }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="al-user">Pelaku</label><select id="al-user" name="user_id"><option value="">Semua pengguna</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="al-start">Dari tanggal</label><input id="al-start" type="date" name="start_date" value="{{ request('start_date') }}"></div>
            <div class="atlas-field"><label for="al-end">s.d. tanggal</label><input id="al-end" type="date" name="end_date" value="{{ request('end_date') }}"></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.audit-logs.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'user_id' ? $users->firstWhere('id', $value)?->name : $value }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.audit-logs.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($logs->total(), 0, ',', '.') }} rekam jejak</h2><span class="atlas-muted">Terbaru lebih dulu · waktu WITA</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Waktu</th><th scope="col">Pelaku</th><th scope="col">Aksi</th><th scope="col">Sasaran</th><th scope="col">IP</th><th scope="col">Rincian</th></tr></thead><tbody>
        @forelse($logs as $log)
            <tr>
                <td class="tabular-nums">{{ $log->created_at?->timezone('Asia/Makassar')->format('d M Y H:i') ?? '—' }}</td>
                <td><div class="atlas-identity"><strong>{{ $log->user->name ?? 'Proses sistem' }}</strong><span class="atlas-muted">{{ $log->user->role ?? 'system' }}</span></div></td>
                <td><span class="atlas-code">{{ $log->action }}</span></td>
                <td>{{ $log->target_table }}<br><span class="atlas-muted">ID: {{ $log->target_id ?: '—' }}</span></td>
                <td><span class="atlas-code">{{ $log->ip_address }}</span></td>
                <td>@if(!empty($log->details))<button type="button" class="atlas-link" data-atlas-dialog-open="atlas-audit-dialog" data-atlas-dialog-focus="#atlas-audit-json" onclick="fillAuditDetails(this)" data-log="{{ json_encode($log->details) }}">Lihat</button>@else<span class="atlas-muted">—</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="atlas-empty"><strong>Belum ada rekam jejak yang cocok.</strong><p>Ubah rentang waktu atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.audit-logs.index') }}">Tampilkan seluruh log →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} rekam</span>{{ $logs->links('admin.partials.pagination') }}</div>
</section>

{{-- Dialog rincian log --}}
<div class="atlas-dialog" id="atlas-audit-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-audit-dialog-title">
    <div class="atlas-dialog__panel" style="max-width: 640px;">
        <div class="atlas-dialog__head">
            <div><h3 id="atlas-audit-dialog-title">Rincian perubahan</h3><p class="atlas-code" id="atlas-audit-dialog-meta"></p></div>
            <button type="button" class="atlas-icon-button" data-atlas-dialog-close aria-label="Tutup dialog"><x-admin.icon name="close" /></button>
        </div>
        <div class="atlas-dialog__body">
            <pre id="atlas-audit-json" style="background: var(--atlas-canvas); border: 1px solid var(--atlas-line); border-radius: 5px; padding: 14px; font-size: 13px; overflow-x: auto; max-height: 380px; margin: 0;"></pre>
        </div>
        <div class="atlas-dialog__foot"><button type="button" class="atlas-button" data-atlas-dialog-close>Tutup</button></div>
    </div>
</div>

@push('scripts')
<script>
function fillAuditDetails(button) {
    const log = JSON.parse(button.dataset.log);
    document.getElementById('atlas-audit-json').textContent = JSON.stringify(log, null, 2);
}
</script>
@endpush
@endsection
