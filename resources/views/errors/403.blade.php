<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Access Denied | Arcade Kebab House</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-warm-900 antialiased">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <section class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-10 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <x-brand-mark mark-class="h-12 w-12 rounded-2xl" />

                    <span>
                        <span class="block text-xl font-black tracking-tight text-warm-950">
                            Arcade Kebab House
                        </span>
                        <span class="block text-xs font-black uppercase tracking-[0.18em] text-brand-500">
                            Restaurant
                        </span>
                    </span>
                </a>

                <div class="mt-12">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-500">
                        Error 403
                    </p>

                    <h1 class="mt-4 max-w-2xl text-4xl font-black tracking-tight text-warm-950 sm:text-6xl">
                        You do not have access to this area.
                    </h1>

                    <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-warm-600">
                        This page is limited to a specific account role or signed-in user. If you believe you should be able to view it, sign in with the correct account or contact the restaurant.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @guest
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600"
                            >
                                Sign In
                            </a>
                        @endguest

                        <a
                            href="{{ route('home') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-brand-200 bg-white px-6 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:bg-brand-50"
                        >
                            Back Home
                        </a>

                        <a
                            href="{{ route('contact') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-warm-200 bg-white px-6 py-3 text-sm font-black text-warm-700 shadow-sm transition hover:bg-warm-50"
                        >
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-brand-900/10 sm:p-8">
                <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
                    <p class="text-5xl font-black tracking-tight text-red-600">403</p>
                    <p class="mt-3 text-sm font-black text-warm-950">Access denied</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                        {{ $exception?->getMessage() ?: 'Your current account cannot view this page.' }}
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-warm-200 bg-[var(--color-surface-warm)] p-4">
                        <p class="text-sm font-black text-warm-950">Customers</p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-600">Use the menu, cart, checkout, and order dashboard.</p>
                    </div>

                    <div class="rounded-2xl border border-warm-200 bg-[var(--color-surface-warm)] p-4">
                        <p class="text-sm font-black text-warm-950">Riders</p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-600">Use rider tools only with a rider account.</p>
                    </div>

                    <div class="rounded-2xl border border-warm-200 bg-[var(--color-surface-warm)] p-4">
                        <p class="text-sm font-black text-warm-950">Admins</p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-600">Restaurant settings require admin access.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
