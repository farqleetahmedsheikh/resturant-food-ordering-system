<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | Arcade Kebab House Customer' : 'Customer Dashboard | Arcade Kebab House' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
<meta name="robots" content="noindex,nofollow">
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
        x-data="{ mobileMenu: false }"
        x-on:keydown.escape.window="mobileMenu = false"
    >
        {{-- Mobile Header --}}
        <header class="sticky top-0 z-[90] border-b border-warm-200 bg-white/90 shadow-sm shadow-brand-900/5 backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-brand-mark mark-class="h-11 w-11 rounded-2xl" />

                <span>
                    <span class="block text-base font-black tracking-tight text-warm-950">
                        Arcade Kebab House
                    </span>
                    <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                        Customer Area
                    </span>
                </span>
            </a>

            <button
                type="button"
                x-on:click="mobileMenu = ! mobileMenu"
                class="rounded-2xl border border-brand-200 bg-white px-4 py-2 text-sm font-black text-warm-900 shadow-sm"
            >
                <span x-show="! mobileMenu">Menu</span>
                <span x-show="mobileMenu" x-cloak>Close</span>
            </button>
        </div>
    </header>

    <div
        x-show="mobileMenu"
        x-transition.opacity
        x-cloak
        x-on:click.self="mobileMenu = false"
        class="fixed inset-x-0 bottom-0 top-16 z-[200] max-h-[calc(100dvh-4rem)] overflow-y-auto bg-warm-950/35 p-2 pb-[calc(1rem+env(safe-area-inset-bottom))] backdrop-blur-sm lg:hidden sm:p-3"
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
                <a href="{{ route('home') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                    Home
                </a>

                <a href="{{ route('menu') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                    Menu
                </a>

                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('customer.dashboard') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}">
                    Dashboard
                </a>

                <a href="{{ route('customer.orders') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('customer.orders*') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}">
                    My Orders
                </a>

                <a href="{{ route('cart.index') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                    Cart ({{ \App\Support\Cart::count() }})
                </a>

                <a href="{{ route('checkout.index') }}" class="rounded-2xl px-4 py-3 text-warm-600 hover:bg-brand-50">
                    Checkout
                </a>

                <a href="{{ route('account.security') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('account.security') ? 'bg-brand-50 text-brand-600' : 'text-warm-600 hover:bg-brand-50' }}">
                    Security
                </a>

                <form action="{{ route('logout') }}" method="POST" class="pt-2">
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-left font-black text-red-600"
                    >
                        Logout
                    </button>
                </form>
                </div>
            </div>
        </div>

    <div class="min-h-screen lg:pl-[300px]">
        {{-- Desktop Sidebar --}}
        <aside class="fixed bottom-0 left-0 top-0 z-40 hidden w-[300px] overflow-hidden border-r border-warm-200 bg-white/90 p-5 shadow-sm shadow-brand-900/5 backdrop-blur-xl lg:block">
            <div class="flex h-full flex-col overflow-y-auto pr-1">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[1.5rem] border border-warm-200 bg-[var(--color-surface-warm)] p-4">
                    <x-brand-mark mark-class="h-12 w-12 rounded-2xl" />

                    <span>
                        <span class="block text-lg font-black tracking-tight text-warm-950">
                            Arcade Kebab House
                        </span>
                        <span class="block text-xs font-black uppercase tracking-[0.18em] text-brand-500">
                            Customer Area
                        </span>
                    </span>
                </a>

                {{-- Customer Mini Card --}}
                <div class="mt-5 rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-sm font-black text-brand-600">
                            {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-warm-950">
                                {{ auth()->user()->name ?? 'Customer' }}
                            </p>
                            <p class="truncate text-xs font-semibold text-warm-500">
                                {{ auth()->user()->email ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="mt-6 grid gap-2 text-sm font-bold">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                    >
                        <x-ui-icon name="home" class="h-5 w-5 shrink-0" />
                        <span>Home</span>
                    </a>

                    <a
                        href="{{ route('menu') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                    >
                        <x-ui-icon name="burger" class="h-5 w-5 shrink-0" />
                        <span>Menu</span>
                    </a>

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('customer.dashboard') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <x-ui-icon name="chart" class="h-5 w-5 shrink-0" />
                        <span>Dashboard</span>
                    </a>

                    <a
                        href="{{ route('customer.orders') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('customer.orders*') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <x-ui-icon name="receipt" class="h-5 w-5 shrink-0" />
                        <span>My Orders</span>
                    </a>

                    <a
                        href="{{ route('cart.index') }}"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                    >
                        <span class="flex items-center gap-3">
                            <x-ui-icon name="cart" class="h-5 w-5 shrink-0" />
                            <span>Cart</span>
                        </span>

                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-black text-brand-600">
                            {{ \App\Support\Cart::count() }}
                        </span>
                    </a>

                    <a
                        href="{{ route('checkout.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                    >
                        <x-ui-icon name="credit-card" class="h-5 w-5 shrink-0" />
                        <span>Checkout</span>
                    </a>

                    <a
                        href="{{ route('account.security') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <x-ui-icon name="shield" class="h-5 w-5 shrink-0" />
                        <span>Security</span>
                    </a>
                </nav>

                {{-- Bottom CTA --}}
                <div class="mt-auto">
                    <div class="rounded-[1.5rem] border border-warm-200 bg-gradient-to-br from-brand-50 to-brand-100 p-4">
                        <p class="text-sm font-black text-warm-950">
                            Hungry again?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-600">
                            Browse fresh items and place another order from the menu.
                        </p>

                        <a
                            href="{{ route('menu') }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600"
                        >
                            Order Now
                        </a>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="mt-4">
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-black text-warm-600 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="min-w-0">
            {{-- Desktop Topbar --}}
            <div class="sticky top-0 z-40 hidden border-b border-warm-200 bg-surface-warm-90 px-6 py-4 backdrop-blur-xl lg:block">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-brand-500">
                            Arcade Kebab House Customer
                        </p>

                        <p class="mt-1 text-sm font-semibold text-warm-500">
                            Manage orders, track deliveries, and continue shopping.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('menu') }}"
                            class="rounded-2xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-black text-warm-900 shadow-sm transition hover:bg-brand-50"
                        >
                            View Menu
                        </a>

                        <a
                            href="{{ route('cart.index') }}"
                            class="rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600"
                        >
                            Cart ({{ \App\Support\Cart::count() }})
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <x-toast />

                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</div>

@stack('scripts')

</body>
</html>
