@component('layouts.admin', ['title' => 'Menu Hero Images'])
@php
    $slideRows = old('slides', $slides);
    $slideRows = collect($slideRows)->values()->map(fn ($slide) => [
        'id' => $slide['id'] ?? '',
        'eyebrow' => $slide['eyebrow'] ?? '',
        'title' => $slide['title'] ?? '',
        'highlight' => $slide['highlight'] ?? '',
        'description' => $slide['description'] ?? '',
        'image' => $slide['image'] ?? '',
        'is_active' => (bool) ($slide['is_active'] ?? false),
    ])->all();
    $slideRows = collect($slideRows)->map(function ($slide) {
        $slide['preview'] = str_starts_with($slide['image'], '/')
            ? asset(ltrim($slide['image'], '/'))
            : \App\Support\ImageUpload::url($slide['image']);
        return $slide;
    })->all();
@endphp

<main class="mx-auto max-w-[1500px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <header class="flex flex-col justify-between gap-4 rounded-3xl border border-warm-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">Public menu presentation</p>
            <h1 class="mt-2 text-2xl font-black text-warm-950 sm:text-3xl">Menu hero images</h1>
            <p class="mt-1 max-w-2xl text-sm text-warm-600">Manage the photos and copy customers see at the top of the menu. Active slides rotate every 3 seconds.</p>
        </div>
        <a href="{{ route('menu') }}" target="_blank" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 px-4 text-sm font-bold text-warm-700 hover:bg-warm-50">Preview menu</a>
    </header>

    @if (session('status'))
        <div class="rounded-xl border border-leaf-200 bg-leaf-50 px-4 py-3 text-sm font-semibold text-leaf-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">Please check the highlighted slide fields and try again.</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.menu-hero.update') }}" enctype="multipart/form-data"
        x-data="menuHeroManager(@js($slideRows))" x-on:submit="submitting = true">
        @csrf
        @method('PUT')

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm font-semibold text-warm-600">Add up to 8 slides. At least one slide must remain active.</p>
            <button type="button" x-on:click="addSlide()" x-bind:disabled="slides.length >= 8" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-500 px-4 text-sm font-black text-white hover:bg-brand-600 disabled:opacity-50">
                <span aria-hidden="true">+</span> Add slide
            </button>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <template x-for="(slide, index) in slides" x-bind:key="slide.key">
                <section class="overflow-hidden rounded-2xl border border-warm-200 bg-white shadow-sm">
                    <div class="relative aspect-[16/5] overflow-hidden bg-warm-100">
                        <img x-bind:src="slide.preview || ''" x-show="slide.preview" alt="Hero slide preview" class="h-full w-full object-cover">
                        <div x-show="!slide.preview" class="grid h-full place-items-center text-sm font-semibold text-warm-500">Choose a hero image</div>
                        <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/30 to-transparent"></div>
                        <div class="absolute inset-y-0 left-0 flex max-w-[70%] flex-col justify-center p-5 text-white sm:p-7">
                            <span class="text-[10px] font-black uppercase tracking-[0.18em]" x-text="slide.eyebrow || 'Your headline' "></span>
                            <span class="mt-2 text-xl font-black leading-tight sm:text-3xl"><span x-text="slide.title || 'Hero title'"></span> <span class="text-red-300" x-text="slide.highlight || 'highlight'"></span></span>
                            <span class="mt-2 line-clamp-2 text-xs text-white/85 sm:text-sm" x-text="slide.description || 'Short supporting line' "></span>
                        </div>
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-black text-warm-900" x-text="`Slide ${index + 1}`"></span>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <input type="hidden" x-bind:name="`slides[${index}][id]`" x-model="slide.id">
                        <label class="text-xs font-bold text-warm-700">Eyebrow
                            <input type="text" maxlength="100" x-bind:name="`slides[${index}][eyebrow]`" x-model="slide.eyebrow" required class="mt-1 h-11 w-full rounded-xl border border-warm-200 px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </label>
                        <label class="text-xs font-bold text-warm-700">Main heading
                            <input type="text" maxlength="80" x-bind:name="`slides[${index}][title]`" x-model="slide.title" required class="mt-1 h-11 w-full rounded-xl border border-warm-200 px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </label>
                        <label class="text-xs font-bold text-warm-700">Highlighted heading
                            <input type="text" maxlength="80" x-bind:name="`slides[${index}][highlight]`" x-model="slide.highlight" required class="mt-1 h-11 w-full rounded-xl border border-warm-200 px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </label>
                        <label class="text-xs font-bold text-warm-700">Supporting text
                            <input type="text" maxlength="200" x-bind:name="`slides[${index}][description]`" x-model="slide.description" required class="mt-1 h-11 w-full rounded-xl border border-warm-200 px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </label>
                        <label class="text-xs font-bold text-warm-700 sm:col-span-2">Hero image <span class="font-normal text-warm-500">(JPG/PNG, max 2 MB)</span>
                            <input type="file" accept="image/jpeg,image/png" x-bind:name="`slides[${index}][image]`" x-on:change="setPreview($event, index)" class="mt-1 block w-full rounded-xl border border-warm-200 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-brand-600">
                        </label>
                        <div class="flex flex-wrap items-center justify-between gap-3 sm:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-warm-700">
                                <input type="checkbox" x-bind:name="`slides[${index}][is_active]`" value="1" x-model="slide.is_active" class="h-4 w-4 rounded border-warm-300 text-brand-500 focus:ring-brand-500">
                                Show this slide on the menu
                            </label>
                            <button type="button" x-on:click="removeSlide(index)" x-bind:disabled="slides.length <= 1" class="text-sm font-bold text-red-600 hover:text-red-800 disabled:opacity-40">Remove slide</button>
                        </div>
                    </div>
                </section>
            </template>
        </div>

        <div class="sticky bottom-3 z-20 mt-6 flex justify-end rounded-2xl border border-warm-200 bg-white/95 p-3 shadow-lg backdrop-blur">
            <button type="submit" x-bind:disabled="submitting" class="min-h-12 rounded-xl bg-brand-500 px-6 text-sm font-black text-white hover:bg-brand-600 disabled:opacity-70" x-text="submitting ? 'Saving…' : 'Save hero slides'"></button>
        </div>
    </form>
</main>

@push('scripts')
<script>
    function menuHeroManager(initialSlides) {
        return {
            slides: initialSlides.map((slide, index) => ({ ...slide, key: `existing-${index}` })),
            submitting: false,
            addSlide() {
                if (this.slides.length >= 8) return;
                this.slides.push({ key: `new-${Date.now()}`, id: '', eyebrow: '', title: '', highlight: '', description: '', preview: '', is_active: true });
            },
            removeSlide(index) {
                if (this.slides.length > 1) this.slides.splice(index, 1);
            },
            setPreview(event, index) {
                const file = event.target.files?.[0];
                if (file) this.slides[index].preview = URL.createObjectURL(file);
            },
        };
    }
</script>
@endpush
@endcomponent
