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
     * @return array{score: float, matched: array<int, string>, missing: array<int, string>}
     */
    public function calculateScore(Application $application): array
    {
        $cvText = $this->extractCvText($application->cv_path);

        if ($cvText === '') {
            return ['score' => 0.0, 'matched' => [], 'missing' => []];
        }

        $result = (new MatchingAgent)->score($application->jobOffer->tech_stack, $cvText);

        return [
            'score' => round((float) ($result['score'] ?? 0), 2),
            'matched' => $result['matched_keywords'] ?? [],
            'missing' => $result['missing_keywords'] ?? [],
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
