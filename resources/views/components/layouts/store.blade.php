@use('App\Support\Localization')

@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
])

@php
    $pageTitle = $title ? $title.' — '.$site->business_name : $site->business_name.($site->tagline ? ' — '.$site->tagline : '');
    $metaDescription = $description ?: ($site->meta_description ?: $site->hero_subtitle);
    $whatsappNumber = $site->whatsappNumber();
    $pageUrl = $canonical ?? url()->current();
    $canonicalUrl = Localization::url($pageUrl, app()->getLocale());
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ Str::limit($metaDescription, 160) }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        @foreach (array_keys(Localization::LOCALES) as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ Localization::url($pageUrl, $locale) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ Localization::url($pageUrl, Localization::DEFAULT) }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $site->business_name }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ Str::limit($metaDescription, 200) }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
        @if ($image ?? $site->logoUrl())
            <meta property="og:image" content="{{ $image ?? $site->logoUrl() }}">
        @endif
        <meta name="theme-color" content="#fafaf9">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-js-translations />

        {{ $head ?? '' }}
    </head>
    <body class="flex min-h-full flex-col" data-cart-sync-url="{{ route('cart.sync') }}" data-cart-url="{{ route('cart.index') }}">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:shadow-lift">{{ __('app.skip_to_content') }}</a>

        <header class="sticky top-0 z-40 border-b border-stone-200/70 bg-stone-50/85 backdrop-blur-md">
            <div class="container-page flex h-16 items-center justify-between gap-3 sm:h-18 sm:gap-4">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    @if ($site->logoUrl())
                        <img src="{{ $site->logoUrl() }}" alt="{{ $site->business_name }}" class="h-9 w-auto max-w-32 object-contain sm:h-10 sm:max-w-40">
                    @else
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-stone-900 text-sm font-bold text-white">{{ Str::upper(Str::substr($site->business_name, 0, 1)) }}</span>
                        <span class="truncate text-lg font-bold tracking-tight">{{ $site->business_name }}</span>
                    @endif
                </a>

                <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                    <nav class="hidden items-center gap-1 sm:flex" aria-label="{{ __('app.nav.main') }}">
                        <a href="{{ route('home') }}#products" @class(['rounded-full px-4 py-2 text-sm font-medium transition hover:bg-stone-200/60', 'text-stone-900' => request()->routeIs('home', 'products.*'), 'text-stone-600' => ! request()->routeIs('home', 'products.*')])>{{ __('app.nav.shop') }}</a>
                        <a href="{{ route('pages.contact') }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition hover:bg-stone-200/60', 'text-stone-900' => request()->routeIs('pages.contact'), 'text-stone-600' => ! request()->routeIs('pages.contact')])>{{ __('app.nav.contact') }}</a>
                    </nav>

                    <x-locale-switcher class="mr-1" />

                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 rounded-full bg-stone-900 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-700 sm:px-4" aria-label="{{ __('app.nav.view_cart') }}">
                        <x-icon name="bag" class="size-5" />
                        <span class="hidden sm:inline">{{ __('app.nav.cart') }}</span>
                        <span data-cart-count class="grid min-w-5 place-items-center rounded-full bg-white px-1.5 text-xs font-bold text-stone-900 tabular-nums" hidden>0</span>
                    </a>

                    <button type="button" class="inline-flex size-10 items-center justify-center rounded-full text-stone-700 transition hover:bg-stone-200/60 sm:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="{{ __('app.nav.open_menu') }}">
                        <x-icon name="menu" class="size-6" />
                    </button>
                </div>
            </div>

            <nav id="mobile-menu" class="border-t border-stone-200/70 sm:hidden" hidden aria-label="{{ __('app.nav.mobile') }}">
                <div class="container-page flex flex-col py-2">
                    <a href="{{ route('home') }}#products" class="rounded-xl px-3 py-3 font-medium hover:bg-stone-100">{{ __('app.nav.shop') }}</a>
                    <a href="{{ route('cart.index') }}" class="rounded-xl px-3 py-3 font-medium hover:bg-stone-100">{{ __('app.nav.cart') }}</a>
                    <a href="{{ route('pages.contact') }}" class="rounded-xl px-3 py-3 font-medium hover:bg-stone-100">{{ __('app.nav.contact') }}</a>
                    <div class="mt-1 flex items-center justify-between border-t border-stone-200/70 px-3 pt-3 pb-1">
                        <span class="text-sm text-stone-500">{{ __('app.nav.language') }}</span>
                        <x-locale-switcher :full="true" />
                    </div>
                </div>
            </nav>
        </header>

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        <x-site-footer />

        @if ($whatsappNumber && ! request()->routeIs('cart.index'))
            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode(__('app.whatsapp.greeting')) }}"
               target="_blank" rel="noopener"
               class="fixed right-4 bottom-4 z-40 grid size-14 place-items-center rounded-full bg-whatsapp text-white shadow-lift transition hover:scale-105 hover:bg-whatsapp-dark sm:right-6 sm:bottom-6"
               aria-label="{{ __('app.whatsapp.chat_aria') }}">
                <x-icon name="whatsapp" class="size-7" />
            </a>
        @endif

        <x-toasts />
    </body>
</html>
