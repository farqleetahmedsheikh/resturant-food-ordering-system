@component('layouts.auth', ['title' => 'Forgot Password'])
<div class="text-center">
    <x-brand-mark mark-class="mx-auto h-14 w-14 rounded-2xl" />

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-brand-500">
        Password Recovery
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-warm-950">
        Get a reset OTP
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-warm-600">
        Enter your account email and we will send a one-time password reset code.
    </p>
</div>

<form action="{{ route('password.otp.send') }}" method="POST" class="mt-8 space-y-5">
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

    <button
        type="submit"
        class="w-full rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
    >
        Send OTP
    </button>
</form>

<div class="mt-7 rounded-2xl border border-warm-200 bg-brand-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-warm-600">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-black text-brand-600 transition hover:text-brand-800">
            Back to login
        </a>
    </p>
</div>
@endcomponent
