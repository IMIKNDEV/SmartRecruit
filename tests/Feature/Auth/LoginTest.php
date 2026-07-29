<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@test.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonPath('user.email', 'user@test.com');
});

it('rejects login with wrong password', function () {
    User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@test.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

it('rejects login for non-existent email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@test.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(401);
});

it('rejects login with missing email', function () {
    $response = $this->postJson('/api/login', [
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects login with missing password', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'user@test.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
