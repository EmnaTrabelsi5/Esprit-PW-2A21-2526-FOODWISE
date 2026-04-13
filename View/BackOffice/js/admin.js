// Configuration - CHANGEZ LE CHEMIN SELON VOTRE DOSSIER
const API_URL = 'http://localhost/modulecommunity/Controller/ReviewController.php';
const RESPONSE_API_URL = 'http://localhost/modulecommunity/Controller/ResponseController.php';

// Variables
let allReviews = [];
let allResponses = [];
let currentPage = 1;
let currentPageResponses = 1;
let itemsPerPage = 10;

// Au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log("BackOffice chargé - Démarrage...");
    chargerAvis();
    chargerReponses();
    setupEventListeners();
});

// ==================== CHARGEMENT ====================

function chargerAvis() {
    console.log("Chargement des avis depuis:", API_URL);
    const tbody = document.getElementById('reviewsTableBody');
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">⏳ Chargement...</td\(';
    
    fetch(API_URL)
        .then(response => {
            console.log("Réponse status:", response.status);
            if(!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log("Données reçues:", data);
            if(data.success && data.data) {
                allReviews = data.data;
                afficherAvis();
                calculerStatistiques();
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="color: red; text-align: center;">❌ Erreur: ' + (data.errors || 'Données invalides') + '</td\(';
            }
        })
        .catch(error => {
            console.error("Erreur détaillée:", error);
            tbody.innerHTML = '<tr><td colspan="8" style="color: red; text-align: center;">❌ Erreur de connexion: ' + error.message + '</td\(';
        });
}

function chargerReponses() {
    console.log("Chargement des réponses depuis:", RESPONSE_API_URL);
    fetch(RESPONSE_API_URL)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.data) {
                allResponses = data.data;
                afficherReponses();
                calculerStatistiques();
            }
        })
        .catch(error => console.error('Erreur chargement réponses:', error));
}

// ==================== AFFICHAGE AVIS ====================

