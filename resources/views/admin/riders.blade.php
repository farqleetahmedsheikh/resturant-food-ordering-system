@component('layouts.admin', ['title' => 'Riders'])
@php
$visibleRiders = collect(
method_exists($riders, 'items')
? $riders->items()
: $riders
);

    $riderCount = method_exists($riders, 'total')
        ? (int) $riders->total()
        : $visibleRiders->count();

    $pageRiderCount = $visibleRiders->count();

    $activeRidersOnPage = $visibleRiders
        ->filter(fn ($rider) => (bool) $rider->is_active)
        ->count();

    $inactiveRidersOnPage = $pageRiderCount - $activeRidersOnPage;

    $assignedOrdersOnPage = (int) $visibleRiders->sum(
        fn ($rider) => (int) ($rider->assigned_orders_count ?? 0)
    );

    $deliveredOrdersOnPage = (int) $visibleRiders->sum(
        fn ($rider) => (int) ($rider->delivered_orders_count ?? 0)
    );

    $teamCompletionRate = $assignedOrdersOnPage > 0
        ? min(
            100,
            round(
                ($deliveredOrdersOnPage / $assignedOrdersOnPage) * 100
            )
        )
        : 0;

    $hasPages = method_exists($riders, 'hasPages')
        && $riders->hasPages();
@endphp

