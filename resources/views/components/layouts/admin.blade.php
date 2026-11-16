@props(['title'])

@php
    $navigation = [
        ['route' => 'admin.products.index', 'active' => 'admin.products.index', 'icon' => 'cube', 'label' => __('admin.nav.products')],
        ['route' => 'admin.products.create', 'active' => 'admin.products.create', 'icon' => 'plus', 'label' => __('admin.nav.add_product')],
        ['route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'icon' => 'cog', 'label' => __('admin.nav.settings')],
        ['route' => 'admin.password.edit', 'active' => 'admin.password.*', 'icon' => 'key', 'label' => __('admin.nav.password')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} — {{ $site->business_name }} {{ __('admin.title_suffix') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
        <x-js-translations />
    </head>
    <body class="min-h-full bg-stone-100/70">
        <div class="lg:flex">
            <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-stone-200 bg-white lg:flex" data-sidebar>
                <div class="flex h-16 items-center justify-between gap-3 border-b border-stone-100 px-5">
                    <a href="{{ route('admin.products.index') }}" class="flex min-w-0 items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-stone-900 text-sm font-bold text-white">{{ Str::upper(Str::substr($site->business_name, 0, 1)) }}</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold">{{ $site->business_name }}</span>
                            <span class="block text-xs text-stone-500">{{ __('admin.panel') }}</span>
                        </span>
                    </a>
                    <button type="button" class="grid size-9 place-items-center rounded-full text-stone-500 hover:bg-stone-100 lg:hidden" data-sidebar-close aria-label="{{ __('admin.close_menu') }}">
                        <x-icon name="x" />
                    </button>
                </div>

                <nav class="flex-1 space-y-1 p-3" aria-label="{{ __('admin.nav.label') }}">
                    @foreach ($navigation as $item)
                        <a href="{{ route($item['route']) }}"
                           @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-stone-900 text-white' => request()->routeIs($item['active']), 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! request()->routeIs($item['active'])])
                           @if (request()->routeIs($item['active'])) aria-current="page" @endif>
                            <x-icon :name="$item['icon']" class="size-5" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="space-y-1 border-t border-stone-100 p-3">
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-xs font-medium text-stone-500">{{ __('app.nav.language') }}</span>
                        <x-locale-switcher :full="true" />
                    </div>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900">
                        <x-icon name="external" class="size-5" /> {{ __('admin.view_shop') }}
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900">
                            <x-icon name="logout" class="size-5" /> {{ __('admin.logout') }}
                        </button>
                    </form>
                    <p class="truncate px-3 pt-2 text-xs text-stone-400">{{ __('admin.signed_in_as', ['email' => auth()->user()->email]) }}</p>
                </div>
            </aside>

            <div class="fixed inset-0 z-30 bg-stone-900/40 lg:hidden" data-sidebar-backdrop hidden></div>

            <div class="min-w-0 flex-1 lg:pl-72">
                <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-stone-200 bg-white/90 px-4 backdrop-blur lg:hidden">
                    <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full text-stone-700 hover:bg-stone-100" data-sidebar-open aria-controls="admin-sidebar" aria-label="{{ __('admin.open_menu') }}">
                        <x-icon name="menu" class="size-6" />
                    </button>
                    <span class="min-w-0 flex-1 truncate font-semibold">{{ $title }}</span>
                    <x-locale-switcher class="shrink-0" />
                </header>

                <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-toasts />
    </body>
</html>
