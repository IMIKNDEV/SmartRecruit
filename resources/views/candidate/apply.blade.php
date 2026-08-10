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
        <p class="page-sub">Votre CV sera analysé en profondeur par notre IA pour mesurer votre compatibilité avec le poste.</p>
    </div>
    <a class="btn btn-ghost" href="#" id="applyBackLink"><x-icon name="arrow-left" :size="16" /> Retour aux offres</a>
</div>

<div class="detail-grid" style="align-items:start">
    <div class="detail-main" style="display:grid;gap:16px">
        <div class="card card-pad" id="applyJobSummary">
            <div class="empty">Chargement…</div>
        </div>

        <div class="card card-pad">
            <h3 style="margin:0 0 4px">Envoyer ma candidature</h3>
            <p class="card-sub" style="margin:0 0 18px">C'est en 2 minutes — l'analyse IA démarre dès l'envoi.</p>
            <form id="applyForm" enctype="multipart/form-data" novalidate>
                <div class="form-alert" style="display:none"></div>
                <div class="form-group">
                    <label class="form-label" for="applyCv">CV <span style="color:var(--slate);font-weight:400">— PDF, 5 Mo max</span></label>
                    <div class="dropzone" id="applyDropzone" role="button" tabindex="0" aria-label="Choisir votre CV">
                        <x-icon name="upload" :size="26" />
                        <div class="dz-text">Glissez votre CV ici ou <span class="dz-link">parcourez</span></div>
                        <div class="dz-file mono" style="display:none"></div>
                    </div>
                    <input type="file" id="applyCv" accept="application/pdf,.pdf" hidden>
                </div>
                <div class="form-group">
                    <label class="form-label" for="applyCover">Lettre de motivation <span style="color:var(--slate);font-weight:400">— 20 caractères minimum</span></label>
                    <textarea class="textarea" id="applyCover" rows="7" minlength="20" maxlength="5000" required placeholder="Présentez votre parcours, vos motivations et vos atouts pour ce poste…"></textarea>
                    <div class="field-hint" id="coverHint">0 / 20 caractères minimum</div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-ghost" type="button" id="applyCancel">Annuler</button>
                    <button class="btn btn-primary" type="submit"><x-icon name="send" :size="16" /> Envoyer ma candidature</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="detail-side">
        <div class="card card-pad">
            <h3 style="margin:0 0 16px">Comment ça marche ?</h3>
            <ol class="steps">
                <li class="step">
                    <span class="step-num">1</span>
                    <div>
                        <strong>Déposez votre CV</strong>
                        <p>Votre CV au format PDF est joint à votre candidature.</p>
                    </div>
                </li>
                <li class="step">
                    <span class="step-num">2</span>
                    <div>
                        <strong>L'IA analyse tout votre profil</strong>
                        <p>Expérience, compétences, formations et parcours sont analysés en profondeur — pas seulement quelques mots-clés.</p>
                    </div>
                </li>
                <li class="step">
                    <span class="step-num">3</span>
                    <div>
                        <strong>Un score de compatibilité (0-100)</strong>
                        <p>Le recruteur voit le score accompagné du détail de l'analyse : points forts et axes à renforcer.</p>
                    </div>
                </li>
                <li class="step">
                    <span class="step-num">4</span>
                    <div>
                        <strong>Suivez en temps réel</strong>
                        <p>Le statut de votre candidature évolue à chaque étape du processus.</p>
                    </div>
                </li>
            </ol>
        </div>
    </aside>
</div>
@endsection
