@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-[#243746] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
