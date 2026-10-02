@props(['status' => null])
@php
    [$tone, $label] = match($status) {
        'clean' => ['clean', 'Clean & Clear'],
        'warning' => ['warning', 'Monitoring'],
        'critical' => ['critical', 'Prioritas mediasi'],
        'pending' => ['warning', 'Menunggu'],
        'approved' => ['clean', 'Disetujui'],
        'rejected' => ['critical', 'Ditolak'],
        default => ['neutral', 'Belum tercatat'],
    };
@endphp
<span class="atlas-status atlas-status--{{ $tone }}"><span aria-hidden="true"></span>{{ $label }}</span>