function afficherAvis() {
    const tbody = document.getElementById('reviewsTableBody');
    
    if(allReviews.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">📭 Aucun avis trouvé</td\(';
        return;
    }
    
    const start = (currentPage - 1) * itemsPerPage;
    const paginated = allReviews.slice(start, start + itemsPerPage);
    
    let html = '';
    for(let i = 0; i < paginated.length; i++) {
        const a = paginated[i];
        html += '<tr>';
        html += '<td><input type="checkbox" class="review-checkbox" data-id="' + a.id + '"></td>';
        html += '<td>' + a.id + '</td>';
        html += '<td>Utilisateur ' + (a.user_id || 1) + '</td>';
        html += '<td><strong>' + escapeHtml(a.recipe_name) + '</strong></td>';
        html += '<td style="max-width: 300px;">' + escapeHtml(a.content) + '</td>';
        html += '<td class="rating-stars">' + '⭐'.repeat(a.rating) + '</td>';
        html += '<td>' + formatDate(a.created_at) + '</td>';
        html += '<td>';
        html += '<button class="btn-edit" onclick="ouvrirModalModificationAvis(' + a.id + ')">✏️ Modifier</button>';
        html += '<button class="btn-delete" onclick="supprimerAvis(' + a.id + ')">🗑️ Supprimer</button>';
        html += '</td>';
        html += '</tr>';
    }
    tbody.innerHTML = html;
    
    const totalPages = Math.ceil(allReviews.length / itemsPerPage);
    document.getElementById('pageInfo').innerHTML = `Page ${currentPage} / ${totalPages || 1}`;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

// ==================== AFFICHAGE RÉPONSES ====================

function afficherReponses() {
    const tbody = document.getElementById('responsesTableBody');
    
    if(allResponses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">📭 Aucune réponse trouvée</td\(';
        return;
    }
    
    const start = (currentPageResponses - 1) * itemsPerPage;
    const paginated = allResponses.slice(start, start + itemsPerPage);
    
    let html = '';
    for(let i = 0; i < paginated.length; i++) {
        const r = paginated[i];
        html += '<tr>';
        html += '<td><input type="checkbox" class="response-checkbox" data-id="' + r.id + '"></td>';
        html += '<td>' + r.id + '</td>';
        html += '<td>Utilisateur ' + r.user_id + '</td>';
        html += '<td>Avis #' + r.review_id + '</td>';
        html += '<td style="max-width: 300px;">' + escapeHtml(r.content) + '</td>';
        html += '<td>' + formatDate(r.created_at) + '</td>';
        html += '<td>';
        html += '<button class="btn-edit" onclick="ouvrirModalModificationReponse(' + r.id + ')">✏️ Modifier</button>';
        html += '<button class="btn-delete" onclick="supprimerReponse(' + r.id + ')">🗑️ Supprimer</button>';
        html += '</td>';
        html += '</tr>';
    }
    tbody.innerHTML = html;
    
    const totalPages = Math.ceil(allResponses.length / itemsPerPage);
    document.getElementById('pageInfoResponses').innerHTML = `Page ${currentPageResponses} / ${totalPages || 1}`;
    document.getElementById('prevPageResponses').disabled = currentPageResponses === 1;
    document.getElementById('nextPageResponses').disabled = currentPageResponses === totalPages;
}

// ==================== STATISTIQUES ====================

function calculerStatistiques() {
    const total = allReviews.length;
    let sommeNotes = 0;
    for(let i = 0; i < allReviews.length; i++) {
        sommeNotes += allReviews[i].rating;
    }
    const moyenne = total > 0 ? (sommeNotes / total).toFixed(1) : 0;
    
    const utilisateursUniques = new Set();
    for(let i = 0; i < allReviews.length; i++) {
        utilisateursUniques.add(allReviews[i].user_id);
    }
    
    document.getElementById('totalReviews').innerHTML = total;
    document.getElementById('avgRating').innerHTML = moyenne;
    document.getElementById('totalResponses').innerHTML = allResponses.length;
    document.getElementById('activeUsers').innerHTML = utilisateursUniques.size;
}

// ==================== ACTIONS SUR AVIS ====================

function ouvrirModalModificationAvis(id) {
    const avis = allReviews.find(a => a.id == id);
    if(!avis) return;
    
    document.getElementById('editReviewId').value = avis.id;
    document.getElementById('editRecipeName').value = avis.recipe_name;
    document.getElementById('editUserName').value = 'Utilisateur ' + (avis.user_id || 1);
    document.getElementById('editContent').value = avis.content;
    document.getElementById('editRating').value = avis.rating;
    
    document.getElementById('editModal').style.display = 'flex';
}

function sauvegarderModificationAvis(event) {
    event.preventDefault();
    
    const id = document.getElementById('editReviewId').value;
    const content = document.getElementById('editContent').value;
    const rating = document.getElementById('editRating').value;
    
    fetch(API_URL, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: parseInt(id), content: content, rating: parseInt(rating)})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('✅ Avis modifié avec succès !');
            fermerModalModification();
            chargerAvis();
            chargerReponses();
        } else {
            alert('❌ Erreur: ' + (data.errors || 'Inconnue'));
        }
    });
}

function supprimerAvis(id) {
    if(confirm('Supprimer cet avis ? Toutes les réponses seront aussi supprimées.')) {
        fetch(API_URL + '?id=' + id, {method: 'DELETE'})
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('✅ Avis supprimé avec succès !');
                    chargerAvis();
                    chargerReponses();
                }
            });
    }
}

// ==================== ACTIONS SUR RÉPONSES ====================

function ouvrirModalModificationReponse(id) {
    const reponse = allResponses.find(r => r.id == id);
    if(!reponse) return;
    
    document.getElementById('editReponseId').value = reponse.id;
    document.getElementById('editReponseReviewId').value = 'Avis #' + reponse.review_id;
    document.getElementById('editReponseAuthor').value = 'Utilisateur ' + reponse.user_id;
    document.getElementById('editReponseContent').value = reponse.content;
    
    document.getElementById('editReponseModal').style.display = 'flex';
}

