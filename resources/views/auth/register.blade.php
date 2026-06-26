@component('layouts.auth', ['title' => 'Register']) <div class="text-center"> <x-brand-mark mark-class="mx-auto h-14 w-14 rounded-2xl" />

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-brand-500">
        Create Account
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-warm-950">
        Start ordering today
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-warm-600">
        Create a customer account to add items to cart, place orders, and track delivery progress.
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

<form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="name" class="block text-sm font-black text-warm-900">
            Full Name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name') }}"
            required
            placeholder="Enter your full name"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

        @error('name')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

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
        <label for="phone" class="block text-sm font-black text-warm-900">
            Phone Number
        </label>

        <input
            id="phone"
            name="phone"
            type="text"
            value="{{ old('phone') }}"
            placeholder="Enter your phone number"
            class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
        >

        @error('phone')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="block text-sm font-black text-warm-900">
                Password
            </label>

            <input
                id="password"
                name="password"
                type="password"
                required
                placeholder="Create password"
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
                Confirm Password
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                placeholder="Repeat password"
                class="mt-2 w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
            >
        </div>
    </div>

    <div class="rounded-2xl border border-warm-200 bg-brand-50 px-5 py-4">
        <p class="text-sm font-semibold leading-6 text-warm-600">
            New accounts are created as <span class="font-black text-brand-600">customers</span> by default. Admin and rider accounts are managed by the restaurant owner.
        </p>
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-brand-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/25"
    >
        Create Customer Account
    </button>
</form>

<div class="mt-7 rounded-2xl border border-warm-200 bg-brand-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-warm-600">
        Already registered?
        <a href="{{ route('login') }}" class="font-black text-brand-600 transition hover:text-brand-800">
            Login here
        </a>
    </p>
</div>

@endcomponent
