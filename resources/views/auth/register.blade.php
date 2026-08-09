@extends('layouts.guest')

@section('title', 'Create an account')

@section('body_attrs')
 data-page="register"
@endsection

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-6xl items-center justify-center px-6 py-16">
    <div class="w-full max-w-lg" data-reveal>
        <div class="rounded-[32px] border border-line bg-white p-8 shadow-tint sm:p-10">
            <span class="inline-flex items-center gap-2 rounded-pill border border-line bg-surface px-3.5 py-1.5 text-xs font-semibold text-body">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-textaccent" aria-hidden="true"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9Z"/></svg>
                Free to start
            </span>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">Create your account</h1>
            <p class="mt-2 text-sm font-medium text-body">Join SmartRecruit — it takes less than a minute.</p>

            <form id="registerForm" class="mt-7" novalidate>
                <div class="form-alert hidden" role="alert"></div>

                <div class="form-group">
                    <label class="form-label" for="name">Full name</label>
                    <input class="input" type="text" id="name" name="name" placeholder="Enter your full name" autocomplete="name" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input class="input" type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <span class="form-label">I am a</span>
                    <div class="role-select grid grid-cols-2 gap-2.5">
                        <button type="button" class="role-option active rounded-[18px] border-2 border-secondary bg-secondary/5 p-4 text-left transition-all hover:-translate-y-0.5" data-role="recruiter" aria-pressed="true">
                            <span class="role-name flex items-center gap-2 text-sm font-bold text-dark"><x-icon name="briefcase" />Recruiter</span>
                            <span class="role-desc mt-1.5 block text-xs font-medium text-body">I publish and manage job offers</span>
                        </button>
                        <button type="button" class="role-option rounded-[18px] border-2 border-line bg-white p-4 text-left transition-all hover:-translate-y-0.5" data-role="candidate" aria-pressed="false">
                            <span class="role-name flex items-center gap-2 text-sm font-bold text-dark"><x-icon name="users" />Candidate</span>
                            <span class="role-desc mt-1.5 block text-xs font-medium text-body">I apply to open positions</span>
                        </button>
                    </div>
                    <p class="form-hint mt-2 text-xs font-medium text-body/70">Your role is permanent and cannot be changed after registration.</p>
                </div>

                <div class="form-row grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" placeholder="8+ characters" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm</label>
                        <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat it" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="mt-2 w-full rounded-pill bg-secondary px-6 py-3.5 text-sm font-bold text-white transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgb(244_63_133/0.28)] active:scale-[0.98]">
                    Create my account
                </button>
            </form>

            <p class="auth-alt mt-5 text-center text-sm font-medium text-body">
                Already registered? <a href="{{ route('login') }}" class="font-semibold text-textaccent hover:underline">Log in</a>
            </p>
        </div>
    </div>
</div>
@endsection
