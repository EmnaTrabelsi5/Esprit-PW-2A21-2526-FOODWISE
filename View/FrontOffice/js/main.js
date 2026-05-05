// Configuration
const API_URL = 'http://localhost/modulecommunity/Controller/ReviewController.php';
const RESPONSE_API_URL = 'http://localhost/modulecommunity/Controller/ResponseController.php';
const currentUserId = 2;
let currentReviewSort = 'date'; // date | likes | responses

// Filtres "recherche avancée" (envoyés au backend)
let advSearch = {
    q: '',
    since: 'all',
    min_rating: 0,
    min_responses: 0,
    min_likes: 0,
};

// ==================== DICTIONNAIRE DES MOTS INTERDITS ====================
const motsInterdits = [
    'connard', 'con', 'merde', 'putain', 'salope', 'enculé', 'batard', 'bâtard',
    'stupide', 'idiot', 'nul', 'nulle', 'mort', 'tuer', 'suicide', 'violence',
    'insulte', 'gros', 'grosse', 'laid', 'moche', 'dégueulasse', 'dégueu',
    'chiant', 'chiante', 'pété', 'pue', 'pourri', 'caca', 'pipi',
    'fuck', 'shit', 'bitch', 'damn', 'hate', 'raciste', 'nazi'
];

// ==================== FONCTIONS DE VALIDATION ====================

function verifierMotsInterdits(texte) {
    if(!texte) return { valide: true, motInterdit: null };
    const texteMinuscule = texte.toLowerCase();
    for(let i = 0; i < motsInterdits.length; i++) {
        const mot = motsInterdits[i];
        if(texteMinuscule.includes(mot)) {
            return { valide: false, motInterdit: mot };
        }
    }
    return { valide: true, motInterdit: null };
}

function verifierCaracteresRepetitifs(texte) {
    if(!texte) return { valide: true, message: null };
    const regex = /(.)\1{3,}/;
    if(regex.test(texte)) {
        return { valide: false, message: "Évitez les répétitions excessives de caractères" };
    }
    const regexPoints = /[!?]{3,}/;
    if(regexPoints.test(texte)) {
        return { valide: false, message: "Évitez de multiplier les points d'exclamation ou d'interrogation" };
    }
    return { valide: true, message: null };
}

function verifierMajuscules(texte) {
    if(!texte || texte.length < 20) return { valide: true, message: null };
    let compteurMajuscules = 0;
    let compteurTotal = 0;
    for(let i = 0; i < texte.length; i++) {
        if(texte[i] !== ' ') {
            compteurTotal++;
            if(texte[i] === texte[i].toUpperCase() && texte[i] !== texte[i].toLowerCase()) {
                compteurMajuscules++;
            }
        }
    }
    const pourcentageMajuscules = (compteurMajuscules / compteurTotal) * 100;
    if(pourcentageMajuscules > 70) {
        return { valide: false, message: "Évitez d'écrire entièrement en majuscules" };
    }
    return { valide: true, message: null };
}

function validerAvis(recipe_id, title, content, rating) {
    const errors = [];
    
    if(!recipe_id) errors.push("Veuillez sélectionner une recette");
    
    if(title && title.length > 100) errors.push("Le titre ne peut pas dépasser 100 caractères");
    if(title) {
        const motsCheck = verifierMotsInterdits(title);
        if(!motsCheck.valide) errors.push(`Le titre contient le mot "${motsCheck.motInterdit}" qui est interdit`);
    }
    
    if(!content || content.trim().length < 5) errors.push("L'avis doit contenir au moins 5 caractères");
    if(content && content.length > 500) errors.push("L'avis ne peut pas dépasser 500 caractères");
    if(content) {
        const motsCheck = verifierMotsInterdits(content);
        if(!motsCheck.valide) errors.push(`L'avis contient le mot "${motsCheck.motInterdit}" qui est interdit`);
        const repetitifCheck = verifierCaracteresRepetitifs(content);
        if(!repetitifCheck.valide) errors.push(repetitifCheck.message);
        const majusculesCheck = verifierMajuscules(content);
        if(!majusculesCheck.valide) errors.push(majusculesCheck.message);
    }
    
    if(!rating || rating < 1 || rating > 5) errors.push("Veuillez attribuer une note entre 1 et 5 étoiles");
    
    return { valide: errors.length === 0, errors: errors };
}

function validerReponse(content) {
    const errors = [];
    
    if(!content || content.trim().length < 2) errors.push("La réponse doit contenir au moins 2 caractères");
    if(content && content.length > 300) errors.push("La réponse ne peut pas dépasser 300 caractères");
    if(content) {
        const motsCheck = verifierMotsInterdits(content);
        if(!motsCheck.valide) errors.push(`La réponse contient le mot "${motsCheck.motInterdit}" qui est interdit`);
        const repetitifCheck = verifierCaracteresRepetitifs(content);
        if(!repetitifCheck.valide) errors.push(repetitifCheck.message);
    }
    
    return { valide: errors.length === 0, errors: errors };
}

