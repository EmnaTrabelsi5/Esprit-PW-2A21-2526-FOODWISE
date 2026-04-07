<?php
/**
 * Vue client — SmartCart : historique, panier, paiement (données statiques).
 */
$pageTitle = 'SmartCart — Commandes & Paiement | FoodWise';
$activeNav = 'smartcart';
$cssHref = '../../../public/css/smartcart.css';

$totalAchats = 24;
$derniereCommande = '#401 — 2 avr. 2026';
$statutGlobal = 'Bon client — paiements à jour';

$historique = [
    ['ref' => '#398', 'date' => '28 mars 2026', 'articles' => 4, 'montant' => 72.40, 'paiement' => 'Carte', 'statut' => 'confirmé'],
    ['ref' => '#401', 'date' => '2 avr. 2026', 'articles' => 3, 'montant' => 65.00, 'paiement' => 'Carte', 'statut' => 'en attente'],
    ['ref' => '#395', 'date' => '15 mars 2026', 'articles' => 2, 'montant' => 38.00, 'paiement' => 'Cash', 'statut' => 'confirmé'],
];

$panier = [
    ['nom' => 'Panier équilibre semaine', 'qty' => 1, 'prix' => 45.00],
    ['nom' => 'Smoothie protéiné', 'qty' => 2, 'prix' => 6.50],
];
$panierTotal = array_sum(array_map(function ($l) {
    return $l['qty'] * $l['prix'];
}, $panier));

include __DIR__ . '/layouts/header.php';
include __DIR__ . '/layouts/sidebar.php';
?>
<div class="main-content">
    <header class="topbar">
        <div class="topbar-search">
            <label class="visually-hidden" for="search-front">Recherche</label>
            <input type="search" id="search-front" placeholder="Rechercher une commande…" autocomplete="off">
            <button type="button">Rechercher</button>
        </div>
        <div class="topbar-user">
            <span>Client</span>
        </div>
    </header>

    <div class="page-hero">
        <h1 class="page-title">SmartCart — Mes commandes &amp; paiement</h1>
        <p class="page-subtitle">Gérez votre historique, votre panier et simulez un paiement.</p>
    </div>

    <div class="page-body">
        <section class="summary-cards" aria-label="Résumé">
            <article class="summary-card">
                <h3>Total achats</h3>
                <p class="big"><?php echo (int) $totalAchats; ?></p>
                <span class="summary-card-hint">commandes enregistrées</span>
            </article>
            <article class="summary-card">
                <h3>Dernière commande</h3>
                <p class="big summary-card-ref"><?php echo htmlspecialchars($derniereCommande, ENT_QUOTES, 'UTF-8'); ?></p>
                <span class="summary-card-hint">référence &amp; date</span>
            </article>
            <article class="summary-card">
                <h3>Statut global</h3>
                <p class="big summary-card-status"><?php echo htmlspecialchars($statutGlobal, ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        </section>

        <div class="front-grid">
            <div class="stack-gap">
                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">📜</span> Historique des commandes</div>
                    <div class="card-body table-wrap">
                        <table class="fw-table">
                            <thead>
                                <tr>
                                    <th>Réf.</th>
                                    <th>Date</th>
                                    <th>Articles</th>
                                    <th>Montant</th>
                                    <th>Mode paiement</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historique as $h) :
                                    $hs = $h['statut'];
                                    $hb = $hs === 'confirmé' ? 'badge-vert' : 'badge-orange';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($h['ref'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($h['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) $h['articles']; ?></td>
                                        <td><?php echo number_format($h['montant'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo htmlspecialchars($h['paiement'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge <?php echo $hb; ?>"><?php echo htmlspecialchars($hs, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="stack-gap">
                <article class="card">
                    <div class="sc-card-bar"><span aria-hidden="true">🧺</span> Panier</div>
                    <div class="card-body">
                        <ul class="cart-list">
                            <?php foreach ($panier as $ligne) :
                                $sous = $ligne['qty'] * $ligne['prix'];
                                ?>
                                <li>
                                    <span><?php echo htmlspecialchars($ligne['nom'], ENT_QUOTES, 'UTF-8'); ?> × <?php echo (int) $ligne['qty']; ?></span>
                                    <strong><?php echo number_format($sous, 2, ',', ' '); ?> €</strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="cart-total">
                            <span>Total</span>
                            <span><?php echo number_format($panierTotal, 2, ',', ' '); ?> €</span>
                        </div>
                    </div>
                </article>

                <article class="card">
                    <div class="sc-card-bar sc-card-bar--vert"><span aria-hidden="true">💳</span> Paiement (simulation)</div>
                    <div class="card-body">
                        <form class="payment-form" action="#" method="post" onsubmit="return false;">
                            <div class="form-group">
                                <label class="form-label" for="methode-paiement">Méthode de paiement</label>
                                <select class="form-control" id="methode-paiement" name="methode">
                                    <option value="carte">Carte bancaire</option>
                                    <option value="cash">Espèces à la livraison</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-pay">Payer</button>
                            <p class="payment-form-note">Aucun prélèvement réel — démonstration module SmartCart.</p>
                        </form>
                    </div>
                </article>
            </div>
        </div>
    </div><!-- /.page-body -->

<?php include __DIR__ . '/layouts/footer.php'; ?>
