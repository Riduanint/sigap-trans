@if ($paginator->hasPages() || $paginator->total() > 0)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Batasi maksimal 4 nomor halaman agar navigasi tetap ringkas
        if ($lastPage <= 4) {
            $start = 1;
            $end = $lastPage;
        } elseif ($currentPage <= 3) {
            $start = 1;
            $end = 4;
        } elseif ($currentPage >= $lastPage - 2) {
            $start = max(1, $lastPage - 3);
            $end = $lastPage;
        } else {
            $start = $currentPage - 1;
            $end = $currentPage + 2;
        }
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center gap-1.5 text-[13px] select-none text-[#243746]" style="font-family: 'Source Sans 3', system-ui, sans-serif;">

        {{-- Halaman sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded border border-[#D4DEE7] text-[#5A6E7D] opacity-50 cursor-not-allowed" aria-disabled="true">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m15 19-7-7 7-7"></path></svg>
                <span>Sebelumnya</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded border border-[#D4DEE7] bg-white text-[#243746] font-semibold hover:bg-[#E7EEF3] transition cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m15 19-7-7 7-7"></path></svg>
                <span>Sebelumnya</span>
            </a>
        @endif

        {{-- Nomor halaman --}}
        <div class="flex items-center gap-1">
            @if ($start > 1)
                <span class="w-7 flex items-center justify-center text-[#5A6E7D]" aria-disabled="true">&hellip;</span>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <span aria-current="page" class="min-w-[30px] h-[30px] px-2 inline-flex items-center justify-center rounded border border-[#2457A7] bg-[#2457A7] text-white font-semibold tabular-nums">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="min-w-[30px] h-[30px] px-2 inline-flex items-center justify-center rounded border border-[#D4DEE7] bg-white text-[#243746] font-medium hover:bg-[#E7EEF3] transition cursor-pointer tabular-nums">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            @if ($end < $lastPage)
                <span class="w-7 flex items-center justify-center text-[#5A6E7D]" aria-disabled="true">&hellip;</span>
            @endif
        </div>

        {{-- Halaman berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded border border-[#D4DEE7] bg-white text-[#243746] font-semibold hover:bg-[#E7EEF3] transition cursor-pointer">
                <span>Berikutnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m9 5 7 7-7 7"></path></svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded border border-[#D4DEE7] text-[#5A6E7D] opacity-50 cursor-not-allowed" aria-disabled="true">
                <span>Berikutnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m9 5 7 7-7 7"></path></svg>
            </span>
        @endif

    </nav>
@endif
