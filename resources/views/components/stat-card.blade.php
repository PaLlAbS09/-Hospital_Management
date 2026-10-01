@props([
    'label' => '',
    'value' => 0,
    'icon' => 'bi-graph-up-arrow',
    'variant' => 'primary',
    'hint' => null,
])

<div class="hms-stat hms-stat--{{ $variant }}">
    <i class="bi {{ $icon }} hms-stat__icon"></i>
    <div class="hms-stat__value">{{ $value }}</div>
    <div class="hms-stat__label">{{ $label }}</div>
    @if ($hint)
        <div class="small mt-2" style="opacity: .85">{{ $hint }}</div>
    @endif
</div>
