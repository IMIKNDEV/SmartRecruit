@extends('layouts.app')

@section('title', 'Modifier l\'offre')

@section('body_attrs')
 data-page="recruiterJobForm"
 data-mode="edit"
 data-job-id="{{ $jobId ?? 0 }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Modifier l'offre</h1>
        <p class="page-sub">Mettez à jour les informations de l'offre.</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('recruiter.jobs') }}"><x-icon name="arrow-left" :size="16" /> Retour</a>
</div>

<div class="card" style="max-width:760px">
    <form id="jobForm" novalidate>
        <div class="form-alert" style="display:none"></div>
        <div class="form-grid">
            <div class="form-group form-col-2">
                <label class="form-label" for="title">Titre du poste</label>
                <input class="input" type="text" id="title" name="title" placeholder="Développeur Laravel Senior" required>
            </div>
            <div class="form-group form-col-2">
                <label class="form-label" for="contract_type">Type de contrat</label>
                <select class="select" id="contract_type" name="contract_type" required>
                    <option value="CDI">CDI</option>
                    <option value="CDD">CDD</option>
                    <option value="Stage">Stage</option>
                    <option value="Alternance">Alternance</option>
                    <option value="Freelance">Freelance</option>
                </select>
            </div>
            <div class="form-group form-col-2">
                <label class="form-label" for="tech_stack">Technologies requises</label>
                <input class="input" type="text" id="tech_stack" name="tech_stack" placeholder="PHP, Laravel, MySQL, Docker" required>
            </div>
            <div class="form-group form-col-2">
                <label class="form-label" for="salary">Salaire (MAD, mensuel brut)</label>
                <input class="input" type="number" id="salary" name="salary" min="0" step="0.01" placeholder="15000">
            </div>
            <div class="form-group form-col-2">
                <label class="form-label" for="deadline">Date limite de candidature</label>
                <input class="input" type="date" id="deadline" name="deadline" required>
            </div>
            <div class="form-group form-col-2">
                <label class="form-label" for="description">Description du poste</label>
                <textarea class="textarea" id="description" name="description" rows="7" placeholder="Missions, profil recherché, avantages…" required></textarea>
            </div>
        </div>
        <div class="form-actions" style="display:flex;justify-content:flex-end;gap:10px;padding-top:8px">
            <a class="btn btn-ghost" href="{{ route('recruiter.jobs') }}">Annuler</a>
            <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
        </div>
    </form>
</div>
@endsection
