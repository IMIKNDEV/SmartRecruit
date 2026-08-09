@extends('layouts.guest')

@section('title', 'Hire for growth')

@section('body_attrs') data-page="landing" @endsection

@section('content')
{{-- ============================================================
     HERO — centered copy + interactive Kanban demo (Teamtailor)
============================================================ --}}
<section class="hero-bg grain relative overflow-hidden pb-16 pt-16 sm:pt-20" aria-label="Introduction">
    <div class="hero-atmosphere" aria-hidden="true">
        <div class="hero-blob hero-blob-pink"></div>
        <div class="hero-blob hero-blob-purple"></div>
        <div class="hero-dots"></div>
    </div>
    <div class="relative z-10 mx-auto max-w-6xl px-6 text-center">
        <span data-reveal class="inline-flex items-center gap-2 rounded-pill border border-line bg-white px-4 py-1.5 text-xs font-semibold tracking-wide text-body">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9Z"/></svg>
            AI-powered candidate matching
        </span>

        <h1 data-reveal class="hero-title heading-display mx-auto max-w-4xl">
            Hire for <span class="pink-word">growth.</span>
        </h1>

        <p data-reveal class="mx-auto mt-4 max-w-2xl text-lg font-medium text-body sm:text-xl">
            The ATS loved by candidates and recruiters — with <span class="highlight">AI at the core</span>.
        </p>

        <div data-reveal class="hero-cta flex flex-wrap items-center justify-center gap-3">
            <x-btn href="{{ route('register') }}" size="lg">Get started</x-btn>
            <x-btn href="{{ route('jobs.index') }}" variant="ghost" size="lg">Explore jobs</x-btn>
        </div>
        <p data-reveal class="mt-4 text-xs font-medium text-body/70">
            Free to start &middot; recruiter account ready in 1 minute &middot; no credit card
        </p>
    </div>

    {{-- Interactive Kanban demo board --}}
    <div data-reveal class="relative z-10 mx-auto mt-14 max-w-6xl px-6">
        <div class="mockup-shadow overflow-hidden rounded-[32px] border border-line bg-white p-4 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 place-items-center rounded-[12px] bg-accent/20 text-sm font-bold text-textaccent">TA</span>
                    <div>
                        <p class="text-sm font-semibold leading-tight">Talent Acquisition Specialist — Agadir</p>
                        <p class="text-xs font-medium text-body/70">Senior Recruiter · CDI</p>
                    </div>
                </div>
                <span class="rounded-pill bg-surface px-3.5 py-1.5 text-xs font-semibold text-body">
                    <span id="demoHints" class="mono">48 candidates</span>
                </span>
            </div>

            <div id="heroKanban" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Interactive Kanban demo — drag cards between stages">
                {{-- Columns are rendered by the demo script so drag & drop works right away --}}
            </div>

            <p class="mt-4 flex items-center justify-center gap-2 text-center text-xs font-medium text-body/70">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                Live demo — drag any card between stages, click a card to review it
            </p>
        </div>
    </div>
</section>

{{-- ============================================================
     TRUST STRIP — scrolling recruitment-workflow marquee
============================================================ --}}
<section class="border-y border-line bg-surface/60 py-6" aria-label="Recruitment workflows">
    <div class="marquee overflow-hidden" aria-hidden="true">
        <div class="marquee-track flex items-center gap-3">
            @php $stack = ['Sourcing', 'Applicant Tracking', 'Talent Pipeline', 'Resume Parsing', 'Interview Scheduling', 'Candidate Scoring', 'Recruiter CRM', 'Job Board Posting', 'Boolean Search', 'Employer Branding', 'Offer Management', 'Reference Checks', 'Onboarding', 'Compliance', 'Talent Analytics', 'Diversity Hiring', 'Workflow Automation', 'Candidate Experience']; @endphp
            @for ($i = 0; $i < 2; $i++)
                @foreach ($stack as $tech)
                    <span class="flex shrink-0 items-center gap-2 rounded-pill border border-line bg-white px-5 py-2 text-sm font-semibold text-body">
                        <span class="size-2 rounded-full bg-accent"></span>{{ $tech }}
                    </span>
                @endforeach
            @endfor
        </div>
    </div>
