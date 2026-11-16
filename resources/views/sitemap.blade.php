@use('App\Support\Localization')
@php
    $pages = [
        ['url' => route('home'), 'priority' => '1.0', 'lastmod' => null],
        ...$products->map(fn ($product) => ['url' => route('products.show', $product), 'priority' => '0.8', 'lastmod' => $product->updated_at->toAtomString()])->all(),
        ['url' => route('pages.contact'), 'priority' => '0.5', 'lastmod' => null],
        ['url' => route('pages.privacy'), 'priority' => '0.3', 'lastmod' => null],
    ];
@endphp
{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($pages as $page)
@foreach (array_keys(Localization::LOCALES) as $locale)
    <url>
        <loc>{{ Localization::url($page['url'], $locale) }}</loc>
@if ($page['lastmod'])
        <lastmod>{{ $page['lastmod'] }}</lastmod>
@endif
        <priority>{{ $page['priority'] }}</priority>
@foreach (array_keys(Localization::LOCALES) as $alternate)
        <xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ Localization::url($page['url'], $alternate) }}" />
@endforeach
    </url>
@endforeach
@endforeach
</urlset>
