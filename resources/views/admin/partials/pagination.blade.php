@if ($paginator->hasPages() || $paginator->total() > 0)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Batasi hanya maksimal 4 nomor halaman yang muncul agar tidak terlalu panjang
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

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm font-sans select-none text-[#1B2632]">
        
        {{-- Previous Page Link ("< Preview") --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 font-semibold text-[#C9C1B1] cursor-not-allowed" aria-disabled="true">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#C9C1B1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path></svg>
                <span>Preview</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 transition font-semibold text-[#2C3B4D] hover:text-[#1B2632] hover:bg-[#EEE9DF]/70 rounded-xl cursor-pointer">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path></svg>
                <span>Preview</span>
            </a>
        @endif

        {{-- Pagination Elements: Numbers & Ellipsis (Max 4 Pages) --}}
        <div class="flex items-center gap-1 sm:gap-1.5">
            {{-- Leading Ellipsis jika start > 1 --}}
            @if ($start > 1)
                <span class="w-5 sm:w-6 flex items-center justify-center text-xs sm:text-sm font-bold text-[#C9C1B1] select-none" aria-disabled="true">
                    &hellip;
                </span>
            @endif

            {{-- 4 Page Numbers --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    {{-- Active Page Box: Rounded Deep Navy Box dengan Teks Putih & border #1B2632 --}}
                    <span aria-current="page" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl font-bold text-xs sm:text-sm bg-[#1B2632] text-white shadow-ambient-xs border border-[#1B2632]" style="min-width: 2rem;">
                        {{ $page }}
                    </span>
                @else
                    {{-- Inactive Page Number Link --}}
                    <a href="{{ $paginator->url($page) }}" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl transition text-xs sm:text-sm font-semibold text-[#2C3B4D] hover:text-[#1B2632] hover:bg-[#EEE9DF]/70 border border-transparent hover:border-[#C9C1B1]/50 cursor-pointer" style="min-width: 2rem;">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Trailing Ellipsis jika end < lastPage --}}
            @if ($end < $lastPage)
                <span class="w-5 sm:w-6 flex items-center justify-center text-xs sm:text-sm font-bold text-[#C9C1B1] select-none" aria-disabled="true">
                    &hellip;
                </span>
            @endif
        </div>

        {{-- Next Page Link ("Next >") --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 transition font-semibold text-[#2C3B4D] hover:text-[#1B2632] hover:bg-[#EEE9DF]/70 rounded-xl cursor-pointer">
                <span>Next</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 font-semibold text-[#C9C1B1] cursor-not-allowed" aria-disabled="true">
                <span>Next</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#C9C1B1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path></svg>
            </span>
        @endif

    </nav>
@endif
