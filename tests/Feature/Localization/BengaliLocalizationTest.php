<?php

use App\Models\Product;
use App\Models\User;
use App\Support\SiteSettings;

beforeEach(function () {
    $this->unencryptedCookies = [];
    app()->setLocale('bn');
});

describe('product content', function () {
    it('shows the Bengali name and description on Bengali pages', function () {
        $product = Product::factory()->create([
            'name_bn' => 'হাই নেক সোয়েটার', 'name_en' => 'High Neck Sweater',
            'description_bn' => 'নরম উলের সোয়েটার।', 'description_en' => 'A soft wool sweater.',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertSee('হাই নেক সোয়েটার')
            ->assertSee('নরম উলের সোয়েটার।')
            ->assertDontSee('A soft wool sweater.');
    });

    it('shows the English name and description on English pages', function () {
        $product = Product::factory()->create([
            'name_bn' => 'হাই নেক সোয়েটার', 'name_en' => 'High Neck Sweater',
            'description_bn' => 'নরম উলের সোয়েটার।', 'description_en' => 'A soft wool sweater.',
        ]);

        $response = $this->get(route('products.show', ['product' => $product, 'lang' => 'en']));

        $response->assertOk()
            ->assertSee('High Neck Sweater')
            ->assertSee('A soft wool sweater.')
            ->assertDontSee('নরম উলের সোয়েটার।');
    });

    it('falls back to the other language when a translation is missing', function () {
        $product = Product::factory()->create(['name_bn' => null, 'name_en' => 'Canvas Tote', 'description_en' => 'Strong canvas.']);

        $response = $this->get('/products/canvas-tote');

        $response->assertOk()
            ->assertSee('Canvas Tote')
            ->assertSee('Strong canvas.');
    });

    it('formats prices with Bengali digits', function () {
        Product::factory()->create(['name_bn' => 'হুডি', 'price' => 1700]);

        $response = $this->get(route('home'));

        $response->assertOk()->assertSee('৳১,৭০০');
    });

    it('finds products by their Bengali name', function () {
        Product::factory()->create(['name_bn' => 'জিপার হুডি', 'name_en' => 'Zipper Hoodie']);
        Product::factory()->create(['name_bn' => 'সোয়েটার', 'name_en' => 'Sweater']);

        $response = $this->get(route('home', ['q' => 'হুডি']));

        $response->assertOk()
            ->assertSee('জিপার হুডি')
            ->assertDontSee('সোয়েটার');
    });
});

describe('cart and WhatsApp', function () {
    it('returns cart totals formatted in Bengali', function () {
        $product = Product::factory()->create(['name_bn' => 'হুডি', 'price' => 850]);

        $response = $this->postJson(route('cart.sync'), ['items' => [['id' => $product->id, 'quantity' => 2]]]);

        $response->assertOk()
            ->assertJsonPath('items.0.name', 'হুডি')
            ->assertJsonPath('grand_total', 1700)
            ->assertJsonPath('grand_total_label', '৳১,৭০০')
            ->assertJsonPath('total_items_label', '২');
    });

    it('sends the cart order message in Bengali', function () {
        app(SiteSettings::class)->update(['whatsapp_number' => '01712345678']);
        $product = Product::factory()->create(['name_bn' => 'হুডি', 'price' => 850]);

        $response = $this->post(route('cart.order'), ['items' => [['id' => $product->id, 'quantity' => 2]]]);

        expect(rawurldecode($response->headers->get('Location')))
            ->toContain("১. হুডি\nপরিমাণ: ২টি\nএকক মূল্য: ৳৮৫০\nমোট: ৳১,৭০০")
            ->toContain('সর্বমোট মূল্য: ৳১,৭০০');
    });

    it('sends the single product order message in English when English is selected', function () {
        app(SiteSettings::class)->update(['whatsapp_number' => '01712345678']);
        $product = Product::factory()->create(['name_bn' => 'হুডি', 'name_en' => 'Hoodie', 'price' => 850]);

        $response = $this->withUnencryptedCookie('locale', 'en')->get(route('products.order', ['product' => $product, 'quantity' => 2]));

        expect(rawurldecode($response->headers->get('Location')))->toContain("Product: Hoodie\nQuantity: 2\nUnit Price: BDT 850\nTotal: BDT 1,700");
    });

    it('shows cart errors in Bengali', function () {
        $response = $this->post(route('cart.order'), ['items' => []]);

        $response->assertSessionHas('error', 'আপনার কার্ট খালি অথবা এর পণ্যগুলো আর পাওয়া যাচ্ছে না।');
    });

    it('embeds the Bengali browser strings in the page', function () {
        $response = $this->get(route('cart.index'));

        $response->assertOk()
            ->assertSee('<script type="application/json" id="i18n">', false)
            ->assertSee('"locale":"bn"', false)
            ->assertSee('কার্ট থেকে সব পণ্য সরিয়ে ফেলতে চান?', false);
    });
});

describe('website settings', function () {
    it('shows each language’s homepage text', function () {
        app(SiteSettings::class)->update(['hero_title_bn' => 'শীতের নতুন সংগ্রহ', 'hero_title_en' => 'New winter collection']);

        $bengali = $this->get(route('home'));
        $english = $this->get(route('home', ['lang' => 'en']));

        $bengali->assertSee('শীতের নতুন সংগ্রহ')->assertDontSee('New winter collection');
        $english->assertSee('New winter collection')->assertDontSee('শীতের নতুন সংগ্রহ');
    });

    it('falls back to the other language when a setting has only one translation', function () {
        app(SiteSettings::class)->update(['hero_title_en' => 'New winter collection']);

        $response = $this->get(route('home'));

        $response->assertOk()->assertSee('New winter collection');
    });

    it('uses the built-in Bengali headline until one is saved', function () {
        $response = $this->get(route('home'));

        $response->assertOk()->assertSee('মানসম্মত পণ্য, মুহূর্তেই অর্ডার।');
    });

    it('saves Bengali and English versions from the admin form', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'business_name' => 'Craft House',
            'hero_title_bn' => 'শীতের নতুন সংগ্রহ',
            'hero_title_en' => 'New winter collection',
        ]);

        $settings = app(SiteSettings::class);
        expect($settings->translated('hero_title', 'bn'))->toBe('শীতের নতুন সংগ্রহ')
            ->and($settings->translated('hero_title', 'en'))->toBe('New winter collection');
    });
});

