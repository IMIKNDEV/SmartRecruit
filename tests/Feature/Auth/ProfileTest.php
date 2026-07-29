<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@test.com',
        'role' => 'candidate',
    ]);
    Sanctum::actingAs($this->user);
});

it('returns authenticated user', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonPath('user.id', $this->user->id)
        ->assertJsonPath('user.name', 'Original Name')
        ->assertJsonPath('user.email', 'original@test.com')
        ->assertJsonPath('user.role', 'candidate');
});

it('updates profile name and email', function () {
    $response = $this->putJson('/api/user/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@test.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'Updated Name')
        ->assertJsonPath('user.email', 'updated@test.com');

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Updated Name',
        'email' => 'updated@test.com',
    ]);
});

it('changes password', function () {
    $response = $this->putJson('/api/user/profile', [
        'name' => 'Original Name',
        'email' => 'original@test.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(200);
});

it('cannot update role', function () {
    $response = $this->putJson('/api/user/profile', [
        'name' => 'Original Name',
        'email' => 'original@test.com',
        'role' => 'recruiter',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'role' => 'candidate',
    ]);
});

it('rejects unauthenticated profile access', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
