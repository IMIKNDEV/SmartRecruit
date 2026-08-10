<?php

namespace App\Services;

use App\Agents\MatchingAgent;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class MatchingService
{
    /**
     * Score an application's CV against its job offer's tech stack.
     *
     * @return array{
     *   score: float,
     *   matched: array<int, string>,
     *   missing: array<int, string>,
     *   strengths: string,
     *   gaps: string,
     *   years_experience: ?int,
     *   education_level: ?string,
     *   languages: array<int, string>,
     *   recommendation: string
     * }
     */
    public function calculateScore(Application $application): array
    {
        $cvText = $this->extractCvText($application->cv_path);

        if ($cvText === '') {
            return [
                'score' => 0.0,
                'matched' => [],
                'missing' => [],
                'strengths' => '',
                'gaps' => '',
                'years_experience' => null,
                'education_level' => null,
                'languages' => [],
                'recommendation' => '',
            ];
        }

        $result = (new MatchingAgent)->score($application->jobOffer->tech_stack, $cvText);

        $educationLevel = $result['education_level'] ?? null;

        return [
            'score' => round((float) ($result['score'] ?? 0), 2),
            'matched' => $result['matched_keywords'] ?? [],
            'missing' => $result['missing_keywords'] ?? [],
            'strengths' => (string) ($result['strengths'] ?? ''),
            'gaps' => (string) ($result['gaps'] ?? ''),
            'years_experience' => isset($result['years_experience'])
                ? max(0, (int) $result['years_experience'])
                : null,
            'education_level' => $educationLevel !== null && $educationLevel !== ''
                ? (string) $educationLevel
                : null,
            'languages' => $result['languages'] ?? [],
            'recommendation' => (string) ($result['recommendation'] ?? ''),
        ];
    }

    protected function extractCvText(string $cvPath): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($cvPath)) {
            return '';
        }

        $content = $disk->get($cvPath);

        if (! is_string($content) || trim($content) === '') {
            return '';
        }

        try {
            return (new Parser)->parseContent($content)->getText();
        } catch (\Exception) {
            return $content;
        }
    }
}