function afficherErreursFormulaire(errors) {
    const errorContainer = document.getElementById('formErrors');
    if(errorContainer) {
        let html = '<div style="background: #FADBD8; border-left: 4px solid #C0392B; padding: 12px; border-radius: 8px; margin-bottom: 15px;">';
        html += '<strong style="color: #C0392B;">❌ Veuillez corriger les erreurs :</strong><ul style="margin: 8px 0 0 20px; color: #922B21;">';
        for(let i = 0; i < errors.length; i++) {
            html += '<li>' + errors[i] + '</li>';
        }
        html += '</ul></div>';
        errorContainer.innerHTML = html;
        errorContainer.style.display = 'block';
    }
}

function effacerErreursFormulaire() {
    const errorContainer = document.getElementById('formErrors');
    if(errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }
}

function afficherErreursReponse(errors) {
    const errorContainer = document.getElementById('reponseErrors');
    if(errorContainer) {
        let html = '<div style="background: #FADBD8; border-left: 4px solid #C0392B; padding: 12px; border-radius: 8px; margin-bottom: 15px;">';
        html += '<strong style="color: #C0392B;">❌ Veuillez corriger les erreurs :</strong><ul style="margin: 8px 0 0 20px; color: #922B21;">';
        for(let i = 0; i < errors.length; i++) {
            html += '<li>' + errors[i] + '</li>';
        }
        html += '</ul></div>';
        errorContainer.innerHTML = html;
        errorContainer.style.display = 'block';
    }
}

function effacerErreursReponse() {
    const errorContainer = document.getElementById('reponseErrors');
    if(errorContainer) {
        errorContainer.innerHTML = '';
        errorContainer.style.display = 'none';
    }
}

// ==================== CHARGEMENT AVIS ====================
function buildReviewsUrl() {
    const params = new URLSearchParams();
    params.set('sort', currentReviewSort);

    if (advSearch.q) params.set('q', advSearch.q);
    if (advSearch.since && advSearch.since !== 'all') params.set('since', advSearch.since);
    if (advSearch.min_rating && advSearch.min_rating > 0) {
        params.set('min_rating', String(advSearch.min_rating));
    }
    if (advSearch.min_responses && advSearch.min_responses > 0) {
        params.set('min_responses', String(advSearch.min_responses));
    }
    if (advSearch.min_likes && advSearch.min_likes > 0) {
        params.set('min_likes', String(advSearch.min_likes));
    }

    return `${API_URL}?${params.toString()}`;
}

// Liste complète (sans filtres) — utile pour ouvrir le modal d'édition même si des filtres sont actifs
function buildAllReviewsUrl() {
    const params = new URLSearchParams();
    params.set('sort', 'date');
    params.set('since', 'all');
    params.set('min_rating', '0');
    params.set('min_responses', '0');
    params.set('min_likes', '0');
    return `${API_URL}?${params.toString()}`;
}

function readAdvancedSearchFromDom() {
    const qEl = document.getElementById('advSearchText');
    const sinceEl = document.getElementById('advSearchSince');
    const ratingEl = document.getElementById('advSearchMinRating');
    const respEl = document.getElementById('advSearchMinResponses');
    const likesEl = document.getElementById('advSearchMinLikes');

    advSearch.q = qEl ? String(qEl.value || '').trim() : '';
    advSearch.since = sinceEl ? String(sinceEl.value || 'all') : 'all';
    advSearch.min_rating = ratingEl ? parseInt(ratingEl.value || '0', 10) : 0;
    advSearch.min_responses = respEl ? parseInt(respEl.value || '0', 10) : 0;
    advSearch.min_likes = likesEl ? parseInt(likesEl.value || '0', 10) : 0;
}

function resetAdvancedSearchDom() {
    const qEl = document.getElementById('advSearchText');
    const sinceEl = document.getElementById('advSearchSince');
    const ratingEl = document.getElementById('advSearchMinRating');
    const respEl = document.getElementById('advSearchMinResponses');
    const likesEl = document.getElementById('advSearchMinLikes');

    if (qEl) qEl.value = '';
    if (sinceEl) sinceEl.value = 'all';
    if (ratingEl) ratingEl.value = '0';
    if (respEl) respEl.value = '0';
    if (likesEl) likesEl.value = '0';

    advSearch = { q: '', since: 'all', min_rating: 0, min_responses: 0, min_likes: 0 };
}

