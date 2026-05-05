// Configuration
const API_URL = 'http://localhost/modulecommunity/Controller/ReviewController.php';
const RESPONSE_API_URL = 'http://localhost/modulecommunity/Controller/ResponseController.php';
const STATS_API_URL = 'http://localhost/modulecommunity/Controller/StatsController.php';

let allReviews = [];
let allResponses = [];
let currentPage = 1;
let currentPageResponses = 1;
let itemsPerPage = 5;

document.addEventListener('DOMContentLoaded', function() {
    chargerAvis();
    chargerReponses();
    chargerStats();
    setupEventListeners();
});

function chargerStats() {
    fetch(STATS_API_URL)
        .then(r => r.json())
        .then(data => {
            if (!data || !data.success || !data.data) return;
            const s = data.data;
            const totalReviewsEl = document.getElementById('totalReviews');
            const avgRatingEl = document.getElementById('avgRating');
            const totalResponsesEl = document.getElementById('totalResponses');
            const activeUsersEl = document.getElementById('activeUsers');

            if (totalReviewsEl) totalReviewsEl.textContent = s.total_reviews ?? 0;
            if (avgRatingEl) avgRatingEl.textContent = (s.avg_rating ?? 0).toFixed ? (s.avg_rating ?? 0).toFixed(1) : (s.avg_rating ?? 0);
            if (totalResponsesEl) totalResponsesEl.textContent = s.total_responses ?? 0;
            if (activeUsersEl) activeUsersEl.textContent = s.active_users ?? 0;
        })
        .catch(() => {});
}

function chargerAvis() {
    fetch(API_URL)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                allReviews = data.data;
                afficherAvis();
            }
        });
}

function chargerReponses() {
    fetch(RESPONSE_API_URL)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                allResponses = data.data;
                afficherReponses();
            }
        });
}

function afficherAvis() {
    const container = document.getElementById('reviewsContainer');
    const start = (currentPage - 1) * itemsPerPage;
    const paginated = allReviews.slice(start, start + itemsPerPage);
    
    if(paginated.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:40px;">📭 Aucun avis</div>';
        return;
    }
    
    let html = '<div class="reviews-grid">';
    for(let a of paginated) {
        html += `
            <div class="review-card">
                <div class="review-header">
                    <div class="recipe-name">${escapeHtml(a.recipe_name)}</div>
                    <div class="rating">${'⭐'.repeat(a.rating)}</div>
                </div>
                <div class="review-meta">
                    <span>📅 ${formatDate(a.created_at)}</span>
                    <span>👤 Utilisateur ${a.user_id || 1}</span>
                </div>
                <div class="review-content">"${escapeHtml(a.content)}"</div>
                <div class="review-actions">
                    <button class="btn-edit" onclick="editReview(${a.id})">✏️ Modifier</button>
                    <button class="btn-delete" onclick="deleteReview(${a.id})">🗑️ Supprimer</button>
                </div>
                <div class="responses-section">
                    <div class="responses-title">💬 Réponses</div>
                    <div id="responses-list-${a.id}">Chargement...</div>
                </div>
            </div>
        `;
    }
    html += '</div>';
    container.innerHTML = html;
    
    for(let a of paginated) {
        chargerReponsesPourAvis(a.id);
    }
    
    const totalPages = Math.ceil(allReviews.length / itemsPerPage);
    document.getElementById('pageInfo').innerHTML = `Page ${currentPage} / ${totalPages || 1}`;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function chargerReponsesPourAvis(reviewId) {
    fetch(`${RESPONSE_API_URL}?review_id=${reviewId}`)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const container = document.getElementById(`responses-list-${reviewId}`);
                if(!container) return;
                
                if(data.data.length === 0) {
                    container.innerHTML = '<div style="font-size:12px; color:#999;">Aucune réponse</div>';
                    return;
                }
                
                let html = '';
                for(let r of data.data) {
                    html += `
                        <div class="response-card">
                            <div class="response-header">
                                <span class="response-author">💬 Utilisateur ${r.user_id}</span>
                                <span class="response-date">${formatDate(r.created_at)}</span>
                            </div>
                            <div class="response-content">${escapeHtml(r.content)}</div>
                            <div class="response-actions">
                                <button onclick="editReponse(${r.id})">✏️ Modifier</button>
                                <button onclick="deleteReponse(${r.id})">🗑️ Supprimer</button>
                            </div>
                        </div>
                    `;
                }
                container.innerHTML = html;
            }
        });
}

function afficherReponses() {
    const container = document.getElementById('responsesContainer');
    const start = (currentPageResponses - 1) * itemsPerPage;
    const paginated = allResponses.slice(start, start + itemsPerPage);
    
    if(paginated.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:40px;">📭 Aucune réponse</div>';
        return;
    }
    
    let html = '<div class="responses-grid">';
    for(let r of paginated) {
        html += `
            <div class="response-item">
                <div class="response-info">
                    <div class="response-id">#${r.id} - Avis #${r.review_id}</div>
                    <div class="response-text">${escapeHtml(r.content)}</div>
                    <div style="font-size:11px; color:#999; margin-top:5px;">📅 ${formatDate(r.created_at)}</div>
                </div>
                <div class="response-actions-item">
                    <button class="btn-edit" onclick="editReponse(${r.id})">✏️</button>
                    <button class="btn-delete" onclick="deleteReponse(${r.id})">🗑️</button>
                </div>
            </div>
        `;
    }
    html += '</div>';
    container.innerHTML = html;
    
    const totalPages = Math.ceil(allResponses.length / itemsPerPage);
    document.getElementById('pageInfoResponses').innerHTML = `Page ${currentPageResponses} / ${totalPages || 1}`;
    document.getElementById('prevPageResponses').disabled = currentPageResponses === 1;
    document.getElementById('nextPageResponses').disabled = currentPageResponses === totalPages;
}

