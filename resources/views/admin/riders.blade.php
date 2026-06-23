@component('layouts.admin', ['title' => 'Riders'])
@php
$riderCount = method_exists($riders, 'total')
? $riders->total()
: $riders->count();

    $visibleRiders = collect(
        method_exists($riders, 'items')
            ? $riders->items()
            : $riders
    );

    $activeRidersOnPage = $visibleRiders
        ->filter(fn ($rider) => (bool) $rider->is_active)
        ->count();

    $assignedOrdersOnPage = $visibleRiders->sum('assigned_orders_count');
    $deliveredOrdersOnPage = $visibleRiders->sum('delivered_orders_count');
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Delivery Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Riders
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Create rider accounts, control account availability, and review assigned and completed delivery counts.
        </p>
    </div>

    <a
        href="{{ route('admin.riders.create') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            class="h-5 w-5"
        >
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
        </svg>

        Create Rider
    </a>
</div>

{{-- Delivery Team Summary --}}
<section class="relative mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-6 text-white shadow-2xl shadow-slate-950/20 sm:p-8">
    <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 left-16 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

    <div class="relative grid gap-7 xl:grid-cols-[1fr_auto] xl:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-300">
                Delivery Team
            </p>

            <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                {{ $riderCount }}
                {{ $riderCount === 1 ? 'registered rider' : 'registered riders' }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-300">
                Active riders can access the rider dashboard, receive order assignments, and update delivery progress.
            </p>
        </div>

        <div class="grid grid-cols-3 gap-3 xl:min-w-[470px]">
            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                    Active
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ $activeRidersOnPage }}
                </p>

                <p class="mt-1 text-[10px] font-semibold text-slate-400">
                    This page
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                    Assigned
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ $assignedOrdersOnPage }}
                </p>

                <p class="mt-1 text-[10px] font-semibold text-slate-400">
                    Deliveries
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                    Delivered
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-300">
                    {{ $deliveredOrdersOnPage }}
                </p>

                <p class="mt-1 text-[10px] font-semibold text-slate-400">
                    Completed
                </p>
            </div>
        </div>
    </div>
</section>

@if ($riders->isEmpty())
    {{-- Empty State --}}
    <section class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-8 text-center shadow-sm sm:p-12">
        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-50 text-orange-600">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-9 w-9"
            >
                <circle cx="6" cy="18" r="2" />
                <circle cx="18" cy="18" r="2" />
                <path d="M8 18h8M7 16l2-6h6l3 6M10 10V7h4" />
            </svg>
        </div>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-slate-950">
            No riders found
        </h2>

        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-600">
            Create the first rider account so delivery orders can be assigned and managed through the rider dashboard.
        </p>

        <a
            href="{{ route('admin.riders.create') }}"
            class="mt-7 inline-flex items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
        >
            Create First Rider
        </a>
    </section>
