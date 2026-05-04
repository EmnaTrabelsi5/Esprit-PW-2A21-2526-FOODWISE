<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Les Offres';
$activeModule = 'offre';

require_once __DIR__ . '/../../../Model/OffreModel.php';
require __DIR__ . '/../../layouts/back/header.php';

$offreModel = new OffreModel();
$offres = $offreModel->findAll();

$stats = [
    'total' => count($offres),
    'disponible' => 0,
    'epuise' => 0,
    'total_stock' => 0,
];

foreach ($offres as $offre) {
  $ratio = $offre['stock_initial'] > 0 
    ? $offre['stock'] / $offre['stock_initial'] 
    : 0;

$isCritical = $ratio <= 0.1;
    if ($offre['statut'] === 'disponible') {
        $stats['disponible']++;
    } elseif ($offre['statut'] === 'epuise') {
        $stats['epuise']++;
    }
    $stats['total_stock'] += $offre['stock'];
}
?>
<div class="search-bar">
  <form method="GET" action="/FOODWISE1/router/offreAdminRouter.php">
    <input type="hidden" name="action" value="indexAdmin">

    <input 
      type="text" 
      name="search" 
      placeholder="🔎 Rechercher une offre..." 
      value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
    >

    <button type="submit">Rechercher</button>
  </form>
</div>

<div class="page-intro">
  <div class="page-intro__header">
    <div>
      <p class="eyebrow"></p>
      <h1>Liste des Offres</h1>
      <p class="text-muted"></p>
    </div>
    <a href="/FOODWISE1/View/Commande/back/commande_admin.php" class="button button--secondary">Retour aux Commandes</a>
  </div>

  <div class="dashboard-grid">
    <section class="dashboard-card dashboard-card--primary">
      <div class="card-header">
        <h2>Statistiques des offres</h2>
      </div>
      <div class="card-body">
        <div class="stat-row">
          <span>Total offres</span>
          <strong><?= $stats['total'] ?></strong>
        </div>
        <div class="stat-row">
          <span>Offres disponibles</span>
          <strong><?= $stats['disponible'] ?></strong>
        </div>
        <div class="stat-row">
          <span>Offres épuisées</span>
          <strong><?= $stats['epuise'] ?></strong>
        </div>
        <div class="stat-row">
          <span>Stock total</span>
          <strong><?= $stats['total_stock'] ?></strong>
        </div>
      </div>
    </section>

    <section class="dashboard-card dashboard-card--wide">
      <div class="card-header">
        <h2>Liste des offres</h2>
      </div>
      <div class="card-body card-body--table">
        <table class="table-dashboard">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Prix</th>
              <th>Stock</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($offres)): ?>
              <tr>
                <td colspan="5" class="table-empty">Aucune offre disponible.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($offres as $offre): ?>
                <?php
$ratio = $offre['stock_initial'] > 0 
    ? $offre['stock'] / $offre['stock_initial'] 
    : 0;

$isCritical = $ratio <= 0.1;
?>

<tr class="<?= $isCritical ? 'row-critical' : '' ?>">
                  <td><?= htmlspecialchars($offre['titre']) ?></td>
                  <td><?= htmlspecialchars($offre['prix_unitaire']) ?> TND</td>
                  <td>
                    <?= htmlspecialchars($offre['stock']) ?>

                    <?php if ($isCritical): ?>
                    <span class="badge badge--warning">⚠ Stock critique</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge badge--<?= $offre['statut'] === 'disponible' ? 'success' : ($offre['statut'] === 'epuise' ? 'danger' : 'secondary') ?>">
                      <?= ucfirst($offre['statut']) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <a href="/FOODWISE1/router/offreAdminRouter.php?action=show&id=<?= $offre['id'] ?>" class="button button--small button--ghost">Voir</a>
                    <a href="/FOODWISE1/router/offreAdminRouter.php?action=edit&id=<?= $offre['id'] ?>" class="button button--small button--ghost">Éditer</a>
<form method="GET" action="/FOODWISE1/router/offreAdminRouter.php">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" value="<?= $offre['id'] ?>">
  <button class="button button--small button--danger">Supprimer</button>
</form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <aside class="dashboard-card dashboard-card--sidebar">
      <div class="card-header">
        <h2>Notifications</h2>
      </div>
      <div class="card-body">
        <div class="notification notification--info">
          <strong>Offres actives</strong>
          <p><?= $stats['disponible'] ?> offre(s) en vente</p>
        </div>

        <div class="notification notification--warning">
          <strong>Stock faible</strong>
          <?php
            $criticalCount = 0;
            foreach ($offres as $o) {
              $r = $o['stock_initial'] > 0 ? $o['stock'] / $o['stock_initial'] : 0;
              if ($r <= 0.1) $criticalCount++;
            }
          ?>

