@extends('layouts.guest')

@section('title', 'Recrutement intelligent')

@section('body_attrs')
 data-page="landing"
@endsection

@section('content')
<style>
/* ---- Landing scoped premium layer (brand tokens only) ---- */
html { scroll-behavior: smooth; }
body[data-page="landing"] { --ease: cubic-bezier(0.32, 0.72, 0, 1); }

/* Eyebrow */
body[data-page="landing"] .lp-eyebrow {
  background: var(--white);
  border: 1px solid var(--line);
  box-shadow: var(--shadow-sm);
  padding: 8px 18px 8px 10px;
  font-family: var(--font-mono);
  font-size: 12px;
  letter-spacing: .02em;
  text-transform: none;
  color: var(--slate);
}
body[data-page="landing"] .lp-eyebrow .eyebrow-dot {
  width: 8px; height: 8px;
  box-shadow: 0 0 0 4px rgba(240, 19, 90, 0.14), 0 0 12px rgba(240, 19, 90, 0.5);
}

/* Hero copy */
body[data-page="landing"] .lp-hero-copy h1 {
  letter-spacing: -0.03em;
  text-wrap: balance;
  font-weight: 800;
}
body[data-page="landing"] .lp-hero-copy h1 .pink-word {
  background: linear-gradient(120deg, var(--pink), var(--pink-dark));
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
}
body[data-page="landing"] .lp-hero-sub {
  font-size: 19px;
  color: var(--slate);
  max-width: 500px;
}

/* Primary CTA — button-in-button arrow island */
body[data-page="landing"] .lp-hero-cta .btn-primary {
  padding: 12px 12px 12px 30px;
  gap: 16px;
  font-size: 16px;
  box-shadow: var(--shadow-glow);
  transition: transform .4s var(--ease), background .3s var(--ease), box-shadow .4s var(--ease);
}
body[data-page="landing"] .lp-hero-cta .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 34px rgba(240, 19, 90, 0.30); }
body[data-page="landing"] .lp-cta-arrow {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.22);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform .4s var(--ease), background .3s var(--ease);
}
body[data-page="landing"] .lp-cta-arrow svg { width: 17px; height: 17px; }
body[data-page="landing"] .btn-primary:hover .lp-cta-arrow { transform: translateX(3px); background: rgba(255, 255, 255, 0.34); }
body[data-page="landing"] .btn-primary:active .lp-cta-arrow { transform: translateX(5px); }
body[data-page="landing"] .lp-hero-cta .btn-ghost {
  border-color: var(--line-strong);
  color: var(--ink);
  background: var(--white);
  transition: border-color .3s var(--ease), color .3s var(--ease), transform .4s var(--ease);
}
body[data-page="landing"] .lp-hero-cta .btn-ghost:hover { border-color: var(--pink); color: var(--pink); transform: translateY(-2px); }
body[data-page="landing"] .lp-hero-micro {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 22px;
  font-size: 13px;
  color: var(--slate);
}
body[data-page="landing"] .lp-hero-micro .mono { font-size: 11.5px; color: var(--pink-dark); }

