<?php

use App\Models\Product;
use App\Models\User;

it('hides a product from the shop', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Hoodie']);

    $response = $this->actingAs($admin)->patchJson(route('admin.products.status', $product), ['is_active' => false]);

    $response->assertOk()->assertJson([
        'is_active' => false,
        'message' => '“Hoodie” is now hidden from your shop.',
    ]);
    expect($product->refresh()->is_active)->toBeFalse();
});

it('requires a status value', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin)->patchJson(route('admin.products.status', $product), []);

    $response->assertUnprocessable()->assertJsonValidationErrors('is_active');
    expect($product->refresh()->is_active)->toBeTrue();
});
