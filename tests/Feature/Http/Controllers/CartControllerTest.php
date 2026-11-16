<?php

use App\Models\Product;
use App\Support\SiteSettings;

beforeEach(function () {
    app(SiteSettings::class)->update(['whatsapp_number' => '01712345678']);
});

describe('sync', function () {
    it('returns current prices and totals for the browser cart', function () {
        $hoodie = Product::factory()->create(['name' => 'Hoodie', 'price' => 850]);
        $sweater = Product::factory()->create(['name' => 'Sweater', 'price' => 1200]);

        $response = $this->postJson(route('cart.sync'), ['items' => [
            ['id' => $hoodie->id, 'quantity' => 2],
            ['id' => $sweater->id, 'quantity' => 1],
        ]]);

        $response->assertOk()
            ->assertJsonPath('items.0.name', 'Hoodie')
            ->assertJsonPath('items.0.subtotal', 1700)
            ->assertJsonPath('items.1.subtotal', 1200)
            ->assertJsonPath('total_items', 3)
            ->assertJsonPath('grand_total', 2900)
            ->assertJsonPath('grand_total_label', '৳2,900')
            ->assertJsonPath('unavailable', [])
            ->assertJsonPath('whatsapp_available', true);
    });

    it('reports inactive products as unavailable', function () {
        $inactive = Product::factory()->inactive()->create();

        $response = $this->postJson(route('cart.sync'), ['items' => [['id' => $inactive->id, 'quantity' => 1]]]);

        $response->assertOk()
            ->assertJsonPath('items', [])
            ->assertJsonPath('unavailable', [$inactive->id])
            ->assertJsonPath('grand_total', 0);
    });

    it('rejects invalid quantities', function (mixed $quantity) {
        $product = Product::factory()->create();

        $response = $this->postJson(route('cart.sync'), ['items' => [['id' => $product->id, 'quantity' => $quantity]]]);

        $response->assertUnprocessable()->assertJsonValidationErrors('items.0.quantity');
    })->with([0, -1, 100, 'abc']);
});

describe('order', function () {
    it('redirects to WhatsApp with the order message built from server prices', function () {
        $product = Product::factory()->create(['name' => 'Hoodie', 'price' => 850]);

        $response = $this->post(route('cart.order'), ['items' => [['id' => $product->id, 'quantity' => 2]]]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        expect($location)->toStartWith('https://wa.me/8801712345678?text=')
            ->and(rawurldecode($location))->toContain("1. Hoodie\nQuantity: 2\nUnit Price: BDT 850\nSubtotal: BDT 1,700")
            ->and(rawurldecode($location))->toContain('Grand Total: BDT 1,700');
    });

    it('leaves inactive products out of the order message', function () {
        $active = Product::factory()->create(['name' => 'Available Hoodie']);
        $inactive = Product::factory()->inactive()->create(['name' => 'Hidden Sweater']);

        $response = $this->post(route('cart.order'), ['items' => [
            ['id' => $active->id, 'quantity' => 1],
            ['id' => $inactive->id, 'quantity' => 1],
        ]]);

        expect(rawurldecode($response->headers->get('Location')))
            ->toContain('Available Hoodie')
            ->not->toContain('Hidden Sweater');
    });

    it('returns to the cart with an error when the cart is empty', function () {
        $response = $this->post(route('cart.order'), ['items' => []]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Your cart is empty or its products are no longer available.');
    });

    it('returns to the cart with an error when only inactive products remain', function () {
        $inactive = Product::factory()->inactive()->create();

        $response = $this->post(route('cart.order'), ['items' => [['id' => $inactive->id, 'quantity' => 1]]]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Your cart is empty or its products are no longer available.');
    });

    it('returns to the cart with an error when no WhatsApp number is configured', function () {
        app(SiteSettings::class)->update(['whatsapp_number' => null]);
        $product = Product::factory()->create();

        $response = $this->post(route('cart.order'), ['items' => [['id' => $product->id, 'quantity' => 1]]]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'WhatsApp ordering is not available right now. Please contact us directly.');
    });
});

it('renders the cart page', function () {
    $response = $this->get(route('cart.index'));

    $response->assertOk()->assertSee('Order via WhatsApp');
});
