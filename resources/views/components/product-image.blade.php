@props(['image' => null, 'alt', 'sizes' => '(min-width: 1024px) 25vw, (min-width: 640px) 33vw, 50vw', 'eager' => false])

@if ($image)
    <img src="{{ $image->thumbnail_url }}"
         @if ($image->path !== $image->thumbnail_path) srcset="{{ $image->thumbnail_url }} 600w, {{ $image->url }} 1600w" sizes="{{ $sizes }}" @endif
         alt="{{ $alt }}"
         loading="{{ $eager ? 'eager' : 'lazy' }}"
         decoding="async"
         {{ $attributes->merge(['class' => 'size-full object-cover']) }}>
@else
    <div {{ $attributes->merge(['class' => 'grid size-full place-items-center bg-stone-100 text-stone-300']) }} role="img" aria-label="{{ $alt }}">
        <x-icon name="photo" class="size-10" />
    </div>
@endif
