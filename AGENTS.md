# SmartRecruit — AGENTS.md

## Project Overview

SmartRecruit is a gamified recruitment platform with intelligent candidate-job matching. Built as a final project for the DWWM/Backend training (Laravel, PHP, MySQL, REST API) by Ayoub Idbelhaj.

**Jira Project Key:** SR (SmartRecruit)
**Jira URL:** https://gamecafemanager.atlassian.net/browse/SR
**Timeline:** Start 13/07/2026 → Deadline 07/08/2026 → Soutenance from 10/08/2026

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Backend Framework | Laravel | 13.x |
| PHP | PHP | 8.3+ |
| Database (prod) | MySQL | 8.x |
| Database (test) | SQLite | :memory: |
| Frontend | Blade + Vite | Vite 8 |
| CSS | Tailwind CSS | 4.x |
| Auth | Laravel Sanctum | Bearer token API |
| Testing | PHPUnit / Pest | PHPUnit 12.5+ |
| AI | laravel/ai SDK | keyword matching |
| CI/CD | GitHub Actions | workflow.yml |
| Container | Docker + docker-compose | PHP 8.3 |
| Queue | Database driver | async jobs |
| File Storage | Local disk | CV uploads |

---

## Current Project State

The project is a **fresh Laravel 13 scaffold**. Only default files exist. Everything must be built from scratch.

**What exists:** User model (default), migrations (users/cache/jobs), routes (web `/` only), config (all defaults).
**What does NOT exist yet:** Sanctum, api.php routes, any custom models/migrations/controllers/policies/services/tests/docker/ci.

---

## Business Context

An enterprise in Agadir manages recruitment via emails and Excel. SmartRecruit centralizes this: recruiters publish offers, receive applications, follow a Kanban pipeline, and get an automatic matching score.

### User Roles

| Role | Description | Permissions |
|---|---|---|
| `recruiter` | Manages job offers, reviews applications, schedules interviews, sets pipeline status | CRUD on own jobs, manage applications on own jobs, schedule interviews, add notes/comments, view dashboard stats |
| `candidate` | Applies to jobs, uploads CV, views own applications and profile | Apply to jobs, view own applications, view own profile with badges |

**Important:** One user = one role. A user cannot be both recruiter and candidate. Role is set at registration and cannot be changed.

---

## User Stories

### Authentication (US-AUTH)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-AUTH-01 | As a visitor, I can register as recruiter or candidate | Name, email, password required. Email unique. Role selectable at registration. Returns Sanctum token. |
| US-AUTH-02 | As a user, I can login with email/password | Returns Sanctum bearer token. 401 on wrong credentials. |
| US-AUTH-03 | As a user, I can logout | Invalidates current token. 401 if no token. |
| US-AUTH-04 | As a user, I can view/edit my profile | Name, email updatable. Password changeable. Role NOT editable. |

### Job Offers (US-JOB)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-JOB-01 | As a recruiter, I can create a job offer | Fields: title, description, tech_stack (comma-separated), contract_type, salary (nullable), deadline, status (default: active). Only recruiter can create. Returns 201. |
| US-JOB-02 | As a recruiter, I can update my job offer | Only the recruiter who created it can update. Returns 200. |
| US-JOB-03 | As a recruiter, I can soft-delete (archive) my job offer | Uses soft deletes. Only owner can delete. Returns 204. |
| US-JOB-04 | As anyone, I can list active job offers | Paginated. Filterable by contract_type, tech_stack search. Returns 200. |
| US-JOB-05 | As anyone, I can view a single job offer | Returns 200 with job data. 404 if not found or archived. |
| US-JOB-06 | As a recruiter, I can see my dashboard | Active offers count, total applications, recent applications. Returns 200. |

### Applications (US-APP)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-APP-01 | As a candidate, I can apply to a job | Upload CV (PDF, max 5MB), cover letter text. Matching score auto-calculated. Status defaults to `received`. Returns 201. |
| US-APP-02 | As a candidate, I cannot apply twice to the same job | Unique constraint on (candidate_id, job_id). Returns 422. |
| US-APP-03 | As a recruiter, I can view applications for my jobs | Filter by job_id. Sorted by matching_score DESC. Returns 200. |
| US-APP-04 | As a recruiter, I can view a single application | Only for own jobs. Returns 200. 403 if not owner. |
| US-APP-05 | As a recruiter, I can update pipeline status | Status: received → interview → accepted/refused. Only forward movement allowed (no going back from accepted to received). Returns 200. |
| US-APP-06 | As a recruiter, I can add notes and comments to an application | Notes (internal), comments (visible to candidate via email). Returns 200. |
| US-APP-07 | As a candidate, I can view my own applications | List of all my applications with job info and scores. Returns 200. |
| US-APP-08 | As a candidate, I can view a single application detail | Only own applications. Returns 200. 403 otherwise. |

### Interviews (US-INT)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-INT-01 | As a recruiter, I can schedule an interview | Fields: application_id, scheduled_at (datetime), link (nullable video URL). Application must be in `interview` status. Returns 201. |
| US-INT-02 | As a recruiter, I can complete an interview with evaluation | Set scores: technique (1-5), communication (1-5), motivation (1-5). Status → completed. Returns 200. |
| US-INT-03 | As a recruiter, I can cancel an interview | Status → cancelled. Returns 200. |
| US-INT-04 | As a recruiter, I can list interviews for my jobs | Filter by application_id, status. Returns 200. |

### Notifications (US-NOTIF)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-NOTIF-01 | As a candidate, I receive email when application accepted/refused | Mailable sent. Contains job title, new status, optional comment. |

### Bonus Features (US-BONUS)

