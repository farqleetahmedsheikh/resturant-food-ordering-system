@component('layouts.admin', ['title' => 'Admin Dashboard'])
@php
    $adminName = auth()->user()->name ?? 'Administrator';
    $firstName = \Illuminate\Support\Str::before($adminName, ' ');
    $restaurantConfigured = (bool) $restaurant;
    $restaurantOpen = (bool) ($restaurant?->is_open ?? false);
    $activeDeliveryOrders = (int) $assignedDeliveries + (int) $outForDeliveryOrders;
    $operationalQueue = (int) $pendingOrders + (int) $preparingOrders + $activeDeliveryOrders;
    $deliveryCompletionRate = $totalOrders > 0
        ? min(100, round(($deliveredOrders / $totalOrders) * 100))
        : 0;
    $menuCoverage = $totalCategories > 0
        ? min(100, round(($activeCategories / $totalCategories) * 100))
        : 0;
    $needsImmediateAttention = (int) $pendingOrders > 0;
    $restaurantStatusLabel = ! $restaurantConfigured
        ? 'Setup Required'
        : ($restaurantOpen ? 'Restaurant Open' : 'Restaurant Closed');
    $restaurantStatusClasses = ! $restaurantConfigured
        ? 'border-gold-100 bg-gold-50 text-gold-700'
        : ($restaurantOpen
            ? 'border-leaf-100 bg-leaf-50 text-leaf-900'
            : 'border-red-200 bg-red-50 text-red-800');
    $restaurantStatusDot = ! $restaurantConfigured
        ? 'bg-gold-500'
        : ($restaurantOpen ? 'bg-leaf-500' : 'bg-red-500');
@endphp

