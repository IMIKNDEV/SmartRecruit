# SmartRecruit

> Plateforme de recrutement à matching intelligent — pipeline Kanban, scoring CV/offre par IA, tableau de bord analytique et outils de productivité recruteur, portés par une API REST sécurisée.

<p>
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Vite-8-646CFF?style=flat&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/Groq-AI_Matching-F55036?style=flat&logo=groq&logoColor=white" alt="Groq" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/PHPUnit-Pest-4E4E4E?style=flat&logo=php&logoColor=white" alt="PHPUnit / Pest" />
  <img src="https://img.shields.io/badge/GitHub_Actions-CI/CD-2088FF?style=flat&logo=githubactions&logoColor=white" alt="GitHub Actions" />
</p>

---

## Démo

<video src="https://github.com/user-attachments/assets/8a7b04b6-a63e-4f1a-bd29-a22b47a087b9" controls width="700"></video>

---

## Le problème

Une entreprise de recrutement gère aujourd'hui ses processus via emails et fichiers Excel partagés : perte de temps, aucune vue d'ensemble, aucun outil de pilotage objectif.

**SmartRecruit** remplace ça par un pipeline visuel, un scoring automatique, et un tableau de bord analytique.

```
Recruteur publie une offre → Candidat postule (CV + lettre)
      → Score de matching calculé en arrière-plan (IA)
      → Suivi dans le pipeline Kanban (reçu → entretien → accepté/refusé)
      → Entretien planifié et noté → Tableau de bord (funnel, délais, scores)
```

## Fonctionnalités

- **Pipeline Kanban** avec machine à états stricte (statuts terminaux, transitions validées)
- **Matching IA** (Groq / Llama 3.3) — score de compatibilité CV/offre, mots-clés trouvés et manquants
- **Tableau de bord** — funnel de conversion, délai moyen d'embauche, distribution des scores
- **Outils recruteur** — filtres sauvegardés, comparaison de candidats, shortlist exportable (CSV/PDF), tags rapides, modèles de réponse
- **Entretiens** — planification, notation multi-critères (technique/communication/motivation)
- **Agent conversationnel** — génération de questions d'entretien personnalisées
- **API REST sécurisée** — Laravel Sanctum, rôles recruteur/candidat, traitement asynchrone (queues)

---

## Démarrage rapide

### Prérequis

PHP 8.3+, Composer, MySQL 8.x (ou Docker), Node.js + NPM, une clé API Groq.

### Installation

```bash
git clone git@github.com:anomalyco/SmartRecruit.git
cd SmartRecruit

composer install
cp .env.example .env
php artisan key:generate

# Ajouter votre clé dans .env :
# GROQ_API_KEY=votre-cle-ici

php artisan migrate:fresh --seed
php artisan storage:link

composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

npm install
npm run dev   # lance serveur + queue worker + logs + Vite en simultané
```

### Variables d'environnement essentielles

| Variable             | Description        | Défaut               |
| --------------------- | ------------------- | ---------------------- |
| `APP_URL`            | URL de base         | `http://localhost`   |
| `DB_CONNECTION`      | Driver BDD          | `mysql` (`sqlite` en test) |
| `QUEUE_CONNECTION`   | Driver de file      | `database`            |
| `GROQ_API_KEY`       | Clé API Groq        | —                      |

### Avec Docker

```bash
docker-compose up -d
```
Services : `app` (port 8000), `mysql` (port 3306), `queue` (worker).

### Tests

```bash
php artisan test
```

---

## Base de données

9 tables métier : `users`, `job_offers`, `applications`, `interviews`, `application_analysis`, `badges`, `saved_filters`, `agent_conversations`, `agent_conversation_messages`.

Diagrammes : [MCD](docs/MCD.png) · [MLD](docs/MLD.png) · [Architecture générale](docs/smartrecruit_architecture.png)

---

## Documentation API

Authentification via **Bearer Token** (Laravel Sanctum) :
```
Authorization: Bearer <votre-token>
```

Aperçu rapide des endpoints principaux :

| Ressource     | Endpoints clés                                                        |
| ------------- | ------------------------------------------------------------------------ |
| Auth          | `POST /register` · `POST /login` · `GET /user`                      |
| Offres        | `GET /job-offers` · `POST /job-offers` · `PUT/DELETE /job-offers/{id}` |
| Candidatures  | `POST /job-offers/{id}/apply` · `PUT /applications/{id}/status`       |
| Pipeline      | `PUT /applications/status/batch` · `PUT /applications/{id}/tags`      |
| Entretiens    | `POST /applications/{id}/interviews` · `PUT /interviews/{id}/complete` |
| Dashboard     | `GET /dashboard/stats`                                                  |
| Agent IA      | `POST /applications/{id}/generate-questions`                            |

📄 **Référence complète (requêtes, réponses, erreurs) : [docs/API.md](docs/API.md)**

---

## Moteur de matching

Le score de compatibilité est calculé de façon asynchrone via le SDK `laravel/ai` (driver Groq, modèle `llama-3.3-70b-versatile`) :

```
Mots-clés de l'offre extraits → Texte du CV parsé → Groq compare les deux
→ Score 0-100 + mots-clés trouvés/manquants → Stocké et affiché au recruteur
```

Traitement en **Job** asynchrone pour ne pas bloquer la candidature (l'appel IA peut prendre 5-15s).

<details>
<summary><strong>Pourquoi ces choix techniques ?</strong> (cliquer pour développer)</summary>

- **Job asynchrone pour le matching** — l'appel Groq peut prendre 5-15s ; la candidature répond instantanément pendant que le score se calcule en arrière-plan.
- **IA plutôt qu'un simple matching de mots-clés** — Llama 3.3 comprend le contexte ("PHP 8" ≈ "PHP"), ce qu'un `str_contains` ne peut pas faire.
- **`job_offers` et non `jobs`** — Laravel réserve déjà `jobs` pour sa table de file d'attente.
- **Sanctum plutôt que Jetstream/Fortify** — SmartRecruit est une API stateless consommée par un frontend Blade et potentiellement des clients externes ; les tokens Bearer sont le standard pour ce cas.
- **Chaque statut validé individuellement dans les actions groupées** — un élément invalide ne doit pas faire échouer tout le lot ; les éléments problématiques sont reportés dans `skipped`.

</details>

---

## CI/CD

GitHub Actions à chaque push/PR sur `main` : installation → migrations (MySQL) → tests (`php artisan test`) → style (Pint).

---

*Construit avec Laravel 13 · PHP 8.3 · Groq · Docker*
**Projet de fin de formation DWWM/Backend — Ayoub Idbelhaj**
Supervisé par Abderrahmane Merradou

[Cahier des charges complet](docs/cahier-des-charges.md)
