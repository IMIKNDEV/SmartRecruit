# SmartRecruit — Cahier des Charges

**CAHIER DES CHARGES**

**SmartRecruit — Plateforme de Recrutement à Matching Intelligent**

| Champ             | Valeur                                                               |
| ----------------- | -------------------------------------------------------------------- |
| Porteur du projet | Ayoub Idbelhaj                                                       |
| Formation         | DWWM / Backend — Laravel, PHP, MySQL, REST API                      |
| Clé Jira         | SR (SmartRecruit) — https://gamecafemanager.atlassian.net/browse/SR |
| Début du projet  | 13/07/2026                                                           |
| Date de rendu     | 07/08/2026                                                           |
| Soutenance        | À partir du 10/08/2026                                              |

## 1. Contexte et Problématique

Une entreprise de recrutement basée à Agadir gère aujourd'hui l'intégralité de son processus d'embauche via des échanges d'emails et des fichiers Excel partagés. Ce fonctionnement artisanal génère plusieurs points de friction récurrents, en particulier pour les recruteurs qui pilotent le processus au quotidien :

- Perte de temps considérable pour trier manuellement les CV reçus par email et évaluer leur adéquation avec le poste ;
- Absence de vue d'ensemble sur l'avancement de chaque candidature (aucun suivi centralisé du pipeline de recrutement) ;
- Aucun indicateur de performance sur le processus de recrutement lui-même (délai de traitement, taux de conversion, qualité des candidatures) ;
- Risque d'erreurs et de doublons dans le suivi Excel (candidature perdue, statut non mis à jour, relance oubliée) ;
- Aucune traçabilité des échanges (notes internes, retours aux candidats) ni des évaluations d'entretien ;
- Aucun outil pour objectiver la pertinence d'une candidature par rapport aux compétences demandées, ni pour comparer rapidement plusieurs profils entre eux.

**Problématique :** Comment centraliser et automatiser le processus de recrutement — de la publication d'une offre jusqu'à la décision finale — tout en donnant aux recruteurs de vrais outils de pilotage et de décision objective, sans dépendre d'outils ou d'API externes payantes ?

SmartRecruit répond à cette problématique en proposant une plateforme web pensée avant tout pour le recruteur : un pipeline visuel type Kanban, un tableau de bord analytique sur la performance du recrutement, des outils de productivité (actions groupées, filtres sauvegardés, comparaison de candidats) et un score de compatibilité candidat/offre calculé automatiquement à l'upload du CV — présenté comme une fonctionnalité "IA", mais reposant sur un algorithme de correspondance par mots-clés 100 % maîtrisé en PHP, sans appel à un service d'intelligence artificielle externe.

## 2. Objectifs du Projet

- **Objectif métier** — digitaliser et fiabiliser le processus de recrutement de l'entreprise, de la publication d'offre à la décision finale, en donnant au recruteur une vision claire et des outils de pilotage sur son activité.
- **Objectif fonctionnel** — fournir un pipeline de suivi (Kanban), un tableau de bord analytique (funnel, délai de traitement, distribution des scores), des outils de productivité recruteur (actions groupées, filtres sauvegardés, comparaison de candidats), un score de matching automatique et transparent, un module d'entretiens noté, et des signaux de qualité rapides (tags, alertes) pour accélérer la prise de décision.
- **Objectif pédagogique** — démontrer la maîtrise d'une architecture Laravel professionnelle (Policies, Form Requests, Resources, Services, Jobs, Observers, tests automatisés) dans le cadre du projet de fin de formation DWWM/Backend.
- **Objectif technique** — livrer une API REST sécurisée par token (Sanctum), testée, conteneurisée (Docker) et intégrée à une chaîne CI/CD (GitHub Actions).

## 3. Périmètre Fonctionnel

### 3.1 Acteurs et rôles

