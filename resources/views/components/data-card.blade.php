{{-- Atomic card shell for consistent admin / doctor / patient panels.
Usage:
    <x-data-card title="Today's queue" icon="bi-people" :count="$queue->count()">
        ...body...
        <x-slot:footer>...</x-slot:footer>
    </x-data-card>
--}}
@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'count' => null,
    'flush' => true,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'hms-card']) }}>
    @if ($title)
        <div class="hms-card__header">
            <h5>
                @if ($icon)
                    <i class="bi {{ $icon }} me-2" aria-hidden="true"></i>
                @endif
                {{ $title }}
                @if ($count !== null)
                    <span class="text-muted fw-normal">({{ $count }})</span>
                @endif
                @if ($subtitle)
                    <small class="d-block fw-normal text-muted mt-1">{{ $subtitle }}</small>
                @endif
            </h5>
            @if ($actions)
                <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="hms-card__body{{ $flush ? ' hms-card__body--flush' : '' }}">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="hms-card__body border-top d-flex justify-content-center">
            {{ $footer }}
        </div>
    @endisset
</div>
