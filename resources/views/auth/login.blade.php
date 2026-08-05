@extends('layouts.guest')

@section('title', 'Log in')

@section('body_attrs')
 data-page="login"
@endsection

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-6xl items-center justify-center px-6 py-16">
    <div class="w-full max-w-md" data-reveal>
        <div class="rounded-[32px] border border-line bg-white p-8 shadow-tint sm:p-10">
            <span class="inline-flex items-center gap-2 rounded-pill border border-line bg-surface px-3.5 py-1.5 text-xs font-semibold text-body">
                <span class="size-2 rounded-full bg-accent"></span>
                Welcome back
            </span>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">Log in to SmartRecruit</h1>
            <p class="mt-2 text-sm font-medium text-body">Manage your job offers and applications from one dashboard.</p>

            <form id="loginForm" class="mt-7" novalidate>
                <div class="form-alert hidden" role="alert"></div>

                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input class="input" type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="mt-2 w-full rounded-pill bg-secondary px-6 py-3.5 text-sm font-bold text-white transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgb(244_63_133/0.28)] active:scale-[0.98]">
                    Log in
                </button>
            </form>

            <p class="auth-alt mt-5 text-center text-sm font-medium text-body">
                No account yet? <a href="{{ route('register') }}" class="font-semibold text-textaccent hover:underline">Get started</a>
            </p>
        </div>

        <div class="mt-5 rounded-[24px] border border-line bg-surface/70 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-body/70">Demo accounts</p>
            <p class="mt-1 text-xs text-body/60">Click a row to fill the form.</p>
            <ul class="mt-3 space-y-2 text-sm font-medium text-body">
                <li>
                    <button type="button" data-demo-email="recruiter@smartrecruit.test" data-demo-password="password" class="flex w-full items-center justify-between gap-3 rounded-2xl border border-line bg-white px-3.5 py-2.5 text-left transition-all hover:-translate-y-0.5 hover:border-accent hover:shadow-tint">
                        <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-accent"></span>Recruiter</span>
                        <span class="mono text-xs text-dark">recruiter@smartrecruit.test</span>
                    </button>
                </li>
                <li>
                    <button type="button" data-demo-email="salma@smartrecruit.test" data-demo-password="password" class="flex w-full items-center justify-between gap-3 rounded-2xl border border-line bg-white px-3.5 py-2.5 text-left transition-all hover:-translate-y-0.5 hover:border-accent hover:shadow-tint">
                        <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-section-green"></span>Candidate</span>
                        <span class="mono text-xs text-dark">salma@smartrecruit.test</span>
                    </button>
                </li>
                <li class="text-xs text-body/60">Password for both: <span class="mono">password</span></li>
            </ul>
        </div>
    </div>
</div>
@endsection