function editReview(id) {
    const review = allReviews.find(r => r.id == id);
    if(!review) return;
    document.getElementById('editReviewId').value = review.id;
    document.getElementById('editRecipeName').value = review.recipe_name;
    document.getElementById('editUserName').value = 'Utilisateur ' + (review.user_id || 1);
    document.getElementById('editContent').value = review.content;
    document.getElementById('editRating').value = review.rating;
    document.getElementById('editModal').style.display = 'flex';
}

function deleteReview(id) {
    if(confirm('Supprimer cet avis ?')) {
        fetch(API_URL + '?id=' + id, {method: 'DELETE'})
            .then(r => r.json())
            .then(data => {
                if(data.success) { alert('✅ Avis supprimé'); chargerAvis(); chargerReponses(); }
            });
    }
}

function editReponse(id) {
    const reponse = allResponses.find(r => r.id == id);
    if(!reponse) return;
    document.getElementById('editReponseId').value = reponse.id;
    document.getElementById('editReponseReviewId').value = 'Avis #' + reponse.review_id;
    document.getElementById('editReponseAuthor').value = 'Utilisateur ' + reponse.user_id;
    document.getElementById('editReponseContent').value = reponse.content;
    document.getElementById('editReponseModal').style.display = 'flex';
}

function deleteReponse(id) {
    if(confirm('Supprimer cette réponse ?')) {
        fetch(RESPONSE_API_URL + '?id=' + id, {method: 'DELETE'})
            .then(r => r.json())
            .then(data => {
                if(data.success) { alert('✅ Réponse supprimée'); chargerAvis(); chargerReponses(); }
            });
    }
}

function showTab(tab) {
    const tabAvis = document.getElementById('tab-avis');
    const tabReponses = document.getElementById('tab-reponses');
    const btns = document.querySelectorAll('.tab-btn');
    
    if(tab === 'avis') {
        tabAvis.style.display = 'block';
        tabReponses.style.display = 'none';
        btns[0].classList.add('active');
        btns[1].classList.remove('active');
    } else {
        tabAvis.style.display = 'none';
        tabReponses.style.display = 'block';
        btns[0].classList.remove('active');
        btns[1].classList.add('active');
        afficherReponses();
    }
}

function setupEventListeners() {
    document.getElementById('refreshBtn').onclick = () => { chargerAvis(); chargerReponses(); chargerStats(); };
    document.getElementById('prevPage').onclick = () => { if(currentPage > 1) { currentPage--; afficherAvis(); } };
    document.getElementById('nextPage').onclick = () => {
        if(currentPage < Math.ceil(allReviews.length / itemsPerPage)) { currentPage++; afficherAvis(); }
    };
    document.getElementById('prevPageResponses').onclick = () => {
        if(currentPageResponses > 1) { currentPageResponses--; afficherReponses(); }
    };
    document.getElementById('nextPageResponses').onclick = () => {
        if(currentPageResponses < Math.ceil(allResponses.length / itemsPerPage)) { currentPageResponses++; afficherReponses(); }
    };
    
    document.getElementById('editReviewForm').onsubmit = (e) => {
        e.preventDefault();
        const id = document.getElementById('editReviewId').value;
        const content = document.getElementById('editContent').value;
        const rating = document.getElementById('editRating').value;
        fetch(API_URL, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: parseInt(id), content, rating: parseInt(rating)})
        }).then(r => r.json()).then(data => {
            if(data.success) { alert('✅ Avis modifié'); document.getElementById('editModal').style.display = 'none'; chargerAvis(); chargerReponses(); }
        });
    };
    
    document.getElementById('editReponseForm').onsubmit = (e) => {
        e.preventDefault();
        const id = document.getElementById('editReponseId').value;
        const content = document.getElementById('editReponseContent').value;
        fetch(RESPONSE_API_URL, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: parseInt(id), content})
        }).then(r => r.json()).then(data => {
            if(data.success) { alert('✅ Réponse modifiée'); document.getElementById('editReponseModal').style.display = 'none'; chargerAvis(); chargerReponses(); }
        });
    };
    
    document.getElementById('closeModal').onclick = () => document.getElementById('editModal').style.display = 'none';
    document.getElementById('cancelModalBtn').onclick = () => document.getElementById('editModal').style.display = 'none';
    document.getElementById('closeReponseModal').onclick = () => document.getElementById('editReponseModal').style.display = 'none';
    document.getElementById('cancelReponseBtn').onclick = () => document.getElementById('editReponseModal').style.display = 'none';
    window.onclick = (e) => { if(e.target.classList.contains('modal')) e.target.style.display = 'none'; };
}

function formatDate(date) { return date ? new Date(date).toLocaleDateString('fr-FR') : ''; }
function escapeHtml(text) { if(!text) return ''; return text.replace(/[&<>]/g, function(m) { if(m === '&') return '&amp;'; if(m === '<') return '&lt;'; if(m === '>') return '&gt;'; return m; }); }