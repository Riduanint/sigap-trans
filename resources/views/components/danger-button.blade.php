<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-[#B43D3D] hover:bg-[#972B2B] active:bg-[#7D2222] text-white border border-[#B43D3D] rounded-md font-semibold text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-[#B43D3D] focus:ring-offset-2 transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
