@extends('layouts.app')

@section('title', $jobId ? 'Application pipeline' : 'All applications')

@section('body_attrs')
 data-page="recruiterApplications"
 data-job-id="{{ $jobId ?? 'null' }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="appsTitle">{{ $jobId ? 'Application pipeline' : 'All applications' }}</h1>
        <p class="page-sub" id="appsSub">
            {{ $jobId ? 'Move candidates through the pipeline for this offer.' : 'Move candidates through the pipeline across all your offers.' }}
        </p>
    </div>
    <a class="btn btn-ghost" href="{{ route('recruiter.jobs') }}"><x-icon name="arrow-left" :size="16" /> Job offers</a>
</div>

<div class="card card-pad">
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <input class="input" type="search" id="appsSearch" placeholder="Search a candidate…" style="flex:1;min-width:220px" aria-label="Search a candidate">
        <span class="kanban-total mono" id="appsTotal">0 applications</span>
    </div>
    <div class="kanban" id="kanbanBoard" style="margin-top:18px"></div>
</div>
@endsection