/* Hero visual — double bezel (outer shell + inner core) */
body[data-page="landing"] .lp-hero-shell {
  padding: 12px;
  border-radius: calc(var(--radius-lg) + 12px);
  background: linear-gradient(160deg, rgba(252, 228, 236, 0.9), rgba(254, 243, 226, 0.65));
  border: 1px solid rgba(240, 19, 90, 0.12);
  box-shadow: var(--shadow-lg), inset 0 1px 0 rgba(255, 255, 255, 0.8);
  width: min(400px, 94%);
}
body[data-page="landing"] .lp-visual-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--line);
  background: var(--white);
  box-shadow: var(--shadow-md), inset 0 1px 0 rgba(255, 255, 255, 0.9);
  padding: 22px;
}
body[data-page="landing"] .lp-hero-kanban-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
body[data-page="landing"] .lp-hero-kicker {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--font-mono); font-size: 11.5px; font-weight: 600;
  color: var(--slate); letter-spacing: .03em;
}
body[data-page="landing"] .lp-hero-score { box-shadow: var(--shadow-glow); }
body[data-page="landing"] .lp-hero-kanban .lp-mini-col {
  background: var(--kanban-col);
  border: 1px solid var(--line);
  box-shadow: var(--shadow-sm);
  border-radius: var(--radius);
  padding: 12px 12px;
}
body[data-page="landing"] .lp-hero-kanban .lp-mini-col b { font-size: 14px; }
body[data-page="landing"] .lp-visual-badge {
  top: -14px; right: -6px;
  background: var(--white);
  border: 1px solid var(--line);
  box-shadow: var(--shadow-md);
  padding: 10px 16px;
  font-size: 12.5px;
}
body[data-page="landing"] .lp-visual-badge::before {
  content: '';
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--success);
  box-shadow: 0 0 0 4px var(--success-bg);
  flex-shrink: 0;
}
body[data-page="landing"] .lp-visual-badge svg { display: none; }

/* Stats band */
body[data-page="landing"] .lp-stats { padding: 0 0 96px; }
body[data-page="landing"] .lp-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}
body[data-page="landing"] .lp-stat {
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  padding: 26px 22px;
  box-shadow: var(--shadow-sm);
  transition: transform .4s var(--ease), box-shadow .4s var(--ease);
}
body[data-page="landing"] .lp-stat:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
body[data-page="landing"] .lp-stat .mono {
  display: block;
  font-size: 34px;
  font-weight: 600;
  color: var(--ink);
  line-height: 1;
  font-variant-numeric: tabular-nums;
  margin-bottom: 10px;
}
body[data-page="landing"] .lp-stat .mono em { font-style: normal; font-size: 18px; color: var(--pink); }
body[data-page="landing"] .lp-stat-label { font-size: 13.5px; color: var(--slate); line-height: 1.45; }
body[data-page="landing"] .lp-stat-label b { color: var(--ink); font-weight: 600; }

/* Section heads */
body[data-page="landing"] .lp-section-head { text-align: left; margin: 0 0 44px; }
body[data-page="landing"] .lp-section-head.center { text-align: center; margin-inline: auto; }
body[data-page="landing"] .lp-section-kicker {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--font-mono); font-size: 11.5px; font-weight: 600;
  letter-spacing: .16em; text-transform: uppercase;
  color: var(--pink-dark);
  margin-bottom: 14px;
}
body[data-page="landing"] .lp-section-kicker::before { content: ''; width: 22px; height: 1px; background: var(--pink); }
body[data-page="landing"] .lp-section-head h2 { font-size: clamp(30px, 3.6vw, 40px); letter-spacing: -0.02em; margin: 0 0 16px; text-wrap: balance; }
body[data-page="landing"] .lp-section-head p { color: var(--slate); font-size: 16.5px; margin: 0; line-height: 1.6; }

/* Features — asymmetric zig-zag */
body[data-page="landing"] .lp-features { padding: 96px 0 40px; }
body[data-page="landing"] .lp-feature-stack { display: grid; gap: 26px; }
body[data-page="landing"] .lp-feature-row {
  display: grid;
  grid-template-columns: 1.02fr 0.98fr;
  gap: clamp(32px, 6vw, 84px);
  align-items: center;
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
  padding: clamp(28px, 4.5vw, 56px);
  box-shadow: var(--shadow-sm);
  transition: box-shadow .5s var(--ease), transform .5s var(--ease);
}
body[data-page="landing"] .lp-feature-row:hover { box-shadow: var(--shadow-lg); transform: translateY(-3px); }
body[data-page="landing"] .lp-feature-row.flip .lp-feature-visual { order: -1; }
body[data-page="landing"] .lp-feature-icon {
  width: 48px; height: 48px;
  border-radius: var(--radius);
  background: var(--pink-soft);
  color: var(--pink);
  display: inline-flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  box-shadow: inset 0 0 0 1px rgba(240, 19, 90, 0.12);
}
body[data-page="landing"] .lp-feature-icon svg { width: 22px; height: 22px; }
body[data-page="landing"] .lp-feature-copy h3 { font-size: 24px; letter-spacing: -0.015em; margin: 0 0 12px; text-wrap: balance; }
body[data-page="landing"] .lp-feature-copy p { font-size: 15.5px; color: var(--slate); line-height: 1.65; margin: 0; max-width: 46ch; }
body[data-page="landing"] .lp-feature-link {
  display: inline-flex; align-items: center; gap: 8px;
  margin-top: 18px; font-weight: 600; font-size: 14px; color: var(--pink);
  transition: gap .3s var(--ease);
}
body[data-page="landing"] .lp-feature-link:hover { gap: 12px; color: var(--pink-dark); }
body[data-page="landing"] .lp-feature-link svg { width: 15px; height: 15px; }

