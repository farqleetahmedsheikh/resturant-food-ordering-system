@php
    $layout = match ($user->role) {
        'admin' => 'layouts.admin',
        'rider' => 'layouts.rider',
        default => 'layouts.customer',
    };

    $dashboardRoute = match ($user->role) {
        'admin' => route('admin.dashboard'),
        'rider' => route('rider.dashboard'),
        default => route('customer.dashboard'),
    };
@endphp

@component($layout, ['title' => 'Account Security'])
<section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-6 text-white shadow-2xl shadow-slate-950/20 sm:p-8">
    <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 left-20 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

    <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.24em] text-orange-300">
                Account Protection
            </p>

            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                Security settings
            </h1>

            <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-300">
                Update your login password for {{ $user->email }}.
            </p>
        </div>

        <a
            href="{{ $dashboardRoute }}"
            class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/20"
        >
            Back to Dashboard
        </a>
    </div>
</section>

<section class="mt-7 grid gap-6 lg:grid-cols-[1fr_360px]">
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Change Password
            </p>

            <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">
                Set a new password
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Use your current password to confirm this change.
            </p>
        </div>

        <form action="{{ route('account.password.update') }}" method="POST" class="mt-7 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-black text-slate-800">
                    Current Password
                </label>

                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                >

                @error('current_password')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
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
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 sm:w-auto"
            >
                Change Password
            </button>
        </form>
    </div>

    <aside class="h-fit rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
            Account
        </p>

        <div class="mt-5 rounded-[1.5rem] border border-orange-100 bg-orange-50 p-4">
            <p class="text-sm font-black text-slate-950">
                {{ $user->name }}
            </p>

            <p class="mt-1 text-sm font-semibold text-slate-600">
                {{ $user->email }}
            </p>

            <div class="mt-4 inline-flex rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-wide text-orange-700">
                {{ ucfirst($user->role) }}
            </div>
        </div>

        <div class="mt-5 rounded-[1.5rem] border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-sm font-black text-emerald-800">
                Active security
            </p>

            <p class="mt-2 text-sm font-semibold leading-6 text-emerald-700">
                Rate limiting and temporary IP blocking are enabled for suspicious request bursts.
            </p>
        </div>
    </aside>
</section>
@endcomponent
