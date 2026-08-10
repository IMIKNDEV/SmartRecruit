<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\Badge;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo dataset for the live demo / local development.
 *
 * Creates the two demo accounts used by the front-end (recruiter +
 * candidate), a handful of active job offers, applications across every
 * pipeline stage (with AI matching scores), interviews, badges and a saved
 * filter — so the public pages, the recruiter Kanban/dashboard and the
 * candidate area all show real content.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Demo users (password: password) ----------
        $recruiter = User::updateOrCreate(
            ['email' => 'recruiter@smartrecruit.test'],
            ['name' => 'Ayoub Recruiter', 'password' => bcrypt('password'), 'role' => 'recruiter'],
        );

        $candidates = collect([
            ['name' => 'Salma Idrissi', 'email' => 'salma@smartrecruit.test'],
            ['name' => 'Yassine El Amrani', 'email' => 'yassine@smartrecruit.test'],
            ['name' => 'Imane Benali', 'email' => 'imane@smartrecruit.test'],
            ['name' => 'Omar Tazi', 'email' => 'omar@smartrecruit.test'],
            ['name' => 'Kenza Alaoui', 'email' => 'kenza@smartrecruit.test'],
            ['name' => 'Hamza Chraibi', 'email' => 'hamza@smartrecruit.test'],
            ['name' => 'Nada Berrada', 'email' => 'nada@smartrecruit.test'],
            ['name' => 'Mehdi El Fassi', 'email' => 'mehdi@smartrecruit.test'],
        ])->map(fn (array $data) => User::updateOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'password' => bcrypt('password'), 'role' => 'candidate'],
        ));

        // ---------- Job offers ----------
        $offers = [
            [
                'title' => 'Senior Laravel Developer',
                'description' => 'We are looking for an experienced Laravel developer to lead the back-end of our recruitment platform. You will design REST APIs, optimise queries and mentor two junior developers. Ideal profile: 4+ years of PHP, strong Laravel knowledge, comfortable with MySQL and Redis, and a taste for clean, tested code.',
                'tech_stack' => 'PHP, Laravel, MySQL, Redis, Docker, Git',
                'contract_type' => 'CDI',
                'salary' => 32000,
                'deadline' => now()->addDays(25)->format('Y-m-d'),
            ],
            [
                'title' => 'Frontend Developer (Vue.js)',
                'description' => 'Join our product team to build a fast, accessible single-page application with Vue 3 and Tailwind CSS. You will work closely with the design team, contribute to our component library and ensure top-notch performance and accessibility.',
                'tech_stack' => 'Vue.js, JavaScript, Tailwind CSS, Vite, Git',
                'contract_type' => 'CDI',
                'salary' => 26000,
                'deadline' => now()->addDays(18)->format('Y-m-d'),
            ],
            [
                'title' => 'DevOps Engineer Intern',
                'description' => 'Six-month internship to help us automate our infrastructure: CI/CD pipelines with GitHub Actions, Docker images, and monitoring dashboards. A great opportunity to learn cloud-native practices with a supportive team.',
                'tech_stack' => 'Docker, GitHub Actions, Linux, Bash, AWS',
                'contract_type' => 'Stage',
                'salary' => 3500,
                'deadline' => now()->addDays(40)->format('Y-m-d'),
            ],
            [
                'title' => 'Product Designer',
                'description' => 'Design end-to-end experiences for our recruiting products: research, wireframes, high-fidelity UI and design systems. You care about typography, motion and accessibility, and you ship pixel-perfect hand-offs.',
                'tech_stack' => 'Figma, Design Systems, UI, UX, Prototyping',
                'contract_type' => 'Alternance',
                'salary' => 4500,
                'deadline' => now()->addDays(12)->format('Y-m-d'),
            ],
            [
                'title' => 'Data Analyst (SQL)',
                'description' => 'Turn recruitment data into decisions: build dashboards, analyse funnels and time-to-hire metrics, and write clear reports for the management team. Strong SQL required; Python is a plus.',
                'tech_stack' => 'SQL, Python, Power BI, Excel, Statistics',
                'contract_type' => 'CDD',
                'salary' => 22000,
                'deadline' => now()->addDays(30)->format('Y-m-d'),
            ],
            [
                'title' => 'QA Automation Engineer',
                'description' => 'Define and automate our testing strategy: API tests, E2E suites with Playwright, and CI integration. You will work with developers to keep our release pipeline fast and reliable.',
                'tech_stack' => 'Playwright, PHPUnit, Jest, CI/CD, Git',
                'contract_type' => 'CDI',
                'salary' => 24000,
                'deadline' => now()->addDays(22)->format('Y-m-d'),
            ],
        ];

        $jobModels = collect($offers)->map(function (array $data) use ($recruiter) {
            return JobOffer::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['recruiter_id' => $recruiter->id, 'status' => 'active']),
            );
        });

        // ---------- Applications across pipeline stages + AI scores ----------
        $statuses = ['received', 'received', 'received', 'interview', 'interview', 'accepted', 'refused'];
        $i = 0;

        foreach ($jobModels as $job) {
            $appCount = rand(4, 7);
            $jobCandidates = $candidates->shuffle()->take($appCount);

            foreach ($jobCandidates as $candidate) {
                $status = $statuses[$i++ % count($statuses)];

                $application = Application::updateOrCreate(
                    ['candidate_id' => $candidate->id, 'job_offer_id' => $job->id],
                    [
                        'cv_path' => 'cvs/'.$candidate->id.'/cv.pdf',
                        'cover_letter' => "I am genuinely excited about the {$job->title} position. My background and skills match the requirements, and I would love to bring my energy and expertise to your team. I am available for an interview at your convenience.",
                        'status' => $status,
                        'notes' => rand(0, 1) ? 'Strong communication, quick follow-up.' : null,
                    ],
                );

                // Transparent matching score + keyword detail (one per application)
                $stack = array_map('trim', explode(',', $job->tech_stack));
                shuffle($stack);
                $matchedCount = rand(1, max(1, count($stack) - 1));
                $matched = array_slice($stack, 0, $matchedCount);
                $missing = array_values(array_diff($stack, $matched));
                $score = round(35 + $matchedCount * (60 / max(1, count($stack))), 2);

                ApplicationAnalysis::updateOrCreate(
                    ['application_id' => $application->id],
                    [
                        'job_offer_id' => $job->id,
                        'matching_score' => min(98, $score),
                        'matched_keywords' => $matched,
                        'missing_keywords' => $missing,
                    ],
                );

                // Interviews for applications in interview/accepted stage
                if (in_array($status, ['interview', 'accepted'])) {
                    Interview::updateOrCreate(
                        ['application_id' => $application->id],
                        [
                            'scheduled_at' => $status === 'interview' && rand(0, 2) === 0
                                ? now()->subDays(2)->format('Y-m-d H:i:s') // past-due → pending evaluation
                                : now()->addDays(rand(1, 10))->format('Y-m-d H:i:s'),
                            'status' => 'scheduled',
                            'link' => 'https://meet.google.com/abc-defg-hij',
                        ],
                    );
                }

                // Backdate a few applications so time-to-hire / pending tasks show real values
                if (in_array($status, ['received', 'interview']) && rand(0, 1) === 0) {
                    $application->forceFill(['created_at' => now()->subDays(rand(8, 14))])->save();
                } elseif ($status === 'accepted') {
                    $application->forceFill([
                        'created_at' => now()->subDays(rand(12, 20)),
                        'updated_at' => now()->subDays(rand(2, 6)),
                    ])->save();
                } elseif ($status === 'refused') {
                    $application->forceFill(['created_at' => now()->subDays(rand(4, 12))])->save();
                }

                // Screening badges (recruiter-side signals)
                if ($score > 80) {
                    Badge::firstOrCreate(['candidate_id' => $candidate->id, 'type' => 'high_match']);
                }
                if (in_array($status, ['accepted'])) {
                    Badge::firstOrCreate(['candidate_id' => $candidate->id, 'type' => 'interview_passed']);
                }
                Badge::firstOrCreate(['candidate_id' => $candidate->id, 'type' => 'cv_complet']);
            }
        }

        // ---------- Saved filter (recruiter productivity tool) ----------
        SavedFilter::firstOrCreate(
            ['recruiter_id' => $recruiter->id, 'name' => 'High-match candidates (CDI)'],
            [
                'criteria' => [
                    'min_score' => 75,
                    'tech_stack' => ['PHP', 'Laravel'],
                    'contract_type' => 'CDI',
                    'status' => 'received',
                ],
            ],
        );
    }
}
