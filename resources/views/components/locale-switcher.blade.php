@use('App\Support\Localization')

@props(['full' => false])

{{-- Desktop: "বাংলা | English", mobile: "BN / EN". Links keep the current page and add ?lang=. --}}
<nav aria-label="{{ __('app.nav.language') }}" {{ $attributes->merge(['class' => 'flex items-center text-sm']) }}>
    @foreach (Localization::LOCALES as $code => $language)
        @if (! $loop->first)
            <span class="px-1 text-stone-300" aria-hidden="true">@if ($full)|@else<span class="sm:hidden">/</span><span class="hidden sm:inline">|</span>@endif</span>
        @endif

        <a href="{{ Localization::switchUrl($code) }}"
           hreflang="{{ $code }}"
           lang="{{ $code }}"
           rel="nofollow"
           data-locale-switch="{{ $code }}"
           @if (app()->getLocale() === $code) aria-current="true" @endif
           @class([
               'rounded-full px-2 py-1 transition',
               'font-semibold text-stone-900 underline decoration-2 underline-offset-4 decoration-whatsapp' => app()->getLocale() === $code,
               'text-stone-500 hover:text-stone-900' => app()->getLocale() !== $code,
           ])>
            @if ($full)
                {{ $language['name'] }}
            @else
                <span class="sm:hidden">{{ $language['short'] }}</span>
                <span class="hidden sm:inline">{{ $language['name'] }}</span>
            @endif
        </a>
    @endforeach
</nav>
