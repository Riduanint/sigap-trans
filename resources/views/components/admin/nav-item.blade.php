@props(['route', 'label', 'icon', 'active' => false, 'badge' => null, 'parameters' => []])
<a href="{{ route($route, $parameters) }}" class="atlas-nav-item {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif title="{{ $label }}">
    <x-admin.icon :name="$icon" />
    <span class="atlas-nav-label">{{ $label }}</span>
    @if($badge > 0)<span class="atlas-count">{{ $badge }}</span>@endif
</a>
