// Configuration
const API_URL = 'http://localhost/modulecommunity/Controller/ReviewController.php';
const RESPONSE_API_URL = 'http://localhost/modulecommunity/Controller/ResponseController.php';
const currentUserId = 1;

// Au chargement
document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
    loadResponses();
    setupEventListeners();
    setupResponseEvents();
    setupStarRating();
});

// ==================== CHARGER AVIS ====================
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

// AFFICHER AVIS EN FORMAT CARTES
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
                    <div class="recipe-name">${escapeHtml(review.recipe_name)}</div>
                    <div class="rating">${'⭐'.repeat(review.rating)}</div>
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
                    <button class="btn-modifier" onclick="editReview(${review.id}, '${escapeHtml(review.content)}', ${review.rating})">✏️ Modifier</button>
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
    
    // Charger les réponses pour chaque avis
    for(let i = 0; i < reviews.length; i++) {
        loadResponsesForReview(reviews[i].id);
    }
}

// CHARGER RÉPONSES POUR UN AVIS
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

// AFFICHER RÉPONSES DANS LA CARTE
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
        
        html += `
            <div class="response-card">
                <div class="response-header">
                    <div class="response-author">💬 ${escapeHtml(r.username || 'Utilisateur ' + r.user_id)}</div>
                    <div class="response-date">${formatDate(r.created_at)}</div>
                </div>
                <div class="response-content">${escapeHtml(r.content)}</div>
                ${isOwner ? `
                <div class="response-actions">
                    <button onclick="editReponse(${r.id}, '${escapeHtml(r.content)}')">✏️ Modifier</button>
                    <button onclick="deleteReponse(${r.id})">🗑️ Supprimer</button>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    container.innerHTML = html;
}

// STATISTIQUES
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

// AJOUTER AVIS
function addReview(e) {
    e.preventDefault();
    const select = document.getElementById('recipeSelect');
    const recipe_name = select.options[select.selectedIndex].text;
    const content = document.getElementById('reviewText').value;
    const rating = document.getElementById('ratingValue').value;
    
    if(content.length < 5) { alert("L'avis doit contenir au moins 5 caractères"); return; }
    if(!rating || rating < 1) { alert("Choisissez une note"); return; }
    
    fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({recipe_name, content, rating})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification('✅ Avis ajouté avec succès !', 'success');
            toggleForm(false);
            loadReviews();
            document.getElementById('reviewForm').reset();
            document.getElementById('ratingValue').value = 0;
            highlightStars(0);
        } else {
            showNotification('❌ Erreur: ' + data.errors.join(', '), 'error');
        }
    });
}

// MODIFIER AVIS
function editReview(id, content, rating) {
    document.getElementById('editReviewId').value = id;
    document.getElementById('editReviewText').value = content;
    document.getElementById('editReviewRating').value = rating;
    document.getElementById('editModal').style.display = 'flex';
}

function updateReview(e) {
    e.preventDefault();
    const id = document.getElementById('editReviewId').value;
    const content = document.getElementById('editReviewText').value;
    const rating = document.getElementById('editReviewRating').value;
    
    fetch(API_URL, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, content, rating})
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

// SUPPRIMER AVIS
function deleteReview(id) {
    if(confirm('Supprimer cet avis ?')) {
        fetch(API_URL + '?id=' + id, {method: 'DELETE'})
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('✅ Avis supprimé avec succès !', 'success');
                    loadReviews();
                }
            });
    }
}

// ==================== RÉPONSES ====================

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
    
    if(responses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Aucune réponse pour le moment</td</tr>';
        return;
    }
    
    let html = '';
    for(let i = 0; i < responses.length; i++) {
        const r = responses[i];
        html += `
            <tr>
                <td>${formatDate(r.created_at)}</td>
                <td>Avis #${r.review_id}</td>
                <td>${escapeHtml(r.content.substring(0, 50))}${r.content.length > 50 ? '...' : ''}</td>
                <td>
                    <button onclick="editReponse(${r.id}, '${escapeHtml(r.content)}')" class="btn btn-sm btn-outline">✏️ Modifier</button>
                    <button onclick="deleteReponse(${r.id})" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                 </td>
             </tr>
        `;
    }
    tbody.innerHTML = html;
}

function openReponseModal(reviewId, reviewContent, recipeName) {
    document.getElementById('reponseReviewId').value = reviewId;
    document.getElementById('reponseOriginalAvis').value = recipeName + ' : ' + reviewContent;
    document.getElementById('reponseModal').style.display = 'flex';
}

function addReponse(e) {
    e.preventDefault();
    const review_id = document.getElementById('reponseReviewId').value;
    const content = document.getElementById('reponseContent').value;
    
    if(content.length < 2) {
        alert("La réponse doit contenir au moins 2 caractères");
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
            showNotification('❌ Erreur lors de l\'ajout', 'error');
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
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none; border:none; color:white; cursor:pointer;">✕</button>
    `;
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
    
    const closeModal = document.getElementById('closeModalBtn');
    if(closeModal) closeModal.onclick = () => closeModal();
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

// ==================== ÉTOILES ====================

function setupStarRating() {
    const stars = document.querySelectorAll('#starRating span');
    const ratingInput = document.getElementById('ratingValue');
    
    for(let i = 0; i < stars.length; i++) {
        const star = stars[i];
        star.onmouseover = () => highlightStars(parseInt(star.dataset.rating));
        star.onmouseout = () => highlightStars(parseInt(ratingInput.value) || 0);
        star.onclick = () => {
            const rating = parseInt(star.dataset.rating);
            ratingInput.value = rating;
            highlightStars(rating);
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

// ==================== UTILITAIRES ====================

function toggleForm(show) {
    const form = document.getElementById('addReviewForm');
    if(form) form.style.display = show ? 'block' : 'none';
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

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function closeReponseModal() {
    document.getElementById('reponseModal').style.display = 'none';
    document.getElementById('reponseForm').reset();
}

function closeEditReponseModal() {
    document.getElementById('editReponseModal').style.display = 'none';
}

// Initialiser
setupStarRating();