@props([
    'columns' => 6,
    'rows' => 6,
])

@for ($r = 0; $r < $rows; $r++)
    <tr class="atlas-skeleton-row" aria-hidden="true">
        @for ($c = 0; $c < $columns; $c++)
            <td>
                @if ($c === 0)
                    <div class="atlas-skeleton-stack">
                        <span class="atlas-skeleton-line" style="width: {{ [65, 75, 55, 80][$r % 4] }}%;"></span>
                        <span class="atlas-skeleton-line" style="width: {{ [35, 45, 30, 40][$r % 4] }}%; height: 10px; opacity: 0.7;"></span>
                    </div>
                @elseif ($c === $columns - 1)
                    <div class="inline-flex gap-2">
                        <span class="atlas-skeleton-btn"></span>
                    </div>
                @elseif ($c === 2)
                    <span class="atlas-skeleton-pill"></span>
                @else
                    <span class="atlas-skeleton-line" style="width: {{ [50, 70, 40, 60][($r + $c) % 4] }}%;"></span>
                @endif
            </td>
        @endfor
    </tr>
@endfor