<div class="space-y-5 pb-8 sm:space-y-6">
    <header class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
        <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="relative overflow-hidden bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 p-5 text-white sm:p-7 lg:p-8">
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

                <div class="relative">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-leaf-500">
                                <span class="block h-2 w-2 animate-ping rounded-full bg-leaf-500"></span>
                            </span>

                            Live Operations
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] backdrop-blur">
                            <span class="h-1.5 w-1.5 rounded-full {{ $restaurantStatusDot }}"></span>
                            {{ $restaurantStatusLabel }}
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                        Welcome back, {{ $firstName }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-warm-300 sm:text-base sm:leading-7">
                        Handle pending orders first, watch the delivery queue move, and jump into menu or rider controls without scanning repeated cards.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-950/30 transition active:scale-[0.98] hover:-translate-y-0.5 hover:bg-brand-600"
                        >
                            Pending Queue

                            @if ($needsImmediateAttention)
                                <span class="rounded-full bg-white/20 px-2 py-0.5 text-[9px]">
                                    {{ $pendingOrders }}
                                </span>
                            @endif
                        </a>

                        <a
                            href="{{ route('admin.menu-items.create') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20"
                        >
                            Add Menu Item
                        </a>
                    </div>
                </div>
            </div>

            <aside class="bg-warm-950 p-5 text-white sm:p-6 xl:border-l xl:border-white/10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/50">
                            Current Pulse
                        </p>

                        <p class="mt-2 text-3xl font-black tracking-tight">
                            {{ $operationalQueue }}
                        </p>

                        <p class="mt-1 text-xs font-semibold text-white/50">
                            orders need movement
                        </p>
                    </div>

                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-500/20 text-brand-200">
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

                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-white/10 px-3 py-3">
                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/50">
                            Pending
                        </p>

                        <p class="mt-1 text-lg font-black">
                            {{ $pendingOrders }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/10 px-3 py-3">
                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/50">
                            Delivery
                        </p>

                        <p class="mt-1 text-lg font-black">
                            {{ $activeDeliveryOrders }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/10 px-3 py-3">
                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/50">
                            Success
                        </p>

                        <p class="mt-1 text-lg font-black">
                            {{ $deliveryCompletionRate }}%
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-white/50">
                        COD Revenue
                    </p>

                    <p class="mt-1 text-2xl font-black">
                        @money($totalCodRevenue)
                    </p>
                </div>
            </aside>
        </div>
    </header>

    @if (! $restaurantConfigured)
        <section class="rounded-[1.5rem] border border-gold-100 bg-gold-50 p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-gold-500 shadow-sm">
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
                                d="M12 9v4M12 17h.01M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z"
                            />
                        </svg>
                    </span>

                    <div>
                        <p class="font-black text-gold-700">
                            Restaurant setup is incomplete
                        </p>

                        <p class="mt-1 text-sm font-semibold leading-6 text-gold-700">
                            Add restaurant details, delivery fees, minimum order value, and opening information before accepting public orders.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('admin.settings.restaurant.edit') }}"
                    class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-gold-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-gold-500/20 transition active:scale-[0.98] hover:bg-gold-700"
                >
                    Complete Setup
                </a>
            </div>
        </section>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start xl:gap-6">
        <div
            class="min-w-0"
            x-data="adminDashboardLive({
                liveUrl: @js(route('admin.dashboard.live')),
                confirmUrl: @js(route('admin.dashboard.orders.confirm', ['order' => '__ORDER__'])),
                declineUrl: @js(route('admin.dashboard.orders.decline', ['order' => '__ORDER__'])),
                csrfToken: @js(csrf_token())
            })"
            x-init="start()"
            x-on:click="handlePanelClick($event)"
        >
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        x-show="message"
                        x-transition
                        x-cloak
                        class="inline-flex items-center rounded-full border border-leaf-100 bg-leaf-50 px-3 py-1.5 text-xs font-black text-leaf-700"
                        x-text="message"
                    ></span>

                    <span
                        x-show="error"
                        x-transition
                        x-cloak
                        class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-black text-red-700"
                        x-text="error"
                    ></span>
                </div>

                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.12em] text-warm-500">
                    <span
                        class="h-2 w-2 rounded-full"
                        x-bind:class="refreshing ? 'animate-pulse bg-brand-500' : 'bg-leaf-500'"
                    ></span>

                    <span x-text="refreshing ? 'Refreshing dashboard' : 'Live dashboard ready'"></span>
                </div>
            </div>

            <div x-ref="livePanel">
                @include('admin.partials.dashboard-live')
            </div>

            <div
                x-show="declineModalOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-[80] grid place-items-center bg-warm-950/50 p-4 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
            >
                <form
                    x-on:submit.prevent="declineOrder()"
                    class="w-full max-w-lg overflow-hidden rounded-[1.5rem] bg-white shadow-2xl shadow-warm-950/25"
                >
                    <div class="border-b border-red-100 bg-red-50 px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-red-600">
                                    Decline order
                                </p>

                                <h3 class="mt-1 text-xl font-black text-warm-950">
                                    Reason required
                                </h3>

                                <p class="mt-1 text-xs font-semibold text-warm-500">
                                    <span x-text="declineOrderNumber"></span>
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="closeDeclineModal()"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-warm-500 shadow-sm transition hover:text-red-600"
                                aria-label="Close decline modal"
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
                                        d="m6 6 12 12M18 6 6 18"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 px-5 py-5 sm:px-6">
                        <label for="decline_reason" class="block text-sm font-black text-warm-900">
                            Why are you declining this order?
                        </label>

                        <textarea
                            id="decline_reason"
                            x-model="declineReason"
                            rows="5"
                            required
                            minlength="5"
                            maxlength="1000"
                            class="w-full rounded-2xl border border-warm-200 bg-warm-50 px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition focus:border-red-300 focus:bg-white focus:ring-4 focus:ring-red-100"
                            placeholder="Example: Item unavailable, customer requested cancellation, or delivery address is outside service area."
                        ></textarea>

                        <p class="text-xs font-semibold leading-5 text-warm-500">
                            This reason is saved in the order status history for admin review.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-warm-100 bg-warm-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                        <button
                            type="button"
                            x-on:click="closeDeclineModal()"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-600 transition hover:bg-warm-100"
                        >
                            Keep Order
                        </button>

                        <button
                            type="submit"
                            x-bind:disabled="busy"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-600/20 transition active:scale-[0.98] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span x-text="busy ? 'Declining...' : 'Decline Order'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-5 xl:sticky xl:top-24">
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Control Center
                </p>

                <h2 class="mt-1 text-xl font-black text-warm-950">
                    Quick actions
                </h2>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="rounded-[1.1rem] border border-warm-200 bg-brand-50 p-4 transition active:scale-[0.98] hover:bg-brand-100"
                    >
                        <span class="block text-sm font-black text-warm-950">
                            Orders
                        </span>

                        <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                            Review all
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.riders.index') }}"
                        class="rounded-[1.1rem] border border-blue-100 bg-blue-50 p-4 transition active:scale-[0.98] hover:bg-blue-100"
                    >
                        <span class="block text-sm font-black text-warm-950">
                            Riders
                        </span>

                        <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                            {{ $totalRiders }} registered
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.menu-items.index') }}"
                        class="rounded-[1.1rem] border border-leaf-100 bg-leaf-50 p-4 transition active:scale-[0.98] hover:bg-leaf-100"
                    >
                        <span class="block text-sm font-black text-warm-950">
                            Menu
                        </span>

                        <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                            {{ $availableMenuItems }} available
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.settings.restaurant.edit') }}"
                        class="rounded-[1.1rem] border border-violet-100 bg-violet-50 p-4 transition active:scale-[0.98] hover:bg-violet-100"
                    >
                        <span class="block text-sm font-black text-warm-950">
                            Settings
                        </span>

                        <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                            Availability
                        </span>
                    </a>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Restaurant
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Public availability
                        </h2>
                    </div>

                    <span class="h-3 w-3 shrink-0 rounded-full {{ $restaurantStatusDot }}"></span>
                </div>

                <div class="mt-4 rounded-2xl border p-4 {{ $restaurantStatusClasses }}">
                    <p class="text-sm font-black">
                        {{ $restaurantStatusLabel }}
                    </p>

                    <p class="mt-1 text-xs font-semibold leading-5">
                        @if (! $restaurantConfigured)
                            Restaurant settings must be completed.
                        @elseif ($restaurantOpen)
                            Customers can currently place orders.
                        @else
                            Public ordering is currently unavailable.
                        @endif
                    </p>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Health Snapshot
                </p>

                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-black text-warm-600">
                                Menu visibility
                            </p>

                            <span class="text-xs font-black text-brand-500">
                                {{ $menuCoverage }}%
                            </span>
                        </div>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-warm-100">
                            <div
                                class="h-full rounded-full bg-brand-500"
                                style="width: {{ $menuCoverage }}%"
                            ></div>
                        </div>

                        <p class="mt-2 text-xs font-semibold text-warm-500">
                            {{ $activeCategories }} of {{ $totalCategories }} categories visible.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-warm-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                Items
                            </p>

                            <p class="mt-1 text-xl font-black text-warm-950">
                                {{ $availableMenuItems }}/{{ $totalMenuItems }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-warm-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                Featured
                            </p>

                            <p class="mt-1 text-xl font-black text-warm-950">
                                {{ $featuredMenuItems }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
    window.adminDashboardLive = function (config) {
        return {
            busy: false,
            error: '',
            message: '',
            refreshing: false,
            declineModalOpen: false,
            declineOrderId: null,
            declineOrderNumber: '',
            declineReason: '',
            refreshTimer: null,
            feedbackTimer: null,

            start() {
                this.refreshTimer = window.setInterval(() => {
                    if (! this.declineModalOpen && ! this.busy) {
                        this.load(true);
                    }
                }, 90000);

                window.addEventListener('beforeunload', () => {
                    if (this.refreshTimer) {
                        window.clearInterval(this.refreshTimer);
                    }
                });
            },

            handlePanelClick(event) {
                const confirmButton = event.target.closest('[data-confirm-order]');

                if (confirmButton) {
                    event.preventDefault();
                    this.confirmOrder(confirmButton.dataset.confirmOrder);

                    return;
                }

                const declineButton = event.target.closest('[data-decline-order]');

                if (declineButton) {
                    event.preventDefault();
                    this.openDeclineModal(
                        declineButton.dataset.declineOrder,
                        declineButton.dataset.orderNumber
                    );
                }
            },

            async load(silent = false) {
                this.refreshing = true;

                try {
                    const response = await fetch(config.liveUrl, {
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (response.redirected) {
                        window.location.href = response.url;

                        return;
                    }

                    if (! response.ok) {
                        throw new Error('Could not refresh the live dashboard.');
                    }

                    this.$refs.livePanel.innerHTML = await response.text();
                } catch (error) {
                    if (! silent) {
                        this.showError(error.message);
                    }
                } finally {
                    this.refreshing = false;
                }
            },

            async confirmOrder(orderId) {
                if (this.busy) {
                    return;
                }

                this.busy = true;
                this.clearFeedback();

                try {
                    const data = await this.post(this.actionUrl(config.confirmUrl, orderId));
                    this.showMessage(data.message || 'Order confirmed successfully.');
                    await this.load(true);
                } catch (error) {
                    this.showError(error.message);
                } finally {
                    this.busy = false;
                }
            },

            openDeclineModal(orderId, orderNumber) {
                this.clearFeedback();
                this.declineOrderId = orderId;
                this.declineOrderNumber = orderNumber || 'Selected order';
                this.declineReason = '';
                this.declineModalOpen = true;
            },

            closeDeclineModal() {
                if (this.busy) {
                    return;
                }

                this.declineModalOpen = false;
                this.declineOrderId = null;
                this.declineOrderNumber = '';
                this.declineReason = '';
            },

            async declineOrder() {
                if (this.busy) {
                    return;
                }

                const reason = this.declineReason.trim();

                if (reason.length < 5) {
                    this.showError('Please add a clearer reason before declining the order.');

                    return;
                }

                this.busy = true;
                this.clearFeedback();

                try {
                    const data = await this.post(
                        this.actionUrl(config.declineUrl, this.declineOrderId),
                        { reason }
                    );

                    this.closeDeclineModal();
                    this.showMessage(data.message || 'Order declined successfully.');
                    await this.load(true);
                } catch (error) {
                    this.showError(error.message);
                } finally {
                    this.busy = false;
                }
            },

            actionUrl(template, orderId) {
                return template.replace('__ORDER__', orderId);
            },

            async post(url, payload = {}) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (response.redirected) {
                    window.location.href = response.url;

                    return {};
                }

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    const errors = data.errors ? Object.values(data.errors).flat() : [];

                    throw new Error(errors[0] || data.message || 'The action could not be completed.');
                }

                return data;
            },

            showMessage(message) {
                this.message = message;
                this.error = '';
                this.queueFeedbackClear();
            },

            showError(message) {
                this.error = message;
                this.message = '';
                this.queueFeedbackClear();
            },

            clearFeedback() {
                this.error = '';
                this.message = '';

                if (this.feedbackTimer) {
                    window.clearTimeout(this.feedbackTimer);
                }
            },

            queueFeedbackClear() {
                if (this.feedbackTimer) {
                    window.clearTimeout(this.feedbackTimer);
                }

                this.feedbackTimer = window.setTimeout(() => {
                    this.error = '';
                    this.message = '';
                }, 5000);
            },
        };
    };
</script>

@endcomponent
