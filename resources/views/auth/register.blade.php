@component('layouts.auth', ['title' => 'Register']) <div class="text-center"> <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 text-sm font-black text-white shadow-lg shadow-orange-600/25">
FB </div>

    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600">
        Create Account
    </p>

    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
        Start ordering today
    </h1>

    <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        Create a customer account to add items to cart, place orders, and track delivery progress.
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

<form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="name" class="block text-sm font-black text-slate-800">
            Full Name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name') }}"
            required
            placeholder="Enter your full name"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('name')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

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
        <label for="phone" class="block text-sm font-black text-slate-800">
            Phone Number
        </label>

        <input
            id="phone"
            name="phone"
            type="text"
            value="{{ old('phone') }}"
            placeholder="Enter your phone number"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
        >

        @error('phone')
            <p class="mt-2 text-sm font-semibold text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="block text-sm font-black text-slate-800">
                Password
            </label>

            <input
                id="password"
                name="password"
                type="password"
                required
                placeholder="Create password"
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
                Confirm Password
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                placeholder="Repeat password"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
            >
        </div>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4">
        <p class="text-sm font-semibold leading-6 text-slate-700">
            New accounts are created as <span class="font-black text-orange-700">customers</span> by default. Admin and rider accounts are managed by the restaurant owner.
        </p>
    </div>

    <button
        type="submit"
        class="w-full rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl hover:shadow-orange-600/25"
    >
        Create Customer Account
    </button>
</form>

<div class="mt-7 rounded-2xl border border-orange-100 bg-orange-50 px-5 py-4 text-center">
    <p class="text-sm font-semibold text-slate-700">
        Already registered?
        <a href="{{ route('login') }}" class="font-black text-orange-700 transition hover:text-orange-800">
            Login here
        </a>
    </p>
</div>

@endcomponent
