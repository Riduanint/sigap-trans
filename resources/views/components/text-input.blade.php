@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-[#D4DEE7] bg-white text-[#243746] placeholder:text-slate-400 focus:border-[#2457A7] focus:ring-2 focus:ring-[#2457A7]/20 rounded-md shadow-xs text-sm px-3 py-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed transition-colors']) }}>
