@php
    $whatsappAvailable = (bool) $site->whatsappNumber();
@endphp

<x-layouts.store :title="__('cart.title')">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <div class="container-page pt-8 sm:pt-12" data-cart-page>
        <div class="flex items-end justify-between gap-4">
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('cart.title') }}</h1>
            <a href="{{ route('home') }}#products" class="hidden items-center gap-2 text-sm font-medium text-stone-600 hover:text-stone-900 sm:inline-flex"><x-icon name="arrow-left" class="size-4" /> {{ __('cart.continue') }}</a>
        </div>

        <noscript>
            <p class="card mt-8 p-6 text-sm">{{ __('cart.js_required') }}</p>
        </noscript>

        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" data-cart-notice hidden></div>

        <div class="mt-8 space-y-3" data-cart-loading>
            @for ($i = 0; $i < 2; $i++)
                <div class="card flex animate-pulse gap-4 p-4">
                    <div class="size-20 rounded-xl bg-stone-100 sm:size-24"></div>
                    <div class="flex-1 space-y-3 py-1">
                        <div class="h-4 w-2/3 rounded bg-stone-100"></div>
                        <div class="h-4 w-1/4 rounded bg-stone-100"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="card mt-8 flex flex-col items-center px-6 py-16 text-center" data-cart-empty hidden>
            <span class="grid size-16 place-items-center rounded-2xl bg-stone-100 text-stone-400"><x-icon name="bag" class="size-7" /></span>
            <p class="mt-5 text-lg font-semibold">{{ __('cart.empty_title') }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ __('cart.empty_text') }}</p>
            <a href="{{ route('home') }}#products" class="btn btn-primary mt-6">{{ __('cart.start_shopping') }}</a>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-12" data-cart-content hidden>
            <ul class="space-y-3 lg:col-span-8" data-cart-items></ul>

            <aside class="lg:col-span-4">
                <div class="card sticky top-24 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold">{{ __('cart.summary') }}</h2>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-stone-600">{{ __('cart.total_items') }}</dt>
                            <dd class="font-medium tabular-nums" data-cart-total-items>0</dd>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-stone-200 pt-3">
                            <dt class="font-semibold">{{ __('cart.grand_total') }}</dt>
                            <dd class="text-2xl font-bold tracking-tight tabular-nums" data-cart-grand-total>৳0</dd>
                        </div>
                    </dl>

                    @if ($whatsappAvailable)
                        <form action="{{ route('cart.order') }}" method="POST" target="_blank" class="mt-6" data-cart-order-form>
                            @csrf
                            <div data-cart-order-inputs></div>
                            <button type="submit" class="btn btn-whatsapp w-full py-3.5 text-base">
                                <x-icon name="whatsapp" class="size-5" /> {{ __('cart.order_whatsapp') }}
                            </button>
                        </form>
                        <p class="mt-3 flex items-start gap-2 text-xs text-stone-500">
                            <x-icon name="shield" class="size-4" />
                            {{ __('cart.order_note') }}
                        </p>
                    @else
                        <a href="{{ route('pages.contact') }}" class="btn btn-primary mt-6 w-full py-3.5">{{ __('cart.order_now') }}</a>
                    @endif

                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-stone-200 pt-4 text-sm">
                        <a href="{{ route('home') }}#products" class="inline-flex items-center gap-1.5 font-medium text-stone-600 hover:text-stone-900"><x-icon name="arrow-left" class="size-4" /> {{ __('cart.continue') }}</a>
                        <button type="button" class="font-medium text-stone-500 hover:text-red-600" data-cart-clear>{{ __('cart.clear') }}</button>
                    </div>
                </div>
            </aside>
        </div>

        <template data-cart-item-template>
            <li class="card flex gap-4 p-3 transition sm:p-4" data-cart-item>
                <a data-item-link class="block size-20 shrink-0 overflow-hidden rounded-xl bg-stone-100 sm:size-24">
                    <img data-item-image alt="" loading="lazy" class="size-full object-cover">
                </a>
                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a data-item-link data-item-name class="line-clamp-2 font-medium break-words hover:underline"></a>
                            <p class="mt-0.5 text-sm text-stone-500 tabular-nums" data-item-price></p>
                        </div>
                        <button type="button" class="-m-1.5 grid size-9 shrink-0 place-items-center rounded-full text-stone-400 transition hover:bg-red-50 hover:text-red-600" data-item-remove aria-label="{{ __('cart.remove') }}">
                            <x-icon name="trash" class="size-5" />
                        </button>
                    </div>
                    <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                        <div class="inline-flex items-center rounded-full border border-stone-300 bg-white p-0.5" data-quantity>
                            <button type="button" class="grid size-8 place-items-center rounded-full text-stone-700 hover:bg-stone-100" data-quantity-step="-1" aria-label="{{ __('cart.decrease') }}"><x-icon name="minus" class="size-4" /></button>
                            <input type="number" inputmode="numeric" min="1" max="{{ \App\Support\Cart::MAX_QUANTITY }}" class="w-10 border-0 bg-transparent text-center text-sm font-semibold tabular-nums focus:ring-0 focus:outline-none" data-quantity-input aria-label="{{ __('cart.quantity') }}">
                            <button type="button" class="grid size-8 place-items-center rounded-full text-stone-700 hover:bg-stone-100" data-quantity-step="1" aria-label="{{ __('cart.increase') }}"><x-icon name="plus" class="size-4" /></button>
                        </div>
                        <p class="font-semibold tabular-nums" data-item-subtotal></p>
                    </div>
                </div>
            </li>
        </template>
    </div>
</x-layouts.store>
