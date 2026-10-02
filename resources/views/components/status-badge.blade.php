@props([
    'status' => 'pending',
    'label' => null,
])

@php
    $variant = match ($status) {
        'approved', 'Completed' => 'success',
        'pending' => 'warning',
        'rejected', 'Cancelled_by_Doctor' => 'danger',
        'Active' => 'primary',
        'Cancelled_by_Patient' => 'secondary',
        'No_Show' => 'dark',
        default => 'secondary',
    };

    $text = $label ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $status));
@endphp

<span {{ $attributes->merge(['class' => 'badge text-bg-'.$variant]) }}>{{ $text }}</span>
