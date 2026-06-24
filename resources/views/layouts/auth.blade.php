<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ isset($title) ? $title . ' | FreshBite' : 'FreshBite Restaurant' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-slate-900 antialiased">
    <main class="relative min-h-screen overflow-hidden">
        {{-- Background glows --}}
        <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-orange-200/50 blur-3xl"></div>
        <div class="absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-red-200/40 blur-3xl"></div>

    <div class="relative mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
        {{-- Left Brand Panel --}}
        <section class="hidden lg:block">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
                    FB
                </span>

                <span>
                    <span class="block text-2xl font-black tracking-tight text-slate-950">
                        FreshBite
                    </span>
                    <span class="block text-xs font-black uppercase tracking-[0.22em] text-orange-600">
                        Restaurant
                    </span>
                </span>
            </a>

            <div class="mt-14 max-w-xl">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-orange-600">
                    Fast Online Ordering
                </p>

                <h1 class="mt-4 text-5xl font-black tracking-tight text-slate-950">
                    Fresh meals, simple checkout, quick delivery.
                </h1>

                <p class="mt-6 text-base leading-8 text-slate-600">
                    Login or create a customer account to add items to cart, place cash-on-delivery orders, and track delivery status from your dashboard.
                </p>
            </div>

            <div class="mt-10 grid max-w-xl gap-4 sm:grid-cols-3">
                <div class="rounded-3xl border border-orange-100 bg-white/80 p-5 shadow-sm backdrop-blur">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <x-ui-icon name="burger" class="h-5 w-5" />
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-950">Fresh Menu</p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Browse categories and items.</p>
                </div>

                <div class="rounded-3xl border border-orange-100 bg-white/80 p-5 shadow-sm backdrop-blur">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <x-ui-icon name="cart" class="h-5 w-5" />
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-950">Easy Cart</p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Add items and checkout.</p>
                </div>

                <div class="rounded-3xl border border-orange-100 bg-white/80 p-5 shadow-sm backdrop-blur">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <x-ui-icon name="scooter" class="h-5 w-5" />
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-950">Delivery</p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Track order progress.</p>
                </div>
            </div>
        </section>

        {{-- Auth Card --}}
        <section class="mx-auto w-full max-w-xl">
            {{-- Mobile brand --}}
            <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
                    FB
                </span>

                <span>
                    <span class="block text-xl font-black tracking-tight text-slate-950">
                        FreshBite
                    </span>
                    <span class="block text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                        Restaurant
                    </span>
                </span>
            </a>

            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white/95 p-6 shadow-2xl shadow-orange-900/10 backdrop-blur sm:p-8">
                {{ $slot }}
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sm font-black text-orange-700 transition hover:text-orange-800">
                    ← Back to website
                </a>
            </div>
        </section>
    </div>
</main>

</body>
</html>
