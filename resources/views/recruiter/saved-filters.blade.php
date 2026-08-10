@extends('layouts.app')

@section('title', 'Filtres sauvegardés')

@section('body_attrs')
 data-page="recruiterSavedFilters"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Filtres sauvegardés</h1>
        <p class="page-sub">Enregistrez des combinaisons de critères pour les réutiliser en un clic.</p>
    </div>
</div>

<div class="card" style="max-width:720px">
    <div style="padding:22px">
        <h3 style="margin:0 0 14px">Nouveau filtre</h3>
        <form id="filterForm" novalidate>
            <div class="form-alert" style="display:none"></div>
            <div class="form-grid">
                <div class="form-group form-col-2">
                    <label class="form-label" for="filterName">Nom du filtre</label>
                    <input class="input" type="text" id="filterName" placeholder="Profil Laravel fort" required>
                </div>
                <div class="form-group form-col-2">
                    <label class="form-label" for="filterStatus">Statut</label>
                    <select class="select" id="filterStatus">
                        <option value="">Tous</option>
                        <option value="received">Reçu</option>
                        <option value="interview">Entretien</option>
                        <option value="accepted">Accepté</option>
                        <option value="refused">Refusé</option>
                    </select>
                </div>
                <div class="form-group form-col-2">
                    <label class="form-label" for="filterMinScore">Score minimum</label>
                    <input class="input" type="number" id="filterMinScore" min="0" max="100" placeholder="80">
                </div>
                <div class="form-group form-col-2">
                    <label class="form-label" for="filterTech">Technologies</label>
                    <input class="input" type="text" id="filterTech" placeholder="PHP, Laravel" required>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:14px">
                <button class="btn btn-primary" type="submit">Enregistrer le filtre</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top:18px">
    <div style="padding:22px">
        <h3 style="margin:0 0 14px">Mes filtres sauvegardés</h3>
        <div id="filtersBox"><div class="empty">Chargement…</div></div>
    </div>
</div>
@endsection
