<?php

use App\Models\User;

it('registers a recruiter successfully', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Ayoub',
        'email' => 'ayoub@recruiter.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'recruiter',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token']);

    $this->assertDatabaseHas('users', [
        'email' => 'ayoub@recruiter.com',
        'role' => 'recruiter',
    ]);
});

it('registers a candidate successfully', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Ahmed',
        'email' => 'ahmed@candidate.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'candidate',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token']);

    $this->assertDatabaseHas('users', [
        'email' => 'ahmed@candidate.com',
        'role' => 'candidate',
    ]);
});

it('rejects registration with invalid role', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'admin',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('rejects registration with duplicate email', function () {
    User::factory()->create(['email' => 'duplicate@test.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'duplicate@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'candidate',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects registration without password confirmation', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'Password123!',
        'role' => 'candidate',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('rejects registration with short password', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'role' => 'candidate',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('rejects registration with missing name', function () {
    $response = $this->postJson('/api/register', [
        'email' => 'test@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'candidate',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('rejects registration with missing email', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'candidate',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
