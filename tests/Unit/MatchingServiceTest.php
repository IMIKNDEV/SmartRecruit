<?php

use App\Agents\MatchingAgent;
use App\Models\Application;
use App\Models\JobOffer;
use App\Services\MatchingService;
use Illuminate\Support\Facades\Storage;

describe('MatchingService', function () {
    it('calculates matching score and keyword detail via the AI engine', function () {
        MatchingAgent::fake([
            [
                'score' => 75.0,
                'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
                'missing_keywords' => ['Docker'],
            ],
        ]);

        $service = new MatchingService;
        $job = JobOffer::factory()->create(['tech_stack' => 'PHP, Laravel, MySQL, Docker']);
        $application = Application::factory()->create(['job_offer_id' => $job->id]);

        Storage::fake('public');
        Storage::disk('public')->put('cvs/'.$application->candidate_id.'/test_cv.pdf', 'PHP Laravel MySQL developer');
        $application->update(['cv_path' => 'cvs/'.$application->candidate_id.'/test_cv.pdf']);

        $result = $service->calculateScore($application);

        expect($result['score'])->toBe(75.0);
        expect($result['matched'])->toBe(['PHP', 'Laravel', 'MySQL']);
        expect($result['missing'])->toBe(['Docker']);
    });

    it('returns the full analysis detail when the AI provides it', function () {
        MatchingAgent::fake([
            [
                'score' => 88.0,
                'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
                'missing_keywords' => ['Redis'],
                'strengths' => 'Strong Laravel architect, clean API design.',
                'gaps' => 'No Redis / caching experience in production.',
                'years_experience' => 6,
                'education_level' => 'Master en informatique',
                'languages' => ['Français', 'Anglais', 'Arabe'],
                'recommendation' => 'À convoquer en entretien.',
            ],
        ]);

        $service = new MatchingService;
        $job = JobOffer::factory()->create(['tech_stack' => 'PHP, Laravel, MySQL, Redis']);
        $application = Application::factory()->create(['job_offer_id' => $job->id]);

        Storage::fake('public');
        Storage::disk('public')->put('cvs/'.$application->candidate_id.'/test_cv.pdf', 'PHP Laravel MySQL architect');
        $application->update(['cv_path' => 'cvs/'.$application->candidate_id.'/test_cv.pdf']);

        $result = $service->calculateScore($application);

        expect($result['score'])->toBe(88.0);
        expect($result['strengths'])->toBe('Strong Laravel architect, clean API design.');
        expect($result['gaps'])->toBe('No Redis / caching experience in production.');
        expect($result['years_experience'])->toBe(6);
        expect($result['education_level'])->toBe('Master en informatique');
        expect($result['languages'])->toBe(['Français', 'Anglais', 'Arabe']);
        expect($result['recommendation'])->toBe('À convoquer en entretien.');
    });

    it('returns 0 and empty lists when the CV has no extractable text', function () {
        MatchingAgent::fake();

        $service = new MatchingService;
        $job = JobOffer::factory()->create(['tech_stack' => 'Python, Django, React']);
        $application = Application::factory()->create(['job_offer_id' => $job->id]);

        Storage::fake('public');
        Storage::disk('public')->put('cvs/'.$application->candidate_id.'/test_cv.pdf', '');
        $application->update(['cv_path' => 'cvs/'.$application->candidate_id.'/test_cv.pdf']);

        $result = $service->calculateScore($application);

        expect($result['score'])->toBe(0.0);
        expect($result['matched'])->toBe([]);
        expect($result['missing'])->toBe([]);
    });
});
