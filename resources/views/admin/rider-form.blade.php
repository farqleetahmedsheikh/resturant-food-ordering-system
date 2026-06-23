@component('layouts.admin', ['title' => $mode === 'create' ? 'Create Rider' : 'Edit Rider'])
@php
$isCreateMode = $mode === 'create';
$pageTitle = $isCreateMode ? 'Create rider' : 'Edit rider';
$submitLabel = $isCreateMode ? 'Create Rider Account' : 'Save Rider Changes';

    $riderName = old('name', $rider->name ?? '');
    $riderEmail = old('email', $rider->email ?? '');
    $riderPhone = old('phone', $rider->phone ?? '');
    $riderActive = (bool) old('is_active', $rider->is_active ?? true);
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Delivery Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            {{ $pageTitle }}
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            {{ $isCreateMode
                ? 'Create a secure rider account that can access assigned deliveries and update delivery progress.'
                : 'Update rider contact information, account access, password, and availability status.' }}
        </p>
    </div>

    <a
        href="{{ route('admin.riders.index') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
        </svg>

        Back to Riders
    </a>
</div>

{{-- Validation Summary --}}
@if ($errors->any())
    <div class="mb-7 rounded-[1.5rem] border border-red-200 bg-red-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" d="M12 9v4M12 17h.01" />
                    <path stroke-linejoin="round" d="M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z" />
                </svg>
            </div>

            <div>
                <p class="font-black text-red-800">
                    Please review the rider information
                </p>

                <ul class="mt-2 list-inside list-disc space-y-1 text-sm font-semibold text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<form
    action="{{ $isCreateMode
        ? route('admin.riders.store')
        : route('admin.riders.update', $rider) }}"
    method="POST"
    class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_380px]"
    x-data="{
        riderName: @js($riderName),
        riderEmail: @js($riderEmail),
        riderPhone: @js($riderPhone),
        riderActive: @js($riderActive),
        showPassword: false
    }"
