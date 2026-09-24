@component('layouts.public', [
    'title' => 'Terms and Conditions',
    'metaDescription' => 'Terms and Conditions for Arcade Kebab House online ordering, mobile app, Stripe payments, delivery, pickup, and customer accounts.',
])
@php
    $restaurantName = $brandRestaurant?->name ?? 'Arcade Kebab House';
@endphp

<main class="bg-[var(--color-surface-warm)]">
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-brand-500">Legal</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-warm-950 sm:text-5xl">Terms and Conditions</h1>
        <p class="mt-4 text-sm font-semibold leading-7 text-warm-600">
            Last updated: 24 September 2026. These Terms and Conditions apply when you use the {{ $restaurantName }} website or mobile app, create an account, browse the menu, place an order, make a payment, request delivery or pickup, or contact us for support.
        </p>

        <div class="mt-10 space-y-8 text-sm leading-7 text-warm-700">
            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Acceptance of Terms</h2>
                <p class="mt-3">
                    By using our website, mobile app, or ordering services, you agree to these terms and our <a href="{{ route('privacy') }}" class="font-black text-brand-600 transition hover:text-brand-800">Privacy Policy</a>. If you do not agree, please do not use the service.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Accounts</h2>
                <p class="mt-3">
                    You are responsible for providing accurate account, contact, and delivery information and for keeping your login details secure. We may refuse, suspend, or close an account if we reasonably believe it is being misused, used fraudulently, or used in breach of these terms.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Menu, Availability, and Pricing</h2>
                <p class="mt-3">
                    Menu items, descriptions, prices, opening hours, delivery areas, minimum order amounts, fees, and availability may change without notice. We try to keep information accurate, but errors can occur. If an item is unavailable after you order, we may contact you to offer a substitute, adjust the order, or cancel and refund the affected item.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Orders</h2>
                <p class="mt-3">
                    An order is submitted when you complete checkout. We may decline or cancel an order where items are unavailable, payment is not authorised, delivery information is incomplete, the order appears fraudulent or abusive, or circumstances outside our control prevent fulfilment.
                </p>
                <p class="mt-3">
                    Please review your order carefully before paying, including items, quantities, notes, pickup or delivery details, and contact information.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Payments</h2>
                <p class="mt-3">
                    Online card payments are processed securely through Stripe. You must be authorised to use the payment method you provide. We do not store full card numbers on our servers. Your bank, card provider, or Stripe may apply separate terms, checks, or fees.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Delivery and Pickup</h2>
                <p class="mt-3">
                    Delivery estimates are estimates only and may be affected by preparation time, traffic, weather, rider availability, incomplete address details, or other operational issues. You must be available at the contact number and delivery address you provide. If we cannot complete delivery because the information is incorrect or nobody is available, the order may still be charged.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Cancellations, Refunds, and Australian Consumer Law</h2>
                <p class="mt-3">
                    Because food is prepared to order, cancellation or change requests may not be possible once preparation has started. If there is a problem with your order, contact us as soon as possible with your order details. Any refund, replacement, credit, or other remedy will be assessed based on the circumstances.
                </p>
                <p class="mt-3">
                    Nothing in these terms limits rights you may have under the Australian Consumer Law, including consumer guarantees that cannot lawfully be excluded.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Allergens and Dietary Information</h2>
                <p class="mt-3">
                    We may provide menu descriptions, options, and notes to help customers choose items, but food may be prepared in kitchens that handle allergens such as gluten, dairy, egg, nuts, sesame, soy, seafood, or other ingredients. Please contact us before ordering if you have allergies or dietary requirements. We cannot guarantee that any item is free from traces of allergens.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Acceptable Use</h2>
                <p class="mt-3">
                    You must not misuse the website or app, attempt unauthorised access, interfere with security, submit false orders, harass staff or riders, scrape the service, upload malicious content, or use the service for unlawful purposes.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Intellectual Property</h2>
                <p class="mt-3">
                    The website, app, branding, menu content, photos, copy, layouts, and software are owned by us or licensed to us. You may use them only for normal browsing and ordering. You must not copy, reproduce, or commercially exploit them without permission.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Service Changes and Availability</h2>
                <p class="mt-3">
                    We may update, suspend, or discontinue parts of the website, mobile app, menu, delivery service, or ordering features at any time. We are not responsible for interruptions caused by maintenance, internet outages, payment provider issues, device problems, or events outside our reasonable control.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Liability</h2>
                <p class="mt-3">
                    To the extent permitted by law, we are not liable for indirect, incidental, special, or consequential loss arising from use of the service. Where our liability cannot be excluded, it is limited to the remedies available under applicable law.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Privacy</h2>
                <p class="mt-3">
                    We handle personal information according to our <a href="{{ route('privacy') }}" class="font-black text-brand-600 transition hover:text-brand-800">Privacy Policy</a>. We do not sell your personal information.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Governing Law</h2>
                <p class="mt-3">
                    These terms are governed by the laws of Australia and the applicable laws of the state or territory where the restaurant operates. The parties submit to the courts that have jurisdiction there.
                </p>
            </section>

            <section class="rounded-2xl border border-warm-200 bg-white p-5">
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Contact</h2>
                <p class="mt-3">
                    Questions about these terms or an order can be sent through our <a href="{{ route('contact') }}" class="font-black text-brand-600 transition hover:text-brand-800">contact page</a>.
                </p>
            </section>
        </div>
    </section>
</main>
@endcomponent
