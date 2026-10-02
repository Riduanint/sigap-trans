@props(['upt', 'link' => true])
<div class="atlas-identity">
    <span class="atlas-code">UPT-{{ str_pad($upt->upt_number, 3, '0', STR_PAD_LEFT) }} · {{ $upt->regency?->name ?? 'Kabupaten belum tercatat' }}</span>
    @if($link)<a href="{{ route('admin.upt.show', $upt->id) }}">{{ $upt->upt_name }}</a>@else<strong>{{ $upt->upt_name }}</strong>@endif
    <span class="atlas-muted">Kini: {{ $upt->current_village_name ?: 'Desa belum tercatat' }}</span>
</div>
