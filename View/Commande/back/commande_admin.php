<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Commandes & Paiements';
$activeModule = 'offre';

require_once __DIR__ . '/../../../Model/CommandeModel.php';
require __DIR__ . '/../../layouts/back/header.php';

$commandes = CommandeModel::getAll();

$stats = [
    'total' => 0,
    'confirme' => 0,
    'annule' => 0,
    'en_attente' => 0,
    'paye_dernieres_24h' => 0,
];

foreach ($commandes as $commande) {
    $stats['total']++;
    if ($commande['statut'] === 'confirme') {
        $stats['confirme']++;
    } elseif ($commande['statut'] === 'annule') {
        $stats['annule']++;
    } elseif ($commande['statut'] === 'en_attente') {
        $stats['en_attente']++;
    }

    if ($commande['paiement_status'] === 'paye' && strtotime($commande['date_commande']) >= strtotime('-24 hours')) {
        $stats['paye_dernieres_24h']++;
    }
}

$pendingOrders = $stats['en_attente'];
$validatedPayments = $stats['paye_dernieres_24h'];
?>

<div class="page-intro">
  <div class="page-intro__header">
    <div>
      <p class="eyebrow"></p>
      <h1>Commandes & Paiements</h1>
      <p class="text-muted"></p>
    </div>
    <a href="../../Offre/back/detail_offre.php" class="button button--secondary">Retour aux offres</a>
  </div>

  <div class="dashboard-grid">
    <section class="dashboard-card dashboard-card--primary">
      <div class="card-header">
        <h2>Statistiques des commandes</h2>
      </div>
      <div class="card-body">
        <div class="stat-row">
          <span>Total commandes</span>
          <strong><?= $stats['total'] ?></strong>
        </div>
        <div class="stat-row">
          <span>Commandes confirmées</span>
          <strong><?= $stats['confirme'] ?></strong>
        </div>
        <div class="stat-row">
          <span>Commandes annulées</span>
          <strong><?= $stats['annule'] ?></strong>
        </div>
      </div>
    </section>

    <section class="dashboard-card dashboard-card--wide">
      <div class="card-header">
        <h2>Liste des commandes</h2>
      </div>
      <div class="card-body card-body--table">
        <table class="table-dashboard">
          <thead>
            <tr>
              <th>ID</th>
              <th>Offre</th>
              <th>Quantité</th>
              <th>Statut</th>
              <th>Paiement</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($commandes)): ?>
              <tr>
                <td colspan="7" class="table-empty">Aucune commande pour le moment.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($commandes as $commande): ?>
                <tr>
                  <td>#<?= htmlspecialchars($commande['id_commande']) ?></td>
                  <td><?= htmlspecialchars($commande['offre_nom']) ?></td>
                  <td><?= htmlspecialchars($commande['quantite']) ?></td>
                  <td>
                    <span class="badge badge--<?= $commande['statut'] === 'confirme' ? 'success' : ($commande['statut'] === 'annule' ? 'danger' : 'warning') ?>">
                      <?= $commande['statut'] === 'confirme' ? 'Confirmé' : ($commande['statut'] === 'annule' ? 'Annulé' : 'En attente') ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge--<?= $commande['paiement_status'] === 'paye' ? 'success' : 'secondary' ?>">
                      <?= $commande['paiement_status'] === 'paye' ? 'Payé' : 'Non payé' ?>
                    </span>
                  </td>
                  <td><?= date('Y-m-d H:i', strtotime($commande['date_commande'])) ?></td>
                  <td class="actions-cell">
                    <?php if ($commande['statut'] === 'en_attente'): ?>
                      <a href="commande.php?action=adminUpdate&id=<?= $commande['id_commande'] ?>&status=confirme" class="button button--small button--success">Confirmer</a>
                      <a href="commande.php?action=adminUpdate&id=<?= $commande['id_commande'] ?>&status=annule" class="button button--small button--ghost button--danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">Annuler</a>
                    <?php else: ?>
                      <span class="text-muted">Aucune action</span>
                    <?php endif; ?>
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
          <strong>Commandes en attente</strong>
          <p><?= $pendingOrders ?> commande(s) à traiter</p>
        </div>

        <div class="notification notification--success">
          <strong>Paiements validés</strong>
          <p><?= $validatedPayments ?> sur les dernières 24 h</p>
        </div>

        <div class="notification notification--warning">
          <strong>Alerte système</strong>
          <p>Synchronisation stock OK</p>
        </div>

        <div class="notification notification--warning">
          <strong>Alerte système</strong>
          <p>API paiement simulée disponible</p>
        </div>
      </div>
    </aside>

    <section class="dashboard-card dashboard-card--secondary">
      <div class="card-header">
        <h2>Alertes de stock</h2>
      </div>
      <div class="card-body">
        <ul class="alert-list">
          <li>Produit A : stock critique (5)</li>
          <li>Produit B : indisponible</li>
        </ul>
      </div>
    </section>
  </div>
</div>

<style>
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
.alert-list {
    list-style: disc;
    padding-left: 20px;
    margin: 0;
}
.alert-list li {
    margin-bottom: 10px;
    color: #50412a;
}
@media (max-width: 1120px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}
</style>

<?php require __DIR__ . '/../../layouts/back/footer.php'; ?>
