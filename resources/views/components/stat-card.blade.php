@props([
    'label' => '',
    'title' => null,
    'value' => '0',
    'unit' => '',
    'subtext' => null,
    'subtitle' => null,
    'footerText' => null,
    'icon' => null,
    'badge' => null,
    'link' => null,
])

@php
    $displayLabel = $label ?: ($title ?? '');
    $displaySubtext = $subtext ?: ($subtitle ?? null);
@endphp

{{-- Kartu metrik Atlas: satu-satunya varian, dipakai semua peran di shell Atlas. --}}
<div {{ $attributes->class(['atlas-stat-card']) }}>
    <p class="atlas-stat-card-label">{{ $displayLabel }}</p>
    <div class="atlas-stat-card-value">{{ $value }} @if($unit)<small>{{ $unit }}</small>@endif</div>
    @if($displaySubtext)<div class="atlas-muted">{{ $displaySubtext }}</div>@endif
    @if(isset($footer))<div class="atlas-stat-card-footer">{{ $footer }}</div>
    @elseif($footerText)<div class="atlas-stat-card-footer">@if($link)<a class="atlas-link" href="{{ $link }}">{{ $footerText }} →</a>@else{{ $footerText }}@endif</div>@endif
</div>
