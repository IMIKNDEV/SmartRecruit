# Frontend JS Contract — `resources/js/app.js` (v1.0)

> Contract between `resources/js/app.js` (single-file vanilla JS, fetch→REST) and the Blade views.
> **Rule:** views and JS markup must keep every hook below intact. Restyling = change classes/CSS only,
> never IDs, `data-*` values, or event flow.

---

## 1. Global page lifecycle

- `DOMContentLoaded` → reads `<body data-page="...">`, runs `SR.pages[page]()`.
- Page keys: `login`, `register`, `guest-jobs`, `guest-job-detail`, `public-jobs`, `public-job-detail`,
  `candidate-jobs`, `candidate-applications`, `recruiter-dashboard`, `recruiter-jobs`, `recruiter-apps`,
  `recruiter-app-detail`, `recruiter-kanban`, `recruiter-interviews`, `recruiter-agents`, `candidate-profile`,
  `recruiter-profile`, `recruiter-settings`, `recruiter-shortlist`, `recruiter-saved-filters`, `recruiter-compare`.
- All `SR.pages.*` functions must stay. `SR.auth`, `SR.api`, `SR.helpers`, `SR.icon`, `SR.load`, `SR.mock`,
  `SR.modal`, `SR.pages`, `SR.toast` namespaces must stay.

---

## 2. `getElementById` hooks (element MUST exist in the view)

### Auth
| ID | Page | Notes |
|---|---|---|
| `loginForm` | login | submit → `SR.auth.login` |
| `registerForm` | register | submit → `SR.auth.register` |

### Public / guest jobs
| ID | Page | Notes |
|---|---|---|
| `jobSearch` | guest-jobs, public-jobs | input, debounced search |
| `contractFilter` | guest-jobs, public-jobs | select, contract filter |
| `jobsList` | guest-jobs, public-jobs | container filled by JS (`job-card` markup) |
| `jobDetail` | guest-job-detail, public-job-detail | container, `data-apply` button inside |

### Candidate
| ID | Page | Notes |
|---|---|---|
| `candAppsBox` | candidate-applications | filled with application rows |
| `applyForm` | candidate-applications (apply modal) | multipart, `SR.load.file` |
| `applyDropzone` | candidate-applications | `.dz-file` inside; `dragover`/`has-file` classes |
| `applyCv` | candidate-applications | file input |
| `applyCover` | candidate-applications | textarea |
| `coverHint` | candidate-applications | char counter target |
| `applyJobTitle` / `applyJobSummary` | candidate-applications | summary of job being applied to |
| `applyCancel` / `applyBackLink` | candidate-applications | closes apply view |

### Recruiter — dashboard
| ID | Page | Notes |
|---|---|---|
| `statGrid` | recruiter-dashboard | stat cards (`stat-card` markup) |
| `funnelBox` | recruiter-dashboard | funnel rows (`funnel-*`) |
| `scoreDistBox` | recruiter-dashboard | distribution rows (`dist-*`) |
| `activityBox` | recruiter-dashboard | activity items (`activity-item`) |
| `offerCompareBox` | recruiter-dashboard | offer comparison bars |
| `pendingBox` | recruiter-dashboard | pending tasks (`task-item`) |
| `dashSub` | recruiter-dashboard | subtitle text (job count) |

### Recruiter — jobs
| ID | Page | Notes |
|---|---|---|
| `jobsList` | recruiter-jobs | job cards (`job-card`) |
| `jobsSearch` / `jobsStatusFilter` | recruiter-jobs | filters |
| `jobForm` | recruiter-jobs (create/edit modal) | JSON POST/PUT |
| `confirmArchive` | recruiter-jobs | archive confirmation button |
| `jobDetail` | recruiter-jobs detail view | container |

