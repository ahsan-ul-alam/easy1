@use('App\Support\Localization')
@use('App\Support\SiteSettings')

@php
    // [setting key, input type]; language-specific settings get a Bengali and an English field.
    $sections = [
        'business' => [['business_name', 'text'], ['tagline', 'text']],
        'ordering' => [['whatsapp_number', 'tel'], ['phone', 'tel'], ['email', 'email'], ['address', 'textarea'], ['business_hours', 'text'], ['facebook_url', 'url'], ['instagram_url', 'url']],
        'homepage' => [['hero_title', 'text'], ['hero_subtitle', 'textarea'], ['meta_description', 'textarea']],
        'pages' => [['contact_intro', 'textarea'], ['privacy_policy', 'textarea-lg']],
    ];
@endphp

<x-layouts.admin :title="__('admin.settings.title')">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ __('admin.settings.title') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ __('admin.settings.intro') }}</p>
    </div>

    @if (! $site->whatsappNumber())
        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <x-icon name="whatsapp" class="mt-0.5 size-5 text-amber-700" />
            <p><strong>{{ __('admin.settings.whatsapp_missing_title') }}</strong> {{ __('admin.settings.whatsapp_missing_text') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ __('admin.fix_errors') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6" data-submit-loading data-preserve-form="settings">
        @csrf
        @method('PUT')

        <p class="flex items-start gap-2 rounded-2xl bg-white/60 px-4 py-3 text-sm text-stone-600 ring-1 ring-stone-200">
            <x-icon name="chat" class="mt-0.5 size-4 text-stone-400" />
            {{ __('admin.settings.translation_help') }}
        </p>

        <section class="card p-5 sm:p-6">
            <h2 class="font-semibold">{{ __('admin.settings.logo') }}</h2>
            <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="grid h-20 w-40 shrink-0 place-items-center overflow-hidden rounded-xl border border-stone-200 bg-stone-50 p-2">
                    <img src="{{ $site->logoUrl() }}" alt="{{ __('admin.settings.current_logo') }}" class="max-h-full max-w-full object-contain" data-logo-preview @if (! $site->logoUrl()) hidden @endif>
                    <span class="text-xs text-stone-400" data-logo-placeholder @if ($site->logoUrl()) hidden @endif>{{ __('admin.settings.no_logo') }}</span>
                </div>
                <div class="min-w-0 space-y-3">
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-full file:border-0 file:bg-stone-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-stone-700" data-logo-input>
                    <p class="field-hint">{{ __('admin.settings.logo_help') }}</p>
                    @if ($site->logoUrl())
                        <label class="flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="remove_logo" value="1" class="size-4 rounded border-stone-300 accent-red-600"> {{ __('admin.settings.remove_logo') }}
                        </label>
                    @endif
                    @error('logo') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        @foreach ($sections as $section => $fields)
            <section class="card p-5 sm:p-6">
                <h2 class="font-semibold">{{ __('admin.settings.sections.'.$section) }}</h2>
                <div class="mt-4 space-y-6">
                    @foreach ($fields as [$key, $type])
                        @php
                            $isTranslatable = in_array($key, SiteSettings::TRANSLATABLE, true);
                            $inputs = $isTranslatable
                                ? collect(Localization::LOCALES)->map(fn ($language, $code) => ['name' => "{$key}_{$code}", 'language' => $language['name'], 'lang' => $code])->values()->all()
                                : [['name' => $key, 'language' => null, 'lang' => null]];
                            $field = __('admin.settings.fields.'.$key);
                        @endphp
                        <fieldset>
                            <legend class="label">{{ $field['label'] }}</legend>
                            <div @class(['grid gap-3', 'md:grid-cols-2' => $isTranslatable])>
                                @foreach ($inputs as $input)
                                    @php($value = old($input['name'], $site->raw($input['name'])))
                                    <div>
                                        @if ($input['language'])
                                            <label for="{{ $input['name'] }}" class="mb-1 inline-block rounded-md bg-stone-100 px-1.5 py-0.5 text-xs font-medium text-stone-600">{{ $input['language'] }}</label>
                                        @endif
                                        @if (str_starts_with($type, 'textarea'))
                                            <textarea id="{{ $input['name'] }}" name="{{ $input['name'] }}" rows="{{ $type === 'textarea-lg' ? 10 : 3 }}" @if ($input['lang']) lang="{{ $input['lang'] }}" @endif @if (! $input['language']) aria-label="{{ $field['label'] }}" @endif @class(['input', 'input-error' => $errors->has($input['name'])])>{{ $value }}</textarea>
                                        @else
                                            <input id="{{ $input['name'] }}" type="{{ $type }}" name="{{ $input['name'] }}" value="{{ $value }}" @if ($input['lang']) lang="{{ $input['lang'] }}" @endif @if (! $input['language']) aria-label="{{ $field['label'] }}" @endif @if ($key === 'business_name') required @endif @class(['input', 'input-error' => $errors->has($input['name'])])>
                                        @endif
                                        @error($input['name']) <p class="field-error">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            @if ($field['hint']) <p class="field-hint">{{ $field['hint'] }}</p> @endif
                        </fieldset>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" class="btn btn-primary shadow-lift" data-submit-button>
                <span class="spinner" data-submit-spinner hidden></span>
                {{ __('admin.settings.save') }}
            </button>
        </div>
    </form>
</x-layouts.admin>