/* Feature visuals */
body[data-page="landing"] .lp-fv {
  background: var(--pink-pale);
  border: 1px solid rgba(240, 19, 90, 0.10);
  border-radius: var(--radius-card);
  padding: 22px;
  display: flex; flex-direction: column; gap: 14px;
}
body[data-page="landing"] .lp-fv-track { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
body[data-page="landing"] .lp-fv-track .fv-arrow { color: var(--pink-light); }
body[data-page="landing"] .lp-fv-track .fv-arrow svg { width: 15px; height: 15px; }
body[data-page="landing"] .lp-fv-note { font-family: var(--font-mono); font-size: 12px; color: var(--slate); }
body[data-page="landing"] .lp-fv-note b { color: var(--pink-dark); }
body[data-page="landing"] .lp-fv-score { flex-direction: row; align-items: center; justify-content: center; gap: 22px; }
body[data-page="landing"] .lp-fv-ring {
  width: 104px; height: 104px; flex-shrink: 0;
  border-radius: 50%;
  background: conic-gradient(var(--pink) 82%, var(--line) 82%);
  display: flex; align-items: center; justify-content: center;
  box-shadow: var(--shadow-glow);
}
body[data-page="landing"] .lp-fv-ring::before {
  content: '';
  position: absolute;
  width: 80px; height: 80px;
  border-radius: 50%;
  background: var(--white);
}
body[data-page="landing"] .lp-fv-ring-inner {
  position: relative;
  z-index: 1;
  text-align: center;
  line-height: 1;
}
body[data-page="landing"] .lp-fv-ring-inner b { font-size: 26px; font-weight: 600; color: var(--ink); }
body[data-page="landing"] .lp-fv-ring-inner span { display: block; margin-top: 4px; font-size: 11px; color: var(--slate); }
body[data-page="landing"] .lp-fv-kw { display: flex; flex-wrap: wrap; gap: 8px; max-width: 220px; align-content: center; }
body[data-page="landing"] .lp-fv-scores { display: flex; gap: 10px; }
body[data-page="landing"] .lp-fv-scores .mono {
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: var(--radius-pill);
  padding: 7px 13px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--ink);
  box-shadow: var(--shadow-sm);
}
body[data-page="landing"] .lp-fv-bar { height: 10px; border-radius: 6px; background: var(--kanban-col); overflow: hidden; }
body[data-page="landing"] .lp-fv-bar i { display: block; height: 100%; border-radius: 6px; background: linear-gradient(90deg, var(--pink), var(--pink-light)); }

/* How it works */
body[data-page="landing"] .lp-how { padding: 96px 0 20px; }
body[data-page="landing"] .lp-how-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
body[data-page="landing"] .lp-how-step {
  border: 1px solid var(--line);
  background: var(--white);
  border-radius: var(--radius-card);
  padding: 30px 26px;
  position: relative;
  box-shadow: var(--shadow-sm);
  transition: transform .4s var(--ease);
}
body[data-page="landing"] .lp-how-step:hover { transform: translateY(-3px); }
body[data-page="landing"] .lp-how-num {
  position: absolute;
  top: -14px; left: 24px;
  width: 34px; height: 34px;
  border-radius: 50%;
  background: var(--pink);
  color: #fff;
  font-family: var(--font-mono);
  font-size: 13px;
  font-weight: 600;
  display: flex; align-items: center; justify-content: center;
  box-shadow: var(--shadow-glow);
}
body[data-page="landing"] .lp-how-step h3 { font-size: 17px; margin: 8px 0 8px; }
body[data-page="landing"] .lp-how-step p { font-size: 13.5px; color: var(--slate); margin: 0; line-height: 1.55; }