<p><?= $criticalCount ?> offre(s) en stock critique ⚠</p>
        </div>

        <div class="notification notification--success">
          <strong>Mises à jour</strong>
          <p>Synchronisation automatique activée</p>
        </div>
      </div>
    </aside>

    <section class="dashboard-card dashboard-card--secondary">
      <div class="card-header">
        <h2>Actions rapides</h2>
      </div>
      <div class="card-body">
        <a href="/FOODWISE1/router/offreAdminRouter.php?action=create" class="button button--success" style="width:100%; margin-bottom:12px;">Ajouter une offre</a>
        <a href="/FOODWISE1/router/offreAdminRouter.php?action=index" class="button button--secondary" style="width:100%;">Voir toutes les offres</a>
      </div>
    </section>
  </div>
</div>

<style>

.search-bar {
    margin-bottom: 10px;
}

.search-bar form {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    padding: 10px 14px;
    border-radius: 999px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border: 1px solid rgba(146, 122, 84, 0.2);
    max-width: 420px;
}

.search-bar input {
    border: none;
    outline: none;
    flex: 1;
    font-size: 0.95rem;
    background: transparent;
    padding: 6px;
    color: #333;
}

.search-bar input::placeholder {
    color: #999;
}

.search-bar button {
    border: none;
    background: #7c5528;
    color: #fff;
    padding: 8px 16px;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.search-bar button:hover {
    background: #5e3f1c;
}
.row-critical {
    background: rgba(255, 100, 100, 0.08);
}
.page-intro {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin: 24px 0;
}
.page-intro__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
}
.eyebrow {
    font-size: 0.95rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7a5a2f;
    margin-bottom: 8px;
}
.text-muted {
    color: #6b6b6b;
    margin-top: 10px;
}
.dashboard-grid {
    display: grid;
    grid-template-columns: minmax(260px, 1fr) minmax(520px, 2fr) minmax(240px, 1fr);
    gap: 22px;
    align-items: start;
}
.dashboard-card {
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid rgba(146, 122, 84, 0.12);
}
.card-header {
    padding: 24px;
    background: #7c5528;
    color: #fff;
}
.card-header h2 {
    margin: 0;
    font-size: 1.05rem;
}
.card-body {
    padding: 24px;
}
.card-body--table {
    padding-top: 0;
}
.dashboard-card--primary .card-header,
.dashboard-card--secondary .card-header {
    background: #8f5b33;
}
.dashboard-card--wide {
    grid-column: span 2;
}
.dashboard-card--sidebar {
    display: flex;
    flex-direction: column;
}
.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid rgba(115, 89, 63, 0.12);
}
.stat-row:last-child {
    border-bottom: none;
}
.stat-row strong {
    font-size: 1.4rem;
    color: #2f2f2f;
}
.table-dashboard {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}
.table-dashboard th,
.table-dashboard td {
    padding: 16px 12px;
    text-align: left;
    border-bottom: 1px solid rgba(197, 187, 170, 0.45);
}
.table-dashboard th {
    color: #7a5a2f;
    font-weight: 600;
    font-size: 0.95rem;
}
.table-dashboard tbody tr:hover {
    background: rgba(245, 235, 220, 0.6);
}
.table-empty {
    text-align: center;
    padding: 40px 0;
    color: #7a7a7a;
}
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55em 0.9em;
    border-radius: 999px;
    font-size: 0.85rem;
}
.badge--success { background: #d7f0df; color: #2c6f37; }
.badge--warning { background: #fff1d6; color: #a86b1d; }
.badge--danger { background: #f9d9d7; color: #91372f; }
.badge--secondary { background: #eef0f1; color: #5d666e; }
.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75em 1.1em;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 600;
    transition: transform 0.16s ease, box-shadow 0.16s ease;
}
.button:hover { transform: translateY(-1px); }
.button--secondary { background: #ffffff; color: #7c5528; border: 1px solid #b48b6f; }
.button--success { background: #7fa26f; color: #fff; }
.button--danger { background: transparent; color: #a5483d; border: 1px solid #a5483d; }
.button--ghost { background: transparent; color: #7a5a2f; border: 1px solid rgba(122, 90, 47, 0.25); }
.button--small { padding: 0.55em 0.9em; font-size: 0.85rem; }
.actions-cell {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.notification {
    background: #f8f4eb;
    padding: 18px 18px;
    border-radius: 18px;
    margin-bottom: 16px;
}
.notification--success { background: #e8f5ec; }
.notification--info { background: #eef6fc; }
.notification--warning { background: #fff4e4; }
.notification strong { display: block; margin-bottom: 8px; }
@media (max-width: 1120px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}
</style>

<?php require __DIR__ . '/../../layouts/back/footer.php'; ?>