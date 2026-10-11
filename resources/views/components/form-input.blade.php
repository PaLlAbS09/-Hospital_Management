{{-- Atomic single-label form field with built-in validation styling.
Usage:
    <x-form-input name="disease" label="Disease / diagnosis" :value="old('disease')" hint="..." />
    <x-form-input type="textarea" name="prescription_details" label="Prescription details" rows="5" :autosize="true" />
--}}
@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => null,
    'id' => null,
    'hint' => null,
    'rows' => 3,
    'autosize' => false,
    'required' => false,
])

@php
    $fieldId = $id ?? ($name !== '' ? $name.'-'.uniqid() : 'field-'.uniqid());
    $hasError = $name !== '' && $errors->has($name);
    $inputClass = $type === 'select' ? 'form-select' : 'form-control';
    if ($type === 'textarea' && $autosize) {
        $inputClass .= ' hms-autosize';
    }
    if ($hasError) {
        $inputClass .= ' is-invalid';
    }
@endphp

<div {{ $attributes->merge(['class' => 'hms-field']) }}>
    @if ($label)
        <label class="form-label fw-semibold" for="{{ $fieldId }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    @if ($type === 'textarea')
        <textarea
            id="{{ $fieldId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            @if ($autosize) data-autosize @endif
            @if ($required) required @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->merge(['class' => $inputClass]) }}
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select
            id="{{ $fieldId }}"
            name="{{ $name }}"
            @if ($required) required @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->merge(['class' => $inputClass]) }}
        >{{ $slot }}</select>
    @else
        <input
            type="{{ $type }}"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $fieldId }}-error" @endif
            {{ $attributes->merge(['class' => $inputClass]) }}
        >
    @endif

    @if ($hint && ! $hasError)
        <div class="form-text">{{ $hint }}</div>
    @endif

    @if ($name !== '')
        @error($name)
            <div class="invalid-feedback d-block" id="{{ $fieldId }}-error" role="alert">{{ $message }}</div>
        @enderror
    @endif
</div>
