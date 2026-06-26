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

$roleLabel = match ($user->role) {
    'admin' => 'Administrator',
    'rider' => 'Delivery Rider',
    default => 'Customer',
};

$roleDescription = match ($user->role) {
    'admin' => 'Full restaurant management access',
    'rider' => 'Delivery dashboard access',
    default => 'Customer ordering account',
};

$roleTheme = match ($user->role) {
    'admin' => [
        'badge' => 'bg-violet-50 text-violet-700 border-violet-100',
        'icon' => 'bg-violet-100 text-violet-700',
    ],

    'rider' => [
        'badge' => 'bg-blue-50 text-blue-700 border-blue-100',
        'icon' => 'bg-blue-100 text-blue-700',
    ],

    default => [
        'badge' => 'bg-brand-50 text-brand-600 border-warm-200',
        'icon' => 'bg-brand-100 text-brand-600',
    ],
};

$initials = collect(
    preg_split('/\s+/', trim($user->name ?? ''))
)
    ->filter()
    ->take(2)
    ->map(
        fn ($part) => mb_strtoupper(
            mb_substr($part, 0, 1)
        )
    )
    ->implode('');

$initials = $initials ?: 'U';

@endphp

@component($layout, ['title' => 'Account Security'])
<div
x-data="{
currentPassword: '',
newPassword: '',
passwordConfirmation: '',

        showCurrentPassword: false,
        showNewPassword: false,
        showConfirmation: false,

        submitting: false,

        passwordScore() {
            let score = 0;

            if (this.newPassword.length >= 8) score++;
            if (
                /[a-z]/.test(this.newPassword)
                && /[A-Z]/.test(this.newPassword)
            ) score++;

            if (/\d/.test(this.newPassword)) score++;
            if (/[^A-Za-z0-9]/.test(this.newPassword)) score++;

            return score;
        },

        passwordLabel() {
            if (! this.newPassword.length) {
                return 'Not entered';
            }

            return [
                'Very weak',
                'Weak',
                'Fair',
                'Strong',
                'Very strong'
            ][this.passwordScore()];
        },

        passwordTextClass() {
            const score = this.passwordScore();

            if (! this.newPassword.length) {
                return 'text-warm-500';
            }

            if (score <= 1) {
                return 'text-red-600';
            }

            if (score === 2) {
                return 'text-gold-500';
            }

            if (score === 3) {
                return 'text-blue-600';
            }

            return 'text-leaf-700';
        },

        passwordBarClass(index) {
            const score = this.passwordScore();

            if (index > score) {
                return 'bg-warm-200';
            }

            if (score <= 1) {
                return 'bg-red-500';
            }

            if (score === 2) {
                return 'bg-gold-500';
            }

            if (score === 3) {
                return 'bg-blue-500';
            }

            return 'bg-leaf-500';
        },

        passwordsMatch() {
            return this.passwordConfirmation.length > 0
                && this.newPassword === this.passwordConfirmation;
        },

        confirmationState() {
            if (! this.passwordConfirmation.length) {
                return 'Confirm your new password';
            }

            return this.passwordsMatch()
                ? 'Passwords match'
                : 'Passwords do not match';
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Header --}}
    <header class="xl:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Account Protection
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-warm-950">
                    Security settings
                </h1>

                <p class="mt-1 text-sm font-semibold leading-5 text-warm-500">
                    Protect your account with a secure password.
                </p>
            </div>

            <a
                href="{{ $dashboardRoute }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
                aria-label="Back to dashboard"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.25"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>
        </div>
    </header>

    {{-- Desktop Hero --}}
    <header class="relative hidden overflow-hidden rounded-[2rem] bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 p-8 text-white shadow-2xl shadow-warm-950/20 xl:block">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-20 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative flex items-end justify-between gap-8">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-3.5 w-3.5 text-brand-200"
                        >
                            <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>

                        Account Protection
                    </span>

                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-brand-200">
                        {{ $roleLabel }}
                    </span>
                </div>

                <h1 class="mt-4 text-4xl font-black tracking-tight">
                    Security settings
                </h1>

                <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-warm-300">
                    Update the password used to access
                    <span class="font-black text-white">
                        {{ $user->email }}
                    </span>.
                </p>
            </div>

            <a
                href="{{ $dashboardRoute }}"
                class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/20"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-4 w-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>

                Back to Dashboard
            </a>
        </div>
    </header>

    {{-- Mobile Security Summary --}}
    <section class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-gradient-to-r from-brand-50 via-white to-gold-50 p-4 shadow-sm xl:hidden">
        <div class="pointer-events-none absolute -right-12 -top-16 h-40 w-40 rounded-full bg-brand-200/50 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-black text-warm-950">
                        Password protection
                    </p>

                    <span class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] {{ $roleTheme['badge'] }}">
                        {{ $roleLabel }}
                    </span>
                </div>

                <p class="mt-1 truncate text-xs font-semibold text-warm-500">
                    {{ $user->email }}
                </p>
            </div>
        </div>
    </section>

    {{-- Success Message --}}
    @if (session('status'))
        <section
            role="status"
            aria-live="polite"
            class="rounded-[1.5rem] border border-leaf-100 bg-leaf-50 p-4 shadow-sm"
        >
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m5 12 4 4L19 6"
                        />
                    </svg>
                </span>

                <div>
                    <p class="font-black text-leaf-900">
                        Password updated
                    </p>

                    <p class="mt-1 text-sm font-semibold leading-5 text-leaf-700">
                        {{ session('status') }}
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- Validation Summary --}}
    @if ($errors->any())
        <section
            role="alert"
            aria-live="assertive"
            class="rounded-[1.5rem] border border-red-200 bg-red-50 p-4 shadow-sm sm:p-5"
        >
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M12 9v4M12 17h.01" />
                        <path d="M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z" />
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="font-black text-red-900">
                        Password could not be changed
                    </p>

                    <p class="mt-1 text-sm font-semibold text-red-700">
                        Review the highlighted fields and try again.
                    </p>

                    <div class="mt-3 grid gap-1 sm:grid-cols-2">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-start gap-2 text-xs font-semibold leading-5 text-red-700">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>

                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-start xl:gap-6">
        {{-- Password Form --}}
        <main>
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <rect x="5" y="10" width="14" height="11" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Change Password
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Create a new login password
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Confirm your identity with the current password before setting a new one.
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    id="password-update-form"
                    action="{{ route('account.password.update') }}"
                    method="POST"
                    class="space-y-6 p-4 sm:p-6"
                    x-on:submit="submitting = true"
                >
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label
                            for="current_password"
                            class="block text-sm font-black text-warm-900"
                        >
                            Current Password
                            <span class="text-red-500">*</span>
                        </label>

                        <p class="mt-1 text-xs font-semibold text-warm-500">
                            Enter the password you currently use to sign in.
                        </p>

                        <div class="relative mt-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                            >
                                <rect x="5" y="10" width="14" height="11" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>

                            <input
                                id="current_password"
                                name="current_password"
                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                x-model="currentPassword"
                                required
                                autocomplete="current-password"
                                placeholder="Enter current password"
                                class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('current_password') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <button
                                type="button"
                                x-on:click="showCurrentPassword = ! showCurrentPassword"
                                class="absolute inset-y-0 right-0 grid w-12 place-items-center text-warm-500 transition hover:text-brand-500"
                                x-bind:aria-label="showCurrentPassword
                                    ? 'Hide current password'
                                    : 'Show current password'"
                            >
                                <svg
                                    x-show="! showCurrentPassword"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>

                                <svg
                                    x-show="showCurrentPassword"
                                    x-cloak
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="m3 3 18 18"
                                    />
                                    <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 10 7 10 7a17 17 0 0 1-2.2 3.1" />
                                    <path d="M6.6 6.6C3.7 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.5 4.3-1.2" />
                                </svg>
                            </button>
                        </div>

                        @error('current_password')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="border-t border-warm-100 pt-6">
                        <div class="mb-5">
                            <p class="text-sm font-black text-warm-950">
                                New password
                            </p>

                            <p class="mt-1 text-xs font-semibold text-warm-500">
                                Use a password that is different from your current one.
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- New Password --}}
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <label
                                        for="password"
                                        class="text-sm font-black text-warm-900"
                                    >
                                        New Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <span
                                        class="text-[10px] font-black"
                                        x-bind:class="passwordTextClass()"
                                        x-text="passwordLabel()"
                                    ></span>
                                </div>

                                <div class="relative mt-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                                    >
                                        <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                                    </svg>

                                    <input
                                        id="password"
                                        name="password"
                                        x-bind:type="showNewPassword ? 'text' : 'password'"
                                        x-model="newPassword"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Create a secure password"
                                        class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('password') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="showNewPassword = ! showNewPassword"
                                        class="absolute inset-y-0 right-0 grid w-12 place-items-center text-warm-500 transition hover:text-brand-500"
                                        x-bind:aria-label="showNewPassword
                                            ? 'Hide new password'
                                            : 'Show new password'"
                                    >
                                        <svg
                                            x-show="! showNewPassword"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>

                                        <svg
                                            x-show="showNewPassword"
                                            x-cloak
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                d="m3 3 18 18"
                                            />
                                            <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 10 7 10 7a17 17 0 0 1-2.2 3.1" />
                                            <path d="M6.6 6.6C3.7 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.5 4.3-1.2" />
                                        </svg>
                                    </button>
                                </div>

                                @error('password')
                                    <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Confirmation --}}
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <label
                                        for="password_confirmation"
                                        class="text-sm font-black text-warm-900"
                                    >
                                        Confirm Password
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <span
                                        x-show="passwordConfirmation.length > 0"
                                        x-cloak
                                        class="text-[10px] font-black"
                                        x-bind:class="passwordsMatch()
                                            ? 'text-leaf-700'
                                            : 'text-red-600'"
                                        x-text="confirmationState()"
                                    ></span>
                                </div>

                                <div class="relative mt-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>

                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        x-bind:type="showConfirmation ? 'text' : 'password'"
                                        x-model="passwordConfirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Repeat new password"
                                        class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                        x-bind:class="passwordConfirmation.length
                                            ? (
                                                passwordsMatch()
                                                    ? 'border-leaf-500 focus:border-leaf-500 focus:ring-leaf-100'
                                                    : 'border-red-300 focus:border-red-400 focus:ring-red-100'
                                            )
                                            : 'border-warm-200 focus:border-brand-500'"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="showConfirmation = ! showConfirmation"
                                        class="absolute inset-y-0 right-0 grid w-12 place-items-center text-warm-500 transition hover:text-brand-500"
                                        x-bind:aria-label="showConfirmation
                                            ? 'Hide password confirmation'
                                            : 'Show password confirmation'"
                                    >
                                        <svg
                                            x-show="! showConfirmation"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>

                                        <svg
                                            x-show="showConfirmation"
                                            x-cloak
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                d="m3 3 18 18"
                                            />
                                            <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 10 7 10 7a17 17 0 0 1-2.2 3.1" />
                                            <path d="M6.6 6.6C3.7 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.5 4.3-1.2" />
                                        </svg>
                                    </button>
                                </div>

                                <p
                                    class="mt-2 text-xs font-semibold"
                                    x-bind:class="passwordConfirmation.length
                                        ? (
                                            passwordsMatch()
                                                ? 'text-leaf-700'
                                                : 'text-red-600'
                                        )
                                        : 'text-warm-500'"
                                    x-text="confirmationState()"
                                ></p>
                            </div>
                        </div>

                        {{-- Strength Feedback --}}
                        <div
                            x-show="newPassword.length > 0"
                            x-cloak
                            class="mt-5 rounded-2xl border border-warm-100 bg-warm-50 p-4"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-xs font-black uppercase tracking-[0.12em] text-warm-500">
                                    Password strength
                                </p>

                                <p
                                    class="text-xs font-black"
                                    x-bind:class="passwordTextClass()"
                                    x-text="passwordLabel()"
                                ></p>
                            </div>

                            <div class="mt-3 grid grid-cols-4 gap-1.5">
                                <template x-for="index in 4" x-bind:key="index">
                                    <span
                                        class="h-1.5 rounded-full transition"
                                        x-bind:class="passwordBarClass(index)"
                                    ></span>
                                </template>
                            </div>

                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                <div
                                    class="flex items-center gap-2 text-xs font-semibold"
                                    x-bind:class="newPassword.length >= 8
                                        ? 'text-leaf-700'
                                        : 'text-warm-500'"
                                >
                                    <span
                                        class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                        x-bind:class="newPassword.length >= 8
                                            ? 'bg-leaf-100'
                                            : 'bg-warm-200'"
                                    >
                                        ✓
                                    </span>

                                    At least 8 characters
                                </div>

                                <div
                                    class="flex items-center gap-2 text-xs font-semibold"
                                    x-bind:class="/[A-Z]/.test(newPassword)
                                        && /[a-z]/.test(newPassword)
                                        ? 'text-leaf-700'
                                        : 'text-warm-500'"
                                >
                                    <span
                                        class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                        x-bind:class="/[A-Z]/.test(newPassword)
                                            && /[a-z]/.test(newPassword)
                                            ? 'bg-leaf-100'
                                            : 'bg-warm-200'"
                                    >
                                        ✓
                                    </span>

                                    Uppercase and lowercase
                                </div>

                                <div
                                    class="flex items-center gap-2 text-xs font-semibold"
                                    x-bind:class="/\d/.test(newPassword)
                                        ? 'text-leaf-700'
                                        : 'text-warm-500'"
                                >
                                    <span
                                        class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                        x-bind:class="/\d/.test(newPassword)
                                            ? 'bg-leaf-100'
                                            : 'bg-warm-200'"
                                    >
                                        ✓
                                    </span>

                                    Includes a number
                                </div>

                                <div
                                    class="flex items-center gap-2 text-xs font-semibold"
                                    x-bind:class="/[^A-Za-z0-9]/.test(newPassword)
                                        ? 'text-leaf-700'
                                        : 'text-warm-500'"
                                >
                                    <span
                                        class="grid h-4 w-4 place-items-center rounded-full text-[9px]"
                                        x-bind:class="/[^A-Za-z0-9]/.test(newPassword)
                                            ? 'bg-leaf-100'
                                            : 'bg-warm-200'"
                                    >
                                        ✓
                                    </span>

                                    Includes a symbol
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop Form Actions --}}
                    <div class="hidden items-center justify-between gap-4 border-t border-warm-100 pt-6 xl:flex">
                        <p class="max-w-md text-xs font-semibold leading-5 text-warm-500">
                            Changing your password may require you to sign in again on other devices.
                        </p>

                        <button
                            type="submit"
                            x-bind:disabled="submitting"
                            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <svg
                                x-show="submitting"
                                x-cloak
                                class="h-5 w-5 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                                ></path>
                            </svg>

                            <svg
                                x-show="! submitting"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>

                            <span
                                x-text="submitting
                                    ? 'Updating password...'
                                    : 'Change Password'"
                            ></span>
                        </button>
                    </div>
                </form>
            </section>
        </main>

        {{-- Account Sidebar --}}
        <aside class="space-y-5 xl:sticky xl:top-24">
            {{-- Account Card --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="relative overflow-hidden bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 px-5 py-6 text-white">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-brand-500/30 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-200">
                                Signed-in Account
                            </p>

                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em]">
                                <span class="h-1.5 w-1.5 rounded-full bg-leaf-500"></span>
                                Active
                            </span>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white text-xl font-black text-brand-500 shadow-xl">
                                {{ $initials }}
                            </span>

                            <div class="min-w-0">
                                <h2 class="truncate text-xl font-black tracking-tight">
                                    {{ $user->name }}
                                </h2>

                                <p class="mt-1 truncate text-xs font-semibold text-warm-300">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="flex items-center gap-3 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $roleTheme['icon'] }}">
                            @if ($user->role === 'admin')
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                                </svg>
                            @elseif ($user->role === 'rider')
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <circle cx="6" cy="18" r="2" />
                                    <circle cx="18" cy="18" r="2" />
                                    <path d="M8 18h8M7 16l2-6h6l3 6" />
                                </svg>
                            @else
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                            @endif
                        </span>

                        <div class="min-w-0">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] {{ $roleTheme['badge'] }}">
                                {{ $roleLabel }}
                            </span>

                            <p class="mt-1 text-xs font-semibold text-warm-500">
                                {{ $roleDescription }}
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ $dashboardRoute }}"
                        class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-black text-brand-600 transition hover:bg-brand-100"
                    >
                        Return to Dashboard

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6"
                            />
                        </svg>
                    </a>
                </div>
            </section>

            {{-- Security Advice --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-leaf-50 text-leaf-700">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-sm font-black text-warm-950">
                            Password recommendations
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                            Follow these practices to keep the account secure.
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-leaf-50 text-[10px] font-black text-leaf-700">
                            1
                        </span>

                        <p class="text-xs font-semibold leading-5 text-warm-600">
                            Use at least eight characters with mixed character types.
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="mt-1 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-leaf-50 text-[10px] font-black text-leaf-700">
                            2
                        </span>

                        <p class="text-xs font-semibold leading-5 text-warm-600">
                            Avoid using your name, email address, or common words.
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="mt-1 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-leaf-50 text-[10px] font-black text-leaf-700">
                            3
                        </span>

                        <p class="text-xs font-semibold leading-5 text-warm-600">
                            Do not reuse a password from another website or application.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Security Notice --}}
            <section class="rounded-[1.75rem] border border-blue-100 bg-blue-50 p-5">
                <div class="flex items-start gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-blue-600 shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v5M12 8h.01" />
                        </svg>
                    </span>

                    <p class="text-xs font-semibold leading-5 text-blue-800">
                        Arcade Kebab House will never ask you to send your password through email, messages, or customer support.
                    </p>
                </div>
            </section>
        </aside>
    </div>

    {{-- Persistent Mobile and Tablet Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ $dashboardRoute }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
                aria-label="Back to dashboard"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>

            <button
                type="submit"
                form="password-update-form"
                x-bind:disabled="submitting"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="submitting"
                    x-cloak
                    class="h-5 w-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                    ></path>
                </svg>

                <svg
                    x-show="! submitting"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>

                <span
                    x-text="submitting
                        ? 'Updating password...'
                        : 'Change Password'"
                ></span>
            </button>
        </div>
    </div>
</div>

@endcomponent