describe('admin panel', function () {
    it('shows the admin panel in Bengali', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk()
            ->assertSee('<html lang="bn"', false)
            ->assertSee('আপনার শপের পণ্য যোগ, সম্পাদনা এবং দেখান বা লুকান।')
            ->assertSee('লগআউট');
    });

    it('shows the login page in Bengali', function () {
        $response = $this->get(route('admin.login'));

        $response->assertOk()->assertSee('আবার স্বাগতম');
    });

    it('shows validation messages in Bengali', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), ['price' => 'abc']);

        $response->assertSessionHasErrors([
            'name_bn' => 'বাংলা অথবা ইংরেজিতে পণ্যের নাম দিন।',
            'price' => 'মূল্য অবশ্যই একটি পূর্ণসংখ্যা হতে হবে।',
            'is_active' => 'দৃশ্যমানতা দেওয়া আবশ্যক।',
        ]);
    });

    it('shows a wrong current password message in Bengali', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('admin.password.update'), [
            'current_password' => 'wrong',
            'password' => 'a-new-secret-phrase',
            'password_confirmation' => 'a-new-secret-phrase',
        ]);

        $response->assertSessionHasErrors(['current_password' => 'পাসওয়ার্ড সঠিক নয়।']);
    });

    it('shows failed login messages in Bengali', function () {
        $response = $this->post(route('admin.login'), ['email' => 'nobody@example.com', 'password' => 'wrong']);

        $response->assertSessionHasErrors(['email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।']);
    });

    it('returns AJAX messages in Bengali', function () {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name_bn' => 'হুডি']);

        $response = $this->actingAs($admin)->patchJson(route('admin.products.status', $product), ['is_active' => false]);

        $response->assertOk()->assertJson(['message' => '“হুডি” এখন আপনার শপ থেকে লুকানো আছে।']);
    });

    it('flags products that are missing a translation', function () {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['name_bn' => null, 'name_en' => 'Canvas Tote']);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk()->assertSee('বাংলা নেই');
    });
});