Deux rôles distincts et exclusifs (un utilisateur ne peut être que l'un ou l'autre, défini à l'inscription et non modifiable) :

| Rôle     | Description                                                                                   | Permissions principales                                                                                                                                                                                         |
| --------- | --------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Recruteur | Gère ses offres d'emploi, le suivi des candidatures et pilote sa performance de recrutement. | CRUD sur ses offres, gestion des candidatures reçues, actions groupées, filtres sauvegardés, comparaison de candidats, planification et notation des entretiens, notes internes, tableau de bord analytique. |
| Candidat  | Postule aux offres et suit ses candidatures.                                                  | Postuler (CV + lettre), consulter ses candidatures et son score, consulter son profil.                                                                                                                          |

### 3.2 Fonctionnalités par module

- **Authentification** — inscription (nom, email, mot de passe, rôle), connexion/déconnexion par token Sanctum, consultation et modification du profil (email, mot de passe ; le rôle reste figé).
- **Offres d'emploi** — création, modification et archivage (soft delete) d'une offre par son recruteur ; consultation publique et paginée des offres actives avec filtres (type de contrat, recherche par stack technique).
- **Candidatures** — un candidat postule une seule fois par offre (upload CV PDF ≤ 5 Mo + lettre de motivation) ; le score de matching est calculé automatiquement à l'upload, avec le détail des mots-clés trouvés et manquants stocké pour affichage recruteur ; le recruteur consulte les candidatures de ses offres triées par score, ajoute des notes internes et des commentaires visibles par le candidat ; le candidat consulte l'historique et le détail de ses candidatures.
- **Pipeline de suivi (Kanban)** — statuts reçu → entretien → accepté / refusé, avec règle stricte de progression : aucun retour en arrière possible une fois le statut "accepté" atteint.
- **Entretiens** — planification (date/heure, lien visio), évaluation à la clôture sur 3 critères notés de 1 à 5 (technique, communication, motivation), annulation possible, liste filtrable par candidature ou statut.
- **Notifications** — email automatique envoyé au candidat lors du passage au statut "accepté" ou "refusé", incluant le titre du poste et un commentaire optionnel du recruteur.

### 3.3 Tableau de bord & analytique recruteur

Cœur de la refonte orientée recruteur : donner une vision claire de la performance du recrutement, pas seulement un journal de candidatures.

- Funnel de conversion par offre : nombre et pourcentage de candidatures à chaque étape (reçu → entretien → accepté/refusé) ;
- Délai moyen de traitement (time-to-hire) : nombre de jours moyen entre candidature et décision finale, par offre ou global ;
- Distribution des scores : répartition des candidatures par tranche de score (> 80, 50-80, < 50), pour juger la qualité d'une offre publiée ;
- Fil d'activité récente : nouvelles candidatures, entretiens planifiés, changements de statut ;
- Comparatif entre offres : taux de conversion entretien → accepté par offre, comparé à la moyenne du recruteur ;
- Digest de tâches en attente : entretiens à évaluer, candidatures en attente depuis plus de 7 jours.

### 3.4 Outils de productivité recruteur

- **Actions groupées** : sélectionner plusieurs candidatures dans le pipeline et changer leur statut en une seule action (ex : rejet groupé après un premier tri) ;
- **Filtres sauvegardés** : le recruteur enregistre une combinaison de critères (score minimum, stack technique) et la réapplique en un clic ;
- **Comparaison de candidats** : vue côte-à-côte de 2 à 4 candidatures d'une même offre (score, détail des mots-clés, notes d'entretien) ;
- **Tags rapides** : étiquettes prédéfinies sur une candidature (à relancer, prioritaire, réserve) pour un scan visuel rapide du pipeline ;
- **Shortlist automatique** : génération en un clic du top 5 des candidatures d'une offre, triées par score ;
- **Export shortlist** : export CSV/PDF d'une sélection de candidatures, pensé pour être partagé avec un hiring manager externe à la plateforme ;
- **Suggestions de profils similaires** : lors d'un refus, proposer d'autres candidatures au profil proche pouvant correspondre à une autre offre ouverte ;
- **Modèles de réponse rapide** : 2-3 messages types réutilisables lors d'un changement de statut (relance, refus standard), modifiables par le recruteur.

### 3.5 Fonctionnalités bonus

