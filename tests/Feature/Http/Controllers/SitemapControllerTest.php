<?php

use App\Models\Product;

it('lists active products in the sitemap', function () {
    $active = Product::factory()->create();
    $inactive = Product::factory()->inactive()->create();

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('products.show', $active), false)
        ->assertDontSee(route('products.show', $inactive), false);
});
