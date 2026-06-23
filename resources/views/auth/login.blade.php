@component('layouts.auth', ['title' => 'Login']) <div class="text-center"> <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
FB </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
        Welcome Back
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
        Login to your account
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        Access your customer dashboard, rider panel, or restaurant owner area.
    </p>
</div>

@if (session('status'))
    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
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
        <label for="email" class="block text-sm font-black text-slate-800">
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
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('email')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between gap-3">
            <label for="password" class="block text-sm font-black text-slate-800">
                Password
            </label>

            <a href="{{ route('password.request') }}" class="text-xs font-black text-orange-700 transition hover:text-orange-800">
                Forgot?
            </a>
        </div>

        <input
            id="password"
            name="password"
            type="password"
            required
            placeholder="Enter your password"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('password')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
        <input
            type="checkbox"
            name="remember"
            class="rounded border-slate-300 text-orange-600 focus:ring-orange-500"
        >
        Remember me on this device
    </label>

    <button
        type="submit"
        class="w-full rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
    >
        Login
    </button>
</form>

<div class="mt-7 rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-slate-700">
        Need an account?
        <a href="{{ route('register') }}" class="font-black text-orange-700 transition hover:text-orange-800">
            Create customer account
        </a>
    </p>
</div>

@endcomponent
