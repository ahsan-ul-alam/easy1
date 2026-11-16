@use('App\Support\Localization')

@props(['code'])

<x-layouts.store :title="__('app.errors.'.$code.'.title')">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <div class="container-page flex flex-col items-center py-24 text-center">
        <p class="text-sm font-semibold text-stone-500">{{ Localization::digits($code) }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('app.errors.'.$code.'.title') }}</h1>
        <p class="mt-3 max-w-md text-stone-600">{{ __('app.errors.'.$code.'.text') }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-8">{{ __('app.errors.back_home') }}</a>
    </div>
</x-layouts.store>
