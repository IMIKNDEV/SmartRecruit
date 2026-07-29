<?php

use App\Agents\MatchingAgent;
use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Storage;

describe('MatchingService', function () {
    it('calculates matching score and keyword detail via the AI engine', function () {
        MatchingAgent::fake([
            'score' => 75.0,
            'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
            'missing_keywords' => ['Docker'],
        ]);

        $service = new App\Services\MatchingService();
        $job = JobOffer::factory()->create(['tech_stack' => 'PHP, Laravel, MySQL, Docker']);
        $application = Application::factory()->create(['job_offer_id' => $job->id]);

        Storage::fake('public');
        Storage::put('cvs/' . $application->candidate_id . '/test_cv.pdf', 'PHP Laravel MySQL developer');

        $result = $service->calculateScore($application);

        expect($result['score'])->toBe(75.0);
        expect($result['matched'])->toBe(['PHP', 'Laravel', 'MySQL']);
        expect($result['missing'])->toBe(['Docker']);
    });

    it('returns 0 and empty lists when the CV has no extractable text', function () {
        MatchingAgent::fake();

        $service = new App\Services\MatchingService();
        $job = JobOffer::factory()->create(['tech_stack' => 'Python, Django, React']);
        $application = Application::factory()->create(['job_offer_id' => $job->id]);

        Storage::fake('public');
        Storage::put('cvs/' . $application->candidate_id . '/test_cv.pdf', '');

        $result = $service->calculateScore($application);

        expect($result['score'])->toBe(0.0);
        expect($result['matched'])->toBe([]);
        expect($result['missing'])->toBe([]);
    });
});
