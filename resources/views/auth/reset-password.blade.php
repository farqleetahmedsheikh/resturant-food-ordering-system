@component('layouts.auth', ['title' => 'New Password'])
<div class="text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
        FB
    </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
        New Password
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
        Create a secure password
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        Your OTP is verified. Set a new password for your FreshBite account.
    </p>
</div>

<form action="{{ route('password.reset.update') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="password" class="block text-sm font-black text-slate-800">
            New Password
        </label>

        <input
            id="password"
            name="password"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Create new password"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('password')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-black text-slate-800">
            Confirm New Password
        </label>

        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Repeat new password"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
    >
        Update Password
    </button>
</form>
@endcomponent
