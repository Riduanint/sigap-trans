@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumb' => [],
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-[#C9C1B1]/60 shadow-ambient-xs']) }}>
    <div>
        @if(!empty($breadcrumb))
            <nav class="flex items-center gap-2 text-xs font-semibold text-[#1B2632]/50 mb-1.5" aria-label="Breadcrumb">
                @foreach($breadcrumb as $name => $url)
                    @if(!$loop->last)
                        <a href="{{ $url }}" class="hover:text-[#1B2632] transition-colors duration-150">{{ $name }}</a>
                        <span class="text-[#C9C1B1] text-[10px]">/</span>
                    @else
                        <span class="text-[#1B2632] font-bold">{{ $name }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-xl sm:text-2xl font-black text-[#1B2632] tracking-tight flex items-center gap-3">
            @if($icon || isset($customIcon))
                <span class="w-9 h-9 rounded-xl bg-[#C9C1B1]/25 text-[#1B2632] border border-[#C9C1B1]/60 flex items-center justify-center shrink-0 shadow-xs">
                    @if(isset($customIcon))
                        {{ $customIcon }}
                    @elseif($icon)
                        {!! $icon !!}
                    @endif
                </span>
            @endif
            <span>{{ $title }}</span>
        </h1>

        @if($subtitle)
            <p class="text-xs sm:text-sm text-brand-ink/70 mt-1 max-w-3xl leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2.5 self-start md:self-auto shrink-0 pt-1 md:pt-0">
            {{ $actions }}
        </div>
    @endif
</div>
