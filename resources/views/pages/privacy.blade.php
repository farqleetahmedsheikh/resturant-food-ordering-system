@component('layouts.public', [
    'title' => 'Privacy Policy',
    'metaDescription' => 'Privacy Policy for Arcade Kebab House online ordering, mobile app, Stripe payments, delivery, and customer accounts.',
])
@php
    $restaurantName = $brandRestaurant?->name ?? 'Arcade Kebab House';
    $contactEmail = $brandRestaurant?->email;
    $contactPhone = $brandRestaurant?->phone;
@endphp

<main class="bg-[var(--color-surface-warm)]">
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-brand-500">Legal</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-warm-950 sm:text-5xl">Privacy Policy</h1>
        <p class="mt-4 text-sm font-semibold leading-7 text-warm-600">
            Last updated: 24 September 2026. This Privacy Policy explains how {{ $restaurantName }} handles personal information through our website, mobile app, ordering, payment, delivery, and customer support services.
        </p>

        <div class="mt-8 rounded-2xl border border-brand-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-warm-950">Short version</p>
            <p class="mt-2 text-sm leading-7 text-warm-600">
                We collect the information needed to create accounts, prepare orders, take payment through Stripe, deliver food, provide support, improve security, and meet legal obligations. We do not sell your personal information.
            </p>
        </div>

        <div class="mt-10 space-y-8 text-sm leading-7 text-warm-700">
            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Who We Are</h2>
                <p class="mt-3">
                    In this policy, "we", "us", and "our" means {{ $restaurantName }}. We operate an Australian restaurant ordering website and mobile app for customers, riders, and restaurant staff.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Information We Collect</h2>
                <p class="mt-3">We may collect:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>account details such as your name, email address, phone number, password, and role;</li>
                    <li>ordering details such as cart items, menu selections, item notes, order history, delivery instructions, and receipts;</li>
                    <li>delivery details such as address, coordinates you choose to provide, delivery status, and rider assignment information;</li>
                    <li>payment information handled by Stripe, including payment status, transaction references, and limited card details such as card brand or last four digits where Stripe returns them to us;</li>
                    <li>support and contact messages you send to us;</li>
                    <li>device, app, and security information such as IP address, browser or device type, push notification tokens, log data, and authentication activity.</li>
                </ul>
                <p class="mt-3">
                    We do not intentionally collect full card numbers, bank account details, or unnecessary sensitive information.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">How We Use Information</h2>
                <p class="mt-3">We use personal information to:</p>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>create and manage customer, rider, and staff accounts;</li>
                    <li>process orders, payments, refunds where applicable, delivery, and order updates;</li>
                    <li>contact you about orders, account security, support requests, or important service messages;</li>
                    <li>send receipts, delivery updates, and optional app notifications;</li>
                    <li>maintain security, prevent misuse, investigate errors, and protect our services;</li>
                    <li>comply with tax, accounting, record keeping, dispute handling, and legal obligations;</li>
                    <li>improve menu availability, checkout, delivery, and app performance.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Payments Through Stripe</h2>
                <p class="mt-3">
                    Card payments are processed by Stripe. When you pay by card, Stripe collects and processes payment information under its own terms and privacy notices. We receive payment confirmations, payment status, and related transaction references so we can confirm your order. We do not store your full card number on our servers.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">When We Share Information</h2>
                <p class="mt-3">
                    We only share information where needed to run the service, complete your order, or meet legal obligations. This may include sharing relevant order and contact details with restaurant staff, assigned riders, Stripe, hosting and email providers, technical service providers, professional advisers, regulators, or authorities where required by law.
                </p>
                <p class="mt-3 font-black text-warm-950">
                    We do not sell your personal information.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Cookies, Analytics, and App Data</h2>
                <p class="mt-3">
                    Our website may use cookies or similar storage to keep you signed in, maintain cart and checkout sessions, protect forms, remember preferences, and improve reliability. Our mobile app may store authentication tokens, app settings, notification tokens, and cached service data on your device.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Security and Retention</h2>
                <p class="mt-3">
                    We use reasonable technical and organisational measures to protect personal information, including access controls, authentication, encrypted transport where supported, and restricted administrative access. No online service is completely risk free. We keep personal information only for as long as needed for the purposes in this policy, including order records, legal compliance, accounting, dispute resolution, and security.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Access, Correction, and Complaints</h2>
                <p class="mt-3">
                    You may ask to access or correct the personal information we hold about you. You may also contact us if you have a privacy concern or complaint. We will respond within a reasonable time and may need to verify your identity before acting on a request.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Children</h2>
                <p class="mt-3">
                    Our ordering services are intended for customers who can lawfully place and pay for food orders. If a parent or guardian believes a child has provided personal information without appropriate permission, please contact us.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Changes to This Policy</h2>
                <p class="mt-3">
                    We may update this Privacy Policy from time to time. The updated version will be posted on this page with a new last updated date.
                </p>
            </section>

            <section class="rounded-2xl border border-warm-200 bg-white p-5">
                <h2 class="text-2xl font-black tracking-tight text-warm-950">Contact Us</h2>
                <p class="mt-3">
                    For privacy questions, access or correction requests, or complaints, contact {{ $restaurantName }} through our <a href="{{ route('contact') }}" class="font-black text-brand-600 transition hover:text-brand-800">contact page</a>.
                    @if ($contactEmail)
                        You can also email <a href="mailto:{{ $contactEmail }}" class="font-black text-brand-600 transition hover:text-brand-800">{{ $contactEmail }}</a>.
                    @endif
                    @if ($contactPhone)
                        You can call {{ $contactPhone }}.
                    @endif
                </p>
            </section>
        </div>
    </section>
</main>
@endcomponent
