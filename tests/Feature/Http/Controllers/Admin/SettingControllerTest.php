<?php

use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

it('renders the settings form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.edit'));

    $response->assertOk()->assertSee('Website settings');
});

it('saves settings and normalizes the WhatsApp number', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'business_name' => 'Craft House',
        'whatsapp_number' => '01712-345678',
        'email' => 'hello@crafthouse.test',
        'logo' => UploadedFile::fake()->image('logo.png', 1200, 400),
    ]);

    $response->assertRedirect(route('admin.settings.edit'))->assertSessionHas('success');
    $settings = app(SiteSettings::class);
    expect($settings->business_name)->toBe('Craft House')
        ->and($settings->whatsapp_number)->toBe('8801712345678')
        ->and($settings->email)->toBe('hello@crafthouse.test');
    Storage::disk('public')->assertExists($settings->logo_path);
});

it('shows the new business name on the storefront', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['business_name' => 'Craft House']);

    $response = $this->get(route('home'));

    $response->assertOk()->assertSee('<title>Craft House</title>', false);
});

it('removes the logo when requested', function () {
    Storage::disk('public')->put('branding/old.webp', 'logo');
    app(SiteSettings::class)->update(['logo_path' => 'branding/old.webp']);

    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['business_name' => 'Craft House', 'remove_logo' => '1']);

    expect(app(SiteSettings::class)->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing('branding/old.webp');
});

it('rejects an invalid WhatsApp number', function (string $number) {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
        'business_name' => 'Craft House',
        'whatsapp_number' => $number,
    ]);

    $response->assertSessionHasErrors('whatsapp_number');
})->with(['call me maybe', '12345']);

it('requires a business name', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), ['business_name' => '']);

    $response->assertSessionHasErrors('business_name');
});
