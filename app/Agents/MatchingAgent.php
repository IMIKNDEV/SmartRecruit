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
        return 'You are a senior recruitment expert. Given a required tech stack '
            .'and a candidate CV, you produce a transparent compatibility analysis: '
            .'a score from 0 to 100, the required keywords found in the CV and the '
            .'missing ones, a short summary of the candidate\'s strengths, the gaps '
            .'versus the offer, an estimated years of experience, the education '
            .'level, the spoken languages, and a clear recommendation for the '
            .'recruiter. Respond ONLY with strict JSON, no markdown, no comments, '
            .'with exactly these keys: '
            .'{"score": <number 0-100>, "matched_keywords": ["..."], '
            .'"missing_keywords": ["..."], "strengths": "<short text>", '
            .'"gaps": "<short text>", "years_experience": <integer or null>, '
            .'"education_level": "<text or null>", "languages": ["..."], '
            .'"recommendation": "<short text>"}. '
            .'Keep strengths, gaps and recommendation to 1-2 sentences each.';
    }

    /**
     * Score the candidate against the required tech stack.
     *
     * @return array{score?: mixed, matched_keywords?: mixed, missing_keywords?: mixed, strengths?: mixed, gaps?: mixed, years_experience?: mixed, education_level?: mixed, languages?: mixed, recommendation?: mixed}
     */
    public function score(string $techStack, string $cvText): array
    {
        $text = trim($this->prompt(
            "Required tech stack: {$techStack}\n\nCandidate CV:\n{$cvText}\n\n"
            .'Analyse the candidate and return the compatibility JSON.'
        )->text);

        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text);

        return json_decode($text, true) ?? [];
    }
}
