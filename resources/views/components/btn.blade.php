@props([
    'variant' => 'ink', // ink, clean, warning, critical, secondary, ghost
    'size' => 'sm', // xs, sm, md
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $sizeClasses = [
        'xs' => 'px-3 py-1.5 text-xs gap-1.5 rounded-lg',
        'sm' => 'px-3.5 py-2 text-xs gap-2 rounded-xl',
        'md' => 'px-4 py-2.5 text-sm gap-2.5 rounded-xl',
    ][$size] ?? 'px-3.5 py-2 text-xs gap-2 rounded-xl';

    $variants = [
        // Primary Abyssal Button (Midnight Ink with warm canvas text)
        'abyssal' => 'bg-[#1B2632] text-[#EEE9DF] hover:bg-[#2C3B4D] border border-[#1B2632] shadow-ambient-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] active:translate-y-0.5',
        // High-Visibility Burning Flame Button
        'flame' => 'bg-[#FFB162] text-[#1B2632] hover:bg-[#f39f4a] border border-[#FFB162] shadow-ambient-xs font-extrabold shadow-[inset_0_1px_0_rgba(255,255,255,0.3)] active:translate-y-0.5',
        // Warm Terracotta Truffle Trouble Button
        'truffle' => 'bg-[#A35139] text-white hover:bg-[#8e422c] border border-[#8e422c] shadow-ambient-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.18)] active:translate-y-0.5',
        // Slate Navy Button
        'slate' => 'bg-[#2C3B4D] text-white hover:bg-[#1B2632] border border-[#2C3B4D] shadow-ambient-xs active:translate-y-0.5',
        // Secondary Oatmeal / Warm Outline Button
        'secondary' => 'bg-white text-[#1B2632] hover:bg-[#EEE9DF] border border-[#C9C1B1] shadow-ambient-xs hover:border-[#1B2632]/30 active:translate-y-0.5',
        'oatmeal' => 'bg-white text-[#1B2632] hover:bg-[#EEE9DF] border border-[#C9C1B1] shadow-ambient-xs hover:border-[#1B2632]/30 active:translate-y-0.5',
        // Ghost Button
        'ghost' => 'bg-transparent text-[#1B2632] hover:bg-[#C9C1B1]/25 hover:text-[#1B2632] border border-transparent',
        // Heritage Clean Green
        'clean' => 'bg-status-clean text-white hover:bg-[#153D31] border border-[#12362B] shadow-ambient-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.2)] active:translate-y-0.5',
    ];

    // Aliases
    $variants['ink'] = $variants['abyssal'];
    $variants['warning'] = $variants['flame'];
    $variants['critical'] = $variants['truffle'];

    $btnClasses = "inline-flex items-center justify-center font-bold tracking-tight transition-all duration-150 cursor-pointer select-none {$sizeClasses} " . ($variants[$variant] ?? $variants['ink']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $btnClasses]) }}>
        @if($icon)
            <span class="shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $btnClasses]) }}>
        @if($icon)
            <span class="shrink-0">{!! $icon !!}</span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
