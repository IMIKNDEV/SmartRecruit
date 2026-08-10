# SmartRecruit API

> Une plateforme de recrutement à matching intelligent — pipeline Kanban, matching automatique CV/offre, tableau de bord analytique, et outils de productivité recruteur, le tout porté par une API REST sécurisée.

---

## Table des matières

- [Aperçu](#aperçu)
- [Tech Stack](#tech-stack)
- [Premiers pas](#premiers-pas)
  - [Prérequis](#prérequis)
  - [Installation](#installation)
  - [Variables d&#39;environnement](#variables-denvironnement)
  - [Lancement](#lancement)
- [Base de données](#base-de-données)
  - [Schéma](#schéma)
  - [Diagrammes](#diagrammes)
- [Authentification](#authentification)
- [Référence API](#référence-api)
  - [Auth](#auth)
  - [Offres d&#39;emploi](#offres-demploi)
  - [Candidatures](#candidatures)
  - [Pipeline Kanban](#pipeline-kanban)
  - [Entretiens](#entretiens)
  - [Tableau de bord](#tableau-de-bord)
  - [Outils de productivité](#outils-de-productivité)
  - [Agent conversationnel](#agent-conversationnel)
- [Moteur de matching](#moteur-de-matching)
- [Files d&#39;attente](#files-dattente)
- [Tests](#tests)
- [CI/CD](#cicd)
- [Déploiement](#déploiement)
- [Concepts clés](#concepts-clés)

---

## Aperçu

SmartRecruit est une plateforme web **recruteur-centric** qui digitalise et sécurise l'intégralité du processus de recrutement — de la publication d'une offre jusqu'à la décision finale.

**Le problème :** Une entreprise de recrutement basée à Agadir gère aujourd'hui ses recrutements via des emails et des fichiers Excel partagés. Résultat : perte de temps, absence de vue d'ensemble, risque d'erreurs, et aucun outil de pilotage objectif.

**La solution :** SmartRecruit remplace ce processus artisanal par un pipeline visuel Kanban, un tableau de bord analytique, des outils de productivité (actions groupées, filtres sauvegardés, comparaison de candidats), et un score de compatibilité candidate/offre calculé automatiquement à l'upload du CV.

**Le workflow type :**

```
Recruteur publie une offre
         ↓
Candidat postule (upload CV PDF + lettre)
         ↓
Score de matching calculé en arrière-plan (Job)
         ↓
Recruteur suit dans le pipeline Kanban
(reçu → entretien → accepté/refusé)
         ↓
Entretien planifié et noté
         ↓
Tableau de bord : funnel, délais, distribution des scores
```

---

## Tech Stack

| Couche                   | Technologie               | Version              |
| ------------------------ | ------------------------- | -------------------- |
| Framework                | Laravel                   | 13.x                 |
| Langage                  | PHP                       | 8.3+                 |
| Base de données (prod)  | MySQL                     | 8.x                  |
| Base de données (tests) | SQLite                    | :memory:             |
| Frontend                 | Blade + Vite              | Vite 8               |
| CSS                      | Tailwind CSS              | 4.x                  |
| Auth API                 | Laravel Sanctum           | Bearer Token         |
| Moteur AI                | `laravel/ai` SDK (Groq) | matching + questions |
| File d'attente           | Database driver           | async jobs           |
| Stockage                 | Disque local              | CVs et avatars       |
| Conteneurisation         | Docker + docker-compose   | PHP 8.3              |
| CI/CD                    | GitHub Actions            | workflow.yml         |
| Tests                    | PHPUnit / Pest            | PHPUnit 12.5+        |

---

## Premiers pas

### Prérequis

- PHP 8.3+ et Composer
- MySQL 8.x (ou Docker Desktop)
- Node.js + NPM (pour le frontend)
- Une clé API Groq (pour le moteur de matching AI)

### Installation

**1. Cloner le dépôt**

```bash
git clone git@github.com:anomalyco/SmartRecruit.git
cd SmartRecruit
```

**2. Installer les dépendances PHP**

```bash
composer install
```

**3. Copier le fichier d'environnement**

```bash
cp .env.example .env
```

**4. Ajouter votre clé Groq API dans `.env`**

```env
GROQ_API_KEY=grok-votre-cle-ici
```

**5. Générer la clé d'application**

```bash
php artisan key:generate
```

**6. Lancer les migrations et les seeders**

```bash
php artisan migrate:fresh --seed
```

**7. Créer le lien symbolique de stockage**

```bash
php artisan storage:link
```

**8. Installer Sanctum**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**9. Démarrer le serveur de développement**

```bash
npm run dev
```

> La commande `npm run dev` lance simultanément le serveur PHP, le worker de queue, les logs et Vite.

### Variables d'environnement

| Variable             | Description          | Défaut                             |
| -------------------- | -------------------- | ----------------------------------- |
| `APP_NAME`         | Nom de l'application | `SmartRecruit`                    |
| `APP_URL`          | URL de base          | `http://localhost`                |
| `DB_CONNECTION`    | Driver BDD           | `sqlite` (dev) / `mysql` (prod) |
| `DB_HOST`          | Hôte BDD            | `127.0.0.1`                       |
| `DB_DATABASE`      | Nom BDD              | `smartrecruit`                    |
| `QUEUE_CONNECTION` | Driver de file       | `database`                        |
| `GROQ_API_KEY`     | Clé API Groq        | —                                  |
| `MAIL_MAILER`      | Mail driver          | `log` (dev) / `smtp` (prod)     |

### Lancement

```bash
# Tout-en-un (serveur + queue + logs + Vite)
npm run dev

# Ou manuellement :
php artisan serve
php artisan queue:work  # terminal séparé
php artisan pail        # logs en temps réel
npm run dev             # frontend

# Tester
php artisan test

# Formater le code
./vendor/bin/pint

# Docker
docker-compose up -d
```

---

## Base de données

### Schéma

Le schéma est organisé autour de 9 tables métier :

```
users
  id, name, email, password, role (recruiter|candidate), avatar

job_offers
  id, recruiter_id (FK), title, description, tech_stack,
  contract_type, salary, deadline, status, deleted_at (soft)

applications
  id, candidate_id (FK), job_offer_id (FK), cv_path, cover_letter,
  tags (JSON), status (received|interview|accepted|refused),
  notes, comments
  UNIQUE (candidate_id, job_offer_id)

interviews
  id, application_id (FK), scheduled_at, link, status,
  score_technique (1-5), score_communication (1-5), score_motivation (1-5)

application_analysis
  id, application_id (FK, unique), matching_score,
  matched_keywords (JSON), missing_keywords (JSON)

badges
  id, candidate_id (FK), type (cv_complet|high_match|interview_passed)
  UNIQUE (candidate_id, type)

saved_filters
  id, recruiter_id (FK), name, criteria (JSON)

agent_conversations
  id, user_id (FK), context_type, context_id, status

agent_conversation_messages
  id, agent_conversation_id (FK), role, content, metadata (JSON)
```

**Relations clés :**

```
User ──< JobOffer (recruiter)
User ──< Application (candidate)
JobOffer ──< Application
Application ──< Interview
Application ── ApplicationAnalysis (one-to-one)
Application ──< Badge
User ──< SavedFilter
User ──< AgentConversation
AgentConversation ──< AgentConversationMessage
```

### Diagrammes

| Diagramme                            | Fichier                                |
| ------------------------------------ | -------------------------------------- |
| MCD (Modèle Conceptuel de Données) | `docs/MCD.png`                       |
| MLD (Modèle Logique de Données)    | `docs/MLD.png`                       |
| Architecture générale              | `docs/smartrecruit_architecture.png` |

---

## Authentification

SmartRecruit utilise **Laravel Sanctum** avec des tokens Bearer.

**Token Bearer (stateless) :**

1. Inscrivez-vous ou connectez-vous pour recevoir un `token`
2. Incluez-le dans chaque requête protégée :

```
Authorization: Bearer <votre-token>
Accept: application/json
```

**Rôles :** Chaque utilisateur possède un rôle fixe (`recruiter` ou `candidate`) défini à l'inscription et non modifiable. Les permissions sont contrôlées via Policies et un middleware de rôle.

---

## Référence API

**URL de base :** `http://localhost/api`

### Auth

#### Inscription

```
POST /api/register
```

```json
{
  "name": "Ayoub Idbelhaj",
  "email": "ayoub@smartrecruit.ma",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "recruiter"
}
```

**Réponse `201 Created` :**

```json
{
  "user": {
    "id": 1,
    "name": "Ayoub Idbelhaj",
    "email": "ayoub@smartrecruit.ma",
    "role": "recruiter"
  },
  "token": "1|abc123..."
}
```

#### Connexion

```
POST /api/login
```

```json
{
  "email": "ayoub@smartrecruit.ma",
  "password": "password123"
}
```

**Réponse `200 OK` :**

```json
{
  "user": { "id": 1, "name": "Ayoub Idbelhaj", "email": "ayoub@smartrecruit.ma", "role": "recruiter" },
  "token": "2|def456..."
}
```

**Identifiants invalides → `401` :**

```json
{ "message": "Invalid credentials." }
```

#### Déconnexion

```
POST /api/logout
```

🔒 *Authentification requise*

**Réponse `204 No Content`**

#### Profil

```
GET /api/user
```

🔒 *Authentification requise*

```
PUT /api/user/profile
```

🔒 *Authentification requise*

```json
{
  "name": "Ayoub",
  "email": "ayoub@smartrecruit.ma",
  "password": "newpassword"
}
```

---

### Offres d'emploi

#### Lister les offres (public)

```
GET /api/job-offers?page=1&per_page=15&contract_type=CDI&search=Laravel&status=active
```

**Réponse `200 OK` :**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Développeur Laravel",
      "description": "Nous recherchons un développeur Laravel expérimenté...",
      "tech_stack": "PHP, Laravel, MySQL, Docker",
      "tech_stack_array": ["PHP", "Laravel", "MySQL", "Docker"],
      "contract_type": "CDI",
      "salary": 15000.00,
      "deadline": "2026-08-15",
      "status": "active",
      "applications_count": 12,
      "recruiter": { "id": 1, "name": "Ayoub Idbelhaj" },
      "created_at": "2026-07-13T10:00:00Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "total": 42 }
}
```

#### Voir une offre (public)

```
GET /api/job-offers/{id}
```

**Réponse `200 OK`** — structure identique à l'élément de la liste.

#### Créer une offre

```
POST /api/job-offers
```

🔒 *Rôle recruteur requis*

```json
{
  "title": "Développeur Laravel",
  "description": "Nous recherchons un développeur Laravel avec 3+ ans d'expérience pour rejoindre notre équipe technique. Vous serez responsable du développement de fonctionnalités backend, de l'optimisation des performances et de la maintenance de l'architecture existante.",
  "tech_stack": "PHP, Laravel, MySQL, Docker, Git",
  "contract_type": "CDI",
  "salary": 15000,
  "deadline": "2026-08-15"
}
```

**Réponse `201 Created`**

#### Modifier une offre

```
PUT /api/job-offers/{id}
```

🔒 *Propriétaire uniquement*

**Réponse `200 OK`**

#### Archiver une offre (soft delete)

```
DELETE /api/job-offers/{id}
```

🔒 *Propriétaire uniquement*

**Réponse `204 No Content`**

---

### Candidatures

#### Postuler (candidat)

```
POST /api/job-offers/{id}/apply
```

🔒 *Rôle candidat requis*

**Multipart form :**

| Champ            | Type | Règles                     |
| ---------------- | ---- | --------------------------- |
| `cv`           | File | PDF, max 5 Mo, requis       |
| `cover_letter` | Text | 20-5000 caractères, requis |

**Réponse `201 Created` :**

```json
{
  "data": {
    "id": 15,
    "status": "received",
    "cv_path": "cvs/42/1721234567_cv_ayoub.pdf",
    "cover_letter": "Je suis très intéressé par cette position...",
    "candidate": { "id": 42, "name": "Ayoub Idbelhaj" },
    "job_offer": { "id": 1, "title": "Développeur Laravel" },
    "created_at": "2026-07-20T14:30:00Z"
  }
}
```

> Le score de matching est calculé en arrière-plan (statut `202` logique). Interrogez `GET /api/applications/{id}` pour voir le résultat une fois le job terminé.

**Double candidature → `422` :**

```json
{
  "message": "Vous avez déjà postulé à cette offre.",
  "errors": { "candidate_id": ["Vous avez déjà postulé à cette offre."] }
}
```

#### Mes candidatures (candidat)

```
GET /api/applications
```

🔒 *Rôle candidat requis*

**Réponse `200 OK` :**

```json
{
  "data": [
    {
      "id": 15,
      "matching_score": 75.5,
      "matched_keywords": ["PHP", "Laravel", "MySQL"],
      "missing_keywords": ["Docker"],
      "status": "interview",
      "job_offer": { "title": "Développeur Laravel", "contract_type": "CDI" },
      "created_at": "2026-07-20T14:30:00Z"
    }
  ]
}
```

#### Voir une candidature

```
GET /api/applications/{id}
```

🔒 *Propriétaire (candidat) ou recruteur de l'offre*

#### Candidatures d'une offre (recruteur)

```
GET /api/job-offers/{id}/applications?status=received&min_score=50
```

🔒 *Propriétaire de l'offre*

**Réponse `200 OK`** — triée par `matching_score` DESC.

---

### Pipeline Kanban

Le pipeline suit une machine à états stricte :

```
received ──▶ interview ──▶ accepted (terminal)
                         ──▶ refused  (terminal)
received ──▶ refused (sans entretien)
```

**Règles :**

- `accepted` et `refused` sont des **états terminaux** — aucun mouvement sortant n'est autorisé
- Impossible de revenir en arrière (`accepted` → `received` interdit)
- `received` → `refused` autorisé (rejet direct sans entretien)

#### Changer le statut

```
PUT /api/applications/{id}/status
```

🔒 *Recruteur propriétaire de l'offre*

```json
{ "status": "interview" }
```

**Réponse `200 OK`**

**Transition invalide → `422` :**

```json
{
  "message": "Cannot transition from accepted to received."
}
```

#### Changement de statut groupé

```
PUT /api/applications/status/batch
```

🔒 *Recruteur*

```json
{
  "ids": [12, 13, 14],
  "status": "refused"
}
```

**Réponse `200 OK` :**

```json
{
  "data": [
    { "id": 12, "status": "refused" },
    { "id": 13, "status": "refused" },
    { "id": 14, "status": "refused" }
  ],
  "updated": 3,
  "skipped": []
}
```

> Chaque candidature est vérifiée individuellement (propriété + transition valide).

#### Notes et commentaires

```
PUT /api/applications/{id}/notes
```

🔒 *Recruteur propriétaire*

```json
{
  "notes": "Candidat très technique mais manque d'expérience en gestion de projet",
  "comments": "Nous vous recontacterons sous 48h."
}
```

**Réponse `200 OK`**

#### Tags rapides

```
PUT /api/applications/{id}/tags
```

🔒 *Recruteur propriétaire*

```json
{
  "tags": ["prioritaire", "entretien_planifie"]
}
```

Tags disponibles : `a_relancer`, `prioritaire`, `reserve`, `entretien_planifie`.

---

### Entretiens

#### Planifier un entretien

```
POST /api/applications/{id}/interviews
```

🔒 *Recruteur propriétaire*

```json
{
  "scheduled_at": "2026-07-25 14:00:00",
  "link": "https://meet.google.com/abc-defg-hij"
}
```

**Réponse `201 Created`**

#### Compléter un entretien (notation)

```
PUT /api/interviews/{id}/complete
```

🔒 *Recruteur*

```json
{
  "score_technique": 4,
  "score_communication": 5,
  "score_motivation": 3
}
```

**Réponse `200 OK` :**

```json
{
  "data": {
    "id": 1,
    "status": "completed",
    "score_technique": 4,
    "score_communication": 5,
    "score_motivation": 3,
    "average_score": 4.0
  }
}
```

#### Annuler un entretien

```
PUT /api/interviews/{id}/cancel
```

**Réponse `200 OK`**

#### Lister les entretiens

```
GET /api/applications/{id}/interviews
```

🔒 *Recruteur propriétaire*

---

### Tableau de bord

```
GET /api/dashboard/stats
```

🔒 *Rôle recruteur requis*

**Réponse `200 OK` :**

```json
{
  "funnels": [
    {
      "job_offer_id": 1, "title": "Développeur Laravel",
      "received": 24, "interview": 8, "accepted": 3, "refused": 13,
      "rates": { "received": 100.0, "interview": 33.3, "accepted": 12.5, "refused": 54.2 }
    }
  ],
  "time_to_hire": {
    "global_avg_days": 9.4,
    "by_offer": [{ "job_offer_id": 1, "avg_days": 7.1 }]
  },
  "score_distribution": { ">80": 5, "50-80": 12, "<50": 7 },
  "recent_activity": [
    { "type": "application", "label": "Nouvelle candidature pour Développeur Laravel", "at": "2026-07-20T14:30:00Z" }
  ],
  "offer_comparison": [
    { "job_offer_id": 1, "interview_to_accepted": 37.5, "recruiter_avg": 30.1 }
  ],
  "pending_tasks": {
    "interviews_to_evaluate": 2,
    "applications_pending_over_7_days": 4
  }
}
```

---

### Outils de productivité

#### Filtres sauvegardés

```
GET /api/saved-filters
POST /api/saved-filters
PUT /api/saved-filters/{id}
DELETE /api/saved-filters/{id}
```

🔒 *Rôle recruteur requis*

```json
// POST request
{
  "name": "Devs Laravel score > 80",
  "criteria": {
    "min_score": 80,
    "tech_stack": ["PHP", "Laravel"],
    "contract_type": "CDI",
    "status": "received"
  }
}
```

#### Comparaison de candidats

```
POST /api/applications/compare
```

🔒 *Recruteur*

```json
{
  "ids": [12, 15, 18]
}
```

**Réponse `200 OK`** — vue côte-à-côte des scores, mots-clés, notes d'entretien.

#### Shortlist (Top 5)

```
GET /api/job-offers/{id}/shortlist
GET /api/job-offers/{id}/shortlist/export
```

🔒 *Propriétaire de l'offre*

Export disponible en CSV et PDF.

#### Suggestions de profils similaires

```
GET /api/applications/{id}/suggestions
```

🔒 *Recruteur*

Propose d'autres candidats au profil proche correspondant à d'autres offres ouvertes.

#### Modèles de réponse rapide

```
GET /api/reply-templates
PUT /api/reply-templates/{key}
```

🔒 *Recruteur*

Templates disponibles : `follow_up` (relance), `standard_refusal` (refus standard).

---

### Agent conversationnel

#### Générer des questions d'entretien

```
POST /api/applications/{id}/generate-questions
```

🔒 *Recruteur*

**Réponse `200 OK` :**

```json
{
  "questions": "1. \"Can you describe a complex Laravel query you optimized and how you identified the bottleneck?\"\n2. \"How do you handle database migrations in a team environment?...\"",
  "conversation_id": 3
}
```

#### Conversations AI

```
GET /api/agent-conversations
POST /api/agent-conversations
GET /api/agent-conversations/{id}/messages
POST /api/agent-conversations/{id}/messages
```

---

## Moteur de matching

Le score de compatibilité est calculé par un moteur **propulsé par l'IA** via le SDK `laravel/ai` (driver Groq) :

```
1. Les mots-clés de l'offre sont extraits de tech_stack
2. Le texte du CV est extrait du PDF (parser local)
3. L'IA (Groq) reçoit la stack technique + le texte du CV
4. L'IA retourne un score (0-100) + les mots-clés trouvés et manquants
5. Le résultat est stocké dans application_analysis (transparence recruteur)
6. Le tout s'exécute de manière asynchrone (Job) pour ne pas bloquer la requête
```

**Ce que voit le recruteur :**

> **Score : 75/100**
> ✅ Trouvés : PHP, Laravel, MySQL
> ❌ Manquants : Docker, Redis

**MatchingAgent :**

```php
class MatchingAgent extends Agent
{
    protected string $driver = 'groq';
    protected string $model = 'llama-3.3-70b-versatile';

    public function score(string $techStack, string $cvText): array
    {
        return $this->withResponseFormat(['type' => 'json_object'])->prompt(
            "Required tech stack: {$techStack}\n\n"
            . "Candidate CV:\n{$cvText}\n\n"
            . "Score the candidate from 0 to 100 and list which required keywords "
            . "were found in the CV and which are missing. "
            . "Respond ONLY with strict JSON: "
            . "{\"score\": <number>, \"matched_keywords\": [\"...\"], \"missing_keywords\": [\"...\"]}."
        );
    }
}
```

> **Tests :** `MatchingAgent::fake([...])` permet de tester sans appel API réel, sans clé Groq en CI.

---

## Files d'attente

Le moteur de matching et les notifications sont traités de manière asynchrone via la queue database.

**Démarrer le worker :**

```bash
php artisan queue:work
```

**Jobs principaux :**

| Job                           | Déclencheur                            | Action                                 |
| ----------------------------- | --------------------------------------- | -------------------------------------- |
| `CalculateMatchingScoreJob` | Création d'une candidature             | Calcule le score + mots-clés via l'IA |
| `ApplicationStatusMail`     | Changement de statut (accepté/refusé) | Envoie un email au candidat            |

---

## Tests

```bash
# Lancer tous les tests
php artisan test

# Ou avec Pest
./vendor/bin/pest
```

**Ce qui est testé :**

| Type              | Contenu                                             |
| ----------------- | --------------------------------------------------- |
| **Feature** | Auth (register, login, logout, profile)             |
|                   | Offres d'emploi (CRUD, permissions)                 |
|                   | Candidatures (apply, status, batch, notes, tags)    |
|                   | Entretiens (planification, complétion, annulation) |
|                   | Tableau de bord (stats, aggregation)                |
|                   | Filtres sauvegardés, comparaison, shortlist        |
| **Unit**    | MatchingService (score et mots-clés)               |
|                   | BadgeService (attribution automatique)              |
|                   | SuggestionService (profils similaires)              |

---

## CI/CD

**GitHub Actions** — pipeline automatique à chaque push/PR sur `main` :

```
Installation des dépendances
         ↓
Migrations (MySQL)
         ↓
Tests (Feature + Unit)
         ↓
Vérification du style (Pint)
```

Le pipeline utilise un service MySQL 8.0 dans le workflow. Les tests sont exécutés avec `php artisan test`.

---

## Déploiement

### Docker

```bash
# Lancer l'application complète (app + MySQL + queue worker)
docker-compose up -d

# Arrêter
docker-compose down
```

L'infrastructure comprend 3 services :

- **app** — serveur PHP (port 8000)
- **mysql** — base de données (port 3306)
- **queue** — worker de file d'attente

### Checklist pré-déploiement

- [ ] `APP_DEBUG=false` et `APP_ENV=production` dans `.env`
- [ ] Clé d'application générée : `php artisan key:generate`
- [ ] Migrations exécutées : `php artisan migrate --force`
- [ ] Lien de stockage : `php artisan storage:link`
- [ ] Cache optimisé : `php artisan optimize`
- [ ] Worker de queue actif

---

## Concepts clés

**Pourquoi `202 Accepted` (asynchrone) pour le matching score ?**
L'appel à l'IA Groq peut prendre 5-15 secondes. En traitant le score en arrière-plan via un Job, la réponse à la candidature est instantanée.

**Pourquoi `job_offers` et pas `jobs` ?**
Laravel utilise déjà une table `jobs` pour la file d'attente. Nommer la table des offres `job_offers` évite un conflit classique.

**Pourquoi un moteur de matching avec IA plutôt qu'un simple calcul de mots-clés en PHP ?**
L'IA (Groq avec Llama 3.3) comprend le contexte — un CV peut mentionner "PHP 8" sans que le mot-clé exact "PHP" apparaisse. L'inférence sémantique améliore la précision du score. Le SDK `laravel/ai` avec le format `json_object` garantit une sortie structurée et prédictible.

**Pourquoi les badges sont-ils recruteur-only ?**
Les badges (`cv_complet`, `high_match`, `interview_passed`) sont des signaux de tri rapide pour le recruteur dans son pipeline, pas des récompenses visibles par le candidat. Pas d'endpoint API candidate pour les badges.

**Pourquoi Sanctum et pas Jetstream/Fortify ?**
SmartRecruit est une API REST consommée par un frontend Blade et potentiellement par des clients externes. Sanctum avec tokens Bearer est le choix standard pour une API stateless.

**Pourquoi chaque statut est validé individuellement dans les actions groupées ?**
Une action groupée ne peut pas échouer en totalité à cause d'un seul élément invalide. Chaque candidature est vérifiée séparément (propriété + transition valide), et les éléments problématiques sont reportés dans la liste `skipped`.

---

*SmartRecruit — Construit avec Laravel 13 · PHP 8.3 · Groq · Docker*

**Projet de fin de formation DWWM/Backend — Ayoub Idbelhaj**

[Documentation complète (cahier des charges)](docs/cahier-des-charges.md)
