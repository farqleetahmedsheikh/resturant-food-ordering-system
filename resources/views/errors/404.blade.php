<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Page Not Found | Arcade Kebab House</title>

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
                        Error 404
                    </p>

                    <h1 class="mt-4 max-w-2xl text-4xl font-black tracking-tight text-warm-950 sm:text-6xl">
                        This page is off the menu.
                    </h1>

                    <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-warm-600">
                        The link may be old, the page may have moved, or the address may have been typed incorrectly. You can head back to the menu and keep ordering.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('menu') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600"
                        >
                            Browse Menu
                        </a>

                        <a
                            href="{{ route('home') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-brand-200 bg-white px-6 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:bg-brand-50"
                        >
                            Back Home
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-brand-900/10 sm:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5">
                        <p class="text-5xl font-black tracking-tight text-brand-500">404</p>
                        <p class="mt-3 text-sm font-black text-warm-950">Page not found</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                            We could not find a page at this address.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-warm-200 bg-[var(--color-surface-warm)] p-5">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-warm-500">Quick fix</p>
                        <p class="mt-3 text-sm font-semibold leading-6 text-warm-700">
                            Check the URL, then use one of the links below to get back to a working page.
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 text-sm font-bold text-warm-600">
                    <a href="{{ route('home') }}" class="rounded-2xl border border-warm-200 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600">
                        Home
                    </a>
                    <a href="{{ route('menu') }}" class="rounded-2xl border border-warm-200 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600">
                        Menu
                    </a>
                    <a href="{{ route('contact') }}" class="rounded-2xl border border-warm-200 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600">
                        Contact
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
