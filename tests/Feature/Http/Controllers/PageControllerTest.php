<?php

use App\Support\SiteSettings;

it('renders the contact page with configured details', function () {
    app(SiteSettings::class)->update(['whatsapp_number' => '8801712345678', 'phone' => '01812-000000']);

    $response = $this->get(route('pages.contact'));

    $response->assertOk()
        ->assertSee('https://wa.me/8801712345678', false)
        ->assertSee('01812-000000');
});

it('renders the custom privacy text when one is saved', function () {
    app(SiteSettings::class)->update(['privacy_policy_en' => 'We never share your number.']);

    $response = $this->get(route('pages.privacy'));

    $response->assertOk()->assertSee('We never share your number.');
});

it('renders the standard privacy text by default', function () {
    $response = $this->get(route('pages.privacy'));

    $response->assertOk()->assertSee('Your cart is saved only in your own browser.');
});
