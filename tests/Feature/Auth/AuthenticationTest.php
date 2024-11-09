<?php

use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertNoContent();
});

test('first login verifies the email', function () {
    $user = User::factory()->unverified()->create();
    $this->assertFalse($user->hasVerifiedEmail());

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $user->refresh();

    $this->assertAuthenticated();
    $response->assertNoContent();
    $this->assertTrue($user->hasVerifiedEmail());
});
