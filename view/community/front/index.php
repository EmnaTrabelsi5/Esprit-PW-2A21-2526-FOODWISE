<?php
$pageTitle = 'Communauté · Avis & Réponses';
$activeNav = 'community';
require_once __DIR__ . '/../../layouts/front/header.php';
?>

<style>
.comm-wrap { color: var(--texte-sombre, #2A1A0A); }
.comm-wrap .page-title { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: var(--brun-fonce, #4E2C0E); margin-bottom: 4px; }
.comm-wrap .page-subtitle { font-size: 14px; color: var(--texte-leger, #9B7355); margin-bottom: 24px; }
.comm-wrap .card { background: var(--blanc, #fff); border-radius: var(--radius, 10px); box-shadow: var(--shadow, 0 2px 12px rgba(78,44,14,.1)); padding: 24px; margin-bottom: 22px; }
.comm-wrap .card-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--creme-fonce, #F5E9D0); }
.comm-wrap .card-title { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: var(--brun-fonce, #4E2C0E); }
.comm-wrap .card-subtitle { color: var(--texte-leger, #9B7355); font-size: 13px; }
.comm-wrap .form-group { margin-bottom: 18px; }
.comm-wrap .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--texte-moyen, #5C3D20); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
.comm-wrap .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--brun-pale, #E8C9A0); border-radius: var(--radius, 10px); font-size: 14px; background: var(--creme, #FDF6EC); color: var(--texte-sombre, #2A1A0A); outline: none; }
.comm-wrap .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; border-radius: 25px; font-size: 14px; font-weight: 700; cursor: pointer; border: none; text-decoration: none; }
.comm-wrap .btn-sm { padding: 5px 14px; font-size: 13px; }
.comm-wrap .btn-primary { background: var(--brun-chaud, #A0522D); color: var(--creme, #FDF6EC); }
.comm-wrap .btn-outline { background: transparent; color: var(--brun-chaud, #A0522D); border: 2px solid var(--brun-chaud, #A0522D); }
.comm-wrap .btn-danger { background: var(--alerte-rouge, #C0392B); color: #fff; }
.comm-wrap .fw-table { width: 100%; border-collapse: collapse; }
.comm-wrap .fw-table th { background: var(--creme-fonce, #F5E9D0); color: var(--brun-moyen, #7B3F1A); font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 10px 16px; text-align: left; }
.comm-wrap .fw-table td { padding: 12px 16px; border-bottom: 1px solid var(--creme-fonce, #F5E9D0); font-size: 14px; }

.comm-wrap .reviews-grid { display: flex; flex-direction: column; gap: 20px; }
.comm-wrap .review-card { background: var(--blanc, #fff); border-radius: var(--radius, 10px); padding: 20px; box-shadow: var(--shadow, 0 2px 12px rgba(78,44,14,.1)); border: 1px solid rgba(78,44,14,.08); }
.comm-wrap .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
.comm-wrap .recipe-name { font-size: 18px; font-weight: 700; color: var(--brun-fonce, #4E2C0E); }
.comm-wrap .rating { font-size: 16px; letter-spacing: 2px; color: #FFD700; }
.comm-wrap .review-title { font-size: 14px; font-weight: 600; color: var(--brun-chaud, #A0522D); margin: 10px 0; font-style: italic; }
.comm-wrap .review-meta { display: flex; gap: 20px; font-size: 12px; color: var(--texte-leger, #9B7355); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--creme-fonce, #F5E9D0); }
.comm-wrap .review-content { background: var(--creme, #FDF6EC); padding: 15px; border-radius: var(--radius, 10px); font-size: 14px; line-height: 1.6; margin-bottom: 15px; font-style: italic; }
.comm-wrap .review-stats { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--creme-fonce, #F5E9D0); font-size: 13px; color: var(--texte-moyen, #5C3D20); }
.comm-wrap .review-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.comm-wrap .responses-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--creme-fonce, #F5E9D0); }
.comm-wrap .responses-title { font-size: 13px; font-weight: 600; color: var(--brun-moyen, #7B3F1A); margin-bottom: 12px; }

.comm-wrap .response-card { border-radius: 16px; padding: 14px 16px; margin-bottom: 12px; background: var(--creme-fonce, #F5E9D0); }
.comm-wrap .response-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
.comm-wrap .response-author { font-weight: 700; color: var(--brun-fonce, #4E2C0E); font-size: 14px; }
.comm-wrap .response-date { font-size: 11px; color: var(--texte-leger, #9B7355); }
.comm-wrap .response-content { font-size: 14px; color: var(--texte-sombre, #2A1A0A); margin-bottom: 12px; line-height: 1.4; }
.comm-wrap .response-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
.comm-wrap .response-reactions { display: flex; gap: 8px; }
.comm-wrap .reaction-btn { background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 30px; font-size: 13px; color: #666; }
.comm-wrap .like-btn:hover, .comm-wrap .like-btn.active { background: #e8f5e9; color: #2e7d32; }
.comm-wrap .dislike-btn:hover, .comm-wrap .dislike-btn.active { background: #fdeaea; color: #c62828; }
.comm-wrap .btn-repondre-reponse { background: none; border: none; color: var(--vert-moyen, #5C7A3E); cursor: pointer; font-size: 12px; padding: 6px 12px; border-radius: 30px; }
.comm-wrap .btn-modifier { background: none; border: none; color: var(--brun-chaud, #A0522D); cursor: pointer; padding: 6px 12px; border-radius: 30px; }
.comm-wrap .btn-supprimer { background: none; border: none; color: var(--alerte-rouge, #C0392B); cursor: pointer; padding: 6px 12px; border-radius: 30px; }
.comm-wrap .responses-children { margin-top: 10px; }

.comm-wrap .top-reviews-grid { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
.comm-wrap .top-review-card { flex: 1; min-width: 250px; background: linear-gradient(135deg, #fff8f0 0%, #fff0e0 100%); border-radius: var(--radius, 10px); padding: 20px; border: 1px solid rgba(160,82,45,.2); }
.comm-wrap .top-review-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--creme-fonce, #F5E9D0); font-size: 12px; }

.comm-wrap .trends-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.comm-wrap .trend-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--brun-pale, #E8C9A0); border-radius: 12px; background: var(--creme, #FDF6EC); }
.comm-wrap .trend-main { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
.comm-wrap .trend-rank { width: 28px; height: 28px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: var(--brun-fonce, #4E2C0E); background: var(--creme-fonce, #F5E9D0); }
.comm-wrap .trend-hashtag { font-weight: 800; color: var(--brun-fonce, #4E2C0E); }
.comm-wrap .trend-count { font-size: 13px; color: var(--texte-moyen, #5C3D20); }

.comm-wrap .mention-badge { display: inline-block; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 4px; padding: 0 5px; font-weight: 600; font-size: .88em; }
.comm-wrap .review-private-views { display: inline-flex; align-items: center; gap: 4px; margin-left: auto; font-size: 13px; color: var(--texte-moyen, #5C3D20); }
.comm-wrap .review-private-views__count { display: inline-flex; align-items: center; justify-content: center; min-width: 1.5em; padding: 2px 8px; font-weight: 800; color: var(--brun-fonce, #4E2C0E); background: var(--creme, #FDF6EC); border-radius: 8px; border: 1px solid rgba(160,82,45,.25); }
</style>

<div class="comm-wrap">
  <div class="page-title">Communauté · Avis &amp; Réponses</div>
  <div class="page-subtitle">Partagez votre expérience, répondez aux avis</div>

  <div class="card" id="advancedSearchCard">
    <div class="card-header">
      <div class="card-title">🔍 Recherche avancée</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="button" class="btn btn-outline btn-sm" id="advSearchResetBtn">↺ Réinitialiser</button>
        <button type="button" class="btn btn-primary btn-sm" id="advSearchApplyBtn">Appliquer</button>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">🔎 Texte</label>
      <input type="text" class="form-control" id="advSearchText" placeholder="Recette, titre, contenu...">
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
      <div class="form-group" style="margin:0;"><label class="form-label">📅 Période</label><select class="form-control" id="advSearchSince"><option value="all" selected>Tout le temps</option><option value="day">Dernières 24h</option><option value="week">Dernière semaine</option><option value="month">Dernier mois</option><option value="year">Dernière année</option></select></div>
      <div class="form-group" style="margin:0;"><label class="form-label">⭐ Note minimum</label><select class="form-control" id="advSearchMinRating"><option value="0" selected>Toutes</option><option value="4">4+ étoiles</option><option value="5">5 étoiles</option></select></div>
      <div class="form-group" style="margin:0;"><label class="form-label">📝 Réponses minimum</label><select class="form-control" id="advSearchMinResponses"><option value="0" selected>0+</option><option value="1">1+</option><option value="3">3+</option><option value="5">5+</option><option value="10">10+</option></select></div>
      <div class="form-group" style="margin:0;"><label class="form-label">👍 Likes minimum</label><select class="form-control" id="advSearchMinLikes"><option value="0" selected>0+</option><option value="1">1+</option><option value="5">5+</option><option value="10">10+</option><option value="25">25+</option></select></div>
    </div>
  </div>

  <div class="card" id="trendsCard">
    <div class="card-header"><div class="card-title">🔥 Tendances du jour</div><div class="card-subtitle">Recettes les plus discutées</div></div>
    <ol id="trendsList" class="trends-list"><li>Chargement des tendances...</li></ol>
  </div>

  <div class="card" id="topReviewsCard">
    <div class="card-header"><div class="card-title">🏆 Top 3 des avis les plus likés</div><div class="card-subtitle">Les avis préférés de la communauté</div></div>
    <div id="topReviewsContainer" class="top-reviews-grid"><div style="text-align:center;padding:20px;">Chargement du top 3...</div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">📋 Les Avis de la communauté</div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="reviewSortSelect" class="form-control" style="width:auto;padding:7px 12px;"><option value="date">🕒 Plus récents</option><option value="likes">❤️ Plus likés</option><option value="responses">💬 Plus commentés</option></select>
        <button class="btn btn-primary btn-sm" id="showAddReviewBtn">+ Nouvel avis</button>
      </div>
    </div>

    <div id="addReviewForm" style="display:none;margin-bottom:24px;padding:20px;background:var(--creme-fonce,#F5E9D0);border-radius:var(--radius,10px);">
      <div id="formErrors" style="display:none;"></div>
      <form id="reviewForm">
        <div class="form-group"><label class="form-label">🍽️ RECETTE *</label><select class="form-control" id="recipeSelect" required><option value="">Chargement des recettes...</option></select></div>
        <div class="form-group"><label class="form-label">📌 TITRE (facultatif)</label><input type="text" class="form-control" id="reviewTitle" placeholder="Ex: Un délice !" maxlength="100"><div style="display:flex;justify-content:flex-end;margin-top:5px;"><small><span id="titleCharCount">0</span> / 100</small></div></div>
        <div class="form-group"><label class="form-label">✍️ VOTRE AVIS *</label><textarea class="form-control" id="reviewText" rows="4" placeholder="Partagez votre expérience... (5 à 500 caractères)" required></textarea><div style="display:flex;justify-content:flex-end;margin-top:5px;"><small><span id="reviewCharCount">0</span> / 500</small></div></div>
        <div class="form-group"><label class="form-label">⭐ NOTE *</label><div class="star-rating" id="starRating" style="display:flex;gap:10px;font-size:30px;cursor:pointer;"><span data-rating="1">☆</span><span data-rating="2">☆</span><span data-rating="3">☆</span><span data-rating="4">☆</span><span data-rating="5">☆</span></div><input type="hidden" id="ratingValue" value="0"></div>
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;"><button type="button" class="btn btn-outline" id="cancelReviewBtn">Annuler</button><button type="submit" class="btn btn-primary">📤 Publier l'avis</button></div>
      </form>
    </div>

    <div id="reviewsContainer"><div style="text-align:center;padding:20px;">Chargement des avis...</div></div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">💬 Mes Réponses</div></div>
    <table class="fw-table"><thead><tr><th>Date</th><th>Avis concerné</th><th>Ma Réponse</th><th>Actions</th></tr></thead><tbody id="responsesList"><tr><td colspan="4">Chargement...</td></tr></tbody></table>
  </div>

  <div class="card"><div class="card-header"><div class="card-title">🔥 Top Recettes Populaires</div></div><div class="recipe-grid" id="popularRecipesGrid"><div style="text-align:center;padding:20px;">Chargement...</div></div></div>
  <div class="card"><div class="card-header"><div class="card-title">🔔 Notifications</div></div><div class="notif-panel" id="notificationsList"><div class="notif-item">💬 Bienvenue sur la communauté !</div></div></div>
</div>

<div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--blanc,#fff);max-width:500px;width:90%;border-radius:var(--radius,10px);padding:24px;">
    <h3>✏️ Modifier mon avis</h3>
    <form id="editReviewForm">
      <input type="hidden" id="editReviewId">
      <div class="form-group"><label>Titre</label><input type="text" class="form-control" id="editReviewTitle"></div>
      <div class="form-group"><label>Avis</label><textarea class="form-control" id="editReviewText" rows="3" required></textarea></div>
      <div class="form-group"><label>Note</label><select class="form-control" id="editReviewRating"><option value="1">⭐ 1 étoile</option><option value="2">⭐⭐ 2 étoiles</option><option value="3">⭐⭐⭐ 3 étoiles</option><option value="4">⭐⭐⭐⭐ 4 étoiles</option><option value="5">⭐⭐⭐⭐⭐ 5 étoiles</option></select></div>
      <div style="display:flex;gap:12px;justify-content:flex-end;"><button type="button" class="btn btn-outline" id="closeModalBtn">Annuler</button><button type="submit" class="btn btn-primary">Enregistrer</button></div>
    </form>
  </div>
</div>

<div id="reponseModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--blanc,#fff);max-width:500px;width:90%;border-radius:var(--radius,10px);padding:24px;">
    <h3>💬 Répondre</h3>
    <div id="reponseErrors" style="display:none;"></div>
    <form id="reponseForm">
      <input type="hidden" id="reponseParentId" value="">
      <input type="hidden" id="reponseReviewId">
      <div class="form-group"><label class="form-label">Avis concerné</label><textarea id="reponseOriginalAvis" class="form-control" rows="2" readonly style="background:var(--creme-fonce,#F5E9D0);"></textarea></div>
      <div class="form-group"><label class="form-label">Votre réponse</label><textarea class="form-control" id="reponseContent" rows="3" placeholder="2 à 300 caractères" required></textarea><div style="text-align:right;margin-top:5px;"><small><span id="reponseCharCount">0</span> / 300</small></div></div>
      <div style="display:flex;gap:12px;justify-content:flex-end;"><button type="button" class="btn btn-outline" id="closeReponseBtn">Annuler</button><button type="submit" class="btn btn-primary">Publier</button></div>
    </form>
  </div>
</div>

<div id="editReponseModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--blanc,#fff);max-width:500px;width:90%;border-radius:var(--radius,10px);padding:24px;">
    <h3>✏️ Modifier réponse</h3>
    <form id="editReponseForm">
      <input type="hidden" id="editReponseId">
      <div class="form-group"><label>Réponse</label><textarea class="form-control" id="editReponseContent" rows="3" required></textarea></div>
      <div style="display:flex;gap:12px;justify-content:flex-end;"><button type="button" class="btn btn-outline" id="closeEditReponseBtn">Annuler</button><button type="submit" class="btn btn-primary">Enregistrer</button></div>
    </form>
  </div>
</div>

<script>
const FOODWISE_USER_ID = <?= (int)($_SESSION['user_id'] ?? 1) ?>;
</script>
<script src="/FOODWISE/assets/community_reviewViews.js"></script>
<script src="/FOODWISE/assets/community_main.js"></script>
<script src="/FOODWISE/assets/community_notifications.js"></script>

<?php require_once __DIR__ . '/../../layouts/front/footer.php'; ?>
