@use('App\Models\Product')
@use('App\Support\Localization')

@php
    $isEditing = $product->exists;
    $existingImages = $isEditing ? $product->images : collect();
    $removedImages = collect(old('remove_images', []))->map(fn ($id) => (int) $id);
    $pageTitle = $isEditing ? __('admin.form.title_edit') : __('admin.form.title_create');
@endphp

<x-layouts.admin :title="$pageTitle">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-900"><x-icon name="arrow-left" class="size-4" /> {{ __('admin.form.back') }}</a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight break-words">{{ $isEditing ? $product->name : __('admin.form.heading_create') }}</h1>
        </div>
        @if ($isEditing && $product->is_active)
            <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm shrink-0"><x-icon name="external" class="size-4" /> {{ __('admin.form.view_in_shop') }}</a>
        @endif
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            {{ __('admin.fix_errors') }}
        </div>
    @endif

    <form method="POST"
          action="{{ $isEditing ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="mt-6 grid gap-6 lg:grid-cols-3"
          data-submit-loading
          data-preserve-form="product-{{ $product->id ?? 'new' }}">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="space-y-6 lg:col-span-2">
            <section class="card space-y-5 p-5 sm:p-6">
                <div>
                    <h2 class="font-semibold">{{ __('admin.form.details') }}</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ __('admin.form.translation_help') }}</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @foreach (Localization::LOCALES as $code => $language)
                        <div>
                            <label for="name_{{ $code }}" class="label">{{ __('admin.form.name') }} <span class="ml-1 rounded-md bg-stone-100 px-1.5 py-0.5 text-xs font-medium text-stone-600">{{ $language['name'] }}</span></label>
                            <input id="name_{{ $code }}" type="text" name="name_{{ $code }}" value="{{ old('name_'.$code, $product->{'name_'.$code}) }}" maxlength="255" lang="{{ $code }}" placeholder="{{ __('admin.form.name_placeholder', [], $code) }}" @class(['input', 'input-error' => $errors->has('name_'.$code)])>
                            @error('name_'.$code) <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <div>
                    <label for="price" class="label">{{ __('admin.form.price') }}</label>
                    <div class="relative sm:max-w-xs">
                        <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-sm font-medium text-stone-500">৳</span>
                        <input id="price" type="number" name="price" value="{{ old('price', $product->price) }}" required min="0" max="10000000" step="1" inputmode="numeric" placeholder="850" @class(['input pl-8', 'input-error' => $errors->has('price')])>
                    </div>
                    @error('price') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @foreach (Localization::LOCALES as $code => $language)
                        <div>
                            <label for="description_{{ $code }}" class="label">{{ __('admin.form.description') }} <span class="ml-1 rounded-md bg-stone-100 px-1.5 py-0.5 text-xs font-medium text-stone-600">{{ $language['name'] }}</span> <span class="font-normal text-stone-400">{{ __('admin.optional') }}</span></label>
                            <textarea id="description_{{ $code }}" name="description_{{ $code }}" rows="7" maxlength="10000" lang="{{ $code }}" placeholder="{{ __('admin.form.description_placeholder', [], $code) }}" @class(['input', 'input-error' => $errors->has('description_'.$code)])>{{ old('description_'.$code, $product->{'description_'.$code}) }}</textarea>
                            @error('description_'.$code) <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card p-5 sm:p-6" data-image-manager data-max="{{ Product::MAX_IMAGES }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">{{ __('admin.form.images') }}</h2>
                        <p class="mt-1 text-sm text-stone-500">{{ __('admin.form.images_help', ['max' => Localization::digits(Product::MAX_IMAGES)]) }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600 tabular-nums" data-image-count></span>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3" data-image-grid>
                    @foreach ($existingImages as $image)
                        <div class="group relative aspect-square overflow-hidden rounded-xl bg-stone-100 ring-1 ring-stone-200" data-existing-image>
                            <img src="{{ $image->thumbnail_url }}" alt="{{ __('admin.form.image_alt', ['number' => Localization::digits($loop->iteration), 'name' => $product->name]) }}" class="size-full object-cover transition">
                            <label class="absolute inset-x-2 bottom-2 flex cursor-pointer items-center justify-center gap-1.5 rounded-full bg-white/95 px-2 py-1.5 text-xs font-semibold text-stone-700 shadow-sm hover:text-red-600 has-checked:bg-red-600 has-checked:text-white">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="sr-only" data-remove-image @checked($removedImages->contains($image->id))>
                                <x-icon name="trash" class="size-3.5" /> <span data-remove-label>{{ __('admin.form.remove') }}</span>
                            </label>
                            @if ($loop->first)
                                <span class="absolute top-2 left-2 rounded-full bg-stone-900/80 px-2 py-0.5 text-[10px] font-semibold text-white">{{ __('admin.form.main') }}</span>
                            @endif
                        </div>
                    @endforeach

                    <label class="flex aspect-square cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-300 text-center text-stone-500 transition hover:border-stone-900 hover:text-stone-900" data-image-drop>
                        <x-icon name="photo" class="size-7" />
                        <span class="px-2 text-xs font-medium">{{ __('admin.form.add_images') }}</span>
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only" data-image-input>
                    </label>
                </div>

                @error('images') <p class="field-error">{{ $message }}</p> @enderror
                @foreach ($errors->get('images.*') as $messages)
                    <p class="field-error">{{ $messages[0] }}</p>
                @endforeach
                <p class="field-hint">{{ __('admin.form.replace_hint') }}</p>
            </section>
        </div>

        <div class="space-y-6">
            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold">{{ __('admin.form.visibility') }}</h2>
                <input type="hidden" name="is_active" value="0">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked(old('is_active', $product->is_active))>
                    <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-stone-300 transition peer-checked:bg-emerald-500 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-stone-900 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                    <span>
                        <span class="block text-sm font-medium">{{ __('admin.form.show_in_shop') }}</span>
                        <span class="block text-xs text-stone-500">{{ __('admin.form.show_in_shop_help') }}</span>
                    </span>
                </label>
            </section>

            @if ($isEditing)
                <section class="card p-5 sm:p-6">
                    <label for="slug" class="label">{{ __('admin.form.web_address') }}</label>
                    <div class="flex items-center rounded-xl border border-stone-300 bg-stone-50 text-sm focus-within:border-stone-900 focus-within:ring-2 focus-within:ring-stone-900/10 @error('slug') border-red-400 @enderror">
                        <span class="hidden shrink-0 pl-3.5 text-stone-400 sm:inline">/products/</span>
                        <input id="slug" type="text" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="190" class="w-full min-w-0 rounded-xl border-0 bg-transparent px-3.5 py-2.5 focus:ring-0 focus:outline-none sm:pl-0.5">
                    </div>
                    @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                    <p class="field-hint">{{ __('admin.form.slug_help') }}</p>
                </section>
            @endif

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn-primary w-full" data-submit-button>
                    <span class="spinner" data-submit-spinner hidden></span>
                    {{ $isEditing ? __('admin.form.save') : __('admin.form.create') }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline w-full">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>

    @if ($isEditing)
        <section class="card mt-10 flex flex-col gap-4 border-red-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h2 class="font-semibold text-red-700">{{ __('admin.form.delete_title') }}</h2>
                <p class="mt-1 text-sm text-stone-600">{{ __('admin.form.delete_text') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm-submit="{{ __('admin.form.delete_confirm') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" class="size-4" /> {{ __('admin.form.delete_button') }}</button>
            </form>
        </section>
    @endif
</x-layouts.admin>
