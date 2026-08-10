
# SmartRecruit — Référence API complète

**URL de base :** `http://localhost/api`

Authentification via Bearer Token (Laravel Sanctum) :

```
Authorization: Bearer <votre-token>
Accept: application/json
```

## Table des matières

- [Auth](#auth)
- [Offres d'emploi](#offres-demploi)
- [Candidatures](#candidatures)
- [Pipeline Kanban](#pipeline-kanban)
- [Entretiens](#entretiens)
- [Tableau de bord](#tableau-de-bord)
- [Outils de productivité](#outils-de-productivité)
- [Agent conversationnel](#agent-conversationnel)

---

## Auth

### Inscription

```
POST /register
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

**`201 Created`**

```json
{
  "user": { "id": 1, "name": "Ayoub Idbelhaj", "email": "ayoub@smartrecruit.ma", "role": "recruiter" },
  "token": "1|abc123..."
}
```

### Connexion

```
POST /login
```

```json
{ "email": "ayoub@smartrecruit.ma", "password": "password123" }
```

**`200 OK`** — même structure que ci-dessus.
**`401`** identifiants invalides : `{ "message": "Invalid credentials." }`

### Déconnexion

```
POST /logout
```

🔒 Auth requise — **`204 No Content`**

### Profil

```
GET /user
PUT /user/profile
```

🔒 Auth requise

```json
{ "name": "Ayoub", "email": "ayoub@smartrecruit.ma", "password": "newpassword" }
```

---

## Offres d'emploi

### Lister (public)

```
GET /job-offers?page=1&per_page=15&contract_type=CDI&search=Laravel&status=active
```

**`200 OK`**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Développeur Laravel",
      "description": "...",
      "tech_stack_array": ["PHP", "Laravel", "MySQL", "Docker"],
      "contract_type": "CDI",
      "salary": 15000.00,
      "deadline": "2026-08-15",
      "status": "active",
      "applications_count": 12,
      "recruiter": { "id": 1, "name": "Ayoub Idbelhaj" }
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "total": 42 }
}
```

### Voir une offre (public)

```
GET /job-offers/{id}
```

### Créer / Modifier / Archiver

```
POST   /job-offers        🔒 rôle recruteur
PUT    /job-offers/{id}   🔒 propriétaire
DELETE /job-offers/{id}   🔒 propriétaire (soft delete)
```

```json
{
  "title": "Développeur Laravel",
  "description": "Nous recherchons un développeur Laravel avec 3+ ans d'expérience...",
  "tech_stack": "PHP, Laravel, MySQL, Docker, Git",
  "contract_type": "CDI",
  "salary": 15000,
  "deadline": "2026-08-15"
}
```

---

## Candidatures

### Postuler (candidat)

```
POST /job-offers/{id}/apply
```

🔒 rôle candidat — multipart form :


| Champ          | Type | Règles                     |
| ---------------- | ------ | ----------------------------- |
| `cv`           | File | PDF, max 5 Mo, requis       |
| `cover_letter` | Text | 20-5000 caractères, requis |

**`201 Created`**

```json
{
  "data": {
    "id": 15,
    "status": "received",
    "cv_path": "cvs/42/1721234567_cv_ayoub.pdf",
    "candidate": { "id": 42, "name": "Ayoub Idbelhaj" },
    "job_offer": { "id": 1, "title": "Développeur Laravel" }
  }
}
```

> Le score de matching est calculé en arrière-plan. Interrogez `GET /applications/{id}` une fois le job terminé.

**`422`** double candidature :

```json
{ "message": "Vous avez déjà postulé à cette offre." }
```

### Mes candidatures (candidat)

```
GET /applications
```

**`200 OK`**

```json
{
  "data": [
    {
      "id": 15,
      "matching_score": 75.5,
      "matched_keywords": ["PHP", "Laravel", "MySQL"],
      "missing_keywords": ["Docker"],
      "status": "interview",
      "job_offer": { "title": "Développeur Laravel", "contract_type": "CDI" }
    }
  ]
}
```

### Voir une candidature

```
GET /applications/{id}
```

🔒 propriétaire (candidat) ou recruteur de l'offre

### Candidatures d'une offre (recruteur)

```
GET /job-offers/{id}/applications?status=received&min_score=50
```

🔒 propriétaire — triée par `matching_score` DESC

---

## Pipeline Kanban

Machine à états stricte :

```
received ──▶ interview ──▶ accepted (terminal)
                         ──▶ refused  (terminal)
received ──▶ refused (rejet direct)
```

- `accepted`/`refused` sont **terminaux**, pas de retour arrière
- `received → refused` autorisé directement

### Changer le statut

```
PUT /applications/{id}/status
```

🔒 recruteur propriétaire

```json
{ "status": "interview" }
```

**`422`** transition invalide : `{ "message": "Cannot transition from accepted to received." }`

### Changement groupé

```
PUT /applications/status/batch
```

🔒 recruteur

```json
{ "ids": [12, 13, 14], "status": "refused" }
```

**`200 OK`**

```json
{
  "data": [{ "id": 12, "status": "refused" }, { "id": 13, "status": "refused" }],
  "updated": 3,
  "skipped": []
}
```

### Notes et commentaires

```
PUT /applications/{id}/notes
```

🔒 recruteur propriétaire

```json
{ "notes": "Candidat très technique mais manque d'expérience en gestion de projet", "comments": "Nous vous recontacterons sous 48h." }
```

### Tags rapides

```
PUT /applications/{id}/tags
```

🔒 recruteur propriétaire

```json
{ "tags": ["prioritaire", "entretien_planifie"] }
```

Disponibles : `a_relancer`, `prioritaire`, `reserve`, `entretien_planifie`

---

## Entretiens

### Planifier

```
POST /applications/{id}/interviews
```

🔒 recruteur propriétaire

```json
{ "scheduled_at": "2026-07-25 14:00:00", "link": "https://meet.google.com/abc-defg-hij" }
```

### Compléter (notation)

```
PUT /interviews/{id}/complete
```

🔒 recruteur

```json
{ "score_technique": 4, "score_communication": 5, "score_motivation": 3 }
```

**`200 OK`**

```json
{ "data": { "id": 1, "status": "completed", "average_score": 4.0 } }
```

### Annuler / Lister

```
PUT /interviews/{id}/cancel
GET /applications/{id}/interviews   🔒 recruteur propriétaire
```

---

## Tableau de bord

```
GET /dashboard/stats
```

🔒 rôle recruteur

```json
{
  "funnels": [
    { "job_offer_id": 1, "title": "Développeur Laravel", "received": 24, "interview": 8, "accepted": 3, "refused": 13,
      "rates": { "received": 100.0, "interview": 33.3, "accepted": 12.5, "refused": 54.2 } }
  ],
  "time_to_hire": { "global_avg_days": 9.4, "by_offer": [{ "job_offer_id": 1, "avg_days": 7.1 }] },
  "score_distribution": { ">80": 5, "50-80": 12, "<50": 7 },
  "recent_activity": [{ "type": "application", "label": "Nouvelle candidature pour Développeur Laravel" }],
  "offer_comparison": [{ "job_offer_id": 1, "interview_to_accepted": 37.5, "recruiter_avg": 30.1 }],
  "pending_tasks": { "interviews_to_evaluate": 2, "applications_pending_over_7_days": 4 }
}
```

---

## Outils de productivité

### Filtres sauvegardés

```
GET/POST/PUT/DELETE /saved-filters
```

🔒 rôle recruteur

```json
{
  "name": "Devs Laravel score > 80",
  "criteria": { "min_score": 80, "tech_stack": ["PHP", "Laravel"], "contract_type": "CDI", "status": "received" }
}
```

### Comparaison de candidats

```
POST /applications/compare
```

🔒 recruteur

```json
{ "ids": [12, 15, 18] }
```

**`200 OK`** — vue côte-à-côte des scores, mots-clés, notes d'entretien

### Shortlist

```
GET /job-offers/{id}/shortlist
GET /job-offers/{id}/shortlist/export
```

🔒 propriétaire — export CSV et PDF

### Suggestions de profils similaires

```
GET /applications/{id}/suggestions
```

🔒 recruteur — propose des candidats au profil proche pour d'autres offres ouvertes

### Modèles de réponse

```
GET /reply-templates
PUT /reply-templates/{key}
```

🔒 recruteur — templates : `follow_up`, `standard_refusal`

---

## Agent conversationnel

### Générer des questions d'entretien

```
POST /applications/{id}/generate-questions
```

🔒 recruteur
**`200 OK`**

```json
{
  "questions": "1. \"Can you describe a complex Laravel query you optimized...\"\n2. \"How do you handle database migrations in a team environment?\"",
  "conversation_id": 3
}
```

### Conversations AI

```
GET  /agent-conversations
POST /agent-conversations
GET  /agent-conversations/{id}/messages
POST /agent-conversations/{id}/messages
```
