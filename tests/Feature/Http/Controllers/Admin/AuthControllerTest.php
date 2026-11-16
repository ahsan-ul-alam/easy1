<?php

use App\Models\Product;
use App\Models\User;

it('redirects guests from admin pages to the login page', function (string $method, string $uri) {
    Product::factory()->create();

    $response = $this->call($method, $uri);

    $response->assertRedirect(route('admin.login'));
})->with([
    ['GET', '/admin/products'],
    ['GET', '/admin/products/create'],
    ['POST', '/admin/products'],
    ['GET', '/admin/products/1/edit'],
    ['PUT', '/admin/products/1'],
    ['DELETE', '/admin/products/1'],
    ['PATCH', '/admin/products/1/status'],
    ['GET', '/admin/settings'],
    ['PUT', '/admin/settings'],
]);

it('forbids signed-in users who are not admins', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.products.index'));

    $response->assertForbidden();
});

it('logs in an admin with valid credentials', function () {
    $admin = User::factory()->admin()->create(['email' => 'owner@example.com']);

    $response = $this->post(route('admin.login'), ['email' => 'owner@example.com', 'password' => 'password']);

    $response->assertRedirect(route('admin.products.index'));
    $this->assertAuthenticatedAs($admin);
});

it('rejects a wrong password', function () {
    User::factory()->admin()->create(['email' => 'owner@example.com']);

    $response = $this->post(route('admin.login'), ['email' => 'owner@example.com', 'password' => 'wrong-password']);

    $response->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
    $this->assertGuest();
});

it('rejects users who are not admins even with the correct password', function () {
    User::factory()->create(['email' => 'customer@example.com']);

    $response = $this->post(route('admin.login'), ['email' => 'customer@example.com', 'password' => 'password']);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('rate limits repeated login attempts with a message on the login form', function () {
    $this->freezeTime();
    User::factory()->admin()->create(['email' => 'owner@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('admin.login'), ['email' => 'owner@example.com', 'password' => 'wrong-password']);
    }

    $response = $this->from(route('admin.login'))->post(route('admin.login'), ['email' => 'owner@example.com', 'password' => 'password']);

    $response->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['email' => 'Too many login attempts. Please try again in 60 seconds.']);
    $this->assertGuest();
});

it('logs the admin out', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.logout'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest();
});

it('has no public registration route', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});