| ID | Story | Acceptance Criteria |
|---|---|---|
| US-BONUS-01 | Leaderboard: top 5 candidates per job | Sorted by matching_score DESC, limited to 5. Visible on job detail. |
| US-BONUS-02 | Badge system on candidate profiles | Auto-awarded: `cv_complet` (CV uploaded), `high_match` (score > 80), `interview_passed` (interview completed with avg score > 3). |
| US-BONUS-03 | Interview question generator | From tech_stack keywords, generate 3-5 questions. Rule-based (no external AI). |
| US-BONUS-04 | PDF export of candidate profile | Downloadable PDF with name, email, applications, badges, scores. |

---

## Database Schema (Complete)

### Migration Order (important — respect this order for foreign keys)

```
1. create_users_table
2. create_cache_table
3. create_jobs_table (queue jobs, not job offers)
4. create_password_reset_tokens_table (part of users migration)
5. create_personal_access_tokens_table (Sanctum)
6. create_jobs_offers_table (renamed to avoid conflict with queue jobs)
7. create_applications_table
8. create_interviews_table
9. create_badges_table
```

**CRITICAL NAMING:** The job offers table MUST be named `job_offers` (not `jobs`) because Laravel already uses a `jobs` table for the queue system. This is a common mistake.

### Table: `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('recruiter', 'candidate') NOT NULL DEFAULT 'candidate',
    avatar VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_role (role)
);
```

**Notes:**
- `role` is set at registration, NEVER editable after
- `avatar` stores the file path relative to storage disk
- Use the default Laravel `users` table, just add `role` and `avatar` columns via a new migration

### Table: `job_offers`

```sql
CREATE TABLE job_offers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recruiter_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    tech_stack VARCHAR(500) NOT NULL COMMENT 'Comma-separated: PHP, Laravel, MySQL',
    contract_type VARCHAR(50) NOT NULL COMMENT 'CDI, CDD, Stage, Alternance',
    salary DECIMAL(10,2) NULL,
    deadline DATE NOT NULL,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_job_offers_recruiter (recruiter_id),
    INDEX idx_job_offers_status (status),
    INDEX idx_job_offers_deadline (deadline),
    CONSTRAINT fk_job_offers_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Notes:**
- `softDeletes()` on this table
- `tech_stack` is stored as comma-separated string (e.g., "PHP, Laravel, MySQL, Docker")
- `deadline` is the application deadline, NOT the job start date
- `status` is separate from soft deletes — `archived` is a logical state, `deleted_at` is physical soft delete

### Table: `applications`

```sql
CREATE TABLE applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT UNSIGNED NOT NULL,
    job_offer_id BIGINT UNSIGNED NOT NULL,
    cv_path VARCHAR(255) NOT NULL COMMENT 'Relative path: cvs/{user_id}/{filename}.pdf',
    cover_letter TEXT NOT NULL,
    matching_score DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score 0.00-100.00',
    status ENUM('received', 'interview', 'accepted', 'refused') NOT NULL DEFAULT 'received',
    notes TEXT NULL COMMENT 'Internal recruiter notes',
    comments TEXT NULL COMMENT 'Visible to candidate',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_application (candidate_id, job_offer_id),
    INDEX idx_applications_candidate (candidate_id),
    INDEX idx_applications_job (job_offer_id),
    INDEX idx_applications_status (status),
    INDEX idx_applications_score (matching_score),
    CONSTRAINT fk_applications_candidate FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE
);
```

**Notes:**
- `UNIQUE KEY (candidate_id, job_offer_id)` — prevents duplicate applications
- `matching_score` is calculated at upload time, NOT real-time
- `status` enum values: `received` → `interview` → `accepted` | `refused`
- `cv_path` stores relative path, file stored on `public` disk

### Table: `interviews`

```sql
CREATE TABLE interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    link VARCHAR(500) NULL COMMENT 'Video meeting URL',
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    score_technique TINYINT UNSIGNED NULL CHECK (score_technique BETWEEN 1 AND 5),
    score_communication TINYINT UNSIGNED NULL CHECK (score_communication BETWEEN 1 AND 5),
    score_motivation TINYINT UNSIGNED NULL CHECK (score_motivation BETWEEN 1 AND 5),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_interviews_application (application_id),
    INDEX idx_interviews_status (status),
    INDEX idx_interviews_date (scheduled_at),
    CONSTRAINT fk_interviews_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);
```

**Notes:**
- One application can have multiple interviews (but typically 1-2)
- Scores are NULL until interview is completed
- `link` is optional (Google Meet, Zoom, etc.)

### Table: `badges`

```sql
CREATE TABLE badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT UNSIGNED NOT NULL,
    type ENUM('cv_complet', 'high_match', 'interview_passed') NOT NULL,
    awarded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_badge (candidate_id, type),
    INDEX idx_badges_candidate (candidate_id),
    CONSTRAINT fk_badges_candidate FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Notes:**
- `UNIQUE KEY (candidate_id, type)` — one badge per type per candidate
- Badges are awarded automatically (observer/event pattern)
- `type` values: `cv_complet`, `high_match`, `interview_passed`

---

## Eloquent Relationships

```php
// User Model
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // A recruiter has many job offers
    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'recruiter_id');
    }

    // A candidate has many applications
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }

    // A candidate has many badges
    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class, 'candidate_id');
    }

    // Helper: is recruiter?
    public function isRecruiter(): bool
    {
        return $this->role === 'recruiter';
    }

    // Helper: is candidate?
    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }
}

// JobOffer Model
class JobOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recruiter_id', 'title', 'description', 'tech_stack',
        'contract_type', 'salary', 'deadline', 'status',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    // A job offer belongs to a recruiter
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    // A job offer has many applications
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // Parse tech_stack string into array
    public function getTechStackArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->tech_stack));
    }
}

// Application Model
class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'job_offer_id', 'cv_path', 'cover_letter',
        'matching_score', 'status', 'notes', 'comments',
    ];

    protected function casts(): array
    {
        return [
            'matching_score' => 'decimal:2',
        ];
    }

    // An application belongs to a candidate
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    // An application belongs to a job offer
    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    // An application has many interviews
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    // An application has one latest interview
    public function latestInterview(): HasOne
    {
        return $this->hasOne(Interview::class)->latestOfMany();
    }
}