/* CTA band — warm pink-glow */
body[data-page="landing"] .lp-cta { padding: 96px 0 48px; }
body[data-page="landing"] .lp-cta-card {
  border: 1px solid rgba(240, 19, 90, 0.16);
  border-radius: 32px;
  padding: clamp(48px, 7vw, 88px) clamp(24px, 6vw, 72px);
  background:
    radial-gradient(640px 320px at 50% -20%, rgba(240, 19, 90, 0.16), transparent 70%),
    radial-gradient(420px 260px at 12% 110%, rgba(252, 228, 236, 0.8), transparent 65%),
    var(--white);
  box-shadow: var(--shadow-lg), inset 0 1px 0 rgba(255, 255, 255, 0.9);
}
body[data-page="landing"] .lp-cta-card::before {
  content: '';
  position: absolute;
  inset: -50%;
  background: radial-gradient(closest-side at 50% 0%, rgba(240, 19, 90, 0.14), transparent 70%);
  pointer-events: none;
}
body[data-page="landing"] .lp-cta-card h2 { position: relative; font-size: clamp(30px, 4vw, 44px); letter-spacing: -0.02em; margin: 0 0 16px; text-wrap: balance; }
body[data-page="landing"] .lp-cta-card p { position: relative; color: var(--slate); font-size: 17px; max-width: 52ch; margin: 0 auto 34px; }
body[data-page="landing"] .lp-cta-actions { position: relative; display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
body[data-page="landing"] .lp-cta-actions .btn { box-shadow: var(--shadow-glow); }

/* Trust strip */
body[data-page="landing"] .lp-trust { padding: 34px 0 0; }
body[data-page="landing"] .lp-trust-list { display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; }
body[data-page="landing"] .lp-trust-item {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 13.5px; color: var(--slate);
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: var(--radius-pill);
  padding: 10px 18px;
  box-shadow: var(--shadow-sm);
}
body[data-page="landing"] .lp-trust-item svg { width: 16px; height: 16px; color: var(--pink); flex-shrink: 0; }

/* Legal strip (complements the layout footer) */
body[data-page="landing"] .lp-legal {
  border-top: 1px solid var(--line);
  margin-top: 56px;
  padding: 22px 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}
body[data-page="landing"] .lp-legal-nav { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; font-size: 13px; }
body[data-page="landing"] .lp-legal-nav a { color: var(--slate); transition: color .3s var(--ease); }
body[data-page="landing"] .lp-legal-nav a:hover { color: var(--pink); }
body[data-page="landing"] .lp-legal-nav .lp-legal-disabled { color: var(--slate-light); cursor: default; }
body[data-page="landing"] .lp-to-top {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--font-mono); font-size: 12px; font-weight: 600;
  color: var(--ink);
  transition: color .3s var(--ease), gap .3s var(--ease);
}
body[data-page="landing"] .lp-to-top:hover { color: var(--pink); gap: 12px; }
body[data-page="landing"] .lp-to-top svg { width: 14px; height: 14px; }

/* Scroll reveals (IntersectionObserver in scripts section) */
body[data-page="landing"] [data-reveal] {
  opacity: 0;
  transform: translateY(26px);
  transition: opacity .8s var(--ease), transform .8s var(--ease);
  will-change: transform, opacity;
}
body[data-page="landing"] [data-reveal].is-in { opacity: 1; transform: none; }
body[data-page="landing"] [data-reveal].rv-1 { transition-delay: .08s; }
body[data-page="landing"] [data-reveal].rv-2 { transition-delay: .16s; }
body[data-page="landing"] [data-reveal].rv-3 { transition-delay: .24s; }

