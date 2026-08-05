@extends('layouts.app')

@section('title', 'Mes offres d\'emploi')

@section('body_attrs')
 data-page="recruiterJobs"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Offres d'emploi</h1>
        <p class="page-sub">Gérez vos offres publiées, leur statut et leurs candidatures.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('recruiter.jobs.create') }}">
        <x-icon name="plus" :size="16" /> Nouvelle offre
    </a>
</div>

<div class="card">
    <div style="display:flex;gap:12px;padding:18px 22px;border-bottom:1px solid var(--line);flex-wrap:wrap">
        <input class="input" type="search" id="jobsSearch" placeholder="Rechercher un titre, une technologie…" style="flex:1;min-width:220px" aria-label="Rechercher une offre">
        <select class="select" id="jobsStatusFilter" aria-label="Filtrer par statut">
            <option value="">Tous les statuts</option>
            <option value="active">Active</option>
            <option value="archived">Archivée</option>
        </select>
    </div>
    <div class="table-wrap" style="padding:0 22px 22px">
        <table class="table">
            <thead>
                <tr>
                    <th>Offre</th>
                    <th>Contrat</th>
                    <th>Limite</th>
                    <th>Candidatures</th>
                    <th>Statut</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="jobsTbody">
                <tr><td colspan="6" style="text-align:center;color:var(--slate)">Chargement…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
