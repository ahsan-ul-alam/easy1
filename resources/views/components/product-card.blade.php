@props(['product'])

<article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-stone-200/60 transition duration-300 hover:-translate-y-0.5 hover:shadow-lift">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/5] overflow-hidden bg-stone-100">
        <x-product-image :image="$product->images->first()" :alt="$product->name" class="transition duration-500 group-hover:scale-[1.04]" />
    </a>

    <div class="flex flex-1 flex-col gap-3 p-3 sm:p-4">
        <h3 class="line-clamp-2 text-sm font-medium break-words text-stone-800 sm:text-base">
            <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 after:content-['']">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex items-center justify-between gap-2">
            <p class="text-base font-bold tracking-tight tabular-nums sm:text-lg">{{ $product->price_label }}</p>

            <button type="button"
                    class="relative z-10 grid size-10 shrink-0 place-items-center rounded-full bg-stone-900 text-white transition hover:bg-stone-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-900"
                    data-add-to-cart
                    data-product-id="{{ $product->id }}"
                    data-product-name="{{ $product->name }}"
                    aria-label="{{ __('products.add_to_cart_aria', ['name' => $product->name]) }}">
                <x-icon name="plus" class="size-5" />
            </button>
        </div>
    </div>
</article>
