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
    <meta property="og:image" content="{{ asset('logo.svg') }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>@yield('title', 'SmartRecruit') — {{ config('app.name', 'SmartRecruit') }}</title>
    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $layoutRole = auth()->user()?->role
        ?? (request()->is('dashboard') || request()->is('recruiter*') ? 'recruiter'
        : (request()->is('my-applications') || request()->is('apply*') ? 'candidate' : null));
@endphp
<body class="min-h-screen bg-canvas text-dark antialiased" data-role="{{ $layoutRole }}" @yield('body_attrs')>
<a href="#app-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-pill focus:bg-secondary focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">Skip to main content</a>

<div class="min-h-screen lg:grid lg:grid-cols-[264px_minmax(0,1fr)]">
    {{-- Sidebar --}}
    <aside class="hidden h-screen flex-col border-r border-line bg-white/70 backdrop-blur-md lg:sticky lg:top-0 lg:flex">
        <a class="flex items-center gap-2.5 px-6 py-5" href="/dashboard" aria-label="SmartRecruit home">
            <img src="/favicon.svg" class="size-9 rounded-[12px]" alt="SmartRecruit logo" width="36" height="36">
            <span class="text-lg font-semibold tracking-tight">SmartRecruit</span>
        </a>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-4" aria-label="Main navigation">
            <div data-nav="recruiter" @if ($layoutRole !== 'recruiter') style="display:none" @endif>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Overview</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('dashboard') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('dashboard') }}">
                    <x-icon name="dashboard" :size="17" /> Dashboard
                </a>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Recruitment</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruiter/jobs*') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.jobs') }}">
                    <x-icon name="briefcase" :size="17" /> Job offers
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruiter/applications*') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.applications') }}">
                    <x-icon name="users" :size="17" /> Applications
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruiter/interviews') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.interviews') }}">
                    <x-icon name="calendar" :size="17" /> Interviews
                </a>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Tools</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruiter/filters') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.filters') }}">
                    <x-icon name="filter" :size="17" /> Saved filters
                </a>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('recruiter/templates') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('recruiter.templates') }}">
                    <x-icon name="template" :size="17" /> Reply templates
                </a>
            </div>
            <div data-nav="candidate" @if ($layoutRole !== 'candidate') style="display:none" @endif>
                <div class="px-3 pb-2 pt-1 text-[0.6875rem] font-semibold uppercase tracking-wider text-body">Candidate</div>
                <a class="group mb-1 flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('my-applications') ? 'bg-accent/20 font-semibold text-dark' : 'text-body hover:bg-surface hover:text-dark' }}" href="{{ route('candidate.applications') }}">
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
                <img src="/favicon.svg" class="size-8 rounded-[10px]" alt="SmartRecruit logo" width="32" height="32">
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

{{-- SmartRecruit AI Chat — floating support widget (Facebook-style popup). Recruiters only: the assistant can also read candidate profiles. --}}
@if ($layoutRole === 'recruiter')
<div class="sr-chat" id="srChat">
    <button class="sr-chat-launcher" id="srChatLauncher" type="button" aria-label="Ouvrir SmartRecruit AI Chat" aria-expanded="false">
        <span class="sr-chat-launcher-ic"><x-icon name="sparkles" :size="20" /></span>
        <span class="sr-chat-launcher-lbl">AI Chat</span>
    </button>

    <div class="sr-chat-panel" id="srChatPanel" role="dialog" aria-label="SmartRecruit AI Chat" aria-hidden="true" hidden>
        <div class="sr-chat-head">
            <div class="sr-chat-head-avatar"><x-icon name="sparkles" :size="16" /></div>
            <div class="sr-chat-head-txt">
                <div class="sr-chat-head-title">SmartRecruit AI Chat</div>
                <div class="sr-chat-head-sub"><span class="sr-chat-dot"></span> En ligne — assistance &amp; FAQ</div>
            </div>
            <button class="sr-chat-close" id="srChatClose" type="button" aria-label="Fermer le chat">
                <x-icon name="x" :size="18" />
            </button>
        </div>
        <div class="sr-chat-body" id="srChatThread"></div>
        <form class="sr-chat-form" id="srChatForm">
            <input class="sr-chat-input" id="srChatInput" type="text" placeholder="Écrivez votre message…" autocomplete="off" aria-label="Votre message">
            <button class="sr-chat-send" id="srChatSend" type="submit" aria-label="Envoyer">
                <x-icon name="send" :size="17" />
            </button>
        </form>
    </div>
</div>
@endif
<script>
    window.SR_BOOT = {
        user: null,
        apiBase: '/api'
    };
</script>
@yield('scripts')
</body>
</html>