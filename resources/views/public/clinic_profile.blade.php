@extends('layouts.public')

@section('title', $clinic->clinic_name)

@section('content')
    <div class="container mt-4">
        @include('partials.clinic._about', [
            'bookingUrl' => route('patient.login'),
            'bookingLabel' => 'Sign in to book',
            'backUrl' => route('clinics.index'),
            'backLabel' => 'Back to clinic directory',
        ])

        @include('partials.clinic._promos')

        @include('partials.clinic._doctors', [
            'bookingUrl' => route('patient.clinics.doctors', $clinic),
            'swiperId' => 'hms-clinic-doctors',
        ])

        @include('partials.clinic._reviews')
    </div>
@endsection

@push('scripts')
    @include('partials.swiper_init')
@endpush
