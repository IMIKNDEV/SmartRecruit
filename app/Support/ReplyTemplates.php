<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ReplyTemplates
{
    /**
     * Default templates (French — the platform is recruiter-facing in French).
     * Recruiter overrides are persisted to storage and merged on top.
     *
     * @var array<string, array{title: string, content: string}>
     */
    public const DEFAULTS = [
        'follow_up' => [
            'title' => 'Relance candidat',
            'content' => "Bonjour {candidate},\n\nNous avons bien reçu votre candidature pour le poste de {job_title} et nous vous remercions pour votre intérêt.\n\nNous revenons vers vous afin de faire le point sur l'avancement de votre dossier. N'hésitez pas à nous communiquer votre disponibilité pour un éventuel entretien.\n\nCordialement,\nL'équipe SmartRecruit",
        ],
        'refusal' => [
            'title' => 'Réponse négative standard',
            'content' => "Bonjour {candidate},\n\nNous vous remercions d'avoir pris le temps de postuler au poste de {job_title}.\n\nAprès une étude attentive de votre candidature, nous avons le regret de vous informer que nous ne pouvons pas donner une suite favorable à celle-ci. Nous vous encourageons à postuler à nouveau lors de futures opportunités.\n\nCordialement,\nL'équipe SmartRecruit",
        ],
    ];

    public static function all(): array
    {
        return array_replace_recursive(self::DEFAULTS, self::overrides());
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function update(string $key, string $content): ?array
    {
        $overrides = self::overrides();
        $template = self::get($key);

        if (! $template) {
            return null;
        }

        $overrides[$key] = ['content' => $content];

        Storage::disk('local')->put(self::path(), json_encode(
            $overrides,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));

        return self::get($key);
    }

    public static function reset(string $key): ?array
    {
        $overrides = self::overrides();
        unset($overrides[$key]);

        if ($overrides === []) {
            Storage::disk('local')->delete(self::path());

            return self::get($key);
        }

        Storage::disk('local')->put(self::path(), json_encode(
            $overrides,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));

        return self::get($key);
    }

    /**
     * @return array<string, array{content: string}>
     */
    protected static function overrides(): array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::path())) {
            return [];
        }

        $decoded = json_decode((string) $disk->get(self::path()), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected static function path(): string
    {
        return 'reply_templates.json';
    }
}
