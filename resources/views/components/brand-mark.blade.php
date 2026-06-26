@props([
    'restaurant' => $brandRestaurant ?? null,
    'label' => null,
    'markClass' => 'h-11 w-11 rounded-2xl',
    'textClass' => 'text-sm',
])

@php
    $restaurantName = $label ?? $restaurant?->name ?? 'Arcade Kebab House';
    $initials = $restaurant?->initials ?? collect(preg_split('/\s+/', trim($restaurantName)) ?: [])
        ->filter()
        ->reject(fn ($word) => in_array(mb_strtolower((string) $word), ['the', 'and', '&'], true))
        ->take(2)
        ->map(fn ($word) => mb_substr((string) $word, 0, 1))
        ->implode('');
    $initials = mb_strtoupper($initials ?: 'AK');
@endphp

<span {{ $attributes->class(['grid shrink-0 place-items-center overflow-hidden bg-gradient-to-br from-brand-500 to-brand-800 font-black text-white shadow-lg shadow-brand-500/20', $markClass, $textClass]) }}>
    @if ($restaurant?->logo_url)
        <img
            src="{{ $restaurant->logo_url }}"
            alt="{{ $restaurantName }} logo"
            class="h-full w-full object-cover"
        >
    @else
        {{ $initials }}
    @endif
</span>
