@props([
    'name',
])

@php
    $iconAttributes = $attributes->merge([
        'class' => 'h-5 w-5',
        'aria-hidden' => 'true',
    ]);
@endphp

@switch($name)
    @case('home')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v10h14V10M9 20v-6h6v6" />
        </svg>
        @break

    @case('menu')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16v16H4z" />
            <path stroke-linecap="round" d="M8 8h8M8 12h8M8 16h5" />
        </svg>
        @break

    @case('burger')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 11h14" />
            <path d="M7 11a5 5 0 0 1 10 0" />
            <path stroke-linecap="round" d="M4 15h16M6 19h12" />
        </svg>
        @break

    @case('chart')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <rect x="7" y="10" width="3" height="6" rx="1" />
            <rect x="12" y="7" width="3" height="9" rx="1" />
            <rect x="17" y="4" width="3" height="12" rx="1" />
        </svg>
        @break

    @case('receipt')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
            <path stroke-linecap="round" d="M9 7h6M9 11h6M9 15h3" />
        </svg>
        @break

    @case('cart')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.8h8.8a2 2 0 0 0 2-1.6L21 8H7" />
        </svg>
        @break

    @case('credit-card')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="M3 10h18" />
            <path stroke-linecap="round" d="M7 15h3" />
        </svg>
        @break

    @case('shield')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 12.5 11 14l3.5-4" />
        </svg>
        @break

    @case('scooter')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="6" cy="18" r="2" />
            <circle cx="18" cy="18" r="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 18h6l2-6h3l2 4M14 12h-4l-2 6M10 8h4" />
        </svg>
        @break

    @case('utensils')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" d="M7 3v8M10 3v8M7 7h3" />
            <path d="M8.5 11v10" />
            <path stroke-linecap="round" d="M17 3v18M17 3c-2 1.5-3 3.5-3 6s1 4 3 5" />
        </svg>
        @break

    @case('check')
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
        </svg>
        @break

    @default
        <svg {{ $iconAttributes }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" d="M12 8v4l3 2" />
        </svg>
@endswitch
