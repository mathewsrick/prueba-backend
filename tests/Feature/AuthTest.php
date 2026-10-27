<?php

use App\Models\User;

test('a user can register and receives a token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Ricardo',
        'email' => 'ricardo@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
        ->assertJsonMissingPath('user.password');

    $this->assertDatabaseHas('users', ['email' => 'ricardo@example.com']);
});

test('registration fails with a duplicated email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Other',
        'email' => 'taken@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('a user can log in with valid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['user', 'token']);
});

test('login fails with a wrong password', function () {
    $user = User::factory()->create();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

test('protected routes require authentication', function () {
    $this->getJson('/api/products')->assertUnauthorized();
});

test('login is throttled after too many attempts', function () {
    foreach (range(1, 6) as $attempt) {
        $this->postJson('/api/auth/login', ['email' => 'nobody@example.com', 'password' => 'bad']);
    }

    $this->postJson('/api/auth/login', ['email' => 'nobody@example.com', 'password' => 'bad'])
        ->assertTooManyRequests();
});