// Interview Model
class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'scheduled_at', 'link', 'status',
        'score_technique', 'score_communication', 'score_motivation',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    // An interview belongs to an application
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // Calculate average score
    public function getAverageScoreAttribute(): ?float
    {
        $scores = array_filter([
            $this->score_technique,
            $this->score_communication,
            $this->score_motivation,
        ]);

        return $scores ? round(array_sum($scores) / count($scores), 2) : null;
    }
}

// Badge Model
class Badge extends Model
{
    use HasFactory;

    protected $fillable = ['candidate_id', 'type', 'awarded_at'];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    // A badge belongs to a candidate
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}
```

---

## Migration File Naming Convention

```
database/migrations/
  2026_07_13_000000_create_users_table.php        (modify existing — add role, avatar)
  2026_07_13_000001_create_cache_table.php         (exists — no change)
  2026_07_13_000002_create_jobs_table.php          (exists — no change)
  2026_07_13_000003_create_personal_access_tokens_table.php  (Sanctum — publish)
  2026_07_13_000004_create_job_offers_table.php
  2026_07_13_000005_create_applications_table.php
  2026_07_13_000006_create_interviews_table.php
  2026_07_13_000007_create_badges_table.php
```

---

## API Design (Complete)

### Authentication

| Method | Endpoint | Auth | Description | Request | Response |
|---|---|---|---|---|---|
| POST | `/api/register` | No | Register user | `{name, email, password, role}` | `{user, token}` 201 |
| POST | `/api/login` | No | Login | `{email, password}` | `{user, token}` 200 |
| POST | `/api/logout` | Yes | Logout (revoke token) | — | 204 |
| GET | `/api/user` | Yes | Get current user | — | `{user}` 200 |
| PUT | `/api/user/profile` | Yes | Update profile | `{name?, email?, password?}` | `{user}` 200 |

### Job Offers

| Method | Endpoint | Auth | Role | Description | Response |
|---|---|---|---|---|---|
| GET | `/api/job-offers` | No | — | List active offers (paginated) | `{data: [...], meta}` 200 |
| GET | `/api/job-offers/{id}` | No | — | Show single offer | `{data: job}` 200 |
| POST | `/api/job-offers` | Yes | recruiter | Create offer | `{data: job}` 201 |
| PUT | `/api/job-offers/{id}` | Yes | recruiter | Update own offer | `{data: job}` 200 |
| DELETE | `/api/job-offers/{id}` | Yes | recruiter | Soft-delete own offer | 204 |

**Query Parameters for GET /api/job-offers:**
- `?page=1&per_page=15` — pagination
- `?contract_type=CDI` — filter by contract type
- `?search=PHP` — search in title, description, tech_stack
- `?status=active` — filter by status (default: active only)

### Applications

| Method | Endpoint | Auth | Role | Description | Response |
|---|---|---|---|---|---|
| GET | `/api/applications` | Yes | candidate | List own applications | `{data: [...]}` 200 |
| GET | `/api/job-offers/{id}/applications` | Yes | recruiter | List applications for job | `{data: [...], leaderboard}` 200 |
| GET | `/api/applications/{id}` | Yes | — | Show application detail | `{data: application}` 200 |
| POST | `/api/job-offers/{id}/apply` | Yes | candidate | Apply to job | `{data: application}` 201 |
| PUT | `/api/applications/{id}/status` | Yes | recruiter | Update pipeline status | `{data: application}` 200 |
| PUT | `/api/applications/{id}/notes` | Yes | recruiter | Add notes/comments | `{data: application}` 200 |

**POST /api/job-offers/{id}/apply — Multipart Form:**
- `cv` — PDF file, max 5MB, required
- `cover_letter` — text, required

**PUT /api/applications/{id}/status — Request:**
- `{status: "interview"}` — must be valid transition

### Interviews

| Method | Endpoint | Auth | Role | Description | Response |
|---|---|---|---|---|---|
| POST | `/api/applications/{id}/interviews` | Yes | recruiter | Schedule interview | `{data: interview}` 201 |
| PUT | `/api/interviews/{id}/complete` | Yes | recruiter | Complete + score | `{data: interview}` 200 |
| PUT | `/api/interviews/{id}/cancel` | Yes | recruiter | Cancel interview | `{data: interview}` 200 |
| GET | `/api/applications/{id}/interviews` | Yes | recruiter | List interviews | `{data: [...]}` 200 |

**POST /api/applications/{id}/interviews — Request:**
- `{scheduled_at: "2026-07-20 14:00:00", link: "https://meet.google.com/..."}`

**PUT /api/interviews/{id}/complete — Request:**
- `{score_technique: 4, score_communication: 5, score_motivation: 3}`

### Dashboard

| Method | Endpoint | Auth | Role | Description | Response |
|---|---|---|---|---|---|
| GET | `/api/dashboard/stats` | Yes | recruiter | Dashboard statistics | `{active_offers, total_applications, acceptance_rate, recent_applications}` 200 |
| GET | `/api/leaderboard/{jobOfferId}` | Yes | — | Top 5 candidates per job | `{data: [...top 5 by score]}` 200 |

### Badges (Bonus)

| Method | Endpoint | Auth | Role | Description | Response |
|---|---|---|---|---|---|
| GET | `/api/candidates/{id}/badges` | Yes | — | Get candidate badges | `{data: [...badges]}` 200 |

---

## HTTP Status Codes

| Code | When to use |
|---|---|
| 200 | Successful GET, PUT |
| 201 | Successful POST (resource created) |
| 204 | Successful DELETE (no content) |
| 400 | Bad request (malformed data) |
| 401 | Unauthenticated (no token or invalid token) |
| 403 | Unauthorized (authenticated but wrong role/ownership) |
| 404 | Resource not found |
| 422 | Validation error (invalid input) |
| 409 | Conflict (e.g., duplicate application) |
| 500 | Server error |

### Error Response Format

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

## Matching Algorithm (Detailed)

### Input
- `job.tech_stack` — comma-separated string: `"PHP, Laravel, MySQL, Docker, Git"`
- `application.cv_path` — PDF file on storage disk

### Steps

```
1. EXTRACT job keywords
   job_keywords = explode(',', job.tech_stack)
   // → ["PHP", "Laravel", "MySQL", "Docker", "Git"]
   // → lowercase + trim each: ["php", "laravel", "mysql", "docker", "git"]

