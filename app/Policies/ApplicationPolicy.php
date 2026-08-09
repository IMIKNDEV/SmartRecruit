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

    public function delete(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }

    public function restore(User $user, Application $application): bool
    {
        if (! $user->isRecruiter()) {
            return false;
        }

        // Look the offer up with trashed so an archived offer still authorizes
        // restoring its applications.
        $offer = $application->jobOffer()->withTrashed()->first();

        return $offer && $offer->recruiter_id === $user->id;
    }
}