@media (max-width: 980px) {
  body[data-page="landing"] .lp-feature-row { grid-template-columns: 1fr; gap: 28px; }
  body[data-page="landing"] .lp-feature-row.flip .lp-feature-visual { order: 0; }
  body[data-page="landing"] .lp-stats-grid { grid-template-columns: 1fr 1fr; }
  body[data-page="landing"] .lp-how-grid { grid-template-columns: 1fr; gap: 26px; }
}
@media (max-width: 640px) {
  body[data-page="landing"] .lp-stats-grid { grid-template-columns: 1fr; }
  body[data-page="landing"] .lp-fv-score { flex-direction: column; }
  body[data-page="landing"] .lp-legal { flex-direction: column; align-items: flex-start; }
}
@media (prefers-reduced-motion: reduce) {
  body[data-page="landing"] [data-reveal] { opacity: 1; transform: none; transition: none; }
}
</style>

<div id="top"></div>

{{-- ================= HERO ================= --}}
<section class="lp-hero">
    <div class="container lp-hero-inner">
        <div class="lp-hero-copy" data-reveal>
            <span class="lp-eyebrow"><i class="eyebrow-dot"></i>Matching CV / offres propulsé par l'IA</span>
            <h1>Recrutez plus vite,<br>avec plus de <span class="pink-word">précision</span>.</h1>
            <p class="lp-hero-sub">
                SmartRecruit centralise vos candidatures dans un pipeline visuel
                et calcule automatiquement la compatibilité de chaque CV avec vos offres.
            </p>
            <div class="lp-hero-cta">
                <a class="btn btn-primary btn-pill" href="{{ route('register') }}">
                    Commencer gratuitement
                    <span class="lp-cta-arrow">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </a>
                <a class="btn btn-ghost btn-pill" href="{{ route('jobs.index') }}">Voir les offres</a>
            </div>
            <p class="lp-hero-micro">
                <span class="mono">sans carte bancaire</span> · compte recruteur créé en 1 minute
            </p>
        </div>

        <div class="lp-hero-visual rv-2" aria-hidden="true" data-reveal>
            <div class="lp-visual-glow"></div>
            <div class="lp-hero-shell">
                <div class="lp-visual-card">
                    <div class="lp-hero-kanban-head">
                        <span class="lp-hero-kicker"><i class="lp-mini-dot" style="background:var(--pink)"></i>Développeur Laravel</span>
                        <span class="pill pill-success lp-hero-score">92</span>
                    </div>
                    <div class="lp-mini-kanban lp-hero-kanban">
                        <div class="lp-mini-col"><i class="lp-mini-dot" style="background:var(--pink)"></i><span>Reçu</span><b>24</b></div>
                        <div class="lp-mini-col"><i class="lp-mini-dot" style="background:var(--pink-light)"></i><span>Entretien</span><b>8</b></div>
                        <div class="lp-mini-col"><i class="lp-mini-dot" style="background:var(--success)"></i><span>Accepté</span><b>3</b></div>
                    </div>
                    <div class="lp-visual-chips">
                        <span class="chip chip-ok">PHP</span>
                        <span class="chip chip-ok">Laravel</span>
                        <span class="chip chip-ok">MySQL</span>
                        <span class="chip chip-no">Docker</span>
                    </div>
                    <div class="lp-visual-foot">
                        <span class="tag tag-red">prioritaire</span>
                        <span class="lp-visual-step"><b>Reçu → Entretien</b></span>
                    </div>
                </div>
            </div>
            <span class="lp-visual-badge">Score calculé par IA à l'upload</span>
        </div>
    </div>
</section>