</section>

{{-- ============================================================
     STATS
============================================================ --}}
<section class="bg-canvas py-16" aria-label="SmartRecruit in numbers">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-6 lg:grid-cols-4">
        <div data-reveal class="rounded-card border border-line bg-white p-6">
            <p class="mono text-4xl font-semibold tracking-tight text-dark">24<span class="text-lg text-body/60">/offer</span></p>
            <p class="mt-2 text-sm font-medium text-body">candidates tracked on average for every published role</p>
        </div>
        <div data-reveal class="rounded-card border border-line bg-white p-6">
            <p class="mono text-4xl font-semibold tracking-tight text-dark">9.4<span class="text-lg text-body/60"> days</span></p>
            <p class="mt-2 text-sm font-medium text-body">average time from offer to hire, tracked on your dashboard</p>
        </div>
        <div data-reveal class="rounded-card border border-line bg-white p-6">
            <p class="mono text-4xl font-semibold tracking-tight text-dark">82<span class="text-lg text-body/60">/100</span></p>
            <p class="mt-2 text-sm font-medium text-body">average AI compatibility score of profiles you move forward</p>
        </div>
        <div data-reveal class="rounded-card border border-line bg-white p-6">
            <p class="mono text-4xl font-semibold tracking-tight text-dark">100<span class="text-lg text-body/60">%</span></p>
            <p class="mt-2 text-sm font-medium text-body">transparent scoring — see the matched and missing keywords</p>
        </div>
    </div>
</section>

