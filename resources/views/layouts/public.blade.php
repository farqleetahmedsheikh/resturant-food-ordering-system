<!DOCTYPE html>

<html lang="en-AU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $brandName = $brandRestaurant?->name ?? 'Arcade Kebab House';
        $brandDescription = $metaDescription
            ?? $brandRestaurant?->short_description
            ?? 'Order kebabs, grilled meals, sides, and drinks online from Arcade Kebab House.';
        $canonicalUrl = url()->current();
        $socialImage = $brandRestaurant?->cover_image_url ?? $brandRestaurant?->logo_url;
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $brandName,
            'url' => route('home'),
            'servesCuisine' => ['Kebab', 'Fast food', 'Middle Eastern'],
            'priceRange' => '$$',
            'acceptsReservations' => false,
            'currenciesAccepted' => 'AUD',
            'paymentAccepted' => 'Cash',
        ];

        if ($socialImage) {
            $jsonLd['image'] = $socialImage;
        }

        if ($brandRestaurant?->logo_url) {
            $jsonLd['logo'] = $brandRestaurant->logo_url;
        }

        if ($brandRestaurant?->phone) {
            $jsonLd['telephone'] = $brandRestaurant->phone;
        }

        $brandAddress = $brandRestaurant?->formatted_address ?? $brandRestaurant?->address;

        if ($brandAddress) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $brandAddress,
                'addressCountry' => 'AU',
            ];
        }

        if ($brandRestaurant?->latitude && $brandRestaurant?->longitude) {
            $jsonLd['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $brandRestaurant->latitude,
                'longitude' => (float) $brandRestaurant->longitude,
            ];
        }

        if ($brandRestaurant?->opening_time && $brandRestaurant?->closing_time) {
            $jsonLd['openingHoursSpecification'] = [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
                ],
                'opens' => \Illuminate\Support\Carbon::parse($brandRestaurant->opening_time)->format('H:i'),
                'closes' => \Illuminate\Support\Carbon::parse($brandRestaurant->closing_time)->format('H:i'),
            ]];
        }
    @endphp

    <title>{{ isset($title) ? $title . ' | ' . $brandName : $brandName . ' Restaurant' }}</title>
    <meta name="description" content="{{ $brandDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <meta property="og:locale" content="en_AU">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:title" content="{{ isset($title) ? $title . ' | ' . $brandName : $brandName . ' Restaurant' }}">
    <meta property="og:description" content="{{ $brandDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($socialImage)
        <meta property="og:image" content="{{ $socialImage }}">
    @endif
    <meta name="twitter:card" content="{{ $socialImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ isset($title) ? $title . ' | ' . $brandName : $brandName . ' Restaurant' }}">
    <meta name="twitter:description" content="{{ $brandDescription }}">
    @if ($socialImage)
        <meta name="twitter:image" content="{{ $socialImage }}">
    @endif
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-warm-900 antialiased">
    <div
        class="min-h-screen overflow-x-hidden"
        x-data="{ open: false }"
        x-on:keydown.escape.window="open = false"
    >
        {{-- Navbar --}}
        <header
            class="sticky top-0 z-[90] border-b border-warm-200/80 bg-white/90 shadow-sm shadow-brand-900/5 backdrop-blur-xl"
        >
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="group flex items-center gap-3">
                    <x-brand-mark mark-class="h-11 w-11 rounded-2xl transition group-hover:-translate-y-0.5" />

                <span class="leading-tight">
                    <span class="block text-lg font-black tracking-tight text-warm-950">
                        {{ $brandName }}
                    </span>
                    <span class="hidden text-xs font-bold uppercase tracking-[0.18em] text-brand-500 sm:block">
                        Restaurant
                    </span>
                </span>
            </a>

            {{-- Mobile Button --}}
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-2xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-black text-warm-900 shadow-sm transition hover:bg-brand-50 md:hidden"
                x-on:click="open = ! open"
                aria-label="Toggle menu"
            >
                <span x-show="!open">Menu</span>
                <span x-show="open" x-cloak>Close</span>
            </button>

            {{-- Desktop Links --}}
            <div class="hidden items-center gap-1 md:flex">
                <a
                    href="{{ route('home') }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('menu') }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('menu*') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    Menu
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('contact') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    Contact
                </a>
            </div>

            {{-- Desktop Actions --}}
            <div class="hidden items-center gap-3 md:flex">
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="rounded-full px-4 py-2 text-sm font-bold text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                        >
                            Admin
                        </a>
                    @elseif (auth()->user()->role === 'rider')
                        <a
                            href="{{ route('rider.dashboard') }}"
                            class="rounded-full px-4 py-2 text-sm font-bold text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                        >
                            Rider
                        </a>
                    @else
                        <a
                            href="{{ route('cart.index') }}"
                            class="relative rounded-full border border-brand-200 bg-white px-4 py-2 text-sm font-black text-warm-900 shadow-sm transition hover:border-brand-200 hover:bg-brand-50"
                        >
                            Cart
                            <span class="ml-1 rounded-full bg-brand-500 px-2 py-0.5 text-xs font-black text-white">
                                {{ \App\Support\Cart::count() }}
                            </span>
                        </a>

                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="rounded-full px-4 py-2 text-sm font-bold text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                        >
                            Dashboard
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-full border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-600 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                        >
                            Logout
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-full px-4 py-2 text-sm font-black text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                    >
                        Login
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-full bg-brand-500 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
                    >
                        Register
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- Mobile Menu --}}
    <div
        class="fixed inset-x-0 bottom-0 top-16 z-[200] max-h-[calc(100dvh-4rem)] overflow-y-auto bg-warm-950/35 p-2 pb-[calc(1rem+env(safe-area-inset-bottom))] backdrop-blur-sm md:hidden sm:p-3"
        x-show="open"
        x-transition.opacity
        x-cloak
        x-on:click.self="open = false"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-y-3 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="-translate-y-3 opacity-0"
                class="min-h-max rounded-[1.5rem] border border-warm-200 bg-white px-3 py-3 shadow-2xl shadow-warm-950/20 sm:px-4 sm:py-4"
            >
                <div class="grid gap-2 text-sm font-bold">
                <a
                    href="{{ route('home') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('menu') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('menu*') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}"
                >
                    Menu
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('contact') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}"
                >
                    Contact
                </a>

                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                            Admin Dashboard
                        </a>
                    @elseif (auth()->user()->role === 'rider')
                        <a href="{{ route('rider.dashboard') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                            Rider Dashboard
                        </a>
                    @else
                        <a href="{{ route('cart.index') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                            Cart ({{ \App\Support\Cart::count() }})
                        </a>

                        <a href="{{ route('customer.dashboard') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                            Dashboard
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="pt-2">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-left font-black text-red-600"
                        >
                            Logout
                        </button>
                    </form>
                @else
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <a
                            href="{{ route('login') }}"
                            class="rounded-2xl border border-brand-200 bg-white px-4 py-3 text-center font-black text-warm-900"
                        >
                            Login
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-2xl bg-brand-500 px-4 py-3 text-center font-black text-white shadow-lg shadow-brand-500/20"
                        >
                            Register
                        </a>
                    </div>
                @endauth
                </div>
            </div>
        </div>

    <x-toast />

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-warm-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <x-brand-mark mark-class="h-11 w-11 rounded-2xl" />

                        <span>
                            <span class="block text-lg font-black tracking-tight text-warm-950">
                                {{ $brandName }}
                            </span>
                            <span class="block text-xs font-bold uppercase tracking-[0.18em] text-brand-500">
                                Restaurant
                            </span>
                        </span>
                    </a>

                    <p class="mt-5 max-w-md text-sm leading-7 text-warm-600">
                        Fresh food, simple ordering, secure card payment, and easy delivery tracking from your customer dashboard.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-warm-950">
                        Quick Links
                    </h3>

                    <div class="mt-5 grid gap-3 text-sm font-semibold text-warm-600">
                        <a href="{{ route('home') }}" class="transition hover:text-brand-500">Home</a>
                        <a href="{{ route('menu') }}" class="transition hover:text-brand-500">Menu</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-brand-500">Contact</a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-warm-950">
                        Ordering
                    </h3>

                    <div class="mt-5 grid gap-3 text-sm font-semibold text-warm-600">
                        <p>Freshly prepared meals</p>
                        <p>Rider delivery updates</p>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-warm-200 pt-6 text-sm font-semibold text-warm-500 md:flex-row md:items-center md:justify-between">
                <p>© {{ date('Y') }} {{ $brandName }} Restaurant. All rights reserved.</p>
                <p>Built for fast and simple online food ordering.</p>
            </div>
        </div>
    </footer>
</div>

@stack('scripts')

</body>
</html>