function loadReviews() {
    const url = buildReviewsUrl();
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                displayReviewsCards(data.data);
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function displayReviewsCards(reviews) {
    const container = document.getElementById('reviewsContainer');
    if(!container) return;
    
    if(reviews.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px;">📭 Aucun avis pour le moment</div>';
        if (typeof initPrivateReviewViews === 'function') initPrivateReviewViews(container);
        return;
    }
    
    let html = '<div class="reviews-grid">';
    
    for(let i = 0; i < reviews.length; i++) {
        const review = reviews[i];
        const isOwner = (review.user_id == currentUserId);
        
        html += `
            <div class="review-card" data-review-id="${review.id}">
                <div class="review-header">
                    <div class="recipe-name">🍽️ ${escapeHtml(review.recipe_name)}</div>
                    <div class="rating">${'⭐'.repeat(review.rating)}</div>
                </div>
                <div class="review-title" style="font-weight: 700; color: var(--brun-chaud); margin: 8px 0;">
                    📌 "${escapeHtml(review.title || 'Avis')}"
                </div>
                <div class="review-meta">
                    <span>📅 ${formatDate(review.created_at)}</span>
                    <span>👤 ${escapeHtml(review.username || 'Utilisateur ' + review.user_id)}</span>
                </div>
                <div class="review-content">"${escapeHtml(review.content)}"</div>
                
                <div class="review-stats">
                    <button class="like-review-btn" onclick="likeReview(${review.id})">
                        ❤️ <span id="review-likes-count-${review.id}">${review.likes_count || 0}</span>
                    </button>
                    <span>💬 <span id="response-count-${review.id}">0</span> réponse(s)</span>
                    <span class="review-private-views" title="Vos vues sur cet avis (cet appareil) : +1 par jour après 3 secondes d’affichage à l’écran.">
                        👁 <span id="review-private-views-${review.id}" class="review-private-views__count">0</span> <span class="review-private-views__label">mes vues</span>
                    </span>
                </div>
                
                <div class="review-actions">
                    <button class="btn-repondre" onclick="openReponseModal(${review.id}, '${escapeHtml(review.content)}', '${escapeHtml(review.recipe_name)}')">💬 Répondre</button>
                    ${isOwner ? `
                        <button class="btn-modifier" onclick="editReview(${review.id})">✏️ Modifier</button>
                        <button class="btn-supprimer" onclick="deleteReview(${review.id})">🗑️ Supprimer</button>
                    ` : ''}
                </div>
                <div class="responses-section">
                    <div class="responses-title">💬 Réponses</div>
                    <div id="responses-list-${review.id}">Chargement...</div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
    
    for(let i = 0; i < reviews.length; i++) {
        loadResponsesForReview(reviews[i].id);
    }

    if (typeof initPrivateReviewViews === 'function') {
        initPrivateReviewViews(container);
    }
}

function loadResponsesForReview(reviewId) {
    fetch(`${RESPONSE_API_URL}?review_id=${reviewId}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                displayResponsesForReview(reviewId, data.data);
                const count = data.data.length;
                const countSpan = document.getElementById(`response-count-${reviewId}`);
                if(countSpan) countSpan.innerHTML = count;
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function displayResponsesForReview(reviewId, responses) {
    const container = document.getElementById(`responses-list-${reviewId}`);
    if(!container) return;
    
    if(!responses || responses.length === 0) {
        container.innerHTML = '<div style="font-size:12px; color:#aaa; padding:8px 0;">Aucune réponse pour le moment</div>';
        return;
    }
    
    // Construire un arbre de réponses (parent -> enfants)
    const responsesMap = {};
    const topLevel = [];
    
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        responsesMap[r.id] = { ...r, children: [] };
    }
    
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        if(r.parent_id && responsesMap[r.parent_id]) {
            responsesMap[r.parent_id].children.push(responsesMap[r.id]);
        } else {
            topLevel.push(responsesMap[r.id]);
        }
    }
    
    // Fonction récursive pour afficher une réponse et ses enfants
    function renderResponse(response, level = 0) {
        const isOwner = (response.user_id == currentUserId);
        const marginLeft = level * 35; // Décalage vers la droite
        const borderColor = level === 0 ? 'none' : 'var(--brun-pale)';
        const bgColor = level === 0 ? 'var(--creme-fonce)' : 'var(--creme)';
        
        let html = `
            <div class="response-card" style="margin-left: ${marginLeft}px; background: ${bgColor}; border-left: ${level > 0 ? '3px solid ' + borderColor : 'none'}; margin-bottom: 12px;">
                <div class="response-header">
                    <div class="response-author">💬 ${escapeHtml(response.username || 'Utilisateur ' + response.user_id)}</div>
                    <div class="response-date">${formatDate(response.created_at)}</div>
                </div>
                <div class="response-content">${highlightMentions(response.content)}</div>
                <div class="response-footer">
                    <div class="response-reactions" id="reactions-${response.id}">
                        <button class="reaction-btn like-btn" onclick="reactToResponse(${response.id}, 'like')">
                            👍 <span id="response-likes-count-${response.id}">0</span>
                        </button>
                        <button class="reaction-btn dislike-btn" onclick="reactToResponse(${response.id}, 'dislike')">
                            👎 <span id="response-dislikes-count-${response.id}">0</span>
                        </button>
                    </div>
                    <div class="response-actions">
                        <button class="btn-repondre-reponse" onclick="openReponseToResponseModal(${response.id}, ${response.review_id}, '${escapeHtml(response.content)}')">💬 Répondre</button>
                        ${isOwner ? `
                            <button class="btn-modifier" onclick="editReponse(${response.id}, '${escapeHtml(response.content)}')">✏️ Modifier</button>
                            <button class="btn-supprimer" onclick="deleteReponse(${response.id})">🗑️ Supprimer</button>
                        ` : ''}
                    </div>
                </div>
        `;
        
        // Afficher les enfants (réponses à cette réponse)
        if(response.children && response.children.length > 0) {
            html += `<div class="responses-children">`;
            for(let i = 0; i < response.children.length; i++) {
                html += renderResponse(response.children[i], level + 1);
            }
            html += `</div>`;
        }
        
        html += `</div>`;
        return html;
    }
    
    let html = '';
    for(let i = 0; i < topLevel.length; i++) {
        html += renderResponse(topLevel[i], 0);
    }
    
    container.innerHTML = html;
    
    // Charger les réactions pour chaque réponse
    for(let i = 0; i < responses.length; i++) {
        loadReactionsForResponse(responses[i].id);
    }
}

// ==================== RÉPONDRE À UNE RÉPONSE ====================

function openReponseToResponseModal(parentResponseId, reviewId, parentContent) {
    document.getElementById('reponseParentId').value = parentResponseId;
    document.getElementById('reponseReviewId').value = reviewId;
    document.getElementById('reponseOriginalAvis').value = "En réponse à : " + parentContent.substring(0, 50);
    document.getElementById('reponseModal').style.display = 'flex';
    effacerErreursReponse();
    document.getElementById('reponseContent').value = '';
    updateReponseCharCount();
}

// ==================== AJOUTER RÉPONSE ====================

function addReponse(e) {
    e.preventDefault();
    const review_id = document.getElementById('reponseReviewId').value;
    const parent_id = document.getElementById('reponseParentId').value;
    const content = document.getElementById('reponseContent').value;
    
    effacerErreursReponse();
    const validation = validerReponse(content);
    
    if(!validation.valide) {
        afficherErreursReponse(validation.errors);
        return;
    }
    
    const body = {
        review_id: parseInt(review_id),
        user_id: currentUserId,
        content: content
    };
    
    if(parent_id && parent_id !== "") {
        body.parent_id = parseInt(parent_id);
    }
    
    fetch(RESPONSE_API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✅ Réponse ajoutée avec succès !', 'success');
            closeReponseModal();
            loadReviews();
            loadResponses();
        } else {
            afficherErreursReponse(data.errors || ['Erreur lors de l\'ajout']);
        }
    });
}