2. EXTRACT CV text
   cv_text = pdf_to_text(cv_path)
   // Use Smalot\PdfParser or similar Laravel-compatible PDF parser
   // Convert to lowercase

3. MATCH keywords
   matched = 0
   total = count(job_keywords)

   foreach job_keywords as keyword:
       if str_contains(cv_text, keyword):
           matched++

4. CALCULATE score
   score = (matched / total) * 100
   // Round to 2 decimal places
   // Example: 3/5 keywords found → 60.00

5. STORE
   application.matching_score = score
```

### Implementation

```php
// app/Services/MatchingService.php

namespace App\Services;

use App\Models\JobOffer;
use App\Models\Application;
use Smalot\PdfParser\Parser;

class MatchingService
{
    public function calculateScore(Application $application): float
    {
        $job = $application->jobOffer;
        $keywords = $this->parseKeywords($job->tech_stack);
        $cvText = $this->extractCvText($application->cv_path);

        if (empty($keywords)) {
            return 0.0;
        }

        $matched = 0;
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($cvText), strtolower($keyword))) {
                $matched++;
            }
        }

        return round(($matched / count($keywords)) * 100, 2);
    }

    protected function parseKeywords(string $techStack): array
    {
        return array_map('trim', array_map('strtolower', explode(',', $techStack)));
    }

    protected function extractCvText(string $cvPath): string
    {
        $fullPath = storage_path('app/public/' . $cvPath);
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        return $pdf->getText();
    }
}
```

### Important Notes
- The matching runs **once at application upload time** (in the ApplicationObserver or in the controller)
- Score is stored in `applications.matching_score` — NOT recalculated on read
- Use `Queue::job(new CalculateMatchingScoreJob($application))` for async processing
- Return 201 immediately, process score in background

---

## Application Pipeline (State Machine)

```
received ──▶ interview ──▶ accepted
                         ──▶ refused
```

**Rules:**
- `received` → `interview` (recruiter schedules interview)
- `interview` → `accepted` (recruiter accepts candidate)
- `interview` → `refused` (recruiter rejects candidate)
- `received` → `refused` (direct rejection without interview)
- `accepted` → `refused` (can change mind) — OPTIONAL, debatable
- **Cannot go backwards**: `accepted` → `received` is FORBIDDEN

### Validation in Controller

```php
// Valid transitions
$validTransitions = [
    'received' => ['interview', 'refused'],
    'interview' => ['accepted', 'refused'],
    'accepted' => [],   // terminal state
    'refused' => [],    // terminal state
];

if (!in_array($request->status, $validTransitions[$currentStatus])) {
    return response()->json([
        'message' => "Cannot transition from {$currentStatus} to {$request->status}"
    ], 422);
}
```

---

## File Storage (CV Uploads)

### Disk Configuration
- Use `public` disk for CVs and avatars
- Run `php artisan storage:link` to create symlink
- Path: `storage/app/public/`

### CV Storage Path
```
storage/app/public/cvs/{candidate_id}/{timestamp}_{original_filename}.pdf
// Example: storage/app/public/cvs/42/1721234567_cv_ayoub.pdf
```

### Avatar Storage Path
```
storage/app/public/avatars/{user_id}/{timestamp}_{original_filename}.{ext}
// Example: storage/app/public/avatars/42/1721234567_photo.jpg
```

### Upload Validation Rules

```php
// CV upload
'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB max

// Avatar upload
'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB max
```

### Storage Code

```php
// Store CV
$cvPath = $request->file('cv')->store(
    'cvs/' . auth()->id(),
    'public'
);

// Store Avatar
$avatarPath = $request->file('avatar')->store(
    'avatars/' . auth()->id(),
    'public'
);
```

---

## Middleware & Authorization

### Sanctum Middleware

```php
// bootstrap/app.php — add API routing
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ADD THIS
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

### Role Middleware (Custom)

```php
// app/Http/Middleware/EnsureUserHasRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!in_array(auth()->user()->role, $roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
```

