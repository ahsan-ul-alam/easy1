@use('App\Support\Localization')

@php
    $firstImage = $product->images->first();
    $whatsappAvailable = (bool) $site->whatsappNumber();
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $product->excerpt,
        'image' => $product->images->pluck('url')->all(),
        'url' => Localization::url(route('products.show', $product), app()->getLocale()),
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'BDT',
            'price' => $product->price,
            'availability' => 'https://schema.org/InStock',
        ],
    ];
@endphp

<x-layouts.store :title="$product->name" :description="$product->excerpt ?: null" :image="$firstImage?->url" :canonical="route('products.show', $product)">
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    </x-slot:head>

    <div class="container-page pt-6 sm:pt-10">
        <nav class="flex items-center gap-2 text-sm text-stone-500" aria-label="{{ __('products.breadcrumb') }}">
            <a href="{{ route('home') }}" class="shrink-0 hover:text-stone-900">{{ __('products.home') }}</a>
            <span aria-hidden="true">/</span>
            <span class="min-w-0 truncate text-stone-700" aria-current="page">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-14">
            <div data-gallery>
                <div class="aspect-square overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-stone-200/60">
                    <x-product-image :image="$firstImage" :alt="$product->name" sizes="(min-width: 1024px) 50vw, 100vw" :eager="true" fetchpriority="high" data-gallery-main />
                </div>

                @if ($product->images->count() > 1)
                    <div class="mt-3 grid grid-cols-3 gap-3 sm:gap-4">
                        @foreach ($product->images as $image)
                            <button type="button"
                                    @class(['aspect-square overflow-hidden rounded-2xl bg-white ring-2 transition', 'ring-stone-900' => $loop->first, 'ring-transparent hover:ring-stone-300' => ! $loop->first])
                                    data-gallery-thumb
                                    data-src="{{ $image->url }}"
                                    aria-label="{{ __('products.show_image', ['current' => Localization::digits($loop->iteration), 'total' => Localization::digits($product->images->count())]) }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                <img src="{{ $image->thumbnail_url }}" alt="" loading="lazy" decoding="async" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:py-4">
                <h1 class="text-3xl leading-tight font-bold tracking-tight text-balance break-words sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-4 text-3xl font-bold tracking-tight tabular-nums">{{ $product->price_label }}</p>

                <div class="mt-8 space-y-6">
                    <div>
                        <label for="quantity" class="label">{{ __('products.quantity') }}</label>
                        <div class="inline-flex items-center rounded-full border border-stone-300 bg-white p-1" data-quantity>
                            <button type="button" class="grid size-10 place-items-center rounded-full text-stone-700 transition hover:bg-stone-100" data-quantity-step="-1" aria-label="{{ __('products.decrease') }}"><x-icon name="minus" class="size-4" /></button>
                            <input id="quantity" type="number" inputmode="numeric" min="1" max="{{ \App\Support\Cart::MAX_QUANTITY }}" value="1" class="w-14 border-0 bg-transparent text-center text-base font-semibold tabular-nums focus:ring-0 focus:outline-none" data-quantity-input>
                            <button type="button" class="grid size-10 place-items-center rounded-full text-stone-700 transition hover:bg-stone-100" data-quantity-step="1" aria-label="{{ __('products.increase') }}"><x-icon name="plus" class="size-4" /></button>
                        </div>
                        <p class="mt-2 text-sm text-stone-500">{{ __('products.total') }} <span class="font-semibold text-stone-900 tabular-nums" data-line-total data-unit-price="{{ $product->price }}">{{ $product->price_label }}</span></p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="button" class="btn btn-primary py-3.5" data-add-to-cart data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-quantity-source="#quantity">
                            <x-icon name="bag" class="size-5" /> {{ __('products.add_to_cart') }}
                        </button>

                        @if ($whatsappAvailable)
                            <a href="{{ route('products.order', ['product' => $product, 'quantity' => 1]) }}" target="_blank" rel="noopener" class="btn btn-whatsapp py-3.5" data-order-link data-quantity-source="#quantity">
                                <x-icon name="whatsapp" class="size-5" /> {{ __('products.order_whatsapp') }}
                            </a>
                        @else
                            <a href="{{ route('pages.contact') }}" class="btn btn-outline py-3.5"><x-icon name="chat" class="size-5" /> {{ __('products.order_now') }}</a>
                        @endif
                    </div>

                    @if ($whatsappAvailable)
                        <p class="flex items-start gap-2 text-xs text-stone-500">
                            <x-icon name="shield" class="size-4" />
                            {{ __('products.whatsapp_note') }}
                        </p>
                    @endif
                </div>

                @if (filled($product->description))
                    <div class="mt-10 border-t border-stone-200 pt-8">
                        <h2 class="text-sm font-semibold tracking-wide text-stone-900 uppercase">{{ __('products.description') }}</h2>
                        <div class="mt-4 leading-relaxed whitespace-pre-line text-stone-700">{{ $product->description }}</div>
                    </div>
                @endif
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-20">
                <h2 class="text-2xl font-bold tracking-tight">{{ __('products.related') }}</h2>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4 lg:gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.store>
