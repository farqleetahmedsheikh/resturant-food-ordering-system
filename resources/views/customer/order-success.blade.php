@component('layouts.public', ['title' => 'Order Placed'])
@php
$statusLabel = \App\Models\Order::STATUSES[$order->order_status]
?? \Illuminate\Support\Str::headline($order->order_status);

    $isCancelled = $order->order_status === 'cancelled';
    $isDelivered = $order->order_status === 'delivered';

    $statusMessage = match ($order->order_status) {
        'pending' => 'Your order has been received and is waiting for restaurant confirmation.',
        'accepted' => 'The restaurant has accepted your order.',
        'preparing' => 'Your food is currently being prepared.',
        'ready' => 'Your order is ready for rider pickup.',
        'assigned_to_rider' => 'A delivery rider has been assigned to your order.',
        'out_for_delivery' => 'Your order is on the way to your address.',
        'delivered' => 'Your order was delivered successfully.',
        'cancelled' => 'This order has been cancelled.',
        default => 'Your order is being processed.',
    };

    $statusStyles = match ($order->order_status) {
        'delivered' => [
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
            'panel' => 'border-emerald-100 bg-emerald-50',
            'icon' => 'bg-emerald-600 text-white',
        ],

        'cancelled' => [
            'badge' => 'border-red-200 bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
            'panel' => 'border-red-100 bg-red-50',
            'icon' => 'bg-red-600 text-white',
        ],

        'out_for_delivery', 'assigned_to_rider' => [
            'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
            'dot' => 'bg-blue-500',
            'panel' => 'border-blue-100 bg-blue-50',
            'icon' => 'bg-blue-600 text-white',
        ],

        'accepted', 'preparing', 'ready' => [
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
            'panel' => 'border-amber-100 bg-amber-50',
            'icon' => 'bg-amber-500 text-white',
        ],

        default => [
            'badge' => 'border-orange-200 bg-orange-50 text-orange-700',
            'dot' => 'bg-orange-500',
            'panel' => 'border-orange-100 bg-orange-50',
            'icon' => 'bg-orange-600 text-white',
        ],
    };
@endphp

