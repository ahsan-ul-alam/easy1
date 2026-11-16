<?php

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->unencryptedCookies = [];
    app()->setLocale('bn');
});

it('shows Bengali to a new visitor', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('<html lang="bn"', false)
        ->assertSee('আমাদের পণ্যসমূহ');
});

it('ignores the browser language for new visitors', function () {
    $response = $this->get(route('home'), ['Accept-Language' => 'en-US,en;q=0.9']);

    $response->assertOk()->assertSee('<html lang="bn"', false);
});

it('switches to English with the lang parameter and remembers the choice', function () {
    $response = $this->get(route('home', ['lang' => 'en']));

    $response->assertOk()
        ->assertSee('<html lang="en"', false)
        ->assertSee('Our products')
        ->assertPlainCookie('locale', 'en');
});

it('keeps the chosen language on later pages', function () {
    $response = $this->withUnencryptedCookie('locale', 'en')->get(route('pages.contact'));

    $response->assertOk()
        ->assertSee('<html lang="en"', false)
        ->assertSee('Contact us');
});

it('falls back to Bengali for an unsupported language parameter', function () {
    $response = $this->get(route('home', ['lang' => 'fr']));

    $response->assertOk()
        ->assertSee('<html lang="bn"', false)
        ->assertCookieMissing('locale');
});

it('ignores an unsupported language cookie', function () {
    $response = $this->withUnencryptedCookie('locale', 'xx')->get(route('home'));

    $response->assertOk()->assertSee('<html lang="bn"', false);
});

it('links the language switcher to the current page', function () {
    $product = Product::factory()->create(['name_en' => 'Canvas Tote']);

    $response = $this->get(route('products.show', $product));

    $response->assertOk()
        ->assertSee('href="'.url('/products/canvas-tote').'?lang=en"', false)
        ->assertSee('বাংলা')
        ->assertSee('English');
});

it('adds a canonical URL and hreflang alternates for each language', function () {
    $product = Product::factory()->create(['name_en' => 'Canvas Tote']);
    $plainUrl = url('/products/canvas-tote');

    $bengali = $this->get(route('products.show', $product));
    $english = $this->get(route('products.show', ['product' => $product, 'lang' => 'en']));

    $bengali->assertSee('<link rel="canonical" href="'.$plainUrl.'">', false)
        ->assertSee('<link rel="alternate" hreflang="bn" href="'.$plainUrl.'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.$plainUrl.'?lang=en">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.$plainUrl.'">', false);
    $english->assertSee('<link rel="canonical" href="'.$plainUrl.'?lang=en">', false);
});

it('shows the 404 page in the visitor’s language', function () {
    $bengali = $this->get('/missing-page');
    $english = $this->withUnencryptedCookie('locale', 'en')->get('/missing-page');

    $bengali->assertNotFound()->assertSee('এই পৃষ্ঠাটি পাওয়া যাচ্ছে না');
    $english->assertNotFound()->assertSee('This page isn’t available');
});

it('keeps the admin signed in when switching language', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.products.index', ['lang' => 'en']));

    $response->assertOk()->assertSee('Add, edit and show or hide the products in your shop.');
    $this->assertAuthenticatedAs($admin);
});

it('lists both languages in the sitemap', function () {
    $product = Product::factory()->create(['name_en' => 'Canvas Tote']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee('<loc>'.url('/products/canvas-tote').'</loc>', false)
        ->assertSee('<loc>'.url('/products/canvas-tote').'?lang=en</loc>', false)
        ->assertSee('hreflang="en"', false);
});
