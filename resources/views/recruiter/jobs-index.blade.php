@extends('layouts.app')

@section('title', 'Job offers')

@section('body_attrs')
 data-page="recruiterJobs"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Job offers</h1>
        <p class="page-sub">Manage your published offers, their pipeline and their status.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('recruiter.jobs.create') }}">
        <x-icon name="plus" :size="16" /> New offer
    </a>
</div>

<div class="card">
    <div style="display:flex;gap:12px;padding:18px 22px;border-bottom:1px solid var(--line);flex-wrap:wrap">
        <input class="input" type="search" id="jobsSearch" placeholder="Search a title, a technology…" style="flex:1;min-width:220px" aria-label="Search a job offer">
        <select class="select" id="jobsStatusFilter" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="archived">Archived</option>
        </select>
    </div>
    <div class="table-wrap" style="padding:0 22px 22px">
        <table class="table">
            <thead>
                <tr>
                    <th>Offer</th>
                    <th>Contract</th>
                    <th>Deadline</th>
                    <th style="min-width:220px">Pipeline</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="jobsTbody">
                <tr><td colspan="6" style="text-align:center;color:var(--slate)">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
