@props([
    'variant' => 'ink', // clean, warning, critical, ink, sand, tan, neutral
    'dot' => false,
    'size' => 'xs', // xs, sm
])

@php
    $sizeClasses = [
        'xs' => 'px-2.5 py-0.5 text-[10px]',
        'sm' => 'px-3 py-1 text-xs',
    ][$size] ?? 'px-2.5 py-0.5 text-[10px]';

    $variants = [
        'abyssal' => 'bg-[#1B2632]/10 text-[#1B2632] border border-[#1B2632]/25 font-extrabold',
        'slate' => 'bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25 font-extrabold',
        'flame' => 'bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/40 font-extrabold',
        'truffle' => 'bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30 font-extrabold',
        'oatmeal' => 'bg-[#C9C1B1]/30 text-[#3C3831] border border-[#C9C1B1]/50 font-extrabold',
        'clean' => 'bg-status-clean-bg text-status-clean border border-status-clean-border/60 font-extrabold',
        'neutral' => 'bg-[#EEE9DF] text-[#1B2632]/80 border border-[#C9C1B1]/40 font-bold',
    ];

    // Aliases
    $variants['ink'] = $variants['abyssal'];
    $variants['warning'] = $variants['flame'];
    $variants['critical'] = $variants['truffle'];
    $variants['sand'] = $variants['oatmeal'];
    $variants['tan'] = $variants['flame'];

    $dotColors = [
        'abyssal' => 'bg-[#1B2632]',
        'slate' => 'bg-[#2C3B4D]',
        'flame' => 'bg-[#FFB162]',
        'truffle' => 'bg-[#A35139]',
        'oatmeal' => 'bg-[#C9C1B1]',
        'clean' => 'bg-status-clean',
        'neutral' => 'bg-[#C9C1B1]',
    ];

    $dotColors['ink'] = $dotColors['abyssal'];
    $dotColors['warning'] = $dotColors['flame'];
    $dotColors['critical'] = $dotColors['truffle'];
    $dotColors['sand'] = $dotColors['oatmeal'];
    $dotColors['tan'] = $dotColors['flame'];

    $badgeClass = $variants[$variant] ?? $variants['ink'];
    $dotClass = $dotColors[$variant] ?? $dotColors['ink'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full {$sizeClasses} {$badgeClass} tracking-wider uppercase transition-colors select-none"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} shrink-0"></span>
    @endif
    <span>{{ $slot }}</span>
</span>
