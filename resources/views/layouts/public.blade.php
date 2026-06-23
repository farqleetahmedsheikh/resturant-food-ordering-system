<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | FreshBite' : 'FreshBite Restaurant' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-slate-900 antialiased">
    <div class="min-h-screen overflow-x-hidden">
        {{-- Navbar --}}
        <header
            class="sticky top-0 z-50 border-b border-orange-100/80 bg-white/90 shadow-sm shadow-orange-900/5 backdrop-blur-xl"
            x-data="{ open: false }"
        >
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="group flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition group-hover:-translate-y-0.5">
                        FB
                    </span>

                <span class="leading-tight">
                    <span class="block text-lg font-black tracking-tight text-slate-950">
                        FreshBite
                    </span>
                    <span class="hidden text-xs font-bold uppercase tracking-[0.18em] text-orange-600 sm:block">
                        Restaurant
                    </span>
                </span>
            </a>

            {{-- Mobile Button --}}
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-2xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-black text-slate-800 shadow-sm transition hover:bg-orange-50 md:hidden"
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
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('home') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('menu') }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('menu*') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    Menu
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="rounded-full px-4 py-2 text-sm font-bold transition {{ request()->routeIs('contact') ? 'bg-orange-50 text-orange-700' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-700' }}"
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
                            class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-orange-50 hover:text-orange-700"
                        >
                            Admin
                        </a>
                    @elseif (auth()->user()->role === 'rider')
                        <a
                            href="{{ route('rider.dashboard') }}"
                            class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-orange-50 hover:text-orange-700"
                        >
                            Rider
                        </a>
                    @else
                        <a
                            href="{{ route('cart.index') }}"
                            class="relative rounded-full border border-orange-200 bg-white px-4 py-2 text-sm font-black text-slate-800 shadow-sm transition hover:border-orange-300 hover:bg-orange-50"
                        >
                            Cart
                            <span class="ml-1 rounded-full bg-orange-600 px-2 py-0.5 text-xs font-black text-white">
                                {{ \App\Support\Cart::count() }}
                            </span>
                        </a>

                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-orange-50 hover:text-orange-700"
                        >
                            Dashboard
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                        >
                            Logout
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-full px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                    >
                        Login
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-full bg-orange-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
                    >
                        Register
                    </a>
                @endauth
            </div>
        </nav>

        {{-- Mobile Menu --}}
        <div
            class="border-t border-orange-100 bg-white px-4 py-4 shadow-lg shadow-orange-900/5 md:hidden"
            x-show="open"
            x-transition
            x-cloak
        >
            <div class="grid gap-2 text-sm font-bold">
                <a
                    href="{{ route('home') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('home') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}"
                >
                    Home
                </a>

                <a
                    href="{{ route('menu') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('menu*') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}"
                >
                    Menu
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="rounded-2xl px-4 py-3 {{ request()->routeIs('contact') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}"
                >
                    Contact
                </a>

                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                            Admin Dashboard
                        </a>
                    @elseif (auth()->user()->role === 'rider')
                        <a href="{{ route('rider.dashboard') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                            Rider Dashboard
                        </a>
                    @else
                        <a href="{{ route('cart.index') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                            Cart ({{ \App\Support\Cart::count() }})
                        </a>

                        <a href="{{ route('customer.dashboard') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
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
                            class="rounded-2xl border border-orange-200 bg-white px-4 py-3 text-center font-black text-slate-800"
                        >
                            Login
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-2xl bg-orange-600 px-4 py-3 text-center font-black text-white shadow-lg shadow-orange-600/20"
                        >
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('status') || session('success') || session('error'))
        <div class="mx-auto mt-5 max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @else
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-sm">
                    {{ session('status') ?? session('success') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-orange-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-[1.2fr_0.8fr_0.8fr]">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                            FB
                        </span>

                        <span>
                            <span class="block text-lg font-black tracking-tight text-slate-950">
                                FreshBite
                            </span>
                            <span class="block text-xs font-bold uppercase tracking-[0.18em] text-orange-600">
                                Restaurant
                            </span>
                        </span>
                    </a>

                    <p class="mt-5 max-w-md text-sm leading-7 text-slate-600">
                        Fresh food, simple ordering, cash on delivery, and easy delivery tracking from your customer dashboard.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">
                        Quick Links
                    </h3>

                    <div class="mt-5 grid gap-3 text-sm font-semibold text-slate-600">
                        <a href="{{ route('home') }}" class="transition hover:text-orange-600">Home</a>
                        <a href="{{ route('menu') }}" class="transition hover:text-orange-600">Menu</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-orange-600">Contact</a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">
                        Ordering
                    </h3>

                    <div class="mt-5 grid gap-3 text-sm font-semibold text-slate-600">
                        <p>Cash on delivery</p>
                        <p>Freshly prepared meals</p>
                        <p>Rider delivery updates</p>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-orange-100 pt-6 text-sm font-semibold text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>© {{ date('Y') }} FreshBite Restaurant. All rights reserved.</p>
                <p>Built for fast and simple online food ordering.</p>
            </div>
        </div>
    </footer>
</div>

</body>
</html>
