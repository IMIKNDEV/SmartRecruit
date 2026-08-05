@extends('layouts.app')

@section('title', 'Postuler')

@section('body_attrs')
 data-page="candidateApply"
 data-job-id="{{ $jobId ?? 0 }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="applyJobTitle">Postuler</h1>
        <p class="page-sub">Votre CV sera analysé automatiquement pour calculer un score de compatibilité.</p>
    </div>
    <a class="btn btn-ghost" href="#" id="applyBackLink"><x-icon name="arrow-left" :size="16" /> Retour aux offres</a>
</div>

<div class="detail-grid" style="align-items:start">
    <div class="detail-main" style="display:grid;gap:16px">
        <div class="card card-pad" id="applyJobSummary">
            <div class="empty">Chargement…</div>
        </div>

        <div class="card card-pad">
            <h3 style="margin:0 0 14px">Envoyer ma candidature</h3>
            <form id="applyForm" enctype="multipart/form-data" novalidate>
                <div class="form-alert" style="display:none"></div>
                <div class="form-group">
                    <label class="form-label" for="applyCv">CV <span style="color:var(--slate);font-weight:400">(PDF, 5 Mo max)</span></label>
                    <div class="dropzone" id="applyDropzone">
                        <x-icon name="upload" :size="22" />
                        <div class="dz-text">Glissez votre CV ici ou <span class="dz-link">parcourez</span></div>
                        <div class="dz-file mono" style="display:none"></div>
                        <input type="file" id="applyCv" accept="application/pdf,.pdf" hidden>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="applyCover">Lettre de motivation</label>
                    <textarea class="textarea" id="applyCover" rows="7" minlength="20" maxlength="5000" required placeholder="Présentez votre parcours, vos motivations et vos atouts pour ce poste…"></textarea>
                    <div class="field-hint" id="coverHint">20 caractères minimum</div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:14px">
                    <button class="btn btn-ghost" type="button" id="applyCancel">Annuler</button>
                    <button class="btn btn-primary" type="submit"><x-icon name="send" :size="16" /> Envoyer ma candidature</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="detail-side">
        <div class="card card-pad">
            <h3 style="margin:0 0 10px">Comment ça marche ?</h3>
            <ul class="mini-list">
                <li>Votre CV (PDF) est déposé sur l'offre.</li>
                <li>Une analyse IA calcule un score de compatibilité (0-100).</li>
                <li>Le recruteur voit les mots-clés trouvés et manquants.</li>
                <li>Vous suivez le statut de votre candidature en temps réel.</li>
            </ul>
        </div>
    </aside>
</div>
@endsection
