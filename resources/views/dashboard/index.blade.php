@extends('layouts.app')

@section('title', 'Dashboard')

@section('body_attrs')
 data-page="dashboard"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="page-sub" id="dashSub">Overview of your recruitment activity.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('recruiter.jobs.create') }}">
        <x-icon name="plus" :size="16" /> New job offer
    </a>
</div>

<div class="stat-grid" id="statGrid">
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Conversion funnel</div>
        <div class="card-sub">Application distribution per stage, per offer.</div>
        <div id="funnelBox" style="margin-top:1rem"></div>
        <hr class="divider">
        <div class="legend">
            <span><i style="background:var(--pink)"></i> Received</span>
            <span><i style="background:var(--pink-light)"></i> Interview</span>
            <span><i style="background:var(--success)"></i> Accepted</span>
            <span><i style="background:#F8B4B4"></i> Refused</span>
        </div>
    </div>
    <div class="card card-pad">
        <div class="card-title">Score distribution</div>
        <div class="card-sub">AI compatibility score of applications.</div>
        <div id="scoreDistBox" style="margin-top:1rem"></div>
        <hr class="divider">
        <div class="card-title" style="font-size:15px">Time to hire</div>
        <div class="card-sub">Average days from application to hire.</div>
        <div id="timeToHireBox" style="margin-top:6px"></div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Applications — last 30 days</div>
        <div class="card-sub">Daily application volume across your offers.</div>
        <div id="trendBox" style="margin-top:1rem"></div>
    </div>
    <div class="card card-pad">
        <div class="card-title">Pipeline health</div>
        <div class="card-sub">Bottlenecks and deadlines to watch.</div>
        <div id="healthBox" style="margin-top:0.75rem"></div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Top candidates</div>
        <div class="card-sub">Best matches across your offers.</div>
        <div id="topCandBox" style="margin-top:0.5rem"></div>
    </div>
    <div class="card card-pad">
        <div class="card-title">Upcoming interviews</div>
        <div class="card-sub">Interviews scheduled in the coming days.</div>
        <div id="upcomingBox" style="margin-top:0.5rem"></div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Recent activity</div>
        <ul class="activity-list" id="activityBox" style="margin-top:0.5rem"></ul>
    </div>
    <div class="card card-pad">
        <div class="card-title">Offer comparison</div>
        <div class="card-sub">Interview → acceptance rate (vs. your average).</div>
        <div id="offerCompareBox" style="margin-top:1rem"></div>
    </div>
</div>
@endsection
