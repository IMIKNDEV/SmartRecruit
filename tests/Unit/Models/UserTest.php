<?php

use App\Models\User;

describe('User', function () {
    it('determines if user is recruiter', function () {
        $user = User::factory()->recruiter()->create();
        expect($user->isRecruiter())->toBeTrue();
        expect($user->isCandidate())->toBeFalse();
    });

    it('determines if user is candidate', function () {
        $user = User::factory()->candidate()->create();
        expect($user->isCandidate())->toBeTrue();
        expect($user->isRecruiter())->toBeFalse();
    });

    it('has correct role default', function () {
        $user = User::factory()->create();
        expect($user->role)->toBe('candidate');
    });

    it('can create users with explicit role', function () {
        $recruiter = User::factory()->create(['role' => 'recruiter']);
        $candidate = User::factory()->create(['role' => 'candidate']);

        expect($recruiter->role)->toBe('recruiter');
        expect($candidate->role)->toBe('candidate');
    });

    it('has many job offers as recruiter', function () {
        $user = User::factory()->recruiter()->create();
        expect($user->jobOffers())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many applications as candidate', function () {
        $user = User::factory()->candidate()->create();
        expect($user->applications())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many badges', function () {
        $user = User::factory()->candidate()->create();
        expect($user->badges())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many saved filters', function () {
        $user = User::factory()->recruiter()->create();
        expect($user->savedFilters())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many agent conversations', function () {
        $user = User::factory()->create();
        expect($user->agentConversations())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('uses HasApiTokens trait', function () {
        $user = User::factory()->create();
        $traits = class_uses_recursive($user);
        expect($traits)->toContain(Laravel\Sanctum\HasApiTokens::class);
    });
});
