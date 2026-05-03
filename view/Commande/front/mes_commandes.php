<?php require __DIR__ . '/../../layouts/front/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mes commandes</h2>
                <a href="?route=offreRouter.php?action=index" class="btn btn-primary">Voir les offres</a>
            </div>

            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['flash_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['flash_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($commandes)): ?>
                <div class="alert alert-info">
                    <h5>Vous n'avez pas encore de commandes</h5>
                    <p>Découvrez nos offres et passez votre première commande !</p>
                    <a href="?route=offreRouter.php?action=index" class="btn btn-primary">Voir les offres</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($commandes as $commande): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($commande['offre_nom']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Quantité:</strong> <?= $commande['quantite'] ?>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Statut:</strong>
                                        <span class="badge
                                            <?php
                                            switch($commande['statut']) {
                                                case 'en_attente': echo 'bg-warning text-dark'; break;
                                                case 'confirme': echo 'bg-success'; break;
                                                case 'annule': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?php
                                            switch($commande['statut']) {
                                                case 'en_attente': echo 'En attente'; break;
                                                case 'confirme': echo 'Confirmée'; break;
                                                case 'annule': echo 'Annulée'; break;
                                                default: echo ucfirst($commande['statut']);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Paiement:</strong>
                                        <span class="badge
                                            <?php
                                            switch($commande['paiement_status']) {
                                                case 'non_paye': echo 'bg-warning text-dark'; break;
                                                case 'paye': echo 'bg-success'; break;
                                                default: echo 'bg-secondary';
                                            }
                                            ?>">
                                            <?= $commande['paiement_status'] === 'paye' ? 'Payé' : 'Non payé' ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            Commandé le: <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <?php if ($commande['statut'] === 'en_attente'): ?>
                                        <div class="d-grid gap-2">
                                            <a href="?route=commandeRouter.php?action=edit&id=<?= $commande['id_commande'] ?>"
                                               class="btn btn-warning btn-sm">?? Modifier</a>
                                            <?php if ($commande['paiement_status'] === 'non_paye'): ?>
                                                <a href="?route=commandeRouter.php?action=pay&id=<?= $commande['id_commande'] ?>"
                                                   class="btn btn-success btn-sm">?? Payer</a>
                                            <?php endif; ?>
                                            <a href="?route=commandeRouter.php?action=cancel&id=<?= $commande['id_commande'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">? Annuler</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>
