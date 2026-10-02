@php
    $exports = match (request()->route()?->getName()) {
        'admin.dashboard' => ['admin.dashboard.pdf' => 'Ringkasan PDF'],
        'admin.upt.index' => ['admin.upt.pdf' => 'Daftar UPT · PDF'],
        'admin.placements.index' => ['admin.placements.pdf' => 'Penempatan · PDF', 'admin.placements.export' => 'Penempatan · CSV'],
        'admin.handovers.index' => ['admin.handovers.pdf' => 'Serah terima · PDF', 'admin.handovers.export' => 'Serah terima · CSV'],
        'admin.sertifikat-tanah.index' => ['admin.sertifikat-tanah.pdf' => 'Pertanahan · PDF', 'admin.sertifikat-tanah.export' => 'Pertanahan · CSV'],
        'admin.documents.index' => ['admin.documents.pdf' => 'Daftar dokumen · PDF'],
        'admin.regencies.index' => ['admin.regencies.pdf' => 'Wilayah · PDF'],
        'admin.analitik.index' => ['admin.analitik.pdf' => 'Analitik · PDF', 'admin.reports.excel' => 'Rekap provinsi · Excel'],
        'admin.audit-logs.index' => ['admin.audit-logs.pdf' => 'Log aktivitas · PDF', 'admin.audit-logs.export' => 'Log aktivitas · CSV'],
        default => [],
    };
@endphp
@if($exports)
<details class="atlas-export" @keydown.escape.stop="$el.removeAttribute('open')" @click.outside="$el.removeAttribute('open')">
    <summary class="atlas-button"><x-admin.icon name="download" /> Ekspor</summary>
    <div class="atlas-export-menu">
        @foreach($exports as $exportRoute => $label)
            <a href="{{ route($exportRoute, $exportRoute === 'admin.reports.excel' ? [] : request()->except('page')) }}">{{ $label }}</a>
        @endforeach
    </div>
</details>
@endif
