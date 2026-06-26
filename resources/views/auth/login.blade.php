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

    <div>
        <div class="flex items-center justify-between gap-3">
            <label for="password" class="block text-sm font-black text-warm-900">
                Password
            </label>

            <a href="{{ route('password.request') }}" class="text-xs font-black text-brand-600 transition hover:text-brand-800">
                Forgot?
            </a>
        </div>

        <input
            id="password"
            name="password"
            type="password"
            required
            placeholder="Enter your password"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

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