// ==================== CHARGEMENT RÉPONSES ====================

function loadResponses() {
    fetch(`${RESPONSE_API_URL}?all=true`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const userResponses = data.data.filter(r => r.user_id == currentUserId);
                displayResponses(userResponses);
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function displayResponses(responses) {
    const tbody = document.getElementById('responsesList');
    if(!tbody) return;
    
    // Vider le tableau
    tbody.innerHTML = '';
    
    if(!responses || responses.length === 0) {
        const row = tbody.insertRow();
        const cell = row.insertCell(0);
        cell.colSpan = 4;
        cell.style.textAlign = 'center';
        cell.innerHTML = 'Aucune réponse pour le moment';
        return;
    }
    
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        const row = tbody.insertRow();
        
        // Cellule Date
        const cellDate = row.insertCell(0);
        cellDate.innerHTML = formatDate(r.created_at);
        
        // Cellule Avis concerné
        const cellAvis = row.insertCell(1);
        cellAvis.innerHTML = 'Avis #' + r.review_id;
        
        // Cellule Réponse
        const cellReponse = row.insertCell(2);
        let reponseText = r.content;
        if(reponseText.length > 50) {
            reponseText = reponseText.substring(0, 50) + '...';
        }
        cellReponse.innerHTML = escapeHtml(reponseText);
        
        // Cellule Actions
        const cellActions = row.insertCell(3);
        cellActions.innerHTML = `
            <button onclick="editReponse(${r.id}, '${escapeHtml(r.content)}')" class="btn btn-sm btn-outline">✏️ Modifier</button>
            <button onclick="deleteReponse(${r.id})" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
        `;
    }
}

// ==================== SYSTÈME D'ÉTOILES ====================

function setupStarRating() {
    const stars = document.querySelectorAll('#starRating span');
    const ratingInput = document.getElementById('ratingValue');
    
    if(stars.length === 0) return;
    
    function updateStars(rating) {
        for(let i = 0; i < stars.length; i++) {
            if(i < rating) {
                stars[i].innerHTML = '★';
                stars[i].style.color = '#FFD700';
            } else {
                stars[i].innerHTML = '☆';
                stars[i].style.color = '#ccc';
            }
        }
    }
    
    for(let i = 0; i < stars.length; i++) {
        const star = stars[i];
        const value = parseInt(star.getAttribute('data-rating'));
        
        star.onmouseover = function() {
            updateStars(value);
        };
        
        star.onmouseout = function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            updateStars(currentRating);
        };
        
        star.onclick = function() {
            ratingInput.value = value;
            updateStars(value);
        };
    }
}

// ==================== AJOUTER AVIS ====================

