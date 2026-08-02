<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Provider('groq')]
#[Model('llama-3.3-70b-versatile')]
class MatchingAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a recruitment expert. Given a required tech stack and a '
            .'candidate CV, score the match from 0 to 100 and list which '
            .'keywords were found in the CV and which are missing. Respond '
            .'ONLY with strict JSON: {"score": <number>, '
            .'"matched_keywords": [...], "missing_keywords": [...]}.';
    }

    /**
     * Score the candidate against the required tech stack.
     *
     * @return array{score?: mixed, matched_keywords?: mixed, missing_keywords?: mixed}
     */
    public function score(string $techStack, string $cvText): array
    {
        $text = trim($this->prompt(
            "Required tech stack: {$techStack}\n\nCandidate CV:\n{$cvText}\n\n"
            .'Score the candidate and list found/missing keywords as JSON.'
        )->text);

        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text);

        return json_decode($text, true) ?? [];
    }
}
