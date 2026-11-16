<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * A guest cart rebuilt from client-supplied product IDs and quantities,
 * using current product data from the database for names and prices.
 */
class Cart
{
    public const MAX_QUANTITY = 99;

    public const MAX_LINES = 50;

    /**
     * @param  Collection<int, array{product: Product, quantity: int, subtotal: int}>  $lines
     * @param  array<int, int>  $unavailableIds
     */
    public function __construct(
        public readonly Collection $lines,
        public readonly array $unavailableIds = [],
    ) {}

    /**
     * Build a cart from raw items, dropping products that are missing or inactive.
     *
     * @param  array<int, array{id: int|string, quantity: int|string}>  $items
     */
    public static function fromItems(array $items): self
    {
        $quantities = [];

        foreach ($items as $item) {
            $id = (int) $item['id'];
            $quantities[$id] = ($quantities[$id] ?? 0) + (int) $item['quantity'];
        }

        $quantities = array_slice($quantities, 0, self::MAX_LINES, true);

        $products = Product::query()
            ->active()
            ->with('images')
            ->whereKey(array_keys($quantities))
            ->get()
            ->keyBy('id');

        $lines = collect($quantities)
            ->filter(fn (int $quantity, int $id): bool => $products->has($id))
            ->map(function (int $quantity, int $id) use ($products): array {
                $product = $products->get($id);
                $quantity = max(1, min(self::MAX_QUANTITY, $quantity));

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->price * $quantity,
                ];
            })
            ->values();

        $unavailableIds = array_values(array_diff(array_keys($quantities), $products->keys()->all()));

        return new self($lines, $unavailableIds);
    }

    public function isEmpty(): bool
    {
        return $this->lines->isEmpty();
    }

    public function totalItems(): int
    {
        return $this->lines->sum('quantity');
    }

    public function grandTotal(): int
    {
        return $this->lines->sum('subtotal');
    }

    /**
     * The cart as JSON-friendly data for the storefront.
     *
     * @return array{items: array<int, array<string, mixed>>, unavailable: array<int, int>, total_items: int, total_items_label: string, grand_total: int, grand_total_label: string}
     */
    public function toArray(): array
    {
        return [
            'items' => $this->lines->map(fn (array $line): array => [
                'id' => $line['product']->id,
                'name' => $line['product']->name,
                'url' => route('products.show', $line['product']),
                'image' => $line['product']->images->first()?->thumbnail_url,
                'price' => $line['product']->price,
                'price_label' => $line['product']->price_label,
                'quantity' => $line['quantity'],
                'subtotal' => $line['subtotal'],
                'subtotal_label' => Localization::money($line['subtotal']),
            ])->all(),
            'unavailable' => $this->unavailableIds,
            'total_items' => $this->totalItems(),
            'total_items_label' => Localization::number($this->totalItems()),
            'grand_total' => $this->grandTotal(),
            'grand_total_label' => Localization::money($this->grandTotal()),
        ];
    }
}
