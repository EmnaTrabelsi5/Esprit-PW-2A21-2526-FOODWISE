// Configuration
const API_URL = 'http://localhost/modulecommunity/Controller/ReviewController.php';
const RESPONSE_API_URL = 'http://localhost/modulecommunity/Controller/ResponseController.php';
const currentUserId = 1;

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
function loadReviews() {
    fetch(API_URL)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                displayReviewsCards(data.data);
                updateStats(data.data);
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function displayReviewsCards(reviews) {
    const container = document.getElementById('reviewsContainer');
    if(!container) return;
    
    if(reviews.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px;">📭 Aucun avis pour le moment</div>';
        return;
    }
    
    let html = '<div class="reviews-grid">';
    
    for(let i = 0; i < reviews.length; i++) {
        const review = reviews[i];
        
        html += `
            <div class="review-card" data-review-id="${review.id}">
                <div class="review-header">
                    <div class="recipe-name">🍽️ ${escapeHtml(review.recipe_name)}</div>
                    <div class="rating">${'⭐'.repeat(review.rating)}</div>
                </div>
                <div class="review-title" style="font-weight: 700; color: var(--brun-chaud); margin: 8px 0; font-size: 16px;">
                    📌 "${escapeHtml(review.title || 'Avis')}"
                </div>
                <div class="review-meta">
                    <span>📅 ${formatDate(review.created_at)}</span>
                    <span>👤 Utilisateur ${review.user_id || 1}</span>
                </div>
                <div class="review-content">"${escapeHtml(review.content)}"</div>
                <div class="review-stats">
                    <span>💬 <span id="response-count-${review.id}">0</span> réponse(s)</span>
                </div>
                <div class="review-actions">
                    <button class="btn-repondre" onclick="openReponseModal(${review.id}, '${escapeHtml(review.content)}', '${escapeHtml(review.recipe_name)}')">💬 Répondre</button>
                    <button class="btn-modifier" onclick="editReview(${review.id})">✏️ Modifier</button>
                    <button class="btn-supprimer" onclick="deleteReview(${review.id})">🗑️ Supprimer</button>
                </div>
                <div class="responses-section">
                    <div class="responses-title">💬 Réponses</div>
                    <div id="responses-list-${review.id}">
                        <div style="font-size:12px; color:#999;">Chargement...</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
    
    for(let i = 0; i < reviews.length; i++) {
        loadResponsesForReview(reviews[i].id);
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
    
    if(responses.length === 0) {
        container.innerHTML = '<div style="font-size:12px; color:#aaa; padding:8px 0;">Aucune réponse pour le moment</div>';
        return;
    }
    
    let html = '';
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        const isOwner = (r.user_id == currentUserId);
        
        // Générer un ID unique pour cette réponse
        const responseId = r.id;
        
        html += `
            <div class="response-card" id="response-${responseId}">
                <div class="response-header">
                    <div class="response-author">💬 ${escapeHtml(r.username || 'Utilisateur ' + r.user_id)}</div>
                    <div class="response-date">${formatDate(r.created_at)}</div>
                </div>
                <div class="response-content">${escapeHtml(r.content)}</div>
                <div class="response-footer">
                    <div class="response-reactions" id="reactions-${responseId}">
                        <button class="reaction-btn like-btn" onclick="reactToResponse(${responseId}, 'like')">
                            👍 <span id="likes-count-${responseId}">0</span>
                        </button>
                        <button class="reaction-btn dislike-btn" onclick="reactToResponse(${responseId}, 'dislike')">
                            👎 <span id="dislikes-count-${responseId}">0</span>
                        </button>
                    </div>
                    ${isOwner ? `
                    <div class="response-actions">
                        <button onclick="editReponse(${responseId}, '${escapeHtml(r.content)}')">✏️ Modifier</button>
                        <button onclick="deleteReponse(${responseId})">🗑️ Supprimer</button>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
    
    // Charger les réactions pour chaque réponse
    for(let i = 0; i < responses.length; i++) {
        loadReactionsForResponse(responses[i].id);
    }
}

function loadResponses() {
    fetch(RESPONSE_API_URL)
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
    
    if(responses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Aucune réponse pour le moment</td\(';
        return;
    }
    
    // Ajouter chaque réponse ligne par ligne
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        const row = tbody.insertRow();
        
        // Cellule Date
        const cellDate = row.insertCell(0);
        cellDate.innerHTML = formatDate(r.created_at);
        
        // Cellule Avis concerné
        const cellAvis = row.insertCell(1);
        cellAvis.innerHTML = `Avis #${r.review_id}`;
        
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

function updateStats(reviews) {
    if(reviews.length === 0) return;
    let totalRating = 0;
    for(let i = 0; i < reviews.length; i++) {
        totalRating += reviews[i].rating;
    }
    const averageRating = (totalRating / reviews.length).toFixed(1);
    
    document.getElementById('statNoteMoyenne').innerHTML = averageRating;
    document.getElementById('statTotalAvis').innerHTML = reviews.length;
}

// ==================== SYSTÈME D'ÉTOILES (SIMPLE ET FONCTIONNEL) ====================

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

function highlightStars(rating) {
    const stars = document.querySelectorAll('#starRating span');
    for(let i = 0; i < stars.length; i++) {
        stars[i].innerHTML = i < rating ? '★' : '☆';
        stars[i].style.color = i < rating ? '#FFD700' : '#ccc';
    }
}

// ==================== AJOUTER AVIS (AVEC CONTROLES) ====================

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
            // Réinitialiser les étoiles
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
    fetch(API_URL)
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

// ==================== RÉPONSES (AVEC CONTROLES) ====================

function openReponseModal(reviewId, reviewContent, recipeName) {
    document.getElementById('reponseReviewId').value = reviewId;
    document.getElementById('reponseOriginalAvis').value = recipeName + ' : ' + reviewContent;
    document.getElementById('reponseModal').style.display = 'flex';
    effacerErreursReponse();
    document.getElementById('reponseContent').value = '';
}

function addReponse(e) {
    e.preventDefault();
    const review_id = document.getElementById('reponseReviewId').value;
    const content = document.getElementById('reponseContent').value;
    
    effacerErreursReponse();
    const validation = validerReponse(content);
    
    if(!validation.valide) {
        afficherErreursReponse(validation.errors);
        return;
    }
    
    fetch(RESPONSE_API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            review_id: parseInt(review_id),
            user_id: currentUserId,
            content: content
        })
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

// ==================== NOTIFICATION ====================

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
    `;
    toast.innerHTML = `${message} <button onclick="this.parentElement.remove()" style="background:none; border:none; color:white; margin-left:10px; cursor:pointer;">✕</button>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== ÉVÉNEMENTS ====================

function setupEventListeners() {
    const showBtn = document.getElementById('showAddReviewBtn');
    if(showBtn) showBtn.onclick = () => toggleForm(true);
    
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
// ==================== FONCTIONS POUR LES LIKES/DISLIKES ====================

function loadReactionsForResponse(responseId) {
    console.log("Chargement des réactions pour la réponse:", responseId);
    
    fetch(`${RESPONSE_API_URL}?action=reactions&response_id=${responseId}&user_id=${currentUserId}`)
        .then(response => response.json())
        .then(data => {
            console.log("Réactions reçues:", data);
            
            if(data.success) {
                // Mettre à jour les compteurs
                const likesSpan = document.getElementById(`likes-count-${responseId}`);
                const dislikesSpan = document.getElementById(`dislikes-count-${responseId}`);
                
                if(likesSpan) likesSpan.innerHTML = data.data.likes || 0;
                if(dislikesSpan) dislikesSpan.innerHTML = data.data.dislikes || 0;
                
                // Mettre en surbrillance le bouton si l'utilisateur a réagi
                const likeBtn = document.querySelector(`#reactions-${responseId} .like-btn`);
                const dislikeBtn = document.querySelector(`#reactions-${responseId} .dislike-btn`);
                
                if(likeBtn && dislikeBtn) {
                    // Enlever les classes actives des deux boutons
                    likeBtn.classList.remove('active');
                    dislikeBtn.classList.remove('active');
                    
                    // Ajouter la classe active au bouton correspondant
                    if(data.data.user_reaction === 'like') {
                        likeBtn.classList.add('active');
                    } else if(data.data.user_reaction === 'dislike') {
                        dislikeBtn.classList.add('active');
                    }
                }
            }
        })
        .catch(error => console.error('Erreur chargement réactions:', error));
}

function reactToResponse(responseId, type) {
    console.log("Réaction à la réponse:", responseId, "Type:", type);
    
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
        } else {
            console.error("Erreur:", data.errors);
        }
    })
    .catch(error => console.error('Erreur:', error));
}
// ==================== INITIALISATION ====================
document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
    loadResponses();
    setupEventListeners();
    setupResponseEvents();
    setupStarRating();
});