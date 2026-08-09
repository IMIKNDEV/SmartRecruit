@extends('layouts.app')

@section('title', $jobId ? 'Application pipeline' : 'Recent applications')

@section('body_attrs')
 data-page="recruiterApplications"
 data-job-id="{{ $jobId ?? 'null' }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="appsTitle">{{ $jobId ? 'Application pipeline' : 'Recent applications' }}</h1>
        <p class="page-sub" id="appsSub">
            {{ $jobId
                ? 'Move candidates through the pipeline for this offer.'
                : 'Review submitted applications across all your offers. Click a candidate to open their full submission and AI score.' }}
        </p>
    </div>
    <a class="btn btn-ghost" href="{{ route('recruiter.jobs') }}"><x-icon name="arrow-left" :size="16" /> Job offers</a>
</div>

@if ($jobId)
    <div class="card card-pad">
        <div class="apps-toolbar">
            <div class="apps-search">
                <x-icon name="search" :size="16" />
                <input class="input" type="search" id="appsSearch" placeholder="Search a candidate…" aria-label="Search a candidate">
            </div>
            <span class="kanban-total mono" id="appsTotal">0 applications</span>
        </div>
        <div class="kanban" id="kanbanBoard"></div>
    </div>
@else
    <div class="card card-pad">
        <div class="apps-toolbar">
            <div class="apps-search">
                <x-icon name="search" :size="16" />
                <input class="input" type="search" id="appsSearch" placeholder="Search by candidate or offer…" aria-label="Search applications">
            </div>
            <div class="apps-tabs" role="tablist" aria-label="Application views">
                <button type="button" class="apps-tab is-active" role="tab" aria-selected="true" data-view="active">Active</button>
                <button type="button" class="apps-tab" role="tab" aria-selected="false" data-view="trashed">Deleted<span class="apps-tab-count mono" id="trashCount">0</span></button>
            </div>
            <span class="kanban-total mono" id="appsTotal">0 applications</span>
        </div>
        <div id="recAppsList"></div>
    </div>
@endif
@endsection
