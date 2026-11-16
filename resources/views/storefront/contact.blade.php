@php
    $whatsappNumber = $site->whatsappNumber();
    $hasDetails = $whatsappNumber || $site->phone || $site->email || $site->address || $site->business_hours;
@endphp

<x-layouts.store :title="__('app.contact.title')" :description="__('app.contact.meta', ['name' => $site->business_name])">
    <div class="container-page max-w-4xl pt-10 sm:pt-16">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('app.contact.title') }}</h1>
        <p class="mt-3 max-w-2xl whitespace-pre-line text-stone-600">{{ $site->contact_intro }}</p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2">
            @if ($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="card group flex items-start gap-4 p-5 transition hover:shadow-lift sm:col-span-2">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-whatsapp/10 text-whatsapp-dark"><x-icon name="whatsapp" class="size-6" /></span>
                    <div>
                        <p class="font-semibold">{{ __('app.contact.whatsapp') }}</p>
                        <p class="mt-0.5 text-stone-600">+{{ $whatsappNumber }}</p>
                        <p class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-whatsapp-dark">{{ __('app.contact.start_chat') }} <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" /></p>
                    </div>
                </a>
            @endif

            @if ($site->phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site->phone) }}" class="card flex items-start gap-4 p-5 transition hover:shadow-lift">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-stone-100 text-stone-700"><x-icon name="phone" class="size-6" /></span>
                    <div>
                        <p class="font-semibold">{{ __('app.contact.phone') }}</p>
                        <p class="mt-0.5 text-stone-600">{{ $site->phone }}</p>
                    </div>
                </a>
            @endif

            @if ($site->email)
                <a href="mailto:{{ $site->email }}" class="card flex items-start gap-4 p-5 transition hover:shadow-lift">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-stone-100 text-stone-700"><x-icon name="mail" class="size-6" /></span>
                    <div class="min-w-0">
                        <p class="font-semibold">{{ __('app.contact.email') }}</p>
                        <p class="mt-0.5 break-all text-stone-600">{{ $site->email }}</p>
                    </div>
                </a>
            @endif

            @if ($site->address)
                <div class="card flex items-start gap-4 p-5">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-stone-100 text-stone-700"><x-icon name="map-pin" class="size-6" /></span>
                    <div>
                        <p class="font-semibold">{{ __('app.contact.address') }}</p>
                        <p class="mt-0.5 whitespace-pre-line text-stone-600">{{ $site->address }}</p>
                    </div>
                </div>
            @endif

            @if ($site->business_hours)
                <div class="card flex items-start gap-4 p-5">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-stone-100 text-stone-700"><x-icon name="clock" class="size-6" /></span>
                    <div>
                        <p class="font-semibold">{{ __('app.contact.hours') }}</p>
                        <p class="mt-0.5 text-stone-600">{{ $site->business_hours }}</p>
                    </div>
                </div>
            @endif
        </div>

        @unless ($hasDetails)
            <p class="card mt-10 p-6 text-stone-600">{{ __('app.contact.coming_soon') }}</p>
        @endunless
    </div>
</x-layouts.store>
