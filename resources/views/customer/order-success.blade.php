@component('layouts.public', ['title' => 'Payment Status'])
@php
    $isPaid = $order?->payment_status === 'paid';
    $hasOrder = $order !== null;
@endphp

<main class="min-h-screen bg-[var(--color-surface-warm)] py-6 sm:py-10 lg:py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
            <div class="bg-gradient-to-br {{ $isPaid ? 'from-leaf-700 via-leaf-600 to-teal-700' : 'from-brand-700 via-brand-600 to-warm-950' }} px-5 py-8 text-white sm:px-8 sm:py-10">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/15 backdrop-blur">
                        @if ($isPaid)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                            </svg>
                        @endif
                    </span>

                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/70">
                            Stripe card payment
                        </p>

                        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
                            {{ $isPaid ? 'Order confirmed' : 'Payment is being confirmed' }}
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/80 sm:text-base">
                            @if ($isPaid)
                                Stripe has confirmed your payment and Arcade Kebab House has received your order.
                            @else
                                Thanks for paying securely by card. Stripe confirmation can take a moment, so this page will never mark an order paid by itself.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                @if ($hasOrder)
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-brand-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                Order
                            </p>
                            <p class="mt-1 break-all text-sm font-black text-warm-950">
                                {{ $order->order_number }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-warm-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-warm-500">
                                Payment
                            </p>
                            <div class="mt-1">
                                <x-status-badge :status="$order->payment_status" type="payment" size="sm" />
                            </div>
                        </div>

                        <div class="rounded-2xl bg-warm-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-warm-500">
                                Total
                            </p>
                            <p class="mt-1 text-sm font-black text-warm-950">
                                @money($order->total)
                            </p>
                        </div>
                    </div>

                    @unless ($isPaid)
                        <div class="rounded-2xl border border-gold-100 bg-gold-50 p-4">
                            <p class="text-sm font-black text-gold-800">
                                Waiting for verified webhook confirmation
                            </p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-gold-700 sm:text-sm">
                                Refreshing this page will not change payment status. Your order will appear in the restaurant queue only after Stripe sends a verified payment event.
                            </p>
                        </div>
                    @endunless
                @else
                    <div class="rounded-2xl border border-gold-100 bg-gold-50 p-4">
                        <p class="text-sm font-black text-gold-800">
                            We could not match this Stripe session yet
                        </p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-gold-700 sm:text-sm">
                            If payment succeeded, confirmation may still be arriving from Stripe. You can check your order history in a moment.
                        </p>
                    </div>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row">
                    @if ($hasOrder)
                        <a href="{{ route('customer.orders.show', $order) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600">
                            View Order
                        </a>
                    @endif

                    <a href="{{ route('customer.orders') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-5 py-3 text-sm font-black text-brand-600 transition hover:bg-brand-100">
                        My Orders
                    </a>

                    <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-700 transition hover:bg-warm-50">
                        Home
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>
@endcomponent
