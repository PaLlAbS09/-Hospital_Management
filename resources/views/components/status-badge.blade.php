@props([
    'status' => 'pending',
    'label' => null,
])

@php
    // Clinical semantic palette: emerald = confirmed/completed, amber = pending,
    // rose/red = cancellations & no-shows, blue/teal = active/approved.
    $variant = match ($status) {
        'approved', 'Completed', 'Confirmed' => 'success',
        'pending', 'Pending' => 'warning',
        'rejected', 'Cancelled_by_Doctor' => 'danger',
        'Active' => 'primary',
        'Cancelled_by_Patient', 'No_Show' => 'dark',
        default => 'secondary',
    };

    $text = $label ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $status));
@endphp

<span {{ $attributes->merge(['class' => 'badge hms-badge hms-badge--'.$variant]) }}>{{ $text }}</span>

