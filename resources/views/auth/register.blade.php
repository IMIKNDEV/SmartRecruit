@extends('layouts.guest')

@section('title', 'Inscription')

@section('body_attrs')
 data-page="register"
@endsection

@section('content')
<div class="lp-auth">
    <div class="container lp-auth-inner">
        <aside class="lp-auth-panel">
            <span class="lp-eyebrow lp-eyebrow-light">Espace recruteurs & candidats</span>
            <div>
                <h2>Publiez une offre, recevez des candidatures triées.</h2>
                <p>Le score de compatibilité est calculé automatiquement à chaque dépôt de CV.</p>
            </div>
            <ul class="lp-auth-proof">
                <li><span class="lp-proof-ic"><x-icon name="pipeline" /></span> Pipeline Kanban en temps réel</li>
                <li><span class="lp-proof-ic"><x-icon name="target" /></span> Score IA transparent, de 0 à 100</li>
                <li><span class="lp-proof-ic"><x-icon name="calendar" /></span> Entretiens planifiés et notés</li>
            </ul>
        </aside>

        <div class="lp-auth-card">
            <h1>Créer un compte</h1>
            <p class="lp-auth-sub">Rejoignez SmartRecruit, c'est gratuit.</p>

            <form id="registerForm" novalidate>
                <div class="form-alert" role="alert"></div>

                <div class="form-group">
                    <label class="form-label" for="name">Nom complet</label>
                    <input class="input" type="text" id="name" name="name" placeholder="Ayoub Idbelhaj" autocomplete="name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="input" type="email" id="email" name="email" placeholder="vous@exemple.com" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <span class="form-label">Je souhaite être</span>
                    <div class="role-select">
                        <button type="button" class="role-option active" data-role="recruiter" aria-pressed="true">
                            <span class="role-name">Recruteur</span>
                            <span class="role-desc">Je publie des offres</span>
                        </button>
                        <button type="button" class="role-option" data-role="candidate" aria-pressed="false">
                            <span class="role-name">Candidat</span>
                            <span class="role-desc">Je postule à des offres</span>
                        </button>
                    </div>
                    <p class="form-hint">Le rôle est définitif et ne peut pas être modifié après l'inscription.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input class="input" type="password" id="password" name="password" placeholder="8 caractères min." autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirmation</label>
                        <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Répétez" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary lp-btn-block">Créer mon compte</button>
            </form>

            <p class="auth-alt">
                Déjà inscrit ? <a href="{{ route('login') }}">Connectez-vous</a>
            </p>
        </div>
    </div>
</div>
@endsection