<div class="space-y-5 pb-6 sm:space-y-6">
    {{-- Hero --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-5 text-white shadow-xl shadow-slate-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative grid gap-7 xl:grid-cols-[minmax(0,1fr)_500px] xl:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-3.5 w-3.5 text-orange-300"
                        >
                            <circle cx="6" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                            <path d="M8 18h8M7 16l2-6h6l3 6" />
                        </svg>

                        Delivery Management
                    </span>

                    <span class="rounded-full bg-orange-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                        {{ $riderCount }}
                        {{ $riderCount === 1 ? 'rider' : 'riders' }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                    Delivery riders
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-300 sm:text-base sm:leading-7">
                    Manage rider accounts, monitor delivery workload, review completion performance, and control assignment availability.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('admin.riders.create') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition active:scale-[0.98] hover:-translate-y-0.5 hover:bg-orange-500 sm:rounded-2xl"
                    >
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
                                d="M12 5v14M5 12h14"
                            />
                        </svg>

                        Create Rider
                    </a>

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                            <path d="M9 7h6M9 11h6M9 15h3" />
                        </svg>

                        View Orders
                    </a>
                </div>
            </div>

            {{-- Team Statistics --}}
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2">
                <article class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Active Riders
                            </p>

                            <p class="mt-1 text-2xl font-black text-emerald-300">
                                {{ $activeRidersOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Available on this page
                    </p>
                </article>

                <article class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Inactive
                            </p>

                            <p class="mt-1 text-2xl font-black text-red-300">
                                {{ $inactiveRidersOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Accounts disabled
                    </p>
                </article>

                <article class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Assigned Orders
                    </p>

                    <p class="mt-1 text-2xl font-black text-blue-300">
                        {{ $assignedOrdersOnPage }}
                    </p>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Current page workload
                    </p>
                </article>

                <article class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Completion
                            </p>

                            <p class="mt-1 text-2xl font-black">
                                {{ $teamCompletionRate }}%
                            </p>
                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            class="h-4 w-4 text-emerald-300"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/15">
                        <div
                            class="h-full rounded-full bg-emerald-400"
                            style="width: {{ $teamCompletionRate }}%"
                        ></div>
                    </div>
                </article>
            </div>
        </div>
    </header>

    @if ($visibleRiders->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-8 w-8 sm:h-9 sm:w-9"
                >
                    <circle cx="6" cy="18" r="2" />
                    <circle cx="18" cy="18" r="2" />
                    <path d="M8 18h8M7 16l2-6h6l3 6" />
                </svg>
            </span>

            <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                Build your delivery team
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                Create a rider account so delivery orders can be assigned, tracked, and completed through the rider dashboard.
            </p>

            <a
                href="{{ route('admin.riders.create') }}"
                class="mt-6 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
            >
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
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                Create First Rider
            </a>
        </section>
    @else
        {{-- Rider Directory --}}
        <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                        Rider Directory
                    </p>

                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        Delivery team
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Showing {{ $pageRiderCount }}
                        {{ $pageRiderCount === 1 ? 'rider' : 'riders' }}
                        on this page
                    </p>
                </div>

                <a
                    href="{{ route('admin.riders.create') }}"
                    class="hidden min-h-10 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100 sm:inline-flex"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Add Rider
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($riders as $rider)
                    @php
                        $assignedCount = (int) (
                            $rider->assigned_orders_count ?? 0
                        );

                        $deliveredCount = (int) (
                            $rider->delivered_orders_count ?? 0
                        );

                        $remainingCount = max(
                            0,
                            $assignedCount - $deliveredCount
                        );

                        $deliveryRate = $assignedCount > 0
                            ? min(
                                100,
                                round(
                                    ($deliveredCount / $assignedCount) * 100
                                )
                            )
                            : 0;

                        $phoneHref = $rider->phone
                            ? preg_replace(
                                '/[^0-9+]/',
                                '',
                                $rider->phone
                            )
                            : null;

                        $initials = collect(
                            preg_split(
                                '/\s+/',
                                trim($rider->name ?? '')
                            )
                        )
                            ->filter()
                            ->take(2)
                            ->map(
                                fn ($part) => mb_strtoupper(
                                    mb_substr($part, 0, 1)
                                )
                            )
                            ->implode('');

                        $initials = $initials ?: 'R';
                    @endphp

                    <article class="group relative p-4 transition hover:bg-orange-50/30 sm:p-5">
                        <div
                            @class([
                                'absolute inset-y-0 left-0 w-1',
                                'bg-emerald-500' => $rider->is_active,
                                'bg-red-500' => ! $rider->is_active,
                            ])
                        ></div>

                        <div class="grid gap-4 pl-2 sm:grid-cols-[64px_minmax(0,1fr)] sm:pl-3 xl:grid-cols-[64px_minmax(0,1fr)_190px_190px_auto] xl:items-center">
                            {{-- Avatar --}}
                            <div
                                @class([
                                    'grid h-16 w-16 place-items-center rounded-[1.25rem] text-lg font-black shadow-sm',
                                    'bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 text-orange-700' => $rider->is_active,
                                    'bg-slate-100 text-slate-500' => ! $rider->is_active,
                                ])
                            >
                                {{ $initials }}
                            </div>

                            {{-- Rider Identity --}}
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        @class([
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]',
                                            'bg-emerald-50 text-emerald-700' => $rider->is_active,
                                            'bg-red-50 text-red-700' => ! $rider->is_active,
                                        ])
                                    >
                                        <span
                                            @class([
                                                'h-1.5 w-1.5 rounded-full',
                                                'bg-emerald-500' => $rider->is_active,
                                                'bg-red-500' => ! $rider->is_active,
                                            ])
                                        ></span>

                                        {{ $rider->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                    @if ($remainingCount > 0)
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[9px] font-black text-blue-700">
                                            {{ $remainingCount }}
                                            {{ $remainingCount === 1 ? 'open delivery' : 'open deliveries' }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-2 truncate text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                                    {{ $rider->name }}
                                </h3>

                                <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                    {{ $rider->email }}
                                </p>

                                @if ($rider->phone)
                                    <a
                                        href="tel:{{ $phoneHref }}"
                                        class="mt-1 inline-block text-xs font-black text-orange-700 transition hover:text-orange-800"
                                    >
                                        {{ $rider->phone }}
                                    </a>
                                @else
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        No phone number added
                                    </p>
                                @endif
                            </div>

                            {{-- Delivery Workload --}}
                            <div class="grid grid-cols-3 gap-2 sm:col-span-2 xl:col-span-1">
                                <div class="rounded-xl bg-blue-50 px-3 py-3 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-blue-600">
                                        Assigned
                                    </p>

                                    <p class="mt-1 text-lg font-black text-blue-950">
                                        {{ $assignedCount }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-emerald-50 px-3 py-3 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-emerald-600">
                                        Delivered
                                    </p>

                                    <p class="mt-1 text-lg font-black text-emerald-950">
                                        {{ $deliveredCount }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-amber-50 px-3 py-3 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-amber-600">
                                        Open
                                    </p>

                                    <p class="mt-1 text-lg font-black text-amber-950">
                                        {{ $remainingCount }}
                                    </p>
                                </div>
                            </div>

                            {{-- Performance --}}
                            <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2 xl:col-span-1">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                            Completion Rate
                                        </p>

                                        <p class="mt-1 text-lg font-black text-slate-950">
                                            {{ $deliveryRate }}%
                                        </p>
                                    </div>

                                    <span
                                        @class([
                                            'grid h-9 w-9 place-items-center rounded-xl',
                                            'bg-emerald-100 text-emerald-700' => $deliveryRate >= 80,
                                            'bg-blue-100 text-blue-700' => $deliveryRate >= 50 && $deliveryRate < 80,
                                            'bg-amber-100 text-amber-700' => $deliveryRate < 50,
                                        ])
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.5"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 12 4 4L19 6"
                                            />
                                        </svg>
                                    </span>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        @class([
                                            'h-full rounded-full',
                                            'bg-emerald-500' => $deliveryRate >= 80,
                                            'bg-blue-500' => $deliveryRate >= 50 && $deliveryRate < 80,
                                            'bg-amber-500' => $deliveryRate < 50,
                                        ])
                                        style="width: {{ $deliveryRate }}%"
                                    ></div>
                                </div>

                                @if ($assignedCount === 0)
                                    <p class="mt-2 text-[9px] font-semibold text-slate-400">
                                        No delivery history yet
                                    </p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="grid grid-cols-[auto_auto_1fr_auto] gap-2 sm:col-span-2 xl:col-span-1 xl:flex xl:justify-end">
                                <a
                                    href="mailto:{{ $rider->email }}"
                                    class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition active:scale-95 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700"
                                    aria-label="Email {{ $rider->name }}"
                                    title="{{ $rider->email }}"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                </a>

                                @if ($phoneHref)
                                    <a
                                        href="tel:{{ $phoneHref }}"
                                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-blue-700 transition active:scale-95 hover:border-blue-600 hover:bg-blue-600 hover:text-white"
                                        aria-label="Call {{ $rider->name }}"
                                        title="{{ $rider->phone }}"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                                        </svg>
                                    </a>
                                @endif

                                <a
                                    href="{{ route('admin.riders.edit', $rider) }}"
                                    class="inline-flex min-h-11 min-w-0 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-xs font-black text-white shadow-lg shadow-orange-600/15 transition active:scale-[0.98] hover:bg-orange-700 xl:min-w-[90px]"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <path d="m14 4 6 6L8 22H2v-6L14 4z" />
                                        <path d="m12 6 6 6" />
                                    </svg>

                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.riders.destroy', $rider) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this rider account? This action cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition active:scale-95 hover:border-red-600 hover:bg-red-600 hover:text-white"
                                        aria-label="Delete {{ $rider->name }}"
                                        title="Delete rider"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path d="M4 7h16" />
                                            <path d="M10 11v6M14 11v6" />
                                            <path d="m6 7 1 14h10l1-14" />
                                            <path d="M9 7V4h6v3" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                {{ $riders->withQueryString()->links() }}
            </div>
        @endif
    @endif

    {{-- Mobile Create Action --}}
    <div class="sticky bottom-3 z-30 rounded-2xl border border-orange-100 bg-white/95 p-3 shadow-xl shadow-slate-950/10 backdrop-blur sm:hidden">
        <a
            href="{{ route('admin.riders.create') }}"
            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
        >
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
                    d="M12 5v14M5 12h14"
                />
            </svg>

            Create New Rider
        </a>
    </div>
</div>

@endcomponent
