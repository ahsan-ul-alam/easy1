<?php

use App\Models\Product;

it('lists only active products', function () {
    Product::factory()->create(['name' => 'Visible Hoodie']);
    Product::factory()->inactive()->create(['name' => 'Hidden Sweater']);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Visible Hoodie')
        ->assertDontSee('Hidden Sweater');
});

it('filters products by search term', function () {
    Product::factory()->create(['name' => 'Black Hoodie']);
    Product::factory()->create(['name' => 'Wool Sweater']);

    $response = $this->get(route('home', ['q' => 'hoodie']));

    $response->assertOk()
        ->assertSee('Black Hoodie')
        ->assertDontSee('Wool Sweater');
});

it('treats search wildcards as literal text', function () {
    Product::factory()->create(['name' => '100% Cotton Tee']);
    Product::factory()->create(['name' => 'Black Hoodie']);

    $response = $this->get(route('home', ['q' => '100%']));

    $response->assertOk()
        ->assertSee('100% Cotton Tee')
        ->assertDontSee('Black Hoodie');
});

it('ignores a malformed array search term', function () {
    Product::factory()->create(['name' => 'Black Hoodie']);

    $response = $this->get('/?q[]=hoodie');

    $response->assertOk()->assertSee('Black Hoodie');
});

it('returns only the results fragment for AJAX searches', function () {
    Product::factory()->create(['name' => 'Black Hoodie']);

    $response = $this->get(route('home', ['q' => 'hoodie']), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()
        ->assertSee('Black Hoodie')
        ->assertDontSee('<html', false);
});
