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
    return Number(s).toLocaleString('fr-MA', { maximumFractionDigits: 0 }) + ' MAD';
  }

  const STATUS_LABELS = {
    received: 'Received', interview: 'Interview', accepted: 'Accepted', refused: 'Refused',
    scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', active: 'Active', archived: 'Archived',
  };

  const STATUS_PILL_CLASS = {
    received: 'pill-slate', interview: '', accepted: 'pill-success', refused: 'pill-danger',
    scheduled: '', completed: 'pill-success', cancelled: 'pill-danger',
    active: 'pill-success', archived: 'pill-slate',
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
    return '<span class="avatar' + (size === 'sm' ? '" style="width:40px;height:40px;font-size:14px"' : '"') + '>' +
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
    eyeOff: '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="m1 1 22 22"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>',
    note: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
    briefcase: '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>',
    video: '<path d="m22 8-6 4 6 4V8z"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
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
      return (user && user.role === 'recruiter') ? '/dashboard' : '/my-applications';
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
        window.location.href = '/login';
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
    /* Touch/pen pointer-drag state */
    _touch: null,
    /* Shared ghost element (one per board, lazily created) */
    _ghost: null,

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

      /* ---- Shared helpers ---- */

      /* Find the card in `col` that the pointer is above — use it as the
         insertion reference so the dropped card lands at the correct position.
         Returns null when the pointer is below every card (append). */
      function findInsertRef(col, clientY) {
        var cards = Array.prototype.slice.call(col.querySelectorAll(cardsSel + ':not(.dragging)'));
        for (var i = 0; i < cards.length; i++) {
          var r = cards[i].getBoundingClientRect();
          if (clientY < r.top + r.height / 2) return cards[i];
        }
        return null;
      }

      /* Lazily create the single ghost element for the board. */
      function ensureGhost() {
        if (!self._ghost || !self._ghost.parentNode) {
          var g = document.createElement('div');
          g.className = 'drop-ghost';
          self._ghost = g;
        }
        return self._ghost;
      }

      /* Position the ghost inside `col` at the correct slot. */
      function positionGhost(col, clientY) {
        var ghost = ensureGhost();
        var ref = findInsertRef(col, clientY);
        /* Only move DOM when needed to avoid layout thrash */
        if (ref) {
          if (ghost.parentNode !== col || ghost.nextElementSibling !== ref) {
            col.insertBefore(ghost, ref);
          }
        } else {
          if (ghost.parentNode !== col || ghost.nextSibling !== null) {
            col.appendChild(ghost);
          }
        }
      }

      /* Remove `.drag-over` from all columns except `keep`. */
      function setDragOver(col) {
        board.querySelectorAll(colSel).forEach(function (c) {
          if (c === col) { c.classList.add('drag-over'); }
          else { c.classList.remove('drag-over'); }
        });
      }

      function clearOver() {
        board.querySelectorAll(colSel).forEach(function (c) {
          c.classList.remove('drag-over');
        });
        if (self._ghost && self._ghost.parentNode) {
          self._ghost.parentNode.removeChild(self._ghost);
        }
      }

      function refreshCounters() {
        board.querySelectorAll(colSel).forEach(function (col) {
          var count = col.querySelector('.kanban-count');
          if (count) count.textContent = String(col.querySelectorAll(cardsSel).length);
        });
      }

      /* Find the column under (clientX, clientY) using elementFromPoint. */
      function colFromPoint(x, y) {
        /* Callers set the dragged card's pointer-events to 'none' first, so
           elementFromPoint returns the element beneath it, not the card. */
        var el = document.elementFromPoint(x, y);
        return el ? el.closest(colSel) : null;
      }

      /* ---- Shared drop logic (used by HTML5 drop AND pointerup) ---- */
      function commitDrop(card, col, clientY) {
        var toStatus = col.dataset.status || col.dataset.col;
        var fromStatus = card.dataset.status;
        var wasSame = (fromStatus === toStatus);

        /* Same-column reorder: just do the positional insert + counters,
           skip canDrop / onOptimistic / onDrop — nothing to persist. */
        if (wasSame) {
          var ref = findInsertRef(col, clientY);
          col.insertBefore(card, ref);
          card.classList.remove('dragging');
          clearOver();
          refreshCounters();
          return;
        }

        /* Cross-column: run the guard */
        var guard = opts.canDrop ? opts.canDrop(fromStatus, toStatus) : true;
        var reject = function (msg) {
          if (self.fromCol) self.fromCol.insertBefore(card, self.beforeEl);
          card.classList.remove('dragging');
          clearOver();
          refreshCounters();
          if (msg) toast(msg, 'error');
        };

        if (!guard) {
          reject('Cannot move from ' + fromStatus + ' to ' + toStatus);
          return;
        }

        /* Positional insert into the new column */
        var ref2 = findInsertRef(col, clientY);
        col.insertBefore(card, ref2);
        card.dataset.status = toStatus;
        card.classList.remove('dragging');
        card.classList.add('drop-in');
        setTimeout(function () { card.classList.remove('drop-in'); }, 400);
        clearOver();
        refreshCounters();

        if (opts.onOptimistic) opts.onOptimistic(card, toStatus);

        if (opts.onDrop) {
          opts.onDrop(card, fromStatus, toStatus, function (ok) {
            if (ok === false) reject();
          });
        }
      }

      /* ---- HTML5 Drag & Drop (mouse / keyboard) ---- */

      board.addEventListener('dragstart', function (e) {
        var card = e.target.closest(cardsSel);
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
        var col = e.target.closest(colSel);
        if (!col) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        setDragOver(col);
        positionGhost(col, e.clientY);
      });

      board.addEventListener('dragleave', function (e) {
        if (!e.relatedTarget || !board.contains(e.relatedTarget)) clearOver();
      });

      board.addEventListener('drop', function (e) {
        e.preventDefault();
        var col = e.target.closest(colSel);
        var card = board.querySelector(cardsSel + '.dragging');
        if (!col || !card) { clearOver(); return; }
        commitDrop(card, col, e.clientY);
      });

      board.addEventListener('dragend', function (e) {
        var card = e.target.closest(cardsSel);
        if (card) card.classList.remove('dragging');
        clearOver();
        refreshCounters();
        self.dragId = null;
        self.fromStatus = null;
        self.fromCol = null;
        self.beforeEl = null;
      });

      /* ---- Touch / Pen Pointer Events (HTML5 DnD doesn't work on touch) ---- */

      board.addEventListener('pointerdown', function (e) {
        if (e.pointerType !== 'touch' && e.pointerType !== 'pen') return;
        var card = e.target.closest(cardsSel);
        if (!card) return;
        self._touch = {
          id: e.pointerId,
          card: card,
          startX: e.clientX,
          startY: e.clientY,
          currentX: e.clientX,
          currentY: e.clientY,
          active: false,
        };
        try { card.setPointerCapture(e.pointerId); } catch (err) {}
      }, { passive: true });

      board.addEventListener('pointermove', function (e) {
        var t = self._touch;
        if (!t || e.pointerId !== t.id) return;
        t.currentX = e.clientX;
        t.currentY = e.clientY;

        /* Activate only after 8px slop so taps still open modals */
        if (!t.active) {
          var dx = e.clientX - t.startX;
          var dy = e.clientY - t.startY;
          if (dx * dx + dy * dy <= 64) return; // 8px^2
          t.active = true;
          /* Mirror dragstart state */
          self.dragId = t.card.dataset.id;
          self.fromStatus = t.card.dataset.status;
          self.fromCol = t.card.closest(colSel);
          self.beforeEl = t.card.nextElementSibling;
          t.card.classList.add('dragging');
        }

        /* Find column under pointer and run drag-over + ghost logic */
        t.card.style.pointerEvents = 'none';
        var col = colFromPoint(e.clientX, e.clientY);
        t.card.style.pointerEvents = '';
        if (col) {
          setDragOver(col);
          positionGhost(col, e.clientY);
        } else {
          clearOver();
        }
      }, { passive: true });

      function endPointer(e, isCancel) {
        var t = self._touch;
        if (!t || e.pointerId !== t.id) return;

        if (t.active) {
          t.card.style.pointerEvents = 'none';
          var col = colFromPoint(t.currentX, t.currentY);
          t.card.style.pointerEvents = '';

          if (isCancel || !col) {
            /* Revert to original position */
            if (self.fromCol) self.fromCol.insertBefore(t.card, self.beforeEl);
            t.card.classList.remove('dragging');
            clearOver();
            refreshCounters();
          } else {
            commitDrop(t.card, col, t.currentY);
          }
        }

        /* Always clean up */
        t.card = null;
        self._touch = null;
        self.dragId = null;
        self.fromStatus = null;
        self.fromCol = null;
        self.beforeEl = null;
      }

      board.addEventListener('pointerup', function (e) { endPointer(e, false); }, { passive: true });
      board.addEventListener('pointercancel', function (e) { endPointer(e, true); }, { passive: true });
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
    { id: 7, title: 'Développeur Cobol Legacy', description: 'Offre historiquement publiée, désormais archivée.\n\nMissions :\n- Maintenir le système existant\n- Documenter les flux\n- Transférer les compétences', tech_stack: 'COBOL, JCL', tech_stack_array: ['COBOL', 'JCL'], contract_type: 'CDD', salary: 12000, deadline: '2026-06-30', status: 'archived', applications_count: 3, created_at: '2026-06-10T09:00:00+00:00' },
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
    applications_trend: (function () {
      const out = []; const now = new Date();
      for (let i = 29; i >= 0; i--) {
        const d = new Date(now); d.setDate(now.getDate() - i);
        out.push({ date: d.toISOString().slice(0, 10), count: Math.floor(Math.random() * 6) });
      }
      return out;
    })(),
    upcoming_interviews: [
      { id: 11, scheduled_at: '2026-08-10T14:00:00+00:00', link: 'https://meet.google.com/abc-defg-hij', candidate_name: 'Sara El Amrani', job_title: 'Développeur Laravel Senior' },
      { id: 12, scheduled_at: '2026-08-12T10:00:00+00:00', link: '', candidate_name: 'Nadia Bouhlel', job_title: 'Développeur Full-Stack React' },
      { id: 13, scheduled_at: '2026-08-13T09:30:00+00:00', link: 'https://meet.google.com/xyz', candidate_name: 'Omar Chraibi', job_title: 'Développeur Laravel Senior' },
    ],
    pipeline_health: {
      stale_by_offer: [{ job_offer_id: 1, title: 'Développeur Laravel Senior', count: 3 }],
      deadline_soon: [{ job_offer_id: 4, title: 'Ingénieur DevOps & Cloud', deadline: '2026-08-10' }],
      avg_first_response_days: 3.4,
    },
    top_candidates: [
      { id: 5, matching_score: 96, status: 'accepted', tags: [], candidate: { id: 5, name: 'Omar Chraibi', badges: ['cv_complet', 'high_match', 'interview_passed'] }, job_offer: { id: 1, title: 'Développeur Laravel Senior' } },
      { id: 1, matching_score: 92, status: 'interview', tags: ['prioritaire'], candidate: { id: 1, name: 'Sara El Amrani', badges: ['cv_complet', 'high_match'] }, job_offer: { id: 1, title: 'Développeur Laravel Senior' } },
      { id: 6, matching_score: 88, status: 'received', tags: ['reserve'], candidate: { id: 6, name: 'Nadia Bouhlel', badges: ['cv_complet'] }, job_offer: { id: 2, title: 'Développeur Full-Stack React' } },
      { id: 3, matching_score: 84, status: 'interview', tags: [], candidate: { id: 3, name: 'Amine Tazi', badges: ['cv_complet'] }, job_offer: { id: 1, title: 'Développeur Laravel Senior' } },
      { id: 2, matching_score: 78, status: 'received', tags: ['a_relancer'], candidate: { id: 2, name: 'Youssef Benali', badges: ['cv_complet'] }, job_offer: { id: 1, title: 'Développeur Laravel Senior' } },
    ],
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

  // Applications submitted by the logged-in candidate in mock mode.
  // Persisted in localStorage so they survive navigation and appear in "Mes candidatures".
  const MOCK_USER_APPS_KEY = 'sr_mock_user_apps';
  let MOCK_USER_APPS = loadMockUserApps();
  function loadMockUserApps() {
    try { return JSON.parse(localStorage.getItem(MOCK_USER_APPS_KEY) || '[]'); }
    catch (e) { return []; }
  }
  function saveMockUserApps() {
    try { localStorage.setItem(MOCK_USER_APPS_KEY, JSON.stringify(MOCK_USER_APPS)); } catch (e) { /* storage full/unavailable */ }
  }

  // Shared cross-role overrides so recruiter pipeline actions (status moves,
  // interview scheduling) are visible to the candidate view even in demo/mock
  // mode. Keyed by application id; only read when data came from the mock store.
  const MOCK_STATUS_KEY = 'sr_mock_statuses';       // { appId: status }
  const MOCK_INTERVIEWS_KEY = 'sr_mock_interviews'; // { appId: [interview…] }
  function mockOverrides() {
    let statuses = {}, interviews = {};
    try { statuses = JSON.parse(localStorage.getItem(MOCK_STATUS_KEY) || '{}'); } catch (e) { statuses = {}; }
    try { interviews = JSON.parse(localStorage.getItem(MOCK_INTERVIEWS_KEY) || '{}'); } catch (e) { interviews = {}; }
    return { statuses: statuses, interviews: interviews };
  }
  function saveMockStatus(appId, status) {
    const o = mockOverrides();
    o.statuses[appId] = status;
    try { localStorage.setItem(MOCK_STATUS_KEY, JSON.stringify(o.statuses)); } catch (e) { /* ignore */ }
  }
  function saveMockInterview(appId, iv) {
    const o = mockOverrides();
    const list = o.interviews[appId] || [];
    const idx = list.findIndex(function (x) { return x.id === iv.id; });
    if (idx === -1) list.push(iv); else list[idx] = iv;
    o.interviews[appId] = list;
    try { localStorage.setItem(MOCK_INTERVIEWS_KEY, JSON.stringify(o.interviews)); } catch (e) { /* ignore */ }
  }
  function applyMockOverrides(a) {
    if (!a) return a;
    const o = mockOverrides();
    if (o.statuses[a.id] !== undefined) a.status = o.statuses[a.id];
    if (o.interviews[a.id] !== undefined) a.interviews = o.interviews[a.id];
    return a;
  }

  // Applications the recruiter deleted in mock/demo mode. Persisted in
  // localStorage so the deletion survives navigation (same key the recruiter
  // and candidate views share — a demo-deleted app disappears everywhere).
  const MOCK_DELETED_APPS_KEY = 'sr_mock_deleted_apps';
  function mockDeletedAppIds() {
    try { return JSON.parse(localStorage.getItem(MOCK_DELETED_APPS_KEY) || '[]'); }
    catch (e) { return []; }
  }
  function saveMockDeletedApp(appId) {
    const list = mockDeletedAppIds();
    if (list.indexOf(appId) === -1) list.push(appId);
    try { localStorage.setItem(MOCK_DELETED_APPS_KEY, JSON.stringify(list)); } catch (e) { /* ignore */ }
  }
  function isMockAppDeleted(appId) {
    return mockDeletedAppIds().indexOf(Number(appId)) !== -1;
  }

  function persistMockApplication(jobId, coverText) {
    const user = auth.user();
    const job = SR.mock.job(jobId);
    if (MOCK_USER_APPS.some(function (a) { return a.candidate.id === user.id && a.job_offer_id === Number(jobId); })) return;
    MOCK_USER_APPS.unshift({
      id: Date.now(), matching_score: null, matched_keywords: [], missing_keywords: [],
      tags: [], status: 'received', cv_path: '', cover_letter: coverText, notes: '', comments: '',
      candidate: { id: user.id, name: user.name, email: user.email, role: 'candidate', avatar: null, badges: [] },
      job_offer: job || { id: Number(jobId), title: 'Offre #' + jobId, contract_type: '', tech_stack_array: [] },
      job_offer_id: Number(jobId),
      interviews: [],
      created_at: new Date().toISOString(),
    });
    saveMockUserApps();
  }

  function mockJobs() { return MOCK_JOBS; }
  function mockJob(id) { return MOCK_JOBS.find(function (j) { return j.id === Number(id); }); }
  function mockApplications(jobId) {
    const userApps = MOCK_USER_APPS.filter(function (a) { return !jobId || a.job_offer_id === Number(jobId); });
    const seeded = MOCK_CANDIDATES.filter(function (c) { return !jobId || c.job === Number(jobId); })
      .map(function (c) {
        return {
          id: c.id, matching_score: c.score, matched_keywords: c.matched, missing_keywords: c.missing,
          tags: c.tags, status: c.status, cv_path: '', cover_letter: c.cover_letter, notes: c.notes,
          comments: '', candidate: { id: c.id, name: c.name, email: c.email, role: 'candidate', avatar: null, badges: c.status === 'accepted' ? [{ type: 'interview_passed', awarded_at: '2026-08-05T00:00:00+00:00' }] : [] },
          job_offer: mockJob(c.job), interviews: c.interviews, created_at: c.applied_at,
        };
      });
    return userApps.concat(seeded)
      .filter(function (a) { return !isMockAppDeleted(a.id); })
      .map(applyMockOverrides);
  }
  function mockApplication(id) {
    if (isMockAppDeleted(id)) return null;
    const c = MOCK_CANDIDATES.find(function (x) { return x.id === Number(id); });
    return applyMockOverrides(mockApplications().find(function (a) { return a.id === Number(id); }) || (c ? {
      id: c.id, matching_score: c.score, matched_keywords: c.matched, missing_keywords: c.missing,
      tags: c.tags, status: c.status, cv_path: '', cover_letter: c.cover_letter, notes: c.notes, comments: '',
      candidate: { id: c.id, name: c.name, email: c.email, role: 'candidate', avatar: null, badges: [] },
      job_offer: mockJob(c.job), interviews: c.interviews, created_at: c.applied_at,
    } : null));
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
        window.location.href = '/recruiter/jobs?search=' + encodeURIComponent(input.value.trim());
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
      if (!token) { window.location.replace('/login'); return false; }
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
      if (roleLabel) roleLabel.textContent = navRole === 'recruiter' ? 'Recruiter' : 'Candidate';
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
      bindPasswordToggles(form);
      document.querySelectorAll('[data-demo-email]').forEach(function (el) {
        el.addEventListener('click', function () {
          form.email.value = el.dataset.demoEmail || '';
          form.password.value = el.dataset.demoPassword || '';
          clearFormError(form);
          form.password.focus();
        });
      });
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFormError(form);
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
      bindPasswordToggles(form);
      let selectedRole = 'recruiter';
      const opts = form.querySelectorAll('.role-option');
      opts.forEach(function (opt) {
        opt.addEventListener('click', function () {
          opts.forEach(function (o) {
            o.classList.remove('active');
            o.setAttribute('aria-pressed', 'false');
            // The selected-look classes live on the buttons' markup (border-secondary
            // bg-secondary/5 vs border-line bg-white) — swap them so the highlight
            // actually follows the clicked role.
            o.classList.toggle('border-secondary', o === opt);
            o.classList.toggle('bg-secondary/5', o === opt);
            o.classList.toggle('border-line', o !== opt);
            o.classList.toggle('bg-white', o !== opt);
          });
          opt.classList.add('active');
          opt.setAttribute('aria-pressed', 'true');
          selectedRole = opt.dataset.role;
        });
      });
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFormError(form);
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
      let apps = [];

      function renderStats() {
        const grid = document.getElementById('statGrid');
        const funnels = dash.funnels || [];
        const received = funnels.reduce(function (s, f) { return s + (f.received || 0); }, 0);
        const inInterview = funnels.reduce(function (s, f) { return s + (f.interview || 0); }, 0);
        const scores = apps.map(function (a) { return Number(a.matching_score) || 0; });
        const avg = scores.length ? (scores.reduce(function (s, v) { return s + v; }, 0) / scores.length) : 0;
        const stats = [
          { label: 'Active offers', value: String(funnels.length || SR.mock.jobs().length), icon: 'briefcase', cls: 'blue', delta: 'published on the platform' },
          { label: 'Applications received', value: String(received), icon: 'users', cls: 'green', delta: 'total across all offers' },
          { label: 'In interview', value: String(inInterview), icon: 'calendar', cls: 'amber', delta: 'applications being processed' },
          { label: 'Average score', value: Math.round(avg) + ' / 100', icon: 'target', cls: 'red', delta: 'average AI compatibility' },
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
        if (!funnels.length) { box.innerHTML = '<div class="empty">No offers.</div>'; return; }
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
            '<span class="funnel-sub">' + total + ' applications</span></div>' +
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
        if (!list.length) { box.innerHTML = '<div class="empty">No activity.</div>'; return; }
        const ic = { application: 'upload', interview: 'calendar', acceptance: 'check', refusal: 'x' };
        box.innerHTML = list.map(function (a) {
          return '<li class="activity-item"><span class="act-ic">' + SR.icon(ic[a.type] || 'info', 16) + '</span>' +
            '<div><div class="act-label">' + escapeHtml(a.label) + '</div>' +
            '<div class="act-at">' + fmtDateTime(a.at) + '</div></div></li>';
        }).join('');
      }

      function renderTimeToHire() {
        const box = document.getElementById('timeToHireBox');
        if (!box) return;
        const t = dash.time_to_hire || {};
        const rows = (t.by_offer || []).filter(function (o) { return o.avg_days > 0; });
        const titleOf = {};
        (dash.funnels || []).forEach(function (f) { titleOf[f.job_offer_id] = f.title; });
        const rowsHtml = rows.map(function (o) {
          return '<div class="task-item"><span class="task-dot navy"></span>' +
            '<span style="flex:1;font-size:13.5px;color:var(--ink)">' + escapeHtml(titleOf[o.job_offer_id] || ('Offer #' + o.job_offer_id)) + '</span>' +
            '<span class="mono" style="font-weight:600;color:var(--navy)">' + o.avg_days + ' d</span></div>';
        }).join('');
        box.innerHTML = '<div class="task-item"><span class="task-dot green"></span>' +
          '<span style="flex:1;font-size:13.5px;color:var(--ink)">Global average</span>' +
          '<span class="mono" style="font-weight:600;color:var(--navy)">' + (t.global_avg_days || 0) + ' days</span></div>' +
          rowsHtml;
      }

      function renderTrend() {
        const box = document.getElementById('trendBox');
        if (!box) return;
        const pts = dash.applications_trend || [];
        if (!pts.length) { box.innerHTML = '<div class="empty">No applications yet.</div>'; return; }
        const max = Math.max.apply(null, pts.map(function (p) { return p.count; })) || 1;
        const bars = pts.map(function (p) {
          const h = Math.max(6, Math.round((p.count / max) * 100));
          const label = p.date + ' — ' + p.count + ' application' + (p.count === 1 ? '' : 's');
          return '<div class="trend-bar-wrap" title="' + label + '">' +
            '<div class="trend-bar" style="height:' + h + '%"></div></div>';
        }).join('');
        function shortDate(iso) {
          const d = new Date(iso + 'T00:00:00');
          return isNaN(d.getTime()) ? iso : d.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
        const mid = pts[Math.floor(pts.length / 2)];
        box.innerHTML = '<div class="trend-chart">' + bars + '</div>' +
          '<div class="trend-axis"><span>' + shortDate(pts[0].date) + '</span>' +
          '<span>' + shortDate(mid.date) + '</span>' +
          '<span>' + shortDate(pts[pts.length - 1].date) + '</span></div>';
      }

      function renderHealth() {
        const box = document.getElementById('healthBox');
        if (!box) return;
        const h = dash.pipeline_health || {};
        const p = dash.pending_tasks || {};
        const stale = h.stale_by_offer || [];
        const deadlines = h.deadline_soon || [];
        const row = function (dot, cls, label, value, valueColor) {
          return '<div class="task-item"><span class="task-dot ' + dot + '"></span>' +
            '<span style="flex:1;font-size:13.5px;color:var(--ink)">' + label + '</span>' +
            '<span class="mono" style="font-weight:600;color:' + valueColor + '">' + value + '</span></div>';
        };
        let html = '';
        if ((p.interviews_to_evaluate || 0) > 0) {
          html += row('red', '', 'Interviews to evaluate', p.interviews_to_evaluate, 'var(--danger)');
        }
        stale.forEach(function (s) {
          html += row('amber', '', escapeHtml(s.title) + ' — pending 7+ days', s.count, 'var(--warning)');
        });
        deadlines.forEach(function (d) {
          html += row('navy', '', escapeHtml(d.title) + ' closes ' + d.deadline, 'soon', 'var(--navy)');
        });
        html += row('green', '', 'Avg time to first interview', (h.avg_first_response_days || 0) + ' days', 'var(--navy)');
        box.innerHTML = html;
      }

      function renderUpcoming() {
        const box = document.getElementById('upcomingBox');
        if (!box) return;
        const list = dash.upcoming_interviews || [];
        if (!list.length) { box.innerHTML = '<div class="empty">No upcoming interviews.</div>'; return; }
        const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        box.innerHTML = list.map(function (iv) {
          const dt = new Date(iv.scheduled_at);
          const day = isNaN(dt.getTime()) ? '' : dt.getDate();
          const mon = isNaN(dt.getTime()) ? '' : MONTHS[dt.getMonth()];
          const time = isNaN(dt.getTime()) ? '' : dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          return '<div class="upcoming-item">' +
            '<div class="upcoming-date"><span class="upcoming-day">' + day + '</span>' +
            '<span class="upcoming-mon">' + mon + '</span></div>' +
            '<div class="upcoming-main"><div class="upcoming-who">' + escapeHtml(iv.candidate_name) + '</div>' +
            '<div class="upcoming-what">' + escapeHtml(iv.job_title) + '</div></div>' +
            (iv.link
              ? '<a class="upcoming-time" href="' + iv.link + '" target="_blank" rel="noopener" title="Join meeting">' + time + ' ↗</a>'
              : '<span class="upcoming-time">' + time + '</span>') +
            '</div>';
        }).join('');
      }

      function renderTopCandidates() {
        const box = document.getElementById('topCandBox');
        if (!box) return;
        const list = dash.top_candidates || [];
        if (!list.length) { box.innerHTML = '<div class="empty">No candidates yet.</div>'; return; }
        const badgeLabel = { cv_complet: 'CV complete', high_match: 'High match', interview_passed: 'Passed interview' };
        box.innerHTML = list.map(function (c, i) {
          const badges = (c.candidate && c.candidate.badges) || [];
          const chips = badges.map(function (b) {
            return '<span class="chip chip-ok">' + (badgeLabel[b] || b.replace('_', ' ')) + '</span>';
          }).join('');
          return '<div class="topcand-item">' +
            '<span class="pill-rank">' + (i + 1) + '</span>' +
            scoreRing(c.matching_score, 'sm') +
            '<div class="topcand-main"><div class="topcand-name">' + escapeHtml(c.candidate ? c.candidate.name : 'Candidate') + '</div>' +
            '<div class="topcand-job">' + escapeHtml(c.job_offer ? c.job_offer.title : '') + '</div>' +
            (chips ? '<div class="topcand-chips">' + chips + '</div>' : '') + '</div>' +
            '<span class="pill ' + statusPill(c.status) + '">' + (STATUS_LABELS[c.status] || c.status) + '</span>' +
            '</div>';
        }).join('');
      }

      function renderCompare() {
        const box = document.getElementById('offerCompareBox');
        const list = dash.offer_comparison || [];
        if (!list.length) { box.innerHTML = '<div class="empty">No data.</div>'; return; }
        const titleOf = {};
        (dash.funnels || []).forEach(function (f) { titleOf[f.job_offer_id] = f.title; });
        box.innerHTML = list.map(function (o) {
          const pct = o.interview_to_accepted || 0;
          return '<div class="funnel-row">' +
            '<div class="funnel-head"><span class="funnel-title">' + escapeHtml(titleOf[o.job_offer_id] || ('Offer #' + o.job_offer_id)) + '</span>' +
            '<span class="funnel-sub">' + pct + '% vs your avg ' + o.recruiter_avg + '%</span></div>' +
            '<div class="dist-track"><div class="dist-fill" style="width:' + Math.min(pct, 100) + '%;background:var(--blue)"></div></div></div>';
        }).join('');
      }

      let dash = {};
      Promise.all([
        SR.load('/dashboard/stats', SR.mock.dashboard),
      ]).then(function (results) {
        dash = results[0] || {};
        apps = (dash.applications && dash.applications.length ? dash.applications : SR.mock.applications());
        renderStats();
        renderFunnels();
        renderScoreDist();
        renderTimeToHire();
        renderTrend();
        renderHealth();
        renderTopCandidates();
        renderUpcoming();
        renderActivity();
        renderCompare();
        const sub = document.getElementById('dashSub');
        if (sub) {
          const u = auth.user();
          sub.textContent = (u ? 'Welcome, ' + u.name + '.' : '') + ' Here is an overview of your recruitment activity.';
        }
      }).catch(function () {
        toast('Unable to load the dashboard.', 'error');
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

  function clearFormError(form) {
    const wrap = form.querySelector('.form-alert');
    if (wrap) wrap.style.display = 'none';
  }

  /* Wrap every password input in a reveal toggle (eye icon) */
  function bindPasswordToggles(scope) {
    scope = scope || document;
    const inputs = scope.querySelectorAll('input[type="password"]');
    inputs.forEach(function (input) {
      if (input.dataset.pwBound) return;
      input.dataset.pwBound = '1';
      const wrap = document.createElement('div');
      wrap.className = 'password-wrap';
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'password-toggle';
      btn.setAttribute('aria-label', 'Show password');
      btn.setAttribute('aria-pressed', 'false');
      btn.innerHTML = icon('eye', 18);
      btn.addEventListener('click', function () {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.innerHTML = icon(show ? 'eyeOff' : 'eye', 18);
      });
      wrap.appendChild(btn);
    });
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
          return '<a class="job-card" href="/jobs/' + j.id + '">' +
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
          if (!u) return '/login';
          if (u.role === 'candidate') return '/apply/' + j.id;
          return '/my-applications';
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
      let funnels = {}; // keyed by job_offer_id: { received, interview, accepted, refused }

      function pipelineHtml(j) {
        const f = funnels[j.id] || { received: 0, interview: 0, accepted: 0, refused: 0 };
        const segs = [
          ['received', f.received, 'var(--pink)'],
          ['interview', f.interview, 'var(--pink-light)'],
          ['accepted', f.accepted, 'var(--success)'],
          ['refused', f.refused, 'var(--danger)'],
        ];
        const total = segs.reduce(function (s, x) { return s + x[1]; }, 0) || 1;
        const bar = segs.map(function (s) {
          return '<span style="width:' + ((s[1] / total) * 100).toFixed(1) + '%;background:' + s[2] + '" title="' + STATUS_LABELS[s[0]] + '"></span>';
        }).join('');
        const counts = segs.map(function (s) {
          return '<span class="mini-pipe" title="' + STATUS_LABELS[s[0]] + '"><i style="background:' + s[2] + '"></i><b>' + s[1] + '</b></span>';
        }).join('');
        return '<div class="mini-bar" aria-hidden="true">' + bar + '</div>' +
          '<div class="mini-pipeline">' + counts + '</div>';
      }

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
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--slate);padding:40px">No job offers found.</td></tr>';
          return;
        }
        tbody.innerHTML = list.map(function (j) {
          const chips = (j.tech_stack_array || []).slice(0, 3).map(function (t) {
            return '<span class="chip" style="font-size:11.5px">' + escapeHtml(t) + '</span>';
          }).join('');
          const archived = j.status === 'archived';
          const actions = '<a class="btn btn-sm btn-ghost" href="/recruiter/jobs/' + j.id + '/applications">Pipeline</a> ' +
            '<a class="btn btn-sm btn-ghost" href="/recruiter/jobs/' + j.id + '/edit">Edit</a> ' +
            (archived
              ? '<button class="btn btn-sm btn-ghost" data-action="restore" data-id="' + j.id + '" data-title="' + escapeHtml(j.title) + '">Restore</button> ' +
                '<button class="btn btn-sm btn-ghost-danger" data-action="delete" data-id="' + j.id + '" data-title="' + escapeHtml(j.title) + '">Delete</button>'
              : '<button class="btn btn-sm btn-ghost-danger" data-action="archive" data-id="' + j.id + '" data-title="' + escapeHtml(j.title) + '">Archive</button>');
          return '<tr>' +
            '<td><div style="font-weight:600;color:var(--ink)">' + escapeHtml(j.title) + '</div>' +
            '<div class="chips" style="gap:4px;margin-top:4px">' + chips + '</div></td>' +
            '<td><span class="pill">' + escapeHtml(j.contract_type) + '</span></td>' +
            '<td class="mono" style="color:var(--slate)">' + fmtDate(j.deadline) + '</td>' +
            '<td>' + pipelineHtml(j) + '</td>' +
            '<td>' + statusPill(j.status) + '</td>' +
            '<td style="text-align:right;white-space:nowrap">' + actions + '</td></tr>';
        }).join('');
      }

      tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        const action = btn.dataset.action;
        const title = btn.dataset.title;

        const confirmHtml = action === 'delete'
          ? '<p style="font-size:14px;color:var(--slate);margin:0 0 18px">Permanently delete « ' + title + ' »? It will be removed from the archived list and its applications will be lost.</p>'
          : '<p style="font-size:14px;color:var(--slate);margin:0 0 18px">' + (action === 'archive' ? 'Archive « ' + title + ' »? It will be hidden from the public site but stays in your archived list.' : 'Restore « ' + title + ' »? It will be visible on the public site again.') + '</p>';
        const confirmLabel = action === 'delete' ? 'Delete' : action === 'archive' ? 'Archive' : 'Restore';
        const confirmClass = action === 'delete' ? 'btn btn-danger' : 'btn btn-primary';

        SR.modal.open(
          confirmHtml +
          '<div style="display:flex;justify-content:flex-end;gap:10px">' +
          '<button class="btn btn-ghost" onclick="SR.modal.close()">Cancel</button>' +
          '<button class="' + confirmClass + '" id="confirmAction">' + confirmLabel + '</button></div>',
          { title: (action === 'delete' ? 'Delete' : action === 'archive' ? 'Archive' : 'Restore') + ' job offer' }
        );

        document.getElementById('confirmAction').addEventListener('click', async function () {
          const btnEl = this;
          SR.helpers.setLoading(btnEl, true);
          try {
            if (action === 'archive') {
              await SR.api.put('/job-offers/' + id, { status: 'archived' });
              jobs.forEach(function (j) { if (j.id === id) j.status = 'archived'; });
              toast('Job offer archived', 'success');
            } else if (action === 'restore') {
              await SR.api.put('/job-offers/' + id, { status: 'active' });
              jobs.forEach(function (j) { if (j.id === id) j.status = 'active'; });
              toast('Job offer restored', 'success');
            } else {
              await SR.api.del('/job-offers/' + id);
              jobs = jobs.filter(function (j) { return j.id !== id; });
              toast('Job offer deleted', 'success');
            }
          } catch (err) {
            if (!USE_MOCKS) { SR.helpers.setLoading(btnEl, false); toast(err.message || 'Action failed', 'error'); return; }
          }
          SR.modal.close();
          render();
        });
      });

      if (searchInput) searchInput.addEventListener('input', debounce(render));
      if (statusSelect) statusSelect.addEventListener('change', render);

      Promise.all([
        SR.load('/recruiter/job-offers', SR.mock.jobs),
        SR.load('/dashboard/stats', SR.mock.dashboard),
      ]).then(function (results) {
        const data = results[0];
        jobs = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
        const d = results[1] || {};
        (d.funnels || []).forEach(function (f) { funnels[f.job_offer_id] = f; });
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
        // Load from the recruiter's own list: the public show endpoint 404s
        // for archived offers, and editing them must keep working.
        SR.load('/recruiter/job-offers', function () { return SR.mock.jobs(); }).then(function (list) {
          const data = Array.isArray(list) ? list : (list && list.data) ? list.data : [];
          const j = data.find(function (x) { return Number(x.id) === jobId; }) || SR.mock.job(jobId);
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
          setTimeout(function () { window.location.href = '/recruiter/jobs'; }, 700);
        } catch (err) {
          SR.helpers.setLoading(btn, false);
          if (USE_MOCKS) {
            toast('Offre ' + (mode === 'edit' ? 'modifiée' : 'publiée') + ' (démo)', 'success');
            setTimeout(function () { window.location.href = '/recruiter/jobs'; }, 700);
            return;
          }
          showFormError(form, err.message || 'Erreur lors de l\'enregistrement');
        }
      });
    },
  };

  /* ---------------- Recruiter: applications (recent list / per-job pipeline) ---------------- */
  SR.pages.recruiterApplications = {
    init: function () {
      const jobId = document.body.dataset.jobId === 'null' ? null : Number(document.body.dataset.jobId);
      if (jobId) { SR.pages.recruiterApplications._kanban(jobId); return; }
      SR.pages.recruiterApplications._list();
    },

    /* -------- Per-job Kanban pipeline -------- */
    _kanban: function (jobId) {
      const board = document.getElementById('kanbanBoard');
      if (!board) return;
      const searchInput = document.getElementById('appsSearch');
      const totalEl = document.getElementById('appsTotal');
      const title = document.getElementById('appsTitle');
      let apps = [];
      let appsAreMock = false; // true when data came from the mock store (demo/fake token)

      const NEXT_STATUS = { received: 'interview', interview: 'accepted', accepted: null, refused: null };
      const NEXT_LABEL = { received: 'Interview', interview: 'Accept' };
      const VALID = { received: ['interview', 'refused'], interview: ['accepted', 'refused'], accepted: [], refused: [] };
      const COL_DOTS = { received: 'var(--pink)', interview: 'var(--pink-light)', accepted: 'var(--success)', refused: 'var(--danger)' };

      function mockFn() {
        return SR.mock.applications(jobId);
      }

      function colApps(status) {
        const q = (searchInput.value || '').toLowerCase().trim();
        return apps.filter(function (a) {
          if (a.status !== status) return false;
          if (!q) return true;
          const name = ((a.candidate && a.candidate.name) || '') + ' ' + ((a.candidate && a.candidate.email) || '');
          return name.toLowerCase().indexOf(q) !== -1;
        });
      }

      function cardHtml(a) {
        const next = NEXT_STATUS[a.status];
        const nextBtn = next
          ? '<button class="btn btn-sm kanban-move" data-id="' + a.id + '" data-next="' + next + '">→ ' + NEXT_LABEL[a.status] + '</button>' +
            (a.status === 'interview' ? '' : '<button class="btn btn-sm btn-ghost-danger kanban-move" data-id="' + a.id + '" data-next="refused">Refuse</button>')
          : '';
        const name = (a.candidate && a.candidate.name) || 'Candidate';
        const jobTxt = (a.job_offer && a.job_offer.title)
          ? '<div class="kanban-cand-job">' + escapeHtml(a.job_offer.title) + '</div>' : '';
        return '<div class="kanban-card" draggable="true" data-id="' + a.id + '" data-status="' + a.status + '">' +
          '<div class="kanban-card-top">' +
          '<div style="display:flex;align-items:center;gap:10px">' + scoreRing(a.matching_score, 'sm') +
          '<div><a class="kanban-cand-name" href="/recruiter/applications/' + a.id + '">' + escapeHtml(name) + '</a>' +
          jobTxt + '</div></div></div>' +
          '<div class="kanban-tags">' + (a.tags || []).map(tagChip).join('') + '</div>' +
          '<div class="kanban-card-foot">' + nextBtn + '</div>' +
          '</div>';
      }

      function render() {
        const cols = ['received', 'interview', 'accepted', 'refused'];
        board.innerHTML = cols.map(function (st) {
          const items = colApps(st);
          const cards = items.length
            ? items.map(cardHtml).join('')
            : '<div class="kanban-empty">No applications here</div>';
          return '<div class="kanban-col" data-status="' + st + '">' +
            '<div class="kanban-col-head">' +
            '<span class="kanban-col-title"><i class="kanban-col-dot" style="background:' + COL_DOTS[st] + '"></i> ' + STATUS_LABELS[st] + '</span>' +
            '<span class="kanban-count">' + items.length + '</span></div>' +
            cards + '</div>';
        }).join('');
        if (totalEl) {
          totalEl.textContent = apps.length + ' application' + (apps.length === 1 ? '' : 's');
        }
        SR.kanban.markDraggable(board);
      }

      function persist(id, status, done) {
        // Demo/mock mode: the mock ids would collide with real DB rows if we
        // PUT them — keep the change local (shared override) so the candidate
        // view reflects it, without touching the real API.
        if (appsAreMock) {
          saveMockStatus(id, status);
          toast('Status updated (demo)', 'success');
          if (done) done(true);
          return;
        }
        SR.api.put('/applications/' + id + '/status', { status: status }).then(function () {
          toast('Status updated', 'success');
          if (done) done(true);
        }).catch(function (err) {
          // A 422 is a real state-machine rejection from the server — surface it
          // even in mock mode instead of silently accepting the optimistic move.
          if (err && err.status === 422) {
            toast(err.message || 'Unable to update the status', 'error');
            if (done) done(false);
            return;
          }
          if (USE_MOCKS) { if (done) done(true); return; }
          if (done) done(false);
          toast(err.message || 'Unable to update the status', 'error');
        });
      }

      board.addEventListener('click', function (e) {
        const btn = e.target.closest('.kanban-move');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        const next = btn.dataset.next;
        const app = apps.find(function (a) { return a.id === id; });
        if (!app) return;
        const from = app.status;
        if (!VALID[from] || VALID[from].indexOf(next) === -1) {
          toast('Cannot move from ' + STATUS_LABELS[from] + ' to ' + STATUS_LABELS[next], 'error');
          return;
        }
        app.status = next;
        render();
        persist(id, next);
      });

      SR.kanban.enable(board, {
        canDrop: function (from, to) {
          return VALID[from] && VALID[from].indexOf(to) !== -1;
        },
        onOptimistic: function (card, to) {
          const a = apps.find(function (x) { return String(x.id) === String(card.dataset.id); });
          if (a) a.status = to;
        },
        onDrop: function (card, from, to, done) {
          persist(Number(card.dataset.id), to, done);
        },
      });

      if (searchInput) searchInput.addEventListener('input', debounce(function () { render(); }));

      SR.load('/job-offers/' + jobId, function () { return SR.mock.job(jobId); }).then(function (j) {
        if (j && title) title.textContent = 'Pipeline — ' + j.title;
      });

      SR.load('/job-offers/' + jobId + '/applications', function () { appsAreMock = true; return mockFn(); }).then(function (data) {
        apps = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
        render();
      }).catch(function () { render(); });
    },

    /* -------- Recent applications list (across all the recruiter's offers) -------- */
    _list: function () {
      const listEl = document.getElementById('recAppsList');
      if (!listEl) return;
      const searchInput = document.getElementById('appsSearch');
      const totalEl = document.getElementById('appsTotal');
      const tabsEl = document.querySelector('.apps-tabs');
      const trashCountEl = document.getElementById('trashCount');
      let apps = [];
      let appsAreMock = false; // true when data came from the mock store (demo/fake token)
      let view = 'active';     // 'active' | 'trashed'
      let trashed = [];
      let trashedCount = 0;

      function rowBody(a, name, job) {
        return avatar(name, 'sm') +
          '<span class="rec-app-copy">' +
          '<span class="rec-app-name">' + escapeHtml(name) + '</span>' +
          '<span class="rec-app-job">' + escapeHtml(job.title || '—') + '</span>' +
          '</span>' +
          scoreRing(a.matching_score, 'sm') +
          '<span class="rec-app-status">' + statusPill(a.status) + '</span>';
      }

      function rowHtml(a) {
        const name = (a.candidate && a.candidate.name) || 'Candidate';
        const job = a.job_offer || {};
        if (view === 'trashed') {
          return '<div class="rec-app-row is-trashed">' +
            '<div class="rec-app-main">' +
            rowBody(a, name, job) +
            '<span class="rec-app-date mono">Deleted ' + fmtDate(a.deleted_at) + '</span>' +
            '</div>' +
            '<button class="btn btn-sm btn-ghost rec-app-restore" data-id="' + a.id + '" data-name="' + escapeHtml(name) + '">Restore</button>' +
            '</div>';
        }
        return '<div class="rec-app-row">' +
          '<a class="rec-app-main" href="/recruiter/applications/' + a.id + '" aria-label="Open the application of ' + escapeHtml(name) + '">' +
          rowBody(a, name, job) +
          '<span class="rec-app-date mono">' + fmtDate(a.created_at) + '</span>' +
          '</a>' +
          '<button class="icon-btn rec-app-del" data-id="' + a.id + '" data-name="' + escapeHtml(name) + '" aria-label="Delete application" title="Delete application">' + icon('trash', 16) + '</button>' +
          '</div>';
      }

      function render() {
        const q = (searchInput.value || '').toLowerCase().trim();
        const source = view === 'trashed' ? trashed : apps;
        const rows = source.filter(function (a) {
          if (!q) return true;
          const hay = ((a.candidate && a.candidate.name) || '') + ' ' +
            ((a.candidate && a.candidate.email) || '') + ' ' +
            ((a.job_offer && a.job_offer.title) || '');
          return hay.toLowerCase().indexOf(q) !== -1;
        });
        listEl.innerHTML = rows.length
          ? rows.map(rowHtml).join('')
          : '<div class="empty" style="padding:34px 10px">' + (source.length ? 'No application matches your search.' : (view === 'trashed' ? 'No deleted applications.' : 'No applications yet.')) + '</div>';
        if (totalEl) {
          totalEl.textContent = q
            ? rows.length + ' of ' + source.length + ' application' + (source.length === 1 ? '' : 's')
            : source.length + (view === 'trashed' ? ' deleted' : '') + ' application' + (source.length === 1 ? '' : 's');
        }
      }

      function updateTrashBadge() {
        if (trashCountEl) trashCountEl.textContent = String(trashedCount);
      }

      function loadActive() {
        SR.load('/recruiter/applications', function () { appsAreMock = true; return SR.mock.applications(); }).then(function (data) {
          apps = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
          render();
        }).catch(function () { render(); });
      }

      function loadTrashed() {
        SR.load('/recruiter/applications/trashed', function () { return []; }).then(function (data) {
          trashed = Array.isArray(data) ? data : (data && data.data) ? data.data : [];
          trashedCount = trashed.length;
          updateTrashBadge();
          if (view === 'trashed') render();
        }).catch(function () {
          trashed = [];
          trashedCount = 0;
          updateTrashBadge();
          if (view === 'trashed') render();
        });
      }

      function doRestore(btn) {
        const a = trashed.find(function (x) { return Number(x.id) === Number(btn.dataset.id); });
        if (!a) return;
        const name = (a.candidate && a.candidate.name) || 'this candidate';
        SR.api.post('/applications/' + a.id + '/restore').then(function () {
          trashed = trashed.filter(function (x) { return Number(x.id) !== Number(a.id); });
          trashedCount = Math.max(0, trashedCount - 1);
          updateTrashBadge();
          toast('Application of ' + name + ' restored', 'success');
          if (view === 'trashed') render();
          loadActive(); // refresh the Active list so the restored app shows up there
        }).catch(function (err) {
          toast(err.message || 'Unable to restore the application', 'error');
        });
      }

      function doDelete(a, btnEl) {
        if (appsAreMock) {
          saveMockDeletedApp(a.id);
          apps = apps.filter(function (x) { return Number(x.id) !== Number(a.id); });
          trashedCount += 1;
          updateTrashBadge();
          toast('Application deleted (demo)', 'success');
          SR.modal.close();
          render();
          return;
        }
        SR.api.del('/applications/' + a.id).then(function () {
          apps = apps.filter(function (x) { return Number(x.id) !== Number(a.id); });
          trashedCount += 1;
          updateTrashBadge();
          toast('Application deleted', 'success');
          SR.modal.close();
          render();
        }).catch(function (err) {
          SR.modal.close();
          toast(err.message || 'Unable to delete the application', 'error');
        });
      }

      if (tabsEl) tabsEl.addEventListener('click', function (e) {
        const btn = e.target.closest('.apps-tab');
        if (!btn || btn.dataset.view === view) return;
        view = btn.dataset.view;
        tabsEl.querySelectorAll('.apps-tab').forEach(function (t) {
          const active = t === btn;
          t.classList.toggle('is-active', active);
          t.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        if (view === 'trashed') loadTrashed();
        else render();
      });

      listEl.addEventListener('click', function (e) {
        const restoreBtn = e.target.closest('.rec-app-restore');
        if (restoreBtn) { doRestore(restoreBtn); return; }
        const btn = e.target.closest('.rec-app-del');
        if (!btn) return;
        const app = apps.find(function (x) { return Number(x.id) === Number(btn.dataset.id); });
        if (!app) return;
        const name = (app.candidate && app.candidate.name) || 'this candidate';
        SR.modal.open(
          '<p style="font-size:14px;color:var(--slate);margin:0 0 18px">Delete the application of <strong>' + escapeHtml(name) + '</strong>? It will disappear from your applications and the pipeline, but its history is kept for your dashboard analytics. You can restore it later from the Deleted view.</p>' +
          '<div style="display:flex;justify-content:flex-end;gap:0.75rem">' +
          '<button class="btn btn-ghost" onclick="SR.modal.close()">Cancel</button>' +
          '<button class="btn btn-danger" id="confirmDelete">Delete</button></div>',
          { title: 'Delete application' }
        );
        document.getElementById('confirmDelete').addEventListener('click', function () {
          doDelete(app, this);
        });
      });

      if (searchInput) searchInput.addEventListener('input', debounce(render));

      loadActive();
      loadTrashed(); // populate the Deleted badge
    },  };

  /* ---------------- Recruiter: application detail ---------------- */
  /* ---------------- Recruiter: application detail (Round 25 premium) ---------------- */
  SR.pages.recruiterApplicationShow = {
    init: function () {
      const wrap = document.getElementById('appDetail');
      if (!wrap) return;
      const id = Number(document.body.dataset.applicationId);
      const TAGS = ['a_relancer', 'prioritaire', 'reserve', 'entretien_planifie'];
      const BADGE_META = {
        cv_complet: { cls: 'badge-cv', label: 'CV complet' },
        high_match: { cls: 'badge-high', label: 'Score élevé' },
        interview_passed: { cls: 'badge-iv', label: 'Entretien réussi' },
      };
      let app = null;
      let appIsMock = false; // true when data came from the mock store (demo/fake token)

      wrap.classList.add('detail-page-scope', 'detail-page-type');
      const h1 = document.getElementById('appName');
      if (h1) h1.classList.add('detail-page-h1');

      SR.load('/applications/' + id, function () { appIsMock = true; return SR.mock.application(id); }).then(function (a) {
        if (!a) { wrap.innerHTML = '<div class="card card-pad empty"><p>Candidature introuvable.</p></div>'; return; }
        app = a;
        const back = document.getElementById('appBackLink');
        back.href = '/recruiter/applications';
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
        const hasCv = !!a.cv_path;

        wrap.innerHTML =
          '<div class="detail-col">' +

          /* ---- Header card ---- */
          '<div class="detail-card">' +
          '<div class="detail-head">' +
          '<span class="avatar-lg">' + escapeHtml(initials(name)) + '</span>' +
          '<div class="detail-head-main">' +
          '<div class="detail-name">' + escapeHtml(name) + '</div>' +
          '<div class="detail-role">' + escapeHtml(email) + ' — postulé le ' + fmtDate(a.created_at) + '</div>' +
          '<div class="detail-meta">' + statusPill(a.status) +
          '<div class="detail-badges">' + badges.map(function (b) {
            const meta = BADGE_META[b.type] || { cls: 'badge-cv', label: b.type.replace('_', ' ') };
            return '<span class="badge-chip ' + meta.cls + '">' + escapeHtml(meta.label) + '</span>';
          }).join('') + '</div></div>' +
          '<div class="detail-meta"><span class="detail-num" style="font-size:12.5px">Offre :</span> <a href="/jobs/' + job.id + '" style="color:var(--dp-purple);font-weight:600">' + escapeHtml(job.title || '') + '</a></div>' +
          '</div>' +
          '<div style="text-align:center">' + scoreRing(a.matching_score) +
          '<div class="detail-num" style="font-size:11px;color:var(--dp-slate);margin-top:4px">Score IA</div></div>' +
          '</div></div>' +

          /* ---- CV card ---- */
          '<div class="detail-card"><div class="detail-card-head"><h2 class="detail-h2">Curriculum Vitae</h2></div>' +
          (hasCv
            ? '<div class="cv-file">' +
              '<span class="cv-file-icon">' + SR.icon('briefcase', 20) + '</span>' +
              '<div style="min-width:0"><div class="cv-file-name">' + escapeHtml(cvFileName(a.cv_path)) + '</div>' +
              '<div class="cv-file-sub">PDF — déposé le ' + fmtDate(a.created_at) + '</div></div>' +
              '<span class="cv-actions">' +
              '<button class="btn btn-sm btn-ghost" id="cvView" type="button">' + SR.icon('eye', 15) + ' Voir</button>' +
              '<button class="btn btn-sm btn-ghost" id="cvDownload" type="button">' + SR.icon('download', 15) + ' Télécharger</button>' +
              '</span></div>'
            : '<p class="ai-empty">Aucun CV déposé.</p>') +
          (a.cover_letter ? '<div class="detail-cover">' + escapeHtml(a.cover_letter) + '</div>' : '') +
          '</div>' +

          /* ---- AI analysis card ---- */
          '<div class="detail-card">' +
          '<div class="detail-card-head"><h2 class="detail-h2">Analyse IA</h2>' +
          '<button class="btn btn-ai btn-sm" id="btnAnalyze" type="button">' + SR.icon('sparkles', 15) + ' Analyser avec l\'IA</button></div>' +
          '<div id="aiBody">' + aiBody(a) + '</div>' +
          '</div>' +

          /* ---- Tags card ---- */
          '<div class="detail-card"><div class="detail-card-head"><h2 class="detail-h2">Tags rapides</h2></div>' +
          '<div class="chips" id="tagBox">' +
          TAGS.map(function (t) {
            const on = tags.indexOf(t) !== -1;
            return '<button type="button" class="tag-opt' + (on ? ' on' : '') + '" data-tag="' + t + '" aria-pressed="' + on + '">' + tagChip(t) + '</button>';
          }).join('') + '</div></div>' +

          /* ---- Notes card ---- */
          '<div class="detail-card"><div class="detail-card-head"><h2 class="detail-h2">Notes internes</h2></div>' +
          '<textarea class="textarea" id="appNotes" rows="4" placeholder="Observations, points à vérifier…">' + escapeHtml(a.notes || '') + '</textarea>' +
          '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;align-items:center">' +
          '<span class="detail-num" id="notesStatus" style="font-size:12px;color:var(--dp-slate)"></span>' +
          '<button class="btn btn-primary btn-sm" id="saveNotes" type="button">' + SR.icon('check', 15) + ' Enregistrer</button></div>' +
          '</div>' +

          '</div>' +

          '<div class="detail-side-col">' +

          /* ---- Interviews card ---- */
          '<div class="detail-card">' +
          '<div class="detail-card-head"><h2 class="detail-h2">Entretiens</h2>' +
          '<button class="btn btn-ghost btn-sm" id="scheduleInterview" type="button">' + SR.icon('calendar', 15) + ' Planifier</button></div>' +
          '<div id="interviewsBox">' + renderInterviews(interviews) + '</div>' +
          '</div>' +

          /* ---- Decision card ---- */
          '<div class="detail-card"><div class="detail-card-head"><h2 class="detail-h2">Décision</h2></div>' +
          '<div id="decisionBox">' + renderStatusActions(a.status) + '</div>' +
          '</div>' +

          suggestionsCard(a) +

          '</div>';
        bindDetail();
      }

      function cvFileName(p) {
        p = String(p || '');
        const i = p.lastIndexOf('/');
        return i >= 0 ? p.slice(i + 1) : p;
      }

      function aiBody(a) {
        const matched = a.matched_keywords || [];
        const missing = a.missing_keywords || [];
        const hasAnalysis = (a.matching_score != null) || matched.length || missing.length;
        if (!hasAnalysis) {
          return '<p class="ai-empty">Aucune analyse disponible. Lancez une analyse IA pour calculer le score de compatibilité avec l\'offre et les mots-clés trouvés / manquants.</p>';
        }
        return '<div class="ai-score-line"><span class="ai-score-num">' + Math.round(Number(a.matching_score) || 0) + '</span>' +
          '<span class="ai-score-max">/ 100</span></div>' +
          '<div style="margin-top:0.875rem;display:grid;gap:12px">' +
          '<div><div class="detail-num" style="font-size:12px;color:var(--dp-slate);margin-bottom:6px">Mots-clés trouvés (' + matched.length + ')</div>' +
          '<div class="chips">' + (matched.map(function (k) { return '<span class="kw-chip ok">' + escapeHtml(k) + '</span>'; }).join('') || '<span class="detail-num" style="color:var(--dp-slate)">Aucun</span>') + '</div></div>' +
          '<div><div class="detail-num" style="font-size:12px;color:var(--dp-slate);margin-bottom:6px">Mots-clés manquants (' + missing.length + ')</div>' +
          '<div class="chips">' + (missing.map(function (k) { return '<span class="kw-chip miss">' + escapeHtml(k) + '</span>'; }).join('') || '<span class="detail-num" style="color:var(--dp-slate)">Aucun — profil complet</span>') + '</div></div>' +
          '</div>';
      }

      function renderInterviews(list) {
        if (!list.length) return '<div class="empty" style="padding:16px">Aucun entretien planifié.</div>';
        return '<div style="display:grid;gap:10px">' + list.map(function (iv) {
          const scores = [iv.score_technique, iv.score_communication, iv.score_motivation].filter(function (s) { return s != null; });
          const avgTxt = scores.length ? '<span class="detail-num" style="font-size:12px;color:var(--dp-slate)">moy. ' + (scores.reduce(function (s, x) { return s + x; }, 0) / scores.length).toFixed(1) + '/5</span>' : '';
          return '<div class="interview-row">' +
            '<div class="iv-head">' +
            '<span class="pill ' + (STATUS_PILL_CLASS[iv.status] || '') + '">' + escapeHtml(STATUS_LABELS[iv.status] || iv.status) + '</span>' +
            '<span class="iv-date"><span class="detail-num">' + fmtDateTime(iv.scheduled_at) + '</span></span>' +
            (iv.link ? '<a href="' + escapeHtml(iv.link) + '" target="_blank" rel="noopener" style="color:var(--dp-purple)" title="Rejoindre">' + SR.icon('video', 15) + '</a>' : '') +
            (iv.status === 'scheduled'
              ? '<span class="iv-actions">' +
                '<button class="btn btn-sm btn-ghost" data-iv="complete" data-id="' + iv.id + '" type="button">Noter</button>' +
                '<button class="btn btn-sm btn-ghost-danger" data-iv="cancel" data-id="' + iv.id + '" type="button">Annuler</button>' +
                '</span>'
              : '') +
            '</div>' +
            (iv.status === 'completed' ? '<div class="iv-scores">' +
              '<span class="iv-score">Technique <b>' + (iv.score_technique ?? '—') + '</b></span>' +
              '<span class="iv-score">Communication <b>' + (iv.score_communication ?? '—') + '</b></span>' +
              '<span class="iv-score">Motivation <b>' + (iv.score_motivation ?? '—') + '</b></span>' +
              avgTxt + '</div>' : '') +
            '</div>';
        }).join('') + '</div>';
      }

      function renderStatusActions(status) {
        if (status === 'accepted' || status === 'refused') {
          return '<p class="detail-num" style="color:var(--dp-slate);margin:0">Statut terminal — aucune action possible.</p>';
        }
        const next = status === 'received' ? [['interview', 'Passer en entretien'], ['refused', 'Refuser']] : [['accepted', 'Accepter'], ['refused', 'Refuser']];
        return '<div style="display:flex;gap:10px;flex-wrap:wrap">' + next.map(function (n) {
          const danger = n[0] === 'refused';
          return '<button class="btn ' + (danger ? 'btn-ghost-danger' : 'btn-primary') + '" data-action="status" data-status="' + n[0] + '" type="button">' + n[1] + '</button>';
        }).join('') + '</div>';
      }

      function suggestionsCard(a) {
        if (a.status !== 'refused') return '';
        return '<div class="detail-card"><div class="detail-card-head"><h2 class="detail-h2">Profils similaires suggérés</h2></div>' +
          '<div id="suggBox"><button class="btn btn-ghost btn-sm" id="loadSugg" type="button">' + SR.icon('users', 15) + ' Afficher les suggestions</button></div></div>';
      }

      function setStatus(next, comment) {
        const prev = app.status;
        if (appIsMock) {
          app.status = next;
          saveMockStatus(app.id, next);
          if (comment) app.comments = comment;
          toast('Statut → ' + (STATUS_LABELS[next] || next) + ' (démo)', 'success');
          render();
          return;
        }
        SR.api.put('/applications/' + app.id + '/status', { status: next })
          .then(function () {
            app.status = next;
            if (comment) {
              app.comments = comment;
              return SR.api.put('/applications/' + app.id + '/notes', { notes: app.notes || '', comments: comment }).catch(function () {});
            }
          })
          .then(function () {
            render();
            toast('Statut → ' + (STATUS_LABELS[next] || next), 'success');
          })
          .catch(function (err) {
            app.status = prev;
            render();
            toast((err && err.message) || 'Impossible de changer le statut', 'error');
          });
      }

      function askConfirm(opts) {
        SR.modal.open(
          '<p class="confirm-copy">' + opts.copy + '</p>' +
          (opts.commentLabel
            ? '<div class="form-group"><label class="form-label" for="confirmComment">' + opts.commentLabel + '</label>' +
              '<textarea class="textarea confirm-comment" id="confirmComment" rows="3" placeholder="Optionnel — sera visible par le candidat"></textarea></div>'
            : '') +
          '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">' +
          '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
          '<button class="btn ' + (opts.danger ? 'btn-danger' : 'btn-primary') + '" id="confirmGo" type="button">' + opts.confirmLabel + '</button></div>',
          { title: opts.title }
        );
        document.getElementById('confirmGo').addEventListener('click', function () {
          const comment = opts.commentLabel ? document.getElementById('confirmComment').value.trim() : null;
          SR.modal.close();
          opts.onConfirm(comment);
        });
      }

      function openCv(mode) {
        if (!app.cv_path) { toast('Aucun CV déposé', 'info'); return; }
        if (appIsMock) { toast('CV non disponible en mode démo', 'info'); return; }
        fetch(API + '/applications/' + app.id + '/cv', {
          headers: auth.token() ? { 'Authorization': 'Bearer ' + auth.token(), 'Accept': 'application/pdf' } : {},
        }).then(function (res) {
          if (!res.ok) throw new Error('Erreur ' + res.status);
          return res.blob();
        }).then(function (blob) {
          const url = URL.createObjectURL(blob);
          if (mode === 'download') {
            const a = document.createElement('a');
            a.href = url;
            a.download = cvFileName(app.cv_path);
            a.click();
            URL.revokeObjectURL(url);
            toast('CV téléchargé', 'success');
          } else {
            window.open(url, '_blank');
          }
        }).catch(function (err) {
          toast((err && err.message) || 'Impossible de charger le CV', 'error');
        });
      }

      function runAnalyze() {
        const btn = document.getElementById('btnAnalyze');
        const aiBodyEl = document.getElementById('aiBody');
        const setBusy = function (busy) {
          if (!btn) return;
          btn.disabled = busy;
          btn.innerHTML = busy
            ? '<span class="ai-spin"></span> Analyse en cours…'
            : SR.icon('sparkles', 15) + ' Analyser avec l\'IA';
        };
        setBusy(true);
        if (aiBodyEl) aiBodyEl.innerHTML = '<div style="display:flex;align-items:center;gap:10px;color:var(--dp-slate);font-size:13.5px"><span class="ai-spin dark"></span> Analyse du CV par l\'IA…</div>';
        if (appIsMock) {
          setTimeout(function () {
            const stack = (app.job_offer && app.job_offer.tech_stack_array) || [];
            const keep = Math.max(2, Math.floor(stack.length * 0.6));
            const matched = stack.slice(0, keep);
            const missing = stack.slice(keep);
            const score = Math.round(55 + Math.random() * 40);
            app.matching_score = score;
            app.matched_keywords = matched;
            app.missing_keywords = missing;
            app.analysis = { matching_score: score, matched_keywords: matched, missing_keywords: missing };
            render();
            toast('Analyse IA terminée (démo)', 'success');
          }, 1400);
          return;
        }
        SR.api.post('/applications/' + app.id + '/analyze')
          .then(function (res) {
            const data = res && res.data ? res.data : res;
            if (data && data.analysis) {
              app.matching_score = data.analysis.matching_score;
              app.matched_keywords = data.analysis.matched_keywords || [];
              app.missing_keywords = data.analysis.missing_keywords || [];
              app.analysis = data.analysis;
            }
            render();
            toast('Analyse IA terminée', 'success');
          })
          .catch(function (err) {
            setBusy(false);
            if (aiBodyEl) aiBodyEl.innerHTML = '<p class="ai-empty">' + escapeHtml((err && err.message) || 'Échec de l\'analyse IA') + '</p>';
            toast((err && err.message) || 'Échec de l\'analyse IA', 'error');
          });
      }

      function bindDetail() {
        // Tags
        const tagBox = document.getElementById('tagBox');
        if (tagBox) tagBox.addEventListener('click', function (e) {
          const btn = e.target.closest('.tag-opt');
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
        if (saveNotes) saveNotes.addEventListener('click', function () {
          const notes = document.getElementById('appNotes').value;
          app.notes = notes;
          const st = document.getElementById('notesStatus');
          SR.api.put('/applications/' + app.id + '/notes', { notes: notes })
            .then(function () {
              if (st) st.textContent = 'Enregistré ✓';
              setTimeout(function () { if (st) st.textContent = ''; }, 2200);
              toast('Notes enregistrées', 'success');
            })
            .catch(function (err) {
              if (USE_MOCKS) {
                if (st) st.textContent = 'Enregistré ✓';
                setTimeout(function () { if (st) st.textContent = ''; }, 2200);
                toast('Notes enregistrées (démo)', 'success');
                return;
              }
              toast((err && err.message) || 'Impossible d\'enregistrer les notes', 'error');
            });
        });

        // CV
        const cvView = document.getElementById('cvView');
        if (cvView) cvView.addEventListener('click', function () { openCv('view'); });
        const cvDownload = document.getElementById('cvDownload');
        if (cvDownload) cvDownload.addEventListener('click', function () { openCv('download'); });

        // Analyze
        const btnAnalyze = document.getElementById('btnAnalyze');
        if (btnAnalyze) btnAnalyze.addEventListener('click', runAnalyze);

        // Status actions (delegated) — confirm for terminal-ish decisions
        wrap.addEventListener('click', function (e) {
          const btn = e.target.closest('[data-action="status"]');
          if (!btn) return;
          const next = btn.dataset.status;
          if (next === 'interview') {
            setStatus('interview', null);
            return;
          }
          askConfirm({
            title: next === 'accepted' ? 'Accepter la candidature' : 'Refuser la candidature',
            copy: next === 'accepted'
              ? 'La candidature passera au statut « Acceptée ». Cette action est définitive.'
              : 'La candidature passera au statut « Refusée ». Cette action est définitive.',
            confirmLabel: next === 'accepted' ? 'Accepter' : 'Confirmer le refus',
            danger: next === 'refused',
            commentLabel: next === 'accepted' ? 'Commentaire visible par le candidat (optionnel)' : 'Motif / commentaire visible par le candidat (optionnel)',
            onConfirm: function (comment) { setStatus(next, comment); },
          });
        });

        // Interviews: schedule
        const schedBtn = document.getElementById('scheduleInterview');
        if (schedBtn) schedBtn.addEventListener('click', function () {
          SR.modal.open(
            '<p class="confirm-copy">Planifiez un entretien pour ' + escapeHtml((app.candidate && app.candidate.name) || '') + '.</p>' +
            '<div class="form-group"><label class="form-label" for="ivDate">Date et heure</label>' +
            '<input class="input" type="datetime-local" id="ivDate" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivLink">Lien vidéo (optionnel)</label>' +
            '<input class="input" type="url" id="ivLink" placeholder="https://meet.google.com/…"></div>' +
            '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">' +
            '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
            '<button class="btn btn-primary" id="confirmSched" type="button">Planifier</button></div>',
            { title: 'Planifier un entretien' }
          );
          document.getElementById('confirmSched').addEventListener('click', function () {
            const dt = document.getElementById('ivDate').value;
            if (!dt) { toast('Choisissez une date', 'error'); return; }
            const link = document.getElementById('ivLink').value;
            const btn = document.getElementById('confirmSched');
            btn.disabled = true;
            const temp = { id: Date.now(), scheduled_at: dt, link: link, status: 'scheduled', score_technique: null, score_communication: null, score_motivation: null };
            app.interviews = app.interviews || [];
            app.interviews.push(temp); // optimistic
            SR.modal.close();
            render();
            if (appIsMock) {
              saveMockInterview(app.id, temp);
              toast('Entretien planifié (démo)', 'success');
              return;
            }
            SR.api.post('/applications/' + app.id + '/interviews', { scheduled_at: dt, link: link })
              .then(function (created) {
                const real = created && created.data;
                if (real) app.interviews[app.interviews.length - 1] = real;
                render();
                toast('Entretien planifié', 'success');
              })
              .catch(function (err) {
                app.interviews.pop(); // roll back the optimistic row
                if (USE_MOCKS) { render(); toast('Entretien planifié (démo)', 'success'); return; }
                render();
                toast((err && err.message) || 'Impossible de planifier l\'entretien', 'error');
              });
          });
        });

        // Interviews: complete/cancel
        const ivBox = document.getElementById('interviewsBox');
        if (ivBox) ivBox.addEventListener('click', function (e) {
          const btn = e.target.closest('[data-iv]');
          if (!btn) return;
          const ivId = Number(btn.dataset.id);
          const iv = (app.interviews || []).find(function (x) { return x.id === ivId; });
          if (!iv) return;
          if (btn.dataset.iv === 'cancel') {
            if (appIsMock) {
              iv.status = 'cancelled';
              render();
              saveMockInterview(app.id, iv);
              toast('Entretien annulé (démo)', 'info');
              return;
            }
            const prevCancel = iv.status;
            SR.api.put('/interviews/' + ivId + '/cancel')
              .then(function () {
                iv.status = 'cancelled';
                render();
                toast('Entretien annulé', 'info');
              })
              .catch(function (err) {
                iv.status = prevCancel; // roll back optimistic change
                render();
                toast((err && err.message) || 'Impossible d\'annuler l\'entretien', 'error');
              });
            return;
          }
          SR.modal.open(
            '<p class="confirm-copy">Évaluez l\'entretien de 1 à 5.</p>' +
            '<div class="form-group"><label class="form-label" for="ivTech">Technique</label>' +
            '<input class="input" type="number" id="ivTech" min="1" max="5" value="3" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivCom">Communication</label>' +
            '<input class="input" type="number" id="ivCom" min="1" max="5" value="3" required></div>' +
            '<div class="form-group"><label class="form-label" for="ivMot">Motivation</label>' +
            '<input class="input" type="number" id="ivMot" min="1" max="5" value="3" required></div>' +
            '<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">' +
            '<button class="btn btn-ghost" onclick="SR.modal.close()">Annuler</button>' +
            '<button class="btn btn-primary" id="confirmScore" type="button">Enregistrer</button></div>',
            { title: 'Compléter l\'entretien' }
          );
          document.getElementById('confirmScore').addEventListener('click', function () {
            const scores = {
              score_technique: Number(document.getElementById('ivTech').value),
              score_communication: Number(document.getElementById('ivCom').value),
              score_motivation: Number(document.getElementById('ivMot').value),
            };
            if (appIsMock) {
              iv.status = 'completed';
              iv.score_technique = scores.score_technique;
              iv.score_communication = scores.score_communication;
              iv.score_motivation = scores.score_motivation;
              saveMockInterview(app.id, iv);
              SR.modal.close();
              render();
              toast('Évaluation enregistrée (démo)', 'success');
              return;
            }
            const prevScore = {
              status: iv.status,
              score_technique: iv.score_technique,
              score_communication: iv.score_communication,
              score_motivation: iv.score_motivation,
            };
            SR.modal.close();
            SR.api.put('/interviews/' + ivId + '/complete', scores)
              .then(function () {
                iv.status = 'completed';
                iv.score_technique = scores.score_technique;
                iv.score_communication = scores.score_communication;
                iv.score_motivation = scores.score_motivation;
                render();
                toast('Évaluation enregistrée', 'success');
              })
              .catch(function (err) {
                iv.status = prevScore.status; // roll back optimistic change
                iv.score_technique = prevScore.score_technique;
                iv.score_communication = prevScore.score_communication;
                iv.score_motivation = prevScore.score_motivation;
                render();
                toast((err && err.message) || 'Impossible d\'enregistrer l\'évaluation', 'error');
              });
          });
        });

        // Suggestions
        const loadSugg = document.getElementById('loadSugg');
        if (loadSugg) loadSugg.addEventListener('click', function () {
          const box = document.getElementById('suggBox');
          const suggs = SR.mock.suggestions(app.id);
          box.innerHTML = '<div style="display:grid;gap:2px">' + suggs.map(function (s) {
            const nm = (s.candidate && s.candidate.name) || '';
            return '<div class="sug-row">' +
              avatar(nm, 'sm') +
              '<div style="flex:1;min-width:0"><div class="sug-name">' + escapeHtml(nm) + '</div>' +
              '<div class="sug-job">' + escapeHtml((s.job_offer && s.job_offer.title) || '') + '</div></div>' +
              '<div>' + scoreRing(s.matching_score, 'sm') + '</div>' +
              '<a class="btn btn-sm btn-ghost" href="/recruiter/applications/' + s.id + '">Voir</a>' +
              '</div>';
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
        back.href = '/recruiter/jobs/' + jobId + '/applications';
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
        window.location.href = '/recruiter/applications';
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
      bindPasswordToggles(form);

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
        return SR.mock.applications().filter(function (a) { return user && a.candidate && a.candidate.id === user.id; });
      }

      function detailModal(a) {
        const ivs = (a.interviews && a.interviews.length) ? a.interviews : [];
        const IV_LABEL = { scheduled: 'Entretien planifié', completed: 'Entretien terminé', cancelled: 'Entretien annulé' };
        // Date rows in the details (only non-cancelled interviews)
        const ivRows = ivs.filter(function (i) { return i.status !== 'cancelled'; }).map(function (i) {
          return '<div class="kv"><dt>' + (IV_LABEL[i.status] || 'Entretien') + '</dt><dd>' + fmtDateTime(i.scheduled_at) + '</dd></div>';
        }).join('');
        // Google Meet links at the bottom (scheduled interviews only)
        const meetBtns = ivs.filter(function (i) { return i.status === 'scheduled' && i.link; }).map(function (i) {
          return '<a class="btn btn-primary" style="width:100%" href="' + escapeHtml(i.link) + '" target="_blank" rel="noopener">' +
            icon('video', 16) + ' Rejoindre l\'entretien — Google Meet</a>';
        }).join('');
        SR.modal.open(
          '<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">' +
          '<div><div style="font-weight:700;font-size:17px;color:var(--ink)">' + escapeHtml(a.job_offer ? a.job_offer.title : '') + '</div>' +
          statusPill(a.status) + '</div></div>' +
          '<div class="kv"><dt>Postulé le</dt><dd>' + fmtDate(a.created_at) + '</dd></div>' +
          ivRows +
          (meetBtns ? '<div style="display:grid;gap:10px;margin-top:18px">' + meetBtns + '</div>' : ''),
          { title: 'Ma candidature' }
        );
      }

      let currentList = [];
      function render(list) {
        currentList = list;
        if (!list.length) { box.innerHTML = '<div class="empty">You haven\'t applied yet.<br><a class="btn btn-primary" style="margin-top:12px" href="/jobs">Browse jobs</a></div>'; return; }
        box.innerHTML =
          '<table class="table"><thead><tr><th>Offre</th><th>Statut</th><th>Postulé le</th><th></th></tr></thead><tbody>' +
          list.map(function (a) {
            return '<tr>' +
              '<td><div style="font-weight:600;color:var(--ink)">' + escapeHtml(a.job_offer ? a.job_offer.title : '') + '</div>' +
              '<div class="mono" style="font-size:11.5px;color:var(--slate)">' + escapeHtml((a.job_offer && a.job_offer.contract_type) || '') + '</div></td>' +
              '<td>' + statusPill(a.status) + '</td>' +
              '<td class="mono" style="color:var(--slate)">' + fmtDate(a.created_at) + '</td>' +
              '<td><button class="btn btn-sm btn-ghost" data-detail="' + a.id + '" type="button">Détail</button></td></tr>';
          }).join('') + '</tbody></table>';
      }

      box.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-detail]');
        if (!btn) return;
        const a = currentList.find(function (x) { return x.id === Number(btn.dataset.detail); });
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
        document.getElementById('applyBackLink').href = '/jobs/' + jobId;
        summary.innerHTML =
          '<div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">' +
          '<div><h3 style="margin:0 0 6px">' + escapeHtml(job.title) + '</h3>' +
          '<div class="mono" style="color:var(--slate);font-size:13px">' + escapeHtml(job.contract_type) + ' · ' + fmtSalary(job.salary) +
          ' · Apply before ' + fmtDate(job.deadline) + '</div></div>' +
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
      dz.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
      });
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
            try {
              const fd = new FormData();
              fd.append('cv', selectedFile);
              fd.append('cover_letter', coverText);
              await SR.api.form('/job-offers/' + jobId + '/apply', fd);
            } catch (err) {
              // Backend answered with an error (duplicate application, validation…) → surface it.
              if (err && (err.status || (err.body && err.body.errors))) throw err;
              // Network/backend unreachable → keep the local mock application.
            }
            persistMockApplication(jobId, coverText);
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
        const job = SR.mock.job(jobId) || {};
        SR.modal.open(
          '<div style="text-align:center;padding:6px 0 2px">' +
          '<div style="width:64px;height:64px;border-radius:50%;background:rgb(16 185 129 / 0.12);color:#10b981;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">' +
          '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg></div>' +
          '<h3 style="margin:0 0 6px">Candidature envoyée !</h3>' +
          '<p style="color:var(--slate);font-size:14px;margin:0 0 18px">Votre candidature pour « ' + escapeHtml(job.title || 'cette offre') +
          ' » a bien été transmise.<br>Le recruteur l\'étudiera et vous informera de la suite.</p>' +
          '<button class="btn btn-primary" onclick="window.location.href=\'/my-applications\'">Voir mes candidatures</button></div>',
          { title: 'Candidature envoyée' }
        );
        form.reset();
        setFile(null);
      });

      document.getElementById('applyCancel').addEventListener('click', function () {
        window.location.href = '/jobs/' + jobId;
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
      let interviewsAreMock = false; // true when data came from the mock store (demo/fake token)

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
              : '<a class="btn btn-sm btn-ghost" href="/recruiter/applications/' + iv.application_id + '">Open</a>';
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
            if (interviewsAreMock) {
              iv.status = 'completed';
              iv.score_technique = payload.score_technique;
              iv.score_communication = payload.score_communication;
              iv.score_motivation = payload.score_motivation;
              SR.modal.close(); render();
              toast('Entretien évalué (démo) — moyenne ' + avg(iv) + '/5', 'success');
              return;
            }
            try {
              await SR.api.put('/interviews/' + iv.id + '/complete', payload);
            } catch (err) {
              SR.modal.close();
              toast((err && err.message) || 'Impossible d\'enregistrer l\'évaluation', 'error');
              return;
            }
            iv.status = 'completed';
            iv.score_technique = payload.score_technique;
            iv.score_communication = payload.score_communication;
            iv.score_motivation = payload.score_motivation;
            SR.modal.close(); render();
            toast('Entretien évalué (moyenne ' + avg(iv) + '/5)', 'success');
          });
        }
        if (cancelBtn) {
          const iv = interviews.find(function (x) { return x.id === Number(cancelBtn.dataset.cancel); });
          if (!iv) return;
          if (interviewsAreMock) {
            iv.status = 'cancelled';
            render();
            toast('Entretien annulé (démo)', 'info');
            return;
          }
          const prevCancel = iv.status;
          SR.api.put('/interviews/' + iv.id + '/cancel')
            .then(function () {
              iv.status = 'cancelled';
              render();
              toast('Entretien annulé', 'info');
            })
            .catch(function (err) {
              iv.status = prevCancel; // roll back optimistic change
              render();
              toast((err && err.message) || 'Impossible d\'annuler l\'entretien', 'error');
            });
        }
      });

      SR.load('/interviews', function () { interviewsAreMock = true; return SR.mock.interviews(); }).then(function (data) {
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

  /* ---------------- Reveal-on-scroll (global, all pages) ----------------
     Adds .in-view to [data-reveal] elements. Safe by design:
     - CSS only hides [data-reveal] under html.js, so no-JS stays visible
     - reduced-motion or missing IntersectionObserver -> show immediately
     - fallback timer forces in-viewport elements visible after 2s        */
  function bindReveals() {
    var els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;
    var show = function (el) { el.classList.add('in-view'); };
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(show);
      return;
    }
    if (!('IntersectionObserver' in window)) {
      els.forEach(show);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { show(entry.target); io.unobserve(entry.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    els.forEach(function (el) { io.observe(el); });
    setTimeout(function () {
      els.forEach(function (el) {
        if (!el.classList.contains('in-view') && el.getBoundingClientRect().top < window.innerHeight) show(el);
      });
    }, 2000);
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindGlobalSearch();
    bindSidebarUser();
    bindReveals();
    dispatch();
  });
})();
