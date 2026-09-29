@props([
    'headerStyle' => 'ink', // 'ink' (Dark Deep Classic Navy) atau 'sand' (Warm Parchment)
    'striped' => false,
])

@php
    $theadClasses = match($headerStyle) {
        'slate' => 'bg-[#2C3B4D] text-[#EEE9DF] border-b border-[#2C3B4D]',
        'oatmeal', 'sand' => 'bg-[#C9C1B1]/40 text-[#1B2632] border-b border-[#C9C1B1]/60',
        default => 'bg-[#1B2632] text-[#EEE9DF] border-b border-[#121A23]',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#C9C1B1]/60 shadow-ambient-xs overflow-hidden flex flex-col justify-between']) }}>
    @if(isset($toolbar))
        <div class="p-4 sm:p-5 border-b border-[#C9C1B1]/40 bg-white">
            {{ $toolbar }}
        </div>
    @endif

    <div class="overflow-x-auto w-full">
        <table class="w-full text-left text-xs border-collapse">
            @if(isset($thead))
                <thead class="{{ $theadClasses }} text-[10px] font-extrabold uppercase tracking-wider select-none">
                    {{ $thead }}
                </thead>
            @endif

            <tbody class="divide-y divide-[#C9C1B1]/35 font-medium text-[#1B2632]/90">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($empty) && (!isset($hasData) || !$hasData))
        <div class="p-12 text-center text-[#1B2632]/50 bg-[#EEE9DF]/30">
            {{ $empty }}
        </div>
    @endif

    @if(isset($pagination))
        <div class="p-4 sm:p-5 border-t border-[#C9C1B1]/40 bg-white/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#1B2632]/65">
            {{ $pagination }}
        </div>
    @endif
</div>