{{-- ================= STATS ================= --}}
<section class="lp-stats" aria-label="Quelques chiffres">
    <div class="container">
        <div class="lp-stats-grid" data-reveal>
            <div class="lp-stat">
                <span class="mono">24<em> candidatures</em></span>
                <span class="lp-stat-label">suivies en moyenne sur <b>chaque offre</b> publiée</span>
            </div>
            <div class="lp-stat">
                <span class="mono">9,4<em> jours</em></span>
                <span class="lp-stat-label">de délai moyen entre <b>l'offre et l'embauche</b></span>
            </div>
            <div class="lp-stat">
                <span class="mono">82<em> / 100</em></span>
                <span class="lp-stat-label">de <b>score de compatibilité</b> moyen sur les profils retenus</span>
            </div>
            <div class="lp-stat">
                <span class="mono">4<em> statuts</em></span>
                <span class="lp-stat-label">dans le pipeline : reçu, entretien, <b>accepté ou refusé</b></span>
            </div>
        </div>
    </div>
</section>

{{-- ================= FEATURES (asymmetric zig-zag) ================= --}}
<section class="lp-features">
    <div class="container">
        <div class="lp-section-head" data-reveal>
            <span class="lp-section-kicker">Le poste de pilotage du recruteur</span>
            <h2>Tout votre recrutement, <span class="highlight">au même endroit</span></h2>
            <p>Des outils pensés pour les recruteurs : de la publication de l'offre jusqu'à la décision finale.</p>
        </div>

        <div class="lp-feature-stack">
            <article class="lp-feature-row" data-reveal>
                <div class="lp-feature-copy">
                    <span class="lp-feature-icon"><x-icon name="pipeline" /></span>
                    <h3>Un pipeline visuel, du premier CV à la décision</h3>
                    <p>Chaque candidature suit un parcours clair : reçue, en entretien, acceptée ou refusée. Déplacez les cartes d'une colonne à l'autre, posez des étiquettes rapides et traitez plusieurs dossiers en un seul geste.</p>
                    <a class="lp-feature-link" href="{{ route('jobs.index') }}">Explorer les offres <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                </div>
                <div class="lp-feature-visual" aria-hidden="true">
                    <div class="lp-fv">
                        <div class="lp-fv-track">
                            <span class="pill pill-slate">Reçu</span>
                            <span class="fv-arrow"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
                            <span class="pill">Entretien</span>
                            <span class="fv-arrow"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
                            <span class="pill pill-success">Accepté</span>
                        </div>
                        <span class="lp-fv-note"><b>24</b> reçues → <b>8</b> en entretien → <b>3</b> acceptées</span>
                    </div>
                </div>
            </article>

            <article class="lp-feature-row flip" data-reveal>
                <div class="lp-feature-copy">
                    <span class="lp-feature-icon"><x-icon name="target" /></span>
                    <h3>Un score de compatibilité, calculé par IA</h3>
                    <p>À chaque dépôt de CV, le moteur compare le profil à la stack technique de l'offre et rend un score de 0 à 100, avec les mots-clés trouvés et manquants. Vous voyez le pourquoi, pas seulement le combien.</p>
                    <span class="lp-fv-note" style="display:inline-flex;gap:6px;align-items:center;margin-top:18px"><b>Score transparent</b> — trouvés : PHP, Laravel, MySQL · manquants : Docker</span>
                </div>
                <div class="lp-feature-visual" aria-hidden="true">
                    <div class="lp-fv lp-fv-score">
                        <div class="lp-fv-ring">
                            <span class="lp-fv-ring-inner"><b class="mono">82</b><span>compatibilité</span></span>
                        </div>
                        <div class="lp-fv-kw">
                            <span class="chip chip-ok">PHP</span>
                            <span class="chip chip-ok">Laravel</span>
                            <span class="chip chip-ok">MySQL</span>
                            <span class="chip chip-no">Docker</span>
                            <span class="chip chip-no">Redis</span>
                        </div>
                    </div>
                </div>
            </article>

            <article class="lp-feature-row" data-reveal>
                <div class="lp-feature-copy">
                    <span class="lp-feature-icon"><x-icon name="calendar" /></span>
                    <h3>Des entretiens planifiés, préparés, évalués</h3>
                    <p>Planifiez un entretien en un clic, laissez l'IA générer des questions techniques depuis la stack de l'offre, puis notez chaque candidat sur trois critères : technique, communication, motivation.</p>
                </div>
                <div class="lp-feature-visual" aria-hidden="true">
                    <div class="lp-fv">
                        <span class="pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Entretien · 10 août</span>
                        <div class="lp-fv-scores">
                            <span class="mono">4/5</span>
                            <span class="mono">5/5</span>
                            <span class="mono">3/5</span>
                        </div>
                        <div class="lp-fv-bar"><i style="width:80%"></i></div>
                        <span class="lp-fv-note">score moyen de l'entretien : <b>4,0 / 5</b></span>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

