@component('layouts.auth', ['title' => 'Forgot Password'])
<div class="text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
        FB
    </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
        Password Recovery
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
        Get a reset OTP
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        Enter your account email and we will send a one-time password reset code.
    </p>
</div>

<form action="{{ route('password.otp.send') }}" method="POST" class="mt-8 space-y-5">
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

    <button
        type="submit"
        class="w-full rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
    >
        Send OTP
    </button>
</form>

<div class="mt-7 rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-slate-700">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-black text-orange-700 transition hover:text-orange-800">
            Back to login
        </a>
    </p>
</div>
@endcomponent
