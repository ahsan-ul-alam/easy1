@use('App\Models\Product')
@use('App\Support\Localization')

<x-layouts.admin :title="__('admin.products.title')">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('admin.products.title') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('admin.products.intro') }}</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> {{ __('admin.products.add') }}</a>
    </div>

    <form action="{{ route('admin.products.index') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row" data-search-form data-search-target="#admin-product-results">
        <div class="relative flex-1">
            <label for="admin-search" class="sr-only">{{ __('admin.products.search_label') }}</label>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-stone-400" />
            <input id="admin-search" type="search" name="q" value="{{ $search }}" placeholder="{{ __('admin.products.search_placeholder') }}" autocomplete="off" maxlength="100" class="input pl-10">
            <span class="spinner absolute top-1/2 right-3.5 -mt-2 text-stone-400" data-search-spinner hidden></span>
        </div>
        <label for="status-filter" class="sr-only">{{ __('admin.products.status_filter') }}</label>
        <select id="status-filter" name="status" class="input sm:w-44">
            <option value="">{{ __('admin.products.filter_all') }}</option>
            <option value="active" @selected($status === 'active')>{{ __('admin.products.filter_active') }}</option>
            <option value="inactive" @selected($status === 'inactive')>{{ __('admin.products.filter_inactive') }}</option>
        </select>
        <noscript><button type="submit" class="btn btn-outline">{{ __('admin.products.search') }}</button></noscript>
    </form>

    <div id="admin-product-results" class="mt-6" aria-live="polite">
        @fragment('results')
            @if ($products->isEmpty())
                <div class="card flex flex-col items-center px-6 py-16 text-center">
                    <span class="grid size-14 place-items-center rounded-2xl bg-stone-100 text-stone-400"><x-icon name="cube" class="size-6" /></span>
                    @if ($search !== '' || $status !== '')
                        <p class="mt-4 font-semibold">{{ __('admin.products.empty_filtered_title') }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ __('admin.products.empty_filtered_text') }}</p>
                    @else
                        <p class="mt-4 font-semibold">{{ __('admin.products.empty_title') }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ __('admin.products.empty_text') }}</p>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-6"><x-icon name="plus" class="size-4" /> {{ __('admin.products.add') }}</a>
                    @endif
                </div>
            @else
                <div class="card divide-y divide-stone-100 overflow-hidden">
                    @foreach ($products as $product)
                        @php($missingLanguages = collect(Localization::LOCALES)->filter(fn ($language, $code) => blank($product->{'name_'.$code}))->pluck('name'))
                        <div class="flex items-center gap-3 p-3 sm:gap-4 sm:p-4" data-product-row>
                            <a href="{{ route('admin.products.edit', $product) }}" class="block size-14 shrink-0 overflow-hidden rounded-xl bg-stone-100 sm:size-16">
                                <x-product-image :image="$product->images->first()" :alt="$product->name" sizes="64px" />
                            </a>

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="block truncate font-medium hover:underline">{{ $product->name }}</a>
                                <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm text-stone-500 tabular-nums">
                                    <span>{{ $product->price_label }}</span>
                                    <span class="text-stone-300">·</span>
                                    <span>{{ __('admin.products.images_count', ['count' => Localization::digits($product->images->count()), 'max' => Localization::digits(Product::MAX_IMAGES)]) }}</span>
                                    @foreach ($missingLanguages as $language)
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-amber-200">{{ __('admin.products.missing_translation', ['language' => $language]) }}</span>
                                    @endforeach
                                </p>
                            </div>

                            <label class="flex shrink-0 cursor-pointer items-center gap-2" title="{{ __('admin.products.toggle_title') }}">
                                <input type="checkbox" class="peer sr-only" data-status-toggle data-url="{{ route('admin.products.status', $product) }}" @checked($product->is_active) aria-label="{{ __('admin.products.toggle_title') }}">
                                <span class="relative h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-emerald-500 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-stone-900 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                                <span class="hidden w-16 text-xs font-medium text-stone-600 sm:inline" data-status-label>{{ $product->is_active ? __('admin.products.visible') : __('admin.products.hidden') }}</span>
                            </label>

                            <div class="flex shrink-0 items-center gap-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="grid size-9 place-items-center rounded-full text-stone-500 hover:bg-stone-100 hover:text-stone-900" aria-label="{{ __('admin.products.edit_aria', ['name' => $product->name]) }}">
                                    <x-icon name="pencil" class="size-5" />
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-delete-form data-confirm="{{ __('admin.products.delete_confirm', ['name' => $product->name]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="grid size-9 place-items-center rounded-full text-stone-500 hover:bg-red-50 hover:text-red-600" aria-label="{{ __('admin.products.delete_aria', ['name' => $product->name]) }}">
                                        <x-icon name="trash" class="size-5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <div class="mt-6">{{ $products->links() }}</div>
                @endif
            @endif
        @endfragment
    </div>
</x-layouts.admin>