{{-- ============================================================
     FEATURES — asymmetric zig-zag
============================================================ --}}
<section class="bg-canvas pb-20" aria-label="Features">
    <div class="mx-auto max-w-6xl px-6">
        <div data-reveal class="mx-auto mb-14 max-w-2xl text-center">
            <span class="text-xs font-bold uppercase tracking-[0.18em] text-textaccent">The recruiter's cockpit</span>
            <h2 class="heading-section mt-3">Everything about your hiring, <span class="text-secondary">in one place</span></h2>
            <p class="mt-4 text-lg font-medium text-body">From publishing an offer to the final decision — visual, measurable, and built for speed.</p>
        </div>

        {{-- Feature 1: pipeline --}}
        <article data-reveal class="grid items-center gap-10 lg:grid-cols-2">
            <div>
                <span class="mb-4 inline-grid size-12 place-items-center rounded-[16px] bg-accent/20 text-textaccent"><x-icon name="pipeline" /></span>
                <h3 class="text-2xl font-semibold tracking-tight">A visual pipeline, from first CV to decision</h3>
                <p class="mt-3 text-body">Every application follows a clear path — received, interview, accepted or refused. Drag cards between columns, add quick tags and process several files in one single gesture.</p>
                <a href="{{ route('jobs.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-textaccent hover:underline">Explore job offers
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div aria-hidden="true" class="rounded-[32px] bg-section-pink p-8">
                <div class="grid grid-cols-4 gap-2">
                    <div class="rounded-[18px] bg-white/90 p-3">
                        <span class="block size-2 rounded-full bg-accent"></span>
                        <p class="mt-2 text-[11px] font-semibold text-body">Received</p>
                        <p class="mono text-xl font-semibold">24</p>
                    </div>
                    <div class="rounded-[18px] bg-white/90 p-3">
                        <span class="block size-2 rounded-full bg-secondary/70"></span>
                        <p class="mt-2 text-[11px] font-semibold text-body">Interview</p>
                        <p class="mono text-xl font-semibold">8</p>
                    </div>
                    <div class="rounded-[18px] bg-white/90 p-3">
                        <span class="block size-2 rounded-full bg-ok"></span>
                        <p class="mt-2 text-[11px] font-semibold text-body">Accepted</p>
                        <p class="mono text-xl font-semibold">3</p>
                    </div>
                    <div class="rounded-[18px] bg-white/90 p-3">
                        <span class="block size-2 rounded-full bg-section-orange"></span>
                        <p class="mt-2 text-[11px] font-semibold text-body">Refused</p>
                        <p class="mono text-xl font-semibold">13</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-pill bg-white/90 px-5 py-3">
                    <span class="text-xs font-semibold text-body">Conversion</span>
                    <span class="mono text-sm font-semibold text-ok">24 → 8 → 3</span>
                </div>
            </div>
        </article>

        {{-- Feature 2: AI score --}}
        <article data-reveal class="mt-20 grid items-center gap-10 lg:grid-cols-2">
            <div aria-hidden="true" class="rounded-[32px] bg-section-purple p-8 lg:order-1">
                <div class="mx-auto w-fit rounded-[28px] bg-white p-6 text-center shadow-tint">
                    <div class="score-ring-wrap" style="margin:0 auto 12px">
                        <svg class="score-ring" viewBox="0 0 54 54" width="100%" height="100%">
                            <circle class="score-ring-track" cx="27" cy="27" r="24"></circle>
                            <circle class="score-ring-fill" cx="27" cy="27" r="24" stroke="#16a34a" stroke-dasharray="150.8" stroke-dashoffset="12.1"></circle>
                        </svg>
                        <span class="score-label">92</span>
                    </div>
                    <p class="text-sm font-semibold text-body">Profile match · DevOps Engineer</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                        <span class="rounded-pill bg-ok-bg px-3 py-1 text-[11px] font-semibold text-ok">Docker</span>
                        <span class="rounded-pill bg-ok-bg px-3 py-1 text-[11px] font-semibold text-ok">Kubernetes</span>
                        <span class="rounded-pill bg-ok-bg px-3 py-1 text-[11px] font-semibold text-ok">CI/CD</span>
                        <span class="rounded-pill bg-danger-bg px-3 py-1 text-[11px] font-semibold text-danger">Terraform</span>
                    </div>
                    <p class="mt-3 text-[11px] font-medium text-body/70">Found: Docker, Kubernetes, CI/CD · Missing: Terraform</p>
                </div>
            </div>
            <div class="lg:order-2">
                <span class="mb-4 inline-grid size-12 place-items-center rounded-[16px] bg-secondary/10 text-secondary"><x-icon name="target" /></span>
                <h3 class="text-2xl font-semibold tracking-tight">A full profile analysis, powered by AI</h3>
                <p class="mt-3 text-body">At every CV upload, the engine studies the whole profile — experience, skills, education, background — and returns a compatibility score from 0 to 100 with a transparent breakdown of strengths and gaps. You see the why, not just the how much.</p>
                <ul class="mt-5 space-y-2.5 text-sm font-medium text-body">
                    <li class="flex items-center gap-2.5"><span class="grid size-5 place-items-center rounded-full bg-ok-bg text-ok">✓</span>Runs as a background job — instant 201, no waiting</li>
                    <li class="flex items-center gap-2.5"><span class="grid size-5 place-items-center rounded-full bg-ok-bg text-ok">✓</span>Full transparency for the recruiter and the candidate</li>
                    <li class="flex items-center gap-2.5"><span class="grid size-5 place-items-center rounded-full bg-ok-bg text-ok">✓</span>Interview questions generated from the same stack</li>
                </ul>
            </div>
        </article>

        {{-- Feature 3: productivity tools --}}
        <article data-reveal class="mt-20 grid items-center gap-10 lg:grid-cols-2">
            <div>
                <span class="mb-4 inline-grid size-12 place-items-center rounded-[16px] bg-section-yellow/70 text-dark"><x-icon name="sparkles" /></span>
                <h3 class="text-2xl font-semibold tracking-tight">Productivity tools that scale with you</h3>
                <p class="mt-3 text-body">Batch actions, saved filters, side-by-side comparison and top-5 shortlists — every repetitive task has been removed so you can focus on the candidates.</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-pill border border-line bg-white px-4 py-2 text-xs font-semibold text-body"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>Batch status updates</span>
                    <span class="inline-flex items-center gap-1.5 rounded-pill border border-line bg-white px-4 py-2 text-xs font-semibold text-body"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>Saved filters</span>
                    <span class="inline-flex items-center gap-1.5 rounded-pill border border-line bg-white px-4 py-2 text-xs font-semibold text-body"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>Compare 2-4 profiles</span>
                    <span class="inline-flex items-center gap-1.5 rounded-pill border border-line bg-white px-4 py-2 text-xs font-semibold text-body"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Top-5 shortlist + export</span>
                </div>
            </div>
            <div aria-hidden="true" class="rounded-[32px] bg-section-green p-8">
                <div class="rounded-[24px] bg-white/95 p-5 shadow-tint">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold">Shortlist — TA Specialist</p>
                        <span class="rounded-pill bg-accent/20 px-3 py-1 text-[11px] font-bold text-textaccent">CSV · PDF</span>
                    </div>
                    @php $short = [['Sara El Amrani', 92, 'ok', 'bg-accent/20 text-textaccent'], ['Amine Tazi', 84, 'ok', 'bg-secondary/15 text-secondary'], ['Youssef Benali', 78, 'ok', 'bg-ok/15 text-ok'], ['Kenza Idrissi', 65, 'warn', 'bg-section-yellow/60 text-dark']]; @endphp
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($short as $idx => $row)
                            <li class="flex items-center gap-3 rounded-[16px] border border-line bg-white px-3.5 py-2.5">
                                <span class="mono text-xs font-semibold text-body/60">#{{ $idx + 1 }}</span>
                                <span class="grid size-8 shrink-0 place-items-center rounded-full text-sm font-bold {{ $row[3] }}">{{ substr($row[0], 0, 1) }}</span>
                                <span class="flex-1 truncate text-sm font-semibold">{{ $row[0] }}</span>
                                <span class="mono text-sm font-semibold {{ $row[2] === 'ok' ? 'text-ok' : 'text-warn' }}">{{ $row[1] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </article>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS
============================================================ --}}
<section class="border-y border-line bg-surface/60 py-20" aria-label="How it works">
    <div class="mx-auto max-w-6xl px-6">
        <div data-reveal class="mx-auto mb-12 max-w-2xl text-center">
            <span class="text-xs font-bold uppercase tracking-[0.18em] text-textaccent">How it works</span>
            <h2 class="heading-section mt-3">From application to hire in three steps</h2>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <div data-reveal class="rounded-[28px] bg-white p-7 shadow-soft">
                <span class="grid size-12 place-items-center rounded-full bg-accent text-lg font-bold text-dark">1</span>
                <h3 class="mt-5 text-xl font-semibold tracking-tight">Publish your offer</h3>
                <p class="mt-2 text-sm font-medium leading-relaxed text-body">Define the title, description, tech stack and deadline. Your job offer goes live instantly and candidates apply with a CV and cover letter.</p>
            </div>
            <div data-reveal class="rounded-[28px] bg-white p-7 shadow-soft">
                <span class="grid size-12 place-items-center rounded-full bg-section-yellow text-lg font-bold text-dark">2</span>
                <h3 class="mt-5 text-xl font-semibold tracking-tight">Screen with AI</h3>
                <p class="mt-2 text-sm font-medium leading-relaxed text-body">Each CV is scored against the required stack. Review matched and missing keywords, add notes and tags, and shortlist the strongest profiles.</p>
            </div>
            <div data-reveal class="rounded-[28px] bg-white p-7 shadow-soft">
                <span class="grid size-12 place-items-center rounded-full bg-secondary text-lg font-bold text-white">3</span>
                <h3 class="mt-5 text-xl font-semibold tracking-tight">Interview & decide</h3>
                <p class="mt-2 text-sm font-medium leading-relaxed text-body">Schedule scored interviews, generate tailored questions, then move cards to accepted or refused. Candidates are notified automatically.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CTA BAND
