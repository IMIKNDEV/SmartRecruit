/* ============================================================
   SmartRecruit — front-end JS
   Vanilla JS. Consumes the Laravel REST API via fetch().
   Falls back to bundled mock data when an endpoint is missing.
   ============================================================ */
(function () {
  'use strict';

  const USE_MOCKS = true; // flip to false once all endpoints are live
  const TOKEN_KEY = 'sr_token';
  const USER_KEY = 'sr_user';

  const API = (window.SR_BOOT && window.SR_BOOT.apiBase) || '/api';

  window.SR = window.SR || {};

  /* ---------------- Helpers ---------------- */
  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function fmtDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function fmtDateTime(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) +
      ' at ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
  }

  function fmtSalary(s) {
    if (s === null || s === undefined || s === '') return 'Not disclosed';
    return Number(s).toLocaleString('en-GB', { maximumFractionDigits: 0 }) + ' €';
  }

  const STATUS_LABELS = {
    received: 'Received', interview: 'Interview', accepted: 'Accepted', refused: 'Refused',
    scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', active: 'Active', archived: 'Archived',
  };

  const STATUS_PILL_CLASS = {
    received: 'pill-slate', interview: '', accepted: 'pill-success', refused: 'pill-danger',
    scheduled: '', completed: 'pill-success', cancelled: 'pill-danger',
  };

  const TAG_LABELS = {
    a_relancer: 'Follow up', prioritaire: 'Priority', reserve: 'Reserve', entretien_planifie: 'Interview scheduled',
  };

  function statusPill(status) {
    const label = STATUS_LABELS[status] || status;
    const cls = STATUS_PILL_CLASS[status] || 'pill-slate';
    return '<span class="pill ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function tagChip(tag) {
    const map = { a_relancer: 'tag-amber', prioritaire: 'tag-red', reserve: 'tag-navy', entretien_planifie: 'tag-green' };
    const cls = map[tag] || 'tag';
    return '<span class="tag ' + cls + '">' + escapeHtml(TAG_LABELS[tag] || tag) + '</span>';
  }

  function scoreRing(value, size) {
    const v = Math.max(0, Math.min(100, Number(value) || 0));
    const r = 24;
    const circ = 2 * Math.PI * r;
    const off = circ - (v / 100) * circ;
    const color = v >= 80 ? '#16A34A' : v >= 50 ? '#F59E0B' : '#EF4444';
    const sm = size === 'sm' ? ' score-ring-sm' : '';
    const wrap = size === 'sm' ? 'sm' : '';
    return '<div class="score-ring-wrap ' + wrap + '">' +
      '<svg class="score-ring" viewBox="0 0 54 54" width="100%" height="100%">' +
      '<circle class="score-ring-track" cx="27" cy="27" r="24"></circle>' +
      '<circle class="score-ring-fill" cx="27" cy="27" r="24" stroke="' + color + '" ' +
      'stroke-dasharray="' + circ + '" stroke-dashoffset="' + off + '"></circle></svg>' +
      '<span class="score-label">' + Math.round(v) + '</span></div>';
  }

  function initials(name) {
    return String(name || 'U').trim().charAt(0).toUpperCase();
  }

  function avatar(name, size) {
    return '<span class="avatar' + (size === 'sm' ? '" style="width:34px;height:34px;font-size:12px' : '"') + '>' +
      escapeHtml(initials(name)) + '</span>';
  }

  function setLoading(el, loading, text) {
    if (!el) return;
    if (loading) {
      el.dataset.original = el.innerHTML;
      el.disabled = true;
      el.innerHTML = '<span class="mono">…</span>';
    } else {
      el.disabled = false;
      if (el.dataset.original) el.innerHTML = el.dataset.original;
    }
  }

  SR.helpers = {
    escapeHtml, fmtDate, fmtDateTime, fmtSalary, statusPill, tagChip, scoreRing, initials, avatar, setLoading,
    STATUS_LABELS, TAG_LABELS,
  };

  /* ---------------- Toasts ---------------- */
  function toast(message, type) {
    type = type || 'info';
    const box = document.getElementById('toasts');
    if (!box) return;
    const icons = { success: 'check', error: 'x', info: 'info' };
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.innerHTML = window.SR.icon ? SR.icon(icons[type] || 'info') + '<span>' + escapeHtml(message) + '</span>' : message;
    box.appendChild(el);
    setTimeout(function () { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 3600);
    setTimeout(function () { el.remove(); }, 4000);
  }
  SR.toast = toast;

  /* ---------------- Icons (runtime) ---------------- */
  const ICONS = {
    check: '<path d="M20 6 9 17l-5-5"/>',
    x: '<path d="M18 6 6 18M6 6l12 12"/>',
    info: '<circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    trash: '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    calendar: '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
    arrowRight: '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    arrowLeft: '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
    sparkles: '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9Z"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    export: '<path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    chat: '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    users: '<path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    eye: '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>',
    note: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
    briefcase: '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>',
  };

  function icon(name, size) {
    size = size || 18;
    return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
      (ICONS[name] || ICONS.info) + '</svg>';
  }
  SR.icon = icon;

  /* ---------------- Modal ---------------- */
  function openModal(html, opts) {
    opts = opts || {};
    const backdrop = document.getElementById('modalBackdrop') || createBackdrop();
    const box = document.getElementById('modalBox');
    box.innerHTML = '<div class="modal-head"><h3>' + (opts.title || '') + '</h3>' +
      '<button class="modal-close" onclick="SR.modal.close()">' + icon('x', 16) + '</button></div>' +
      html;
    if (opts.onOpen) opts.onOpen();
    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    const backdrop = document.getElementById('modalBackdrop');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }
  function createBackdrop() {
    const b = document.createElement('div');
    b.className = 'modal-backdrop';
    b.id = 'modalBackdrop';
    b.innerHTML = '<div class="modal" id="modalBox"></div>';
    b.addEventListener('click', function (e) { if (e.target === b) closeModal(); });
    document.body.appendChild(b);
    return b;
  }
  SR.modal = { open: openModal, close: closeModal };

  /* ---------------- Auth ---------------- */
  const auth = {
    token: function () { return localStorage.getItem(TOKEN_KEY); },
    user: function () {
      try { return JSON.parse(localStorage.getItem(USER_KEY)); }
      catch (e) { return (window.SR_BOOT && SR_BOOT.user) || null; }
    },
    set: function (data) {
      localStorage.setItem(TOKEN_KEY, data.token);
      localStorage.setItem(USER_KEY, JSON.stringify(data.user || {}));
    },
    clear: function () {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
    },
    home: function (user) {
      user = user || auth.user();
      return (user && user.role === 'recruiter') ? '/dashboard' : '/mes-candidatures';
    },
    logout: async function (e) {
      if (e) e.preventDefault();
      try { await SR.api.post('/logout'); } catch (err) {}
      auth.clear();
      window.location.href = '/';
    },
  };
  SR.auth = auth;

  /* ---------------- API ---------------- */
  async function apiFetch(path, opts) {
    opts = opts || {};
    const headers = opts.headers || {};
    headers['Accept'] = 'application/json';
    if (!(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    const token = auth.token();
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(API + path, { ...opts, headers });

    if (res.status === 401 && !path.includes('/login') && !path.includes('/register')) {
      if (!USE_MOCKS) {
        auth.clear();
        window.location.href = '/connexion';
      }
      throw new Error('Unauthenticated');
    }

    if (res.status === 204) return null;
    const contentType = res.headers.get('content-type') || '';
    const body = contentType.includes('application/json') ? await res.json() : await res.text();
    if (!res.ok) {
      const msg = (body && body.message) || 'Erreur ' + res.status;
      const err = new Error(msg);
      err.status = res.status;
      err.body = body;
      throw err;
    }
    return body;
  }

  const api = {
    get: (p) => apiFetch(p),
    post: (p, b) => apiFetch(p, { method: 'POST', body: JSON.stringify(b || {}) }),
    put: (p, b) => apiFetch(p, { method: 'PUT', body: JSON.stringify(b || {}) }),
    del: (p) => apiFetch(p, { method: 'DELETE' }),
    form: (p, fd) => apiFetch(p, { method: 'POST', body: fd }),
  };
  SR.api = api;

  /* ---------------- Kanban DnD (reusable) ----------------
     Usage:
       SR.kanban.enable(boardEl, {
         onDrop(card, fromStatus, toStatus, done)  // persist; call done(true|false)
         canDrop(fromStatus, toStatus)             // optional state-machine guard
         onOptimistic(card, toStatus)              // optional local state sync (demo)
       });
     Cards:   .kanban-card[draggable=true][data-id][data-status]
     Columns: .kanban-col with .kanban-count in the head.
  */
  const kanban = {
    dragId: null,
    fromStatus: null,
    fromCol: null,
    beforeEl: null,

    enable: function (board, opts) {
      if (!board || board.dataset.kanban) return;
      board.dataset.kanban = '1';
      this.bind(board, opts || {});
    },

    /* Mark every card draggable — call again after a re-render. */
    markDraggable: function (board) {
      if (!board) return;
      board.querySelectorAll('.kanban-card').forEach(function (c) {
        c.draggable = true;
      });
    },

    bind: function (board, opts) {
      const cardsSel = opts.cards || '.kanban-card';
      const colSel = opts.cols || '.kanban-col';
      const self = this;

      function clearOver() {
        board.querySelectorAll(colSel).forEach(function (c) {
          c.classList.remove('drag-over');
        });
        board.querySelectorAll('.drop-ghost').forEach(function (g) {
          g.remove();
        });
      }

      function refreshCounters() {
        board.querySelectorAll(colSel).forEach(function (col) {
          const count = col.querySelector('.kanban-count');
          if (count) count.textContent = String(col.querySelectorAll(cardsSel).length);
        });
      }

      board.addEventListener('dragstart', function (e) {
        const card = e.target.closest(cardsSel);
        if (!card) return;
        self.dragId = card.dataset.id;
        self.fromStatus = card.dataset.status;
        self.fromCol = card.closest(colSel);
        self.beforeEl = card.nextElementSibling;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', String(card.dataset.id)); } catch (err) {}
      });

      board.addEventListener('dragover', function (e) {
        const col = e.target.closest(colSel);
        if (!col) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        col.classList.add('drag-over');
        // Positional drop ghost inside the target column
        const ghost = col.querySelector('.drop-ghost');
        const cards = Array.prototype.slice.call(col.querySelectorAll(cardsSel));
        const after = cards.find(function (c) {
          const r = c.getBoundingClientRect();
          return e.clientY < r.top + r.height / 2;
        }) || null;
        if (ghost) ghost.remove();
        const g = document.createElement('div');
        g.className = 'drop-ghost';
        if (after) col.insertBefore(g, after); else col.appendChild(g);
      });

      board.addEventListener('dragleave', function (e) {
        if (!e.relatedTarget || !board.contains(e.relatedTarget)) clearOver();
      });

      board.addEventListener('drop', function (e) {
        e.preventDefault();
        const col = e.target.closest(colSel);
        clearOver();
        const card = board.querySelector(cardsSel + '.dragging');
        if (!col || !card) return;
        const toStatus = col.dataset.status || col.dataset.col;
        const fromStatus = card.dataset.status;

        if (fromStatus === toStatus) {
          card.classList.remove('dragging');
          refreshCounters();
          return;
        }

        const guard = opts.canDrop ? opts.canDrop(fromStatus, toStatus) : true;
        const reject = function (msg) {
          if (self.fromCol) self.fromCol.insertBefore(card, self.beforeEl);
          card.classList.remove('dragging');
          refreshCounters();
          if (msg) toast(msg, 'error');
        };

        if (!guard) {
          reject('Cannot move from ' + fromStatus + ' to ' + toStatus);
          return;
        }

        // Optimistic move
        col.appendChild(card);
        card.dataset.status = toStatus;
        card.classList.remove('dragging');
        card.classList.add('drop-in');
        setTimeout(function () { card.classList.remove('drop-in'); }, 400);
        refreshCounters();

        if (opts.onOptimistic) opts.onOptimistic(card, toStatus);

        if (opts.onDrop) {
          opts.onDrop(card, fromStatus, toStatus, function (ok) {
            if (ok === false) reject();
          });
        }
      });

      board.addEventListener('dragend', function (e) {
        const card = e.target.closest(cardsSel);
        if (card) card.classList.remove('dragging');
        clearOver();
        refreshCounters();
        self.dragId = null;
        self.fromStatus = null;
        self.fromCol = null;
        self.beforeEl = null;
      });
    },
  };
  SR.kanban = kanban;

  /* ---------------- Mock data ---------------- */
  const MOCK_JOBS = [
    { id: 1, title: 'Développeur Laravel Senior', description: "Nous recherchons un développeur Laravel senior pour rejoindre notre équipe produit. Vous serez responsable de la conception et du développement de fonctionnalités backend critiques, de l'optimisation des performances et du mentorat des développeurs juniors.\n\nMissions :\n- Concevoir et développer des API REST robustes avec Laravel\n- Modéliser et optimiser les bases de données MySQL\n- Participer aux revues de code et au suivi qualité\n- Accompagner les développeurs juniors dans leur montée en compétences\n\nProfil : 5+ ans d'expérience PHP/Laravel, maîtrise de MySQL, Git et Docker.", tech_stack: 'PHP, Laravel, MySQL, Docker, Git', tech_stack_array: ['PHP', 'Laravel', 'MySQL', 'Docker', 'Git'], contract_type: 'CDI', salary: 18000, deadline: '2026-09-30', status: 'active', applications_count: 24, created_at: '2026-07-20T10:00:00+00:00' },
    { id: 2, title: 'Développeur Full-Stack React', description: 'Nous cherchons un développeur full-stack passionné par React et Node.js pour construire des interfaces modernes et performantes.\n\nMissions :\n- Développer des interfaces réactives avec React\n- Créer des services backend en Node.js\n- Collaborer avec les équipes design et produit', tech_stack: 'React, Node.js, TypeScript, MongoDB', tech_stack_array: ['React', 'Node.js', 'TypeScript', 'MongoDB'], contract_type: 'CDI', salary: 16500, deadline: '2026-09-15', status: 'active', applications_count: 18, created_at: '2026-07-22T14:00:00+00:00' },
    { id: 3, title: 'Développeur Backend PHP Junior', description: 'Une opportunité idéale pour un développeur junior souhaitant se former auprès d’une équipe expérimentée.\n\nMissions :\n- Développer des fonctionnalités en PHP\n- Participer à la maintenance du parc applicatif\n- Apprendre les bonnes pratiques de code', tech_stack: 'PHP, MySQL, Git', tech_stack_array: ['PHP', 'MySQL', 'Git'], contract_type: 'Stage', salary: 5000, deadline: '2026-08-30', status: 'active', applications_count: 12, created_at: '2026-07-25T09:30:00+00:00' },
    { id: 4, title: 'Ingénieur DevOps & Cloud', description: 'Nous recherchons un ingénieur DevOps pour industrialiser notre chaîne de déploiement et fiabiliser nos environnements.\n\nMissions :\n- Mettre en place CI/CD avec GitHub Actions\n- Administrer les conteneurs Docker et Kubernetes\n- Surveiller et optimiser les coûts cloud', tech_stack: 'Docker, Kubernetes, CI/CD, AWS', tech_stack_array: ['Docker', 'Kubernetes', 'CI/CD', 'AWS'], contract_type: 'CDD', salary: 19000, deadline: '2026-10-10', status: 'active', applications_count: 9, created_at: '2026-07-28T11:00:00+00:00' },
    { id: 5, title: 'Développeur Front-End Vue.js', description: 'Rejoignez une équipe moderne pour développer des interfaces élégantes avec Vue.js.\n\nMissions :\n- Développer des composants réutilisables\n- Optimiser les performances d’affichage\n- Participer à la refonte de l’application', tech_stack: 'Vue.js, JavaScript, CSS, Vite', tech_stack_array: ['Vue.js', 'JavaScript', 'CSS', 'Vite'], contract_type: 'Alternance', salary: 8000, deadline: '2026-09-05', status: 'active', applications_count: 15, created_at: '2026-07-30T15:00:00+00:00' },
    { id: 6, title: 'Data Analyst', description: 'Nous cherchons un data analyst pour transformer nos données en décisions éclairées.\n\nMissions :\n- Analyser les indicateurs de performance\n- Construire des dashboards de pilotage\n- Automatiser les rapports récurrents', tech_stack: 'SQL, Python, Power BI', tech_stack_array: ['SQL', 'Python', 'Power BI'], contract_type: 'CDI', salary: 15000, deadline: '2026-09-20', status: 'active', applications_count: 7, created_at: '2026-08-01T10:00:00+00:00' },
  ];

  const MOCK_CANDIDATES = [
    { id: 1, name: 'Sara El Amrani', role: 'candidate', email: 'sara@example.com', score: 92, status: 'interview', tags: ['prioritaire'], job: 1, applied_at: '2026-07-28T09:00:00+00:00', matched: ['PHP', 'Laravel', 'MySQL', 'Docker'], missing: ['Git'], cover_letter: 'Fort intérêt pour les projets Laravel à grande échelle. 6 ans d’expérience sur des plateformes SaaS.', notes: 'Excellent parcours, très bonnes connaissances Laravel. À faire passer en entretien.', interviews: [{ id: 1, scheduled_at: '2026-08-10T14:00:00+00:00', status: 'scheduled', link: 'https://meet.google.com/abc-defg-hij', score_technique: null, score_communication: null, score_motivation: null }] },
    { id: 2, name: 'Youssef Benali', role: 'candidate', email: 'youssef@example.com', score: 78, status: 'received', tags: ['a_relancer'], job: 1, applied_at: '2026-07-29T11:00:00+00:00', matched: ['PHP', 'Laravel'], missing: ['Docker', 'Git'], cover_letter: 'Développeur PHP confirmé, souhaitant progresser sur l’écosystème Laravel.', notes: '', interviews: [] },
    { id: 3, name: 'Amine Tazi', role: 'candidate', email: 'amine@example.com', score: 84, status: 'interview', tags: [], job: 1, applied_at: '2026-07-27T16:00:00+00:00', matched: ['PHP', 'Laravel', 'MySQL'], missing: ['Docker'], cover_letter: 'Passionné d’architecture logicielle et de bonnes pratiques.', notes: 'Candidat sérieux, entretien technique programmé.', interviews: [{ id: 2, scheduled_at: '2026-08-12T10:00:00+00:00', status: 'completed', link: '', score_technique: 4, score_communication: 5, score_motivation: 4 }] },
    { id: 4, name: 'Kenza Idrissi', role: 'candidate', email: 'kenza@example.com', score: 65, status: 'refused', tags: [], job: 1, applied_at: '2026-07-25T10:00:00+00:00', matched: ['PHP'], missing: ['Laravel', 'MySQL', 'Docker'], cover_letter: 'Débutante motivée.', notes: 'Profil junior, pas adapté au poste senior.', interviews: [] },
    { id: 5, name: 'Omar Chraibi', role: 'candidate', email: 'omar@example.com', score: 96, status: 'accepted', tags: ['entretien_planifie'], job: 1, applied_at: '2026-07-24T08:00:00+00:00', matched: ['PHP', 'Laravel', 'MySQL', 'Docker', 'Git'], missing: [], cover_letter: 'Architecte applicatif avec une forte expérience Laravel et DevOps.', notes: 'Profil exceptionnel. Offre envoyée.', interviews: [{ id: 3, scheduled_at: '2026-08-05T14:00:00+00:00', status: 'completed', link: 'https://meet.google.com/xyz', score_technique: 5, score_communication: 5, score_motivation: 5 }] },
    { id: 6, name: 'Nadia Bouhlel', role: 'candidate', email: 'nadia@example.com', score: 88, status: 'received', tags: ['reserve'], job: 2, applied_at: '2026-07-30T12:00:00+00:00', matched: ['React', 'Node.js'], missing: ['TypeScript'], cover_letter: 'Développeuse full-stack React passionnée.', notes: '', interviews: [] },
  ];

  const MOCK_DASHBOARD = {
    funnels: [
      { job_offer_id: 1, title: 'Développeur Laravel Senior', received: 24, interview: 8, accepted: 3, refused: 13, rates: { received: 100, interview: 33.3, accepted: 12.5, refused: 54.2 } },
      { job_offer_id: 2, title: 'Développeur Full-Stack React', received: 18, interview: 5, accepted: 2, refused: 11, rates: { received: 100, interview: 27.8, accepted: 11.1, refused: 61.1 } },
      { job_offer_id: 4, title: 'Ingénieur DevOps & Cloud', received: 9, interview: 3, accepted: 1, refused: 5, rates: { received: 100, interview: 33.3, accepted: 11.1, refused: 55.6 } },
    ],
    time_to_hire: { global_avg_days: 9.4, by_offer: [{ job_offer_id: 1, avg_days: 7.1 }, { job_offer_id: 2, avg_days: 11.2 }] },
    score_distribution: { '>80': 5, '50-80': 12, '<50': 7 },
    recent_activity: [
      { type: 'application', label: 'Sara El Amrani a postulé à Développeur Laravel Senior', at: '2026-08-02T10:00:00+00:00' },
      { type: 'interview', label: 'Entretien terminé pour Omar Chraibi', at: '2026-08-01T15:00:00+00:00' },
      { type: 'acceptance', label: 'Yasmine Alaoui acceptée pour Full-Stack React', at: '2026-07-31T09:00:00+00:00' },
      { type: 'application', label: 'Nadia Bouhlel a postulé à Développeur Full-Stack React', at: '2026-07-30T12:00:00+00:00' },
    ],
    offer_comparison: [
      { job_offer_id: 1, interview_to_accepted: 37.5, recruiter_avg: 30.1 },
      { job_offer_id: 2, interview_to_accepted: 40.0, recruiter_avg: 30.1 },
    ],
    pending_tasks: { interviews_to_evaluate: 2, applications_pending_over_7_days: 4 },
  };

  const MOCK_TEMPLATES = [
    { key: 'relance', label: 'Relance après candidature', subject: 'Votre candidature chez SmartRecruit', body: 'Bonjour,\n\nNous avons bien reçu votre candidature pour le poste de {job_title}. Notre équipe l’examine actuellement.\n\nNous revenons vers vous sous peu.\n\nCordialement,\nL’équipe recrutement' },
    { key: 'refus_standard', label: 'Refus standard', subject: 'Candidature non retenue', body: 'Bonjour,\n\nNous vous remercions pour votre candidature au poste de {job_title}. Après examen, nous avons décidé de ne pas la retenir à ce stade.\n\nNous vous souhaitons une belle continuation.\n\nCordialement,\nL’équipe recrutement' },
    { key: 'entretien', label: 'Invitation entretien', subject: 'Invitation à un entretien', body: 'Bonjour,\n\nVotre candidature au poste de {job_title} a retenu notre attention. Seriez-vous disponible pour un entretien ?\n\nBien à vous,\nL’équipe recrutement' },
  ];

  const MOCK_CONVERSATIONS = [
    { id: 1, context_type: 'interview_questions', context_id: 1, status: 'active', title: 'Questions — Développeur Laravel', created_at: '2026-08-01T10:00:00+00:00' },
    { id: 2, context_type: 'interview_questions', context_id: 3, status: 'active', title: 'Questions — Full-Stack React', created_at: '2026-07-30T14:00:00+00:00' },
  ];

  const MOCK_MESSAGES = {
    1: [
      { id: 1, role: 'user', content: 'Génère 3-5 questions d’entretien pour le poste : PHP, Laravel, MySQL, Docker, Git', created_at: '2026-08-01T10:00:00+00:00' },
      { id: 2, role: 'assistant', content: 'Voici 5 questions d’entretien adaptées au poste :\n\n1. Comment gérez-vous les migrations et la gestion de versions de schéma dans Laravel ?\n2. Décrivez la différence entre les query builders et Eloquent ORM. Quand privilégiez-vous l’un ou l’autre ?\n3. Comment optimisez-vous une requête MySQL lente ? Donnez un exemple concret.\n4. Expliquez comment vous structurez un pipeline de déploiement Docker pour une application Laravel.\n5. Quelles stratégies adoptez-vous pour la sécurité des API (authentification, validation, CSRF) ?', created_at: '2026-08-01T10:00:05+00:00' },
    ],
    2: [
      { id: 3, role: 'user', content: 'Génère des questions sur : React, Node.js, TypeScript, MongoDB', created_at: '2026-07-30T14:00:00+00:00' },
      { id: 4, role: 'assistant', content: '1. Comment gérez-vous le state global d’une application React ?\n2. Décrivez les différences entre TypeScript et JavaScript et les avantages du typage.\n3. Comment concevez-vous un schéma MongoDB pour une relation many-to-many ?\n4. Comment sécurisez-vous une API Node.js (authentification JWT, validation) ?', created_at: '2026-07-30T14:00:05+00:00' },
    ],
  };

  const MOCK_INTERVIEWS = [
    { id: 1, application_id: 1, candidate_name: 'Sara El Amrani', job_title: 'Développeur Laravel Senior', scheduled_at: '2026-08-10T14:00:00+00:00', link: 'https://meet.google.com/abc-defg-hij', status: 'scheduled', score_technique: null, score_communication: null, score_motivation: null },
    { id: 2, application_id: 3, candidate_name: 'Amine Tazi', job_title: 'Développeur Laravel Senior', scheduled_at: '2026-08-12T10:00:00+00:00', link: '', status: 'completed', score_technique: 4, score_communication: 5, score_motivation: 4 },
    { id: 3, application_id: 5, candidate_name: 'Omar Chraibi', job_title: 'Développeur Laravel Senior', scheduled_at: '2026-08-05T14:00:00+00:00', link: 'https://meet.google.com/xyz', status: 'completed', score_technique: 5, score_communication: 5, score_motivation: 5 },
    { id: 4, application_id: 1, candidate_name: 'Sara El Amrani', job_title: 'Développeur Laravel Senior', scheduled_at: '2026-08-14T09:00:00+00:00', link: '', status: 'cancelled', score_technique: null, score_communication: null, score_motivation: null },
  ];

  function mockJobs() { return MOCK_JOBS; }
  function mockJob(id) { return MOCK_JOBS.find(function (j) { return j.id === Number(id); }); }
  function mockApplications(jobId) {
    return MOCK_CANDIDATES.filter(function (c) { return !jobId || c.job === Number(jobId); })
      .map(function (c) {
        return {
          id: c.id, matching_score: c.score, matched_keywords: c.matched, missing_keywords: c.missing,
          tags: c.tags, status: c.status, cv_path: '', cover_letter: c.cover_letter, notes: c.notes,
          comments: '', candidate: { id: c.id, name: c.name, email: c.email, role: 'candidate', avatar: null, badges: c.status === 'accepted' ? [{ type: 'interview_passed', awarded_at: '2026-08-05T00:00:00+00:00' }] : [] },
          job_offer: mockJob(c.job), interviews: c.interviews, created_at: c.applied_at,
        };
      });
  }
  function mockApplication(id) {
    const c = MOCK_CANDIDATES.find(function (x) { return x.id === Number(id); });
    return mockApplications().find(function (a) { return a.id === Number(id); }) || (c ? {
      id: c.id, matching_score: c.score, matched_keywords: c.matched, missing_keywords: c.missing,
      tags: c.tags, status: c.status, cv_path: '', cover_letter: c.cover_letter, notes: c.notes, comments: '',
      candidate: { id: c.id, name: c.name, email: c.email, role: 'candidate', avatar: null, badges: [] },
      job_offer: mockJob(c.job), interviews: c.interviews, created_at: c.applied_at,
    } : null);
  }
  function mockSuggestions(appId) {
    const cur = mockApplication(appId);
    const others = MOCK_CANDIDATES.filter(function (c) { return c.id !== Number(appId) && c.job !== (cur && cur.job_offer ? cur.job_offer.id : 0); });
    return others.slice(0, 3).map(function (c) {
      return { id: c.id, matching_score: c.score, matched_keywords: c.matched, missing_keywords: c.missing, status: c.status, candidate: { id: c.id, name: c.name, email: c.email, role: 'candidate', avatar: null, badges: [] }, job_offer: mockJob(c.job), created_at: c.applied_at };
    });
  }
  function mockShortlist(jobId) {
    return mockApplications(jobId).slice().sort(function (a, b) { return b.matching_score - a.matching_score; }).slice(0, 5);
  }
  function mockDashboard() { return MOCK_DASHBOARD; }
  function mockTemplates() { return MOCK_TEMPLATES; }
  function mockConversations() { return MOCK_CONVERSATIONS; }
  function mockMessages(id) { return MOCK_MESSAGES[id] || []; }
  function mockInterviews() { return MOCK_INTERVIEWS; }
  function mockSavedFilters() {
    return [
      { id: 1, name: 'Profil Laravel fort', criteria: { min_score: 80, tech_stack: ['PHP', 'Laravel'], contract_type: 'CDI', status: 'received' }, created_at: '2026-07-25T10:00:00+00:00' },
      { id: 2, name: 'Backlog entretiens', criteria: { min_score: 60, status: 'interview' }, created_at: '2026-07-28T09:00:00+00:00' },
    ];
  }

  SR.mock = {
    jobs: mockJobs, job: mockJob, applications: mockApplications, application: mockApplication,
    suggestions: mockSuggestions, shortlist: mockShortlist, dashboard: mockDashboard,
    templates: mockTemplates, conversations: mockConversations, messages: mockMessages,
    interviews: mockInterviews, savedFilters: mockSavedFilters,
  };

  /* ---------------- Data loader with mock fallback ---------------- */
  async function load(path, mockFn) {
    if (!auth.token() || !USE_MOCKS) return mockFn();
    try {
      const res = await api.get(path);
      if (Array.isArray(res)) return normalize(res);
      if (res && Array.isArray(res.data)) return normalize(res.data);
      if (res && res.data && typeof res.data === 'object') return normalizeOne(res.data);
      return res;
    } catch (e) {
      if (!USE_MOCKS) throw e;
      return mockFn();
    }
  }
  function normalize(list) {
    return (list || []).map(normalizeOne);
  }
  function normalizeOne(a) {
    if (a && a.analysis) {
      a.matching_score = a.analysis.matching_score;
      a.matched_keywords = a.analysis.matched_keywords;
      a.missing_keywords = a.analysis.missing_keywords;
    }
    return a;
  }
  SR.load = load;

  /* ---------------- Global search ---------------- */
  function bindGlobalSearch() {
    const input = document.getElementById('globalSearchInput');
    if (!input) return;
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && input.value.trim()) {
        window.location.href = '/recruteur/offres?search=' + encodeURIComponent(input.value.trim());
      }
    });
  }

  /* ---------------- Page init dispatcher ---------------- */
  function guard() {
    const page = document.body.dataset.page;
    const role = document.body.dataset.role;
    const token = auth.token();
    const user = auth.user();

    if (role) {
      if (!token) { window.location.replace('/connexion'); return false; }
      if (user && user.role && user.role !== role) { window.location.replace(auth.home(user)); return false; }
    }
    if (token && user && user.role) {
      if (page === 'login' || page === 'register') {
        window.location.replace(auth.home(user));
        return false;
      }
    }
    return true;
  }

  function dispatch() {
    const page = document.body.dataset.page;
    if (guard() && page && SR.pages && SR.pages[page] && typeof SR.pages[page].init === 'function') {
      try { SR.pages[page].init(); } catch (e) { console.error('Page init error:', e); }
    }
  }

  /* ---------------- Sidebar user (from localStorage) ---------------- */
  function bindSidebarUser() {
    const user = auth.user();
    const navRole = document.body.dataset.role || (user && user.role);
    if (navRole) {
      document.querySelectorAll('[data-nav]').forEach(function (el) {
        el.style.display = (el.dataset.nav === navRole) ? '' : 'none';
      });
      const roleLabel = document.getElementById('sidebarRole');
      if (roleLabel) roleLabel.textContent = navRole === 'recruiter' ? 'Recruteur' : 'Candidat';
    }
    if (!user) return;
    const name = document.getElementById('sidebarName');
    if (name) name.textContent = user.name;
    const avatar = document.getElementById('sidebarAvatar');
    if (avatar) avatar.textContent = initials(user.name);
    const topAvatar = document.getElementById('topbarAvatar');
    if (topAvatar) topAvatar.textContent = initials(user.name);
  }

  SR.pages = SR.pages || {};

  /* ---------------- Login page ---------------- */
  SR.pages.login = {
    init: function () {
      const form = document.getElementById('loginForm');
      if (!form) return;
      if (SR.auth.token()) { window.location.href = SR.auth.home(); return; }
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = form.querySelector('[type="submit"]');
        const payload = { email: form.email.value.trim(), password: form.password.value };
        SR.helpers.setLoading(btn, true);
        try {
          const res = await SR.api.post('/login', payload);
          SR.auth.set(res);
          toast('Logged in. Redirecting…', 'success');
          window.location.href = SR.auth.home(res.user);
        } catch (err) {
          SR.helpers.setLoading(btn, false);
          showFormError(form, err.message || 'Invalid email or password');
        }
      });
    },
  };

  /* ---------------- Register page ---------------- */
  SR.pages.register = {
    init: function () {
      const form = document.getElementById('registerForm');
      if (!form) return;
      if (SR.auth.token()) { window.location.href = SR.auth.home(); return; }
      let selectedRole = 'recruiter';
      const opts = form.querySelectorAll('.role-option');
      opts.forEach(function (opt) {
        opt.addEventListener('click', function () {
          opts.forEach(function (o) { o.classList.remove('active'); o.setAttribute('aria-pressed', 'false'); });
          opt.classList.add('active');
          opt.setAttribute('aria-pressed', 'true');
          selectedRole = opt.dataset.role;
        });
      });
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (form.password.value !== form.password_confirmation.value) {
          showFormError(form, 'Passwords do not match.');
          return;
        }
        const btn = form.querySelector('[type="submit"]');
        const payload = {
          name: form.name.value.trim(),
          email: form.email.value.trim(),
          password: form.password.value,
          password_confirmation: form.password_confirmation.value,
          role: selectedRole,
        };
        SR.helpers.setLoading(btn, true);
        try {
          const res = await SR.api.post('/register', payload);
          SR.auth.set(res);
          toast('Account created. Welcome!', 'success');
          window.location.href = SR.auth.home(res.user);
        } catch (err) {
          SR.helpers.setLoading(btn, false);
          const msg = (err.body && err.body.errors)
            ? Object.values(err.body.errors).flat().join(' / ')
            : (err.message || 'Unable to create your account');
          showFormError(form, msg);
        }
      });
    },
  };

  /* ---------------- Recruiter: dashboard ---------------- */
  SR.pages.dashboard = {
    init: function () {
      const board = document.getElementById('kanbanBoard');
      if (!board) return;

      let apps = [];

      const NEXT_STATUS = {
        received: 'interview', interview: 'accepted', accepted: null, refused: null,
      };
      const NEXT_LABEL = { received: 'Entretien', interview: 'Accepter' };

      function colApps(status) {
        return apps.filter(function (a) { return a.status === status; });
      }

      function renderKanban() {
        const cols = ['received', 'interview', 'accepted', 'refused'];
        board.innerHTML = cols.map(function (st) {
          const items = colApps(st);
          const dots = { received: 'var(--pink)', interview: 'var(--pink-light)', accepted: 'var(--success)', refused: 'var(--danger)' };
          return '<div class="kanban-col"><div class="kanban-col-head">' +
            '<span class="kanban-col-title"><i class="kanban-col-dot" style="background:' + dots[st] + '"></i> ' + STATUS_LABELS[st] + '</span>' +
            '<span class="kanban-count">' + items.length + '</span></div>' +
            items.map(function (a) {
              const next = NEXT_STATUS[a.status];
              const nextBtn = next
                ? '<button class="btn btn-sm kanban-move" data-id="' + a.id + '" data-next="' + next + '">→ ' + NEXT_LABEL[a.status] + '</button>' +
                  (a.status === 'interview' ? '' : '<button class="btn btn-sm btn-ghost-danger kanban-move" data-id="' + a.id + '" data-next="refused">Refuser</button>')
                : '';
              return '<div class="kanban-card">' +
                '<div class="kanban-card-top">' +
                '<div style="display:flex;align-items:center;gap:10px">' + scoreRing(a.matching_score, 'sm') +
                '<div><div class="kanban-cand-name">' + escapeHtml(a.candidate.name) + '</div>' +
                '<div class="kanban-cand-job">' + escapeHtml((a.job_offer && a.job_offer.title) || '') + '</div></div></div>' +
                '</div>' +
                '<div class="kanban-tags">' + (a.tags || []).map(tagChip).join('') + '</div>' +
                '<div class="kanban-card-foot">' + nextBtn + '</div>' +
                '</div>';
            }).join('') + '</div>';
        }).join('');
      }

      board.addEventListener('click', function (e) {
        const btn = e.target.closest('.kanban-move');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        const next = btn.dataset.next;
        const app = apps.find(function (a) { return a.id === id; });
        if (!app) return;
        app.status = next;
        renderKanban();
        toast(app.candidate.name + ' → ' + (STATUS_LABELS[next] || next).toLowerCase(), 'success');
      });

      function renderStats() {
        const grid = document.getElementById('statGrid');
        const funnels = dash.funnels || [];
        const received = funnels.reduce(function (s, f) { return s + (f.received || 0); }, 0);
        const inInterview = funnels.reduce(function (s, f) { return s + (f.interview || 0); }, 0);
        const scores = apps.map(function (a) { return Number(a.matching_score) || 0; });
        const avg = scores.length ? (scores.reduce(function (s, v) { return s + v; }, 0) / scores.length) : 0;
        const stats = [
          { label: 'Offres actives', value: String(funnels.length || SR.mock.jobs().length), icon: 'briefcase', cls: 'blue', delta: 'publiées sur la plateforme' },
          { label: 'Candidatures reçues', value: String(received), icon: 'users', cls: 'green', delta: 'au total sur toutes les offres' },
          { label: 'En entretien', value: String(inInterview), icon: 'calendar', cls: 'amber', delta: 'candidatures en cours de traitement' },
          { label: 'Score moyen', value: Math.round(avg) + ' / 100', icon: 'target', cls: 'red', delta: 'compatibilité IA moyenne' },
        ];
        grid.innerHTML = stats.map(function (s) {
          return '<div class="card card-pad stat-card"><div class="stat-top">' +
            '<span class="stat-label">' + s.label + '</span>' +
            '<span class="stat-icon ' + s.cls + '">' + SR.icon(s.icon) + '</span></div>' +
            '<div class="stat-value">' + s.value + '</div>' +
            '<div class="stat-delta">' + s.delta + '</div></div>';
        }).join('');
      }

      function renderFunnels() {
        const box = document.getElementById('funnelBox');
        const funnels = dash.funnels || [];
        if (!funnels.length) { box.innerHTML = '<div class="empty">Aucune offre.</div>'; return; }
        box.innerHTML = funnels.map(function (f) {
          const seg = [
            ['received', f.received], ['interview', f.interview], ['accepted', f.accepted], ['refused', f.refused],
          ];
          const total = seg.reduce(function (s, x) { return s + x[1]; }, 0) || 1;
          const parts = seg.map(function (s) {
            const pct = (s[1] / total) * 100;
            return pct > 0 ? '<span class="funnel-seg ' + s[0] + '" style="width:' + pct + '%" title="' + STATUS_LABELS[s[0]] + ' : ' + s[1] + '"></span>' : '';
          }).join('');
          return '<div class="funnel-row">' +
            '<div class="funnel-head"><span class="funnel-title">' + escapeHtml(f.title) + '</span>' +
            '<span class="funnel-sub">' + total + ' candidatures</span></div>' +
            '<div class="funnel-track">' + parts + '</div></div>';
        }).join('');
      }

      function renderScoreDist() {
        const box = document.getElementById('scoreDistBox');
        const d = dash.score_distribution || { '>80': 0, '50-80': 0, '<50': 0 };
        const rows = [['> 80', d['>80'], 'var(--success)'], ['50 – 80', d['50-80'], 'var(--warning)'], ['< 50', d['<50'], 'var(--danger)']];
        const max = Math.max(rows[0][1], rows[1][1], rows[2][1], 1);
        box.innerHTML = rows.map(function (r) {
          return '<div class="dist-row"><span class="dist-label">' + r[0] + '</span>' +
            '<div class="dist-track"><div class="dist-fill" style="width:' + (r[1] / max * 100) + '%;background:' + r[2] + '"></div></div>' +
            '<span class="dist-count">' + r[1] + '</span></div>';
        }).join('');
      }

      function renderActivity() {
        const box = document.getElementById('activityBox');
        const list = dash.recent_activity || [];
        if (!list.length) { box.innerHTML = '<div class="empty">Aucune activité.</div>'; return; }
        const ic = { application: 'upload', interview: 'calendar', acceptance: 'check', refusal: 'x' };
        box.innerHTML = list.map(function (a) {
          return '<li class="activity-item"><span class="act-ic">' + SR.icon(ic[a.type] || 'info', 16) + '</span>' +
            '<div><div class="act-label">' + escapeHtml(a.label) + '</div>' +
            '<div class="act-at">' + fmtDateTime(a.at) + '</div></div></li>';
        }).join('');
      }

      function renderPending() {
        const box = document.getElementById('pendingBox');
        const p = dash.pending_tasks || {};
        const tasks = [
          { label: 'Entretiens à évaluer', count: p.interviews_to_evaluate, dot: 'amber' },
          { label: 'Candidatures en attente depuis +7 jours', count: p.applications_pending_over_7_days, dot: 'red' },
        ];
        box.innerHTML = tasks.map(function (t) {
          return '<div class="task-item"><span class="task-dot ' + t.dot + '"></span>' +
            '<span style="flex:1;font-size:13.5px;color:var(--ink)">' + t.label + '</span>' +
            '<span class="mono" style="font-weight:600;color:var(--navy)">' + t.count + '</span></div>';
        }).join('');
      }

      function renderCompare() {
        const box = document.getElementById('offerCompareBox');
        const list = dash.offer_comparison || [];
        if (!list.length) { box.innerHTML = '<div class="empty">Aucune donnée.</div>'; return; }
        box.innerHTML = list.map(function (o) {
          const pct = o.interview_to_accepted || 0;
          return '<div class="funnel-row">' +
            '<div class="funnel-head"><span class="funnel-title">Offre #' + o.job_offer_id + '</span>' +
            '<span class="funnel-sub">' + pct + '% vs moyenne ' + o.recruiter_avg + '%</span></div>' +
            '<div class="dist-track"><div class="dist-fill" style="width:' + Math.min(pct, 100) + '%;background:var(--blue)"></div></div></div>';
        }).join('');
      }

      let dash = {};
      Promise.all([
        SR.load('/dashboard/stats', SR.mock.dashboard),
      ]).then(function (results) {
        dash = results[0] || {};
        apps = SR.mock.applications();
        renderKanban();
        renderStats();
        renderFunnels();
        renderScoreDist();
        renderActivity();
        renderPending();
        renderCompare();
        const sub = document.getElementById('dashSub');
        if (sub) {
          const u = auth.user();
          sub.textContent = (u ? 'Bienvenue, ' + u.name + '.' : '') + ' Vue d\'ensemble de votre activité de recrutement.';
        }
      }).catch(function () {
        board.innerHTML = '<div class="empty">Impossible de charger le tableau de bord.</div>';
      });
    },
  };

  function showFormError(form, message) {
    const wrap = form.querySelector('.form-alert');
    if (wrap) {
      wrap.textContent = message;
      wrap.style.display = 'block';
    } else {
      toast(message, 'error');
    }
  }

  function debounce(fn, ms) {
    let t;
    return function () { clearTimeout(t); t = setTimeout(fn, ms || 250); };
  }

  /* ---------------- Public: job offers index ---------------- */
  SR.pages.jobsIndex = {
    init: function () {
      const wrap = document.getElementById('jobsList');
      if (!wrap) return;
      let jobs = [];

      function render(list) {
        if (!list.length) {
          wrap.innerHTML = '<div class="empty"><p>No open positions match your criteria.</p></div>';
          return;
        }
        wrap.innerHTML = list.map(function (j) {
          const chips = (j.tech_stack_array || []).map(function (t) { return '<span class="chip">' + escapeHtml(t) + '</span>'; }).join('');
          const apps = j.applications_count != null
            ? j.applications_count + ' application' + (j.applications_count === 1 ? '' : 's')
            : 'Open for applications';
          return '<a class="job-card" href="/offres/' + j.id + '">' +
            '<div class="job-card-top"><h3>' + escapeHtml(j.title) + '</h3>' +
            '<span class="pill">' + escapeHtml(j.contract_type) + '</span></div>' +
            '<p class="job-card-desc">' + escapeHtml(j.description).slice(0, 160) + '…</p>' +
            '<div class="chips">' + chips + '</div>' +
            '<div class="job-card-meta">' +
            '<span>' + SR.icon('briefcase', 15) + ' ' + apps + '</span>' +
            '<span>' + SR.icon('calendar', 15) + ' Deadline: ' + fmtDate(j.deadline) + '</span>' +
            '<span>' + SR.icon('note', 15) + ' ' + fmtSalary(j.salary) + '</span>' +
            '</div></a>';
        }).join('');
      }

      function applyFilters() {
        const q = (searchInput.value || '').toLowerCase().trim();
        const ct = contractSelect.value;
        const filtered = jobs.filter(function (j) {
          const hay = (j.title + ' ' + j.description + ' ' + j.tech_stack).toLowerCase();
          const okQ = !q || hay.indexOf(q) !== -1;
          const okCt = !ct || j.contract_type === ct;
          return okQ && okCt;
        });
        render(filtered);
      }

      const searchInput = document.getElementById('jobSearch');
      const contractSelect = document.getElementById('contractFilter');
      if (searchInput) searchInput.addEventListener('input', debounce(applyFilters));
      if (contractSelect) contractSelect.addEventListener('change', applyFilters);

      SR.load('/job-offers', SR.mock.jobs).then(function (data) {
        jobs = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
        applyFilters();
      }).catch(function () { render(jobs); });
    },
  };

  /* ---------------- Public: job offer detail ---------------- */
  SR.pages.jobShow = {
    init: function () {
      const wrap = document.getElementById('jobDetail');
      if (!wrap) return;
      const id = Number(wrap.dataset.jobId);

      SR.load('/job-offers/' + id, function () { return SR.mock.job(id); }).then(function (j) {
        if (!j) { wrap.innerHTML = '<div class="empty"><p>Position not found.</p></div>'; return; }
        const chips = (j.tech_stack_array || []).map(function (t) { return '<span class="chip">' + escapeHtml(t) + '</span>'; }).join('');
        const appCount = j.applications_count != null
          ? j.applications_count + ' application' + (j.applications_count === 1 ? '' : 's')
          : 'Several applications';
        const meta = [
          ['briefcase', 'Contract: ' + escapeHtml(j.contract_type)],
          ['note', 'Salary: ' + fmtSalary(j.salary)],
          ['calendar', 'Deadline: ' + fmtDate(j.deadline)],
          ['users', appCount],
          ['clock', 'Posted ' + fmtDate(j.created_at)],
        ];
        wrap.innerHTML =
          '<div class="detail-hero">' +
          '<div class="detail-hero-main"><span class="pill">' + escapeHtml(j.contract_type) + '</span>' +
          '<h1>' + escapeHtml(j.title) + '</h1>' +
          '<div class="chips">' + chips + '</div></div>' +
          '<a class="btn btn-primary" id="applyCta" href="' + applyHref() + '">' + SR.icon('arrowRight', 16) + ' Apply</a>' +
          '</div>' +
          '<div class="detail-grid">' +
          '<div class="detail-main"><h2>Job description</h2><div class="prose">' + escapeHtml(j.description).replace(/\n/g, '<br>') + '</div></div>' +
          '<aside class="detail-side">' +
          '<div class="side-card"><h3>Details</h3>' +
          meta.map(function (m) { return '<div class="side-row">' + SR.icon(m[0], 16) + '<span>' + m[1] + '</span></div>'; }).join('') +
          '</div></aside></div>';

        function applyHref() {
          const u = auth.user();
          if (!u) return '/connexion';
          if (u.role === 'candidate') return '/postuler/' + j.id;
          return '/mes-candidatures';
        }
      }).catch(function () { wrap.innerHTML = '<div class="empty"><p>Unable to load this position.</p></div>'; });
    },
  };

  /* ---------------- Recruiter: jobs management ---------------- */
  SR.pages.recruiterJobs = {
    init: function () {
      const tbody = document.getElementById('jobsTbody');
      if (!tbody) return;
      const searchInput = document.getElementById('jobsSearch');
      const statusSelect = document.getElementById('jobsStatusFilter');
      let jobs = [];

      function render() {
        const q = (searchInput.value || '').toLowerCase().trim();
        const st = statusSelect.value;
        const list = jobs.filter(function (j) {
          const hay = (j.title + ' ' + (j.tech_stack || '') + ' ' + (j.contract_type || '')).toLowerCase();
          const okQ = !q || hay.indexOf(q) !== -1;
          const okS = !st || j.status === st;
          return okQ && okS;
        });
        if (!list.length) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--slate);padding:40px">Aucune offre trouvée.</td></tr>';
          return;
        }
        tbody.innerHTML = list.map(function (j) {
          const chips = (j.tech_stack_array || []).slice(0, 3).map(function (t) {
            return '<span class="chip" style="font-size:11.5px">' + escapeHtml(t) + '</span>';
          }).join('');
          return '<tr>' +
            '<td><div style="font-weight:600;color:var(--ink)">' + escapeHtml(j.title) + '</div>' +
            '<div class="chips" style="gap:4px;margin-top:4px">' + chips + '</div></td>' +
            '<td><span class="pill">' + escapeHtml(j.contract_type) + '</span></td>' +
            '<td class="mono" style="color:var(--slate)">' + fmtDate(j.deadline) + '</td>' +
            '<td style="color:var(--ink)">' + (j.applications_count != null ? j.applications_count : 0) + '</td>' +
            '<td>' + statusPill(j.status) + '</td>' +
            '<td style="text-align:right;white-space:nowrap">' +
            '<a class="btn btn-sm btn-ghost" href="/recruteur/offres/' + j.id + '/candidatures">Candidatures</a> ' +
            '<a class="btn btn-sm btn-ghost" href="/recruteur/offres/' + j.id + '/modifier">Modifier</a> ' +
            '<button class="btn btn-sm btn-ghost-danger" data-action="archive" data-id="' + j.id + '" data-title="' + escapeHtml(j.title) + '">Archiver</button>' +
            '</td></tr>';
        }).join('');
      }

      tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action="archive"]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        SR.modal.open(
          '<p style="font-size:14px;color:var(--slate);margin:0 0 18px">Archiver l\'offre « ' + btn.dataset.title + ' » ? Elle sera masquée du site public.</p>' +
          '<div style="display:flex;justify-content:flex-end;gap:10px">' +
          '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
          '<button class="btn btn-danger" id="confirmArchive">Archiver</button></div>',
          { title: 'Archiver l\'offre' }
        );
        document.getElementById('confirmArchive').addEventListener('click', async function () {
          try { await SR.api.del('/job-offers/' + id); } catch (err) { if (!USE_MOCKS) throw err; }
          jobs = jobs.filter(function (j) { return j.id !== id; });
          SR.modal.close();
          render();
          toast('Offre archivée', 'success');
        });
      });

      if (searchInput) searchInput.addEventListener('input', debounce(render));
      if (statusSelect) statusSelect.addEventListener('change', render);

      SR.load('/job-offers', SR.mock.jobs).then(function (data) {
        jobs = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
        render();
      }).catch(function () { render(); });
    },
  };

  /* ---------------- Recruiter: job offer create/edit form ---------------- */
  SR.pages.recruiterJobForm = {
    init: function () {
      const form = document.getElementById('jobForm');
      if (!form) return;
      const mode = document.body.dataset.mode || 'create';
      const jobId = Number(document.body.dataset.jobId || 0);

      if (mode === 'edit' && jobId) {
        SR.load('/job-offers/' + jobId, function () { return SR.mock.job(jobId); }).then(function (j) {
          if (!j) return;
          form.title.value = j.title || '';
          form.contract_type.value = j.contract_type || 'CDI';
          form.tech_stack.value = j.tech_stack || '';
          form.salary.value = j.salary || '';
          form.deadline.value = j.deadline ? String(j.deadline).slice(0, 10) : '';
          form.description.value = j.description || '';
        });
      }

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = form.querySelector('[type="submit"]');
        const payload = {
          title: form.title.value.trim(),
          contract_type: form.contract_type.value,
          tech_stack: form.tech_stack.value.trim(),
          salary: form.salary.value ? Number(form.salary.value) : null,
          deadline: form.deadline.value,
          description: form.description.value.trim(),
        };
        if (!payload.title || !payload.tech_stack || !payload.deadline || payload.description.length < 20) {
          showFormError(form, 'Veuillez remplir tous les champs obligatoires (description 20+ caractères).');
          return;
        }
        SR.helpers.setLoading(btn, true);
        try {
          if (mode === 'edit') await SR.api.put('/job-offers/' + jobId, payload);
          else await SR.api.post('/job-offers', payload);
          toast('Offre ' + (mode === 'edit' ? 'modifiée' : 'publiée') + ' avec succès', 'success');
          setTimeout(function () { window.location.href = '/recruteur/offres'; }, 700);
        } catch (err) {
          SR.helpers.setLoading(btn, false);
          if (USE_MOCKS) {
            toast('Offre ' + (mode === 'edit' ? 'modifiée' : 'publiée') + ' (démo)', 'success');
            setTimeout(function () { window.location.href = '/recruteur/offres'; }, 700);
            return;
          }
          showFormError(form, err.message || 'Erreur lors de l\'enregistrement');
        }
      });
    },
  };

  /* ---------------- Recruiter: applications index (all / by job) ---------------- */
  SR.pages.recruiterApplications = {
    init: function () {
      const tbody = document.getElementById('appsTbody');
      if (!tbody) return;
      const jobId = document.body.dataset.jobId === 'null' ? null : Number(document.body.dataset.jobId);
      const searchInput = document.getElementById('appsSearch');
      const statusFilter = document.getElementById('appsStatusFilter');
      const scoreFilter = document.getElementById('appsScoreFilter');
      const selectAll = document.getElementById('appsSelectAll');
      const batchBar = document.getElementById('batchBar');
      const batchCount = document.getElementById('batchCount');
      const batchStatus = document.getElementById('batchStatus');
      const savedFilterSelect = document.getElementById('savedFilterSelect');
      const title = document.getElementById('appsTitle');
      let apps = [];
      let selected = [];

      function loadPath() {
        return jobId ? '/job-offers/' + jobId + '/applications' : '/applications';
      }
      function mockFn() {
        return SR.mock.applications(jobId);
      }

      function render() {
        const q = (searchInput.value || '').toLowerCase().trim();
        const st = statusFilter.value;
        const sc = scoreFilter.value ? Number(scoreFilter.value) : 0;
        const list = apps.filter(function (a) {
          const name = ((a.candidate && a.candidate.name) || '') + ' ' + ((a.candidate && a.candidate.email) || '');
          const okQ = !q || name.toLowerCase().indexOf(q) !== -1;
          const okS = !st || a.status === st;
          const okSc = Number(a.matching_score) >= sc;
          return okQ && okS && okSc;
        });
        if (!list.length) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--slate);padding:40px">Aucune candidature trouvée.</td></tr>';
          selectAll.checked = false;
          updateBatch();
          return;
        }
        tbody.innerHTML = list.map(function (a) {
          const name = (a.candidate && a.candidate.name) || 'Candidat';
          const email = (a.candidate && a.candidate.email) || '';
          const jobTitle = (a.job_offer && a.job_offer.title) || '—';
          return '<tr>' +
            '<td><input type="checkbox" class="app-check" data-id="' + a.id + '"' + (selected.indexOf(a.id) !== -1 ? ' checked' : '') + ' aria-label="Sélectionner ' + escapeHtml(name) + '"></td>' +
            '<td><div style="display:flex;align-items:center;gap:10px">' + avatar(name, 'sm') +
            '<div><div style="font-weight:600;color:var(--ink)">' + escapeHtml(name) + '</div>' +
            '<div class="mono" style="font-size:11.5px;color:var(--slate)">' + escapeHtml(email) + '</div></div></div></td>' +
            '<td style="color:var(--ink)">' + escapeHtml(jobTitle) + '</td>' +
            '<td>' + scoreRing(a.matching_score, 'sm') + '</td>' +
            '<td>' + (a.tags || []).map(tagChip).join('') + '</td>' +
            '<td>' + statusPill(a.status) + '</td>' +
            '<td class="mono" style="color:var(--slate)">' + fmtDate(a.created_at) + '</td>' +
            '<td style="text-align:right;white-space:nowrap"><a class="btn btn-sm btn-ghost" href="/recruteur/candidatures/' + a.id + '">Voir</a></td>' +
            '</tr>';
        }).join('');
      }

      function updateBatch() {
        selected = selected.filter(function (id) { return apps.some(function (a) { return a.id === id; }); });
        batchCount.textContent = selected.length + ' sélectionnée(s)';
        batchBar.style.display = selected.length ? 'flex' : 'none';
        const checks = Array.prototype.slice.call(tbody.querySelectorAll('.app-check'));
        selectAll.checked = checks.length > 0 && checks.every(function (c) { return c.checked; });
      }

      tbody.addEventListener('change', function (e) {
        const c = e.target.closest('.app-check');
        if (!c) return;
        const id = Number(c.dataset.id);
        const i = selected.indexOf(id);
        if (c.checked && i === -1) selected.push(id);
        if (!c.checked && i !== -1) selected.splice(i, 1);
        updateBatch();
      });
      if (selectAll) selectAll.addEventListener('change', function () {
        Array.prototype.slice.call(tbody.querySelectorAll('.app-check')).forEach(function (c) {
          c.checked = selectAll.checked;
          const id = Number(c.dataset.id);
          const i = selected.indexOf(id);
          if (selectAll.checked && i === -1) selected.push(id);
          if (!selectAll.checked && i !== -1) selected.splice(i, 1);
        });
        updateBatch();
      });

      document.getElementById('batchApply').addEventListener('click', async function () {
        const status = batchStatus.value;
        if (!status || !selected.length) return;
        const n = selected.length;
        try { await SR.api.put('/applications/status/batch', { ids: selected, status: status }); }
        catch (err) { if (!USE_MOCKS) { toast(err.message, 'error'); return; } }
        selected.forEach(function (id) {
          const a = apps.find(function (x) { return x.id === id; });
          if (a) a.status = status;
        });
        selected = [];
        batchStatus.value = '';
        render();
        updateBatch();
        toast('Statut mis à jour pour ' + n + ' candidature(s)', 'success');
      });
      document.getElementById('batchClear').addEventListener('click', function () {
        selected = [];
        batchStatus.value = '';
        render();
        updateBatch();
      });

      if (searchInput) searchInput.addEventListener('input', debounce(function () { render(); updateBatch(); }));
      if (statusFilter) statusFilter.addEventListener('change', function () { render(); updateBatch(); });
      if (scoreFilter) scoreFilter.addEventListener('change', function () { render(); updateBatch(); });

      // Saved filters dropdown
      SR.load('/saved-filters', SR.mock.savedFilters).then(function (filters) {
        filters = Array.isArray(filters) ? filters : [];
        if (savedFilterSelect) {
          filters.forEach(function (f) {
            const o = document.createElement('option');
            o.value = f.id;
            o.textContent = f.name;
            savedFilterSelect.appendChild(o);
          });
          savedFilterSelect.addEventListener('change', function () {
            const f = filters.find(function (x) { return String(x.id) === savedFilterSelect.value; });
            if (!f || !f.criteria) return;
            if (statusFilter && f.criteria.status) { statusFilter.value = f.criteria.status; }
            if (scoreFilter && f.criteria.min_score) { scoreFilter.value = String(f.criteria.min_score >= 80 ? 80 : f.criteria.min_score >= 60 ? 60 : 50); }
            render();
            updateBatch();
            toast('Filtre « ' + f.name + ' » appliqué', 'info');
          });
        }
      });

      SR.load(loadPath(), mockFn).then(function (data) {
        apps = Array.isArray(data) ? data : [];
        if (jobId && title) {
          const j = SR.mock.job(jobId);
          if (j) title.textContent = 'Candidatures — ' + j.title;
        }
        render();
        updateBatch();
      }).catch(function () { render(); });
    },
  };

  /* ---------------- Recruiter: application detail ---------------- */
  SR.pages.recruiterApplicationShow = {
    init: function () {
      const wrap = document.getElementById('appDetail');
      if (!wrap) return;
      const id = Number(document.body.dataset.applicationId);
      const TAGS = ['a_relancer', 'prioritaire', 'reserve', 'entretien_planifie'];
      let app = null;
      let appsOfJob = [];

      SR.load('/applications/' + id, function () { return SR.mock.application(id); }).then(function (a) {
        if (!a) { wrap.innerHTML = '<div class="card card-pad empty"><p>Candidature introuvable.</p></div>'; return; }
        app = a;
        const back = document.getElementById('appBackLink');
        back.href = '/recruteur/offres/' + (a.job_offer ? a.job_offer.id : '') + '/candidatures';
        render();
      });

      function render() {
        const a = app;
        const name = (a.candidate && a.candidate.name) || 'Candidat';
        const email = (a.candidate && a.candidate.email) || '';
        const job = a.job_offer || {};
        const matched = a.matched_keywords || [];
        const missing = a.missing_keywords || [];
        const tags = a.tags || [];
        const badges = (a.candidate && a.candidate.badges) || [];
        const interviews = a.interviews || [];

        wrap.innerHTML =
          '<div class="detail-main" style="display:grid;gap:16px">' +

          '<div class="card card-pad" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">' +
          avatar(name, '') +
          '<div style="flex:1;min-width:200px"><div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">' +
          '<h2 style="margin:0">' + escapeHtml(name) + '</h2>' + statusPill(a.status) + '</div>' +
          '<div class="mono" style="color:var(--slate);font-size:12.5px">' + escapeHtml(email) + '</div>' +
          '<div style="margin-top:6px;font-size:13.5px;color:var(--ink)">Postule à : <a href="/offres/' + job.id + '" style="color:var(--blue)">' + escapeHtml(job.title || '') + '</a></div>' +
          badges.map(function (b) { return '<span class="tag tag-green">' + escapeHtml(b.type.replace('_', ' ')) + '</span>'; }).join('') +
          '</div>' +
          '<div style="text-align:center">' + scoreRing(a.matching_score) +
          '<div style="font-size:12px;color:var(--slate);margin-top:4px">Score IA</div></div>' +
          '</div>' +

          '<div class="card card-pad"><h3>Compatibilité avec l\'offre</h3>' +
          '<div style="display:grid;gap:14px;margin-top:10px">' +
          '<div><div class="app-kw-label">Mots-clés trouvés (' + matched.length + ')</div>' +
          '<div class="chips">' + (matched.map(function (k) { return '<span class="chip chip-ok">' + escapeHtml(k) + '</span>'; }).join('') || '<span class="mono" style="color:var(--slate)">Aucun</span>') + '</div></div>' +
          '<div><div class="app-kw-label">Mots-clés manquants (' + missing.length + ')</div>' +
          '<div class="chips">' + (missing.map(function (k) { return '<span class="chip chip-no">' + escapeHtml(k) + '</span>'; }).join('') || '<span class="mono" style="color:var(--slate)">Aucun — profil complet</span>') + '</div></div>' +
          '</div></div>' +

          '<div class="card card-pad"><h3>Tags rapides</h3><div class="chips" id="tagBox">' +
          TAGS.map(function (t) {
            const on = tags.indexOf(t) !== -1;
            return '<button type="button" class="tag-chip' + (on ? ' on' : '') + '" data-tag="' + t + '" aria-pressed="' + on + '">' + tagChip(t) + '</button>';
          }).join('') + '</div></div>' +

          '<div class="card card-pad"><h3>Notes internes</h3>' +
          '<textarea class="textarea" id="appNotes" rows="4" placeholder="Observations, points à vérifier…">' + escapeHtml(a.notes || '') + '</textarea>' +
          '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px">' +
          '<button class="btn btn-primary" id="saveNotes" type="button">Enregistrer les notes</button></div></div>' +

          '<div class="card card-pad"><h3>Entretiens</h3><div id="interviewsBox">' + renderInterviews(interviews) + '</div>' +
          '<button class="btn btn-ghost" id="scheduleInterview" type="button" style="margin-top:12px">' + SR.icon('calendar', 15) + ' Planifier un entretien</button></div>' +

          '<div class="card card-pad"><h3>Décision</h3>' + renderStatusActions(a.status) + '</div>' +

          suggestionsCard(a) +

          '</div>';

        bindDetail();
      }

      function renderInterviews(list) {
        if (!list.length) return '<div class="empty" style="padding:16px">Aucun entretien planifié.</div>';
        return '<div style="display:grid;gap:10px">' + list.map(function (iv) {
          const avg = [iv.score_technique, iv.score_communication, iv.score_motivation].filter(function (s) { return s != null; });
          const avgTxt = avg.length ? ' — Moyenne : ' + (avg.reduce(function (s, x) { return s + x; }, 0) / avg.length).toFixed(1) + '/5' : '';
          return '<div style="display:flex;align-items:center;gap:10px;justify-content:space-between;border:1px solid var(--line);border-radius:10px;padding:10px 12px">' +
            '<div><span class="pill">' + escapeHtml(STATUS_LABELS[iv.status] || iv.status) + '</span> <span class="mono" style="font-size:13px">' + fmtDateTime(iv.scheduled_at) + '</span>' + avgTxt + '</div>' +
            (iv.status === 'scheduled'
              ? '<span><button class="btn btn-sm btn-ghost" data-iv="complete" data-id="' + iv.id + '">Noter</button> <button class="btn btn-sm btn-ghost-danger" data-iv="cancel" data-id="' + iv.id + '">Annuler</button></span>'
              : '') + '</div>';
        }).join('') + '</div>';
      }

      function renderStatusActions(status) {
        if (status === 'accepted' || status === 'refused') {
          return '<p class="mono" style="color:var(--slate);margin:0">Statut terminal — aucune action possible.</p>';
        }
        const next = status === 'received' ? [['interview', '→ Entretien'], ['refused', 'Refuser']] : [['accepted', '→ Accepter'], ['refused', 'Refuser']];
        return '<div style="display:flex;gap:10px;flex-wrap:wrap">' + next.map(function (n) {
          const danger = n[0] === 'refused';
          return '<button class="btn ' + (danger ? 'btn-danger' : 'btn-primary') + '" data-action="status" data-status="' + n[0] + '" type="button">' + n[1] + '</button>';
        }).join('') + '</div>';
      }

      function suggestionsCard(a) {
        if (a.status !== 'refused') return '';
        return '<div class="card card-pad"><h3>Profils similaires suggérés</h3><div id="suggBox">' +
          '<button class="btn btn-ghost" id="loadSugg" type="button">Afficher les suggestions</button></div></div>';
      }

      function bindDetail() {
        // Tags
        const tagBox = document.getElementById('tagBox');
        if (tagBox) tagBox.addEventListener('click', function (e) {
          const btn = e.target.closest('.tag-chip');
          if (!btn) return;
          const t = btn.dataset.tag;
          const tags = app.tags || [];
          const i = tags.indexOf(t);
          if (i === -1) tags.push(t); else tags.splice(i, 1);
          app.tags = tags;
          btn.classList.toggle('on');
          btn.setAttribute('aria-pressed', tags.indexOf(t) !== -1);
          SR.api.put('/applications/' + app.id + '/tags', { tags: tags }).catch(function () {});
        });

        // Notes
        const saveNotes = document.getElementById('saveNotes');
        if (saveNotes) saveNotes.addEventListener('click', async function () {
          const notes = document.getElementById('appNotes').value;
          app.notes = notes;
          try { await SR.api.put('/applications/' + app.id + '/notes', { notes: notes }); }
          catch (err) { if (!USE_MOCKS) { toast(err.message, 'error'); return; } }
          toast('Notes enregistrées', 'success');
        });

        // Status actions
        wrap.addEventListener('click', function (e) {
          const btn = e.target.closest('[data-action="status"]');
          if (!btn) return;
          const next = btn.dataset.status;
          const prev = app.status;
          SR.api.put('/applications/' + app.id + '/status', { status: next }).catch(function () {});
          app.status = next;
          toast('Statut → ' + (STATUS_LABELS[next] || next), 'success');
          render();
        });

        // Interviews: schedule
        const schedBtn = document.getElementById('scheduleInterview');
        if (schedBtn) schedBtn.addEventListener('click', function () {
          SR.modal.open(
            '<p style="font-size:14px;color:var(--slate);margin:0 0 14px">Planifiez un entretien pour ' + escapeHtml((app.candidate && app.candidate.name) || '') + '.</p>' +
            '<div class="form-group"><label class="form-label" for="ivDate">Date et heure</label>' +
            '<input class="input" type="datetime-local" id="ivDate" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivLink">Lien vidéo (optionnel)</label>' +
            '<input class="input" type="url" id="ivLink" placeholder="https://meet.google.com/…"></div>' +
            '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">' +
            '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
            '<button class="btn btn-primary" id="confirmSched">Planifier</button></div>',
            { title: 'Planifier un entretien' }
          );
          document.getElementById('confirmSched').addEventListener('click', async function () {
            const dt = document.getElementById('ivDate').value;
            if (!dt) { toast('Choisissez une date', 'error'); return; }
            const link = document.getElementById('ivLink').value;
            app.interviews = app.interviews || [];
            app.interviews.push({ id: Date.now(), scheduled_at: dt, link: link, status: 'scheduled', score_technique: null, score_communication: null, score_motivation: null });
            SR.modal.close();
            render();
            toast('Entretien planifié', 'success');
          });
        });

        // Interviews: complete/cancel (mock)
        const ivBox = document.getElementById('interviewsBox');
        if (ivBox) ivBox.addEventListener('click', function (e) {
          const btn = e.target.closest('[data-iv]');
          if (!btn) return;
          const id = Number(btn.dataset.id);
          const iv = (app.interviews || []).find(function (x) { return x.id === id; });
          if (!iv) return;
          if (btn.dataset.iv === 'cancel') {
            iv.status = 'cancelled';
            render();
            toast('Entretien annulé', 'info');
            return;
          }
          SR.modal.open(
            '<p style="font-size:14px;color:var(--slate);margin:0 0 14px">Évaluez l\'entretien de 1 à 5.</p>' +
            '<div class="form-group"><label class="form-label" for="ivTech">Technique</label>' +
            '<input class="input" type="number" id="ivTech" min="1" max="5" value="3" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivCom">Communication</label>' +
            '<input class="input" type="number" id="ivCom" min="1" max="5" value="3" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivMot">Motivation</label>' +
            '<input class="input" type="number" id="ivMot" min="1" max="5" value="3" required></div>' +
            '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">' +
            '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
            '<button class="btn btn-primary" id="confirmScore">Enregistrer</button></div>',
            { title: 'Compléter l\'entretien' }
          );
          document.getElementById('confirmScore').addEventListener('click', function () {
            iv.score_technique = Number(document.getElementById('ivTech').value);
            iv.score_communication = Number(document.getElementById('ivCom').value);
            iv.score_motivation = Number(document.getElementById('ivMot').value);
            iv.status = 'completed';
            SR.modal.close();
            render();
            toast('Évaluation enregistrée', 'success');
          });
        });

        // Suggestions
        const loadSugg = document.getElementById('loadSugg');
        if (loadSugg) loadSugg.addEventListener('click', function () {
          const box = document.getElementById('suggBox');
          const suggs = SR.mock.suggestions(app.id);
          box.innerHTML = '<div style="display:grid;gap:10px">' + suggs.map(function (s) {
            const nm = (s.candidate && s.candidate.name) || '';
            return '<div style="display:flex;align-items:center;gap:12px;border:1px solid var(--line);border-radius:10px;padding:10px 12px">' +
              avatar(nm, 'sm') + '<div style="flex:1">' + scoreRing(s.matching_score, 'sm') + '</div>' +
              '<div style="flex:2"><div style="font-weight:600;color:var(--ink)">' + escapeHtml(nm) + '</div>' +
              '<div class="mono" style="font-size:12px;color:var(--slate)">' + escapeHtml((s.job_offer && s.job_offer.title) || '') + '</div></div>' +
              '<a class="btn btn-sm btn-ghost" href="/recruteur/candidatures/' + s.id + '">Voir</a></div>';
          }).join('') + '</div>';
        });
      }
    },
  };

  /* ---------------- Recruiter: shortlist (top-5 + export) ---------------- */
  SR.pages.recruiterShortlist = {
    init: function () {
      const box = document.getElementById('shortlistBox');
      if (!box) return;
      const jobId = Number(document.body.dataset.jobId);
      const job = SR.mock.job(jobId);
      const title = document.getElementById('shortlistTitle');
      const back = document.getElementById('shortlistBack');
      if (job) {
        title.textContent = 'Shortlist — ' + job.title;
        back.href = '/recruteur/offres/' + jobId + '/candidatures';
      }

      function avgOf(a) {
        const ivs = (a.interviews || []).filter(function (iv) { return iv.status === 'completed'; });
        if (!ivs.length) return null;
        const scores = [];
        ivs.forEach(function (iv) { [iv.score_technique, iv.score_communication, iv.score_motivation].forEach(function (s) { if (s != null) scores.push(s); }); });
        return scores.length ? (scores.reduce(function (s, x) { return s + x; }, 0) / scores.length).toFixed(1) : null;
      }

      function render(list) {
        if (!list.length) { box.innerHTML = '<div class="empty">Aucune candidature pour cette offre.</div>'; return; }
        box.innerHTML =
          '<table class="table"><thead><tr><th style="width:50px">Rang</th><th>Candidat</th><th>Score</th><th>Mots-clés</th><th>Entretien</th><th>Statut</th></tr></thead><tbody>' +
          list.map(function (a, i) {
            const name = (a.candidate && a.candidate.name) || '';
            const matched = a.matched_keywords || [];
            const missing = a.missing_keywords || [];
            const kw = '<div style="display:flex;gap:4px;flex-wrap:wrap">' +
              matched.map(function (k) { return '<span class="chip chip-ok" style="font-size:11px">' + escapeHtml(k) + '</span>'; }).join('') +
              missing.map(function (k) { return '<span class="chip chip-no" style="font-size:11px">' + escapeHtml(k) + '</span>'; }).join('') + '</div>';
            const avg = avgOf(a);
            return '<tr>' +
              '<td><span class="pill rank-pill">#' + (i + 1) + '</span></td>' +
              '<td><div style="display:flex;align-items:center;gap:10px">' + avatar(name, 'sm') +
              '<div><div style="font-weight:600;color:var(--ink)">' + escapeHtml(name) + '</div>' +
              '<div class="mono" style="font-size:11.5px;color:var(--slate)">' + escapeHtml((a.candidate && a.candidate.email) || '') + '</div></div></div></td>' +
              '<td>' + scoreRing(a.matching_score, 'sm') + '</td>' +
              '<td>' + kw + '</td>' +
              '<td class="mono" style="color:var(--ink)">' + (avg ? avg + ' / 5' : '—') + '</td>' +
              '<td>' + statusPill(a.status) + '</td></tr>';
          }).join('') + '</tbody></table>';
      }

      function exportCsv(list) {
        const header = ['rang', 'candidat', 'email', 'score', 'mots_cles_trouves', 'mots_cles_manquants', 'statut'];
        const rows = list.map(function (a, i) {
          return [i + 1, (a.candidate && a.candidate.name) || '', (a.candidate && a.candidate.email) || '',
            a.matching_score, (a.matched_keywords || []).join('; '), (a.missing_keywords || []).join('; '), a.status];
        });
        const csv = [header].concat(rows).map(function (r) {
          return r.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'shortlist-offre-' + jobId + '.csv';
        a.click();
        URL.revokeObjectURL(url);
        toast('Export CSV téléchargé', 'success');
      }

      function exportPdf(list) {
        const w = window.open('', '_blank', 'width=800,height=900');
        if (!w) { toast('Autorisez les pop-ups pour exporter', 'error'); return; }
        w.document.write('<html><head><title>Shortlist</title>' +
          '<style>body{font-family:Inter,Arial,sans-serif;padding:32px;color:#0f172a}h1{font-size:20px}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #e2e8f0;font-size:13px}th{background:#f8fafc}.pill{display:inline-block;padding:2px 8px;border-radius:999px;background:#eef2ff;color:#2563eb;font-size:11px;font-weight:600}</style></head><body>' +
          '<h1>Shortlist — ' + escapeHtml(job ? job.title : '') + '</h1>' +
          '<p style="color:#64748b">Généré le ' + new Date().toLocaleDateString('fr-FR') + '</p>' +
          '<table><thead><tr><th>Rang</th><th>Candidat</th><th>Score</th><th>Statut</th></tr></thead><tbody>' +
          list.map(function (a, i) {
            return '<tr><td>#' + (i + 1) + '</td><td>' + escapeHtml((a.candidate && a.candidate.name) || '') + '</td><td>' + a.matching_score + ' / 100</td><td><span class="pill">' + (STATUS_LABELS[a.status] || a.status) + '</span></td></tr>';
          }).join('') + '</tbody></table></body></html>');
        w.document.close();
        w.focus();
        w.print();
        toast('Export PDF généré', 'success');
      }

      document.getElementById('exportCsv').addEventListener('click', function () { exportCsv(currentList); });
      document.getElementById('exportPdf').addEventListener('click', function () { exportPdf(currentList); });

      let currentList = [];
      SR.load('/job-offers/' + jobId + '/shortlist', function () { return SR.mock.shortlist(jobId); }).then(function (data) {
        currentList = Array.isArray(data) ? data : [];
        render(currentList);
      }).catch(function () { render(currentList); });
    },
  };

  /* ---------------- Recruiter: saved filters ---------------- */
  SR.pages.recruiterSavedFilters = {
    init: function () {
      const box = document.getElementById('filtersBox');
      const form = document.getElementById('filterForm');
      if (!box) return;
      let filters = [];

      function render() {
        if (!filters.length) { box.innerHTML = '<div class="empty">Aucun filtre sauvegardé.</div>'; return; }
        box.innerHTML = '<div style="display:grid;gap:10px">' + filters.map(function (f) {
          const c = f.criteria || {};
          const chips = [];
          if (c.min_score) chips.push('<span class="tag tag-navy">Score ≥ ' + c.min_score + '</span>');
          if (c.tech_stack && c.tech_stack.length) chips.push(c.tech_stack.map(function (t) { return '<span class="tag">' + escapeHtml(t) + '</span>'; }).join(''));
          if (c.contract_type) chips.push('<span class="tag tag-amber">' + escapeHtml(c.contract_type) + '</span>');
          if (c.status) chips.push('<span class="tag tag-green">' + (STATUS_LABELS[c.status] || c.status) + '</span>');
          return '<div style="display:flex;align-items:center;gap:14px;border:1px solid var(--line);border-radius:10px;padding:12px 14px">' +
            '<div style="flex:1"><div style="font-weight:600;color:var(--ink)">' + escapeHtml(f.name) + '</div>' +
            '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">' + chips.join('') + '</div></div>' +
            '<button class="btn btn-sm btn-ghost" data-apply="' + f.id + '" type="button">Appliquer</button>' +
            '<button class="btn btn-sm btn-ghost-danger" data-del="' + f.id + '" type="button">Supprimer</button></div>';
        }).join('') + '</div>';
      }

      box.addEventListener('click', function (e) {
        const del = e.target.closest('[data-del]');
        if (!del) return;
        const id = Number(del.dataset.del);
        SR.api.del('/saved-filters/' + id).catch(function () {});
        filters = filters.filter(function (f) { return f.id !== id; });
        render();
        toast('Filtre supprimé', 'info');
      });
      box.addEventListener('click', function (e) {
        const ap = e.target.closest('[data-apply]');
        if (!ap) return;
        toast('Filtre appliqué — ouvert dans Candidatures', 'success');
        window.location.href = '/recruteur/candidatures';
      });

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = form.filterName.value.trim();
        if (!name) { showFormError(form, 'Nom du filtre requis.'); return; }
        const criteria = {
          status: form.filterStatus.value || undefined,
          min_score: form.filterMinScore.value ? Number(form.filterMinScore.value) : undefined,
          tech_stack: form.filterTech.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean),
        };
        SR.api.post('/saved-filters', { name: name, criteria: criteria }).catch(function () {});
        filters.push({ id: Date.now(), name: name, criteria: criteria, created_at: new Date().toISOString() });
        form.reset();
        render();
        toast('Filtre « ' + name + ' » enregistré', 'success');
      });

      SR.load('/saved-filters', SR.mock.savedFilters).then(function (data) {
        filters = Array.isArray(data) ? data : [];
        render();
      }).catch(function () { render(); });
    },
  };

  /* ---------------- Recruiter: reply templates ---------------- */
  SR.pages.recruiterTemplates = {
    init: function () {
      const box = document.getElementById('templatesBox');
      if (!box) return;
      let templates = [];

      function render() {
        box.innerHTML = templates.map(function (t) {
          return '<div style="border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:14px">' +
            '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">' +
            '<div style="display:flex;align-items:center;gap:10px"><span class="tag tag-navy">' + escapeHtml(t.label) + '</span>' +
            '<span class="mono" style="color:var(--slate);font-size:12px">' + escapeHtml(t.key) + '</span></div>' +
            '<button class="btn btn-sm btn-ghost" data-edit="' + escapeHtml(t.key) + '" type="button">Modifier</button></div>' +
            '<div style="margin-top:10px;font-size:13.5px"><span class="form-label" style="margin-bottom:2px">Objet</span>' +
            '<div style="color:var(--ink)">' + escapeHtml(t.subject) + '</div></div>' +
            '<div style="margin-top:10px"><span class="form-label" style="margin-bottom:2px">Contenu</span>' +
            '<div style="color:var(--ink);white-space:pre-line;font-size:13.5px">' + escapeHtml(t.body) + '</div></div></div>';
        }).join('');
      }

      box.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-edit]');
        if (!btn) return;
        const t = templates.find(function (x) { return x.key === btn.dataset.edit; });
        if (!t) return;
        SR.modal.open(
          '<div class="form-group"><label class="form-label" for="tplSubject">Objet</label>' +
          '<input class="input" type="text" id="tplSubject" value="' + escapeHtml(t.subject) + '"></div>' +
          '<div class="form-group"><label class="form-label" for="tplBody">Contenu</label>' +
          '<textarea class="textarea" id="tplBody" rows="8">' + escapeHtml(t.body) + '</textarea></div>' +
          '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:14px">' +
          '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
          '<button class="btn btn-primary" id="saveTpl">Enregistrer</button></div>',
          { title: 'Modifier le modèle — ' + t.label }
        );
        document.getElementById('saveTpl').addEventListener('click', function () {
          t.subject = document.getElementById('tplSubject').value;
          t.body = document.getElementById('tplBody').value;
          SR.api.put('/reply-templates/' + t.key, { subject: t.subject, body: t.body }).catch(function () {});
          SR.modal.close();
          render();
          toast('Modèle enregistré', 'success');
        });
      });

      SR.load('/reply-templates', SR.mock.templates).then(function (data) {
        templates = Array.isArray(data) ? data : [];
        render();
      }).catch(function () { render(); });
    },
  };

  /* ---------------- Profile ---------------- */
  SR.pages.profile = {
    init: function () {
      const form = document.getElementById('profileForm');
      if (!form) return;
      const user = auth.user();
      if (user) {
        if (document.getElementById('pfName')) document.getElementById('pfName').value = user.name || '';
        if (document.getElementById('pfEmail')) document.getElementById('pfEmail').value = user.email || '';
        if (document.getElementById('profileName')) document.getElementById('profileName').textContent = user.name || 'User';
        if (document.getElementById('profileEmail')) document.getElementById('profileEmail').textContent = user.email || '';
        if (document.getElementById('profileAvatar')) document.getElementById('profileAvatar').textContent = initials(user.name);
        if (document.getElementById('profileRole')) document.getElementById('profileRole').textContent = user.role === 'recruiter' ? 'Recruiter' : 'Candidate';
        if (document.getElementById('kvRole')) document.getElementById('kvRole').textContent = user.role === 'recruiter' ? 'Recruiter' : 'Candidate';
        if (document.getElementById('kvJoined')) document.getElementById('kvJoined').textContent = fmtDate(user.created_at);
      }

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const alertWrap = form.querySelector('.form-alert');
        if (alertWrap) alertWrap.style.display = 'none';
        const btn = form.querySelector('[type="submit"]');
        const pwd = document.getElementById('pfPassword').value;
        const pwd2 = document.getElementById('pfPasswordConfirm').value;
        if (pwd && pwd.length < 8) { showFormError(form, 'Password must be at least 8 characters.'); return; }
        if (pwd !== pwd2) { showFormError(form, 'Passwords do not match.'); return; }

        const payload = { name: document.getElementById('pfName').value.trim(), email: document.getElementById('pfEmail').value.trim() };
        if (pwd) payload.password = pwd;

        setLoading(btn, true);
        try {
          await SR.api.put('/user/profile', payload);
        } catch (err) {
          if (!USE_MOCKS) { setLoading(btn, false); showFormError(form, err.message || 'Unable to update profile'); return; }
        }
        const stored = auth.user() || {};
        auth.set({ token: auth.token(), user: Object.assign({}, stored, { name: payload.name, email: payload.email }) });
        bindSidebarUser();
        setLoading(btn, false);
        toast('Profile updated', 'success');
      });
    },
  };

  /* ---------------- Candidate: my applications ---------------- */
  SR.pages.candidateApplications = {
    init: function () {
      const box = document.getElementById('candAppsBox');
      if (!box) return;

      function myApps() {
        const user = auth.user();
        const all = SR.mock.applications();
        const mine = all.filter(function (a) { return user && a.candidate && a.candidate.id === user.id; });
        return mine.length ? mine : all.slice(0, 3);
      }

      function detailModal(a) {
        const kw = (a.matched_keywords || []).map(function (k) { return '<span class="chip chip-ok">' + escapeHtml(k) + '</span>'; }).join('') +
          (a.missing_keywords || []).map(function (k) { return '<span class="chip chip-no">' + escapeHtml(k) + '</span>'; }).join('');
        const iv = (a.interviews && a.interviews.length) ? a.interviews.map(function (i) {
          return '<div class="kv"><dt>' + (i.status === 'completed' ? 'Entretien terminé' : 'Entretien planifié') + '</dt><dd>' +
            fmtDateTime(i.scheduled_at) + (i.link ? ' · <a href="' + escapeHtml(i.link) + '" target="_blank" rel="noopener">' + escapeHtml(i.link) + '</a>' : '') + '</dd></div>';
        }).join('') : '<div class="kv"><dt>Entretien</dt><dd>Aucun entretien programmé</dd></div>';
        SR.modal.open(
          '<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">' + scoreRing(a.matching_score) +
          '<div><div style="font-weight:700;font-size:17px;color:var(--ink)">' + escapeHtml(a.job_offer ? a.job_offer.title : '') + '</div>' +
          statusPill(a.status) + '</div></div>' +
          '<div class="kv"><dt>Postulé le</dt><dd>' + fmtDate(a.created_at) + '</dd></div>' +
          '<div class="kv" style="margin-top:10px"><dt>Mots-clés trouvés / manquants</dt><dd>' + (kw || '—') + '</dd></div>' +
          '<div class="kv" style="margin-top:10px"><dt>Votre lettre</dt><dd style="font-weight:400;white-space:pre-line">' + escapeHtml(a.cover_letter) + '</dd></div>' +
          '<div style="margin-top:10px">' + iv + '</div>',
          { title: 'Ma candidature' }
        );
      }

      function render(list) {
        if (!list.length) { box.innerHTML = '<div class="empty">Vous n\'avez pas encore postulé.<br><a class="btn btn-primary" style="margin-top:12px" href="/offres">Explorer les offres</a></div>'; return; }
        box.innerHTML =
          '<table class="table"><thead><tr><th>Offre</th><th>Score</th><th>Mots-clés</th><th>Statut</th><th>Postulé le</th><th></th></tr></thead><tbody>' +
          list.map(function (a) {
            const kw = (a.matched_keywords || []).map(function (k) { return '<span class="chip chip-ok" style="font-size:11px">' + escapeHtml(k) + '</span>'; }).join('') +
              (a.missing_keywords || []).map(function (k) { return '<span class="chip chip-no" style="font-size:11px">' + escapeHtml(k) + '</span>'; }).join('');
            return '<tr>' +
              '<td><div style="font-weight:600;color:var(--ink)">' + escapeHtml(a.job_offer ? a.job_offer.title : '') + '</div>' +
              '<div class="mono" style="font-size:11.5px;color:var(--slate)">' + escapeHtml((a.job_offer && a.job_offer.contract_type) || '') + '</div></td>' +
              '<td>' + scoreRing(a.matching_score, 'sm') + '</td>' +
              '<td>' + (kw || '<span class="mono" style="color:var(--slate)">—</span>') + '</td>' +
              '<td>' + statusPill(a.status) + '</td>' +
              '<td class="mono" style="color:var(--slate)">' + fmtDate(a.created_at) + '</td>' +
              '<td><button class="btn btn-sm btn-ghost" data-detail="' + a.id + '" type="button">Détail</button></td></tr>';
          }).join('') + '</tbody></table>';
      }

      box.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-detail]');
        if (!btn) return;
        const a = SR.mock.applications().find(function (x) { return x.id === Number(btn.dataset.detail); });
        if (a) detailModal(a);
      });

      SR.load('/applications', myApps).then(function (data) {
        render(Array.isArray(data) ? data : myApps());
      }).catch(function () { render(myApps()); });
    },
  };

  /* ---------------- Candidate: apply ---------------- */
  SR.pages.candidateApply = {
    init: function () {
      const form = document.getElementById('applyForm');
      const summary = document.getElementById('applyJobSummary');
      const dz = document.getElementById('applyDropzone');
      const fileInput = document.getElementById('applyCv');
      const fileLabel = dz.querySelector('.dz-file');
      if (!form) return;

      const jobId = Number(document.body.dataset.jobId);
      let selectedFile = null;

      function renderJob() {
        const job = SR.mock.job(jobId);
        if (!job) { summary.innerHTML = '<div class="empty">Offre introuvable.</div>'; return; }
        document.getElementById('applyJobTitle').textContent = job.title;
        document.getElementById('applyBackLink').href = '/offres/' + jobId;
        summary.innerHTML =
          '<div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">' +
          '<div><h3 style="margin:0 0 6px">' + escapeHtml(job.title) + '</h3>' +
          '<div class="mono" style="color:var(--slate);font-size:13px">' + escapeHtml(job.contract_type) + ' · ' + fmtSalary(job.salary) +
          ' · Postulez avant le ' + fmtDate(job.deadline) + '</div></div>' +
          '<div style="display:flex;gap:6px;flex-wrap:wrap;align-content:flex-start">' +
          (job.tech_stack_array || []).map(function (t) { return '<span class="chip">' + escapeHtml(t) + '</span>'; }).join('') + '</div></div>' +
          '<p style="margin:14px 0 0;color:var(--ink);line-height:1.6">' + escapeHtml((job.description || '').split('\n\n')[0]) + '</p>';
      }
      renderJob();

      function setFile(file) {
        selectedFile = file;
        if (!file) { dz.classList.remove('has-file'); fileLabel.style.display = 'none'; return; }
        dz.classList.add('has-file');
        fileLabel.style.display = '';
        fileLabel.textContent = (file.size / 1024 / 1024).toFixed(2) + ' Mo — ' + file.name;
      }

      dz.addEventListener('click', function () { fileInput.click(); });
      dz.addEventListener('dragover', function (e) { e.preventDefault(); dz.classList.add('dragover'); });
      dz.addEventListener('dragleave', function () { dz.classList.remove('dragover'); });
      dz.addEventListener('drop', function (e) {
        e.preventDefault(); dz.classList.remove('dragover');
        if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
      });
      fileInput.addEventListener('change', function () { setFile(fileInput.files[0] || null); });

      const cover = document.getElementById('applyCover');
      cover.addEventListener('input', function () {
        document.getElementById('coverHint').textContent = cover.value.length + ' / 20 caractères minimum';
      });

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const coverText = cover.value.trim();
        if (!selectedFile) { showFormError(form, 'Veuillez joindre votre CV (PDF).'); return; }
        if (selectedFile.type !== 'application/pdf' && !/\.pdf$/i.test(selectedFile.name)) { showFormError(form, 'Le CV doit être un fichier PDF.'); return; }
        if (selectedFile.size > 5 * 1024 * 1024) { showFormError(form, 'Le CV ne doit pas dépasser 5 Mo.'); return; }
        if (coverText.length < 20) { showFormError(form, 'La lettre de motivation doit contenir au moins 20 caractères.'); return; }

        const btn = form.querySelector('[type="submit"]');
        setLoading(btn, true);
        try {
          if (USE_MOCKS) {
            await new Promise(function (r) { setTimeout(r, 700); });
          } else {
            const fd = new FormData();
            fd.append('cv', selectedFile);
            fd.append('cover_letter', coverText);
            await SR.api.form('/job-offers/' + jobId + '/apply', fd);
          }
        } catch (err) {
          setLoading(btn, false);
          const msg = (err.body && err.body.errors) ? Object.values(err.body.errors).flat().join(' / ') : (err.message || 'Échec de l\'envoi');
          showFormError(form, msg);
          return;
        }
        SR.modal.open(
          '<div style="text-align:center;padding:6px 0 2px">' +
          '<div class="score-ring-wrap" style="margin:0 auto 14px">' + scoreRing(82) + '</div>' +
          '<h3 style="margin:0 0 6px">Candidature envoyée !</h3>' +
          '<p style="color:var(--slate);font-size:14px;margin:0 0 18px">Le score de compatibilité est calculé en arrière-plan.<br>Suivez son évolution dans « Mes candidatures ».</p>' +
          '<button class="btn btn-primary" onclick="window.location.href=\'/mes-candidatures\'">Voir mes candidatures</button></div>',
          { title: 'Candidature envoyée' }
        );
        form.reset();
        setFile(null);
      });

      document.getElementById('applyCancel').addEventListener('click', function () {
        window.location.href = '/offres/' + jobId;
      });
    },
  };

  /* ---------------- Recruiter: interviews ---------------- */
  SR.pages.recruiterInterviews = {
    init: function () {
      const box = document.getElementById('interviewsBox');
      const filtersBar = document.getElementById('interviewFilters');
      if (!box) return;
      let interviews = [];
      let filter = 'all';

      function avg(iv) {
        const scores = [iv.score_technique, iv.score_communication, iv.score_motivation].filter(function (s) { return s != null; });
        return scores.length ? (scores.reduce(function (s, x) { return s + x; }, 0) / scores.length).toFixed(1) : null;
      }

      function renderFilters() {
        const chips = [
          ['all', 'Tous'], ['scheduled', 'Planifiés'], ['completed', 'Terminés'], ['cancelled', 'Annulés'],
        ];
        filtersBar.innerHTML = chips.map(function (c) {
          return '<button class="filter-chip' + (filter === c[0] ? ' active' : '') + '" data-f="' + c[0] + '" type="button">' + c[1] + '</button>';
        }).join('');
      }

      function render() {
        const list = interviews.filter(function (iv) { return filter === 'all' || iv.status === filter; });
        if (!list.length) { box.innerHTML = '<div class="empty">Aucun entretien.</div>'; return; }
        box.innerHTML =
          '<table class="table"><thead><tr><th>Candidat</th><th>Offre</th><th>Date</th><th>Statut</th><th>Moyenne</th><th>Lien</th><th></th></tr></thead><tbody>' +
          list.map(function (iv) {
            const a = avg(iv);
            const actions = iv.status === 'scheduled'
              ? '<div style="display:flex;gap:6px">' +
                '<button class="btn btn-sm" data-complete="' + iv.id + '" type="button">Compléter</button>' +
                '<button class="btn btn-sm btn-ghost-danger" data-cancel="' + iv.id + '" type="button">Annuler</button></div>'
              : '<a class="btn btn-sm btn-ghost" href="/recruteur/candidatures/' + iv.application_id + '">Ouvrir</a>';
            return '<tr>' +
              '<td><div style="display:flex;align-items:center;gap:10px">' + avatar(iv.candidate_name, 'sm') +
              '<span style="font-weight:600;color:var(--ink)">' + escapeHtml(iv.candidate_name) + '</span></div></td>' +
              '<td style="color:var(--ink)">' + escapeHtml(iv.job_title) + '</td>' +
              '<td class="mono" style="color:var(--ink)">' + fmtDateTime(iv.scheduled_at) + '</td>' +
              '<td>' + statusPill(iv.status) + '</td>' +
              '<td class="mono" style="color:var(--ink)">' + (a ? a + ' / 5' : '—') + '</td>' +
              '<td>' + (iv.link ? '<a class="btn btn-sm btn-ghost" href="' + escapeHtml(iv.link) + '" target="_blank" rel="noopener">Visio</a>' : '<span class="mono" style="color:var(--slate)">—</span>') + '</td>' +
              '<td>' + actions + '</td></tr>';
          }).join('') + '</tbody></table>';
      }

      filtersBar.addEventListener('click', function (e) {
        const chip = e.target.closest('[data-f]');
        if (!chip) return;
        filter = chip.dataset.f;
        renderFilters();
        render();
      });

      box.addEventListener('click', function (e) {
        const id = (e.target.closest('[data-complete]') || e.target.closest('[data-cancel]') || { dataset: {} }).dataset;
        const completeBtn = e.target.closest('[data-complete]');
        const cancelBtn = e.target.closest('[data-cancel]');
        if (completeBtn) {
          const iv = interviews.find(function (x) { return x.id === Number(completeBtn.dataset.complete); });
          if (!iv) return;
          SR.modal.open(
            '<div class="form-group"><label class="form-label" for="sT">Technique (1-5)</label>' +
            '<input class="input" type="number" id="sT" min="1" max="5" step="1" value="3"></div>' +
            '<div class="form-group"><label class="form-label" for="sC">Communication (1-5)</label>' +
            '<input class="input" type="number" id="sC" min="1" max="5" step="1" value="3"></div>' +
            '<div class="form-group"><label class="form-label" for="sM">Motivation (1-5)</label>' +
            '<input class="input" type="number" id="sM" min="1" max="5" step="1" value="3"></div>' +
            '<div class="modal-foot"><button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
            '<button class="btn btn-primary" id="saveScores">Valider</button></div>',
            { title: 'Évaluer — ' + escapeHtml(iv.candidate_name) }
          );
          document.getElementById('saveScores').addEventListener('click', async function () {
            const payload = {
              score_technique: Number(document.getElementById('sT').value),
              score_communication: Number(document.getElementById('sC').value),
              score_motivation: Number(document.getElementById('sM').value),
            };
            try { await SR.api.put('/interviews/' + iv.id + '/complete', payload); } catch (err) { if (!USE_MOCKS) { toast(err.message, 'error'); return; } }
            iv.status = 'completed'; iv.score_technique = payload.score_technique; iv.score_communication = payload.score_communication; iv.score_motivation = payload.score_motivation;
            SR.modal.close(); render(); toast('Entretien évalué (moyenne ' + avg(iv) + '/5)', 'success');
          });
        }
        if (cancelBtn) {
          const iv = interviews.find(function (x) { return x.id === Number(cancelBtn.dataset.cancel); });
          if (!iv) return;
          SR.api.put('/interviews/' + iv.id + '/cancel').catch(function () { if (!USE_MOCKS) throw new Error(); });
          iv.status = 'cancelled';
          render();
          toast('Entretien annulé', 'info');
        }
      });

      SR.load('/interviews', SR.mock.interviews).then(function (data) {
        interviews = Array.isArray(data) ? data : [];
        renderFilters();
        render();
      }).catch(function () { renderFilters(); render(); });
    },
  };

  /* ---------------- Recruiter: AI agent chat ---------------- */
  SR.pages.recruiterAgentChat = {
    init: function () {
      const listBox = document.getElementById('convList');
      const thread = document.getElementById('chatThread');
      const title = document.getElementById('convTitle');
      const form = document.getElementById('chatForm');
      const input = document.getElementById('chatText');
      const sendBtn = document.getElementById('chatSend');
      if (!listBox || !form) return;

      let conversations = [];
      let activeId = null;
      let messageStore = {};

      function msgFor(id) {
        if (!messageStore[id]) messageStore[id] = (SR.mock.messages(id) || []).slice();
        return messageStore[id];
      }

      function bubble(msg) {
        const iconHtml = msg.role === 'assistant' ? '<span class="avatar avatar-sm" style="background:var(--navy);color:#fff">IA</span>' : '';
        return '<div class="chat-msg ' + escapeHtml(msg.role) + '" style="' + (msg.role === 'assistant' ? 'display:flex;gap:10px;align-items:flex-start' : '') + '">' +
          iconHtml + '<div>' + escapeHtml(msg.content) + '</div></div>';
      }

      function renderList() {
        if (!conversations.length) { listBox.innerHTML = '<div class="empty">Aucune conversation.</div>'; return; }
        listBox.innerHTML = conversations.map(function (c) {
          return '<div class="conv-item' + (c.id === activeId ? ' active' : '') + '" data-conv="' + c.id + '">' +
            '<div class="conv-title">' + escapeHtml(c.title) + '</div>' +
            '<div class="conv-sub">' + fmtDate(c.created_at) + '</div></div>';
        }).join('');
      }

      function renderMessages() {
        thread.innerHTML = msgFor(activeId).map(bubble).join('') ||
          '<div class="empty">Écrivez un message pour démarrer.</div>';
        thread.scrollTop = thread.scrollHeight;
      }

      function select(id) {
        activeId = Number(id);
        const c = conversations.find(function (x) { return x.id === activeId; });
        title.textContent = c ? c.title : 'Aucune conversation';
        input.disabled = false;
        sendBtn.disabled = false;
        renderList();
        renderMessages();
      }

      listBox.addEventListener('click', function (e) {
        const item = e.target.closest('[data-conv]');
        if (item) select(item.dataset.conv);
      });

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || activeId == null) return;
        msgFor(activeId).push({ id: Date.now(), role: 'user', content: text, created_at: new Date().toISOString() });
        renderMessages();
        input.value = '';
        try { await SR.api.post('/agent-conversations/' + activeId + '/messages', { content: text }); }
        catch (err) { if (!USE_MOCKS) { toast(err.message, 'error'); return; } }
        setTimeout(function () {
          msgFor(activeId).push({ id: Date.now() + 1, role: 'assistant', content: 'J\'ai bien noté votre question. Pouvez-vous préciser le contexte (offre, candidat, entretien) afin que je vous aide au mieux ?', created_at: new Date().toISOString() });
          renderMessages();
        }, 700);
      });

      document.getElementById('newConvBtn').addEventListener('click', function () {
        SR.modal.open(
          '<div class="form-group"><label class="form-label" for="ncTech">Technologies du poste</label>' +
          '<input class="input" type="text" id="ncTech" placeholder="PHP, Laravel, MySQL"></div>' +
          '<div class="form-group"><label class="form-label" for="ncJob">Intitulé (optionnel)</label>' +
          '<input class="input" type="text" id="ncJob" placeholder="Développeur Laravel"></div>' +
          '<div class="modal-foot"><button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
          '<button class="btn btn-primary" id="genQ">Générer des questions</button></div>',
          { title: 'Nouvelle conversation — questions d\'entretien' }
        );
        document.getElementById('genQ').addEventListener('click', function () {
          const tech = document.getElementById('ncTech').value.trim();
          if (!tech) { toast('Indiquez les technologies', 'error'); return; }
          const job = document.getElementById('ncJob').value.trim() || 'Recrutement';
          const id = Date.now();
          conversations.push({ id: id, context_type: 'interview_questions', context_id: null, status: 'active', title: 'Questions — ' + job, created_at: new Date().toISOString() });
          messageStore[id] = [
            { id: 1, role: 'user', content: 'Génère 3-5 questions d’entretien pour le poste : ' + tech, created_at: new Date().toISOString() },
            { id: 2, role: 'assistant', content: 'Voici des questions adaptées à la stack demandée (' + tech + ') :\n\n1. Décrivez votre expérience concrète sur ces technologies.\n2. Quel projet complexe avez-vous mené à bien et quel a été votre rôle ?\n3. Comment résolvez-vous un incident de performance en production ?\n4. Comment assurez-vous la qualité et la revue de code ?\n5. Comment vous tenez-vous à jour sur cet écosystème ?', created_at: new Date().toISOString() },
          ];
          SR.modal.close();
          select(id);
          toast('Questions générées via l\'agent IA', 'success');
        });
      });

      SR.load('/agent-conversations', SR.mock.conversations).then(function (data) {
        conversations = Array.isArray(data) ? data : [];
        renderList();
        if (conversations.length) select(conversations[0].id);
      }).catch(function () { renderList(); });
    },
  };

  document.addEventListener('DOMContentLoaded', function () {
    bindGlobalSearch();
    bindSidebarUser();
    dispatch();
  });
})();
