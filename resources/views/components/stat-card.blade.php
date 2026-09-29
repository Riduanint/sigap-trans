@props([
    'label' => '',
    'title' => null,
    'value' => '0',
    'unit' => '',
    'subtext' => null,
    'subtitle' => null,
    'footerText' => null,
    'variant' => 'ink', // ink, clean, warning, critical, sand, tan, abyssal, slate, flame, truffle, oatmeal
    'icon' => null,
    'badge' => null,
    'link' => null,
])

@php
    $displayLabel = $label ?: ($title ?? '');
    $displaySubtext = $subtext ?: ($subtitle ?? null);

    // Warna & border mapping selaras MP072 Architectural Palette: #EEE9DF, #C9C1B1, #2C3B4D, #FFB162, #A35139, #1B2632
    $variantStyles = [
        'abyssal' => [
            'card_border' => 'border-[#C9C1B1]/60 hover:border-[#1B2632]/40',
            'top_accent' => 'bg-[#1B2632]',
            'icon_bg' => 'bg-[#1B2632]/10 text-[#1B2632] border border-[#1B2632]/20',
            'value_color' => 'text-[#1B2632]',
            'unit_color' => 'text-[#1B2632]/60',
        ],
        'slate' => [
            'card_border' => 'border-[#C9C1B1]/60 hover:border-[#2C3B4D]/40',
            'top_accent' => 'bg-[#2C3B4D]',
            'icon_bg' => 'bg-[#2C3B4D]/10 text-[#2C3B4D] border border-[#2C3B4D]/25',
            'value_color' => 'text-[#2C3B4D]',
            'unit_color' => 'text-[#2C3B4D]/60',
        ],
        'flame' => [
            'card_border' => 'border-[#FFB162]/60 hover:border-[#FFB162]',
            'top_accent' => 'bg-[#FFB162]',
            'icon_bg' => 'bg-[#FFB162]/20 text-[#8F4E0A] border border-[#FFB162]/40',
            'value_color' => 'text-[#8F4E0A]',
            'unit_color' => 'text-[#8F4E0A]/70',
        ],
        'truffle' => [
            'card_border' => 'border-[#A35139]/40 hover:border-[#A35139]',
            'top_accent' => 'bg-[#A35139]',
            'icon_bg' => 'bg-[#A35139]/15 text-[#A35139] border border-[#A35139]/30',
            'value_color' => 'text-[#A35139]',
            'unit_color' => 'text-[#A35139]/70',
        ],
        'oatmeal' => [
            'card_border' => 'border-[#C9C1B1] hover:border-[#2C3B4D]/30',
            'top_accent' => 'bg-[#C9C1B1]',
            'icon_bg' => 'bg-[#C9C1B1]/30 text-[#1B2632] border border-[#C9C1B1]/50',
            'value_color' => 'text-[#1B2632]',
            'unit_color' => 'text-[#1B2632]/60',
        ],
        'clean' => [
            'card_border' => 'border-status-clean-border/60 hover:border-status-clean-border',
            'top_accent' => 'bg-status-clean',
            'icon_bg' => 'bg-status-clean-bg text-status-clean border border-status-clean-border/50',
            'value_color' => 'text-status-clean',
            'unit_color' => 'text-status-clean/70',
        ],
    ];

    // Aliases
    $variantStyles['ink'] = $variantStyles['abyssal'];
    $variantStyles['sand'] = $variantStyles['oatmeal'];
    $variantStyles['tan'] = $variantStyles['flame'];
    $variantStyles['warning'] = $variantStyles['flame'];
    $variantStyles['critical'] = $variantStyles['truffle'];

    $style = $variantStyles[$variant] ?? $variantStyles['abyssal'];
@endphp

<div {{ $attributes->merge(['class' => "relative bg-white rounded-2xl p-5 border {$style['card_border']} shadow-ambient-xs hover:shadow-ambient transition-all duration-200 flex flex-col justify-between overflow-hidden group"]) }}>
    <!-- Hairline Top Accent bar -->
    <div class="absolute top-0 left-0 right-0 h-[3px] {{ $style['top_accent'] }} opacity-90"></div>

    <!-- Header label & icon wrapper -->
    <div class="flex items-start justify-between gap-3 pt-1">
        <div class="space-y-0.5">
            @if($displayLabel)
                <span class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#2C3B4D]/70 block">
                    {{ $displayLabel }}
                </span>
            @endif
            @if($badge)
                <span class="inline-block mt-0.5">{{ $badge }}</span>
            @endif
        </div>

        @if($icon || isset($customIcon))
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $style['icon_bg'] }} shadow-xs transition-transform duration-200 group-hover:scale-105">
                @if(isset($customIcon))
                    {{ $customIcon }}
                @elseif($icon)
                    {!! $icon !!}
                @endif
            </div>
        @endif
    </div>

    <!-- Main Metric Value with Tabular Nums -->
    <div class="mt-4 pt-1">
        <div class="text-2xl sm:text-3xl font-extrabold tracking-tight tabular-nums {{ $style['value_color'] }} flex items-baseline gap-1.5">
            <span>{{ $value }}</span>
            @if($unit)
                <span class="text-xs font-semibold {{ $style['unit_color'] }} uppercase tracking-wider">{{ $unit }}</span>
            @endif
        </div>

        @if($displaySubtext)
            <div class="text-xs text-[#2C3B4D]/75 mt-1.5 font-medium flex items-center gap-1.5">
                {{ $displaySubtext }}
            </div>
        @endif

        @if(isset($footer))
            <div class="mt-3 pt-2.5 border-t border-[#C9C1B1]/40 text-[11px]">
                {{ $footer }}
            </div>
        @elseif($footerText)
            <div class="mt-3 pt-2.5 border-t border-[#C9C1B1]/40 text-[11px] flex items-center justify-between">
                @if($link)
                    <a href="{{ $link }}" class="text-[#A35139] hover:underline font-bold flex items-center gap-1">
                        <span>{{ $footerText }}</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <span class="text-[#2C3B4D]/70">{{ $footerText }}</span>
                @endif
            </div>
        @endif
    </div>
</div>