============================================================ --}}
<section class="bg-canvas py-20" aria-label="Get started">
    <div data-reveal class="mx-auto max-w-6xl px-6">
        <div class="grain relative overflow-hidden rounded-[36px] bg-secondary px-8 py-16 text-center text-white sm:px-16">
            <div class="relative z-10">
                <h2 class="heading-section text-white">Ready to hire for growth?</h2>
                <p class="mx-auto mt-3 max-w-xl text-lg font-medium text-white/85">Join recruiters who ditched the spreadsheets. Set up your pipeline and score your first candidates in minutes.</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="rounded-pill bg-white px-8 py-3.5 text-sm font-bold text-secondary shadow-pop transition-transform hover:-translate-y-0.5">Create your free account</a>
                    <a href="{{ route('jobs.index') }}" class="rounded-pill border border-white/40 px-8 py-3.5 text-sm font-bold text-white transition-colors hover:bg-white/10">Browse open jobs</a>
                </div>
                <p class="mt-5 text-xs font-medium text-white/70">No credit card · 1-minute signup · Works on every device</p>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
  /* ============================================================
     Landing page — scroll reveals + interactive Kanban demo
     Uses only SR.* helpers (toast, modal, kanban) from app.js.
  ============================================================ */
  (function () {
    'use strict';

    /* ---- Demo data: 12 sample candidates ---- */
    var DEMO_APPS = [      { id: 1,  name: 'Sara El Amrani', role: 'Product Manager',        status: 'interview', score: 92, matched: ['Roadmap', 'Stakeholders', 'Sprint planning'], missing: ['Analytics'], tag: 'Prioritaire' },
      { id: 2,  name: 'Amine Tazi',     role: 'DevOps Engineer',        status: 'interview', score: 88, matched: ['Docker', 'Kubernetes'],     missing: ['CI/CD', 'AWS'], tag: '' },
      { id: 3,  name: 'Nadia Bouhlel',  role: 'UI Designer',            status: 'interview', score: 81, matched: ['Figma', 'Prototyping'],    missing: ['Design systems', 'UX research'], tag: 'Interview planned' },
      { id: 4,  name: 'Mehdi Alaoui',   role: 'Backend Developer',      status: 'interview', score: 76, matched: ['PHP'],                     missing: ['Laravel', 'MySQL', 'API design'], tag: '' },
      { id: 5,  name: 'Youssef Benali', role: 'Frontend Developer',     status: 'received',  score: 71, matched: ['React', 'TypeScript'],    missing: ['Testing', 'CSS'], tag: 'To follow up' },
      { id: 6,  name: 'Kenza Idrissi',  role: 'Data Analyst',           status: 'received',  score: 64, matched: ['SQL'],                    missing: ['Python', 'Power BI', 'Statistics'], tag: '' },
      { id: 7,  name: 'Omar Chraibi',   role: 'QA Engineer',            status: 'received',  score: 58, matched: ['Test automation'],       missing: ['Cypress', 'CI/CD', 'Load testing'], tag: '' },
      { id: 8,  name: 'Salma Berrada',  role: 'Security Engineer',      status: 'received',  score: 49, matched: [],                        missing: ['OWASP', 'Penetration testing', 'IAM', 'Auditing'], tag: '' },
      { id: 9,  name: 'Rania Fassi',    role: 'Software Architect',     status: 'accepted',  score: 95, matched: ['System design', 'Microservices', 'Cloud', 'Scalability'], missing: [], tag: 'Offer sent' },
      { id: 10, name: 'Hamza Ouazzani', role: 'Mobile Developer',       status: 'accepted', score: 83, matched: ['React Native'],           missing: ['Swift'], tag: 'Offer sent' },
      { id: 11, name: 'Ilias Mansouri', role: 'Scrum Master',           status: 'refused',   score: 41, matched: ['Agile'],                  missing: ['Facilitation', 'Retrospectives', 'Jira'], tag: '' },
      { id: 12, name: 'Douae Bennani',  role: 'UX Researcher',          status: 'refused',   score: 37, matched: [],                        missing: ['Interviews', 'Usability testing', 'Personas', 'Surveys'], tag: '' },
    ];

    var COLUMNS = [
      { status: 'received',  label: 'Received',  dot: 'bg-accent',        cardBg: 'bg-white' },
      { status: 'interview', label: 'Interview', dot: 'bg-secondary/70',  cardBg: 'bg-white' },
      { status: 'accepted',  label: 'Accepted',  dot: 'bg-ok',            cardBg: 'bg-white' },
      { status: 'refused',   label: 'Refused',   dot: 'bg-section-orange', cardBg: 'bg-white' },
    ];

    var board = document.getElementById('heroKanban');
    if (!board) return;

    /* ---- Build columns + cards ---- */
    function buildDemo() {
      var html = '';
      COLUMNS.forEach(function (col) {
        html += '<div class="kanban-col min-h-[120px] rounded-[22px] border border-line bg-surface/50 p-2.5 [&>.kanban-card+.kanban-card]:mt-2" data-col="' + col.status + '">' +
          '<div class="mb-2.5 flex items-center justify-between px-1">' +
            '<span class="flex items-center gap-2 text-xs font-bold text-dark"><span class="size-2.5 rounded-full ' + col.dot + '"></span>' + col.label + '</span>' +
            '<span class="kanban-count rounded-pill bg-white px-2.5 py-0.5 mono text-[11px] font-bold text-body">0</span>' +
          '</div></div>';
      });
      board.innerHTML = html;

      DEMO_APPS.forEach(function (c) {
        var colEl = board.querySelector('[data-col="' + c.status + '"]');
        if (!colEl) return;
        colEl.insertAdjacentHTML('beforeend', demoCard(c));
      });
      refreshCounts();
      SR.kanban.enable(board, {
        canDrop: function () { return true; },
        onOptimistic: refreshCounts,
        onDrop: function (card, fromStatus, toStatus, done) {
          // Demo: no persistence — always accept the move.
          refreshCounts();
          done(true);
        },
      });
      SR.kanban.markDraggable(board);
    }

    function demoCard(c) {
      var chips = '';
      c.matched.slice(0, 2).forEach(function (k) {
        chips += '<span class="rounded-pill bg-ok-bg px-2 py-0.5 text-[10px] font-bold text-ok">' + k + '</span>';
      });
      if (c.missing.length) {
        chips += '<span class="rounded-pill bg-danger-bg px-2 py-0.5 text-[10px] font-bold text-danger">-' + c.missing.length + '</span>';
      }
      return '<div class="kanban-card cursor-grab select-none rounded-[18px] border border-line bg-white p-3 shadow-soft active:cursor-grabbing" draggable="true" data-id="' + c.id + '" data-status="' + c.status + '" data-name="' + c.name + '" data-role="' + c.role + '" data-score="' + c.score + '" data-matched="' + c.matched.join('|') + '" data-missing="' + c.missing.join('|') + '" data-tag="' + c.tag + '">' +
        '<div class="flex items-center gap-2.5">' +
          '<span class="grid size-9 shrink-0 place-items-center rounded-[12px] bg-surface text-sm font-bold text-dark">' + c.name.charAt(0) + '</span>' +
          '<span class="min-w-0 flex-1">' +
            '<span class="block truncate text-[13px] font-bold leading-tight">' + c.name + '</span>' +
            '<span class="block truncate text-[11px] font-medium text-body/80">' + c.role + '</span>' +
          '</span>' +
          '<span class="score-ring-wrap sm"><svg class="score-ring" viewBox="0 0 54 54" width="100%" height="100%"><circle class="score-ring-track" cx="27" cy="27" r="24"></circle><circle class="score-ring-fill" cx="27" cy="27" r="24" stroke="' + (c.score >= 80 ? '#16a34a' : c.score >= 50 ? '#f5a623' : '#ef4444') + '" stroke-dasharray="' + (2 * Math.PI * 24) + '" stroke-dashoffset="' + (2 * Math.PI * 24 - (c.score / 100) * 2 * Math.PI * 24) + '"></circle></svg><span class="score-label">' + c.score + '</span></span>' +
        '</div>' +
        '<div class="mt-2.5 flex flex-wrap gap-1">' + chips + '</div>' +
      '</div>';
    }

    function refreshCounts() {
      if (!board) return;
      board.querySelectorAll('.kanban-col').forEach(function (col) {
        var n = col.querySelectorAll('.kanban-card').length;
        var countEl = col.querySelector('.kanban-count');
        if (countEl) countEl.textContent = n;
      });
      var total = board.querySelectorAll('.kanban-card').length;
      var hint = document.getElementById('demoHints');
      if (hint) hint.textContent = total + ' candidates';
    }

    /* ---- Click card → modal detail with demo Accept/Refuse pills ---- */
    function openDemoDetail(card) {
      var score = Number(card.dataset.score || 0);
      var matched = (card.dataset.matched || '').split('|').filter(Boolean);
      var missing = (card.dataset.missing || '').split('|').filter(Boolean);
      var html = '<div style="text-align:center;padding:6px 0 4px">' +
        '<span class="avatar" style="width:56px;height:56px;font-size:20px">' + card.dataset.name.charAt(0) + '</span>' +
        '<p class="mono" style="margin-top:12px;font-weight:700;font-size:17px">' + card.dataset.name + '</p>' +
        '<p style="margin-top:2px;font-size:13px;font-weight:500;color:var(--slate)">' + card.dataset.role + ' · Talent Acquisition Specialist — Agadir</p>' +
        '<div class="score-ring-wrap" style="margin:14px auto 0">' +
          '<svg class="score-ring" viewBox="0 0 54 54" width="100%" height="100%"><circle class="score-ring-track" cx="27" cy="27" r="24"></circle>' +
          '<circle class="score-ring-fill" cx="27" cy="27" r="24" stroke="' + (score >= 80 ? '#16a34a' : score >= 50 ? '#f5a623' : '#ef4444') + '" stroke-dasharray="' + (2 * Math.PI * 24) + '" stroke-dashoffset="' + (2 * Math.PI * 24 - (score / 100) * 2 * Math.PI * 24) + '"></circle></svg>' +
          '<span class="score-label">' + score + '</span></div>' +
        '<p style="margin-top:4px;font-size:11px;font-weight:600;color:var(--slate)">AI compatibility score</p>' +
        '<div class="kv" style="text-align:left;margin-top:16px">' +
          '<div class="kv-row"><span class="kv-k">Found</span><span>' + (matched.length ? matched.map(function (k) { return '<span class="tag tag-green">' + k + '</span>'; }).join(' ') : '<span style="color:var(--slate)">—</span>') + '</span></div>' +
          '<div class="kv-row"><span class="kv-k">Missing</span><span>' + (missing.length ? missing.map(function (k) { return '<span class="tag tag-red">' + k + '</span>'; }).join(' ') : '<span style="color:var(--slate)">—</span>') + '</span></div>' +
          (card.dataset.tag ? '<div class="kv-row"><span class="kv-k">Tag</span><span class="tag tag-navy">' + card.dataset.tag + '</span></div>' : '') +
        '</div></div>' +
        '<div style="display:flex;gap:10px;margin-top:18px">' +
          '<button class="btn btn-success btn-pill" style="flex:1" data-demo-action="accept">Accept</button>' +
          '<button class="btn btn-danger btn-pill" style="flex:1" data-demo-action="refuse">Refuse</button>' +
        '</div>';
      SR.modal.open(html, { title: 'Candidate review' });

      setTimeout(function () {
        var box = document.getElementById('modalBox');
        if (!box) return;
        box.querySelectorAll('[data-demo-action]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            SR.modal.close();
            SR.toast(btn.dataset.demoAction === 'accept'
              ? 'Demo — "Accept" would notify the candidate by email'
              : 'Demo — "Refuse" would suggest similar profiles', btn.dataset.demoAction === 'accept' ? 'success' : 'info');
          });
        });
      }, 0);
    }

    /* ---- Demo: needs SR.* (app.js is a deferred module) — wait for it ---- */
    function whenReady(fn) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
      } else {
        fn();
      }
    }

    whenReady(function () {
      board.addEventListener('click', function (e) {
        var card = e.target.closest('.kanban-card');
        if (card) openDemoDetail(card);
      });
      buildDemo();
    });
  })();
</script>
@endsection
