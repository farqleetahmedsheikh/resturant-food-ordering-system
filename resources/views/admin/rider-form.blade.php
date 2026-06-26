@component('layouts.admin', ['title' => $mode === 'create' ? 'Create Rider' : 'Edit Rider'])
@php
$isCreateMode = $mode === 'create';

    $pageTitle = $isCreateMode
        ? 'Create rider'
        : 'Edit rider';

    $submitLabel = $isCreateMode
        ? 'Create Rider Account'
        : 'Save Rider Changes';

    $riderName = old(
        'name',
        $rider->name ?? ''
    );

    $riderEmail = old(
        'email',
        $rider->email ?? ''
    );

    $riderPhone = old(
        'phone',
        $rider->phone ?? ''
    );

    $riderActive = filter_var(
        old('is_active', $rider->is_active ?? true),
        FILTER_VALIDATE_BOOL
    );
@endphp

<div
    x-data="{
        riderName: @js($riderName),
        riderEmail: @js($riderEmail),
        riderPhone: @js($riderPhone),
        riderActive: {{ $riderActive ? 'true' : 'false' }},
        password: '',
        showPassword: false,
        submitting: false,

        passwordScore() {
            let score = 0;

            if (this.password.length >= 8) score++;
            if (/[a-z]/.test(this.password) && /[A-Z]/.test(this.password)) score++;
            if (/\d/.test(this.password)) score++;
            if (/[^A-Za-z0-9]/.test(this.password)) score++;

            return score;
        },

        passwordLabel() {
            if (! this.password.length) {
                return 'Not entered';
            }

            return [
                'Very weak',
                'Weak',
                'Fair',
                'Strong',
                'Very strong'
            ][this.passwordScore()];
        },

        passwordBarClass(index) {
            const score = this.passwordScore();

            if (index > score) {
                return 'bg-warm-200';
            }

            if (score <= 1) {
                return 'bg-red-500';
            }

            if (score === 2) {
                return 'bg-gold-500';
            }

            if (score === 3) {
                return 'bg-blue-500';
            }

            return 'bg-leaf-500';
        },

        passwordTextClass() {
            const score = this.passwordScore();

            if (! this.password.length) {
                return 'text-warm-500';
            }

            if (score <= 1) {
                return 'text-red-600';
            }

            if (score === 2) {
                return 'text-gold-500';
            }

            if (score === 3) {
                return 'text-blue-600';
            }

            return 'text-leaf-700';
        },

        initials() {
            const value = this.riderName.trim();

            if (! value) {
                return 'R';
            }

            return value
                .split(/\s+/)
                .slice(0, 2)
                .map(part => part.charAt(0).toUpperCase())
                .join('');
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Header --}}
    <header class="xl:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Delivery Management
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-warm-950">
                    {{ $pageTitle }}
                </h1>

                <p class="mt-1 text-sm font-semibold leading-5 text-warm-500">
                    {{ $isCreateMode
                        ? 'Create a secure delivery rider account.'
                        : 'Update rider details and account access.' }}
                </p>
            </div>

            <a
                href="{{ route('admin.riders.index') }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
                aria-label="Back to riders"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.25"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>
        </div>
    </header>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 xl:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Delivery Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-warm-950">
                {{ $pageTitle }}
            </h1>

            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-warm-600">
                {{ $isCreateMode
                    ? 'Create a secure rider account with accurate contact details and delivery dashboard access.'
                    : 'Update rider contact information, account security, login access and delivery availability.' }}
            </p>
        </div>

        <a
            href="{{ route('admin.riders.index') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-50"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-4 w-4"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m15 18-6-6 6-6"
                />
            </svg>

            Back to Riders
        </a>
    </header>

    {{-- Workflow Banner --}}
    <section class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-gradient-to-r from-brand-50 via-white to-gold-50 p-4 shadow-sm sm:p-5">
        <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-200/50 blur-3xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-white shadow-lg"
                    x-bind:class="riderActive
                        ? 'bg-leaf-700 shadow-leaf-700/20'
                        : 'bg-warm-600 shadow-warm-600/20'"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <circle cx="6" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                        <path d="M8 18h8M7 16l2-6h6l3 6" />
                    </svg>
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-black text-warm-950">
                            {{ $isCreateMode
                                ? 'New rider account'
                                : 'Existing rider account' }}
                        </p>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                            x-bind:class="riderActive
                                ? 'bg-leaf-100 text-leaf-700'
                                : 'bg-red-100 text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="riderActive
                                    ? 'bg-leaf-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="riderActive ? 'Active' : 'Inactive'"></span>
                        </span>
                    </div>

                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                        Complete identity, security and account-access settings before saving.
                    </p>
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach (['Rider Details', 'Security', 'Access'] as $index => $step)
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-black text-warm-600 shadow-sm">
                        <span class="grid h-5 w-5 place-items-center rounded-full bg-brand-100 text-[9px] text-brand-600">
                            {{ $index + 1 }}
                        </span>

                        {{ $step }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Validation Summary --}}
    @if ($errors->any())
        <section
            role="alert"
            aria-live="polite"
            class="rounded-[1.5rem] border border-red-200 bg-red-50 p-4 shadow-sm sm:p-5"
        >
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            d="M12 9v4M12 17h.01"
                        />

                        <path
                            stroke-linejoin="round"
                            d="M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z"
                        />
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="font-black text-red-900">
                        Some rider information needs attention
                    </p>

                    <p class="mt-1 text-sm font-semibold text-red-700">
                        Review the highlighted fields and submit the form again.
                    </p>

                    <div class="mt-3 grid gap-1 sm:grid-cols-2">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-start gap-2 text-xs font-semibold leading-5 text-red-700">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>

                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <form
        id="rider-form"
        action="{{ $isCreateMode
            ? route('admin.riders.store')
            : route('admin.riders.update', $rider) }}"
        method="POST"
        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_380px] xl:items-start xl:gap-6"
        x-on:submit="submitting = true"
    >
        @csrf

        @unless ($isCreateMode)
            @method('PUT')
        @endunless

        {{-- Form Content --}}
        <main class="min-w-0 space-y-5">
            {{-- Step 1: Rider Information --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500 text-sm font-black text-white shadow-lg shadow-brand-500/20">
                            1
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Rider Information
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Identity and contact details
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Add accurate information for login, communication and delivery coordination.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    {{-- Name --}}
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-black text-warm-900"
                        >
                            Full Name
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative mt-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-warm-500"
                            >
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21a8 8 0 0 1 16 0" />
                            </svg>

                            <input
                                id="name"
                                name="name"
                                value="{{ $riderName }}"
                                x-model="riderName"
                                required
                                maxlength="120"
                                autocomplete="name"
                                placeholder="Enter rider's full name"
                                class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-12 pr-4 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('name') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >
                        </div>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-warm-500">
                                Use the rider's complete legal or professional name.
                            </p>

                            <span
                                class="shrink-0 text-[10px] font-bold text-warm-500"
                                x-text="`${riderName.length}/120`"
                            ></span>
                        </div>

                        @error('name')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Email --}}
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-black text-warm-900"
                            >
                                Email Address
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative mt-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                                >
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ $riderEmail }}"
                                    x-model="riderEmail"
                                    required
                                    maxlength="180"
                                    autocomplete="email"
                                    inputmode="email"
                                    placeholder="rider@example.com"
                                    class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-4 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('email') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                                >
                            </div>

                            <p class="mt-2 text-xs font-semibold leading-5 text-warm-500">
                                Used to sign in to the rider dashboard.
                            </p>

                            @error('email')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label
                                    for="phone"
                                    class="text-sm font-black text-warm-900"
                                >
                                    Phone Number
                                </label>

                                <span class="text-[10px] font-bold text-warm-500">
                                    Optional
                                </span>
                            </div>

                            <div class="relative mt-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                                >
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                                </svg>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ $riderPhone }}"
                                    x-model="riderPhone"
                                    maxlength="30"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    placeholder="+92 300 0000000"
                                    class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-4 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('phone') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                                >
                            </div>

                            <p class="mt-2 text-xs font-semibold leading-5 text-warm-500">
                                Used for customer and restaurant communication.
                            </p>

                            @error('phone')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Step 2: Security --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Account Security
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Rider login password
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                {{ $isCreateMode
                                    ? 'Create a secure password for rider-dashboard access.'
                                    : 'Only enter a password when the existing password should be replaced.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <label
                            for="password"
                            class="text-sm font-black text-warm-900"
                        >
                            Password

                            @if ($isCreateMode)
                                <span class="text-red-500">*</span>
                            @else
                                <span class="font-semibold text-warm-500">
                                    (optional)
                                </span>
                            @endif
                        </label>

                        <span
                            class="text-[10px] font-black"
                            x-bind:class="passwordTextClass()"
                            x-text="passwordLabel()"
                        ></span>
                    </div>

                    <div class="relative mt-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                        >
                            <rect x="5" y="10" width="14" height="11" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                        </svg>

                        <input
                            id="password"
                            name="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            x-model="password"
                            @required($isCreateMode)
                            autocomplete="new-password"
                            placeholder="{{ $isCreateMode
                                ? 'Create a secure password'
                                : 'Leave blank to keep current password' }}"
                            class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('password') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                        >

                        <button
                            type="button"
                            x-on:click="showPassword = ! showPassword"
                            class="absolute inset-y-0 right-0 grid w-12 place-items-center text-warm-500 transition hover:text-brand-500"
                            x-bind:aria-label="showPassword
                                ? 'Hide password'
                                : 'Show password'"
                        >
                            <svg
                                x-show="! showPassword"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            <svg
                                x-show="showPassword"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="m3 3 18 18"
                                />

                                <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 10 7 10 7a17 17 0 0 1-2.2 3.1" />
                                <path d="M6.6 6.6C3.7 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.5 4.3-1.2" />
                            </svg>
                        </button>
                    </div>

                    {{-- Password Strength --}}
                    <div
                        x-show="password.length > 0"
                        x-cloak
                        class="mt-3"
                    >
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="index in 4" x-bind:key="index">
                                <span
                                    class="h-1.5 rounded-full transition"
                                    x-bind:class="passwordBarClass(index)"
                                ></span>
                            </template>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            <div
                                class="flex items-center gap-2 text-xs font-semibold"
                                x-bind:class="password.length >= 8
                                    ? 'text-leaf-700'
                                    : 'text-warm-500'"
                            >
                                <span
                                    class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                    x-bind:class="password.length >= 8
                                        ? 'bg-leaf-100'
                                        : 'bg-warm-100'"
                                >
                                    <x-ui-icon name="check" class="h-3 w-3" />
                                </span>

                                At least 8 characters
                            </div>

                            <div
                                class="flex items-center gap-2 text-xs font-semibold"
                                x-bind:class="/\d/.test(password)
                                    ? 'text-leaf-700'
                                    : 'text-warm-500'"
                            >
                                <span
                                    class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                    x-bind:class="/\d/.test(password)
                                        ? 'bg-leaf-100'
                                        : 'bg-warm-100'"
                                >
                                    <x-ui-icon name="check" class="h-3 w-3" />
                                </span>

                                Includes a number
                            </div>

                            <div
                                class="flex items-center gap-2 text-xs font-semibold"
                                x-bind:class="/[A-Z]/.test(password)
                                    ? 'text-leaf-700'
                                    : 'text-warm-500'"
                            >
                                <span
                                    class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                    x-bind:class="/[A-Z]/.test(password)
                                        ? 'bg-leaf-100'
                                        : 'bg-warm-100'"
                                >
                                    <x-ui-icon name="check" class="h-3 w-3" />
                                </span>

                                Includes uppercase
                            </div>

                            <div
                                class="flex items-center gap-2 text-xs font-semibold"
                                x-bind:class="/[^A-Za-z0-9]/.test(password)
                                    ? 'text-leaf-700'
                                    : 'text-warm-500'"
                            >
                                <span
                                    class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                    x-bind:class="/[^A-Za-z0-9]/.test(password)
                                        ? 'bg-leaf-100'
                                        : 'bg-warm-100'"
                                >
                                    <x-ui-icon name="check" class="h-3 w-3" />
                                </span>

                                Includes a symbol
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <div class="flex items-start gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-blue-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 11v5M12 8h.01" />
                                </svg>
                            </span>

                            <p class="text-xs font-semibold leading-5 text-blue-800">
                                @if ($isCreateMode)
                                    Share the initial password through a secure channel and ask the rider to change it after signing in.
                                @else
                                    Leaving this field empty preserves the rider’s current password.
                                @endif
                            </p>
                        </div>
                    </div>

                    @error('password')
                        <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>

            {{-- Step 3: Account Access --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Account Access
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Login and assignment availability
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Only active riders should receive new delivery assignments.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <label
                        class="block cursor-pointer rounded-2xl border p-4 transition sm:p-5"
                        x-bind:class="riderActive
                            ? 'border-leaf-100 bg-leaf-50'
                            : 'border-red-200 bg-red-50'"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                    x-bind:class="riderActive
                                        ? 'text-leaf-700'
                                        : 'text-red-600'"
                                >
                                    <svg
                                        x-show="riderActive"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M5 12.5 9.5 17 19 7" />
                                    </svg>

                                    <svg
                                        x-show="! riderActive"
                                        x-cloak
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="m9 9 6 6M15 9l-6 6" />
                                    </svg>
                                </span>

                                <span>
                                    <span
                                        class="block text-sm font-black"
                                        x-bind:class="riderActive
                                            ? 'text-leaf-900'
                                            : 'text-red-950'"
                                        x-text="riderActive
                                            ? 'Active rider account'
                                            : 'Rider account disabled'"
                                    ></span>

                                    <span
                                        class="mt-1 block text-xs font-semibold leading-5"
                                        x-bind:class="riderActive
                                            ? 'text-leaf-700'
                                            : 'text-red-700'"
                                        x-text="riderActive
                                            ? 'The rider can sign in and receive delivery assignments.'
                                            : 'The rider cannot sign in or receive new assignments.'"
                                    ></span>
                                </span>
                            </div>

                            <span class="relative mt-1 shrink-0">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    x-model="riderActive"
                                    @checked($riderActive)
                                    class="peer sr-only"
                                >

                                <span class="block h-7 w-12 rounded-full bg-warm-300 transition peer-checked:bg-leaf-700 peer-focus:ring-4 peer-focus:ring-leaf-100"></span>

                                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </div>
                    </label>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-warm-50 px-4 py-4">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M4 4h16v16H4z" />
                                    <path d="M8 8h8M8 12h8M8 16h5" />
                                </svg>
                            </span>

                            <p class="mt-3 text-xs font-black text-warm-950">
                                Assigned Orders
                            </p>

                            <p class="mt-1 text-[10px] font-semibold leading-4 text-warm-500">
                                Review assigned deliveries.
                            </p>
                        </div>

                        <div class="rounded-xl bg-warm-50 px-4 py-4">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-blue-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                    <circle cx="12" cy="9" r="2.5" />
                                </svg>
                            </span>

                            <p class="mt-3 text-xs font-black text-warm-950">
                                Customer Details
                            </p>

                            <p class="mt-1 text-[10px] font-semibold leading-4 text-warm-500">
                                View contact and address.
                            </p>
                        </div>

                        <div class="rounded-xl bg-warm-50 px-4 py-4">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>

                            <p class="mt-3 text-xs font-black text-warm-950">
                                Update Progress
                            </p>

                            <p class="mt-1 text-[10px] font-semibold leading-4 text-warm-500">
                                Update delivery status.
                            </p>
                        </div>
                    </div>

                    @error('is_active')
                        <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>
        </main>

        {{-- Preview and Actions --}}
        <aside class="space-y-5 xl:sticky xl:top-24">
            {{-- Rider Profile Preview --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="relative overflow-hidden bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 px-5 py-6 text-white sm:px-6 sm:py-7">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-brand-500/30 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-16 left-4 h-44 w-44 rounded-full bg-red-500/15 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-200">
                                    Rider Preview
                                </p>

                                <p class="mt-1 text-sm font-black text-white">
                                    Delivery profile
                                </p>
                            </div>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] backdrop-blur"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full"
                                    x-bind:class="riderActive
                                        ? 'bg-leaf-500'
                                        : 'bg-red-400'"
                                ></span>

                                <span x-text="riderActive ? 'Active' : 'Inactive'"></span>
                            </span>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <span
                                class="grid h-20 w-20 shrink-0 place-items-center rounded-[1.5rem] border border-white/20 bg-white text-2xl font-black text-brand-500 shadow-xl"
                                x-text="initials()"
                            ></span>

                            <div class="min-w-0">
                                <h2
                                    class="truncate text-2xl font-black tracking-tight"
                                    x-text="riderName.trim() || 'Rider Name'"
                                ></h2>

                                <p class="mt-1 text-sm font-semibold text-warm-300">
                                    Arcade Kebab House Delivery Rider
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="space-y-2">
                        <div class="flex items-start gap-3 rounded-xl bg-warm-50 px-3 py-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-brand-500 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                    Login Email
                                </p>

                                <p
                                    class="mt-1 truncate text-xs font-black text-warm-950"
                                    x-text="riderEmail.trim() || 'No email entered'"
                                ></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-xl bg-warm-50 px-3 py-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-blue-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                    Phone Number
                                </p>

                                <p
                                    class="mt-1 truncate text-xs font-black text-warm-950"
                                    x-text="riderPhone.trim() || 'Not provided'"
                                ></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                            <span class="text-xs font-semibold text-warm-500">
                                Account type
                            </span>

                            <span class="text-xs font-black text-warm-950">
                                {{ $isCreateMode ? 'New account' : 'Existing account' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-4 rounded-xl px-4 py-3 text-center text-xs font-black"
                        x-bind:class="riderActive
                            ? 'bg-leaf-50 text-leaf-700'
                            : 'bg-red-50 text-red-700'"
                        x-text="riderActive
                            ? 'Eligible for delivery assignments'
                            : 'Account access is disabled'"
                    ></div>
                </div>
            </section>

            {{-- Readiness Summary --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Account Readiness
                </p>

                <h2 class="mt-1 text-lg font-black text-warm-950">
                    Before saving
                </h2>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Rider name
                        </span>

                        <span
                            class="text-xs font-black"
                            x-bind:class="riderName.trim()
                                ? 'text-leaf-700'
                                : 'text-red-600'"
                            x-text="riderName.trim()
                                ? 'Added'
                                : 'Required'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Login email
                        </span>

                        <span
                            class="text-xs font-black"
                            x-bind:class="riderEmail.trim()
                                ? 'text-leaf-700'
                                : 'text-red-600'"
                            x-text="riderEmail.trim()
                                ? 'Added'
                                : 'Required'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Password
                        </span>

                        <span class="text-xs font-black text-warm-600">
                            @if ($isCreateMode)
                                <span
                                    x-text="password.length
                                        ? 'Entered'
                                        : 'Required'"
                                    x-bind:class="password.length
                                        ? 'text-leaf-700'
                                        : 'text-red-600'"
                                ></span>
                            @else
                                Optional update
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Account access
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black"
                            x-bind:class="riderActive
                                ? 'text-leaf-700'
                                : 'text-red-600'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="riderActive
                                    ? 'bg-leaf-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="riderActive ? 'Active' : 'Disabled'"></span>
                        </span>
                    </div>
                </div>
            </section>

            {{-- Desktop Actions --}}
            <section class="hidden rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm xl:block">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="M5 5h12l2 2v12H5z" />
                            <path d="M8 5v5h8V5M8 19v-6h8v6" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-sm font-black text-warm-950">
                            Ready to save?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                            Confirm the email, password and account status before continuing.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <svg
                        x-show="submitting"
                        x-cloak
                        class="h-5 w-5 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                        ></path>
                    </svg>

                    <svg
                        x-show="! submitting"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M5 5h12l2 2v12H5z" />
                        <path d="M8 5v5h8V5M8 19v-6h8v6" />
                    </svg>

                    <span
                        x-text="submitting
                            ? 'Saving rider...'
                            : @js($submitLabel)"
                    ></span>
                </button>

                <a
                    href="{{ route('admin.riders.index') }}"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                >
                    Cancel
                </a>
            </section>
        </aside>
    </form>

    {{-- Persistent Mobile and Tablet Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.riders.index') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
                aria-label="Cancel and return to riders"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>

            <button
                type="submit"
                form="rider-form"
                x-bind:disabled="submitting"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="submitting"
                    x-cloak
                    class="h-5 w-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                    ></path>
                </svg>

                <svg
                    x-show="! submitting"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M5 5h12l2 2v12H5z" />
                    <path d="M8 5v5h8V5M8 19v-6h8v6" />
                </svg>

                <span
                    x-text="submitting
                        ? 'Saving rider...'
                        : @js($submitLabel)"
                ></span>
            </button>
        </div>
    </div>
</div>

@endcomponent
