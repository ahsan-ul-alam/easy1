<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates an admin account', function () {
    $this->artisan('admin:create', ['--name' => 'Owner', '--email' => 'owner@example.com', '--password' => 'secret-phrase'])
        ->assertSuccessful();

    $admin = User::firstWhere('email', 'owner@example.com');
    expect($admin->is_admin)->toBeTrue()
        ->and(Hash::check('secret-phrase', $admin->password))->toBeTrue();
});

it('resets the password of an existing user and makes them an admin', function () {
    $user = User::factory()->create(['email' => 'owner@example.com']);

    $this->artisan('admin:create', ['--email' => 'owner@example.com', '--password' => 'new-secret-phrase'])
        ->assertSuccessful();

    $user->refresh();
    expect($user->is_admin)->toBeTrue()
        ->and(Hash::check('new-secret-phrase', $user->password))->toBeTrue();
});

it('rejects a short password', function () {
    $this->artisan('admin:create', ['--name' => 'Owner', '--email' => 'owner@example.com', '--password' => 'short'])
        ->assertFailed();

    $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);
});
