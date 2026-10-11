@component('layouts.admin', ['title' => 'Add-ons & Upselling'])
    @php
        $fieldClass = 'w-full rounded-2xl border border-warm-200 bg-white px-4 py-3 text-sm font-bold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100';
        $compactFieldClass = 'w-full rounded-xl border border-warm-200 bg-white px-3 py-2.5 text-sm font-bold text-warm-900 shadow-sm outline-none transition placeholder:text-warm-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100';
        $labelClass = 'text-[11px] font-black uppercase tracking-[0.14em] text-warm-500';
        $buttonClass = 'inline-flex min-h-11 items-center justify-center rounded-2xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-100';
        $darkButtonClass = 'inline-flex min-h-10 items-center justify-center rounded-xl bg-warm-950 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-warm-800 focus:outline-none focus:ring-4 focus:ring-warm-200';
        $softButtonClass = 'inline-flex min-h-10 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2.5 text-xs font-black text-warm-700 shadow-sm transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-100';
        $dangerButtonClass = 'inline-flex min-h-10 items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-black text-red-700 transition hover:border-red-200 hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-100';
        $toggleClass = 'inline-flex items-center gap-2 rounded-full border border-warm-200 bg-warm-50 px-3 py-2 text-xs font-black text-warm-600 transition has-[:checked]:border-leaf-200 has-[:checked]:bg-leaf-50 has-[:checked]:text-leaf-800';
        $section = $section ?? 'overview';
        $moduleNav = [
            'overview' => ['label' => 'Overview', 'route' => route('admin.addons-upselling.index'), 'description' => 'Module health'],
            'addons' => ['label' => 'Add-ons Library', 'route' => route('admin.addons-upselling.addons.index'), 'description' => 'Reusable extras'],
            'groups' => ['label' => 'Add-on Groups', 'route' => route('admin.addons-upselling.groups.index'), 'description' => 'Selection rules'],
            'assignments' => ['label' => 'Product Assignments', 'route' => route('admin.addons-upselling.assignments.index'), 'description' => 'Customization workspace'],
            'rules' => ['label' => 'Upselling Rules', 'route' => route('admin.addons-upselling.rules.index'), 'description' => 'Recommendations'],
        ];
        $emptyAddon = ['id' => null, 'name' => '', 'description' => '', 'price' => '', 'type' => 'customization', 'sort_order' => 0, 'is_available' => true, 'is_active' => true, 'image_url' => null];
        $selectedProductGroups = $selectedProduct?->activeAddonGroups ?? collect();
        $inheritedGroups = collect()
            ->concat($selectedProduct?->category?->activeAddonGroups ?? [])
            ->concat($selectedProduct?->restaurant?->globalAddonGroups ?? [])
            ->unique('id')
            ->values();
        $previewGroups = collect()->concat($selectedProductGroups)->concat($inheritedGroups)->unique('id')->values();
    @endphp

    <div
        class="space-y-6"
        x-data="{
            drawerOpen: false,
            drawerMode: 'create',
            addon: @js($emptyAddon),
            createAction: @js(route('admin.addons-upselling.addons.store')),
            editActionBase: @js(url('/admin/addons-upselling/addons')),
            openCreate() {
                this.drawerMode = 'create';
                this.addon = @js($emptyAddon);
                this.drawerOpen = true;
            },
            openEdit(payload) {
                this.drawerMode = 'edit';
                this.addon = payload;
                this.drawerOpen = true;
            },
            formAction() {
                return this.drawerMode === 'edit' ? `${this.editActionBase}/${this.addon.id}` : this.createAction;
            }
        }"
    >
        <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-warm-200">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-500">Menu growth</p>
                    <h1 class="mt-2 text-3xl font-black text-warm-950">Add-ons & Upselling</h1>
                    <p class="mt-2 max-w-3xl text-sm font-medium text-warm-600">
                        Compact tools for reusable extras, grouped customizations, product assignments, and cart recommendations.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-2xl bg-brand-50 px-4 py-3">
                        <p class="text-2xl font-black text-brand-600">{{ $addonTotal }}</p>
                        <p class="text-xs font-black uppercase text-brand-700">Add-ons</p>
                    </div>
                    <div class="rounded-2xl bg-leaf-50 px-4 py-3">
                        <p class="text-2xl font-black text-leaf-700">{{ $groupTotal }}</p>
                        <p class="text-xs font-black uppercase text-leaf-700">Groups</p>
                    </div>
                    <div class="rounded-2xl bg-warm-100 px-4 py-3">
                        <p class="text-2xl font-black text-warm-800">{{ $ruleTotal }}</p>
                        <p class="text-xs font-black uppercase text-warm-700">Rules</p>
                    </div>
                </div>
            </div>
        </div>

        <nav class="grid gap-3 md:grid-cols-5">
            @foreach ($moduleNav as $key => $item)
                <a href="{{ $item['route'] }}" class="rounded-3xl border p-4 shadow-sm transition {{ $section === $key ? 'border-brand-200 bg-brand-500 text-white shadow-brand-500/20' : 'border-warm-200 bg-white text-warm-700 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700' }}">
                    <span class="block text-sm font-black">{{ $item['label'] }}</span>
                    <span class="mt-1 block text-xs font-semibold {{ $section === $key ? 'text-white/75' : 'text-warm-500' }}">{{ $item['description'] }}</span>
                </a>
            @endforeach
        </nav>

        @if (session('status'))
            <div class="rounded-2xl border border-leaf-200 bg-leaf-50 px-4 py-3 text-sm font-bold text-leaf-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
        @endif

        @if ($section === 'overview')
            <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
                @foreach (array_slice($moduleNav, 1, null, true) as $key => $item)
                    <a href="{{ $item['route'] }}" class="group rounded-[2rem] border border-warm-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lg hover:shadow-brand-900/5">
                        <div class="flex h-full flex-col justify-between gap-8">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-500">{{ $item['label'] }}</p>
                                <h2 class="mt-3 text-2xl font-black text-warm-950">
                                    @if ($key === 'addons')
                                        {{ $addonTotal }} reusable add-ons
                                    @elseif ($key === 'groups')
                                        {{ $groupTotal }} configured groups
                                    @elseif ($key === 'assignments')
                                        Product customization workspace
                                    @else
                                        {{ $ruleTotal }} recommendation rules
                                    @endif
                                </h2>
                                <p class="mt-3 text-sm font-semibold leading-6 text-warm-500">{{ $item['description'] }}</p>
                            </div>
                            <span class="{{ $softButtonClass }} w-fit group-hover:border-brand-200 group-hover:bg-brand-50 group-hover:text-brand-600">Open</span>
                        </div>
                    </a>
                @endforeach
            </section>
        @endif

        @if ($section === 'addons')
            <section class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-warm-950">Add-ons Library</h2>
                        <p class="text-sm font-semibold text-warm-500">Search, filter, page, and edit add-ons without rendering forms in every row.</p>
                    </div>
                    <button type="button" x-on:click="openCreate()" class="{{ $buttonClass }}">Create Add-on</button>
                </div>

                <form method="GET" action="{{ route('admin.addons-upselling.addons.index') }}" class="mt-5 grid gap-3 rounded-3xl border border-warm-200 bg-warm-50/60 p-4 lg:grid-cols-[minmax(0,1fr)_180px_160px_170px_140px]">
                    <input name="q" value="{{ request('q') }}" placeholder="Search add-ons..." class="{{ $compactFieldClass }}" />
                    <select name="type" class="{{ $compactFieldClass }}">
                        <option value="">All types</option>
                        @foreach ($addonTypes as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="{{ $compactFieldClass }}">
                        <option value="">Any status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                    <select name="availability" class="{{ $compactFieldClass }}">
                        <option value="">Any availability</option>
                        <option value="available" @selected(request('availability') === 'available')>Available</option>
                        <option value="unavailable" @selected(request('availability') === 'unavailable')>Unavailable</option>
                    </select>
                    <button class="{{ $darkButtonClass }}">Filter</button>
                </form>

                <div class="mt-5 overflow-hidden rounded-3xl border border-warm-200">
                    <div class="hidden grid-cols-[minmax(0,1.4fr)_120px_150px_140px_150px] gap-4 bg-warm-50 px-5 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-warm-500 lg:grid">
                        <a href="{{ route('admin.addons-upselling.addons.index', array_merge(request()->except('page'), ['sort' => 'name'])) }}">Add-on</a>
                        <a href="{{ route('admin.addons-upselling.addons.index', array_merge(request()->except('page'), ['sort' => 'price'])) }}">Price</a>
                        <a href="{{ route('admin.addons-upselling.addons.index', array_merge(request()->except('page'), ['sort' => 'type'])) }}">Type</a>
                        <span>Groups</span>
                        <span class="text-right">Actions</span>
                    </div>

                    <div class="divide-y divide-warm-100">
                        @forelse ($addons as $addon)
                            @php
                                $addonPayload = [
                                    'id' => $addon->id,
                                    'name' => $addon->name,
                                    'description' => $addon->description,
                                    'price' => (float) $addon->price,
                                    'type' => $addon->type,
                                    'sort_order' => (int) $addon->sort_order,
                                    'is_available' => (bool) $addon->is_available,
                                    'is_active' => (bool) $addon->is_active,
                                    'image_url' => $addon->image_url,
                                ];
                            @endphp
                            <div class="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(0,1.4fr)_120px_150px_140px_150px] lg:items-center">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-brand-50 text-sm font-black text-brand-600">
                                        @if ($addon->image_url)
                                            <img src="{{ $addon->image_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            {{ mb_substr($addon->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-warm-950">{{ $addon->name }}</p>
                                        <p class="truncate text-xs font-semibold text-warm-500">{{ $addon->description ?: 'No description' }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="rounded-full px-2 py-1 text-[11px] font-black {{ $addon->is_available ? 'bg-leaf-50 text-leaf-700' : 'bg-red-50 text-red-700' }}">{{ $addon->is_available ? 'Available' : 'Unavailable' }}</span>
                                            <span class="rounded-full px-2 py-1 text-[11px] font-black {{ $addon->is_active ? 'bg-brand-50 text-brand-700' : 'bg-warm-100 text-warm-600' }}">{{ $addon->is_active ? 'Active' : 'Inactive' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm font-black text-warm-950">@money($addon->price)</p>
                                <p class="text-sm font-bold text-warm-600">{{ $addonTypes[$addon->type] ?? ucfirst($addon->type) }}</p>
                                <p class="text-sm font-bold text-warm-600">{{ $addon->assigned_groups_count }} group{{ $addon->assigned_groups_count === 1 ? '' : 's' }}</p>
                                <div class="flex items-center justify-start gap-2 lg:justify-end">
                                    <button
                                        type="button"
                                        class="{{ $softButtonClass }}"
                                        x-on:click="openEdit({{ \Illuminate\Support\Js::from($addonPayload) }})"
                                    >Edit</button>
                                    <form method="POST" action="{{ route('admin.addons-upselling.addons.destroy', $addon) }}" onsubmit="return confirm('Archive this add-on? Existing order history will stay unchanged.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="{{ $dangerButtonClass }}">Archive</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <p class="text-lg font-black text-warm-950">No add-ons found</p>
                                <p class="mt-2 text-sm font-semibold text-warm-500">Adjust filters or create the first reusable add-on.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if (method_exists($addons, 'links'))
                    <div class="mt-5">{{ $addons->links() }}</div>
                @endif
            </section>
        @endif

        @if ($section === 'groups')
            <section class="grid items-start gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
                <form method="POST" action="{{ route('admin.addons-upselling.groups.store') }}" class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    @csrf
                    <h2 class="text-xl font-black text-warm-950">Create Group</h2>
                    <div class="mt-5 grid gap-4">
                        <input name="name" placeholder="Choose Your Drink" required class="{{ $fieldClass }}" />
                        <textarea name="description" rows="2" placeholder="Optional description" class="{{ $fieldClass }} min-h-24"></textarea>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="selection_type" class="{{ $fieldClass }}">
                                @foreach ($selectionTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input name="max_selections" type="number" min="1" placeholder="Max" class="{{ $fieldClass }}" />
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <input name="min_selections" type="number" min="0" value="0" class="{{ $fieldClass }}" />
                            <input name="quantity_limit" type="number" min="1" value="1" class="{{ $fieldClass }}" />
                            <input name="sort_order" type="number" min="0" value="0" class="{{ $fieldClass }}" />
                        </div>
                        <div class="max-h-56 overflow-y-auto rounded-2xl border border-warm-200 bg-warm-50/60 p-3">
                            @foreach ($addons as $addon)
                                <label class="mb-2 flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 text-sm font-bold text-warm-700 ring-1 ring-warm-100 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                                    <span>{{ $addon->name }}</span>
                                    <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" class="h-4 w-4 rounded border-warm-300 text-brand-500">
                                </label>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="{{ $toggleClass }}"><input type="checkbox" name="is_required" value="1" class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Required</label>
                            <label class="{{ $toggleClass }}"><input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Active</label>
                        </div>
                        <button class="{{ $buttonClass }} w-full">Create Group</button>
                    </div>
                </form>

                <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    <h2 class="text-xl font-black text-warm-950">Add-on Groups</h2>
                    <p class="text-sm font-semibold text-warm-500">Collapsed summaries keep the page short. Open only the group you need.</p>
                    <div class="mt-5 grid gap-3 lg:grid-cols-2">
                        @forelse ($groups as $group)
                            <details class="group rounded-3xl border border-warm-200 bg-warm-50/50 p-4 open:bg-white open:shadow-sm">
                                <summary class="cursor-pointer list-none">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-black text-warm-950">{{ $group->name }}</h3>
                                            <p class="mt-1 text-xs font-bold text-warm-500">{{ $group->addons->count() }} add-ons · {{ $group->is_required ? 'Required' : 'Optional' }} · {{ $selectionTypes[$group->selection_type] ?? $group->selection_type }}</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $group->is_active ? 'bg-leaf-50 text-leaf-700' : 'bg-warm-100 text-warm-600' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
                                    </div>
                                </summary>
                                <form method="POST" action="{{ route('admin.addons-upselling.groups.update', $group) }}" class="mt-4 grid gap-3 border-t border-warm-200 pt-4">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" value="{{ $group->name }}" class="{{ $compactFieldClass }}" />
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <select name="selection_type" class="{{ $compactFieldClass }}">
                                            @foreach ($selectionTypes as $value => $label)
                                                <option value="{{ $value }}" @selected($group->selection_type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input name="max_selections" type="number" min="1" value="{{ $group->max_selections }}" placeholder="Max" class="{{ $compactFieldClass }}" />
                                    </div>
                                    <div class="grid max-h-40 gap-2 overflow-y-auto rounded-2xl border border-warm-200 bg-white p-3 sm:grid-cols-2">
                                        @foreach ($addons as $addon)
                                            <label class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-xs font-bold text-warm-600 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                                                <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" @checked($group->addons->contains('id', $addon->id)) class="h-4 w-4 rounded border-warm-300 text-brand-500">
                                                {{ $addon->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="{{ $toggleClass }}"><input type="checkbox" name="is_required" value="1" @checked($group->is_required) class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Required</label>
                                        <label class="{{ $toggleClass }}"><input type="checkbox" name="is_active" value="1" @checked($group->is_active) class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Active</label>
                                    </div>
                                    <input type="hidden" name="min_selections" value="{{ $group->min_selections }}" />
                                    <input type="hidden" name="quantity_limit" value="{{ $group->quantity_limit }}" />
                                    <input type="hidden" name="sort_order" value="{{ $group->sort_order }}" />
                                    <button class="{{ $darkButtonClass }} w-fit">Save Group</button>
                                </form>
                            </details>
                        @empty
                            <p class="rounded-3xl bg-warm-50 p-8 text-center text-sm font-bold text-warm-500 lg:col-span-2">No groups yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif

        @if ($section === 'assignments')
            <section class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)_340px]">
                <aside class="rounded-[2rem] bg-white p-4 shadow-sm ring-1 ring-warm-200">
                    <form method="GET" action="{{ route('admin.addons-upselling.assignments.index') }}" class="grid gap-3">
                        <input name="product_q" value="{{ request('product_q') }}" placeholder="Search products..." class="{{ $compactFieldClass }}" />
                        <button class="{{ $darkButtonClass }}">Search</button>
                    </form>
                    <div class="mt-4 max-h-[580px] space-y-2 overflow-y-auto pr-1">
                        @foreach ($menuItems as $item)
                            <a href="{{ route('admin.addons-upselling.assignments.index', array_filter(['product_id' => $item->id, 'product_q' => request('product_q')])) }}" class="block rounded-2xl border p-3 transition {{ $selectedProduct?->id === $item->id ? 'border-brand-200 bg-brand-50' : 'border-warm-200 bg-white hover:border-brand-200 hover:bg-brand-50' }}">
                                <p class="truncate text-sm font-black text-warm-950">{{ $item->name }}</p>
                                <p class="mt-1 text-xs font-semibold text-warm-500">{{ $item->category?->name ?? 'Uncategorised' }} · @money($item->price)</p>
                            </a>
                        @endforeach
                    </div>
                </aside>

                <main class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    @if ($selectedProduct)
                        <div class="flex flex-col gap-3 border-b border-warm-200 pb-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-500">Selected product</p>
                                <h2 class="mt-1 text-2xl font-black text-warm-950">{{ $selectedProduct->name }}</h2>
                                <p class="mt-1 text-sm font-semibold text-warm-500">{{ $selectedProduct->category?->name ?? 'Uncategorised' }}</p>
                            </div>
                            <span class="rounded-2xl bg-warm-100 px-4 py-2 text-sm font-black text-warm-800">@money($selectedProduct->price)</span>
                        </div>

                        <form method="POST" action="{{ route('admin.addons-upselling.assign-product') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="menu_item_id" value="{{ $selectedProduct->id }}" />
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach ($groups as $group)
                                    <label class="rounded-3xl border border-warm-200 bg-warm-50/50 p-4 transition has-[:checked]:border-brand-200 has-[:checked]:bg-brand-50">
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" name="addon_group_ids[]" value="{{ $group->id }}" @checked($selectedProductGroups->contains('id', $group->id)) class="mt-1 h-4 w-4 rounded border-warm-300 text-brand-500">
                                            <div class="min-w-0">
                                                <p class="font-black text-warm-950">{{ $group->name }}</p>
                                                <p class="mt-1 text-xs font-bold text-warm-500">{{ $group->addons->count() }} add-ons · {{ $group->is_required ? 'Required' : 'Optional' }} · {{ $selectionTypes[$group->selection_type] ?? $group->selection_type }}</p>
                                                <p class="mt-2 truncate text-xs font-semibold text-warm-500">{{ $group->addons->pluck('name')->take(4)->join(', ') }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <button class="{{ $buttonClass }} mt-5">Save Product Assignments</button>
                        </form>
                    @else
                        <div class="rounded-3xl bg-warm-50 p-10 text-center">
                            <p class="text-xl font-black text-warm-950">No products available</p>
                            <p class="mt-2 text-sm font-semibold text-warm-500">Create available menu items before assigning add-ons.</p>
                        </div>
                    @endif
                </main>

                <aside class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    <h2 class="text-xl font-black text-warm-950">Customer Preview</h2>
                    @if ($selectedProduct)
                        <div class="mt-4 rounded-3xl border border-warm-200 bg-warm-50/60 p-4">
                            <p class="text-lg font-black text-warm-950">{{ $selectedProduct->name }}</p>
                            <p class="mt-1 text-sm font-bold text-brand-600">@money($selectedProduct->price)</p>
                            <div class="mt-4 space-y-3">
                                @forelse ($previewGroups as $group)
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-warm-100">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-black text-warm-950">{{ $group->name }}</p>
                                            <span class="text-[11px] font-black uppercase text-warm-500">{{ $group->is_required ? 'Required' : 'Optional' }}</span>
                                        </div>
                                        <div class="mt-2 space-y-1.5">
                                            @foreach ($group->addons->take(4) as $addon)
                                                <div class="flex items-center justify-between text-xs font-bold text-warm-600">
                                                    <span>{{ $addon->name }}</span>
                                                    <span>+ @money($addon->price)</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-2xl bg-white p-4 text-sm font-bold text-warm-500">No customer customizations assigned.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </aside>
            </section>
        @endif

        @if ($section === 'rules')
            <section class="grid items-start gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
                <form method="POST" action="{{ route('admin.addons-upselling.rules.store') }}" class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    @csrf
                    <h2 class="text-xl font-black text-warm-950">Rule Wizard</h2>
                    <div class="mt-5 space-y-5">
                        <div>
                            <p class="{{ $labelClass }}">1. Choose trigger</p>
                            <input name="name" placeholder="Burger meal recommendation" required class="{{ $fieldClass }} mt-2" />
                            <select name="trigger_type" class="{{ $fieldClass }} mt-3">
                                @foreach ($triggers as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="{{ $labelClass }}">2. Configure conditions</p>
                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                <input name="min_subtotal" type="number" min="0" step="0.01" placeholder="Subtotal" class="{{ $fieldClass }}" />
                                <select name="missing_category_id" class="{{ $fieldClass }}">
                                    <option value="">Missing category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <p class="{{ $labelClass }}">3. Select recommendations</p>
                            <select name="recommended_product_ids[]" multiple required class="{{ $fieldClass }} mt-2 min-h-32">
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="{{ $labelClass }}">4. Placement and priority</p>
                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                <select name="placement" class="{{ $fieldClass }}">
                                    @foreach ($placements as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input name="priority" type="number" min="0" value="0" class="{{ $fieldClass }}" />
                            </div>
                        </div>
                        <div>
                            <p class="{{ $labelClass }}">5. Review</p>
                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                <input name="max_recommendations" type="number" min="1" max="20" placeholder="Max shown" class="{{ $fieldClass }}" />
                                <label class="{{ $toggleClass }}"><input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Active</label>
                            </div>
                            <label class="{{ $toggleClass }} mt-3"><input type="checkbox" name="allow_duplicates" value="1" class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Allow duplicate cart items</label>
                        </div>
                        <button class="{{ $buttonClass }} w-full">Save Rule</button>
                    </div>
                </form>

                <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-warm-200">
                    <h2 class="text-xl font-black text-warm-950">Upselling Rules</h2>
                    <div class="mt-5 overflow-hidden rounded-3xl border border-warm-200">
                        @forelse ($rules as $rule)
                            <div class="grid gap-3 border-b border-warm-100 px-5 py-4 last:border-b-0 lg:grid-cols-[minmax(0,1fr)_160px_120px_100px] lg:items-center">
                                <div>
                                    <p class="font-black text-warm-950">{{ $rule->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-warm-500">{{ $triggers[$rule->trigger_type] ?? $rule->trigger_type }} · {{ $placements[$rule->placement] ?? $rule->placement }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold text-warm-500">{{ $rule->products->pluck('name')->join(', ') ?: 'No products' }}</p>
                                </div>
                                <span class="text-sm font-bold text-warm-600">Priority {{ $rule->priority }}</span>
                                <span class="rounded-full px-3 py-1 text-center text-xs font-black {{ $rule->is_active ? 'bg-leaf-50 text-leaf-700' : 'bg-warm-100 text-warm-600' }}">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span>
                                <form method="POST" action="{{ route('admin.addons-upselling.rules.destroy', $rule) }}" onsubmit="return confirm('Delete this upselling rule?');" class="lg:text-right">
                                    @csrf
                                    @method('DELETE')
                                    <button class="{{ $dangerButtonClass }}">Delete</button>
                                </form>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm font-bold text-warm-500">No rules yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif

        @if ($section === 'addons')
        <div x-cloak x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-[80]" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-warm-950/40 backdrop-blur-sm" x-on:click="drawerOpen = false"></div>
            <aside class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-warm-200 px-6 py-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-500" x-text="drawerMode === 'edit' ? 'Edit add-on' : 'New add-on'"></p>
                        <h2 class="mt-1 text-2xl font-black text-warm-950" x-text="drawerMode === 'edit' ? addon.name : 'Create Add-on'"></h2>
                    </div>
                    <button type="button" x-on:click="drawerOpen = false" class="{{ $softButtonClass }}">Close</button>
                </div>

                <form method="POST" enctype="multipart/form-data" x-bind:action="formAction()" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="drawerMode === 'edit' ? 'PUT' : ''" x-bind:disabled="drawerMode !== 'edit'" />
                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-5">
                        <div class="grid gap-2">
                            <label class="{{ $labelClass }}">Name</label>
                            <input name="name" x-model="addon.name" required class="{{ $fieldClass }}" />
                        </div>
                        <div class="grid gap-2">
                            <label class="{{ $labelClass }}">Description</label>
                            <textarea name="description" x-model="addon.description" rows="4" class="{{ $fieldClass }} min-h-28"></textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <label class="{{ $labelClass }}">Price</label>
                                <input name="price" type="number" min="0" step="0.01" x-model="addon.price" required class="{{ $fieldClass }}" />
                            </div>
                            <div class="grid gap-2">
                                <label class="{{ $labelClass }}">Type</label>
                                <select name="type" x-model="addon.type" class="{{ $fieldClass }}">
                                    @foreach ($addonTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <label class="{{ $labelClass }}">Image</label>
                            <input type="file" name="image" class="block w-full rounded-2xl border border-dashed border-warm-300 bg-warm-50 px-4 py-3 text-sm font-bold text-warm-500 file:mr-3 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-xs file:font-black file:text-brand-600" />
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="{{ $toggleClass }}"><input type="checkbox" name="is_available" value="1" x-model="addon.is_available" class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Available</label>
                            <label class="{{ $toggleClass }}"><input type="checkbox" name="is_active" value="1" x-model="addon.is_active" class="h-4 w-4 rounded border-warm-300 text-leaf-600"> Active</label>
                        </div>
                        <input type="hidden" name="sort_order" x-model="addon.sort_order" />
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-warm-200 px-6 py-5">
                        <button type="button" x-on:click="drawerOpen = false" class="{{ $softButtonClass }}">Cancel</button>
                        <button class="{{ $buttonClass }}" x-text="drawerMode === 'edit' ? 'Save Changes' : 'Create Add-on'"></button>
                    </div>
                </form>
            </aside>
        </div>
        @endif
    </div>
@endcomponent
