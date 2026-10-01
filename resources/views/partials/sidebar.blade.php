{{--
    Sidebar navigation. Receives $guard and $currentUser from layouts.app,
    but resolves them itself when rendered standalone.
--}}
@php
    $guard = $guard ?? \App\Support\Navigation::activeGuard();
    $currentUser = $currentUser ?? \App\Support\Navigation::user($guard);
@endphp

<aside class="hms-sidebar">
    <button class="hms-sidebar__close" type="button" id="hms-sidebar-close" aria-label="Close navigation">
        <i class="bi bi-x-lg"></i>
    </button>

    <a href="{{ route('home') }}" class="hms-brand">
        <x-brand-logo />
        <span class="hms-brand__text">
            {{ config('app.name') }}
            <small>{{ \App\Support\Navigation::label($guard ?? '') }} Portal</small>
        </span>
    </a>

    <div class="hms-nav-label">Navigation</div>

    <nav class="d-flex flex-column gap-1">
        @forelse (\App\Support\Navigation::items($guard ?? '') as $item)
            <a href="{{ route($item['route']) }}"
               class="hms-nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}"
               @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                <i class="bi {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @empty
            <span class="hms-nav-link disabled">No menu available</span>
        @endforelse
    </nav>

    <div class="hms-nav-label">Visitor site</div>
    <nav class="d-flex flex-column gap-1">
        <a href="{{ route('home') }}" class="hms-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-globe2"></i><span>Landing page</span>
        </a>
        <a href="{{ route('clinics.index') }}" class="hms-nav-link {{ request()->routeIs('clinics.index') ? 'active' : '' }}">
            <i class="bi bi-map"></i><span>Clinic directory</span>
        </a>
    </nav>

    <div class="hms-sidebar__footer">
        <div class="text-white fw-semibold">{{ $currentUser?->display_name ?? 'Signed in user' }}</div>
        <div class="text-truncate">{{ $currentUser?->email }}</div>

        @if ($guard === 'clinic' && $currentUser instanceof \App\Models\Clinic)
            <div class="mt-2"><x-status-badge :status="$currentUser->status" /></div>
        @endif
    </div>
</aside>
