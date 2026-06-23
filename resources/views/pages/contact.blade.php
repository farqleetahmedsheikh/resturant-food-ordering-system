@component('layouts.public', ['title' => 'Contact'])
@php
$restaurantName = $restaurant?->name ?? 'FreshBite Restaurant';
$phone = $restaurant?->phone ?? '0300 0000010';
$email = $restaurant?->email ?? '[hello@freshbite.test](mailto:hello@freshbite.test)';
$address = $restaurant?->address ?? 'Main Food Street, City Center';

    $timingText = $restaurant?->opening_time && $restaurant?->closing_time
        ? $restaurant->opening_time . ' - ' . $restaurant->closing_time
        : '12:00 PM - 11:00 PM';

    $isOpen = (bool) ($restaurant?->is_open ?? true);

    $phoneHref = preg_replace('/[^0-9+]/', '', $phone);
    $emailHref = 'mailto:' . $email;

    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode($address);
@endphp

<main class="min-h-screen bg-[var(--color-surface-warm)] pb-24 lg:pb-0">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-orange-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-28 bottom-0 h-80 w-80 rounded-full bg-red-200/40 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-8 pt-5 sm:px-6 sm:pb-12 sm:pt-8 lg:px-8 lg:py-16">
            {{-- Mobile Restaurant Header --}}
            <div class="mb-6 flex items-center justify-between gap-4 lg:hidden">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-orange-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                        @if ($restaurant?->logo_url)
                            <img
                                src="{{ $restaurant->logo_url }}"
                                alt="{{ $restaurantName }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ mb_strtoupper(mb_substr($restaurantName, 0, 2)) }}
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-slate-950">
                            {{ $restaurantName }}
                        </p>

                        <div class="mt-1 flex items-center gap-2">
                            <span
                                @class([
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em]',
                                    'bg-emerald-50 text-emerald-700' => $isOpen,
                                    'bg-amber-50 text-amber-700' => ! $isOpen,
                                ])
                            >
                                <span
                                    @class([
                                        'h-1.5 w-1.5 rounded-full',
                                        'animate-pulse bg-emerald-500' => $isOpen,
                                        'bg-amber-500' => ! $isOpen,
                                    ])
                                ></span>

                                {{ $isOpen ? 'Open now' : 'Closed' }}
                            </span>

                            <span class="truncate text-[10px] font-semibold text-slate-500">
                                {{ $timingText }}
                            </span>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('menu') }}"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-orange-100 bg-white text-orange-600 shadow-sm transition active:scale-95"
                    aria-label="Browse menu"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M4 3h16v18H4z" />
                        <path d="M8 7h8M8 11h8M8 15h5" />
                    </svg>
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-center lg:gap-12">
                {{-- Hero Content --}}
                <div>
                    <div class="hidden items-center gap-3 lg:inline-flex">
                        <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-2xl bg-orange-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                            @if ($restaurant?->logo_url)
                                <img
                                    src="{{ $restaurant->logo_url }}"
                                    alt="{{ $restaurantName }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                {{ mb_strtoupper(mb_substr($restaurantName, 0, 2)) }}
                            @endif
                        </div>

                        <div>
                            <p class="font-black text-slate-950">
                                {{ $restaurantName }}
                            </p>

                            <div class="mt-1 flex items-center gap-2">
                                <span
                                    @class([
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em]',
                                        'bg-emerald-50 text-emerald-700' => $isOpen,
                                        'bg-amber-50 text-amber-700' => ! $isOpen,
                                    ])
                                >
                                    <span
                                        @class([
                                            'h-1.5 w-1.5 rounded-full',
                                            'animate-pulse bg-emerald-500' => $isOpen,
                                            'bg-amber-500' => ! $isOpen,
                                        ])
                                    ></span>

                                    {{ $isOpen ? 'Open now' : 'Currently closed' }}
                                </span>

                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $timingText }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-orange-600 lg:mt-8 lg:text-xs">
                        Restaurant Support
                    </p>

                    <h1 class="mt-2 max-w-3xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        How can we
                        <span class="text-orange-600">help you today?</span>
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-slate-600 sm:mt-5 sm:text-base sm:leading-8">
                        Contact us for order support, delivery updates, restaurant information, feedback, or general questions.
                    </p>

                    {{-- Main Mobile/Desktop Actions --}}
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap lg:mt-8">
                        <a
                            href="tel:{{ $phoneHref }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98] hover:bg-orange-700 sm:rounded-2xl sm:px-6"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                            </svg>

                            Call Now
                        </a>

                        <a
                            href="{{ $emailHref }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-black text-orange-700 shadow-sm transition active:scale-[0.98] hover:bg-orange-50 sm:rounded-2xl sm:px-6"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>

                            Email Us
                        </a>

                        <a
                            href="{{ $mapsUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="col-span-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-black text-orange-700 transition active:scale-[0.98] hover:bg-orange-100 sm:col-auto sm:rounded-2xl sm:px-6"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                <circle cx="12" cy="9" r="2.5" />
                            </svg>

                            Get Directions
                        </a>
                    </div>
                </div>

                {{-- Desktop Support Card --}}
                <aside class="rounded-[2rem] border border-orange-100 bg-white p-6 shadow-2xl shadow-orange-900/10 sm:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                                Quick Support
                            </p>

                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                Need help with an order?
                            </h2>
                        </div>

                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-6 w-6"
                            >
                                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-sm font-semibold leading-6 text-slate-600">
                        Calling is the fastest option for urgent delivery or active-order questions.
                    </p>

                    <a
                        href="tel:{{ $phoneHref }}"
                        class="mt-6 flex items-center gap-4 rounded-2xl bg-orange-600 p-4 text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/15">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-[10px] font-black uppercase tracking-[0.14em] text-orange-100">
                                Call Restaurant
                            </span>

                            <span class="mt-1 block break-words text-lg font-black">
                                {{ $phone }}
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 shrink-0"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a
                            href="{{ $emailHref }}"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5 text-orange-600"
                            >
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>

                            <p class="mt-3 text-sm font-black text-slate-950">
                                Send Email
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                General support
                            </p>
                        </a>

                        <a
                            href="{{ $mapsUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5 text-orange-600"
                            >
                                <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                <circle cx="12" cy="9" r="2.5" />
                            </svg>

                            <p class="mt-3 text-sm font-black text-slate-950">
                                Directions
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Open in Maps
                            </p>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Contact Methods --}}
    <section class="border-y border-orange-100 bg-white py-8 sm:py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-end justify-between gap-4 sm:mb-8">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600 sm:text-xs">
                        Contact Options
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Choose the best way to reach us
                    </h2>
                </div>

                <span
                    @class([
                        'hidden rounded-full px-4 py-2 text-xs font-black sm:inline-flex',
                        'bg-emerald-50 text-emerald-700' => $isOpen,
                        'bg-amber-50 text-amber-700' => ! $isOpen,
                    ])
                >
                    {{ $isOpen ? 'Restaurant open' : 'Restaurant closed' }}
                </span>
            </div>

            <div class="grid gap-3 md:grid-cols-3 md:gap-5">
                {{-- Phone --}}
                <a
                    href="tel:{{ $phoneHref }}"
                    class="group flex items-center gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm transition active:scale-[0.99] hover:border-orange-200 hover:shadow-xl hover:shadow-orange-900/5 sm:p-5 md:block md:p-6"
                >
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 transition group-hover:bg-orange-600 group-hover:text-white md:h-14 md:w-14">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                        </svg>
                    </span>

                    <span class="min-w-0 flex-1 md:mt-5 md:block">
                        <span class="block text-[9px] font-black uppercase tracking-[0.16em] text-orange-600 sm:text-xs">
                            Phone Support
                        </span>

                        <span class="mt-1 block break-words text-base font-black text-slate-950 sm:text-lg md:mt-2">
                            {{ $phone }}
                        </span>

                        <span class="mt-1 hidden text-sm font-semibold leading-6 text-slate-500 md:block">
                            Best for active orders and urgent delivery questions.
                        </span>
                    </span>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-orange-600 md:hidden"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- Email --}}
                <a
                    href="{{ $emailHref }}"
                    class="group flex items-center gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm transition active:scale-[0.99] hover:border-orange-200 hover:shadow-xl hover:shadow-orange-900/5 sm:p-5 md:block md:p-6"
                >
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white md:h-14 md:w-14">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                    </span>

                    <span class="min-w-0 flex-1 md:mt-5 md:block">
                        <span class="block text-[9px] font-black uppercase tracking-[0.16em] text-blue-600 sm:text-xs">
                            Email Support
                        </span>

                        <span class="mt-1 block break-all text-sm font-black text-slate-950 sm:text-base md:mt-2">
                            {{ $email }}
                        </span>

                        <span class="mt-1 hidden text-sm font-semibold leading-6 text-slate-500 md:block">
                            Best for feedback, business inquiries, and non-urgent help.
                        </span>
                    </span>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-600 md:hidden"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>

                {{-- Hours --}}
                <div class="flex items-center gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm sm:p-5 md:block md:p-6">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 md:h-14 md:w-14">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>

                    <span class="min-w-0 flex-1 md:mt-5 md:block">
                        <span class="block text-[9px] font-black uppercase tracking-[0.16em] text-emerald-600 sm:text-xs">
                            Operating Hours
                        </span>

                        <span class="mt-1 block text-base font-black text-slate-950 sm:text-lg md:mt-2">
                            {{ $timingText }}
                        </span>

                        <span class="mt-1 hidden text-sm font-semibold leading-6 text-slate-500 md:block">
                            Restaurant ordering and delivery availability.
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Location --}}
    <section class="py-9 sm:py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch lg:gap-8">
                {{-- Address Card --}}
                <div class="rounded-[1.75rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600 sm:text-xs">
                        Restaurant Location
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Find {{ $restaurantName }}
                    </h2>

                    <p class="mt-3 text-sm font-semibold leading-6 text-slate-600">
                        Open the address in your maps application for navigation and directions.
                    </p>

                    <div class="mt-6 rounded-2xl border border-orange-100 bg-orange-50 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                    <circle cx="12" cy="9" r="2.5" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-orange-600">
                                    Address
                                </p>

                                <p class="mt-1 break-words text-base font-black leading-7 text-slate-950">
                                    {{ $address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <a
                        href="{{ $mapsUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:w-auto sm:rounded-2xl"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                            <circle cx="12" cy="9" r="2.5" />
                        </svg>

                        Open in Google Maps
                    </a>
                </div>

                {{-- Visual Map Panel --}}
                <a
                    href="{{ $mapsUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="group relative min-h-[260px] overflow-hidden rounded-[1.75rem] border border-orange-100 bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 shadow-sm sm:min-h-[340px]"
                    aria-label="Open restaurant location in Google Maps"
                >
                    <div class="absolute inset-0 opacity-60">
                        <div class="absolute -left-12 top-[20%] h-10 w-[130%] rotate-12 bg-white/80"></div>
                        <div class="absolute -left-16 top-[58%] h-8 w-[130%] -rotate-6 bg-white/75"></div>
                        <div class="absolute left-[28%] -top-16 h-[140%] w-8 rotate-6 bg-white/70"></div>
                        <div class="absolute right-[22%] -top-16 h-[140%] w-6 -rotate-12 bg-white/65"></div>
                    </div>

                    <div class="absolute left-[14%] top-[18%] h-3 w-3 rounded-full bg-orange-300"></div>
                    <div class="absolute right-[18%] top-[24%] h-4 w-4 rounded-full bg-red-300"></div>
                    <div class="absolute bottom-[18%] left-[22%] h-4 w-4 rounded-full bg-amber-300"></div>

                    <div class="absolute inset-0 grid place-items-center p-6">
                        <div class="text-center">
                            <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-600 text-white shadow-2xl shadow-orange-600/30 transition group-hover:-translate-y-1 group-hover:scale-105">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-9 w-9"
                                >
                                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                    <circle cx="12" cy="9" r="2.5" />
                                </svg>
                            </div>

                            <div class="mt-5 rounded-2xl bg-white/90 px-5 py-4 shadow-xl backdrop-blur">
                                <p class="font-black text-slate-950">
                                    {{ $restaurantName }}
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Tap to open directions
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Support Guidance --}}
    <section class="border-y border-orange-100 bg-white py-9 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[0.7fr_1.3fr] lg:items-start lg:gap-12">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600 sm:text-xs">
                        Support Guide
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        The quickest way to get help
                    </h2>

                    <p class="mt-3 text-sm font-semibold leading-6 text-slate-600">
                        Choose a contact method based on the type and urgency of your request.
                    </p>
                </div>

                <div class="divide-y divide-slate-100 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <a
                        href="tel:{{ $phoneHref }}"
                        class="group flex items-center gap-4 p-4 transition hover:bg-orange-50 sm:p-5"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-50 text-red-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-950">
                                Active order or delivery problem
                            </span>

                            <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                                Call the restaurant for the fastest response.
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-orange-600"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>

                    <a
                        href="{{ $emailHref }}"
                        class="group flex items-center gap-4 p-4 transition hover:bg-blue-50 sm:p-5"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-950">
                                Feedback or general question
                            </span>

                            <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                                Email us when an immediate response is not required.
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-600"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>

                    <a
                        href="{{ $mapsUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="group flex items-center gap-4 p-4 transition hover:bg-emerald-50 sm:p-5"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                <circle cx="12" cy="9" r="2.5" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-950">
                                Restaurant location
                            </span>

                            <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                                Open the address in Google Maps for directions.
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-600"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-white py-9 sm:py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-r from-orange-600 to-red-600 px-5 py-8 text-white shadow-2xl shadow-orange-900/20 sm:px-8 sm:py-10 lg:rounded-[2rem] lg:px-10">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-100 sm:text-xs">
                            Ready to order?
                        </p>

                        <h2 class="mt-2 max-w-2xl text-2xl font-black tracking-tight sm:text-3xl">
                            Browse the menu and find your next meal.
                        </h2>

                        <p class="mt-2 max-w-xl text-xs font-semibold leading-5 text-orange-50 sm:text-sm sm:leading-6">
                            Choose your food, add it to your cart, and pay when your order arrives.
                        </p>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-black text-orange-700 shadow-lg transition active:scale-[0.98] hover:bg-orange-50 sm:w-auto sm:rounded-2xl"
                    >
                        Browse Menu

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Persistent Mobile Contact Bar --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="tel:{{ $phoneHref }}"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                </svg>

                Call Restaurant
            </a>

            <a
                href="{{ $emailHref }}"
                class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-[0.98]"
                aria-label="Email restaurant"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="m3 7 9 6 9-6" />
                </svg>
            </a>

            <a
                href="{{ $mapsUrl }}"
                target="_blank"
                rel="noopener"
                class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-[0.98]"
                aria-label="Open restaurant directions"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                    <circle cx="12" cy="9" r="2.5" />
                </svg>
            </a>
        </div>
    </div>
</main>

@endcomponent
