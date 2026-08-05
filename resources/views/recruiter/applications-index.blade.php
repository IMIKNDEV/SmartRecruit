@extends('layouts.app')

@section('title', $jobId ? 'Candidatures de l\'offre' : 'Candidatures')

@section('body_attrs')
 data-page="recruiterApplications"
 data-job-id="{{ $jobId ?? 'null' }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="appsTitle">{{ $jobId ? 'Candidatures de l\'offre' : 'Toutes les candidatures' }}</h1>
        <p class="page-sub" id="appsSub">Recherchez, filtrez et traitez les candidatures de vos offres.</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('recruiter.jobs') }}"><x-icon name="arrow-left" :size="16" /> Mes offres</a>
</div>

<div class="card">
    <div style="display:flex;gap:12px;padding:18px 22px;border-bottom:1px solid var(--line);flex-wrap:wrap;align-items:center">
        <input class="input" type="search" id="appsSearch" placeholder="Rechercher un candidat…" style="flex:1;min-width:200px" aria-label="Rechercher un candidat">
        <select class="select" id="appsStatusFilter" aria-label="Filtrer par statut">
            <option value="">Tous les statuts</option>
            <option value="received">Reçu</option>
            <option value="interview">Entretien</option>
            <option value="accepted">Accepté</option>
            <option value="refused">Refusé</option>
        </select>
        <select class="select" id="appsScoreFilter" aria-label="Filtrer par score minimum">
            <option value="">Score minimum</option>
            <option value="80">80+</option>
            <option value="60">60+</option>
            <option value="50">50+</option>
        </select>
        <select class="select" id="savedFilterSelect" aria-label="Filtre sauvegardé">
            <option value="">Filtres sauvegardés</option>
        </select>
    </div>
    <div class="batch-bar" id="batchBar" style="display:none;padding:12px 22px;border-bottom:1px solid var(--line);background:var(--pink-soft);align-items:center;gap:12px">
        <span id="batchCount" class="mono" style="font-size:13px"></span>
        <select class="select" id="batchStatus" style="width:auto" aria-label="Statut du lot">
            <option value="">Changer le statut…</option>
            <option value="interview">→ Entretien</option>
            <option value="accepted">→ Accepté</option>
            <option value="refused">→ Refusé</option>
        </select>
        <button class="btn btn-sm btn-primary" id="batchApply" type="button">Appliquer</button>
        <button class="btn btn-sm btn-ghost" id="batchClear" type="button">Annuler la sélection</button>
    </div>
    <div class="table-wrap" style="padding:0 22px 22px">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="appsSelectAll" aria-label="Tout sélectionner"></th>
                    <th>Candidat</th>
                    <th>Offre</th>
                    <th>Score</th>
                    <th>Tags</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="appsTbody">
                <tr><td colspan="8" style="text-align:center;color:var(--slate)">Chargement…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
