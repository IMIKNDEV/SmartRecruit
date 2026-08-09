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
    <meta property="og:image" content="{{ asset('logo.svg') }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>@yield('title', 'Job offers') | {{ config('app.name', 'SmartRecruit') }}</title>
    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-dark antialiased" @yield('body_attrs')>
<a href="#lp-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-pill focus:bg-secondary focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">Skip to main content</a>

<header class="sticky top-0 z-50 border-b border-line bg-canvas/85 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="SmartRecruit home">
            <img src="/favicon.svg" class="size-9 rounded-[12px]" alt="SmartRecruit logo" width="36" height="36">
            <span class="text-lg font-semibold tracking-tight">SmartRecruit</span>
        </a>
        <nav class="flex items-center gap-2 sm:gap-6" aria-label="Main navigation">
            <span id="srGuestNav" class="flex items-center gap-2 sm:gap-6">
                <a href="{{ route('login') }}" class="hidden text-sm font-medium text-body transition-colors hover:text-dark sm:block">Log in</a>
                <x-btn href="{{ route('register') }}" size="sm">Get started</x-btn>
            </span>
            <a href="{{ route('profile') }}" id="srAuthProfile" class="flex items-center gap-2 rounded-pill border border-line py-1 pl-1 pr-3 text-sm font-medium text-body transition-colors hover:text-dark" style="display:none">
                <span id="srAuthInitial" class="flex size-8 items-center justify-center rounded-full bg-secondary text-xs font-bold text-white">?</span>
                <span id="srAuthName" class="hidden sm:block">Account</span>
            </a>
        </nav>
    </div>
</header>
<script>
/* Auth lives client-side (Sanctum token in localStorage), so the header is
   updated based on the stored token/user instead of server-side auth().
   Keys mirror resources/js/app.js (TOKEN_KEY='sr_token', USER_KEY='sr_user'). */
(function () {
    var TOKEN = 'sr_token', USER = 'sr_user';
    var token = null, user = null;
    try { token = localStorage.getItem(TOKEN); } catch (e) {}
    try { user = JSON.parse(localStorage.getItem(USER) || 'null'); } catch (e) {}

    var el;
    var guest = document.getElementById('srGuestNav');
    var profile = document.getElementById('srAuthProfile');
    if (token && user && user.name) {
        // Logged in → hide guest buttons, show profile chip.
        if (guest) guest.style.display = 'none';
        if (profile) profile.style.display = 'flex';
        if ((el = document.getElementById('srAuthInitial'))) el.textContent = user.name.trim().charAt(0).toUpperCase();
        if ((el = document.getElementById('srAuthName'))) el.textContent = user.name;
    } else {
        // Guest → keep Log in / Get started, ensure the profile chip stays hidden.
        if (guest) guest.style.display = 'flex';
        if (profile) profile.style.display = 'none';
    }
})();
</script>

<main class="lp-main" id="lp-main-content">
    @yield('content')
</main>

<footer class="bg-dark text-white">
    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
            <a href="{{ url('/') }}" class="flex items-center" aria-label="SmartRecruit home">
                <img src="/logo-white.svg" class="h-9 w-auto" alt="SmartRecruit logo" width="160" height="36">
            </a>
        </div>
        <p class="mt-10 text-xs text-white/40">AI-powered recruiting platform &copy; {{ date('Y') }} SmartRecruit</p>
    </div>
</footer>

<div class="toast-container" id="toasts"></div>
@yield('scripts')
</body>
</html>