<?php
$pageTitle = 'Admin — Avis & Réponses';
$activeNav = 'community';
$backoffice = true;
require_once __DIR__ . '/../../layouts/back/header.php';
?>

<style>
/* Styles admin community scopés */
.comm-admin .page-title   { font-size:1.5rem; font-weight:700; margin-bottom:4px; }
.comm-admin .page-subtitle{ color:var(--texte-leger,#777); margin-bottom:20px; font-size:.95rem; }
.comm-admin .stats-grid   { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:20px; }
.comm-admin .stat-card    { background:var(--blanc,#fff); border-radius:var(--radius,10px); padding:20px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.07); }
.comm-admin .stat-icon    { font-size:1.8rem; margin-bottom:6px; }
.comm-admin .stat-number  { font-size:1.8rem; font-weight:800; color:var(--vert-fonce,#2b3627); }
.comm-admin .stat-label   { font-size:.8rem; color:var(--texte-leger,#999); margin-top:4px; }
.comm-admin .tabs-container{ display:flex; gap:8px; margin-bottom:16px; }
.comm-admin .tab-btn      { padding:9px 20px; border-radius:var(--radius-sm,6px); border:none; cursor:pointer; font-weight:600; background:var(--creme-fonce,#f0ebe4); color:var(--texte-sombre,#333); transition:.2s; }
.comm-admin .tab-btn.active{ background:var(--vert,#8FAF87); color:#fff; }
.comm-admin .actions-bar  { margin-bottom:14px; }
.comm-admin .btn-refresh  { padding:8px 16px; background:var(--vert,#8FAF87); color:#fff; border:none; border-radius:var(--radius-sm,6px); cursor:pointer; font-weight:600; }
.comm-admin .reviews-grid,.comm-admin .responses-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; }
.comm-admin .review-card-admin,.comm-admin .response-card-admin { background:var(--blanc,#fff); border-radius:var(--radius,10px); padding:16px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid var(--vert,#8FAF87); }
.comm-admin .card-actions { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
.comm-admin .btn-edit     { padding:6px 12px; background:var(--vert,#8FAF87); color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:.82rem; font-weight:600; }
.comm-admin .btn-delete   { padding:6px 12px; background:#e53e3e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:.82rem; font-weight:600; }
.comm-admin .pagination   { display:flex; align-items:center; gap:12px; margin-top:16px; justify-content:center; }
.comm-admin .page-btn     { padding:7px 16px; background:var(--creme-fonce,#f0ebe4); border:none; border-radius:var(--radius-sm,6px); cursor:pointer; font-weight:600; }
.comm-admin .modal        { display:none; position:fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center; }
.comm-admin .modal-content{ background:var(--blanc,#fff); max-width:500px;width:90%;border-radius:var(--radius,10px);padding:24px; }
.comm-admin .modal-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:16px; }
.comm-admin .modal-close  { cursor:pointer;font-size:1.4rem; }
.comm-admin .form-group   { margin-bottom:14px; }
.comm-admin .form-control { width:100%;padding:9px 12px;border:1px solid var(--border,#ddd);border-radius:var(--radius-sm,6px);font-size:.95rem;box-sizing:border-box; }
.comm-admin .modal-buttons{ display:flex;gap:12px;justify-content:flex-end;margin-top:20px; }
.comm-admin .btn-cancel   { padding:8px 16px;background:var(--creme-fonce,#f0ebe4);border:none;border-radius:var(--radius-sm,6px);cursor:pointer;font-weight:600; }
.comm-admin .btn-save     { padding:8px 16px;background:var(--vert,#8FAF87);color:#fff;border:none;border-radius:var(--radius-sm,6px);cursor:pointer;font-weight:600; }
.comm-admin .stars        { color:#F6B93B; }
</style>

<div class="comm-admin">
  <div class="page-title">📋 Administration des Avis &amp; Réponses</div>
  <div class="page-subtitle">Gérez tous les avis et réponses de la communauté</div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">📝</div><div class="stat-number" id="totalReviews">0</div><div class="stat-label">Total avis</div></div>
    <div class="stat-card"><div class="stat-icon">⭐</div><div class="stat-number" id="avgRating">0.0</div><div class="stat-label">Note moyenne</div></div>
    <div class="stat-card"><div class="stat-icon">💬</div><div class="stat-number" id="totalResponses">0</div><div class="stat-label">Total réponses</div></div>
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-number" id="activeUsers">0</div><div class="stat-label">Auteurs uniques</div></div>
  </div>

  <div class="tabs-container">
    <button class="tab-btn active" onclick="showTab('avis')">📝 Avis</button>
    <button class="tab-btn" onclick="showTab('reponses')">💬 Réponses</button>
  </div>

  <div class="actions-bar">
    <button class="btn-refresh" id="refreshBtn">🔄 Actualiser</button>
  </div>

  <div id="tab-avis" class="tab-content active">
    <div class="reviews-grid" id="reviewsContainer"><div style="text-align:center;padding:40px;">Chargement…</div></div>
    <div class="pagination">
      <button class="page-btn" id="prevPage">◀ Précédent</button>
      <span id="pageInfo">Page 1 / 1</span>
      <button class="page-btn" id="nextPage">Suivant ▶</button>
    </div>
  </div>

  <div id="tab-reponses" class="tab-content" style="display:none;">
    <div class="responses-grid" id="responsesContainer"><div style="text-align:center;padding:40px;">Chargement…</div></div>
    <div class="pagination">
      <button class="page-btn" id="prevPageResponses">◀ Précédent</button>
      <span id="pageInfoResponses">Page 1 / 1</span>
      <button class="page-btn" id="nextPageResponses">Suivant ▶</button>
    </div>
  </div>

  <!-- MODAL AVIS -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><h3>✏️ Modifier l'avis</h3><span class="modal-close" id="closeModal">&times;</span></div>
      <input type="hidden" id="editReviewId">
      <div class="form-group"><label>Recette</label><input type="text" id="editRecipeName" class="form-control" readonly></div>
      <div class="form-group"><label>Avis</label><textarea id="editContent" rows="4" class="form-control" required></textarea></div>
      <div class="form-group">
        <label>Note</label>
        <select id="editRating" class="form-control">
          <option value="1">⭐ 1</option><option value="2">⭐⭐ 2</option><option value="3">⭐⭐⭐ 3</option>
          <option value="4">⭐⭐⭐⭐ 4</option><option value="5">⭐⭐⭐⭐⭐ 5</option>
        </select>
      </div>
      <div class="modal-buttons">
        <button type="button" class="btn-cancel" id="cancelModalBtn">Annuler</button>
        <button type="button" class="btn-save" id="saveEditReviewAdminBtn">Enregistrer</button>
      </div>
    </div>
  </div>

  <!-- MODAL RÉPONSE -->
  <div id="editReponseModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><h3>✏️ Modifier la réponse</h3><span class="modal-close" id="closeReponseModal">&times;</span></div>
      <input type="hidden" id="editReponseId">
      <div class="form-group"><label>Réponse</label><textarea id="editReponseContent" rows="4" class="form-control" required></textarea></div>
      <div class="modal-buttons">
        <button type="button" class="btn-cancel" id="cancelReponseBtn">Annuler</button>
        <button type="button" class="btn-save" id="saveEditReponseAdminBtn">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<script src="/FOODWISE/assets/community_admin.js"></script>

<?php require_once __DIR__ . '/../../layouts/back/footer.php'; ?>
