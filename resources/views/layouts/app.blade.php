<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SmartRecruit — AI-powered ATS with candidate scoring, a visual Kanban pipeline and productivity tools for recruiters.">
    <meta property="og:title" content="@yield('title', 'SmartRecruit') — SmartRecruit">
    <meta property="og:description" content="AI-powered ATS with candidate scoring, a visual Kanban pipeline and productivity tools for recruiters.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%236ebbff'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='700' font-size='14'%3ESR%3C/text%3E%3C/svg%3E">
    <title>@yield('title', 'SmartRecruit') — {{ config('app.name', 'SmartRecruit') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $layoutRole = auth()->user()?->role
        ?? (request()->is('dashboard') || request()->is('recruteur*') ? 'recruiter'
        : (request()->is('mes-candidatures') || request()->is('postuler*') ? 'candidate' : null));
@endphp
<body class="min-h-screen bg-canvas text-dark antialiased" data-role="{{ $layoutRole }}" @yield('body_attrs')>
<a href="#app-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-pill focus:bg-secondary focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">Skip to main content</a>

<div class="min-h-screen lg:grid lg:grid-cols-[264px_minmax(0,1fr)]">
    {{-- Sidebar --}}
    <aside class="hidden h-screen flex-col border-r border-line bg-white/70 backdrop-blur-md lg:sticky lg:top-0 lg:flex">
        <a class="flex items-center gap-2.5 px-6 py-5" href="/dashboard" aria-label="SmartRecruit home">
            <span class="grid size-9 place-items-center rounded-[14px] bg-accent text-sm font-bold text-dark">SR</span>
            <span class="text-lg font-semibold tracking-tight">SmartRecruit</span>
        </a>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-4" aria-label="Main navigation">
            <div data-nav="recruiter" @if ($layoutRole !== 'recruiter') style="display:none" @endif>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Overview</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('dashboard') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('dashboard') }}">
                    <x-icon name="dashboard" :size="17" /> Dashboard
                </a>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Recruitment</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/offres*') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.jobs') }}">
                    <x-icon name="briefcase" :size="17" /> Job offers
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/candidatures*') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.applications') }}">
                    <x-icon name="users" :size="17" /> Applications
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/entretiens') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.interviews') }}">
                    <x-icon name="calendar" :size="17" /> Interviews
                </a>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Tools</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/agent') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.agent') }}">
                    <x-icon name="sparkles" :size="17" /> AI Agent
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/filtres') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.filters') }}">
                    <x-icon name="filter" :size="17" /> Saved filters
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruteur/modeles') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.templates') }}">
                    <x-icon name="template" :size="17" /> Reply templates
                </a>
            </div>
            <div data-nav="candidate" @if ($layoutRole !== 'candidate') style="display:none" @endif>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Candidate</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('mes-candidatures') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('candidate.applications') }}">
                    <x-icon name="briefcase" :size="17" /> My applications
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors text-body hover:bg-surface hover:text-dark" href="{{ route('jobs.index') }}">
                    <x-icon name="search" :size="17" /> Explore jobs
                </a>
            </div>
        </nav>

        <div class="border-t border-line p-3">
            <a class="flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium text-body transition-colors hover:bg-surface hover:text-dark" href="{{ route('profile') }}">
                <x-icon name="user" :size="17" /> My profile
            </a>
            <a class="flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium text-body transition-colors hover:bg-surface hover:text-dark" href="#" onclick="SR.auth.logout(event)">
                <x-icon name="logout" :size="17" /> Log out
            </a>
            <div class="mt-2 flex items-center gap-3 rounded-[20px] bg-surface p-3">
                <span class="grid size-10 place-items-center rounded-full bg-accent font-bold text-dark" id="sidebarAvatar">U</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-dark" id="sidebarName">User</div>
                    <div class="truncate text-xs text-body" id="sidebarRole">{{ $layoutRole === 'recruiter' ? 'Recruiter' : 'Candidate' }}</div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="app-main min-w-0">
        <header class="sticky top-0 z-40 flex h-16 items-center justify-between gap-4 border-b border-line bg-canvas/85 px-6 backdrop-blur-md">
            <a class="flex items-center gap-2.5 lg:hidden" href="/" aria-label="SmartRecruit home">
                <span class="grid size-8 place-items-center rounded-[12px] bg-accent text-xs font-bold text-dark">SR</span>
            </a>
            <form class="hidden max-w-md flex-1 items-center gap-2.5 rounded-pill border border-line bg-white px-4 py-2.5 focus-within:border-accent focus-within:shadow-[0_0_0_4px_rgb(110_187_255/0.2)] sm:flex" id="globalSearch" onsubmit="return false;">
                <x-icon name="search" :size="16" />
                <input type="text" placeholder="Search jobs, candidates…" id="globalSearchInput" class="w-full bg-transparent text-sm text-dark outline-none placeholder:text-body">
            </form>
            <div class="flex items-center gap-3">
                <button class="grid size-10 place-items-center rounded-full border border-line bg-white text-body transition-colors hover:border-accent hover:text-dark" type="button" title="Notifications" aria-label="Notifications">
                    <x-icon name="mail" :size="18" />
                </button>
                <a class="grid size-10 place-items-center rounded-full bg-accent font-bold text-dark ring-2 ring-canvas transition-transform hover:-translate-y-0.5" style="text-decoration:none" href="{{ route('profile') }}" title="My profile" id="topbarAvatar" aria-label="My profile">U</a>
            </div>
        </header>

        <main class="app-content p-6 lg:p-10" id="app-main-content">
            @yield('content')
        </main>
    </div>
</div>

<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal" id="modalBox"></div>
</div>
<div class="toast-container" id="toasts"></div>
<script>
    window.SR_BOOT = {
        user: null,
        apiBase: '/api'
    };
</script>
@yield('scripts')
</body>
</html>