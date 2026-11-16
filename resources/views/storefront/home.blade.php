@use('App\Support\Localization')

<x-layouts.store :title="$search !== '' ? __('app.home.search_title', ['term' => $search]) : null" :canonical="route('home')">
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(60rem_30rem_at_85%_-10%,rgb(37_211_102/0.12),transparent),radial-gradient(40rem_24rem_at_0%_0%,rgb(214_211_209/0.5),transparent)]"></div>

        <div class="container-page grid items-center gap-10 py-14 sm:py-20 lg:grid-cols-12 lg:py-24">
            <div class="lg:col-span-7">
                @if ($site->tagline)
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white/70 px-3 py-1 text-xs font-medium text-stone-600">
                        <span class="size-1.5 rounded-full bg-whatsapp"></span>{{ $site->tagline }}
                    </p>
                @endif
                <h1 class="max-w-2xl text-4xl leading-tight font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl">{{ $site->hero_title }}</h1>
                <p class="mt-5 max-w-xl text-base text-pretty text-stone-600 sm:text-lg">{{ $site->hero_subtitle }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#products" class="btn btn-primary">{{ __('app.home.shop_now') }} <x-icon name="arrow-right" class="size-4" /></a>
                    @if ($site->whatsappNumber())
                        <a href="https://wa.me/{{ $site->whatsappNumber() }}" target="_blank" rel="noopener" class="btn btn-outline"><x-icon name="whatsapp" class="size-4 text-whatsapp-dark" /> {{ __('app.whatsapp.chat') }}</a>
                    @endif
                </div>
            </div>

            <ol class="grid gap-3 sm:grid-cols-3 lg:col-span-5 lg:grid-cols-1">
                @foreach (__('app.order_steps') as $step)
                    @php($icon = ['bag', 'whatsapp', 'check'][$loop->index])
                    <li class="card flex items-start gap-4 p-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $icon === 'whatsapp' ? 'bg-whatsapp/10 text-whatsapp-dark' : 'bg-stone-100 text-stone-700' }}">
                            <x-icon :name="$icon" class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold">{{ Localization::digits($loop->iteration) }}. {{ $step['title'] }}</p>
                            <p class="mt-0.5 text-sm text-stone-600">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="products" class="container-page scroll-mt-24">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ __('app.home.products_heading') }}</h2>
                <p class="mt-1 text-sm text-stone-600">{{ __('app.home.products_intro') }}</p>
            </div>

            <form action="{{ route('home') }}#products" method="GET" role="search" class="relative w-full sm:max-w-xs" data-search-form data-search-target="#product-results">
                <label for="product-search" class="sr-only">{{ __('app.home.search_label') }}</label>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-stone-400" />
                <input id="product-search" type="search" name="q" value="{{ $search }}" placeholder="{{ __('app.home.search_placeholder') }}" autocomplete="off" maxlength="100" class="input rounded-full! pl-10">
                <span class="spinner absolute top-1/2 right-3.5 -mt-2 text-stone-400" data-search-spinner hidden></span>
            </form>
        </div>

        <div id="product-results" class="mt-8" aria-live="polite">
            @fragment('results')
                @if ($products->isEmpty())
                    <div class="card flex flex-col items-center px-6 py-16 text-center">
                        <span class="grid size-14 place-items-center rounded-2xl bg-stone-100 text-stone-400"><x-icon name="search" class="size-6" /></span>
                        @if ($search !== '')
                            <p class="mt-4 font-semibold">{{ __('app.home.no_results_title', ['term' => $search]) }}</p>
                            <p class="mt-1 text-sm text-stone-600">{!! __('app.home.no_results_text', ['link' => '<a href="'.e(route('home')).'#products" class="font-medium underline underline-offset-4">'.e(__('app.home.see_all')).'</a>']) !!}</p>
                        @else
                            <p class="mt-4 font-semibold">{{ __('app.home.coming_soon_title') }}</p>
                            <p class="mt-1 text-sm text-stone-600">{{ __('app.home.coming_soon_text') }}</p>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 lg:gap-6">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-10">
                            {{ $products->fragment('products')->links() }}
                        </div>
                    @endif
                @endif
            @endfragment
        </div>
    </section>
</x-layouts.store>
