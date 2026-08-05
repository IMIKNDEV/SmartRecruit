@extends('layouts.guest')

@section('title', 'Connexion')

@section('body_attrs')
 data-page="login"
@endsection

@section('content')
<div class="lp-auth">
    <div class="container lp-auth-inner">
        <aside class="lp-auth-panel">
            <span class="lp-eyebrow lp-eyebrow-light">Espace recruteurs & candidats</span>
            <div>
                <h2>Vos offres, vos candidatures, un seul tableau de bord.</h2>
                <p>Score de compatibilité, pipeline visuel et entretiens, réunis dans une interface claire.</p>
            </div>
            <ul class="lp-auth-proof">
                <li><span class="lp-proof-ic"><x-icon name="pipeline" /></span> Pipeline Kanban en temps réel</li>
                <li><span class="lp-proof-ic"><x-icon name="target" /></span> Score IA transparent, de 0 à 100</li>
                <li><span class="lp-proof-ic"><x-icon name="calendar" /></span> Entretiens planifiés et notés</li>
            </ul>
        </aside>

        <div class="lp-auth-card">
            <h1>Bon retour</h1>
            <p class="lp-auth-sub">Connectez-vous pour gérer vos offres et candidatures.</p>

            <form id="loginForm" novalidate>
                <div class="form-alert" role="alert"></div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="input" type="email" id="email" name="email" placeholder="vous@exemple.com" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="input" type="password" id="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary lp-btn-block">Se connecter</button>
            </form>

            <p class="auth-alt">
                Pas encore de compte ? <a href="{{ route('register') }}">Inscrivez-vous</a>
            </p>
        </div>
    </div>
</div>
@endsection
