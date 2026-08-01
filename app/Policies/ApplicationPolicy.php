<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function create(User $user): bool
    {
        return $user->isCandidate();
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isCandidate()) {
            return $application->candidate_id === $user->id;
        }

        if ($user->isRecruiter()) {
            return $application->jobOffer->recruiter_id === $user->id;
        }

        return false;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }

    public function addNotes(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }

    public function updateTags(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }
}
