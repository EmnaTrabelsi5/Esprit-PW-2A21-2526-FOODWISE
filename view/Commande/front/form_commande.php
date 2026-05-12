<?php 

if (!isset($offre) || !is_array($offre)) {
    $_SESSION['flash_error'] = "Erreur: Offre non trouvée.";
    header('Location: ?route=offres/index');
    exit;
}

$id_offre = $offre['id'] ?? 0;

require __DIR__ . '/../../layouts/front/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">?? Commander une offre</h4>
                </div>

                <div class="card-body">

                    <!-- OFFRE INFO -->
                    <div class="mb-4">
                        <h5><?= htmlspecialchars($offre['titre']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($offre['description']) ?></p>

                        <p><strong>Prix :</strong> <?= number_format($offre['prix_unitaire'], 2) ?> TND</p>
                        <p><strong>Stock :</strong> <?= $offre['stock'] ?> <?= $offre['unite'] ?></p>
                    </div>

                    <!-- FORM -->
                    <form method="POST" 
                          action="?route=commandes/store"
                          onsubmit="return validateCommande()">

                        <input type="hidden" name="id_offre" value="<?= $id_offre ?>">

                        <!-- Quantité -->
                        <div class="mb-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="quantite" name="quantite" min="1" required>
                            <small id="err_qte" class="text-danger"></small>
                        </div>

                        <!-- Adresse -->
                        <div class="mb-3">
                            <label class="form-label">Adresse de livraison</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" required minlength="5">
                            <small id="err_adresse" class="text-danger"></small>
                        </div>

                        <!-- Téléphone -->
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" pattern="[0-9]{8}" placeholder="8 chiffres" required>
                            <small id="err_tel" class="text-danger"></small>
                        </div>

                        <!-- Paiement -->
                        <div class="mb-3">
                            <label class="form-label">Mode de paiement</label>
                            <select class="form-control" id="paiement" name="mode_paiement" required>
                                <option value="">-- Choisir --</option>
                                <option value="carte">Carte</option>
                                <option value="especes">Espèces</option>
                            </select>
                            <small id="err_paiement" class="text-danger"></small>
                        </div>

                        <!-- Note -->
                        <div class="mb-3">
                            <label class="form-label">Note (optionnelle)</label>
                            <textarea class="form-control" name="note"></textarea>
                        </div>

                        <!-- BUTTONS -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">? Confirmer la commande</button>
                            <a href="/FOODWISE1/offre.php?action=index" 
                               class="btn btn-secondary">? Retour aux offres</a>
                            <a href="/FOODWISE1/offre.php?action=show&id=<?= $id_offre ?>" 
                               class="btn btn-outline-secondary">Annuler</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ?? VALIDATION JS -->
<script>
function validateCommande() {

    let valid = true;

    let qte = parseInt(document.getElementById("quantite").value) || 0;
    let stock = <?= $offre['stock'] ?>;
    let adresse = document.getElementById("adresse").value.trim();
    let tel = document.getElementById("telephone").value.trim();
    let paiement = document.getElementById("paiement").value;

    document.getElementById("err_qte").innerText = "";
    document.getElementById("err_adresse").innerText = "";
    document.getElementById("err_tel").innerText = "";
    document.getElementById("err_paiement").innerText = "";

    // Quantité
    if (qte <= 0) {
        document.getElementById("err_qte").innerText = "Quantité invalide";
        valid = false;
    } else if (qte > stock) {
        document.getElementById("err_qte").innerText = "Stock insuffisant";
        valid = false;
    }

    // Adresse
    if (adresse.length < 5) {
        document.getElementById("err_adresse").innerText = "Adresse trop courte";
        valid = false;
    }

    // Téléphone (8 chiffres)
    let regex = /^[0-9]{8}$/;
    if (!regex.test(tel)) {
        document.getElementById("err_tel").innerText = "Numéro invalide (8 chiffres)";
        valid = false;
    }

    // Paiement
    if (paiement === "") {
        document.getElementById("err_paiement").innerText = "Choisir un mode de paiement";
        valid = false;
    }

    return valid;
}
</script>

<?php require __DIR__ . '/../../layouts/front/footer.php'; ?>