function addReview(e) {
    e.preventDefault();
    const select = document.getElementById('recipeSelect');
    const recipe_name = select.options[select.selectedIndex].text;
    const recipe_id = select.value;
    const title = document.getElementById('reviewTitle').value;
    const content = document.getElementById('reviewText').value;
    const rating = document.getElementById('ratingValue').value;
    
    effacerErreursFormulaire();
    const validation = validerAvis(recipe_id, title, content, rating);
    
    if(!validation.valide) {
        afficherErreursFormulaire(validation.errors);
        return;
    }
    
    fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({recipe_name, title, content, rating: parseInt(rating)})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✅ Avis ajouté avec succès !', 'success');
            toggleForm(false);
            loadReviews();
            document.getElementById('reviewForm').reset();
            document.getElementById('reviewTitle').value = '';
            document.getElementById('ratingValue').value = 0;
            const stars = document.querySelectorAll('#starRating span');
            for(let s of stars) {
                s.innerHTML = '☆';
                s.style.color = '#ccc';
            }
        } else {
            afficherErreursFormulaire(data.errors);
        }
    });
}

// ==================== MODIFIER AVIS ====================

function editReview(id) {
    fetch(buildAllReviewsUrl())
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const review = data.data.find(r => r.id == id);
                if(review) {
                    document.getElementById('editReviewId').value = review.id;
                    document.getElementById('editReviewTitle').value = review.title || '';
                    document.getElementById('editReviewText').value = review.content;
                    document.getElementById('editReviewRating').value = review.rating;
                    document.getElementById('editModal').style.display = 'flex';
                }
            }
        });
}

function updateReview(e) {
    e.preventDefault();
    const id = document.getElementById('editReviewId').value;
    const title = document.getElementById('editReviewTitle').value;
    const content = document.getElementById('editReviewText').value;
    const rating = document.getElementById('editReviewRating').value;
    
    fetch(API_URL, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, title, content, rating})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✅ Avis modifié avec succès !', 'success');
            closeModal();
            loadReviews();
        } else {
            showNotification('❌ Erreur lors de la modification', 'error');
        }
    });
}

function deleteReview(id) {
    if(confirm('Supprimer cet avis ?')) {
        fetch(API_URL + '?id=' + id, {method: 'DELETE'})
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('✅ Avis supprimé !', 'success');
                    loadReviews();
                }
            });
    }
}

// ==================== RÉPONSES (CRUD) ====================

function openReponseModal(reviewId, reviewContent, recipeName) {
    document.getElementById('reponseReviewId').value = reviewId;
    document.getElementById('reponseParentId').value = '';
    document.getElementById('reponseOriginalAvis').value = recipeName + ' : ' + reviewContent;
    document.getElementById('reponseModal').style.display = 'flex';
    effacerErreursReponse();
    document.getElementById('reponseContent').value = '';
}

function editReponse(id, content) {
    document.getElementById('editReponseId').value = id;
    document.getElementById('editReponseContent').value = content;
    document.getElementById('editReponseModal').style.display = 'flex';
}

function updateReponse(e) {
    e.preventDefault();
    const id = document.getElementById('editReponseId').value;
    const content = document.getElementById('editReponseContent').value;
    
    fetch(RESPONSE_API_URL, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: parseInt(id), content: content})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✅ Réponse modifiée avec succès !', 'success');
            closeEditReponseModal();
            loadReviews();
            loadResponses();
        } else {
            showNotification('❌ Erreur lors de la modification', 'error');
        }
    });
}

function deleteReponse(id) {
    if(confirm('Supprimer cette réponse ?')) {
        fetch(RESPONSE_API_URL + '?id=' + id, {method: 'DELETE'})
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('✅ Réponse supprimée avec succès !', 'success');
                    loadReviews();
                    loadResponses();
                }
            });
    }
}

// ==================== LIKES/DISLIKES RÉPONSES ====================

// ==================== LIKES/DISLIKES SUR RÉPONSES ====================

function loadReactionsForResponse(responseId) {
    console.log("Chargement réactions pour réponse:", responseId);
    
    fetch(`${RESPONSE_API_URL}?action=reactions&response_id=${responseId}&user_id=${currentUserId}`)
        .then(response => response.json())
        .then(data => {
            console.log("Réactions reçues:", data);
            if(data.success) {
                // Mettre à jour les compteurs
                const likesSpan = document.getElementById(`response-likes-count-${responseId}`);
                const dislikesSpan = document.getElementById(`response-dislikes-count-${responseId}`);
                
                if(likesSpan) likesSpan.textContent = data.data.likes || 0;
                if(dislikesSpan) dislikesSpan.textContent = data.data.dislikes || 0;
                
                // Mettre à jour l'état actif des boutons
                const likeBtn = document.querySelector(`#reactions-${responseId} .like-btn`);
                const dislikeBtn = document.querySelector(`#reactions-${responseId} .dislike-btn`);
                
                if(likeBtn && dislikeBtn) {
                    likeBtn.classList.remove('active');
                    dislikeBtn.classList.remove('active');
                    
                    if(data.data.user_reaction === 'like') {
                        likeBtn.classList.add('active');
                        likeBtn.style.background = '#e8f5e9';
                        likeBtn.style.color = '#2e7d32';
                    } else if(data.data.user_reaction === 'dislike') {
                        dislikeBtn.classList.add('active');
                        dislikeBtn.style.background = '#fdeaea';
                        dislikeBtn.style.color = '#c62828';
                    } else {
                        likeBtn.style.background = '';
                        likeBtn.style.color = '';
                        dislikeBtn.style.background = '';
                        dislikeBtn.style.color = '';
                    }
                }
            }
        })
        .catch(error => console.error('Erreur chargement réactions:', error));
}

