@props([
    'status' => 'clean',
    'mode' => 'short', // 'short' (Clean, Warning, Kritis), 'full' (Clean & Clear, Waspada / Monitoring, Kritis / Prioritas Mediasi)
    'size' => 'xs',    // 'xs', 'sm', 'md'
    'dot' => true,     // Tampilkan bulatan warna emoji 🟢/🟡/🔴
    'pulse' => null,   // null = otomatis pulse jika critical
])

@php
    $normalized = strtolower(trim((string)$status));
    if ($normalized === 'aman' || $normalized === 'clear') {
        $normalized = 'clean';
    } elseif ($normalized === 'waspada' || $normalized === 'monitoring') {
        $normalized = 'warning';
    } elseif ($normalized === 'kasus kritis' || $normalized === 'prioritas kritis' || $normalized === 'prioritas mediasi') {
        $normalized = 'critical';
    }

    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-[10px]',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-xs',
    ][$size] ?? 'px-2 py-0.5 text-[10px]';

    $pulseActive = $pulse ?? ($normalized === 'critical');

    switch ($normalized) {
        case 'warning':
            $badgeColor = 'bg-amber-50 text-amber-800 border-amber-200';
            $dotEmoji = '🟡';
            $label = ($mode === 'full') ? 'Waspada / Monitoring' : 'Warning';
            break;

        case 'critical':
            $badgeColor = 'bg-rose-50 text-rose-800 border-rose-200';
            $dotEmoji = '🔴';
            $label = ($mode === 'full') ? 'Kritis / Prioritas Mediasi' : 'Kritis';
            break;

        case 'clean':
        default:
            $badgeColor = 'bg-emerald-50 text-emerald-800 border-emerald-200';
            $dotEmoji = '🟢';
            $label = ($mode === 'full') ? 'Clean & Clear' : 'Clean';
            break;
    }
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1 font-bold rounded-full border shadow-2xs {$badgeColor} {$sizeClasses} " . ($pulseActive ? 'animate-pulse' : '')
]) }}>
    @if($dot)
        <span class="text-[9px] leading-none shrink-0 select-none">{{ $dotEmoji }}</span>
    @endif
    <span class="truncate">{{ $label }}</span>
</span>