### Register Middleware

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void
{
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

### Route Protection Rules

```php
// routes/api.php

// Public routes (no auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/job-offers', [JobOfferController::class, 'index']);
Route::get('/job-offers/{id}', [JobOfferController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Recruiter-only routes
    Route::middleware('role:recruiter')->group(function () {
        Route::apiResource('job-offers', JobOfferController::class)->except(['index', 'show']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    });

    // Candidate-only routes
    Route::middleware('role:candidate')->group(function () {
        Route::post('/job-offers/{id}/apply', [ApplicationController::class, 'apply']);
        Route::get('/applications', [ApplicationController::class, 'myApplications']);
    });

    // Shared authenticated routes (with ownership checks in controller/policy)
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);
    Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
    Route::put('/applications/{id}/notes', [ApplicationController::class, 'updateNotes']);
    Route::get('/job-offers/{id}/applications', [ApplicationController::class, 'byJob']);
    Route::post('/applications/{id}/interviews', [InterviewController::class, 'store']);
    Route::put('/interviews/{id}/complete', [InterviewController::class, 'complete']);
    Route::put('/interviews/{id}/cancel', [InterviewController::class, 'cancel']);
    Route::get('/applications/{id}/interviews', [InterviewController::class, 'index']);
    Route::get('/leaderboard/{jobOfferId}', [LeaderboardController::class, 'show']);
});
```

### Authorization in Controllers (Ownership Checks)

```php
// In JobOfferController@update
public function update(UpdateJobOfferRequest $request, JobOffer $jobOffer)
{
    // Policy check: only the recruiter who created the offer can update
    $this->authorize('update', $jobOffer);

    $jobOffer->update($request->validated());
    return new JobOfferResource($jobOffer);
}

// In ApplicationController@show
public function show(Application $application)
{
    $this->authorize('view', $application);

    return new ApplicationResource($application->load(['candidate', 'jobOffer', 'interviews']));
}
```

---

## Policies (Complete)

```php
// app/Policies/JobOfferPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\JobOffer;

class JobOfferPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Anyone can list active offers
    }

    public function view(User $user, JobOffer $jobOffer): bool
    {
        return true; // Anyone can view an active offer
    }

    public function create(User $user): bool
    {
        return $user->isRecruiter();
    }

    public function update(User $user, JobOffer $jobOffer): bool
    {
        return $user->isRecruiter() && $jobOffer->recruiter_id === $user->id;
    }

    public function delete(User $user, JobOffer $jobOffer): bool
    {
        return $user->isRecruiter() && $jobOffer->recruiter_id === $user->id;
    }
}

// app/Policies/ApplicationPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Application;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        // Candidate can view own applications
        if ($user->isCandidate() && $application->candidate_id === $user->id) {
            return true;
        }

        // Recruiter can view applications on own job offers
        if ($user->isRecruiter() && $application->jobOffer->recruiter_id === $user->id) {
            return true;
        }

        return false;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }

    public function addNotes(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $application->jobOffer->recruiter_id === $user->id;
    }
}
```

---

## Form Requests (Validation Rules)

```php
// app/Http/Requests/RegisterRequest.php
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:recruiter,candidate'],
        ];
    }
}

// app/Http/Requests/LoginRequest.php
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}

// app/Http/Requests/StoreJobOfferRequest.php
class StoreJobOfferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'tech_stack' => ['required', 'string', 'max:500'],
            'contract_type' => ['required', 'string', 'in:CDI,CDD,Stage,Alternance,Freelance'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['required', 'date', 'after:today'],
        ];
    }
}

// app/Http/Requests/ApplyRequest.php
class ApplyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'cover_letter' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }
}

// app/Http/Requests/UpdateApplicationStatusRequest.php
class UpdateApplicationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:interview,accepted,refused'],
        ];
    }
}

// app/Http/Requests/StoreInterviewRequest.php
class StoreInterviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'link' => ['nullable', 'url', 'max:500'],
        ];
    }
}

// app/Http/Requests/CompleteInterviewRequest.php
class CompleteInterviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'score_technique' => ['required', 'integer', 'between:1,5'],
            'score_communication' => ['required', 'integer', 'between:1,5'],
            'score_motivation' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
```

---

## API Resources (Response Format)

```php
// app/Http/Resources/JobOfferResource.php
class JobOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'tech_stack' => $this->tech_stack,
            'tech_stack_array' => $this->tech_stack_array,
            'contract_type' => $this->contract_type,
            'salary' => $this->salary,
            'deadline' => $this->deadline->format('Y-m-d'),
            'status' => $this->status,
            'applications_count' => $this->whenCounted('applications'),
            'recruiter' => new UserResource($this->whenLoaded('recruiter')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

// app/Http/Resources/ApplicationResource.php
class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matching_score' => $this->matching_score,
            'status' => $this->status,
            'cv_path' => $this->cv_path,
            'cover_letter' => $this->cover_letter,
            'notes' => $this->when($request->user()->isRecruiter(), $this->notes),
            'comments' => $this->when($request->user()->isRecruiter(), $this->comments),
            'candidate' => new UserResource($this->whenLoaded('candidate')),
            'job_offer' => new JobOfferResource($this->whenLoaded('jobOffer')),
            'interviews' => InterviewResource::collection($this->whenLoaded('interviews')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($request->user()->id === $this->id, $this->email),
            'role' => $this->role,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'badges' => BadgeResource::collection($this->whenLoaded('badges')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

// app/Http/Resources/InterviewResource.php
class InterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheduled_at' => $this->scheduled_at->toISOString(),
            'link' => $this->link,
            'status' => $this->status,
            'score_technique' => $this->score_technique,
            'score_communication' => $this->score_communication,
            'score_motivation' => $this->score_motivation,
            'average_score' => $this->average_score,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

---

## Laravel File Structure (Target)

```
app/
├── Console/
│   └── Commands/
│       └── AwardBadgeCommand.php           # artisan badges:award
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── JobOfferController.php
│   │       ├── ApplicationController.php
│   │       ├── InterviewController.php
│   │       ├── DashboardController.php
│   │       ├── LeaderboardController.php
│   │       └── BadgeController.php
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php
│   ├── Requests/
│   │   ├── RegisterRequest.php
│   │   ├── LoginRequest.php
│   │   ├── UpdateProfileRequest.php
│   │   ├── StoreJobOfferRequest.php
│   │   ├── UpdateJobOfferRequest.php
│   │   ├── ApplyRequest.php
│   │   ├── UpdateApplicationStatusRequest.php
│   │   ├── UpdateApplicationNotesRequest.php
│   │   ├── StoreInterviewRequest.php
│   │   └── CompleteInterviewRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── JobOfferResource.php
│       ├── ApplicationResource.php
│       ├── InterviewResource.php
│       └── BadgeResource.php
├── Jobs/
│   └── CalculateMatchingScoreJob.php
├── Mail/
│   └── ApplicationStatusMail.php
├── Models/
│   ├── User.php                           # MODIFIED: add role, avatar, HasApiTokens
│   ├── JobOffer.php
│   ├── Application.php
│   ├── Interview.php
│   └── Badge.php
├── Observers/
│   ├── ApplicationObserver.php            # auto-calculate score on created
│   └── InterviewObserver.php              # auto-award badge on completed
├── Policies/
│   ├── JobOfferPolicy.php
│   └── ApplicationPolicy.php
└── Services/
    ├── MatchingService.php
    └── BadgeService.php

bootstrap/
└── app.php                                # MODIFIED: add api routes + role middleware

config/
└── (all defaults — no published configs needed except Sanctum via env)

database/
├── factories/
│   ├── UserFactory.php                    # MODIFIED: add role factory states
│   ├── JobOfferFactory.php
│   ├── ApplicationFactory.php
│   ├── InterviewFactory.php
│   └── BadgeFactory.php
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php      # MODIFIED
│   ├── 0001_01_01_000001_create_cache_table.php       # exists
│   ├── 0001_01_01_000002_create_jobs_table.php        # exists
│   ├── 2026_07_13_000003_create_personal_access_tokens_table.php
│   ├── 2026_07_13_000004_create_job_offers_table.php
│   ├── 2026_07_13_000005_create_applications_table.php
│   ├── 2026_07_13_000006_create_interviews_table.php
│   └── 2026_07_13_000007_create_badges_table.php
└── seeders/
    └── DatabaseSeeder.php                 # MODIFIED: seed test data

resources/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── views/
    ├── layouts/
    │   └── app.blade.php                 # main layout
    ├── components/
    │   ├── navbar.blade.php
    │   ├── sidebar.blade.php
    │   └── kanban-card.blade.php
    ├── dashboard/
    │   └── index.blade.php               # recruiter dashboard
    ├── job-offers/
    │   ├── index.blade.php               # list jobs
    │   ├── show.blade.php                # job detail
    │   ├── create.blade.php              # create form
    │   └── edit.blade.php                # edit form
    ├── applications/
    │   ├── index.blade.php               # candidate's applications
    │   ├── show.blade.php                # application detail
    │   └── kanban.blade.php              # recruiter Kanban board
    ├── interviews/
    │   ├── index.blade.php               # list interviews
    │   └── schedule.blade.php            # schedule form
    ├── auth/
    │   ├── login.blade.php
    │   └── register.blade.php
    └── profiles/
        └── show.blade.php                # candidate profile with badges

routes/
├── web.php                               # MODIFIED: all Blade routes
├── api.php                               # NEW: all API routes
└── console.php

tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegisterTest.php
│   │   ├── LoginTest.php
│   │   └── LogoutTest.php
│   ├── JobOfferTest.php
│   ├── ApplicationTest.php
│   ├── InterviewTest.php
│   ├── DashboardTest.php
│   └── Api/
│       └── JobOfferApiTest.php
├── Unit/
│   ├── MatchingServiceTest.php
│   ├── BadgeServiceTest.php
│   └── Models/
│       └── ApplicationTest.php
└── TestCase.php

docker/
├── Dockerfile
└── docker-compose.yml

.github/
└── workflows/
    └── tests.yml
```

---

## Testing Strategy

### Test Configuration (already set in phpunit.xml)
- DB: SQLite `:memory:`
- Queue: `sync` (jobs run immediately)
- Cache: `array`
- Mail: `array` (captures emails, does not send)

### What to Test

#### Feature Tests (API Endpoints)

```php
// tests/Feature/Auth/RegisterTest.php
it('can register as recruiter', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Ayoub',
        'email' => 'ayoub@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'recruiter',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token']);
    $this->assertDatabaseHas('users', ['email' => 'ayoub@example.com', 'role' => 'recruiter']);
});

