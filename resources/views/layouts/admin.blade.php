<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | FreshBite Admin' : 'FreshBite Admin' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

</head>

<body class="min-h-screen bg-[var(--color-surface-app)] font-sans text-slate-900 antialiased">
    <div
        class="min-h-screen overflow-x-hidden"
        x-data="{ mobileMenu: false }"
        x-on:keydown.escape.window="mobileMenu = false"
    >
        {{-- Mobile Header --}}
        <header class="sticky top-0 z-[90] border-b border-orange-100 bg-white/95 shadow-sm backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                        FB
                    </span>

                <span>
                    <span class="block text-base font-black tracking-tight text-slate-950">
                        FreshBite
                    </span>

                    <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-orange-600">
                        Admin Panel
                    </span>
                </span>
            </a>

            <button
                type="button"
                x-on:click="mobileMenu = ! mobileMenu"
                class="grid h-11 w-11 place-items-center rounded-2xl border border-orange-200 bg-white text-slate-700 shadow-sm transition hover:bg-orange-50"
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

    {{-- Mobile Navigation --}}
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
                {{-- Mobile Admin Card --}}
                <div class="mb-4 flex items-center gap-3 rounded-2xl border border-orange-100 bg-[var(--color-surface-warm)] p-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-600 text-sm font-black text-white">
                        {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-slate-950">
                            {{ auth()->user()->name ?? 'Administrator' }}
                        </p>

                        <p class="truncate text-xs font-semibold text-slate-500">
                            {{ auth()->user()->email ?? '' }}
                        </p>
                    </div>
                </div>

                <nav class="grid gap-2 text-sm font-bold">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
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
                    href="{{ route('admin.orders.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.orders*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                        <path d="M9 7h6M9 11h6M9 15h3" />
                    </svg>
                    Orders
                </a>

                <a
                    href="{{ route('admin.menu-items.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.menu-items*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4z" />
                        <path d="M8 8h8M8 12h8M8 16h5" />
                    </svg>
                    Menu Items
                </a>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.categories*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 15h6v4h-6z" />
                    </svg>
                    Categories
                </a>

                <a
                    href="{{ route('admin.riders.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.riders*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="6" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                        <path d="M8 18h8M7 16l2-6h6l3 6M10 10V7h4" />
                    </svg>
                    Riders
                </a>

                <a
                    href="{{ route('admin.settings.restaurant.edit') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.settings.*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.35.28.7.5 1.1.6h.5v4h-.1a1.7 1.7 0 0 0-1.5 1.4z" />
                    </svg>
                    Restaurant Settings
                </a>

                <a
                    href="{{ route('account.security') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
                        <path d="M9.5 12.5 11 14l3.5-4" />
                    </svg>
                    Security
                </a>

                <div class="my-2 border-t border-slate-100"></div>

                <a
                    href="/admin"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4z" />
                        <path d="M4 9h16M9 9v11" />
                    </svg>
                    Filament Panel
                </a>

                <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-slate-700 transition hover:bg-orange-50 hover:text-orange-700"
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

    <div class="min-h-screen lg:pl-[300px]">
        {{-- Desktop Sidebar --}}
        <aside class="fixed bottom-0 left-0 top-0 z-40 hidden w-[300px] overflow-hidden border-r border-orange-100 bg-white p-5 shadow-sm shadow-orange-900/5 lg:block">
            <div class="flex h-full flex-col overflow-y-auto pr-1">
                {{-- Brand --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 rounded-[1.5rem] border border-orange-100 bg-[var(--color-surface-warm)] p-4"
                >
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
                        FB
                    </span>

                    <span>
                        <span class="block text-lg font-black tracking-tight text-slate-950">
                            FreshBite
                        </span>

                        <span class="block text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                            Admin Panel
                        </span>
                    </span>
                </a>

                {{-- Administrator Card --}}
                <div class="mt-5 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-sm font-black text-orange-700">
                            {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-950">
                                {{ auth()->user()->name ?? 'Administrator' }}
                            </p>

                            <p class="truncate text-xs font-semibold text-slate-500">
                                {{ auth()->user()->email ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-black text-emerald-700">
                            Administrator Online
                        </span>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="mt-6 grid gap-2 text-sm font-bold">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
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
                        href="{{ route('admin.orders.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.orders*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                            <path d="M9 7h6M9 11h6M9 15h3" />
                        </svg>
                        Orders
                    </a>

                    <a
                        href="{{ route('admin.menu-items.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.menu-items*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16v16H4z" />
                            <path d="M8 8h8M8 12h8M8 16h5" />
                        </svg>
                        Menu Items
                    </a>

                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.categories*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 15h6v4h-6z" />
                        </svg>
                        Categories
                    </a>

                    <a
                        href="{{ route('admin.riders.index') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.riders*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="6" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                            <path d="M8 18h8M7 16l2-6h6l3 6M10 10V7h4" />
                        </svg>
                        Riders
                    </a>

                    <a
                        href="{{ route('admin.settings.restaurant.edit') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.settings.*') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.35.28.7.5 1.1.6h.5v4h-.1a1.7 1.7 0 0 0-1.5 1.4z" />
                        </svg>
                        Restaurant Settings
                    </a>

                    <a
                        href="{{ route('account.security') }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ request()->routeIs('account.security') ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/20' : 'text-slate-700 hover:bg-orange-50 hover:text-orange-700' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6l-7-3z" />
                            <path d="M9.5 12.5 11 14l3.5-4" />
                        </svg>
                        Security
                    </a>
                </nav>

                {{-- Bottom Links --}}
                <div class="mt-auto">
                    <div class="rounded-[1.5rem] border border-orange-100 bg-gradient-to-br from-orange-50 to-red-50 p-4">
                        <p class="text-sm font-black text-slate-950">
                            Advanced management
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                            Open the Filament panel for advanced database management.
                        </p>

                        <a
                            href="/admin"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white transition hover:bg-slate-800"
                        >
                            Open Filament
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
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
            <header class="sticky top-0 z-40 hidden border-b border-orange-100 bg-white/90 px-6 py-4 shadow-sm shadow-orange-900/5 backdrop-blur-xl lg:block">
                <div class="flex items-center justify-between gap-6">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
                            FreshBite Administration
                        </p>

                        <h1 class="mt-1 truncate text-xl font-black text-slate-950">
                            {{ $title ?? 'Admin Dashboard' }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-orange-50 hover:text-orange-700"
                        >
                            View Public Site
                        </a>

                        <a
                            href="{{ route('admin.orders.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                        >
                            Manage Orders
                        </a>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
                {{-- Flash Messages --}}
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

                <div class="mx-auto max-w-[1600px]">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    {{-- Mobile Admin Quick Actions --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-3 py-2 shadow-[var(--shadow-bottom-nav)] backdrop-blur-xl lg:hidden">
        <div class="mx-auto grid max-w-md grid-cols-3 gap-2">
            <a
                href="{{ route('admin.orders.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('admin.orders*') ? 'bg-orange-50 text-orange-700' : 'text-slate-500 hover:bg-orange-50 hover:text-orange-700' }}"
            >
                <x-ui-icon name="receipt" class="h-5 w-5" />
                Orders
            </a>

            <a
                href="{{ route('admin.riders.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('admin.riders*') ? 'bg-orange-50 text-orange-700' : 'text-slate-500 hover:bg-orange-50 hover:text-orange-700' }}"
            >
                <x-ui-icon name="scooter" class="h-5 w-5" />
                Riders
            </a>

            <a
                href="{{ route('admin.menu-items.index') }}"
                class="flex flex-col items-center justify-center gap-1 rounded-2xl px-3 py-2 text-xs font-black transition {{ request()->routeIs('admin.menu-items*') || request()->routeIs('admin.categories*') ? 'bg-orange-50 text-orange-700' : 'text-slate-500 hover:bg-orange-50 hover:text-orange-700' }}"
            >
                <x-ui-icon name="menu" class="h-5 w-5" />
                Menu
            </a>
        </div>
    </nav>
</div>

</body>
</html>
