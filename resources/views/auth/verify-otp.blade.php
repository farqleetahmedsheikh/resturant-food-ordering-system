@component('layouts.auth', ['title' => 'Verify OTP'])
<div class="text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
        OTP
    </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
        Verify Code
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
        Enter your OTP
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        We sent a six-digit code to <span class="font-black text-slate-900">{{ $email }}</span>.
    </p>
</div>

<form action="{{ route('password.otp.verify') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="otp" class="block text-sm font-black text-slate-800">
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
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-2xl font-black tracking-[0.35em] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('otp')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
    >
        Verify OTP
    </button>
</form>

<form action="{{ route('password.otp.send') }}" method="POST" class="mt-4">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">

    <button
        type="submit"
        class="w-full rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-50"
    >
        Send New Code
    </button>
</form>
@endcomponent