- Badges automatiques affichés côté recruteur sur la fiche candidature : "CV complet", "Match élevé" (score > 80), "Entretien réussi" (moyenne d'évaluation > 3) — pensés comme des signaux de tri rapide plutôt que comme une récompense visible du candidat ;
- Générateur de questions d'entretien à partir des mots-clés de la stack technique (règles simples, sans IA externe).

Le classement (leaderboard) et l'export PDF du profil candidat, initialement prévus comme fonctionnalités candidat, sont retirés du périmètre afin de concentrer l'effort sur les outils recruteur ci-dessus.

## 4. Le Moteur de Matching ("IA" maison)

Le score de compatibilité repose sur un algorithme de correspondance par mots-clés, calculé une seule fois à l'upload du CV et stocké en base (jamais recalculé à la lecture, pour des raisons de performance). Le détail du calcul est conservé afin d'alimenter la transparence du score côté recruteur :

1. Extraction des mots-clés de l'offre depuis le champ tech_stack (liste séparée par virgules) ;
2. Extraction du texte du CV (parsing du PDF) ;
3. Comparaison insensible à la casse : chaque mot-clé recherché dans le texte du CV ;
4. Score = (nombre de mots-clés trouvés / nombre total de mots-clés) × 100, arrondi à 2 décimales ;
5. Les listes de mots-clés trouvés et manquants sont stockées avec le score, pour affichage recruteur ("Trouvés : PHP, Laravel, MySQL — Manquants : Docker, Redis") ;
6. Traitement asynchrone via une file d'attente (Job) pour ne pas bloquer la réponse à la candidature.

Ce choix garantit un fonctionnement 100 % autonome, explicable et gratuit (aucune dépendance à une API d'IA externe). La transparence du détail du score (mots-clés trouvés / manquants) transforme le matching d'un simple chiffre en véritable aide à la décision pour le recruteur.

## 5. Spécifications Techniques

| Couche                   | Technologie             | Version          |
| ------------------------ | ----------------------- | ---------------- |
| Framework backend        | Laravel                 | 13.x             |
| Langage                  | PHP                     | 8.3+             |
| Base de données (prod)  | MySQL                   | 8.x              |
| Base de données (tests) | SQLite                  | en mémoire      |
| Frontend                 | Blade + Vite            | Vite 8           |
| CSS                      | Tailwind CSS            | 4.x              |
| Authentification API     | Laravel Sanctum         | Token Bearer     |
| Tests                    | PHPUnit / Pest          | PHPUnit 12.5+    |
| Conteneurisation         | Docker + docker-compose | PHP 8.3          |
| File d'attente           | Database driver         | Jobs asynchrones |
| Stockage fichiers        | Disque local            | CV et avatars    |
| Intégration continue    | GitHub Actions          | workflow.yml     |

### 5.1 Modèle de données (extrait)

Cinq tables métier, en plus des tables Laravel par défaut : users (rôle, avatar), job_offers (soft delete), applications (contrainte d'unicité candidat/offre, score, mots-clés trouvés/manquants, tags, statut), interviews (scores 1-5 par critère), badges (unicité candidat/type). L'ordre de création respecte les clés étrangères : users → job_offers → applications → interviews → badges. Les filtres sauvegardés du recruteur sont stockés dans une table dédiée (saved_filters) rattachée à l'utilisateur recruteur.

### 5.2 Sécurité et autorisations

- Authentification par token Bearer (Sanctum) sur toutes les routes protégées ;
- Middleware de rôle (recruteur / candidat) sur les routes sensibles ;
- Policies Laravel pour les contrôles de propriété (un recruteur ne peut agir que sur ses propres offres et candidatures, y compris pour les actions groupées) ;
- Validation systématique des entrées via des Form Requests dédiées.

## 6. Livrables Attendus

| Livrable                 | Détail                                                                                         |
| ------------------------ | ----------------------------------------------------------------------------------------------- |
| Code source (GitHub)     | Minimum 25 commits propres, une branche par fonctionnalité, historique lisible.                |
| Gestion de projet (Jira) | Backlog à jour, user stories organisées par module (AUTH, JOB, APP, INT, NOTIF, DASH, BONUS). |
| Base de données         | MCD et MLD conformes au schéma défini, scripts de migration Laravel.                          |
| API documentée          | Endpoints REST avec formats de requête/réponse et codes HTTP normalisés.                     |
| Tests automatisés       | Tests Feature (endpoints) et Unit (services), exécutés en CI.                                 |
| Conteneurisation         | Dockerfile + docker-compose.yml fonctionnels (app, MySQL, worker de file).                      |
| CI/CD                    | Pipeline GitHub Actions : installation, migrations, tests, vérification du style de code.      |
| Documentation            | README.md avec instructions d'installation, fonctionnalités et endpoints API.                  |
| Présentation            | Slides + démonstration live + diagrammes d'architecture pour la soutenance.                    |

## 7. Critères d'Évaluation

| Critère               | Poids | Points clés observés                                                                                                                 |
| ---------------------- | ----- | -------------------------------------------------------------------------------------------------------------------------------------- |
| Architecture Laravel   | 35 %  | Policies, Form Requests, Resources, soft deletes, Services, Jobs, Observers, code propre.                                              |
| Fonctionnalités       | 25 %  | Pipeline Kanban opérationnel, matching transparent, tableau de bord analytique, outils de productivité recruteur, entretiens notés. |
| Présentation          | 20 %  | Slides clairs, démo sans bug, diagrammes MCD/MLD et architecture corrects.                                                            |
| Organisation & Process | 20 %  | Commits réguliers, Jira à jour, branches par fonctionnalité, code formaté (Pint).                                                  |

## 8. Planning Indicatif

| Période                | Jalon                                                                                                                                            |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| 13/07/2026              | Lancement du projet — cadrage, initialisation Laravel, Jira.                                                                                    |
| Semaine 1-2             | Authentification, offres d'emploi, base de données, policies.                                                                                   |
| Semaine 2-3             | Candidatures, moteur de matching transparent, pipeline Kanban, entretiens.                                                                       |
| Semaine 3               | Tableau de bord analytique recruteur (funnel, délai de traitement, distribution des scores).                                                    |
| Semaine 4               | Outils de productivité recruteur (actions groupées, filtres sauvegardés, comparaison, shortlist), notifications, bonus, tests, Docker, CI/CD. |
| 07/08/2026              | Date limite de rendu du projet.                                                                                                                  |
| À partir du 10/08/2026 | Soutenance orale et démonstration live.                                                                                                         |
