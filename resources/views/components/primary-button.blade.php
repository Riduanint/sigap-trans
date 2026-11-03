<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-[#2457A7] hover:bg-[#194482] active:bg-[#133566] text-white border border-[#2457A7] rounded-md font-semibold text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-[#2457A7] focus:ring-offset-2 transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
