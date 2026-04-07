<?php
/**
 * Vue administration — SmartCart : commandes & paiements (données statiques).
 */
$pageTitle = 'Module 6 – SmartCart – Commandes & Paiements | FoodWise';
$activeNav = 'commandes';
$cssHref = '../../../public/css/smartcart.css';

$statTotalCommandes = 1980;
$statConfirmees = 1450;
$statAnnulees = 128;

$commandes = [
    ['id' => 401, 'client' => 'Hassan M.', 'date' => '2026-04-02', 'montant' => 65.00, 'statut' => 'en attente'],
    ['id' => 402, 'client' => 'Sara L.', 'date' => '2026-04-03', 'montant' => 42.50, 'statut' => 'confirmé'],
    ['id' => 403, 'client' => 'Youssef K.', 'date' => '2026-04-04', 'montant' => 118.90, 'statut' => 'en attente'],
    ['id' => 404, 'client' => 'Amira B.', 'date' => '2026-04-05', 'montant' => 33.00, 'statut' => 'annulé'],
    ['id' => 405, 'client' => 'Omar T.', 'date' => '2026-04-05', 'montant' => 89.20, 'statut' => 'confirmé'],
];

$paiements = [
    ['id' => 'P-9001', 'commande_id' => 402, 'montant' => 42.50, 'methode' => 'Carte', 'statut' => 'validé'],
    ['id' => 'P-9002', 'commande_id' => 405, 'montant' => 89.20, 'methode' => 'Cash', 'statut' => 'validé'],
    ['id' => 'P-9003', 'commande_id' => 401, 'montant' => 65.00, 'methode' => 'Carte', 'statut' => 'en attente'],
    ['id' => 'P-9004', 'commande_id' => 404, 'montant' => 33.00, 'methode' => 'Carte', 'statut' => 'annulé'],
];

$notifAttente = 3;
$notifPaiementsValides = 12;
$alertesSysteme = ['Synchronisation stock OK', 'API paiement simulée disponible'];

include __DIR__ . '/layouts/header.php';
include __DIR__ . '/layouts/sidebar.php';
?>
<div class="main-content">
    <header class="topbar">
        <div class="topbar-search">
            <label class="visually-hidden" for="search-admin">Recherche</label>
            <input type="search" id="search-admin" name="q" placeholder="Rechercher commandes, clients…" autocomplete="off">
            <button type="button">Rechercher</button>
        </div>
        <div class="topbar-user">
            <span>Admin</span>
        </div>
    </header>

    <div class="page-body">
        <h1 class="page-title">Module 6 – SmartCart – Commandes &amp; Paiements</h1>
        <p class="page-subtitle">Données de démonstration — module Commandes &amp; Paiement.</p>

        <div class="admin-grid">
            <div class="col-stats stack-gap">
                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">📈</span> Statistiques des commandes</div>
                    <div class="card-body">
                        <div class="stat-row"><span><span class="stat-ico">📦</span> Total commandes</span><strong><?php echo number_format($statTotalCommandes, 0, ',', ' '); ?></strong></div>
                        <div class="stat-row"><span><span class="stat-ico">✓</span> Commandes confirmées</span><strong><?php echo number_format($statConfirmees, 0, ',', ' '); ?></strong></div>
                        <div class="stat-row"><span><span class="stat-ico">✕</span> Commandes annulées</span><strong><?php echo number_format($statAnnulees, 0, ',', ' '); ?></strong></div>
                    </div>
                </article>
                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">⚠️</span> Alertes de stock</div>
                    <div class="card-body">
                        <ul class="alert-list">
                            <li>Produit A : stock critique (5)</li>
                            <li>Produit B : indisponible</li>
                        </ul>
                    </div>
                </article>
            </div>

            <div class="col-main stack-gap">
                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">📋</span> Liste des commandes</div>
                    <div class="card-body table-wrap">
                        <table class="fw-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commandes as $c) :
                                    $st = $c['statut'];
                                    $badgeClass = $st === 'confirmé' ? 'badge-vert' : ($st === 'annulé' ? 'badge-rouge' : 'badge-orange');
                                    ?>
                                    <tr>
                                        <td>#<?php echo (int) $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['client'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($c['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format($c['montant'], 2, ',', ' '); ?> €</td>
                                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="actions-cell">
                                            <button type="button" class="btn btn-primary btn-sm">Confirmer</button>
                                            <button type="button" class="btn btn-outline btn-sm">Annuler</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">💳</span> Paiements</div>
                    <div class="card-body table-wrap">
                        <table class="fw-table">
                            <thead>
                                <tr>
                                    <th>ID paiement</th>
                                    <th>Commande associée</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiements as $p) :
                                    $ps = $p['statut'];
                                    $pb = $ps === 'validé' ? 'badge-vert' : ($ps === 'annulé' ? 'badge-rouge' : 'badge-orange');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>#<?php echo (int) $p['commande_id']; ?></td>
                                        <td><?php echo number_format($p['montant'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo htmlspecialchars($p['methode'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge <?php echo $pb; ?>"><?php echo htmlspecialchars($ps, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="col-notifications stack-gap">
                <article class="card">
                    <div class="sc-card-bar sc-card-bar--vert"><span aria-hidden="true">🔔</span> Notifications</div>
                    <div class="card-body notif-list">
                        <div class="notif-item notif-mild">
                            <span aria-hidden="true">⏳</span>
                            <div><strong>Commandes en attente</strong><br><?php echo (int) $notifAttente; ?> commande(s) à traiter</div>
                        </div>
                        <div class="notif-item notif-mild">
                            <span aria-hidden="true">✓</span>
                            <div><strong>Paiements validés</strong><br><?php echo (int) $notifPaiementsValides; ?> sur les dernières 24 h</div>
                        </div>
                        <?php foreach ($alertesSysteme as $msg) : ?>
                            <div class="notif-item notif-warn">
                                <span aria-hidden="true">⚠️</span>
                                <div><strong>Alerte système</strong><br><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
