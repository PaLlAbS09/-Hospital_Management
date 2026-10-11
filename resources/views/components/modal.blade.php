{{-- Atomic accessible modal shell with focus trap support.
Usage:
    <x-modal id="addScheduleModal" title="New doctor schedule" icon="bi-calendar-plus" size="modal-lg">
        <form ...>...</form>
    </x-modal>
--}}
@props([
    'id' => 'hms-modal',
    'title' => '',
    'labelledby' => null,
    'icon' => null,
    'size' => 'modal-lg',
    'scrollable' => false,
    'dismissLabel' => 'Close',
])

@php
    $labelId = $labelledby ?? $id.'-label';
@endphp

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="{{ $labelId }}"
    data-hms-modal
>
    <div class="modal-dialog {{ $size }}{{ $scrollable ? ' modal-dialog-scrollable' : '' }}">
        <div class="modal-content">
            @if ($title)
                <div class="modal-header">
                    <h6 class="modal-title" id="{{ $labelId }}">
                        @if ($icon)
                            <i class="bi {{ $icon }} me-2" aria-hidden="true"></i>
                        @endif
                        {{ $title }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ $dismissLabel }}"></button>
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
