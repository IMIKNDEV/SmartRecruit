# SmartRecruit Frontend Redesign — Teamtailor Design System Implementation Plan

**Goal:** Rebuild the entire SmartRecruit frontend (21 Blade views) from the current custom pink/cream CSS to the Teamtailor-inspired design system (light `#fffdf9`, Inter typography, rounded 24px+, `#6ebbff` accent, `#f43f85` secondary, playful color blocks) using Tailwind CSS 4 + Laravel Blade + Vite — without breaking the existing vanilla-JS data layer.

**Architecture:** Tailwind 4 `@theme` tokens in `resources/css/app.css` carry the whole design system. Blade views become thin utility-class markup + small reusable Blade components. The vanilla JS (1937 lines, fetch → REST API) moves into the Vite pipeline but is **only styled against, never rewritten** — every ID/data-attribute/class it binds is a preserved contract.

**Tech stack:** Laravel 13 Blade, Tailwind CSS 4 (`@tailwindcss/vite`), Vite 8, vanilla JS, Inter font (bunny plugin). English copy everywhere (backend already English).

## Global Constraints

- **Palette (only these):** `canvas #fffdf9`, `surface #fff3e7`, `line/border #fff3e7`, `body-text #71717a`, `dark #34353a`, `accent #6ebbff`, `secondary #f43f85`, sections `#5bd2a7` `#d4b3ff` `#fdec8c` `#ffab95` `#ffc8dc` `#ffa4c4`, `textaccent #3860be`, white.
- **Typography:** Inter only. Heading weight 600, tight tracking; body 500. H1 up to 104px.
- **Shape:** min 16–24px radii; primary CTA = filled `#f43f85` pill (radius 999px). No sharp corners, no dark backgrounds, no pure-black text.
- **Spacing:** 16px base unit.
- **English copy** in every view and every JS string (toasts, mock data, empty states) + meta/OG tags.
- **JS contract (must survive):** all `getElementById` hooks, `data-*` attrs, `.app-check`, `.role-option`, `.form-alert`, `.dz-file`, `[data-nav]`, and `SR.*` namespaces.
- **No new dependencies.** Vite only asset pipeline; legacy `public/css/app.css`/`public/js/app.js` deleted after switch.
- **Interactivity:** landing hero + dashboard kanban — cards clickable, drag & drop to any column (landing demo free; dashboard guarded by state machine).
- **Verification per task:** `npm run build` passes, `php artisan test` green, DevTools smoke check.
- Marquee content: tech-stack pills (option A).
- Execution: inline single agent, new commit per task, plan file `docs/superpowers/plans/2026-08-05-teamtailor-redesign.md`.

## The Endpoint: Interactive Landing Hero (Task 7)
- Centered text: `Hire for growth.` (display) + `The ATS loved by candidates and recruiters — with AI at the core.` (swosh underline on highlight).
- CTA: pill `Commencer` style + ghost `Explorer`.
- Full-width interactive Kanban demo: 4 columns `Received 24 / Interview 8 / Accepted 3 / Refused 13`, candidate cards (`avatar tile, name, role, score ring, keyword chips`), drag & drop between any columns via native HTML5 DnD, live counters, clickable card → modal detail with Accept/Refuse pills.
- Trust strip: scrolling marquee of tech-stack pill wordmarks.

## Tasks (executable order)
1. Tokens + Inter (vite.config.js bunny; resources/css/app.css `@theme`; resources/css/components.css utilities; `drag-over`/`dragging`/`drop-ghost`, `.btn-pill`, `.heading-display`, swosh underline, status pills, grain).
2. Vite wiring (`git mv public/js/app.js → resources/js/app.js`; layouts `@vite(...)`; delete legacy assets).
3. JS contract audit → `docs/frontend-js-contract.md`.
4. Blade components: `x-btn`, `x-card`, `x-badge`, `x-status-pill`, `x-field`; extend `x-icon` (`x-mark`, `chevron-down`, `download`, `plus`, `trash`, `clock`, `star`).
4.5. `SR.kanban.enable(boardEl, {onDrop})` — HTML5 DnD, `.drag-over`, `.drop-ghost`, animated placement.
5. Guest/public layouts — English header (logo, Offres, Log in, pill CTA) + dark footer.
6. App shell — light sidebar (brand mark, pill nav, `bg-accent/20` active), topbar (global search, avatar ring, toasts, modal containers). Preserve `data-nav`, sidebar user hooks.
7. Landing — hero (above) + feature sections + How it works + CTA band + footer.
8. Auth — login/register centered card, role picker pill radios, English.
9. Public jobs — index (accent hero strip, search, contract pills) + show (detail hero, sticky Apply pill); JS templates restyled.
10. Candidate — applications list (score circles, chips), apply (CV dropzone), profile. English.
11. Dashboard — stats/funnel/score-dist/activity/pending/offer-compare + interactive kanban click/drag with state-machine guard.
12. Recruiter jobs — index (filters, card rows, archive), create/edit (chips stack, sticky actions). English.
13. Applications board — filters bar, score rows, `.app-check` batch bar, saved-filter dropdown. English.
14. Application detail — hero, tabs (notes/comments/tags/interviews/scores/suggestions), schedule/score modals, Generate-questions modal. English.
15. Interviews / Shortlist / Reply templates / Saved filters — cards, podium, editor, grid. English.
16. Agent chat — conversations rail + bubbles, composer, question modal. English.
17. Polish + English sweep — responsive (390/768/1280), a11y snapshot, French grep sweep, regression.
18. Final verification — pint, build, tests, docs note.
```