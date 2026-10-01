@php
    $flashMap = [
        'success' => ['icon' => 'bi-check-circle-fill', 'variant' => 'success'],
        'error' => ['icon' => 'bi-exclamation-octagon-fill', 'variant' => 'danger'],
        'warning' => ['icon' => 'bi-exclamation-triangle-fill', 'variant' => 'warning'],
        'info' => ['icon' => 'bi-info-circle-fill', 'variant' => 'info'],
    ];
@endphp

@foreach ($flashMap as $key => $meta)
    @if (session()->has($key))
        <div class="alert alert-{{ $meta['variant'] }} alert-dismissible d-flex align-items-start gap-2 shadow-sm" role="alert">
            <i class="bi {{ $meta['icon'] }} fs-5 lh-1"></i>
            <div class="flex-grow-1">{{ session($key) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 lh-1"></i>
        <div>
            <strong class="d-block mb-1">Please correct the following:</strong>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if (session()->has('approval_message'))
    @php
        $pending = session('approval_status') === 'pending';
    @endphp
    <div class="alert alert-{{ $pending ? 'warning' : 'danger' }} d-flex align-items-start gap-2 shadow-sm" role="alert">
        <i class="bi {{ $pending ? 'bi-hourglass-split' : 'bi-shield-x' }} fs-5 lh-1"></i>
        <div>
            <strong class="d-block">{{ $pending ? 'Approval pending' : 'Registration rejected' }}</strong>
            <span class="small">{{ session('approval_message') }}</span>
        </div>
    </div>
@endif
