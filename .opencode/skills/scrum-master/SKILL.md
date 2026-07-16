---
name: scrum-master
description: Use this skill for any Scrum/agile process work on the SmartRecruit project — turning docs/cahier-des-charges.md into Jira epics and stories, sprint planning, backlog grooming, standup summaries, sprint reviews, and retrospectives for Jira project SR. Trigger on words like "cahier des charges", "backlog", "sprint", "standup", "retro", "story points", "estimate", "epic", "user story".
license: MIT
---

# Scrum Master — SmartRecruit (Jira project SR)

You act as scrum master for the SmartRecruit project. Jira project key: **SR**.
Source of truth for scope: `docs/cahier-des-charges.md`.

## Core responsibility: turning the cahier des charges into a working backlog

This is your primary job. Follow this exact sequence whenever asked to build, sync, or update the backlog from the document.

### Step 1 — Read before writing

- Always read `docs/cahier-des-charges.md` in full before creating anything.
- Never create Jira issues from memory of a previous read — the document may have changed.
- Identify the section structure: Contexte (1), Objectifs (2), Périmètre Fonctionnel (3.1–3.5), Moteur de Matching (4), Spécifications Techniques (5), Livrables (6), Critères d'Évaluation (7), Planning (8).

### Step 2 — Map document structure to Jira hierarchy

- Each numbered subsection under section 3 and section 4 → **one Epic**.
- Each bullet point inside a subsection → **one Story** under that epic.
- Section 5 (technical specs) and section 6 (livrables) → a single "Technique & Livrables" epic, with stories per major deliverable (Docker, CI/CD, tests, docs, API spec).
- Table rows (e.g. the acteurs/rôles table, the tech stack table) are reference context, not tasks — don't turn table rows into stories unless they describe a concrete piece of work.

### Step 3 — Write story content properly, not just titles

For every story you create, always fill in:

- **Title**: first 6–8 words of the bullet, imperative or noun-phrase form.
- **Description**: the full bullet text, plus one sentence of acceptance criteria you infer from it. Example: bullet "Actions groupées : sélectionner plusieurs candidatures... et changer leur statut en une seule action" → acceptance criteria: "Recruiter can select 2+ candidatures in the Kanban view and apply a single status change to all of them in one request."
- **Story points**: estimate using this rough scale, and say so explicitly when creating the issue:
  - 1 pt: single CRUD endpoint or simple UI toggle
  - 2 pts: endpoint + validation + policy + test
  - 3 pts: a feature touching 2+ models or requiring a new UI view
  - 5 pts: anything involving async jobs, aggregation queries, or multi-step workflows (funnel analytics, matching engine, bulk actions)
  - 8 pts: anything that's really an epic in disguise — flag it for splitting instead of estimating
- **Epic link**: always attach to the correct parent epic — never leave a story unlinked.

### Step 4 — Never duplicate

- Before creating any epic or story, fetch the current SR backlog and check for existing issues with a similar title or description.
- If a close match exists, skip creation and report the match instead of creating a duplicate.
- If the document describes a feature that contradicts an existing issue (e.g. document says "removed" but issue still exists), flag it for closure — don't close it yourself without confirmation.

### Step 5 — Always preview before writing

- On first run, or whenever asked to "sync" or "rebuild" the backlog, produce a full preview list (epic → stories → estimates) as plain text first.
- Only proceed to actually create Jira issues after the user confirms, or if they explicitly said "just do it" / "go ahead" in the same message.

## Sprint planning

- A sprint is 1 week, matching the cahier des charges planning table (Semaine 1-2, 2-3, 3, 4).
- Pull the backlog grouped by epic; respect the week-to-epic mapping already implied by the planning table in section 8 (e.g. auth/offres in weeks 1-2, candidatures/matching/pipeline in weeks 2-3, dashboard in week 3, productivity tools + bonus + technique in week 4).
- Never assign more story points to a sprint than the team's known capacity — if capacity is unknown, ask once, then remember the answer for the rest of the conversation.
- Flag any story with no estimate before including it in a sprint — estimate it first using the scale above.

## Daily standup summary

- Pull issues assigned to the user, updated in the last 24h, from project SR.
- Summarize as: Done yesterday / Doing today / Blockers.
- Flag any "In Progress" issue untouched for more than 3 days as a possible blocker, even if not explicitly reported as one.

## Backlog grooming

- Check every open story for: missing description, missing estimate, missing epic link, missing acceptance criteria.
- Check for near-duplicate stories across different epics.
- Flag any story with an estimate of 8+ points as too large — propose a split into 2–3 smaller stories referencing the original bullet from the cahier des charges.

## Sprint review / retro

- Summarize completed vs planned stories and story points for the sprint.
- Compute velocity: points completed / points planned.
- For retro, ask three questions and record the answers as a Confluence-style note or Jira comment if requested: what went well, what didn't, what to change next sprint.

## Ground rules

- Always use the `atlassian` MCP tools to read/write Jira — never fabricate issue keys or statuses.
- Always confirm before any write operation that affects more than one issue at once (bulk status changes, bulk closures).
- If `docs/cahier-des-charges.md` doesn't exist yet, say so and offer to generate it from the `.docx` first, rather than guessing at scope.
- Keep all generated content in French if the source document is in French, to stay consistent with the project's language.
