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

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm font-sans select-none" style="color: #334155 !important;">
        
        {{-- Previous Page Link ("< Preview") --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 font-semibold text-slate-300 cursor-not-allowed" aria-disabled="true" style="color: #cbd5e1 !important; cursor: not-allowed !important;">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" style="color: #cbd5e1 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path></svg>
                <span>Preview</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 transition font-semibold text-slate-700 hover:text-[#0B1849] hover:bg-slate-100 rounded-xl cursor-pointer" style="color: #334155 !important;">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"></path></svg>
                <span>Preview</span>
            </a>
        @endif

        {{-- Pagination Elements: Numbers & Ellipsis (Max 4 Pages) --}}
        <div class="flex items-center gap-1 sm:gap-1.5">
            {{-- Leading Ellipsis jika start > 1 --}}
            @if ($start > 1)
                <span class="w-5 sm:w-6 flex items-center justify-center text-xs sm:text-sm font-bold text-slate-400 select-none" aria-disabled="true" style="color: #94a3b8 !important; letter-spacing: 0.15em !important;">
                    &hellip;
                </span>
            @endif

            {{-- 4 Page Numbers --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    {{-- Active Page Box: Rounded Navy Box dengan Teks Putih --}}
                    <span aria-current="page" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl font-bold text-xs sm:text-sm bg-[#0B1849] text-white shadow-xs border border-[#0B1849]" style="background-color: #0B1849 !important; border: 1px solid #0B1849 !important; color: #ffffff !important; min-width: 2rem;">
                        {{ $page }}
                    </span>
                @else
                    {{-- Inactive Page Number Link --}}
                    <a href="{{ $paginator->url($page) }}" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl transition text-xs sm:text-sm font-semibold text-slate-600 hover:text-[#0B1849] hover:bg-slate-100 cursor-pointer" style="color: #475569 !important; min-width: 2rem;">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Trailing Ellipsis jika end < lastPage --}}
            @if ($end < $lastPage)
                <span class="w-5 sm:w-6 flex items-center justify-center text-xs sm:text-sm font-bold text-slate-400 select-none" aria-disabled="true" style="color: #94a3b8 !important; letter-spacing: 0.15em !important;">
                    &hellip;
                </span>
            @endif
        </div>

        {{-- Next Page Link ("Next >") --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 transition font-semibold text-slate-700 hover:text-[#0B1849] hover:bg-slate-100 rounded-xl cursor-pointer" style="color: #334155 !important;">
                <span>Next</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 py-1 px-1.5 sm:px-2.5 font-semibold text-slate-300 cursor-not-allowed" aria-disabled="true" style="color: #cbd5e1 !important; cursor: not-allowed !important;">
                <span>Next</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" style="color: #cbd5e1 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path></svg>
            </span>
        @endif

    </nav>
@endif
