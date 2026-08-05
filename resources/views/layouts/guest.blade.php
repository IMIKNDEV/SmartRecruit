<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SmartRecruit — AI-powered ATS with candidate scoring and a visual Kanban pipeline for recruiters.">
    <meta property="og:title" content="@yield('title', 'SmartRecruit') | SmartRecruit">
    <meta property="og:description" content="AI-powered ATS with candidate scoring and a visual Kanban pipeline for recruiters.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="{{ asset('logo.svg') }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>@yield('title', 'SmartRecruit') | {{ config('app.name', 'SmartRecruit') }}</title>
    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-dark antialiased" @yield('body_attrs')>
<a href="#lp-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-pill focus:bg-secondary focus:px-5 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">Skip to main content</a>

<header class="sticky top-0 z-50 border-b border-line bg-canvas/85 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
        <a href="/" class="flex items-center gap-2.5" aria-label="SmartRecruit home">
            <img src="/favicon.svg" class="size-9 rounded-[12px]" alt="SmartRecruit logo" width="36" height="36">
            <span class="text-lg font-semibold tracking-tight">SmartRecruit</span>
        </a>
        <nav class="flex items-center gap-2 sm:gap-6" aria-label="Main navigation">
            <a href="{{ route('jobs.index') }}" class="hidden text-sm font-medium text-body transition-colors hover:text-dark sm:block">Jobs</a>
            <a href="{{ route('login') }}" class="hidden text-sm font-medium text-body transition-colors hover:text-dark sm:block">Log in</a>
            <x-btn href="{{ route('register') }}" size="sm">Get started</x-btn>
        </nav>
    </div>
</header>

<main class="lp-main" id="lp-main-content">
    @yield('content')
</main>

<footer class="bg-dark text-white">
    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
            <a href="/" class="flex items-center" aria-label="SmartRecruit home">
                <img src="/logo-white.svg" class="h-9 w-auto" alt="SmartRecruit logo" width="160" height="36">
            </a>
            <nav class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/60" aria-label="Footer navigation">
                <a href="{{ route('jobs.index') }}" class="transition-colors hover:text-white">Jobs</a>
                <a href="{{ route('login') }}" class="transition-colors hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="transition-colors hover:text-white">Get started</a>
            </nav>
        </div>
        <p class="mt-10 text-xs text-white/40">AI-powered recruiting platform &copy; {{ date('Y') }} SmartRecruit</p>
    </div>
</footer>

<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal" id="modalBox"></div>
</div>
<div class="toast-container" id="toasts"></div>
@yield('scripts')
</body>
</html>
