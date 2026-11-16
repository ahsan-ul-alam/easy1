@use('App\Support\Localization')

@php
    $whatsappNumber = $site->whatsappNumber();
    $socialLinks = array_filter([
        'facebook' => $site->facebook_url,
        'instagram' => $site->instagram_url,
        'whatsapp' => $whatsappNumber ? 'https://wa.me/'.$whatsappNumber : null,
    ]);
@endphp

<footer class="relative mt-24 overflow-hidden bg-stone-950 text-stone-400">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-whatsapp/50 to-transparent"></div>
    <div class="pointer-events-none absolute -top-40 right-0 size-[28rem] rounded-full bg-whatsapp/10 blur-3xl"></div>

    <div class="container-page relative pt-14 sm:pt-16">
        {{-- Order call-to-action --}}
        <div class="flex flex-col gap-6 rounded-3xl bg-white/[0.04] p-6 ring-1 ring-white/10 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-xl">
                <p class="text-xs font-semibold tracking-[0.2em] text-whatsapp uppercase">{{ __('app.footer.cta_eyebrow') }}</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-balance text-white sm:text-3xl">{{ __('app.footer.cta_title') }}</h2>
                <p class="mt-2 text-sm text-stone-400">{{ __('app.footer.cta_text') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($whatsappNumber)
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icon name="whatsapp" class="size-5" /> {{ __('app.whatsapp.chat') }}</a>
                @else
                    <a href="{{ route('pages.contact') }}" class="btn bg-white text-stone-900 hover:bg-stone-200">{{ __('app.footer.order_now') }}</a>
                @endif
                <a href="{{ route('cart.index') }}" class="btn border border-white/15 text-white hover:border-white/40 hover:bg-white/5"><x-icon name="bag" class="size-5" /> {{ __('app.nav.view_cart') }}</a>
            </div>
        </div>

        <div class="grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12">
            {{-- Brand --}}
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    @if ($site->logoUrl())
                        <span class="rounded-xl bg-white px-3 py-2"><img src="{{ $site->logoUrl() }}" alt="{{ $site->business_name }}" loading="lazy" class="h-8 w-auto max-w-36 object-contain"></span>
                    @else
                        <span class="grid size-10 place-items-center rounded-xl bg-white text-base font-bold text-stone-950">{{ Str::upper(Str::substr($site->business_name, 0, 1)) }}</span>
                        <span class="text-lg font-bold tracking-tight text-white">{{ $site->business_name }}</span>
                    @endif
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed">{{ $site->tagline ?: __('app.footer.default_tagline') }}</p>

                @if ($socialLinks)
                    <div class="mt-6 flex items-center gap-2">
                        @foreach ($socialLinks as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $network === 'whatsapp' ? 'WhatsApp' : ucfirst($network) }}"
                               class="grid size-10 place-items-center rounded-full bg-white/5 text-stone-400 ring-1 ring-white/10 transition hover:-translate-y-0.5 hover:bg-white hover:text-stone-950">
                                <x-icon :name="$network" class="size-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- How ordering works --}}
            <div class="lg:col-span-3">
                <p class="text-sm font-semibold text-white">{{ __('app.footer.how_it_works') }}</p>
                <ol class="mt-4 space-y-4 text-sm">
                    @foreach (__('app.footer.steps') as $step)
                        <li class="flex items-center gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-white/5 text-xs font-semibold text-white ring-1 ring-white/10 tabular-nums">{{ Localization::digits($loop->iteration) }}</span>
                            {{ $step }}
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Links --}}
            <div class="lg:col-span-2">
                <p class="text-sm font-semibold text-white">{{ __('app.footer.explore') }}</p>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#products" class="transition hover:text-white">{{ __('app.footer.all_products') }}</a></li>
                    <li><a href="{{ route('cart.index') }}" class="transition hover:text-white">{{ __('app.footer.your_cart') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="transition hover:text-white">{{ __('app.footer.contact_us') }}</a></li>
                    <li><a href="{{ route('pages.privacy') }}" class="transition hover:text-white">{{ __('app.footer.privacy') }}</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="lg:col-span-3">
                <p class="text-sm font-semibold text-white">{{ __('app.footer.get_in_touch') }}</p>
                <ul class="mt-4 space-y-3 text-sm">
                    @if ($site->phone)
                        <li class="flex gap-2.5"><x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-stone-400" /><a href="tel:{{ preg_replace('/[^0-9+]/', '', $site->phone) }}" class="transition hover:text-white">{{ $site->phone }}</a></li>
                    @endif
                    @if ($site->email)
                        <li class="flex gap-2.5"><x-icon name="mail" class="mt-0.5 size-4 shrink-0 text-stone-400" /><a href="mailto:{{ $site->email }}" class="break-all transition hover:text-white">{{ $site->email }}</a></li>
                    @endif
                    @if ($site->address)
                        <li class="flex gap-2.5"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-stone-400" /><span class="whitespace-pre-line">{{ $site->address }}</span></li>
                    @endif
                    @if ($site->business_hours)
                        <li class="flex gap-2.5"><x-icon name="clock" class="mt-0.5 size-4 shrink-0 text-stone-400" /><span>{{ $site->business_hours }}</span></li>
                    @endif
                    @unless ($site->phone || $site->email || $site->address || $site->business_hours)
                        <li><a href="{{ route('pages.contact') }}" class="transition hover:text-white">{{ __('app.footer.contact_details') }} &rarr;</a></li>
                    @endunless
                </ul>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-white/10 py-6 text-xs text-stone-400 sm:flex-row sm:items-center sm:justify-between">
            <p>{{ __('app.footer.rights', ['year' => Localization::digits(date('Y')), 'name' => $site->business_name]) }}</p>
            <a href="#main" class="inline-flex items-center gap-1.5 transition hover:text-white">{{ __('app.footer.back_to_top') }} <span aria-hidden="true">&uarr;</span></a>
        </div>
    </div>

    {{-- Oversized wordmark --}}
    <p class="pointer-events-none container-page relative -mb-[0.18em] text-[clamp(3rem,13vw,11rem)] leading-none font-bold tracking-tighter break-words text-white/[0.04] select-none" aria-hidden="true">{{ $site->business_name }}</p>
</footer>
