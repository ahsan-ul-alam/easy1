<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\SiteSettings;

describe('show', function () {
    it('renders an active product with its price', function () {
        $product = Product::factory()->create(['name' => 'Premium Zipper Hoodie', 'price' => 1850]);

        $response = $this->get('/products/premium-zipper-hoodie');

        $response->assertOk()
            ->assertSee('<title>Premium Zipper Hoodie — ', false)
            ->assertSee('৳1,850');
    });

    it('shows images stored as external links', function () {
        $product = Product::factory()->create();
        ProductImage::factory()->for($product)->create([
            'path' => 'https://images.example.com/hoodie.jpg?w=1200',
            'thumbnail_path' => 'https://images.example.com/hoodie.jpg?w=600',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertSee('src="https://images.example.com/hoodie.jpg?w=600"', false)
            ->assertSee('https://images.example.com/hoodie.jpg?w=1200 1600w', false);
    });

    it('returns 404 for an inactive product', function () {
        $product = Product::factory()->inactive()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertNotFound();
    });

    it('escapes product content', function () {
        $product = Product::factory()->create(['description' => '<script>alert(1)</script>']);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    });

    it('shows an order now link to the contact page when no WhatsApp number is configured', function () {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertSee('Order now')
            ->assertSee(route('pages.contact'))
            ->assertDontSee(route('products.order', $product));
    });
});

describe('order', function () {
    beforeEach(function () {
        app(SiteSettings::class)->update(['whatsapp_number' => '+880 1712-345678']);
    });

    it('redirects to WhatsApp with the selected quantity and total', function () {
        $product = Product::factory()->create(['name' => 'Hoodie', 'price' => 850]);

        $response = $this->get(route('products.order', ['product' => $product, 'quantity' => 3]));

        $location = $response->assertRedirect()->headers->get('Location');
        expect($location)->toStartWith('https://wa.me/8801712345678?text=')
            ->and(rawurldecode($location))->toContain("Product: Hoodie\nQuantity: 3\nUnit Price: BDT 850\nTotal: BDT 2,550");
    });

    it('returns 404 when ordering an inactive product', function () {
        $product = Product::factory()->inactive()->create();

        $response = $this->get(route('products.order', $product));

        $response->assertNotFound();
    });

    it('rejects a quantity above the maximum', function () {
        $product = Product::factory()->create();

        $response = $this->get(route('products.order', ['product' => $product, 'quantity' => 500]));

        $response->assertSessionHasErrors('quantity');
    });

    it('returns to the product page when no WhatsApp number is configured', function () {
        app(SiteSettings::class)->update(['whatsapp_number' => null]);
        $product = Product::factory()->create();

        $response = $this->get(route('products.order', $product));

        $response->assertRedirect(route('products.show', $product))
            ->assertSessionHas('error');
    });
});
