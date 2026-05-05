// ============================================================
//  FoodWise · notifications.js
//  Système de notifications — icône 🔔 dans le topbar
//
//  INTÉGRATION :
//  1. Ajouter dans index.html, avant </body> :
//       <script src="js/notifications.js"></script>
//  2. Le script s'initialise automatiquement au chargement.
// ============================================================

// ------ Configuration ----------------------------------------
const NOTIF_API_URL    = 'http://localhost/modulecommunity/Controller/NotificationController.php';
const NOTIF_USER_ID    = 1;          // ← remplacer par l'ID de session PHP
const NOTIF_POLL_MS    = 30000;      // polling toutes les 30 secondes
// -------------------------------------------------------------

(function () {
    'use strict';

    // ── État local ────────────────────────────────────────────
    let _notifications  = [];
    let _unreadCount    = 0;
    let _panelOpen      = false;
    let _pollInterval   = null;

    // =========================================================
    //  1. INJECTION DU HTML dans le topbar
    // =========================================================
    function injectBell() {
        const topbarUser = document.querySelector('.topbar-user');
        if (!topbarUser) return;

        const wrapper = document.createElement('div');
        wrapper.id        = 'notif-wrapper';
        wrapper.innerHTML = `
            <button id="notif-bell" aria-label="Notifications" title="Notifications">
                <svg id="notif-bell-icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span id="notif-badge" class="notif-badge notif-badge-hidden">0</span>
            </button>

            <!-- Panneau déroulant -->
            <div id="notif-panel" class="notif-panel notif-panel-hidden" role="dialog" aria-label="Panneau de notifications">
                <div class="notif-panel-header">
                    <span class="notif-panel-title">Notifications</span>
                    <button id="notif-read-all" class="notif-read-all-btn" title="Tout marquer comme lu">✓ Tout lire</button>
                </div>
                <div id="notif-list" class="notif-list">
                    <div class="notif-empty">Chargement…</div>
                </div>
            </div>
        `;

        // Insérer AVANT l'avatar utilisateur
        topbarUser.parentNode.insertBefore(wrapper, topbarUser);

        // Injecter le CSS
        injectCSS();

        // Events
        document.getElementById('notif-bell').addEventListener('click', togglePanel);
        document.getElementById('notif-read-all').addEventListener('click', markAllRead);
        document.addEventListener('click', closeOnOutsideClick);
    }

    // =========================================================
    //  2. CSS — injecté dynamiquement pour rester autonome
    // =========================================================
    function injectCSS() {
        const style = document.createElement('style');
        style.textContent = `
            /* ── Wrapper positionné ── */
            #notif-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }

            /* ── Bouton cloche ── */
            #notif-bell {
                background: none;
                border: none;
                cursor: pointer;
                padding: 8px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.18s ease;
                color: var(--texte-moyen);
                position: relative;
            }
            #notif-bell:hover { background: var(--creme-fonce); color: var(--brun-chaud); }
            #notif-bell-icon  { width: 22px; height: 22px; }

            /* Badge rouge (compteur) */
            .notif-badge {
                position: absolute;
                top: 4px; right: 4px;
                min-width: 17px; height: 17px;
                background: var(--alerte-rouge);
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                border-radius: 999px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 4px;
                border: 2px solid var(--blanc);
                pointer-events: none;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            .notif-badge-hidden { opacity: 0; transform: scale(0.4); }

            /* Animation "pulse" quand nouvelle notif */
            @keyframes notif-pulse {
                0%   { transform: scale(1); }
                30%  { transform: scale(1.35); }
                60%  { transform: scale(0.95); }
                100% { transform: scale(1); }
            }
            .notif-badge-pulse { animation: notif-pulse 0.45s ease; }

            /* ── Panneau ── */
            .notif-panel {
                position: absolute;
                top: calc(100% + 10px);
                right: 0;
                width: 340px;
                background: var(--blanc);
                border: 1.5px solid var(--brun-pale);
                border-radius: 14px;
                box-shadow: 0 8px 32px rgba(78,44,14,0.16);
                z-index: 500;
                overflow: hidden;
                transform-origin: top right;
                transition: opacity 0.18s ease, transform 0.18s ease;
            }
            .notif-panel-hidden {
                opacity: 0;
                transform: scale(0.94) translateY(-8px);
                pointer-events: none;
            }

            /* En-tête du panneau */
            .notif-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 16px 10px;
                border-bottom: 1px solid var(--creme-fonce);
            }
            .notif-panel-title { font-weight: 700; font-size: 14px; color: var(--brun-fonce); }
            .notif-read-all-btn {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 12px;
                color: var(--brun-chaud);
                padding: 4px 8px;
                border-radius: 6px;
                transition: background 0.15s;
            }
            .notif-read-all-btn:hover { background: var(--creme-fonce); }

            /* Liste */
            .notif-list {
                max-height: 380px;
                overflow-y: auto;
                padding: 6px 0;
            }
            .notif-list::-webkit-scrollbar { width: 4px; }
            .notif-list::-webkit-scrollbar-thumb { background: var(--brun-pale); border-radius: 4px; }

            /* Item de notification */
            .notif-item {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                padding: 10px 16px;
                cursor: pointer;
                transition: background 0.15s;
                border-left: 3px solid transparent;
            }
            .notif-item:hover  { background: var(--creme); }
            .notif-item.unread {
                background: #fff8f2;
                border-left-color: var(--brun-chaud);
            }
            .notif-item.unread:hover { background: #fdf0e4; }

            .notif-item-icon {
                font-size: 18px;
                flex-shrink: 0;
                margin-top: 1px;
            }
            .notif-item-body { flex: 1; min-width: 0; }
            .notif-item-msg  {
                font-size: 13px;
                color: var(--texte-sombre);
                line-height: 1.4;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .notif-item-time {
                font-size: 11px;
                color: var(--texte-leger);
                margin-top: 2px;
            }
            .notif-item-dot {
                width: 7px; height: 7px;
                border-radius: 50%;
                background: var(--brun-chaud);
                flex-shrink: 0;
                margin-top: 6px;
                transition: opacity 0.2s;
            }
            .notif-item:not(.unread) .notif-item-dot { opacity: 0; }

            /* Vide / chargement */
            .notif-empty {
                text-align: center;
                padding: 30px 20px;
                color: var(--texte-leger);
                font-size: 13px;
            }

            /* Toast de confirmation */
            #notif-toast {
                position: fixed;
                bottom: 28px; right: 28px;
                background: var(--brun-fonce);
                color: var(--creme);
                font-size: 13px;
                padding: 10px 18px;
                border-radius: 10px;
                box-shadow: 0 4px 18px rgba(78,44,14,0.25);
                z-index: 9999;
                opacity: 0;
                transform: translateY(10px);
                transition: opacity 0.22s ease, transform 0.22s ease;
                pointer-events: none;
            }
            #notif-toast.show {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);

        // Toast DOM
        const toast = document.createElement('div');
        toast.id = 'notif-toast';
        document.body.appendChild(toast);
    }

    // =========================================================
    //  3. POLLING — vérification légère toutes les 30 s
    // =========================================================
    function startPolling() {
        fetchUnreadCount();               // vérification immédiate
        _pollInterval = setInterval(fetchUnreadCount, NOTIF_POLL_MS);
    }

    async function fetchUnreadCount() {
        try {
            const res  = await fetch(`${NOTIF_API_URL}?user_id=${NOTIF_USER_ID}&count=1`);
            const data = await res.json();
            if (data.success) {
                const prev = _unreadCount;
                _unreadCount = data.unread;
                updateBadge(prev < _unreadCount); // pulse si nouvelles notifs
            }
        } catch (e) {
            // Silencieux — pas de console.error pour ne pas parasiter
        }
    }

    // =========================================================
    //  4. CHARGEMENT de la liste complète (au clic sur la cloche)
    // =========================================================
    async function fetchNotifications() {
        const list = document.getElementById('notif-list');
        list.innerHTML = '<div class="notif-empty">Chargement…</div>';

        try {
            const res  = await fetch(`${NOTIF_API_URL}?user_id=${NOTIF_USER_ID}`);
            const data = await res.json();

            if (!data.success) {
                list.innerHTML = '<div class="notif-empty">Erreur de chargement</div>';
                return;
            }

            _notifications = data.notifications || [];
            _unreadCount   = data.unread;
            updateBadge(false);
            renderList();

        } catch (e) {
            list.innerHTML = '<div class="notif-empty">Erreur réseau</div>';
        }
    }

    // =========================================================
    //  5. RENDU de la liste
    // =========================================================
    function renderList() {
        const list = document.getElementById('notif-list');

        if (_notifications.length === 0) {
            list.innerHTML = '<div class="notif-empty">🎉 Aucune notification</div>';
            return;
        }

        list.innerHTML = _notifications.map(n => `
            <div class="notif-item ${n.is_read === '0' || n.is_read === 0 ? 'unread' : ''}"
                 data-id="${n.id}"
                 data-type="${n.type}"
                 data-review-id="${n.review_id || ''}"
                 data-response-id="${n.response_id || ''}">
                <div class="notif-item-icon">${escapeHtml(n.icon || '🔔')}</div>
                <div class="notif-item-body">
                    <div class="notif-item-msg">${escapeHtml(n.message)}</div>
                    <div class="notif-item-time">${escapeHtml(n.time_ago)}</div>
                </div>
                <div class="notif-item-dot"></div>
            </div>
        `).join('');

        // Clic sur un item → marquer comme lu + scroll vers l'élément
        list.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', () => handleItemClick(el));
        });
    }

    // =========================================================
    //  6. ACTIONS
    // =========================================================
    async function handleItemClick(el) {
        const id = parseInt(el.dataset.id);

        // Optimistic UI : retirer la classe unread immédiatement
        el.classList.remove('unread');
        _unreadCount = Math.max(0, _unreadCount - 1);
        updateBadge(false);

        // Appel API
        try {
            await fetch(NOTIF_API_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ action: 'read', id, user_id: NOTIF_USER_ID }),
            });
        } catch (e) { /* silencieux */ }

        // Scroll vers l'avis ou la réponse dans la page
        closePanel();
        scrollToLinkedElement(el.dataset.reviewId, el.dataset.responseId);
    }

    async function markAllRead() {
        try {
            await fetch(NOTIF_API_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ action: 'read_all', user_id: NOTIF_USER_ID }),
            });
        } catch (e) { /* silencieux */ }

        // Optimistic UI
        _unreadCount = 0;
        updateBadge(false);
        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
        showToast('Toutes les notifications ont été lues');
    }

    // =========================================================
    //  7. SCROLL vers l'élément cible dans la page
    // =========================================================
    function scrollToLinkedElement(reviewId, responseId) {
        const targetId = responseId
            ? `response-${responseId}`
            : reviewId ? `review-${reviewId}` : null;

        if (!targetId) return;

        setTimeout(() => {
            const el = document.getElementById(targetId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.style.transition = 'box-shadow 0.3s ease';
                el.style.boxShadow  = '0 0 0 2px var(--brun-chaud)';
                setTimeout(() => { el.style.boxShadow = ''; }, 1800);
            }
        }, 250);
    }

    // =========================================================
    //  8. UI HELPERS
    // =========================================================
    function togglePanel() {
        _panelOpen ? closePanel() : openPanel();
    }

    function openPanel() {
        _panelOpen = true;
        document.getElementById('notif-panel').classList.remove('notif-panel-hidden');
        fetchNotifications();
    }

    function closePanel() {
        _panelOpen = false;
        document.getElementById('notif-panel').classList.add('notif-panel-hidden');
    }

    function closeOnOutsideClick(e) {
        const wrapper = document.getElementById('notif-wrapper');
        if (_panelOpen && wrapper && !wrapper.contains(e.target)) {
            closePanel();
        }
    }

    function updateBadge(pulse) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;

        badge.textContent = _unreadCount > 99 ? '99+' : _unreadCount;

        if (_unreadCount > 0) {
            badge.classList.remove('notif-badge-hidden');
            if (pulse) {
                badge.classList.remove('notif-badge-pulse');
                void badge.offsetWidth; // force reflow pour relancer l'animation
                badge.classList.add('notif-badge-pulse');
            }
        } else {
            badge.classList.add('notif-badge-hidden');
        }
    }

    function showToast(msg) {
        const toast = document.getElementById('notif-toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2800);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // =========================================================
    //  9. INITIALISATION au chargement du DOM
    // =========================================================
    function init() {
        injectBell();
        startPolling();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

// ============================================================
//  FONCTIONS EXPOSÉES — à appeler depuis main.js
//  après un POST réponse ou une réaction
// ============================================================

/**
 * Déclenche manuellement un refresh du compteur de notifications.
 * Appeler depuis main.js après chaque action qui crée une notif.
 *
 * Exemple dans main.js, dans la fonction qui soumet une réponse :
 *   notifRefresh();
 */
function notifRefresh() {
    // Le module est en IIFE, on passe par un event custom
    document.dispatchEvent(new CustomEvent('notif:refresh'));
}

// Écoute de l'event depuis l'IIFE
document.addEventListener('notif:refresh', () => {
    const badge  = document.getElementById('notif-badge');
    if (!badge) return;
    // Re-fetch le compteur
    fetch(`${NOTIF_API_URL}?user_id=${NOTIF_USER_ID}&count=1`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const prev = parseInt(badge.textContent) || 0;
            const next = data.unread;
            badge.textContent = next > 99 ? '99+' : next;
            if (next > 0) {
                badge.classList.remove('notif-badge-hidden');
                if (next > prev) {
                    badge.classList.remove('notif-badge-pulse');
                    void badge.offsetWidth;
                    badge.classList.add('notif-badge-pulse');
                }
            } else {
                badge.classList.add('notif-badge-hidden');
            }
        })
        .catch(() => {});
});