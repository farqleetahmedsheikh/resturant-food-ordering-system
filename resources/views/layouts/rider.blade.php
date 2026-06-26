<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | Arcade Kebab House Rider' : 'Arcade Kebab House Rider' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
<meta name="robots" content="noindex,nofollow">
@stack('head')

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

</head>

<body class="min-h-screen bg-[var(--color-surface-app)] font-sans text-warm-900 antialiased">
    <div
        class="min-h-screen overflow-x-hidden"
        x-data="{ mobileMenu: false }"
        x-on:keydown.escape.window="mobileMenu = false"
    >
        {{-- Mobile Header --}}
        <header class="sticky top-0 z-[90] border-b border-warm-200 bg-white/95 shadow-sm backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('rider.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <x-brand-mark mark-class="h-11 w-11 rounded-2xl" />

                <span class="min-w-0">
                    <span class="block truncate text-base font-black tracking-tight text-warm-950">
                        Arcade Kebab House
                    </span>

                    <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-brand-500">
                        Rider Portal
                    </span>
                </span>
            </a>

            <button
                type="button"
                x-on:click="mobileMenu = ! mobileMenu"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-200 bg-white text-warm-600 shadow-sm transition hover:bg-brand-50"
                aria-label="Toggle navigation"
            >
                <svg
                    x-show="! mobileMenu"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                <svg
                    x-show="mobileMenu"
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </header>

    {{-- Mobile Menu --}}
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
                <div class="mb-4 flex items-center gap-3 rounded-2xl border border-warm-200 bg-[var(--color-surface-warm)] p-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-500 text-sm font-black text-white">
                        {{ mb_substr(auth()->user()->name ?? 'R', 0, 1) }}
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-warm-950">
                            {{ auth()->user()->name ?? 'Delivery Rider' }}
                        </p>

                        <p class="truncate text-xs font-semibold text-warm-500">
                            {{ auth()->user()->phone ?? auth()->user()->email ?? '' }}
                        </p>
                    </div>
                </div>

                <nav class="grid gap-2 text-sm font-bold">
                <a
                    href="{{ route('rider.dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('rider.dashboard') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>

                    Dashboard
                </a>

                <a
                    href="{{ route('rider.orders') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('rider.orders*') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 7h11v10H3z" />
                        <path d="M14 10h4l3 3v4h-7z" />
                        <circle cx="7" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                    </svg>

                    Assigned Orders
                </a>

                <a
                    href="{{ route('account.security') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
                        <path d="M9.5 12.5 11 14l3.5-4" />
                    </svg>

                    Security
                </a>

                <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-warm-600 transition hover:bg-brand-50 hover:text-brand-600"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 11 9-8 9 8" />
                        <path d="M5 10v10h14V10M9 20v-6h6v6" />
                    </svg>

                    Public Website
                </a>

                <form action="{{ route('logout') }}" method="POST" class="pt-2">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-left font-black text-red-600 transition hover:bg-red-100"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 17l5-5-5-5M15 12H3M14 3h7v18h-7" />
                        </svg>

                        Logout
                    </button>
                </form>
                </nav>
            </div>
        </div>

    <div class="min-h-screen lg:pl-[290px]">
        {{-- Desktop Sidebar --}}
        <aside class="fixed bottom-0 left-0 top-0 z-40 hidden w-[290px] overflow-hidden border-r border-warm-200 bg-white p-5 shadow-sm shadow-brand-900/5 lg:block">
            <div class="flex h-full flex-col overflow-y-auto pr-1">
                {{-- Brand --}}
                <a
                    href="{{ route('rider.dashboard') }}"
                    class="flex items-center gap-3 rounded-[1.5rem] border border-warm-200 bg-[var(--color-surface-warm)] p-4"
                >
                    <x-brand-mark mark-class="h-12 w-12 rounded-2xl" />

                    <span>
                        <span class="block text-lg font-black tracking-tight text-warm-950">
                            Arcade Kebab House
                        </span>

                        <span class="block text-xs font-black uppercase tracking-[0.18em] text-brand-500">
                            Rider Portal
                        </span>
                    </span>
                </a>

                {{-- Rider Card --}}
                <div class="mt-5 rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-sm font-black text-brand-600">
                            {{ mb_substr(auth()->user()->name ?? 'R', 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-warm-950">
                                {{ auth()->user()->name ?? 'Delivery Rider' }}
                            </p>

                            <p class="truncate text-xs font-semibold text-warm-500">
                                {{ auth()->user()->phone ?? auth()->user()->email ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 rounded-xl bg-leaf-50 px-3 py-2">
                        <span class="h-2 w-2 rounded-full bg-leaf-500"></span>

                        <span class="text-xs font-black text-leaf-700">
                            Ready for delivery
                        </span>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="mt-6 grid gap-2 text-sm font-bold">
                    <a
                        href="{{ route('rider.dashboard') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('rider.dashboard') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>

                        Dashboard
                    </a>

                    <a
                        href="{{ route('rider.orders') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('rider.orders*') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7h11v10H3z" />
                            <path d="M14 10h4l3 3v4h-7z" />
                            <circle cx="7" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                        </svg>

                        Assigned Orders
                    </a>

                    <a
                        href="{{ route('account.security') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-warm-600 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
                            <path d="M9.5 12.5 11 14l3.5-4" />
                        </svg>

                        Security
                    </a>
                </nav>

                {{-- Delivery Reminder --}}
                <div class="mt-auto">
                    <div class="rounded-[1.5rem] border border-warm-200 bg-gradient-to-br from-brand-50 to-brand-100 p-4">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-brand-500 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 7h11v10H3z" />
                                <path d="M14 10h4l3 3v4h-7z" />
                                <circle cx="7" cy="18" r="2" />
                                <circle cx="18" cy="18" r="2" />
                            </svg>
                        </div>

                        <p class="mt-4 text-sm font-black text-warm-950">
                            Check assigned orders
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-600">
                            Review delivery details and update each order status on time.
                        </p>

                        <a
                            href="{{ route('rider.orders') }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600"
                        >
                            View Deliveries
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-warm-200 bg-white px-3 py-3 text-xs font-black text-warm-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                        >
                            Public Site
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button
                                type="submit"
                                class="w-full rounded-2xl border border-red-100 bg-red-50 px-3 py-3 text-xs font-black text-red-600 transition hover:bg-red-100"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Area --}}
        <main class="min-w-0 pb-24 lg:pb-0">
            {{-- Desktop Topbar --}}
            <header class="sticky top-0 z-40 hidden border-b border-warm-200 bg-white/90 px-6 py-4 shadow-sm shadow-brand-900/5 backdrop-blur-xl lg:block">
                <div class="flex items-center justify-between gap-6">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-brand-500">
                            Arcade Kebab House Delivery
                        </p>

                        <h1 class="mt-1 truncate text-xl font-black text-warm-950">
                            {{ $title ?? 'Rider Dashboard' }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 rounded-2xl border border-leaf-100 bg-leaf-50 px-4 py-2.5">
                            <span class="h-2 w-2 rounded-full bg-leaf-500"></span>
                            <span class="text-sm font-black text-leaf-700">
                                Available
                            </span>
                        </div>

                        <a
                            href="{{ route('rider.orders') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600"
                        >
                            Assigned Orders
                        </a>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
                <x-toast />

                <div class="mx-auto max-w-[1500px]">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    {{-- Mobile Bottom Navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-3 py-2 shadow-[var(--shadow-bottom-nav)] backdrop-blur-xl lg:hidden">
        <div class="mx-auto grid max-w-md grid-cols-3 gap-2">
            <a
                href="{{ route('rider.dashboard') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('rider.dashboard') ? 'bg-brand-50 text-brand-600' : 'text-warm-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>

                Dashboard
            </a>

            <a
                href="{{ route('rider.orders') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('rider.orders*') ? 'bg-brand-50 text-brand-600' : 'text-warm-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7h11v10H3z" />
                    <path d="M14 10h4l3 3v4h-7z" />
                    <circle cx="7" cy="18" r="2" />
                    <circle cx="18" cy="18" r="2" />
                </svg>

                Assigned Orders
            </a>

            <a
                href="{{ route('account.security') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('account.security') ? 'bg-brand-50 text-brand-600' : 'text-warm-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
                    <path d="M9.5 12.5 11 14l3.5-4" />
                </svg>

                Security
            </a>
        </div>
    </nav>
</div>

@stack('scripts')

</body>
</html>