@else
    {{-- Desktop Riders Table --}}
    <section class="hidden overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm xl:block">
        <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-6 py-5">
            <div>
                <h2 class="text-xl font-black text-slate-950">
                    Rider directory
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Review rider contact details, activity, and account status.
                </p>
            </div>

            <span class="rounded-full bg-orange-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-orange-700">
                {{ $riderCount }} Total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80">
                    <tr class="text-xs font-black uppercase tracking-[0.13em] text-slate-500">
                        <th class="px-6 py-4">Rider</th>
                        <th class="px-5 py-4">Contact</th>
                        <th class="px-5 py-4 text-center">Assigned</th>
                        <th class="px-5 py-4 text-center">Delivered</th>
                        <th class="px-5 py-4">Performance</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($riders as $rider)
                        @php
                            $assignedCount = (int) $rider->assigned_orders_count;
                            $deliveredCount = (int) $rider->delivered_orders_count;

                            $deliveryRate = $assignedCount > 0
                                ? min(100, round(($deliveredCount / $assignedCount) * 100))
                                : 0;
                        @endphp

                        <tr class="group transition hover:bg-orange-50/40">
                            {{-- Rider --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 text-xl font-black text-orange-700 shadow-sm">
                                        {{ mb_substr($rider->name, 0, 1) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-[220px] truncate text-base font-black text-slate-950">
                                            {{ $rider->name }}
                                        </p>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            FreshBite Delivery Rider
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-5 py-5">
                                <a
                                    href="mailto:{{ $rider->email }}"
                                    class="block max-w-[220px] truncate font-bold text-slate-700 transition hover:text-orange-700"
                                >
                                    {{ $rider->email }}
                                </a>

                                @if ($rider->phone)
                                    <a
                                        href="tel:{{ $rider->phone }}"
                                        class="mt-1 block font-semibold text-slate-500 transition hover:text-orange-700"
                                    >
                                        {{ $rider->phone }}
                                    </a>
                                @else
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        No phone added
                                    </p>
                                @endif
                            </td>

                            {{-- Assigned --}}
                            <td class="px-5 py-5 text-center">
                                <span class="inline-grid h-11 min-w-11 place-items-center rounded-xl bg-blue-50 px-3 font-black text-blue-700">
                                    {{ $assignedCount }}
                                </span>
                            </td>

                            {{-- Delivered --}}
                            <td class="px-5 py-5 text-center">
                                <span class="inline-grid h-11 min-w-11 place-items-center rounded-xl bg-emerald-50 px-3 font-black text-emerald-700">
                                    {{ $deliveredCount }}
                                </span>
                            </td>

                            {{-- Performance --}}
                            <td class="px-5 py-5">
                                <div class="w-32">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-black text-slate-700">
                                            {{ $deliveryRate }}%
                                        </span>

                                        <span class="text-[10px] font-bold text-slate-400">
                                            completed
                                        </span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-emerald-500"
                                            style="width: {{ $deliveryRate }}%"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $rider->is_active ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-red-100 bg-red-50 text-red-700' }}">
                                    <span class="h-2 w-2 rounded-full {{ $rider->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                                    {{ $rider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.riders.edit', $rider) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-orange-200 bg-white px-4 py-2.5 text-xs font-black text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.riders.destroy', $rider) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this rider account?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-black text-red-600 transition hover:bg-red-600 hover:text-white"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Mobile and Tablet Rider Cards --}}
    <div class="grid gap-5 md:grid-cols-2 xl:hidden">
        @foreach ($riders as $rider)
            @php
                $assignedCount = (int) $rider->assigned_orders_count;
                $deliveredCount = (int) $rider->delivered_orders_count;

                $deliveryRate = $assignedCount > 0
                    ? min(100, round(($deliveredCount / $assignedCount) * 100))
                    : 0;
            @endphp

            <article class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                {{-- Rider Header --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 p-5 text-white sm:p-6">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-[1.4rem] border border-white/30 bg-white text-2xl font-black text-orange-600 shadow-xl">
                                    {{ mb_substr($rider->name, 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    <h2 class="truncate text-xl font-black tracking-tight">
                                        {{ $rider->name }}
                                    </h2>

                                    <p class="mt-1 text-xs font-semibold text-orange-100">
                                        Delivery Rider
                                    </p>
                                </div>
                            </div>

                            <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-xs font-black backdrop-blur">
                                <span class="h-2 w-2 rounded-full {{ $rider->is_active ? 'bg-emerald-300' : 'bg-red-300' }}"></span>

                                {{ $rider->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    {{-- Contact Information --}}
                    <div class="space-y-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Email Address
                            </p>

                            <a
                                href="mailto:{{ $rider->email }}"
                                class="mt-1 block break-all text-sm font-black text-slate-950 transition hover:text-orange-700"
                            >
                                {{ $rider->email }}
                            </a>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Phone Number
                            </p>

                            @if ($rider->phone)
                                <a
                                    href="tel:{{ $rider->phone }}"
                                    class="mt-1 block font-black text-slate-950 transition hover:text-orange-700"
                                >
                                    {{ $rider->phone }}
                                </a>
                            @else
                                <p class="mt-1 font-bold text-slate-400">
                                    Not provided
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Delivery Statistics --}}
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-blue-600">
                                Assigned
                            </p>

                            <p class="mt-2 text-2xl font-black text-blue-950">
                                {{ $assignedCount }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-blue-700">
                                Total orders
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-emerald-600">
                                Delivered
                            </p>

                            <p class="mt-2 text-2xl font-black text-emerald-950">
                                {{ $deliveredCount }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-emerald-700">
                                Completed
                            </p>
                        </div>
                    </div>

                    {{-- Completion Rate --}}
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                    Completion Rate
                                </p>

                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ $deliveryRate }}%
                                </p>
                            </div>

                            <div class="grid h-11 w-11 place-items-center rounded-xl bg-white text-emerald-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>
                            </div>
                        </div>

                        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full rounded-full bg-emerald-500"
                                style="width: {{ $deliveryRate }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a
                            href="{{ route('admin.riders.edit', $rider) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                        >
                            Edit Rider
                        </a>

                        <form
                            action="{{ route('admin.riders.destroy', $rider) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this rider account?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-black text-red-600 transition hover:bg-red-100"
                            >
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($riders->hasPages())
        <div class="mt-8 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
            {{ $riders->withQueryString()->links() }}
        </div>
    @endif
@endif

@endcomponent
