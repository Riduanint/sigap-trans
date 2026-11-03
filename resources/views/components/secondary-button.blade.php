<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-[#F3F6F8] active:bg-[#E7EEF3] text-[#243746] border border-[#D4DEE7] rounded-md font-semibold text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-[#2457A7] focus:ring-offset-2 transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
