@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Confirm action',
    'description' => 'Please confirm this action.',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'variant' => 'danger',
    'buttonClass' => 'inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-black text-red-600 transition hover:bg-red-50',
])

@php
    $dialogId = 'confirm-dialog-'.\Illuminate\Support\Str::uuid()->toString();
    $variantClasses = match ($variant) {
        'brand' => [
            'icon' => 'bg-brand-50 text-brand-500',
            'button' => 'bg-brand-500 hover:bg-brand-600',
            'iconName' => 'receipt',
        ],
        default => [
            'icon' => 'bg-red-50 text-red-600',
            'button' => 'bg-red-600 hover:bg-red-700',
            'iconName' => 'trash',
        ],
    };
@endphp

<div
    x-data="{ open: false, submitting: false }"
    x-on:keydown.escape.window="open = false"
>
    <button
        type="button"
        x-on:click="open = true; $nextTick(() => $refs.cancelButton?.focus())"
        {{ $attributes->class($buttonClass) }}
    >
        {{ $slot }}
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[300] grid place-items-center bg-warm-950/55 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $dialogId }}"
    >
        <div
            x-show="open"
            x-transition
            x-on:click.outside="open = false"
            class="w-full max-w-md rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-warm-950/20"
        >
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $variantClasses['icon'] }}">
                    <x-ui-icon :name="$variantClasses['iconName']" class="h-5 w-5" />
                </span>

                <div>
                    <h2 id="{{ $dialogId }}" class="text-lg font-black text-warm-950">
                        {{ $title }}
                    </h2>

                    <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                        {{ $description }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-ref="cancelButton"
                    x-on:click="open = false"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-700 transition hover:bg-warm-50"
                >
                    {{ $cancelLabel }}
                </button>

                <form
                    action="{{ $action }}"
                    method="POST"
                    x-on:submit="submitting = true"
                >
                    @csrf

                    @if (strtoupper($method) !== 'POST')
                        @method($method)
                    @endif

                    <button
                        type="submit"
                        x-bind:disabled="submitting"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-black text-white transition disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto {{ $variantClasses['button'] }}"
                    >
                        <span x-text="submitting ? 'Working...' : @js($confirmLabel)"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