### Recruiter — applications table
| ID | Page | Notes |
|---|---|---|
| `appsTitle` | recruiter-apps | heading text |
| `appsTbody` | recruiter-apps | `<tbody>` filled by JS (`tr`, `app-check`) |
| `appsSearch` / `appsStatusFilter` / `appsScoreFilter` | recruiter-apps | filters |
| `batchBar` / `batchCount` / `batchApply` / `batchClear` / `batchStatus` | recruiter-apps | batch bar UI |
| `appsSelectAll` | recruiter-apps | checkbox select-all |
| `exportCsv` / `exportPdf` | recruiter-apps | export buttons |
| `savedFilterSelect` | recruiter-apps | select of saved filters |
| `filterForm` / `filtersBox` | recruiter-apps | filter form |
| `tagBox` | recruiter-apps | tag toggle chips (`tag-chip.on`) |

### Recruiter — application detail
| ID | Page | Notes |
|---|---|---|
| `appDetail` | recruiter-app-detail | container, `data-status`, `data-tag`, `data-iv` buttons inside |
| `appNotes` / `saveNotes` | recruiter-app-detail | notes textarea + save |
| `scheduleInterview` | recruiter-app-detail | schedule submit |
| `confirmSched` | recruiter-app-detail | schedule confirm modal |
| `confirmScore` | recruiter-app-detail | scoring modal confirm |
| `genQ` | recruiter-app-detail | AI question generator button |
| `loadSugg` | recruiter-app-detail | suggestions loader |
| `suggBox` | recruiter-app-detail | suggestions container |
| `ivDate` / `ivLink` / `ivTech` / `ivCom` / `ivMot` | recruiter-app-detail | interview form fields |
| `appBackLink` | recruiter-app-detail | back link |

### Recruiter — kanban
| ID | Page | Notes |
|---|---|---|
| `kanbanBoard` | recruiter-dashboard (hero demo), recruiter-kanban | columns (`kanban-col`) + cards (`kanban-card`, `data-id`, `data-status`) |
| `kanbanMoves` (none — drop handled by `SR.kanban`) | — | drag & drop helper |

### Recruiter — interviews
| ID | Page | Notes |
|---|---|---|
| `interviewsBox` | recruiter-interviews | list |
| `interviewFilters` | recruiter-interviews | filter selects |

### Recruiter — agents / conversations
| ID | Page | Notes |
|---|---|---|
| `convList` | recruiter-agents | conversation items (`conv-item`) |
| `convTitle` | recruiter-agents | current conversation title |
| `chatThread` | recruiter-agents | messages (`chat-msg user/assistant`) |
| `chatForm` / `chatText` / `chatSend` | recruiter-agents | chat input form |
| `newConvBtn` | recruiter-agents | new conversation button |
| `ncJob` / `ncTech` | recruiter-agents | new-conversation form selects |
| `sT` / `sM` / `sC` | recruiter-agents | score inputs in chat metadata |

### Recruiter — shortlist / compare
| ID | Page | Notes |
|---|---|---|
| `shortlistBox` / `shortlistTitle` / `shortlistBack` | recruiter-shortlist | shortlist table (`rank-pill`) |
| `compareBox` (via `appsTbody` context) | recruiter-compare | side-by-side table |

### Recruiter — settings / templates
| ID | Page | Notes |
|---|---|---|
| `templatesBox` / `saveTpl` / `tplSubject` / `tplBody` | recruiter-settings | reply templates CRUD |

### Profile (shared)
| ID | Page | Notes |
|---|---|---|
| `profileForm` / `profileName` / `profileEmail` / `profilePassword` / `profilePasswordConfirm` | both profiles | PUT profile |
| `profileAvatar` | both profiles | avatar file input |
| `profileRole` / `profileName` / `profileEmail` (read-only text) | both profiles | display |
| `kvRole` / `kvJoined` | both profiles | info rows |

### Global chrome
| ID | Page | Notes |
|---|---|---|
| `sidebarName` / `sidebarRole` / `sidebarAvatar` | all authed | topbar/sidebar fill |
| `topbarAvatar` | all authed | avatar in topbar |
| `globalSearchInput` | all authed | nav search |
| `toasts` | all | toast stack container |
| `modalBackdrop` / `modalBox` | all | modal system |

---

## 3. `querySelector(All)` hooks

| Selector | Purpose |
|---|---|
| `.app-check` | application checkboxes in apps table (batch) |
| `.role-option` | role cards in register form |
| `.form-alert` | form error/success alert inside a form |
| `.dz-file` | dropzone file display |
| `[data-nav]` | global nav active-state toggling |