function sauvegarderModificationReponse(event) {
    event.preventDefault();
    
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
            alert('✅ Réponse modifiée avec succès !');
            fermerModalModificationReponse();
            chargerReponses();
        } else {
            alert('❌ Erreur');
        }
    });
}

function supprimerReponse(id) {
    if(confirm('Supprimer cette réponse ?')) {
        fetch(RESPONSE_API_URL + '?id=' + id, {method: 'DELETE'})
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('✅ Réponse supprimée avec succès !');
                    chargerReponses();
                }
            });
    }
}

// ==================== ONGLETS ====================

function showTab(tab) {
    const tabAvis = document.getElementById('tab-avis');
    const tabReponses = document.getElementById('tab-reponses');
    const btns = document.querySelectorAll('.tab-btn');
    
    if(tab === 'avis') {
        tabAvis.style.display = 'block';
        tabReponses.style.display = 'none';
        btns[0].classList.add('active');
        btns[1].classList.remove('active');
        afficherAvis();
    } else {
        tabAvis.style.display = 'none';
        tabReponses.style.display = 'block';
        btns[0].classList.remove('active');
        btns[1].classList.add('active');
        afficherReponses();
    }
}

// ==================== FERMETURE MODALES ====================

function fermerModalModification() {
    document.getElementById('editModal').style.display = 'none';
}

function fermerModalModificationReponse() {
    document.getElementById('editReponseModal').style.display = 'none';
}

// ==================== ÉVÉNEMENTS ====================

function setupEventListeners() {
    // Refresh
    const refreshBtn = document.getElementById('refreshBtn');
    if(refreshBtn) refreshBtn.onclick = () => { currentPage = 1; chargerAvis(); };
    
    const refreshResponsesBtn = document.getElementById('refreshResponsesBtn');
    if(refreshResponsesBtn) refreshResponsesBtn.onclick = () => { currentPageResponses = 1; chargerReponses(); };
    
    // Pagination avis
    const prevPage = document.getElementById('prevPage');
    if(prevPage) prevPage.onclick = () => { if(currentPage > 1) { currentPage--; afficherAvis(); } };
    
    const nextPage = document.getElementById('nextPage');
    if(nextPage) nextPage.onclick = () => {
        const totalPages = Math.ceil(allReviews.length / itemsPerPage);
        if(currentPage < totalPages) { currentPage++; afficherAvis(); }
    };
    
    // Pagination réponses
    const prevPageResponses = document.getElementById('prevPageResponses');
    if(prevPageResponses) prevPageResponses.onclick = () => { if(currentPageResponses > 1) { currentPageResponses--; afficherReponses(); } };
    
    const nextPageResponses = document.getElementById('nextPageResponses');
    if(nextPageResponses) nextPageResponses.onclick = () => {
        const totalPages = Math.ceil(allResponses.length / itemsPerPage);
        if(currentPageResponses < totalPages) { currentPageResponses++; afficherReponses(); }
    };
    
    // Formulaires
    const editForm = document.getElementById('editReviewForm');
    if(editForm) editForm.onsubmit = sauvegarderModificationAvis;
    
    const editReponseForm = document.getElementById('editReponseForm');
    if(editReponseForm) editReponseForm.onsubmit = sauvegarderModificationReponse;
    
    // Fermeture modales
    const closeModal = document.getElementById('closeModal');
    if(closeModal) closeModal.onclick = fermerModalModification;
    
    const cancelModal = document.getElementById('cancelModalBtn');
    if(cancelModal) cancelModal.onclick = fermerModalModification;
    
    const closeReponseModal = document.getElementById('closeReponseModal');
    if(closeReponseModal) closeReponseModal.onclick = fermerModalModificationReponse;
    
    const cancelReponse = document.getElementById('cancelReponseBtn');
    if(cancelReponse) cancelReponse.onclick = fermerModalModificationReponse;
    
    // Fermer en cliquant dehors
    window.onclick = function(event) {
        if(event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
}

// ==================== UTILITAIRES ====================

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