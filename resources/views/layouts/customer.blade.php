<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | FreshBite Customer' : 'Customer Dashboard | FreshBite' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-slate-900 antialiased">
    <div
        class="min-h-screen overflow-x-hidden"
        x-data="{ mobileMenu: false }"
        x-on:keydown.escape.window="mobileMenu = false"
    >
        {{-- Mobile Header --}}
        <header class="sticky top-0 z-[90] border-b border-orange-100 bg-white/90 shadow-sm shadow-orange-900/5 backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                        FB
                    </span>

                <span>
                    <span class="block text-base font-black tracking-tight text-slate-950">
                        FreshBite
                    </span>
                    <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                        Customer Area
                    </span>
                </span>
            </a>

            <button
                type="button"
                x-on:click="mobileMenu = ! mobileMenu"
                class="rounded-2xl border border-orange-200 bg-white px-4 py-2 text-sm font-black text-slate-800 shadow-sm"
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
        class="fixed inset-x-0 bottom-0 top-16 z-[200] max-h-[calc(100dvh-4rem)] overflow-y-auto bg-slate-950/35 p-2 pb-[calc(1rem+env(safe-area-inset-bottom))] backdrop-blur-sm lg:hidden sm:p-3"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-y-3 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="-translate-y-3 opacity-0"
                class="min-h-max rounded-[1.5rem] border border-orange-100 bg-white px-3 py-3 shadow-2xl shadow-slate-950/20 sm:px-4 sm:py-4"
            >
                <div class="grid gap-2 text-sm font-bold">
                <a href="{{ route('home') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                    Home
                </a>

                <a href="{{ route('menu') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                    Menu
                </a>

                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('customer.dashboard') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}">
                    Dashboard
                </a>

                <a href="{{ route('customer.orders') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('customer.orders*') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}">
                    My Orders
                </a>

                <a href="{{ route('cart.index') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                    Cart ({{ \App\Support\Cart::count() }})
                </a>

                <a href="{{ route('checkout.index') }}" class="rounded-2xl px-4 py-3 text-slate-700 hover:bg-orange-50">
                    Checkout
                </a>

                <a href="{{ route('account.security') }}" class="rounded-2xl px-4 py-3 {{ request()->routeIs('account.security') ? 'bg-orange-50 text-orange-700' : 'text-slate-700 hover:bg-orange-50' }}">
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
        <aside class="fixed bottom-0 left-0 top-0 z-40 hidden w-[300px] overflow-hidden border-r border-orange-100 bg-white/90 p-5 shadow-sm shadow-orange-900/5 backdrop-blur-xl lg:block">
            <div class="flex h-full flex-col overflow-y-auto pr-1">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[1.5rem] border border-orange-100 bg-[var(--color-surface-warm)] p-4">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
                        FB
                    </span>

                    <span>
                        <span class="block text-lg font-black tracking-tight text-slate-950">
                            FreshBite
                        </span>
                        <span class="block text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                            Customer Area
                        </span>
                    </span>
                </a>

                {{-- Customer Mini Card --}}
                <div class="mt-5 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 text-sm font-black text-orange-700">
                            {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-950">
                                {{ auth()->user()->name ?? 'Customer' }}
                            </p>
                            <p class="truncate text-xs font-semibold text-slate-500">
                                {{ auth()->user()->email ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="mt-6 grid gap-2 text-sm font-bold">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                    >
                        <x-ui-icon name="home" class="h-5 w-5 shrink-0" />
                        <span>Home</span>
                    </a>

                    <a
                        href="{{ route('menu') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                    >
                        <x-ui-icon name="burger" class="h-5 w-5 shrink-0" />
                        <span>Menu</span>
                    </a>

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('customer.dashboard') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <x-ui-icon name="chart" class="h-5 w-5 shrink-0" />
                        <span>Dashboard</span>
                    </a>

                    <a
                        href="{{ route('customer.orders') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('customer.orders*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <x-ui-icon name="receipt" class="h-5 w-5 shrink-0" />
                        <span>My Orders</span>
                    </a>

                    <a
                        href="{{ route('cart.index') }}"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                    >
                        <span class="flex items-center gap-3">
                            <x-ui-icon name="cart" class="h-5 w-5 shrink-0" />
                            <span>Cart</span>
                        </span>

                        <span class="rounded-full bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-700">
                            {{ \App\Support\Cart::count() }}
                        </span>
                    </a>

                    <a
                        href="{{ route('checkout.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                    >
                        <x-ui-icon name="credit-card" class="h-5 w-5 shrink-0" />
                        <span>Checkout</span>
                    </a>

                    <a
                        href="{{ route('account.security') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <x-ui-icon name="shield" class="h-5 w-5 shrink-0" />
                        <span>Security</span>
                    </a>
                </nav>

                {{-- Bottom CTA --}}
                <div class="mt-auto">
                    <div class="rounded-[1.5rem] border border-orange-100 bg-gradient-to-br from-orange-50 to-red-50 p-4">
                        <p class="text-sm font-black text-slate-950">
                            Hungry again?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                            Browse fresh items and place another order from the menu.
                        </p>

                        <a
                            href="{{ route('menu') }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-orange-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                        >
                            Order Now
                        </a>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="mt-4">
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
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
            <div class="sticky top-0 z-40 hidden border-b border-orange-100 bg-surface-warm-90 px-6 py-4 backdrop-blur-xl lg:block">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
                            FreshBite Customer
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            Manage orders, track deliveries, and continue shopping.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('menu') }}"
                            class="rounded-2xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-black text-slate-800 shadow-sm transition hover:bg-orange-50"
                        >
                            View Menu
                        </a>

                        <a
                            href="{{ route('cart.index') }}"
                            class="rounded-2xl bg-orange-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                        >
                            Cart ({{ \App\Support\Cart::count() }})
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                @if (session('status') || session('success') || session('error'))
                    <div class="mb-6">
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

                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
