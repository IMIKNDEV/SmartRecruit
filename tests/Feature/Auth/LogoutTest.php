<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('logs out successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(204);
    $this->assertCount(0, $user->tokens);
});

it('rejects logout without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
