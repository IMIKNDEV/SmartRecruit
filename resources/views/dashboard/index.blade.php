@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('body_attrs')
 data-page="dashboard"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Tableau de bord</h1>
        <p class="page-sub" id="dashSub">Vue d'ensemble de votre activité de recrutement.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('recruiter.jobs.create') }}">
        <x-icon name="plus" :size="16" /> Nouvelle offre
    </a>
</div>

<div class="stat-grid" id="statGrid">
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
    <div class="card card-pad stat-card"><div class="skeleton" style="height:20px"></div></div>
</div>

<div class="card card-pad" style="margin-bottom:24px">
    <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
        <span>Pipeline des candidatures</span>
        <a href="{{ route('recruiter.applications') }}" class="nav-textlink" style="font-size:13px">Tout voir →</a>
    </div>
    <div class="kanban" id="kanbanBoard" style="margin-top:18px"></div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Entonnoir de conversion</div>
        <div class="card-sub">Répartition des candidatures par étape, par offre.</div>
        <div id="funnelBox"></div>
        <hr class="divider">
        <div class="legend">
            <span><i style="background:var(--pink)"></i> Reçues</span>
            <span><i style="background:var(--pink-light)"></i> Entretien</span>
            <span><i style="background:var(--success)"></i> Acceptées</span>
            <span><i style="background:#F8B4B4"></i> Refusées</span>
        </div>
    </div>
    <div class="card card-pad">
        <div class="card-title">Distribution des scores</div>
        <div class="card-sub">Score de compatibilité des candidatures.</div>
        <div id="scoreDistBox"></div>
        <hr class="divider">
        <div class="card-title" style="font-size:15px">Tâches en attente</div>
        <div id="pendingBox" style="margin-top:6px"></div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    <div class="card card-pad">
        <div class="card-title">Activité récente</div>
        <ul class="activity-list" id="activityBox"></ul>
    </div>
    <div class="card card-pad">
        <div class="card-title">Comparaison des offres</div>
        <div class="card-sub">Taux entretien → acceptation (vs. moyenne recruteur).</div>
        <div id="offerCompareBox"></div>
    </div>
</div>
@endsection
