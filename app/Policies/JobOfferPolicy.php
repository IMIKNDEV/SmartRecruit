<?php

namespace App\Policies;

use App\Models\JobOffer;
use App\Models\User;

class JobOfferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobOffer $jobOffer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isRecruiter();
    }

    public function update(User $user, JobOffer $jobOffer): bool
    {
        return $user->isRecruiter() && $jobOffer->recruiter_id === $user->id;
    }

    public function delete(User $user, JobOffer $jobOffer): bool
    {
        return $user->isRecruiter() && $jobOffer->recruiter_id === $user->id;
    }
}
