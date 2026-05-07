<?php
/**
 * FoodWise — Liste de Courses Intelligente
 * view/recipebook/front/courses.php
 *
 * Variables transmises par RecetteController::courses($id) :
 *   $recette   object  La recette concernée
 *   $courses   array   [
 *                        'avec_offre' => [...],
 *                        'sans_offre' => [...],
 *                        'cout_total' => float,
 *                        'economies'  => int,
 *                        'total_ing'  => int,
 *                      ]
 */
include __DIR__ . '/layout/header.php';

$avecOffre = $courses['avec_offre'] ?? [];
$sansOffre = $courses['sans_offre'] ?? [];
$coutTotal = $courses['cout_total'] ?? 0;
$economies = $courses['economies']  ?? 0;
$totalIng  = $courses['total_ing']  ?? 0;

/*
 * Prépare les marqueurs pour Leaflet :
 * On déduplique par commercant_id pour n'avoir qu'un marqueur par magasin.
 * On ajoute la liste de ses ingrédients dans le popup.
 */
$marqueurs = [];
foreach ($avecOffre as $ligne) {
    if (empty($ligne->commercant_id)) continue;
    $cid = $ligne->commercant_id;

    if (!isset($marqueurs[$cid])) {
        $marqueurs[$cid] = [
            'id'        => $cid,
            'nom'       => $ligne->commercant_nom,
            'ville'     => $ligne->commercant_ville,
            'adresse'   => $ligne->commercant_adresse,
            'tel'       => $ligne->commercant_tel,
            'offres'    => [],
        ];
    }
    $marqueurs[$cid]['offres'][] = [
        'ingredient' => $ligne->ingredient_nom,
        'titre'      => $ligne->offre_titre,
        'prix'       => $ligne->prix_unitaire,
        'unite'      => $ligne->offre_unite,
        'stock'      => $ligne->stock,
    ];
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

<style>
#map { height: 420px; border-radius: var(--radius); box-shadow: var(--shadow); }

.offre-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--creme);
    border-radius: 8px;
    margin-bottom: 9px;
    gap: 12px;
    flex-wrap: wrap;
}
.offre-row.sans-offre { background: #FEF9F0; border: 1px dashed var(--brun-pale); opacity: 0.85; }

.prix-tag {
    background: var(--vert-fonce);
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
}

.commercant-tag {
    font-size: 11px;
    color: var(--texte-leger);
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 3px;
}

.stat-box {
    background: var(--blanc);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 16px 20px;
    text-align: center;
}

.leaflet-popup-content { font-family: 'Lato', sans-serif; font-size: 13px; }
.popup-title { font-weight: 700; color: var(--brun-fonce); margin-bottom: 6px; font-size: 14px; }
.popup-offre { padding: 3px 0; border-bottom: 1px solid #eee; }
.popup-offre:last-child { border-bottom: none; }
</style>

<!-- Fil d'Ariane -->
<nav style="font-size:13px;color:var(--texte-leger);margin-bottom:16px;">
    <a href="/FOODWISE/index.php?url=recettes" style="color:var(--brun-chaud);text-decoration:none;">Mes Recettes</a>
    <span style="margin:0 6px;">›</span>
    <a href="/FOODWISE/index.php?url=recettes/<?= $recette->id_recette ?>"
       style="color:var(--brun-chaud);text-decoration:none;"><?= htmlspecialchars($recette->nom) ?></a>
    <span style="margin:0 6px;">›</span>
    <span>Liste de courses</span>
</nav>

<!-- En-tête -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
    <div>
        <h1 class="page-title">🛒 Liste de courses</h1>
        <p class="page-subtitle">
            Offres locales disponibles pour <strong><?= htmlspecialchars($recette->nom) ?></strong>
        </p>
    </div>
    <a href="/FOODWISE/index.php?url=recettes/<?= $recette->id_recette ?>"
       class="btn btn-outline btn-sm">← Retour à la recette</a>
</div>

<!-- Stats résumé -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    <div class="stat-box">
        <div style="font-size:28px;font-weight:700;color:var(--vert-fonce);"><?= $economies ?>/<?= $totalIng ?></div>
        <div style="font-size:13px;color:var(--texte-leger);">ingrédients avec offre locale</div>
    </div>
    <div class="stat-box">
        <div style="font-size:28px;font-weight:700;color:var(--brun-chaud);"><?= $coutTotal ?> DT</div>
        <div style="font-size:13px;color:var(--texte-leger);">coût total estimé</div>
    </div>
    <div class="stat-box">
        <div style="font-size:28px;font-weight:700;color:var(--brun-fonce);"><?= count($marqueurs) ?></div>
        <div style="font-size:13px;color:var(--texte-leger);">commerçant<?= count($marqueurs) > 1 ? 's' : '' ?> trouvé<?= count($marqueurs) > 1 ? 's' : '' ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

  <!-- ══ Colonne principale ══ -->
  <div>

    <!-- Ingrédients avec offre -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Disponibles en offre locale</h2>
            <span class="badge badge-vert"><?= count($avecOffre) ?> ingrédient<?= count($avecOffre) > 1 ? 's' : '' ?></span>
        </div>

        <?php if (!empty($avecOffre)): ?>
            <?php foreach ($avecOffre as $ligne): ?>
            <div class="offre-row">
                <!-- Ingrédient -->
                <div style="flex:1;min-width:140px;">
                    <div style="font-weight:700;font-size:14px;color:var(--texte-sombre);">
                        <?= htmlspecialchars($ligne->ingredient_nom) ?>
                        <?php if ($ligne->est_optionnel): ?>
                            <span style="font-size:11px;color:var(--texte-leger);font-weight:400;"> (optionnel)</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:var(--texte-leger);margin-top:2px;">
                        Quantité nécessaire : <strong><?= $ligne->quantite ?> <?= $ligne->unite ?></strong>
                    </div>
                </div>

                <!-- Offre -->
                <div style="flex:1;min-width:130px;">
                    <div style="font-size:13px;color:var(--texte-moyen);"><?= htmlspecialchars($ligne->offre_titre) ?></div>
                    <div class="commercant-tag">
                        📍 <?= htmlspecialchars($ligne->commercant_nom) ?> — <?= htmlspecialchars($ligne->commercant_ville) ?>
                    </div>
                    <div style="font-size:11px;color:var(--texte-leger);margin-top:2px;">
                        Stock : <?= $ligne->stock ?> <?= $ligne->offre_unite ?>
                    </div>
                </div>

                <!-- Prix + bouton -->
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <span class="prix-tag"><?= number_format($ligne->prix_unitaire, 2) ?> DT/<?= $ligne->offre_unite ?></span>
                    <a href="/FOODWISE/index.php?url=offres/show&action=show&id=<?= $ligne->offre_id ?>"
                       class="btn btn-outline btn-sm" style="font-size:11px;padding:3px 10px;">
                        Voir l'offre
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
                <?php if (!empty($avecOffre)): ?>
    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
        <a href="/FOODWISE/index.php?route=commandes/createBulk&id_recette=<?= $recette->id_recette ?>" 
            class="btn btn-confirm">
            <span></span> commander en ligne
        </a>
    </div>
    <?php endif; ?>

        <?php else: ?>
            <p style="text-align:center;padding:24px;color:var(--texte-leger);">
                Aucune offre locale active pour cette recette pour le moment.
            </p>
        <?php endif; ?>
    </div>

    <!-- Ingrédients sans offre -->
    <?php if (!empty($sansOffre)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">⚠️ À trouver ailleurs</h2>
            <span class="badge badge-orange"><?= count($sansOffre) ?> ingrédient<?= count($sansOffre) > 1 ? 's' : '' ?></span>
        </div>
        
        <?php foreach ($sansOffre as $ligne): ?>
        <div class="offre-row sans-offre">
            <div style="flex:1;">
                <div style="font-weight:700;font-size:14px;color:var(--texte-sombre);">
                    <?= htmlspecialchars($ligne->ingredient_nom) ?>
                </div>
                <div style="font-size:12px;color:var(--texte-leger);margin-top:2px;">
                    <?= $ligne->quantite ?> <?= $ligne->unite ?> — Aucune offre active
                </div>
            </div>
            <span class="badge badge-brun">À acheter en magasin</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>



  </div><!-- /colonne principale -->

  <!-- ══ Colonne carte ══ -->
  <div>
    <div class="card" style="padding:16px;position:sticky;top:80px;">
        <div class="card-header" style="margin-bottom:12px;">
            <h2 class="card-title">📍 Carte des commerçants</h2>
        </div>

        <?php if (!empty($marqueurs)): ?>

        <!-- Carte Leaflet -->
        <div id="map"></div>

        <div style="margin-top:14px;font-size:12px;color:var(--texte-leger);text-align:center;">
            Cliquez sur un marqueur pour voir les offres disponibles
        </div>

        <!-- Liste des commerçants sous la carte -->
<div style="margin-top:16px;display:flex;flex-direction:column;gap:10px;" id="commercants-list">
    <?php foreach (array_values($marqueurs) as $idx => $com): ?>
    <div class="com-card"
         id="com-card-<?= $idx ?>"
         onclick="activerCommercant(<?= $idx ?>)"
         style="padding:10px 12px;background:var(--creme);border-radius:8px;
                font-size:13px;cursor:pointer;border:2px solid transparent;
                transition:0.2s;">
        <div style="font-weight:700;color:var(--brun-fonce);">
            🏪 <?= htmlspecialchars($com['nom']) ?>
        </div>
        <div style="color:var(--texte-leger);font-size:12px;margin-top:2px;">
            📍 <?= htmlspecialchars($com['adresse'] ?? $com['ville']) ?>
        </div>
        <?php if (!empty($com['tel'])): ?>
        <div style="color:var(--texte-leger);font-size:12px;">
            📞 <?= htmlspecialchars($com['tel']) ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
            <?php foreach ($com['offres'] as $o): ?>
            <span class="badge badge-vert" style="font-size:10px;">
                <?= htmlspecialchars($o['ingredient']) ?> — <?= number_format($o['prix'], 2) ?> DT
            </span>
            <?php endforeach; ?>
        </div>
        <div style="font-size:11px;color:var(--brun-chaud);margin-top:6px;font-weight:700;"
             id="com-hint-<?= $idx ?>">
            Cliquez pour localiser sur la carte ↑
        </div>
    </div>
    <?php endforeach; ?>
</div>

        <?php else: ?>
        <div style="text-align:center;padding:30px;color:var(--texte-leger);">
            <div style="font-size:36px;margin-bottom:10px;">🗺️</div>
            <p>Aucun commerçant disponible à afficher sur la carte.</p>
        </div>
        <?php endif; ?>

    </div>
  </div>

</div><!-- /grid -->

<!-- Leaflet JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
const commercants = <?= json_encode(array_values($marqueurs), JSON_UNESCAPED_UNICODE) ?>;

/* ── Initialisation carte ── */
const map = L.map('map').setView([33.8869, 9.5375], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18
}).addTo(map);

/* ── Cache des marqueurs (un par commerçant, chargés à la demande) ── */
const markers    = {};   // idx → marker Leaflet
const coordCache = {};   // idx → {lat, lng}

/* ── Marqueur actif ── */
let activeIdx = null;

/* ── Icône FoodWise ── */
function makeIcon(active) {
    const bg = active ? '#A0522D' : '#5C7A3E';
    return L.divIcon({
        html: `<div style="
            background:${bg};
            width:32px;height:32px;
            border-radius:50% 50% 50% 0;
            transform:rotate(-45deg);
            border:3px solid #fff;
            box-shadow:0 3px 8px rgba(0,0,0,0.35);
            transition:background 0.2s;
        "></div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -36],
        className: ''
    });
}

/* ── Contenu popup ── */
function makePopup(com) {
    const offresHtml = com.offres.map(o => `
        <div style="padding:4px 0;border-bottom:1px solid #eee;">
            🥗 <strong>${o.ingredient}</strong> —
            ${parseFloat(o.prix).toFixed(2)} DT/${o.unite}
            <br><small style="color:#9B7355;">${o.titre} (stock: ${o.stock})</small>
        </div>
    `).join('');

    return `
        <div style="font-family:'Lato',sans-serif;font-size:13px;min-width:200px;">
            <div style="font-weight:700;color:#4E2C0E;font-size:14px;margin-bottom:6px;">
                🏪 ${com.nom}
            </div>
            <div style="font-size:12px;color:#9B7355;margin-bottom:8px;">
                📍 ${com.adresse || com.ville}
                ${com.tel ? `<br>📞 ${com.tel}` : ''}
            </div>
            <div style="font-size:12px;font-weight:600;color:#5C7A3E;margin-bottom:4px;">
                Offres disponibles :
            </div>
            ${offresHtml}
        </div>
    `;
}

/* ── Style carte commerçant (active/inactive) ── */
function setCardStyle(idx, active) {
    const card = document.getElementById('com-card-' + idx);
    const hint = document.getElementById('com-hint-' + idx);
    if (!card) return;

    if (active) {
        card.style.borderColor     = '#A0522D';
        card.style.background      = '#FDF0E6';
        card.style.boxShadow       = '0 2px 10px rgba(160,82,45,0.18)';
        hint.textContent           = '✅ Affiché sur la carte';
        hint.style.color           = '#5C7A3E';
    } else {
        card.style.borderColor     = 'transparent';
        card.style.background      = 'var(--creme)';
        card.style.boxShadow       = 'none';
        hint.textContent           = 'Cliquez pour localiser sur la carte ↑';
        hint.style.color           = 'var(--brun-chaud)';
    }
}

/* ── Fonction principale : activer un commerçant ── */
async function activerCommercant(idx) {

    /* Si déjà actif → désactiver (toggle) */
    if (activeIdx === idx) {
        if (markers[idx]) {
            map.removeLayer(markers[idx]);
            delete markers[idx];
        }
        setCardStyle(idx, false);
        activeIdx = null;
        map.setView([33.8869, 9.5375], 6);
        return;
    }

    /* Désactiver l'ancien marqueur actif */
    if (activeIdx !== null) {
        if (markers[activeIdx]) {
            map.removeLayer(markers[activeIdx]);
            delete markers[activeIdx];
        }
        setCardStyle(activeIdx, false);
    }

    activeIdx = idx;
    setCardStyle(idx, true);

    const com = commercants[idx];

    /* Indiquer le chargement */
    document.getElementById('com-hint-' + idx).textContent = '⏳ Localisation en cours...';

    /* Utiliser le cache si déjà géocodé */
    let lat, lng;
    if (coordCache[idx]) {
        lat = coordCache[idx].lat;
        lng = coordCache[idx].lng;
    } else {
        /* Appel Nominatim pour géocoder */
        const query = encodeURIComponent(
            (com.adresse ? com.adresse + ', ' : '') + com.ville + ', Tunisie'
        );
        try {
            const resp = await fetch(
                `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1`,
                { headers: { 'Accept-Language': 'fr' } }
            );
            const data = await resp.json();

            if (!data || data.length === 0) {
                document.getElementById('com-hint-' + idx).textContent = '❌ Localisation introuvable';
                setCardStyle(idx, false);
                activeIdx = null;
                return;
            }

            lat = parseFloat(data[0].lat);
            lng = parseFloat(data[0].lon);
            coordCache[idx] = { lat, lng }; /* Mettre en cache */

        } catch (err) {
            document.getElementById('com-hint-' + idx).textContent = '❌ Erreur de connexion';
            setCardStyle(idx, false);
            activeIdx = null;
            return;
        }
    }

    /* Créer et afficher le marqueur */
    const marker = L.marker([lat, lng], { icon: makeIcon(true) })
        .addTo(map)
        .bindPopup(makePopup(com), { maxWidth: 280 })
        .openPopup();

    markers[idx] = marker;

    /* Centrer et zoomer sur le marqueur */
    map.flyTo([lat, lng], 14, { duration: 1.2 });
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
