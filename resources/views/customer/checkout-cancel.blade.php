@component('layouts.public', ['title' => 'Payment Cancelled'])
<main class="min-h-screen bg-[var(--color-surface-warm)] py-6 sm:py-10 lg:py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
            <div class="bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 px-5 py-8 text-white sm:px-8 sm:py-10">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/15 text-gold-100 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="m9 9 6 6m0-6-6 6" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/70">
                            Stripe Checkout
                        </p>

                        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
                            Payment cancelled
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/80 sm:text-base">
                            No payment has been confirmed. Your order will not enter the restaurant queue unless Stripe sends a verified payment success webhook.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                <div class="rounded-2xl border border-gold-100 bg-gold-50 p-4">
                    <p class="text-sm font-black text-gold-800">
                        Your cart should still be available
                    </p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-gold-700 sm:text-sm">
                        Review your items, adjust anything you need, and start a new secure card checkout when you are ready.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <a href="{{ route('cart.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600">
                        Return to Cart
                    </a>

                    <a href="{{ route('checkout.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-5 py-3 text-sm font-black text-brand-600 transition hover:bg-brand-100">
                        Try Again
                    </a>

                    <a href="{{ route('contact') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-700 transition hover:bg-warm-50">
                        Contact Us
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>
@endcomponent
