<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Too Many Requests | FreshBite</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--color-surface-warm)] font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-orange-100 bg-white p-6 text-center shadow-2xl shadow-orange-900/10 sm:p-8">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-50 text-2xl font-black text-red-600">
                429
            </div>

            <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
                Security Pause
            </p>

            <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                Too many requests
            </h1>

            <p class="mx-auto mt-3 max-w-md text-sm font-semibold leading-7 text-slate-600">
                {{ $message ?? 'Too many requests were sent from your network. Please try again later.' }}
            </p>

            <div class="mt-6 rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4">
                <p class="text-sm font-black text-orange-800">
                    Try again in about {{ max(1, (int) ceil(($retryAfter ?? 600) / 60)) }} minutes.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
