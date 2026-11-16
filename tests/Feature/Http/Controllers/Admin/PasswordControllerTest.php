<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('changes the password when the current password is correct', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put(route('admin.password.update'), [
        'current_password' => 'password',
        'password' => 'a-new-secret-phrase',
        'password_confirmation' => 'a-new-secret-phrase',
    ]);

    $response->assertRedirect(route('admin.password.edit'))->assertSessionHas('success');
    expect(Hash::check('a-new-secret-phrase', $admin->refresh()->password))->toBeTrue();
});

it('rejects an incorrect current password', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put(route('admin.password.update'), [
        'current_password' => 'wrong',
        'password' => 'a-new-secret-phrase',
        'password_confirmation' => 'a-new-secret-phrase',
    ]);

    $response->assertSessionHasErrors('current_password');
    expect(Hash::check('password', $admin->refresh()->password))->toBeTrue();
});
