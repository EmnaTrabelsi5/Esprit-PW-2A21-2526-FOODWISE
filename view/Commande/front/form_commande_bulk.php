<?php
if (!isset($recette) || !is_object($recette)) {
    $_SESSION['flash_error'] = "Erreur: Recette non trouvée.";
    header('Location: /FOODWISE/recettes');
    exit;
}

require __DIR__ . '/../../layouts/front/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">✅ Confirmer toutes les offres de « <?= htmlspecialchars($recette->nom) ?> »</h4>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">
                        Cette commande va regrouper toutes les offres disponibles pour cette recette.
                    </p>

                    <div class="mb-4">
                        <h5>Récapitulatif des offres</h5>
                        <div class="list-group">
                            <?php foreach ($avecOffre as $offre): ?>
                                <div class="list-group-item">
                                    <strong><?= htmlspecialchars($offre->ingredient_nom) ?></strong>
                                    <div style="font-size:13px;color:#666;margin-top:4px;">
                                        <?= htmlspecialchars($offre->commercant_nom) ?> —
                                        <?= htmlspecialchars($offre->commercant_ville) ?>
                                    </div>
                                    <div style="font-size:13px;color:#333;margin-top:4px;">
                                        Quantité : <?= $offre->quantite ?> <?= htmlspecialchars($offre->unite) ?>
                                        • Prix estimé : <?= number_format(($offre->quantite / 1000) * $offre->prix_unitaire, 2) ?> DT
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <strong>Coût total estimé :</strong>
                            <?= number_format($coutTotal, 2) ?> DT
                        </div>
                    </div>

                    <form method="POST" action="?route=commandes/storeBulk&id_recette=<?= $recette->id_recette ?>"
                          onsubmit="return validateCommandeBulk()">

                        <?php foreach ($avecOffre as $idx => $offre): ?>
                            <input type="hidden" name="offers[<?= $idx ?>][id_offre]" value="<?= (int)$offre->offre_id ?>">
                            <input type="hidden" name="offers[<?= $idx ?>][quantite]" value="<?= (int)$offre->quantite ?>">
                        <?php endforeach; ?>

                        <div class="mb-3">
                            <label class="form-label">Adresse de livraison</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" required minlength="5">
                            <small id="err_adresse" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" pattern="[0-9]{8}" placeholder="8 chiffres" required>
                            <small id="err_tel" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mode de paiement</label>
                            <select class="form-control" id="paiement" name="mode_paiement" required>
                                <option value="">-- Choisir --</option>
                                <option value="carte">Carte</option>
                                <option value="especes">Espèces</option>
                            </select>
                            <small id="err_paiement" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note (optionnelle)</label>
                            <textarea class="form-control" name="note"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Confirmer la commande groupée</button>
                            <a href="/FOODWISE/index.php?url=recettes/<?= $recette->id_recette ?>/courses" class="btn btn-secondary">Retour à la liste de courses</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateCommandeBulk() {
    let valid = true;

    let adresse = document.getElementById('adresse').value.trim();
    let tel = document.getElementById('telephone').value.trim();
    let paiement = document.getElementById('paiement').value;

    document.getElementById('err_adresse').innerText = '';
    document.getElementById('err_tel').innerText = '';
    document.getElementById('err_paiement').innerText = '';

    if (adresse.length < 5) {
        document.getElementById('err_adresse').innerText = 'Adresse trop courte';
        valid = false;
    }

    let regex = /^[0-9]{8}$/;
    if (!regex.test(tel)) {
        document.getElementById('err_tel').innerText = 'Numéro invalide (8 chiffres)';
        valid = false;
    }

    if (paiement === '') {
        document.getElementById('err_paiement').innerText = 'Choisir un mode de paiement';
        valid = false;
    }

    return valid;
}
</script>

<?php require __DIR__ . '/../../layouts/front/footer.php';
