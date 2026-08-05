<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Explore open positions on SmartRecruit — simplified applications with AI scoring.">
    <meta property="og:title" content="@yield('title', 'Job offers') | SmartRecruit">
    <meta property="og:description" content="Explore open positions on SmartRecruit — simplified applications with AI scoring.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%236ebbff'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='700' font-size='14'%3ESR%3C/text%3E%3C/svg%3E">
    <title>@yield('title', 'Job offers') | {{ config('app.name', 'SmartRecruit') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="lp-page" @yield('body_attrs')>
<a href="#lp-main-content" class="skip-link">Aller au contenu principal</a>
<nav class="lp-nav" aria-label="Navigation principale">
    <div class="container lp-nav-inner">
        <a class="brand" href="/">
            <span class="brand-mark">SR</span>
            <span>SmartRecruit</span>
        </a>
        <nav class="lp-nav-links" aria-label="Navigation secondaire">
            <a class="nav-textlink" href="{{ route('jobs.index') }}">Offres d'emploi</a>
            <a class="nav-textlink" href="{{ route('login') }}">Se connecter</a>
            <a class="btn btn-primary btn-pill lp-nav-cta" href="{{ route('register') }}">Commencer</a>
        </nav>
    </div>
</nav>

<main class="lp-main" id="lp-main-content">
    @yield('content')
</main>

<footer class="lp-footer">
    <div class="container lp-footer-inner">
        <a class="brand" href="/">
            <span class="brand-mark sm">SR</span>
            <span>SmartRecruit</span>
        </a>
        <p class="lp-copy">Plateforme de recrutement intelligent, &copy; {{ date('Y') }}</p>
    </div>
</footer>

<div class="toast-container" id="toasts"></div>
@yield('scripts')
</body>
</html>
