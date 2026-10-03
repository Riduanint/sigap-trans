@extends('layouts.admin')
@section('atlas_subtitle', 'Periksa usulan kabupaten dan tindak lanjuti lokasi yang memerlukan perhatian.')
@section('content')
<form method="GET" action="{{ route('admin.dashboard') }}" class="atlas-inline" style="margin-bottom: 20px;">
    <label for="dashboard-regency" class="atlas-muted" style="font-size: 13px; font-weight: 500;">Wilayah kerja</label>
    <select name="regency_id" id="dashboard-regency" class="rounded border-slate-300 text-sm" style="min-height: 38px; border: 1px solid var(--atlas-line); background-color: #fff; color: var(--atlas-ink); border-radius: 6px; padding: 6px 32px 6px 12px;" onchange="this.form.submit()">
        <option value="">Semua kabupaten</option>
        @foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach
    </select>
    <noscript><button class="atlas-button">Terapkan</button></noscript>
</form>
<div class="atlas-metrics" aria-label="Ringkasan pekerjaan">
    <a class="atlas-metric" href="{{ route('admin.verification.index', array_merge(request()->only('regency_id'), ['status' => 'pending'])) }}">
        <div>
            <strong>{{ $pendingApprovals }}</strong><span>Menunggu verifikasi</span>
            <small>Usulan yang belum diproses</small>
        </div>
    </a>
    <a class="atlas-metric" href="{{ route('admin.upt.index', array_merge(request()->only('regency_id'), ['status' => 'critical'])) }}">
        <div>
            <strong>{{ $priorityCases->count() }}</strong><span>Prioritas mediasi</span>
            <small>Lokasi berstatus kritis</small>
        </div>
    </a>
    <a class="atlas-metric" href="{{ route('admin.upt.index', request()->only('regency_id')) }}">
        <div>
            <strong>{{ $locations->count() }}</strong><span>UPT terdaftar</span>
            <small>Termasuk wilayah belum dipublikasikan</small>
        </div>
    </a>
</div>
<div class="atlas-stack">
    <div class="atlas-dashboard-grid">
        <section class="atlas-panel" aria-labelledby="queue-heading">
            <div class="atlas-panel-heading"><div><h2 id="queue-heading">Antrean verifikasi</h2><p class="atlas-muted">Usulan paling lama menunggu ditampilkan lebih dulu.</p></div><a href="{{ route('admin.verification.index', array_merge(request()->only('regency_id'), ['status' => 'pending'])) }}">Lihat semua →</a></div>
            @forelse($pendingRequests as $proposal)
                <article class="atlas-request">
                    <div>
                        <x-admin.upt-identity :upt="$proposal->uptLocation" />
                        <div class="atlas-request-meta"><span>{{ $proposal->user?->name ?? 'Pengguna tidak tersedia' }}</span><span>{{ match($proposal->request_type) { 'REGISTRY_SYNC' => 'Registri warga', 'LEGAL_ISSUE' => 'Permasalahan lahan', 'DATA_UPDATE' => 'Perubahan data', default => 'Dokumen pendukung' } }}</span><time datetime="{{ $proposal->created_at->toIso8601String() }}" title="{{ $proposal->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA">{{ $proposal->created_at->locale('id')->diffForHumans() }}</time></div>
                    </div>
                    <div class="atlas-request-actions"><button type="button" class="atlas-icon-button" data-atlas-location="{{ $proposal->uptLocation->id }}" aria-label="Lihat lokasi {{ $proposal->uptLocation->upt_name }}"><x-admin.icon name="pin" /></button><a class="atlas-button atlas-button--primary" href="{{ route('admin.verification.show', $proposal->id) }}">Periksa <x-admin.icon name="arrow" /></a></div>
                </article>
            @empty
                <div class="atlas-empty"><strong>Tidak ada usulan menunggu verifikasi.</strong><p>Pengajuan baru dari kabupaten akan muncul di sini.</p><a class="atlas-link" href="{{ route('admin.verification.index') }}">Lihat riwayat pengajuan →</a></div>
            @endforelse
        </section>
        <section class="atlas-panel" aria-labelledby="map-heading">
            <div class="atlas-panel-heading"><h2 id="map-heading">Konteks lokasi</h2><x-admin.icon name="map" /></div>
            <div id="atlas-work-map" class="atlas-map" aria-label="Peta lokasi UPT"></div>
            <div id="atlas-map-status" class="atlas-map-status" role="status">Pilih lokasi dari antrean atau titik pada peta.</div>
            <div class="atlas-map-caption"><strong id="atlas-map-name">Sebaran UPT Kalimantan Selatan</strong><p id="atlas-map-location" class="atlas-muted">{{ $locations->whereNotNull('latitude')->whereNotNull('longitude')->count() }} dari {{ $locations->count() }} UPT memiliki koordinat.</p><a id="atlas-map-detail" hidden href="#">Buka detail UPT →</a></div>
            <div class="atlas-map-legend"><span><i style="background:#287451"></i>Clean & Clear</span><span><i style="background:#97620b"></i>Monitoring</span><span><i style="background:#b43d3d"></i>Prioritas</span></div>
            <script type="application/json" id="atlas-map-data">{!! json_encode($mapLocations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        </section>
    </div>
    <section class="atlas-panel">
        <div class="atlas-panel-heading"><div><h2>UPT yang perlu perhatian</h2><p class="atlas-muted">Lokasi berstatus kritis untuk koordinasi dan mediasi pertanahan.</p></div><a href="{{ route('admin.upt.index', array_merge(request()->only('regency_id'), ['status' => 'critical'])) }}">Buka daftar →</a></div>
        <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Lokasi</th><th scope="col">Catatan permasalahan</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
            @forelse($priorityCases->take(5) as $upt)
                <tr><td><x-admin.upt-identity :upt="$upt" /></td><td class="atlas-reading">{{ $upt->issue_note ?: 'Catatan permasalahan belum tersedia.' }}</td><td><x-admin.status :status="$upt->issue_status" /></td><td><button class="atlas-button" type="button" data-atlas-location="{{ $upt->id }}"><x-admin.icon name="pin" /> Lihat lokasi</button></td></tr>
            @empty
                <tr><td colspan="4"><div class="atlas-empty">Tidak ada UPT berstatus kritis pada wilayah ini.</div></td></tr>
            @endforelse
        </tbody></table></div>
    </section>
    <section class="atlas-panel">
        <div class="atlas-panel-heading"><div><h2>Aktivitas terbaru</h2><p class="atlas-muted">Rekam aktivitas seluruh wilayah.</p></div><a href="{{ route('admin.audit-logs.index') }}">Buka log aktivitas →</a></div>
        <ul class="atlas-activity">
            @forelse($recentLogs as $log)
                <li><x-admin.icon name="history" /><div><strong>{{ $log->user?->name ?? 'Sistem' }}</strong><span> · {{ match($log->action) { 'APPROVE_CHANGE_REQUEST' => 'Menyetujui perubahan', 'REJECT_CHANGE_REQUEST' => 'Menolak usulan', 'CREATE_UPT_LOCATION' => 'Menambahkan UPT', 'UPDATE_UPT_LOCATION' => 'Memperbarui UPT', 'MANUAL_BACKUP_DB' => 'Membuat cadangan data', default => str($log->action)->replace('_', ' ')->lower()->ucfirst() } }}</span><p class="atlas-muted">{{ $log->details['upt_name'] ?? ($log->target_table . ' · ' . $log->target_id) }}</p></div><time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->timezone('Asia/Makassar')->format('d M, H:i') }} WITA</time></li>
            @empty
                <li>Belum ada aktivitas tercatat.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
