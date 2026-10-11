{{-- Skeleton loader for async work (booking availability, clinic filtering).
Usage: <x-skeleton-loader lines="3" /> or <x-skeleton-card /> via type.
Keeps users grounded vs full-page overlay spinners. --}}
@props(['lines' => 3, 'type' => 'lines'])

<div {{ $attributes->merge(['class' => 'hms-skeleton', 'aria-hidden' => 'true']) }}>
    <style>
        .hms-skeleton__line--wide { width: 90%; }
        .hms-skeleton__line--medium { width: 65%; }
        .hms-skeleton__line--standard { width: 80%; }
    </style>

    @if ($type === 'card')
        <div class="hms-skeleton__thumb"></div>
        <div class="hms-skeleton__line" style="width:70%"></div>
        <div class="hms-skeleton__line" style="width:45%"></div>
    @elseif ($type === 'table')
        @for ($i = 0; $i < max(1, (int) $lines); $i++)
            <div class="hms-skeleton__row">
                <div class="hms-skeleton__line" style="width:22%"></div>
                <div class="hms-skeleton__line" style="width:32%"></div>
                <div class="hms-skeleton__line" style="width:18%"></div>
            </div>
        @endfor
    @else
        @for ($i = 0; $i < max(1, (int) $lines); $i++)
            @php
                $lineClass = $i === 0
                    ? 'hms-skeleton__line--wide'
                    : ($i % 2 ? 'hms-skeleton__line--medium' : 'hms-skeleton__line--standard');
            @endphp
            <div class="hms-skeleton__line {{ $lineClass }}"></div>
        @endfor
    @endif
</div>