{{-- ================= HOW IT WORKS ================= --}}
<section class="lp-how">
    <div class="container">
        <div class="lp-section-head center" data-reveal>
            <span class="lp-section-kicker" style="justify-content:center">Comment ça marche</span>
            <h2>Trois étapes, zéro tableur</h2>
        </div>
        <div class="lp-how-grid">
            <div class="lp-how-step rv-1" data-reveal>
                <span class="lp-how-num">01</span>
                <h3>Publiez une offre</h3>
                <p>Titre, stack technique, contrat, salaire et date limite. Le lien est prêt à partager sur vos canaux habituels.</p>
            </div>
            <div class="lp-how-step rv-2" data-reveal>
                <span class="lp-how-num">02</span>
                <h3>Recevez des CV notés</h3>
                <p>Chaque candidature arrive avec son score de compatibilité et le détail des mots-clés, calculés par l'IA.</p>
            </div>
            <div class="lp-how-step rv-3" data-reveal>
                <span class="lp-how-num">03</span>
                <h3>Décidez en entretien</h3>
                <p>Planifiez, évaluez, comparez les profils retenus, puis acceptez ou refusez avec un suivi complet.</p>
            </div>
        </div>
    </div>
</section>

{{-- ================= CTA ================= --}}
<section class="lp-cta">
    <div class="container">
        <div class="lp-cta-card" data-reveal>
            <span class="lp-section-kicker" style="position:relative;justify-content:center">C'est parti</span>
            <h2>Prêt à recruter plus vite ?</h2>
            <p>Publiez une offre, partagez le lien, et laissez le score de compatibilité trier les CV à votre place.</p>
            <div class="lp-cta-actions">
                <a class="btn btn-primary btn-pill" href="{{ route('register') }}">
                    Créer un compte gratuit
                    <span class="lp-cta-arrow">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </a>
                <a class="btn btn-ghost btn-pill" href="{{ route('login') }}">J'ai déjà un compte</a>
            </div>
            <div class="lp-trust" style="position:relative">
                <div class="lp-trust-list">
                    <span class="lp-trust-item"><x-icon name="shield" /> API sécurisée par jeton</span>
                    <span class="lp-trust-item"><x-icon name="check" /> Accès limité par rôle</span>
                    <span class="lp-trust-item"><x-icon name="eye" /> Historique complet des candidatures</span>
                </div>
            </div>

            {{-- Legal strip: complements the layout footer (mimo owns that file) --}}
            <nav class="lp-legal" aria-label="Liens légaux">
                <div class="lp-legal-nav">
                    <span class="lp-legal-disabled" aria-disabled="true" title="Page à venir">Confidentialité</span>
                    <span class="lp-legal-disabled" aria-disabled="true" title="Page à venir">CGU</span>
                    <a href="mailto:contact@smartrecruit.test">Contact</a>
                </div>
                <a class="lp-to-top" href="#top">
                    Retour en haut
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>
                </a>
            </nav>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
  /* Landing scroll reveals — IntersectionObserver only, GPU-safe transform/opacity */
  (function () {
    var els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      for (var i = 0; i < els.length; i++) els[i].classList.add('is-in');
      return;
    }
    if (!('IntersectionObserver' in window)) {
      for (var j = 0; j < els.length; j++) els[j].classList.add('is-in');
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });
    els.forEach(function (el) { io.observe(el); });
  })();
</script>
@endsection
