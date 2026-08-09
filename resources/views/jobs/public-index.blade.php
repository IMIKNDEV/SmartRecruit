@extends('layouts.public-jobs')

@section('title', 'Open positions')

@section('body_attrs')
 data-page="jobsIndex"
@endsection

@section('content')
<div class="mx-auto max-w-6xl px-6 py-16 sm:py-20">
    <div class="mb-12 max-w-2xl">
        <span class="inline-flex items-center rounded-pill bg-surface px-4 py-1.5 text-xs font-semibold tracking-wide text-body uppercase">Open positions</span>
        <h1 class="heading-display mt-4">Find your next&nbsp;role</h1>
        <p class="mt-4 text-base text-body sm:text-lg">
            Explore the latest opportunities published on SmartRecruit — apply in minutes
            with an AI-scored CV match.
        </p>
    </div>

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center">
        <label class="flex flex-1 items-center gap-2.5 rounded-pill border border-line bg-white px-5 py-3 transition-colors focus-within:border-accent" for="jobSearch">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-body" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input class="w-full bg-transparent text-sm text-dark outline-none placeholder:text-body/70" type="search" id="jobSearch" name="q" placeholder="Search by title, keyword…" aria-label="Search job offers">
        </label>
        <select class="cursor-pointer rounded-pill border border-line bg-white px-5 py-3 text-sm text-dark outline-none transition-colors focus:border-accent" id="contractFilter" name="contract_type" aria-label="Filter by contract type">
            <option value="">All contracts</option>
            <option value="CDI">CDI</option>
            <option value="CDD">CDD</option>
            <option value="Stage">Stage</option>
            <option value="Alternance">Alternance</option>
            <option value="Freelance">Freelance</option>
        </select>
    </div>

    <div id="jobsList" class="grid gap-4 sm:grid-cols-2">
        <div class="empty"><p>Loading open positions…</p></div>
    </div>
</div>
@endsection