## 4. `data-*` attributes (event delegation)

| Attribute | Elements | Meaning |
|---|---|---|
| `data-nav` | nav links | marks nav item for active state |
| `data-id` | kanban cards, app rows, conv items | entity id |
| `data-status` | kanban cards, app detail buttons | current pipeline status |
| `data-action` | various buttons | action name (e.g. `status`) |
| `data-apply` | job detail apply buttons | open apply flow |
| `data-tag` | tag chips | tag key (`a_relancer`, `prioritaire`, `reserve`, `entretien_planifie`) |
| `data-iv` | interview rows | interview id |
| `data-del` / `data-edit` / `data-cancel` / `data-complete` / `data-detail` | tables/lists | row actions |
| `data-conv` | conv items | conversation id |
| `data-title` | various | display title |
| `data-f` | nav/back links | target page key (single-page nav) |

## 5. CSS variables used by JS inline styles (must be defined in new theme)

```css
--ink --slate --line --blue --navy --pink --pink-light --success --danger --warning
```

## 6. Classes emitted by JS templates (must exist or be mapped)

### Layout atoms (style once, globally)
`pill` `pill-rank` `tag` `tag-amber` `tag-green` `tag-navy` `chip` `chip-ok` `chip-no`
`score-ring-wrap` `score-ring` `score-ring-track` `score-ring-fill` `score-label`
`avatar` `avatar-sm` `btn` `btn-sm` `btn-primary` `btn-ghost` `btn-danger` `btn-ghost-danger`
`kanban-move` `modal` `modal-backdrop` `modal-head` `modal-close` `modal-foot`
`toast` `form-group` `form-label` `input` `textarea` `empty` `mono` `prose` `table`

### Structure classes (page sections — views/JS restyle)
`card` `card-pad` `stat-card` `stat-top` `stat-label` `stat-value` `stat-delta`
`funnel-row` `funnel-head` `funnel-title` `funnel-sub` `funnel-track` `funnel-seg-*`
`dist-row` `dist-label` `dist-track` `dist-fill` `dist-count`
`activity-item` `act-ic` `act-label` `act-at`
`task-item` `job-card` `job-card-top` `job-card-desc` `job-card-meta` `chips`
`detail-hero` `detail-hero-main` `detail-grid` `detail-main` `detail-side`
`side-card` `side-row` `kv` `app-kw-label`
`kanban-col` `kanban-col-head` `kanban-col-title` `kanban-col-dot` `kanban-count`
`kanban-card` `kanban-card-top` `kanban-cand-name` `kanban-cand-job` `kanban-tags` `kanban-card-foot`
`filter-chip` (`.active`) `conv-item` (`.active`) `conv-title` `conv-sub` `chat-msg` (`user`/`assistant`)
`score-ring` fills use `--blue`/`--success`/`--danger` by band.

## 7. Drag & drop contract (`SR.kanban`)

- Cards: `.kanban-card[draggable=true]`, `data-id`, `data-status`.
- Columns: `.kanban-col`, add `.drag-over` when target.
- On drop: `PUT /api/applications/{id}/status` with `{status}`; invalid transition → toast + revert.
- Demo board (guest dashboard hero): free drag, counters in `.kanban-count` update locally, no API.

## 8. Restyle strategy

1. **Views** (`resources/views/**/*.blade.php`): Tailwind utilities only; keep every ID from §2, `data-*` from §4, class hooks for `querySelector` from §3, and shell classes (`kanban-col`, `.app-check`, `.role-option`, `.form-alert`, `.dz-file`, `[data-nav]`).
2. **JS templates**: edit markup strings to Tailwind utility classes **or** keep class names and style them in `resources/css/components.css`; never change IDs/`data-*`/structure/flow.
3. **CSS vars**: define `--ink --slate --line --blue --navy --pink --pink-light --success --danger --warning` mapped to the new palette in `components.css` (JS inline styles depend on them).
4. `.kanban-card.dragging`, `.kanban-col.drag-over`, `.drop-ghost`, `.dz-file.dragover`, `.has-file`, `.chip-toggle` states must keep working.