it('cannot register with invalid role', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertStatus(422);
});

// tests/Feature/JobOfferTest.php
it('recruiter can create job offer', function () {
    $user = User::factory()->create(['role' => 'recruiter']);

    $response = $this->actingAs($user)->postJson('/api/job-offers', [
        'title' => 'Laravel Developer',
        'description' => 'We are looking for a Laravel developer with 3+ years of experience...',
        'tech_stack' => 'PHP, Laravel, MySQL',
        'contract_type' => 'CDI',
        'salary' => 15000,
        'deadline' => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['title' => 'Laravel Developer']);
});

it('candidate cannot create job offer', function () {
    $user = User::factory()->create(['role' => 'candidate']);

    $response = $this->actingAs($user)->postJson('/api/job-offers', [
        'title' => 'Laravel Developer',
        'description' => 'Looking for a developer...',
        'tech_stack' => 'PHP, Laravel',
        'contract_type' => 'CDI',
        'deadline' => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertStatus(403);
});

it('unauthenticated user cannot create job offer', function () {
    $response = $this->postJson('/api/job-offers', [
        'title' => 'Laravel Developer',
    ]);

    $response->assertStatus(401);
});
```

#### Unit Tests (Services/Algorithms)

```php
// tests/Unit/MatchingServiceTest.php
it('calculates matching score correctly', function () {
    $service = new MatchingService();

    $job = JobOffer::factory()->create(['tech_stack' => 'PHP, Laravel, MySQL']);
    $application = Application::factory()->create(['job_offer_id' => $job->id]);

    // Create a mock CV file with PHP and Laravel keywords
    Storage::fake('public');
    Storage::put('cvs/' . $application->candidate_id . '/test_cv.pdf', 'PHP Laravel MySQL developer');

    $score = $service->calculateScore($application);

    expect($score)->toBe(100.0); // All 3 keywords found
});

it('returns 0 when no keywords match', function () {
    $service = new MatchingService();

    $job = JobOffer::factory()->create(['tech_stack' => 'Python, Django, React']);
    $application = Application::factory()->create(['job_offer_id' => $job->id]);

    Storage::fake('public');
    Storage::put('cvs/' . $application->candidate_id . '/test_cv.pdf', 'PHP Laravel developer');

    $score = $service->calculateScore($application);

    expect($score)->toBe(0.0);
});
```

#### Queue Tests

```php
// tests/Feature/ApplicationTest.php
it('dispatches matching score job on apply', function () {
    Queue::fake();

    $candidate = User::factory()->create(['role' => 'candidate']);
    $job = JobOffer::factory()->create();

    $response = $this->actingAs($candidate)->postJson("/api/job-offers/{$job->id}/apply", [
        'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'cover_letter' => 'I am interested in this position...',
    ]);

    $response->assertStatus(201);
    Queue::assertPushed(CalculateMatchingScoreJob::class);
});
```

### Test Patterns to Follow

1. **Each test = one assertion** (or one logical behavior)
2. **Use `it('...')` syntax** (Pest) or `public function test_...()` (PHPUnit)
3. **Use factories** for all model creation
4. **Use `actingAs()`** for authenticated requests
5. **Use `$this->postJson()`** for API testing (returns JSON response)
6. **Always assert:** status code, database state, JSON structure
7. **Use `Queue::fake()`** for testing jobs without running them
8. **Use `Mail::fake()`** for testing emails without sending
9. **Use `Storage::fake()`** for testing file uploads without writing to disk
10. **Group tests by feature** (Auth/, JobOffer, Application, etc.)

---

## Badge Awarding Logic

```php
// app/Services/BadgeService.php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use App\Models\Badge;

class BadgeService
{
    public function checkAndAward(Application $application): void
    {
        $candidate = $application->candidate;

        // CV Complet: always awarded when application is created
        $this->awardIfNotExists($candidate, 'cv_complet');

        // High Match: awarded if score > 80
        if ($application->matching_score > 80) {
            $this->awardIfNotExists($candidate, 'high_match');
        }
    }

    public function checkInterviewBadge(Application $application): void
    {
        if ($application->status === 'accepted') {
            $interview = $application->interviews()
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->first();

            if ($interview && $interview->average_score > 3) {
                $this->awardIfNotExists($application->candidate, 'interview_passed');
            }
        }
    }

    protected function awardIfNotExists(User $candidate, string $type): void
    {
        Badge::firstOrCreate([
            'candidate_id' => $candidate->id,
            'type' => $type,
        ], [
            'awarded_at' => now(),
        ]);
    }
}
```

---

## Notification (Mailable)

```php
// app/Mail/ApplicationStatusMail.php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationStatusMail extends Mailable
{
    public function __construct(
        public Application $application,
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->application->status === 'accepted' ? 'acceptée' : 'refusée';

        return new Envelope(
            subject: "Votre candidature a été {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application.status',
            with: [
                'candidateName' => $this->application->candidate->name,
                'jobTitle' => $this->application->jobOffer->title,
                'status' => $this->application->status,
                'comment' => $this->application->comments,
            ],
        );
    }
}
```

---

## Docker Setup

### Dockerfile

```dockerfile
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev libfreetype6-dev libjpeg62-turbo-dev libwebp-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN php artisan key:generate
RUN php artisan storage:link || true

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### docker-compose.yml

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=smartrecruit
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - mysql

  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      - MYSQL_DATABASE=smartrecruit
      - MYSQL_ROOT_PASSWORD=secret
    volumes:
      - mysql_data:/var/lib/mysql

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan queue:work --tries=3
    volumes:
      - .:/var/www
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=smartrecruit
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - mysql

volumes:
  mysql_data:
```

---

## CI/CD (GitHub Actions)

### .github/workflows/tests.yml

```yaml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: smartrecruit_test
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=5

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json, bcmath, pdo, pdo_mysql
          coverage: none

      - name: Install Dependencies
        run: composer install --no-progress --prefer-dist

      - name: Prepare Environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run Tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: smartrecruit_test
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan test

      - name: Check Code Style
        run: ./vendor/bin/pint --test
```

---

## Deployment Checklist

### Pre-Deployment
- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` generated
- [ ] `.env` NOT committed to git (in `.gitignore`)
- [ ] All migrations run: `php artisan migrate --force`
- [ ] Storage linked: `php artisan storage:link`
- [ ] Cache cleared: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Optimized: `php artisan optimize`

### Platform (Railway / Render)
- [ ] MySQL add-on configured
- [ ] Environment variables set (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, etc.)
- [ ] Start command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`
- [ ] Or use a Procfile for worker + web

### Post-Deployment
- [ ] Test login/register
- [ ] Test job CRUD
- [ ] Test application flow
- [ ] Verify storage (CV uploads)
- [ ] Check queue worker is running

---

## Common Mistakes to Avoid

### 1. Table Naming
**WRONG:** `jobs` table for job offers (conflicts with Laravel queue `jobs` table)
**RIGHT:** `job_offers` table

### 2. Sanctum Installation
**WRONG:** Assuming Sanctum is installed by default in Laravel 13
**RIGHT:** Run `composer require laravel/sanctum` first, then `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`

### 3. API Routes Registration
**WRONG:** Creating `routes/api.php` without registering it in `bootstrap/app.php`
**RIGHT:** Add `api: __DIR__.'/../routes/api.php'` to `withRouting()` in `bootstrap/app.php`

### 4. Soft Deletes vs Status
**WRONG:** Using `status = 'archived'` AND soft deletes for the same purpose
**RIGHT:** `status` = logical state (active/archived), `deleted_at` = physical soft delete. They are independent.

### 5. Matching Score Calculation
**WRONG:** Calculating score on every request (expensive, requires PDF parsing)
**RIGHT:** Calculate once at upload time, store in `matching_score` column

### 6. File Storage in Tests
**WRONG:** Writing real files during tests
**RIGHT:** Use `Storage::fake('public')` in every test that involves file uploads

### 7. Role as Editable Field
**WRONG:** Allowing users to change their role after registration
**RIGHT:** Role set at registration, NOT updatable. Add `role` to `$hidden` or exclude from update validation.

### 8. Missing Authorization
**WRONG:** Only checking `auth()->check()` (authentication)
**RIGHT:** Also check ownership (authorization) — `$jobOffer->recruiter_id === auth()->id()`

### 9. Migration Order
**WRONG:** Creating `applications` table before `job_offers` (foreign key reference)
**RIGHT:** Create parent tables first (users → job_offers → applications → interviews → badges)

### 10. Test Database
**WRONG:** Running tests against MySQL (slow, fragile)
**RIGHT:** Tests use SQLite `:memory:` (configured in phpunit.xml)

### 11. Queue in Tests
**WRONG:** Queue jobs actually running during tests
**RIGHT:** `QUEUE_CONNECTION=sync` in phpunit.xml (jobs run immediately, synchronously)

### 12. API Response Format
**WRING:** Returning raw Eloquent models
**RIGHT:** Always use API Resources for consistent JSON structure

### 13. Password Confirmation
**WRONG:** Accepting `password` without `password_confirmation`
**RIGHT:** Laravel validation `confirmed` rule requires both fields to match

### 14. CV PDF Parsing
**WRONG:** Assuming all PDFs are text-based (scanned PDFs have no text)
**RIGHT:** Handle edge case — if text extraction returns empty, default score to 0

### 15. Soft-Deleted Records
**WRONG:** Not filtering soft-deleted records in queries
**RIGHT:** Soft-deleted records are automatically excluded by default, but be explicit when needed: `withTrashed()`

---

## Implementation Order (Step by Step)

Follow this EXACT order to avoid dependency issues:

```
STEP 1:  composer require laravel/sanctum
STEP 2:  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
STEP 3:  Create users migration modification (add role, avatar columns)
STEP 4:  Update User model (HasApiTokens, role, avatar, relationships)
STEP 5:  Create job_offers migration + model + factory
STEP 6:  Create applications migration + model + factory
STEP 7:  Create interviews migration + model + factory
STEP 8:  Create badges migration + model + factory
STEP 9:  php artisan migrate
STEP 10: Create Form Requests (Register, Login, Store, Update)
STEP 11: Create API Resources (User, JobOffer, Application, Interview, Badge)
STEP 12: Create Policies (JobOfferPolicy, ApplicationPolicy)
STEP 13: Create Middleware (EnsureUserHasRole)
STEP 14: Register middleware + API routes in bootstrap/app.php
STEP 15: Create AuthController (register, login, logout, profile)
STEP 16: Create JobOfferController (CRUD)
STEP 17: Create MatchingService + CalculateMatchingScoreJob
STEP 18: Create ApplicationController (apply, status, notes)
STEP 19: Create InterviewController (store, complete, cancel)
STEP 20: Create DashboardController (stats)
STEP 21: Create BadgeService + observers
STEP 22: Create ApplicationStatusMail (Mailable)
STEP 23: Write Feature tests (Auth, JobOffer, Application, Interview)
STEP 24: Write Unit tests (MatchingService, BadgeService)
STEP 25: Create Blade views (dashboard, job-offers, applications/kanban, auth)
STEP 26: Create Dockerfile + docker-compose.yml
STEP 27: Create .github/workflows/tests.yml
STEP 28: Create README.md with installation instructions
STEP 29: Generate MCD/MLD/Architecture diagrams
STEP 30: Deploy to Railway/Render
```

---

## Useful Commands

```bash
# Install dependencies
composer install
npm install

# Run dev server (concurrent: server + queue + pail + vite)
npm run dev

# Run tests
php artisan test

# Run Pest (if migrating)
./vendor/bin/pest

# Format code
./vendor/bin/pint

# Docker
docker-compose up -d

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed   # Reset and reseed

# Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# Storage
php artisan storage:link

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## Key Files Reference

| Purpose | Location |
|---|---|
| Routes (web) | `routes/web.php` |
| Routes (API) | `routes/api.php` |
| Bootstrap | `bootstrap/app.php` |
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/Api/` |
| Form Requests | `app/Http/Requests/` |
| API Resources | `app/Http/Resources/` |
| Middleware | `app/Http/Middleware/` |
| Services | `app/Services/` |
| Jobs | `app/Jobs/` |
| Mail | `app/Mail/` |
| Observers | `app/Observers/` |
| Policies | `app/Policies/` |
| Factories | `database/factories/` |
| Migrations | `database/migrations/` |
| Tests (Feature) | `tests/Feature/` |
| Tests (Unit) | `tests/Unit/` |
| Config | `config/` |
| .env | `.env` (never commit) |
| PHPUnit | `phpunit.xml` |
| Jira Board | https://gamecafemanager.atlassian.net/browse/SR |

---

## Evaluation Criteria (Detailed)

| Criterion | Weight | What They Look For |
|---|---|---|
| Laravel Architecture | 35% | Policies, FormRequests, Resources, Soft deletes, Services, Jobs, Observers, proper Eloquent usage, clean controller logic |
| Functionalities | 25% | Pipeline Kanban working, applications CRUD, interviews with scoring, matching algorithm producing correct scores, badges auto-awarding, dashboard stats |
| Presentation | 20% | Clear slides, live demo without bugs, MCD/MLD diagrams correct, architecture diagram clear, can explain every decision |
| Organization & Process | 20% | 25+ clean git commits, Jira board up to date, feature branches, clean commit messages, code formatted with Pint |

---

## Presentation Tips (Soutenance)

1. **Demo flow:** Register → Create job → Apply → View matching score → Move in Kanban → Schedule interview → Score interview → Show badges → Show dashboard
2. **Explain the AI:** "I built a keyword-matching algorithm in PHP that parses the CV and compares it to the job's tech stack. It runs as a queued job for performance. No external API, 100% controllable."
3. **Show the code:** Have `MatchingService.php`, a Policy, and a FormRequest ready to show
4. **Mention trade-offs:** "I chose comma-separated keywords for tech_stack to keep it simple. In production, I'd use a pivot table with a `technologies` table."
5. **Docker demo:** `docker-compose up -d` → app running, show it works
6. **CI demo:** Push a commit, show the green check on GitHub
