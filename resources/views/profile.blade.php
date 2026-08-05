@extends('layouts.app')

@section('title', 'Mon profil')

@section('body_attrs')
 data-page="profile"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Mon profil</h1>
        <p class="page-sub">Vos informations personnelles. Le rôle est défini à l'inscription et n'est pas modifiable.</p>
    </div>
</div>

<div class="detail-grid" style="align-items:start">
    <div class="detail-main" style="display:grid;gap:16px">
        <div class="card card-pad">
            <div class="profile-hero" style="display:flex;align-items:center;gap:18px;margin-bottom:20px">
                <span style="display:inline-flex;padding:5px;border-radius:50%;background:var(--pink-soft);flex-shrink:0">
                    <span class="avatar avatar-lg" id="profileAvatar">U</span>
                </span>
                <div>
                    <h2 style="margin:0;font-size:22px" id="profileName">Utilisateur</h2>
                    <div style="display:flex;gap:10px;align-items:center;margin-top:7px;flex-wrap:wrap">
                        <span class="tag" id="profileRole" style="background:var(--pink-soft);color:var(--pink-dark);border:1px solid rgba(240,19,90,.18)">Candidat</span>
                        <span class="mono" style="color:var(--slate);font-size:12.5px" id="profileEmail">—</span>
                    </div>
                </div>
            </div>

            <form id="profileForm" novalidate>
                <div class="form-alert" style="display:none"></div>
                <div class="form-grid">
                    <div class="form-group form-col-2">
                        <label class="form-label" for="pfName">Nom complet</label>
                        <input class="input" type="text" id="pfName" maxlength="255" required>
                    </div>
                    <div class="form-group form-col-2">
                        <label class="form-label" for="pfEmail">Email</label>
                        <input class="input" type="email" id="pfEmail" maxlength="255" required>
                    </div>
                    <div class="form-group form-col-2">
                        <label class="form-label" for="pfPassword">Nouveau mot de passe <span style="color:var(--slate);font-weight:400">(laisser vide pour conserver)</span></label>
                        <input class="input" type="password" id="pfPassword" minlength="8" autocomplete="new-password" placeholder="••••••••">
                    </div>
                    <div class="form-group form-col-2">
                        <label class="form-label" for="pfPasswordConfirm">Confirmation du mot de passe</label>
                        <input class="input" type="password" id="pfPasswordConfirm" autocomplete="new-password" placeholder="••••••••">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:18px">
                    <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="detail-side" style="display:grid;gap:14px">
        <div class="card card-pad">
            <div style="display:flex;align-items:center;gap:9px;margin:0 0 14px">
                <span style="width:9px;height:9px;border-radius:50%;background:var(--pink);box-shadow:0 0 0 4px var(--pink-soft)"></span>
                <h3 style="margin:0;font-size:15px">Compte</h3>
            </div>
            <dl class="kv">
                <dt>Rôle</dt><dd id="kvRole">—</dd>
                <dt>Membre depuis</dt><dd id="kvJoined">—</dd>
            </dl>
        </div>
    </aside>
</div>
@endsection
