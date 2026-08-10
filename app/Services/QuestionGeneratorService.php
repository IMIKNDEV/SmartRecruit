<?php

namespace App\Services;

use App\Agents\ConversationalAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\Application;
use App\Models\User;

class QuestionGeneratorService
{
    /**
     * Generate interview questions for an application's tech stack and persist
     * the conversation (user + assistant messages).
     *
     * @return array{questions: string, conversation_id: int}
     */
    public function generate(Application $application): array
    {
        $job = $application->jobOffer;

        $conversation = AgentConversation::firstOrCreate([
            'user_id' => auth()->id(),
            'context_type' => 'interview_questions',
            'context_id' => $application->id,
        ]);

        $userContent = "Generate interview questions for tech stack: {$job->tech_stack}";
        $history = $this->historyFor($conversation);

        $this->storeMessage($conversation, 'user', $userContent);

        $questions = $this->askWithHistory($history, $userContent);

        $this->storeMessage($conversation, 'assistant', $questions);

        return [
            'questions' => $questions,
            'conversation_id' => $conversation->id,
        ];
    }

    /**
     * Send a turn in an existing conversation and persist both messages.
     */
    public function sendMessage(AgentConversation $conversation, string $content): AgentConversationMessage
    {
        $history = $this->historyFor($conversation);

        $this->storeMessage($conversation, 'user', $content);

        $prompt = $content;

        // Recruiter-aware: when the message references a candidate or the
        // candidate pipeline, enrich the prompt with recruiter-owned profiles
        // (details, badges, per-offer IA score, interview scores) so the
        // assistant can answer factually instead of inventing data.
        $recruiter = $conversation->user;
        if ($recruiter && $recruiter->isRecruiter()) {
            $context = $this->candidateContextFor($recruiter, $content);

            if ($context !== null) {
                $prompt .= "\n\n--- Candidate profiles (SmartRecruit data, answer in French using ONLY these facts) ---\n{$context}";
            }
        }

        $reply = $this->askWithHistory($history, $prompt);

        return $this->storeMessage($conversation, 'assistant', $reply);
    }

    /**
     * Build a compact, recruiter-owned candidate context when the message asks
     * about candidates. Returns null when the question is unrelated so generic
     * chats are not polluted with profile data.
     *
     * Detection: explicit candidate keywords (candidat/candidate, profil,
     * score, meilleur, shortlist, pipeline, entretien) OR a case-insensitive
     * match against a candidate first/last/full name in the message.
     */
    public function candidateContextFor(User $recruiter, string $message): ?string
    {
        $jobIds = $recruiter->jobOffers()->pluck('id');

        if ($jobIds->isEmpty()) {
            return null;
        }

        $applications = Application::whereIn('job_offer_id', $jobIds)
            ->with(['candidate.badges', 'jobOffer', 'analysis', 'latestInterview'])
            ->get()
            ->sortByDesc(fn (Application $application) => (float) ($application->analysis?->matching_score ?? 0));

        if ($applications->isEmpty()) {
            return null;
        }

        $haystack = mb_strtolower($message);
        $keywordHit = str_contains($haystack, 'candidat')
            || str_contains($haystack, 'candidate')
            || str_contains($haystack, 'profil')
            || str_contains($haystack, 'score')
            || str_contains($haystack, 'meilleur')
            || str_contains($haystack, 'shortlist')
            || str_contains($haystack, 'pipeline')
            || str_contains($haystack, 'entretien');

        if (! $keywordHit) {
            $byName = $applications->filter(function (Application $application) use ($haystack) {
                $name = mb_strtolower(trim((string) $application->candidate?->name));

                if ($name === '' || ! str_contains($haystack, $name)) {
                    return false;
                }

                // Only trust name matches of a reasonable length (>= 4 chars)
                // to avoid false positives on tiny fragments.
                return mb_strlen($name) >= 4;
            });

            if ($byName->isEmpty()) {
                return null;
            }

            $applications = $byName;
        }

        $lines = [];
        $seenCandidates = [];

        foreach ($applications->take(8) as $application) {
            $candidate = $application->candidate;
            $analysis = $application->analysis;

            if ($candidate && ! isset($seenCandidates[$candidate->id])) {
                $seenCandidates[$candidate->id] = true;

                $badges = $candidate->badges->pluck('type')->map(fn (string $type) => match ($type) {
                    'cv_complet' => 'CV complet',
                    'high_match' => 'forte correspondance',
                    'interview_passed' => 'entretien réussi',
                    default => $type,
                })->implode(', ');

                $lines[] = 'Candidat : '.$candidate->name.' ('.$candidate->email.')'
                    .($badges !== '' ? ' — Signaux : '.$badges : '');
            }

            $parts = ['Offre « '.($application->jobOffer?->title ?? '-').' » : statut '.$application->status];

            if ($analysis !== null) {
                $parts[] = 'score IA '.number_format((float) $analysis->matching_score, 2).'/100';
            }

            if (! empty($analysis?->matched_keywords)) {
                $parts[] = 'compétences trouvées : '.implode(', ', $analysis->matched_keywords);
            }

            if (! empty($analysis?->missing_keywords)) {
                $parts[] = 'compétences manquantes : '.implode(', ', $analysis->missing_keywords);
            }

            $interview = $application->latestInterview;
            if ($interview && $interview->status === 'completed') {
                $parts[] = sprintf(
                    'entretien technique %d/5, communication %d/5, motivation %d/5',
                    $interview->score_technique,
                    $interview->score_communication,
                    $interview->score_motivation
                );
            }

            $lines[] = '  - '.implode(' | ', $parts);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    protected function historyFor(AgentConversation $conversation): array
    {
        return AgentConversationMessage::where('agent_conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (AgentConversationMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();
    }

    protected function askWithHistory(array $history, string $prompt): string
    {
        $agent = new ConversationalAgent;
        $agent->history = $history;

        return $agent->ask($prompt);
    }

    protected function storeMessage(AgentConversation $conversation, string $role, string $content): AgentConversationMessage
    {
        return AgentConversationMessage::create([
            'agent_conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);
    }
}
