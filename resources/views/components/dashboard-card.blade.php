@props([
'title',
'description' => null,
'href' => null,
])

@if ($href)
<a
href="{{ $href }}"
{{ $attributes->class([
'group relative block overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm transition duration-300',
'hover:-translate-y-1 hover:border-orange-200 hover:shadow-xl hover:shadow-orange-900/5',
'focus:outline-none focus:ring-4 focus:ring-orange-100',
]) }}
>
{{-- Hover decoration --}} <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-orange-100/70 blur-2xl transition duration-300 group-hover:bg-orange-200/80"></div>

    <div class="relative flex items-start justify-between gap-5">
        <div class="min-w-0">
            <h3 class="break-words text-base font-black tracking-tight text-slate-950 transition group-hover:text-orange-700">
                {{ $title }}
            </h3>

            @if ($description)
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                    {{ $description }}
                </p>
            @endif
        </div>

        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 transition duration-300 group-hover:bg-orange-600 group-hover:text-white">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-5 w-5 transition duration-300 group-hover:translate-x-0.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m9 18 6-6-6-6"
                />
            </svg>
        </span>
    </div>

    <div class="relative mt-5 flex items-center gap-2 border-t border-orange-100 pt-4">
        <span class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">
            Open
        </span>

        <span class="h-px flex-1 bg-gradient-to-r from-orange-200 to-transparent"></span>
    </div>
</a>

@else
<div
{{ $attributes->class([
'relative overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm',
]) }}
> <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-orange-100/60 blur-2xl"></div>

    <div class="relative">
        <div class="mb-4 grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
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
                    d="M5 12h14M12 5v14"
                />
            </svg>
        </div>

        <h3 class="break-words text-base font-black tracking-tight text-slate-950">
            {{ $title }}
        </h3>

        @if ($description)
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                {{ $description }}
            </p>
        @endif
    </div>
</div>

@endif
