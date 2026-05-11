/**
 * FoodWise — Vues privées par avis (localStorage)
 * Chaque carte affiche le nombre de jours où cet avis a été « vu » (3 s visibles), max. 1 / jour.
 * Aucun backend.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'foodwise_private_review_views_per_review_v1';
    var HOLD_MS = 3000;
    var MIN_INTERSECTION_RATIO = 0.38;

    var observer = null;
    var timersByReviewId = new Map();

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function todayISO() {
        var d = new Date();
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    /** @returns {{ reviews: Object.<string, { c: number, d: string }> }} */
    function loadState() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return { reviews: {} };
            var parsed = JSON.parse(raw);
            if (!parsed || typeof parsed.reviews !== 'object' || parsed.reviews === null) {
                return { reviews: {} };
            }
            return { reviews: parsed.reviews };
        } catch (e) {
            return { reviews: {} };
        }
    }

    function saveState(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ reviews: state.reviews }));
        } catch (e) { /* quota */ }
    }

    function getEntry(state, reviewId) {
        var key = String(reviewId);
        var e = state.reviews[key];
        if (!e || typeof e.c !== 'number') {
            return { c: 0, d: null };
        }
        return { c: e.c, d: e.d || null };
    }

    function countedToday(state, reviewId) {
        return getEntry(state, reviewId).d === todayISO();
    }

    function setEntry(state, reviewId, entry) {
        state.reviews[String(reviewId)] = { c: entry.c, d: entry.d };
    }

    function updateCountDisplay(reviewId, count) {
        var el = document.getElementById('review-private-views-' + reviewId);
        if (el) el.textContent = String(count);
    }

    function syncAllCardsFromState(container, state) {
        var cards = container.querySelectorAll('.review-card[data-review-id]');
        cards.forEach(function (card) {
            var id = card.getAttribute('data-review-id');
            if (!id) return;
            var e = getEntry(state, id);
            updateCountDisplay(id, e.c);
        });
    }

    function pulseCounterForReview(reviewId) {
        var el = document.getElementById('review-private-views-' + reviewId);
        if (!el) return;
        el.classList.remove('review-private-views__count--pulse');
        void el.offsetWidth;
        el.classList.add('review-private-views__count--pulse');
        var onEnd = function (ev) {
            if (ev.animationName !== 'reviewPrivateViewsCountPulse') return;
            el.removeEventListener('animationend', onEnd);
            el.classList.remove('review-private-views__count--pulse');
        };
        el.addEventListener('animationend', onEnd);
    }

    function clearHoldTimer(reviewId) {
        var t = timersByReviewId.get(reviewId);
        if (t) {
            clearTimeout(t);
            timersByReviewId.delete(reviewId);
        }
    }

    function clearAllTimers() {
        timersByReviewId.forEach(function (t) { clearTimeout(t); });
        timersByReviewId.clear();
    }

    function applyInitialCardClasses(card, state) {
        var id = card.getAttribute('data-review-id');
        if (!id) return;
        if (countedToday(state, id)) {
            card.classList.add('review-card--view-counted');
        }
    }

    function onHoldComplete(card) {
        var reviewId = card.getAttribute('data-review-id');
        if (!reviewId) return;

        var state = loadState();
        if (countedToday(state, reviewId)) {
            card.classList.remove('review-card--viewing');
            return;
        }

        var cur = getEntry(state, reviewId);
        cur.c += 1;
        cur.d = todayISO();
        setEntry(state, reviewId, cur);
        saveState(state);

        card.classList.remove('review-card--viewing');
        card.classList.add('review-card--view-counted');
        updateCountDisplay(reviewId, cur.c);
        pulseCounterForReview(reviewId);
    }

    function handleIntersection(entry) {
        var card = entry.target;
        var reviewId = card.getAttribute('data-review-id');
        if (!reviewId) return;

        var state = loadState();
        if (countedToday(state, reviewId)) {
            clearHoldTimer(reviewId);
            card.classList.remove('review-card--viewing');
            return;
        }

        var visibleEnough = entry.isIntersecting && entry.intersectionRatio >= MIN_INTERSECTION_RATIO;

        if (!visibleEnough) {
            clearHoldTimer(reviewId);
            card.classList.remove('review-card--viewing');
            return;
        }

        if (timersByReviewId.has(reviewId)) return;

        card.classList.add('review-card--viewing');

        var tid = setTimeout(function () {
            timersByReviewId.delete(reviewId);
            onHoldComplete(card);
        }, HOLD_MS);
        timersByReviewId.set(reviewId, tid);
    }

    /**
     * @param {HTMLElement} [root]
     */
    function initPrivateReviewViews(root) {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
        clearAllTimers();

        var container = root || document.getElementById('reviewsContainer');
        if (!container) return;

        var state = loadState();
        syncAllCardsFromState(container, state);

        var cards = container.querySelectorAll('.review-card[data-review-id]');
        if (!cards.length) return;

        cards.forEach(function (card) {
            applyInitialCardClasses(card, state);
        });

        observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(handleIntersection);
            },
            {
                root: null,
                rootMargin: '0px',
                threshold: [0, 0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4, 0.45, 0.5, 0.55, 0.6, 0.65, 0.7, 0.75, 0.8, 0.85, 0.9, 0.95, 1]
            }
        );

        cards.forEach(function (card) {
            observer.observe(card);
        });
    }

    window.initPrivateReviewViews = initPrivateReviewViews;
})();
