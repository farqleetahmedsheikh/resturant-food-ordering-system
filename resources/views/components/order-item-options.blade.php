@props(['item'])

@php
$addons = collect($item->addons_snapshot ?? [])
->filter(fn ($addon) => ! empty($addon['name']));

$hasSizePrice = (float) ($item->size_price ?? 0) > 0;
$hasAddonsPrice = (float) ($item->addons_total ?? 0) > 0;

@endphp

@if ($item->size_name || $addons->isNotEmpty())
<div
{{ $attributes->class([
'mt-3 overflow-hidden rounded-2xl border border-warm-200 bg-brand-50/70',
]) }}
> <div class="space-y-3 px-4 py-3">
@if ($item->size_name) <div class="flex items-start gap-3"> <div class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm"> <svg
                         xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         class="h-4 w-4"
                     > <circle cx="12" cy="12" r="8" /> <circle cx="12" cy="12" r="3" /> </svg> </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-brand-500">
                        Selected Size
                    </p>

                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="break-words text-xs font-black text-warm-900">
                            {{ $item->size_name }}
                        </span>

                        @if ($hasSizePrice)
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-brand-600 shadow-sm">
                                ($item->size_price)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($item->size_name && $addons->isNotEmpty())
            <div class="border-t border-warm-200"></div>
        @endif

        @if ($addons->isNotEmpty())
            <div class="flex items-start gap-3">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                    >
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-brand-500">
                            Selected Extras
                        </p>

                        @if ($hasAddonsPrice)
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-brand-600 shadow-sm">
                                + ($item->addons_total)
                            </span>
                        @endif
                    </div>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($addons as $addon)
                            <span class="inline-flex max-w-full items-center rounded-full border border-warm-200 bg-white px-2.5 py-1 text-[11px] font-bold text-warm-600 shadow-sm">
                                <span class="truncate">
                                    {{ $addon['name'] }}
                                </span>

                                @if (! empty($addon['price']) && (float) $addon['price'] > 0)
                                    <span class="ml-1 whitespace-nowrap text-brand-500">
                                        +{{ number_format($addon['price'], 0) }}
                                    </span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endif