>
    @csrf

    @unless ($isCreateMode)
        @method('PUT')
    @endunless

    {{-- Main Form --}}
    <div class="space-y-7">
        {{-- Personal Information --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="12" cy="8" r="4" />
                        <path stroke-linecap="round" d="M4 21c0-4 3.5-7 8-7s8 3 8 7" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Personal Information
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Rider details
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Add accurate contact details so the restaurant and customers can reach the rider.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Name --}}
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-black text-slate-800">
                        Full Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ $riderName }}"
                        x-model="riderName"
                        required
                        autocomplete="name"
                        placeholder="Enter rider's full name"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-black text-slate-800">
                        Email Address
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ $riderEmail }}"
                        x-model="riderEmail"
                        required
                        autocomplete="email"
                        placeholder="rider@example.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        The rider will use this email to log in.
                    </p>

                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-black text-slate-800">
                        Phone Number
                    </label>

                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ $riderPhone }}"
                        x-model="riderPhone"
                        autocomplete="tel"
                        placeholder="+92 300 0000000"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Used for delivery coordination and customer contact.
                    </p>

                    @error('phone')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Account Security --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <rect x="5" y="10" width="14" height="11" rx="2" />
                        <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                        <circle cx="12" cy="15" r="1" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Account Security
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Login password
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $isCreateMode
                            ? 'Create a secure password for the rider dashboard.'
                            : 'Enter a new password only when the rider’s current password should be changed.' }}
                    </p>
                </div>
            </div>

            <div class="mt-7">
                <label for="password" class="block text-sm font-black text-slate-800">
                    Password
                    @if ($isCreateMode)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="font-semibold text-slate-400">(optional)</span>
                    @endif
                </label>

                <div class="relative mt-2">
                    <input
                        id="password"
                        name="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        @required($isCreateMode)
                        autocomplete="new-password"
                        placeholder="{{ $isCreateMode ? 'Create a secure password' : 'Leave blank to keep current password' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <button
                        type="button"
                        x-on:click="showPassword = ! showPassword"
                        class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-orange-600"
                        aria-label="Show or hide password"
                    >
                        <svg
                            x-show="! showPassword"
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
                            x-show="showPassword"
                            x-cloak
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path stroke-linecap="round" d="m3 3 18 18" />
                            <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 10 7 10 7a17 17 0 0 1-2.2 3.1M6.6 6.6C3.7 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.5 4.3-1.2" />
                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="mt-0.5 h-5 w-5 shrink-0 text-blue-600"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 11v5M12 8h.01" />
                        </svg>

                        <p class="text-xs font-semibold leading-6 text-blue-800">
                            @if ($isCreateMode)
                                Use a strong password containing letters, numbers, and special characters. Share it securely with the rider.
                            @else
                                Leave this field empty to preserve the rider’s current password.
                            @endif
                        </p>
                    </div>
                </div>

                @error('password')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </section>

        {{-- Account Status --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Account Status
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Rider availability
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Only active riders should be available for new delivery assignments.
                    </p>
                </div>
            </div>

            <label class="mt-7 flex cursor-pointer items-center justify-between gap-5 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200">
                <span class="flex min-w-0 items-start gap-4">
                    <span
                        class="mt-1 h-3 w-3 shrink-0 rounded-full"
                        x-bind:class="riderActive ? 'bg-emerald-500' : 'bg-red-500'"
                    ></span>

                    <span>
                        <span class="block text-sm font-black text-slate-950">
                            Active Rider Account
                        </span>

                        <span
                            class="mt-1 block text-xs font-semibold leading-5 text-slate-500"
                            x-text="riderActive
                                ? 'The rider can log in and receive delivery assignments.'
                                : 'The rider account is disabled and should not receive new assignments.'"
                        ></span>
                    </span>
                </span>

                <span class="relative shrink-0">
                    <input type="hidden" name="is_active" value="0">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        x-model="riderActive"
                        @checked($riderActive)
                        class="peer sr-only"
                    >

                    <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-100"></span>

                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                </span>
            </label>

            @error('is_active')
                <p class="mt-2 text-sm font-semibold text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </section>
    </div>

    {{-- Rider Preview and Actions --}}
    <aside class="h-fit space-y-5 xl:sticky xl:top-28">
        {{-- Rider Profile Preview --}}
        <section class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
            <div class="relative overflow-hidden bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 px-6 py-8 text-white">
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
                <div class="absolute -bottom-16 left-4 h-44 w-44 rounded-full bg-yellow-200/20 blur-3xl"></div>

                <div class="relative">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-100">
                            Rider Profile
                        </p>

                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-xs font-black backdrop-blur"
                        >
                            <span
                                class="h-2 w-2 rounded-full"
                                x-bind:class="riderActive ? 'bg-emerald-300' : 'bg-red-300'"
                            ></span>

                            <span x-text="riderActive ? 'Active' : 'Inactive'"></span>
                        </span>
                    </div>

                    <div class="mt-7 flex items-center gap-4">
                        <div class="grid h-20 w-20 shrink-0 place-items-center rounded-[1.5rem] border border-white/30 bg-white text-3xl font-black text-orange-600 shadow-xl">
                            <span x-text="riderName ? riderName.charAt(0).toUpperCase() : 'R'"></span>
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="truncate text-2xl font-black tracking-tight"
                                x-text="riderName || 'Rider Name'"
                            ></h2>

                            <p class="mt-1 text-sm font-semibold text-orange-50">
                                FreshBite Delivery Rider
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                            Email Address
                        </p>

                        <p
                            class="mt-1 break-all text-sm font-black text-slate-950"
                            x-text="riderEmail || 'No email entered'"
                        ></p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                            Phone Number
                        </p>

                        <p
                            class="mt-1 break-all text-sm font-black text-slate-950"
                            x-text="riderPhone || 'No phone entered'"
                        ></p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                            Account Mode
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-950">
                            {{ $isCreateMode ? 'New rider account' : 'Existing rider account' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Rider Access Information --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M3 7h11v10H3z" />
                        <path d="M14 10h4l3 3v4h-7z" />
                        <circle cx="7" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                    </svg>
                </div>

                <div>
                    <h2 class="font-black text-slate-950">
                        Rider dashboard access
                    </h2>

                    <p class="mt-2 text-xs font-semibold leading-6 text-slate-600">
                        Active riders can log in, review assigned orders, view customer delivery details, and update delivery progress.
                    </p>
                </div>
            </div>
        </section>

        {{-- Save Actions --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6" />
                </svg>

                {{ $submitLabel }}
            </button>

            <a
                href="{{ route('admin.riders.index') }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Cancel
            </a>

            <p class="mt-4 text-center text-xs font-semibold leading-5 text-slate-500">
                Confirm the rider’s email and account status before saving.
            </p>
        </section>
    </aside>
</form>

@endcomponent
