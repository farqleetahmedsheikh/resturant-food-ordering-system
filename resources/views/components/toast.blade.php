@php
    $messages = collect([
        'success' => session('success') ?? session('status'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ])->filter();

    $styles = [
        'success' => [
            'box' => 'border-leaf-100 bg-leaf-50 text-leaf-700',
            'icon' => 'bg-white text-leaf-700',
            'iconName' => 'check',
        ],
        'error' => [
            'box' => 'border-red-100 bg-red-50 text-red-700',
            'icon' => 'bg-white text-red-600',
            'iconName' => 'trash',
        ],
        'warning' => [
            'box' => 'border-gold-100 bg-gold-50 text-gold-700',
            'icon' => 'bg-white text-gold-700',
            'iconName' => 'receipt',
        ],
        'info' => [
            'box' => 'border-blue-100 bg-blue-50 text-blue-700',
            'icon' => 'bg-white text-blue-600',
            'iconName' => 'receipt',
        ],
    ];
@endphp

@if ($messages->isNotEmpty())
    <div
        class="fixed right-4 top-20 z-[260] grid w-[calc(100vw-2rem)] max-w-sm gap-3 sm:right-6"
        aria-live="polite"
        aria-atomic="true"
    >
        @foreach ($messages as $type => $message)
            @php($style = $styles[$type] ?? $styles['info'])

            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5200)"
                x-show="show"
                x-transition
                class="rounded-2xl border p-4 shadow-xl shadow-warm-950/10 {{ $style['box'] }}"
                role="{{ $type === 'error' ? 'alert' : 'status' }}"
            >
                <div class="flex items-start gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl shadow-sm {{ $style['icon'] }}">
                        <x-ui-icon :name="$style['iconName']" class="h-4 w-4" />
                    </span>

                    <p class="min-w-0 flex-1 text-sm font-black leading-6">
                        {{ $message }}
                    </p>

                    <button
                        type="button"
                        x-on:click="show = false"
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-white/70 text-current transition hover:bg-white"
                        aria-label="Dismiss notification"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
