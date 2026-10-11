@component('layouts.auth', ['title' => 'Login']) <div class="text-center"> <x-brand-mark mark-class="mx-auto h-14 w-14 rounded-2xl" />

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-brand-500">
        Welcome Back
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-warm-950">
        Login to your account
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-warm-600">
        Access your customer dashboard, rider panel, or restaurant owner area.
    </p>
</div>

@if (session('status'))
    <div class="mt-6 rounded-2xl border border-leaf-100 bg-leaf-50 px-5 py-4 text-sm font-bold text-leaf-700">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-black text-warm-900">
            Email Address
        </label>

        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
            autofocus
            placeholder="you@example.com"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

        @error('email')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div x-data="{ showPassword: false }">
        <div class="flex items-center justify-between gap-3">
            <label for="password" class="block text-sm font-black text-warm-900">
                Password
            </label>

            <a href="{{ route('password.request') }}" class="text-xs font-black text-brand-600 transition hover:text-brand-800">
                Forgot?
            </a>
        </div>

        <div class="relative mt-2">
            <input
                id="password"
                name="password"
                x-bind:type="showPassword ? 'text' : 'password'"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 pr-12 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
            >

            <button
                type="button"
                x-on:click="showPassword = ! showPassword"
                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                x-bind:title="showPassword ? 'Hide password' : 'Show password'"
                class="absolute inset-y-0 right-0 grid w-12 place-items-center text-warm-500 transition hover:text-brand-600 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-brand-100"
            >
                <svg x-show="! showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                    <circle cx="12" cy="12" r="2.5" />
                </svg>
                <svg x-cloak x-show="showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a18.5 18.5 0 0 1-3.1 3.8M6.2 6.7C3.7 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.3 4-.8" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                </svg>
            </button>
        </div>

        @error('password')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <label class="flex items-center gap-3 rounded-2xl border border-warm-200 bg-warm-50 px-4 py-3 text-sm font-bold text-warm-600">
        <input
            type="checkbox"
            name="remember"
            class="rounded border-warm-300 text-brand-500 focus:ring-brand-500"
        >
        Remember me on this device
    </label>

    <button
        type="submit"
        class="w-full rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
    >
        Login
    </button>
</form>

<div class="mt-7 rounded-2xl border border-warm-200 bg-brand-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-warm-600">
        Need an account?
        <a href="{{ route('register') }}" class="font-black text-brand-600 transition hover:text-brand-800">
            Create customer account
        </a>
    </p>
</div>

@endcomponent
