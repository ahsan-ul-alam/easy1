<?php

use App\Models\Product;
use App\Support\Cart;

it('calculates subtotals, total items and grand total from database prices', function () {
    $hoodie = Product::factory()->create(['price' => 850]);
    $sweater = Product::factory()->create(['price' => 1200]);

    $cart = Cart::fromItems([
        ['id' => $hoodie->id, 'quantity' => 2],
        ['id' => $sweater->id, 'quantity' => 3],
    ]);

    expect($cart->lines->pluck('subtotal')->all())->toBe([1700, 3600])
        ->and($cart->totalItems())->toBe(5)
        ->and($cart->grandTotal())->toBe(5300);
});

it('merges duplicate products into one line', function () {
    $product = Product::factory()->create(['price' => 100]);

    $cart = Cart::fromItems([
        ['id' => $product->id, 'quantity' => 2],
        ['id' => $product->id, 'quantity' => 3],
    ]);

    expect($cart->lines)->toHaveCount(1)
        ->and($cart->totalItems())->toBe(5);
});

it('caps the quantity of a merged line at the maximum', function () {
    $product = Product::factory()->create(['price' => 100]);

    $cart = Cart::fromItems([
        ['id' => $product->id, 'quantity' => 60],
        ['id' => $product->id, 'quantity' => 60],
    ]);

    expect($cart->totalItems())->toBe(Cart::MAX_QUANTITY);
});

it('drops inactive and missing products and reports them as unavailable', function () {
    $active = Product::factory()->create(['price' => 500]);
    $inactive = Product::factory()->inactive()->create();

    $cart = Cart::fromItems([
        ['id' => $active->id, 'quantity' => 1],
        ['id' => $inactive->id, 'quantity' => 1],
        ['id' => 9999, 'quantity' => 1],
    ]);

    expect($cart->lines->pluck('product.id')->all())->toBe([$active->id])
        ->and($cart->unavailableIds)->toBe([$inactive->id, 9999])
        ->and($cart->grandTotal())->toBe(500);
});
