<?php

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\Badge;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\User;
use App\Services\BadgeService;

describe('BadgeService', function () {
    it('awards cv_complet badge on application creation', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
        ]);

        $service = new BadgeService;
        $service->checkAndAward($application);

        $this->assertDatabaseHas('badges', [
            'candidate_id' => $candidate->id,
            'type' => 'cv_complet',
        ]);
    });

    it('awards high_match badge when matching score > 80', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $application->id,
            'matching_score' => 85.0,
        ]);

        $service = new BadgeService;
        $service->checkAndAward($application);

        $this->assertDatabaseHas('badges', [
            'candidate_id' => $candidate->id,
            'type' => 'high_match',
        ]);
    });

    it('does not award high_match badge when score is 80 or below', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $application->id,
            'matching_score' => 80.0,
        ]);

        $service = new BadgeService;
        $service->checkAndAward($application);

        $this->assertDatabaseMissing('badges', [
            'candidate_id' => $candidate->id,
            'type' => 'high_match',
        ]);
    });

    it('does not award duplicate badges', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
        ]);
        Badge::factory()->create([
            'candidate_id' => $candidate->id,
            'type' => 'cv_complet',
        ]);

        $service = new BadgeService;
        $service->checkAndAward($application);

        $this->assertDatabaseCount('badges', 1);
    });

    it('awards interview_passed when average score > 3', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
            'status' => 'accepted',
        ]);
        Interview::factory()->create([
            'application_id' => $application->id,
            'status' => 'completed',
            'score_technique' => 4,
            'score_communication' => 4,
            'score_motivation' => 3,
        ]);

        $service = new BadgeService;
        $service->checkInterviewBadge($application);

        $this->assertDatabaseHas('badges', [
            'candidate_id' => $candidate->id,
            'type' => 'interview_passed',
        ]);
    });

    it('does not award interview_passed when average score <= 3', function () {
        $candidate = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $application = Application::factory()->create([
            'candidate_id' => $candidate->id,
            'job_offer_id' => $job->id,
            'status' => 'accepted',
        ]);
        Interview::factory()->create([
            'application_id' => $application->id,
            'status' => 'completed',
            'score_technique' => 2,
            'score_communication' => 3,
            'score_motivation' => 2,
        ]);

        $service = new BadgeService;
        $service->checkInterviewBadge($application);

        $this->assertDatabaseMissing('badges', [
            'candidate_id' => $candidate->id,
            'type' => 'interview_passed',
        ]);
    });
});
