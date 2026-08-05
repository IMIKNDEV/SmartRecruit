@extends('layouts.public-jobs')

@section('title', 'Offres d\'emploi')

@section('body_attrs')
 data-page="jobsIndex"
@endsection

@section('content')
<div class="container lp-jobs-wrap">
    <div class="lp-jobs-head">
        <div>
            <h1>Offres d'emploi</h1>
            <p>Découvrez les dernières opportunités publiées sur SmartRecruit.</p>
        </div>
    </div>

    <div class="lp-jobs-filters">
        <label class="lp-search" for="jobSearch">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input class="lp-search-input" type="search" id="jobSearch" name="q" placeholder="Rechercher par titre, mot-clé…" aria-label="Rechercher une offre">
        </label>
        <select class="lp-select" id="contractFilter" name="contract_type" aria-label="Filtrer par type de contrat">
            <option value="">Tous les contrats</option>
            <option value="CDI">CDI</option>
            <option value="CDD">CDD</option>
            <option value="Stage">Stage</option>
            <option value="Alternance">Alternance</option>
            <option value="Freelance">Freelance</option>
        </select>
    </div>

    <div class="jobs-list" id="jobsList">
        <div class="empty"><p>Chargement des offres…</p></div>
    </div>
</div>
@endsection