<main
    x-data="{
        copied: false,

        async copyOrderNumber() {
            try {
                await navigator.clipboard.writeText(@js($order->order_number));
                this.copied = true;

                setTimeout(() => {
                    this.copied = false;
                }, 1800);
            } catch (error) {
                this.copied = false;
            }
        }
    }"
    class="min-h-screen bg-[var(--color-surface-warm)] py-5 sm:py-9 lg:py-14"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        {{-- Mobile Top Bar --}}
        <div class="mb-5 flex items-center justify-between lg:hidden">
            <a
                href="{{ route('menu') }}"
                class="grid h-11 w-11 place-items-center rounded-full border border-orange-100 bg-white text-slate-700 shadow-sm transition active:scale-95"
                aria-label="Return to menu"
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

            <div class="text-center">
                <p class="text-sm font-black text-slate-950">
                    Order confirmation
                </p>

                <p class="mt-0.5 text-[10px] font-semibold text-slate-500">
                    Cash on delivery
                </p>
            </div>

            <span class="grid h-11 w-11 place-items-center rounded-full bg-emerald-50 text-emerald-600">
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
        </div>

        {{-- Success Hero --}}
        <section
            role="status"
            aria-live="polite"
            class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-emerald-500 via-emerald-500 to-teal-600 px-5 py-7 text-white shadow-2xl shadow-emerald-900/15 sm:px-8 sm:py-10 lg:rounded-[2rem] lg:px-10"
        >
            <div class="pointer-events-none absolute -right-16 -top-20 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-emerald-200/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-4 sm:gap-5">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-full border-4 border-white/25 bg-white text-emerald-600 shadow-xl sm:h-18 sm:w-18">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                            class="h-7 w-7 sm:h-9 sm:w-9"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-50 sm:text-xs">
                            Order placed successfully
                        </p>

                        <h1 class="mt-2 text-2xl font-black leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                            Thank you for your order!
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-emerald-50 sm:mt-3 sm:text-base sm:leading-7">
                            The restaurant has received your cash-on-delivery order and will begin processing it shortly.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex lg:shrink-0">
                    <a
                        href="{{ route('customer.orders.show', $order) }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-emerald-700 shadow-lg transition active:scale-[0.98] hover:bg-emerald-50 sm:rounded-2xl sm:px-5"
                    >
                        Track Order

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

                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl sm:px-5"
                    >
                        Browse Menu
                    </a>
                </div>
            </div>
        </section>

        {{-- Order Reference --}}
        <section class="relative z-10 -mt-3 mx-3 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-xl shadow-orange-900/5 sm:-mt-5 sm:mx-6 sm:p-5 lg:mx-10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[9px] font-black uppercase tracking-[0.16em] text-orange-600 sm:text-xs">
                        Order reference
                    </p>

                    <div class="mt-1 flex min-w-0 items-center gap-2">
                        <p class="min-w-0 break-all text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                            {{ $order->order_number }}
                        </p>

                        <button
                            type="button"
                            x-on:click="copyOrderNumber"
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600 transition active:scale-95 hover:bg-orange-100"
                            aria-label="Copy order number"
                        >
                            <svg
                                x-show="! copied"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <rect x="9" y="9" width="11" height="11" rx="2" />
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                            </svg>

                            <svg
                                x-show="copied"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                class="h-4 w-4 text-emerald-600"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </button>
                    </div>

                    <p class="mt-1 text-xs font-semibold text-slate-500 sm:text-sm">
                        Placed {{ $order->created_at->format('M d, Y · h:i A') }}
                    </p>
                </div>

                <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Current status
                        </p>

                        <span class="mt-1.5 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $statusStyles['badge'] }}">
                            <span class="h-2 w-2 rounded-full {{ $statusStyles['dot'] }}"></span>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p
                        x-show="copied"
                        x-cloak
                        class="text-xs font-black text-emerald-600"
                    >
                        Copied
                    </p>
                </div>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start lg:gap-8">
            {{-- Main Content --}}
            <div class="min-w-0 space-y-5">
                {{-- Current Status --}}
                <section class="rounded-[1.5rem] border p-4 shadow-sm sm:rounded-[1.75rem] sm:p-6 {{ $statusStyles['panel'] }}">
                    <div class="flex items-start gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl shadow-sm {{ $statusStyles['icon'] }}">
                            @if ($isCancelled)
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    class="h-6 w-6"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path
                                        stroke-linecap="round"
                                        d="m9 9 6 6m0-6-6 6"
                                    />
                                </svg>
                            @elseif ($isDelivered)
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    class="h-6 w-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            @else
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-6 w-6"
                                >
                                    <path d="M3 7h11v10H3z" />
                                    <path d="M14 10h4l3 3v4h-7z" />
                                    <circle cx="7" cy="18" r="2" />
                                    <circle cx="18" cy="18" r="2" />
                                </svg>
                            @endif
                        </span>

                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                                Current update
                            </p>

                            <h2 class="mt-1 text-lg font-black text-slate-950 sm:text-xl">
                                {{ $statusLabel }}
                            </h2>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                {{ $statusMessage }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- What Happens Next --}}
                @unless ($isCancelled || $isDelivered)
                    <section class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm sm:rounded-[1.75rem]">
                        <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                What Happens Next
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Your order journey
                            </h2>
                        </div>

                        {{-- Mobile Step List --}}
                        <div class="divide-y divide-slate-100 lg:hidden">
                            <div class="flex items-start gap-3 p-4">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-4 w-4"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        Order placed
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                        Your order information has been sent to the restaurant.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-4">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-orange-200 bg-orange-50 text-sm font-black text-orange-600">
                                    2
                                </span>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        Preparation
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                        The restaurant confirms and prepares your food.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-4">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-slate-200 bg-slate-50 text-sm font-black text-slate-400">
                                    3
                                </span>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        Rider delivery
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                        A rider collects the order and delivers it to your address.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-4">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-slate-200 bg-slate-50 text-sm font-black text-slate-400">
                                    4
                                </span>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        Cash payment
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                        Pay the rider when your order arrives.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Desktop Stepper --}}
                        <div class="hidden grid-cols-4 p-6 lg:grid">
                            <div class="relative px-3 text-center">
                                <div class="absolute left-1/2 top-5 h-0.5 w-full bg-orange-200"></div>

                                <span class="relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full bg-emerald-500 text-white shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-4 w-4"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </span>

                                <p class="mt-3 text-sm font-black text-slate-950">
                                    Order placed
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                    Sent to restaurant
                                </p>
                            </div>

                            <div class="relative px-3 text-center">
                                <div class="absolute left-0 top-5 h-0.5 w-full bg-slate-200"></div>

                                <span class="relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full border-2 border-orange-300 bg-orange-50 text-sm font-black text-orange-600">
                                    2
                                </span>

                                <p class="mt-3 text-sm font-black text-slate-950">
                                    Preparation
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                    Food is prepared
                                </p>
                            </div>

                            <div class="relative px-3 text-center">
                                <div class="absolute left-0 top-5 h-0.5 w-full bg-slate-200"></div>

                                <span class="relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full border-2 border-slate-200 bg-white text-sm font-black text-slate-400">
                                    3
                                </span>

                                <p class="mt-3 text-sm font-black text-slate-950">
                                    Delivery
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                    Rider heads to you
                                </p>
                            </div>

                            <div class="relative px-3 text-center">
                                <div class="absolute right-1/2 top-5 h-0.5 w-full bg-slate-200"></div>

                                <span class="relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full border-2 border-slate-200 bg-white text-sm font-black text-slate-400">
                                    4
                                </span>

                                <p class="mt-3 text-sm font-black text-slate-950">
                                    Payment
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                    Pay on arrival
                                </p>
                            </div>
                        </div>
                    </section>
                @endunless

                {{-- Delivery Address --}}
                @if ($order->delivery_address)
                    <section class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm sm:rounded-[1.75rem] sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                    <circle cx="12" cy="9" r="2.5" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-orange-600">
                                    Delivery Address
                                </p>

                                <h2 class="mt-1 text-base font-black text-slate-950 sm:text-lg">
                                    Delivering to
                                </h2>

                                <p class="mt-2 break-words text-sm font-semibold leading-6 text-slate-600">
                                    {{ $order->delivery_address }}
                                </p>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Mobile Summary --}}
                <section class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm lg:hidden">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-orange-600">
                                Payment Summary
                            </p>

                            <h2 class="mt-1 text-lg font-black text-slate-950">
                                Order total
                            </h2>
                        </div>

                        <p class="text-2xl font-black text-orange-600">
                            Rs. {{ number_format($order->total, 0) }}
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Subtotal
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                Rs. {{ number_format($order->subtotal, 0) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Delivery
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                Rs. {{ number_format($order->delivery_fee, 0) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-3 rounded-xl bg-emerald-50 px-3 py-3">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5 shrink-0 text-emerald-600"
                        >
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>

                        <div>
                            <p class="text-xs font-black text-emerald-900">
                                Cash on Delivery
                            </p>

                            <p class="mt-0.5 text-[10px] font-semibold text-emerald-700">
                                Pay the rider when your food arrives.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Desktop Summary --}}
            <aside class="hidden lg:sticky lg:top-24 lg:block">
                <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
                    <div class="border-b border-orange-100 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                                    Order Summary
                                </p>

                                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                    Payment details
                                </h2>
                            </div>

                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-6 w-6"
                                >
                                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                                    <path d="M9 7h6M9 11h6M9 15h3" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-slate-500">
                                    Subtotal
                                </span>

                                <span class="font-black text-slate-950">
                                    Rs. {{ number_format($order->subtotal, 0) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-slate-500">
                                    Delivery fee
                                </span>

                                <span class="font-black text-slate-950">
                                    Rs. {{ number_format($order->delivery_fee, 0) }}
                                </span>
                            </div>

                            <div class="border-t border-orange-100 pt-4">
                                <div class="flex items-end justify-between gap-4">
                                    <span class="text-base font-black text-slate-950">
                                        Total
                                    </span>

                                    <span class="text-2xl font-black text-orange-600">
                                        Rs. {{ number_format($order->total, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-emerald-50 p-4">
                            <div class="flex items-start gap-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                                >
                                    <rect x="3" y="6" width="18" height="12" rx="2" />
                                    <circle cx="12" cy="12" r="2" />
                                </svg>

                                <div>
                                    <p class="text-sm font-black text-emerald-900">
                                        Cash on Delivery
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">
                                        Payment will be collected when the rider delivers your order.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a
                            href="{{ route('customer.orders.show', $order) }}"
                            class="mt-5 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
                        >
                            Track This Order

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

                        <a
                            href="{{ route('customer.orders') }}"
                            class="mt-3 inline-flex min-h-12 w-full items-center justify-center rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-50"
                        >
                            View All Orders
                        </a>

                        <a
                            href="{{ route('menu') }}"
                            class="mt-2 inline-flex min-h-11 w-full items-center justify-center text-xs font-black text-slate-500 transition hover:text-orange-700"
                        >
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Mobile Primary Actions --}}
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:hidden">
            <a
                href="{{ route('customer.orders.show', $order) }}"
                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:rounded-2xl"
            >
                Track This Order

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

            <a
                href="{{ route('customer.orders') }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition active:scale-[0.98] hover:bg-orange-50 sm:rounded-2xl"
            >
                View My Orders
            </a>
        </div>

        <p class="mt-5 text-center text-xs font-semibold leading-5 text-slate-500">
            Keep your order number for reference. Status updates are available from your order tracking page.
        </p>
    </div>
</main>

@endcomponent