function reactToResponse(responseId, type) {
    console.log("Réaction:", type, "pour réponse:", responseId);
    
    fetch(`${RESPONSE_API_URL}?action=reaction`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            response_id: responseId,
            user_id: currentUserId,
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Réponse du serveur:", data);
        if(data.success) {
            // Recharger les réactions pour cette réponse
            loadReactionsForResponse(responseId);
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// ==================== TENDANCES (Twitter-like) ====================

function recipeToHashtag(recipeName) {
    const name = String(recipeName || '').trim();
    if (!name) return '#';
    const compact = name.replace(/\s+/g, '');
    return '#' + compact;
}

function applyTrendSearch(recipeName) {
    const qEl = document.getElementById('advSearchText');
    if (qEl) qEl.value = String(recipeName || '').trim();
    readAdvancedSearchFromDom();
    loadReviews();
}

function loadTrends() {
    const list = document.getElementById('trendsList');
    if (!list) return;

    fetch(`${API_URL}?trends=1&limit=10`)
        .then(r => r.json())
        .then(data => {
            if (!data || !data.success || !Array.isArray(data.data)) {
                list.innerHTML = '<li class="trends-empty">Impossible de charger les tendances</li>';
                return;
            }

            if (data.data.length === 0) {
                list.innerHTML = '<li class="trends-empty">📭 Aucune tendance pour le moment</li>';
                return;
            }

            list.innerHTML = '';
            for (let i = 0; i < data.data.length; i++) {
                const row = data.data[i];
                const rank = i + 1;
                const recipe = row.recipe_name;
                const count = row.reviews_count || 0;

                const li = document.createElement('li');
                li.className = 'trend-item';
                li.title = 'Cliquer pour filtrer sur cette recette';
                li.innerHTML = `
                    <div class="trend-main">
                        <span class="trend-rank">${rank}</span>
                        <span class="trend-hashtag">${escapeHtml(recipeToHashtag(recipe))}</span>
                    </div>
                    <span class="trend-count">${count} avis</span>
                `;
                li.addEventListener('click', () => applyTrendSearch(recipe));
                list.appendChild(li);
            }
        })
        .catch(() => {
            list.innerHTML = '<li class="trends-empty">Erreur réseau</li>';
        });
}

// ==================== TOP 3 AVIS LIKÉS ====================

function loadTopReviews() {
    fetch(`${API_URL}?top=true`)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.data.length > 0) {
                displayTopReviews(data.data);
            } else {
                document.getElementById('topReviewsContainer').innerHTML = '<div style="text-align: center; padding: 20px;">📭 Aucun avis liké pour le moment</div>';
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function displayTopReviews(reviews) {
    const container = document.getElementById('topReviewsContainer');
    if(!container) return;
    
    if(reviews.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 20px;">📭 Aucun avis liké</div>';
        return;
    }
    
    let html = '<div class="top-reviews-grid">';
    
    for(let i = 0; i < reviews.length; i++) {
        const review = reviews[i];
        const rank = i + 1;
        const medal = rank === 1 ? '🥇' : rank === 2 ? '🥈' : '🥉';
        
        html += `
            <div class="top-review-card rank-${rank}">
                <div class="top-review-rank">${medal}</div>
                <div class="top-review-recipe">🍽️ ${escapeHtml(review.recipe_name)}</div>
                <div class="top-review-title">📌 "${escapeHtml(review.title || 'Avis')}"</div>
                <div class="top-review-content">"${escapeHtml(review.content.substring(0, 80))}${review.content.length > 80 ? '...' : ''}"</div>
                <div class="top-review-meta">
                    <div class="top-review-likes">❤️ ${review.likes_count || 0} likes</div>
                    <div class="top-review-responses">💬 ${review.responses_count || 0} réponses</div>
                    <div class="top-review-rating">${'⭐'.repeat(review.rating)}</div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// ==================== LIKES SUR AVIS ====================

const REVIEW_LIKE_API_URL = 'http://localhost/modulecommunity/Controller/ReviewLikeController.php';

function likeReview(reviewId) {
    fetch(REVIEW_LIKE_API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            review_id: reviewId,
            user_id: currentUserId
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            loadTopReviews();
            loadReviews();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

// ==================== NOTIFICATIONS ====================

function showNotification(message, type) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27AE60' : '#C0392B'};
        color: white;
        padding: 12px 20px;
        border-radius: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideInRight 0.3s ease;
        font-size: 14px;
    `;
    toast.innerHTML = `${message} <button onclick="this.parentElement.remove()" style="background:none; border:none; color:white; margin-left:10px; cursor:pointer;">✕</button>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== COMPTEURS CARACTÈRES ====================

function updateCharCounts() {
    const title = document.getElementById('reviewTitle');
    const titleCount = document.getElementById('titleCharCount');
    if(title && titleCount) titleCount.innerHTML = title.value.length;
    
    const content = document.getElementById('reviewText');
    const contentCount = document.getElementById('reviewCharCount');
    if(content && contentCount) contentCount.innerHTML = content.value.length;
}

function updateReponseCharCount() {
    const reponse = document.getElementById('reponseContent');
    const reponseCount = document.getElementById('reponseCharCount');
    if(reponse && reponseCount) reponseCount.innerHTML = reponse.value.length;
}

function setupCounters() {
    const title = document.getElementById('reviewTitle');
    if(title) title.addEventListener('input', updateCharCounts);
    
    const content = document.getElementById('reviewText');
    if(content) content.addEventListener('input', updateCharCounts);
    
    const reponse = document.getElementById('reponseContent');
    if(reponse) reponse.addEventListener('input', updateReponseCharCount);
}

// ==================== ÉVÉNEMENTS ====================

function setupEventListeners() {
    const showBtn = document.getElementById('showAddReviewBtn');
    if(showBtn) showBtn.onclick = () => toggleForm(true);

    const applyAdv = document.getElementById('advSearchApplyBtn');
    if (applyAdv) applyAdv.onclick = () => {
        readAdvancedSearchFromDom();
        loadReviews();
    };

    const resetAdv = document.getElementById('advSearchResetBtn');
    if (resetAdv) resetAdv.onclick = () => {
        resetAdvancedSearchDom();
        loadReviews();
    };

    const sortSelect = document.getElementById('reviewSortSelect');
    if (sortSelect) {
        sortSelect.value = currentReviewSort;
        sortSelect.onchange = () => {
            currentReviewSort = sortSelect.value || 'date';
            loadReviews();
        };
    }
    
    const cancelBtn = document.getElementById('cancelReviewBtn');
    if(cancelBtn) cancelBtn.onclick = () => toggleForm(false);
    
    const form = document.getElementById('reviewForm');
    if(form) form.onsubmit = addReview;
    
    const editForm = document.getElementById('editReviewForm');
    if(editForm) editForm.onsubmit = updateReview;
    
    const closeModalBtn = document.getElementById('closeModalBtn');
    if(closeModalBtn) closeModalBtn.onclick = () => closeModal();
}

function setupResponseEvents() {
    const reponseForm = document.getElementById('reponseForm');
    if(reponseForm) reponseForm.onsubmit = addReponse;
    
    const closeReponse = document.getElementById('closeReponseBtn');
    if(closeReponse) closeReponse.onclick = closeReponseModal;
    
    const editReponseForm = document.getElementById('editReponseForm');
    if(editReponseForm) editReponseForm.onsubmit = updateReponse;
    
    const closeEditReponse = document.getElementById('closeEditReponseBtn');
    if(closeEditReponse) closeEditReponse.onclick = closeEditReponseModal;
}

function setupRecipeSelectEvents() {
    const select = document.getElementById('recipeSelect');
    if(select) {
        select.addEventListener('change', function() {
            effacerErreurRecette();
        });
        select.addEventListener('focus', function() {
            effacerErreurRecette();
        });
    }
}

function effacerErreurRecette() {
    const recipeError = document.getElementById('recipeError');
    if(recipeError) {
        recipeError.innerHTML = '';
        recipeError.style.display = 'none';
    }
    const select = document.getElementById('recipeSelect');
    if(select) {
        select.style.borderColor = 'var(--brun-pale)';
        select.style.borderWidth = '1.5px';
    }
}

function toggleForm(show) {
    const form = document.getElementById('addReviewForm');
    if(form) form.style.display = show ? 'block' : 'none';
    
    if(show) {
        setTimeout(function() {
            document.getElementById('ratingValue').value = 0;
            const stars = document.querySelectorAll('#starRating span');
            for(let s of stars) {
                s.innerHTML = '☆';
                s.style.color = '#ccc';
            }
        }, 50);
    }
    
    if(!show) {
        document.getElementById('reviewTitle').value = '';
        document.getElementById('reviewText').value = '';
        document.getElementById('ratingValue').value = 0;
        const stars = document.querySelectorAll('#starRating span');
        for(let s of stars) {
            s.innerHTML = '☆';
            s.style.color = '#ccc';
        }
        effacerErreursFormulaire();
    }
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function closeReponseModal() {
    document.getElementById('reponseModal').style.display = 'none';
    document.getElementById('reponseForm').reset();
    document.getElementById('reponseParentId').value = '';
    effacerErreursReponse();
}

function closeEditReponseModal() {
    document.getElementById('editReponseModal').style.display = 'none';
}

function formatDate(dateString) {
    if(!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function escapeHtml(text) {
    if(!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== SYSTÈME DE MENTIONS @username ====================

// Cache des auteurs connus (chargé une fois depuis le serveur)
let knownAuthors = [];
// Liste temporaire pour tester l'autocomplete @mention si l'API ne renvoie rien
const TEMP_KNOWN_AUTHORS = [
    { user_id: 1, username: 'jean_dupont' },
    { user_id: 2, username: 'yassin' },
    { user_id: 3, username: 'admin' },
    { user_id: 4, username: 'sara' },
];

// Charger la liste des auteurs pour l'autocomplete
function loadKnownAuthors() {
    fetch(`${RESPONSE_API_URL}?action=users`)
        .then(r => r.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.data) && data.data.length > 0) {
                knownAuthors = data.data; // [{user_id, username}]
            } else {
                knownAuthors = TEMP_KNOWN_AUTHORS;
            }
        })
        .catch(() => {
            knownAuthors = TEMP_KNOWN_AUTHORS;
        });
}

// ------------------------------------------------------------------
//  Attacher l'autocomplete @mention sur un textarea
// ------------------------------------------------------------------
function attachMentionAutocomplete(textarea) {
    if (!textarea || textarea.dataset.mentionReady) return;
    textarea.dataset.mentionReady = '1';

    // Créer le dropdown
    const dropdown = document.createElement('ul');
    dropdown.className = 'mention-dropdown';
    dropdown.style.cssText = `
        display:none; position:absolute; z-index:9999;
        background:#fff; border:1px solid #d1d5db; border-radius:8px;
        list-style:none; margin:0; padding:4px 0;
        max-height:180px; overflow-y:auto;
        box-shadow:0 4px 16px rgba(0,0,0,0.12); min-width:180px;
    `;
    // Positionner par rapport au parent
    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'position:relative; display:inline-block; width:100%;';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(textarea);
    wrapper.appendChild(dropdown);

    let mentionStart = -1; // position du @ dans le texte

    textarea.addEventListener('input', () => {
        const val   = textarea.value;
        const pos   = textarea.selectionStart;
        // Chercher un @ avant le curseur (sans espace depuis le @)
        const before = val.slice(0, pos);
        const match  = before.match(/@([a-zA-ZÀ-ÿ0-9_\-]*)$/u);

        if (match) {
            mentionStart = before.lastIndexOf('@');
            const query  = match[1].toLowerCase();
            const results = knownAuthors.filter(a =>
                a.username.toLowerCase().includes(query) && a.username !== ''
            ).slice(0, 6);

            if (results.length) {
                dropdown.innerHTML = '';
                results.forEach(author => {
                    const li = document.createElement('li');
                    li.style.cssText = 'padding:8px 14px; cursor:pointer; font-size:14px; display:flex; align-items:center; gap:8px;';
                    li.innerHTML = `<span style="font-size:18px;">👤</span><span>@${escapeHtml(author.username)}</span>`;
                    li.addEventListener('mousedown', e => {
                        e.preventDefault();
                        // Remplacer @query par @username (avec espace après)
                        const before2 = val.slice(0, mentionStart);
                        const after2  = val.slice(pos);
                        textarea.value = before2 + '@' + author.username + ' ' + after2;
                        // Repositionner le curseur
                        const newPos = before2.length + author.username.length + 2;
                        textarea.setSelectionRange(newPos, newPos);
                        hideDropdown();
                        textarea.dispatchEvent(new Event('input'));
                    });
                    li.addEventListener('mouseover', () => li.style.background = '#f3f4f6');
                    li.addEventListener('mouseout',  () => li.style.background = '');
                    dropdown.appendChild(li);
                });
                dropdown.style.display = 'block';
            } else {
                hideDropdown();
            }
        } else {
            hideDropdown();
        }
    });

    textarea.addEventListener('keydown', e => {
        if (dropdown.style.display === 'none') return;
        const items = dropdown.querySelectorAll('li');
        const active = dropdown.querySelector('li.active');
        let idx = Array.from(items).indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (active) active.classList.remove('active');
            idx = (idx + 1) % items.length;
            items[idx].classList.add('active');
            items[idx].style.background = '#e5e7eb';
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active) active.classList.remove('active');
            idx = (idx - 1 + items.length) % items.length;
            items[idx].classList.add('active');
            items[idx].style.background = '#e5e7eb';
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            if (active) { e.preventDefault(); active.dispatchEvent(new Event('mousedown')); }
        } else if (e.key === 'Escape') {
            hideDropdown();
        }
    });

    textarea.addEventListener('blur', () => setTimeout(hideDropdown, 150));

    function hideDropdown() {
        dropdown.style.display = 'none';
        dropdown.querySelectorAll('li.active').forEach(li => li.classList.remove('active'));
    }
}

// ------------------------------------------------------------------
//  Highlight des @mentions dans le texte affiché
//  Remplace @username par un badge coloré
// ------------------------------------------------------------------
function highlightMentions(text) {
    if (!text) return '';
    // Échapper d'abord le HTML
    const escaped = escapeHtml(text);
    // Puis colorer les @mentions
    return escaped.replace(/@([a-zA-ZÀ-ÿ0-9_\-]+)/gu, (match, uname) =>
        `<span class="mention-badge">@${uname}</span>`
    );
}

// ==================== INITIALISATION ====================
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les filtres depuis le HTML (valeurs par défaut)
    readAdvancedSearchFromDom();
    loadReviews();
    loadResponses();
    loadTrends();
    loadTopReviews();
    setupEventListeners();
    setupResponseEvents();
    setupStarRating();
    setupCounters();
    setupRecipeSelectEvents();
    updateCharCounts();
    // Charger les auteurs pour l'autocomplete @mention
    loadKnownAuthors();
    // Attacher l'autocomplete sur le textarea de réponse (déjà dans le DOM)
    const ta = document.getElementById('reponseContent');
    if (ta) attachMentionAutocomplete(ta);
});