@component('layouts.auth', ['title' => 'Verify OTP'])
<div class="text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 text-sm font-black text-white shadow-lg shadow-brand-500/25">
        OTP
    </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-brand-500">
        Verify Code
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-warm-950">
        Enter your OTP
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-warm-600">
        We sent a six-digit code to <span class="font-black text-warm-900">{{ $email }}</span>.
    </p>
</div>

<form action="{{ route('password.otp.verify') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="otp" class="block text-sm font-black text-warm-900">
            One-Time Code
        </label>

        <input
            id="otp"
            name="otp"
            type="text"
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            value="{{ old('otp') }}"
            required
            autofocus
            placeholder="123456"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-center text-2xl font-black tracking-[0.35em] text-warm-900 shadow-sm outline-none transition placeholder:text-warm-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

        @error('otp')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
    >
        Verify OTP
    </button>
</form>

<form action="{{ route('password.otp.send') }}" method="POST" class="mt-4">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">

    <button
        type="submit"
        class="w-full rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:bg-brand-50"
    >
        Send New Code
    </button>
</form>
@endcomponent
