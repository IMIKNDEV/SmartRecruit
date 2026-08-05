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
<body data-role="{{ $layoutRole }}" @yield('body_attrs')>
<a href="#app-main-content" class="skip-link">Aller au contenu principal</a>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="/dashboard">
            <span class="brand-mark">SR</span>
            SmartRecruit
        </a>

        <nav class="side-nav" aria-label="Navigation principale">
            <div data-nav="recruiter" @if ($layoutRole !== 'recruiter') style="display:none" @endif>
                <div class="side-section-label">Pilotage</div>
                <a class="side-nav-item {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <x-icon name="dashboard" /> Tableau de bord
                </a>
                <div class="side-section-label">Recrutement</div>
                <a class="side-nav-item {{ request()->is('recruteur/offres*') ? 'active' : '' }}" href="{{ route('recruiter.jobs') }}">
                    <x-icon name="briefcase" /> Offres d'emploi
                </a>
                <a class="side-nav-item {{ request()->is('recruteur/candidatures*') ? 'active' : '' }}" href="{{ route('recruiter.applications') }}">
                    <x-icon name="users" /> Candidatures
                </a>
                <a class="side-nav-item {{ request()->is('recruteur/entretiens') ? 'active' : '' }}" href="{{ route('recruiter.interviews') }}">
                    <x-icon name="calendar" /> Entretiens
                </a>
                <div class="side-section-label">Outils</div>
                <a class="side-nav-item {{ request()->is('recruteur/agent') ? 'active' : '' }}" href="{{ route('recruiter.agent') }}">
                    <x-icon name="sparkles" /> Agent IA
                </a>
                <a class="side-nav-item {{ request()->is('recruteur/filtres') ? 'active' : '' }}" href="{{ route('recruiter.filters') }}">
                    <x-icon name="filter" /> Filtres sauvegardés
                </a>
                <a class="side-nav-item {{ request()->is('recruteur/modeles') ? 'active' : '' }}" href="{{ route('recruiter.templates') }}">
                    <x-icon name="template" /> Modèles de réponse
                </a>
            </div>
            <div data-nav="candidate" @if ($layoutRole !== 'candidate') style="display:none" @endif>
                <div class="side-section-label">Candidat</div>
                <a class="side-nav-item {{ request()->is('mes-candidatures') ? 'active' : '' }}" href="{{ route('candidate.applications') }}">
                    <x-icon name="briefcase" /> Mes candidatures
                </a>
                <a class="side-nav-item" href="{{ route('jobs.index') }}">
                    <x-icon name="search" /> Explorer les offres
                </a>
            </div>
        </nav>

        <div class="sidebar-foot">
            <a class="side-nav-item" href="{{ route('profile') }}">
                <x-icon name="user" /> Mon profil
            </a>
            <a class="side-nav-item" href="#" onclick="SR.auth.logout(event)">
                <x-icon name="logout" /> Déconnexion
            </a>
            <div class="side-user">
                <span class="avatar" id="sidebarAvatar">U</span>
                <div>
                    <div class="name" id="sidebarName">Utilisateur</div>
                    <div class="role" id="sidebarRole">{{ $layoutRole === 'recruiter' ? 'Recruteur' : 'Candidat' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <form class="topbar-search" id="globalSearch" onsubmit="return false;">
                <x-icon name="search" :size="16" />
                <input type="text" placeholder="Rechercher une offre, un candidat…" id="globalSearchInput">
            </form>
            <div class="topbar-right">
                <button class="icon-btn" type="button" title="Notifications">
                    <x-icon name="mail" :size="18" />
                </button>
                <a class="avatar" style="text-decoration:none" href="{{ route('profile') }}" title="Mon profil" id="topbarAvatar">U</a>
            </div>
        </header>

        <main class="app-content" id="app-main-content">
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
