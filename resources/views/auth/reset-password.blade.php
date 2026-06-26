@component('layouts.auth', ['title' => 'New Password'])
<div class="text-center">
    <x-brand-mark mark-class="mx-auto h-14 w-14 rounded-2xl" />

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-brand-500">
        New Password
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-warm-950">
        Create a secure password
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-warm-600">
        Your OTP is verified. Set a new password for your Arcade Kebab House account.
    </p>
</div>

<form action="{{ route('password.reset.update') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="password" class="block text-sm font-black text-warm-900">
            New Password
        </label>

        <input
            id="password"
            name="password"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Create new password"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

        @error('password')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-black text-warm-900">
            Confirm New Password
        </label>

        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Repeat new password"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
    >
        Update Password
    </button>
</form>
@endcomponent
