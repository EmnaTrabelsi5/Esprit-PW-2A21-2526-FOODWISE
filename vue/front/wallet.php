<?php
$pageTitle = 'Mon Portefeuille Santé — FoodWise';
$sidebarActive = 'wallet';
require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
    <header class="page-header fade-in">
        <div class="page-header__row">
            <h1 class="page-title">
                Mon Portefeuille Santé
                <small>Gérez vos calories comme un capital précieux</small>
            </h1>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <form method="GET" action="<?= $url::index() ?>" id="objectif-selector-form">
                    <input type="hidden" name="area" value="front">
                    <input type="hidden" name="resource" value="wallet">
                    <input type="hidden" name="action" value="index">
                    <select name="id_obj" class="form-input" onchange="document.getElementById('objectif-selector-form').submit()" style="min-width: 220px; font-weight: 600; color: #4a5568; border-color: #cbd5e0;">
                        <?php foreach ($objectifs as $obj): ?>
                            <option value="<?= $obj['id_obj'] ?>" <?= (int)$obj['id_obj'] === $selectedId ? 'selected' : '' ?>>
                                Objectif #<?= $obj['id_obj'] ?> : <?= htmlspecialchars($obj['type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="badge badge--ok" style="padding: 0.8rem 1.5rem; font-size: 1rem;">
                    <i class="fas fa-medal"></i> Grade : <?= $grade ?>
                </div>
            </div>
        </div>
    </header>

    <?php if ($risk): ?>
    <div class="card" style="background: #fff5f5; border-left: 5px solid #fc8181; margin-bottom: 2rem;">
        <div class="card__body" style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="font-size: 2.5rem; color: #e53e3e;"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <h3 style="color: #c53030; margin: 0;">Alerte : Risque de Banqueroute Énergétique !</h3>
                <p style="margin: 0.5rem 0 0; color: #742a2a;">Vous avez déjà dépensé <?= round(($spent/$budget)*100) ?>% de votre budget à <?= date('H:i') ?>. Privilégiez des repas "Low Cost" (légumes, protéines maigres) pour ce soir.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <!-- Wallet Balance Card -->
        <div class="card" style="background: linear-gradient(135deg, #7b3f1a 0%, #a0522d 100%); color: white; border: none; box-shadow: 0 15px 35px rgba(123, 63, 26, 0.2);">
            <div class="card__body" style="padding: 2.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <span style="text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; opacity: 0.8;">Solde Disponible</span>
                    <i class="fas fa-wallet" style="font-size: 1.5rem; opacity: 0.8;"></i>
                </div>
                <h2 style="font-size: 3.5rem; margin: 0; font-weight: 800;"><?= number_format($remaining) ?> <small style="font-size: 1.2rem; font-weight: 400;">Crédits</small></h2>
                <div style="margin-top: 2rem; display: flex; gap: 1.5rem; font-size: 0.9rem;">
                    <div>
                        <div style="opacity: 0.7;">Budget Quotidien</div>
                        <div style="font-weight: 600;"><?= $budget ?></div>
                    </div>
                    <div style="width: 1px; background: rgba(255,255,255,0.2);"></div>
                    <div>
                        <div style="opacity: 0.7;">Remise Qualité</div>
                        <div style="font-weight: 600; color: #68d391;">+ <?= $discount ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings Summary -->
        <div class="card">
            <div class="card__header">
                <h3><i class="fas fa-piggy-bank"></i> Épargne Santé</h3>
            </div>
            <div class="card__body">
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary);"><?= number_format($totalSaved) ?></div>
                    <p style="color: var(--text-muted);">Crédits Bonus accumulés (7j)</p>
                </div>
                <div style="margin-top: 1rem;">
                    <div class="progress-bar" style="height: 10px; background: #edf2f7; border-radius: 5px;">
                        <div style="width: <?= min(100, ($totalSaved/5000)*100) ?>%; height: 100%; background: var(--secondary); border-radius: 5px;"></div>
                    </div>
                    <p style="font-size: 0.75rem; text-align: right; margin-top: 0.5rem; color: var(--text-muted);">Objectif Expert : 5,000</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenditure Chart (Simulation) -->
    <div class="card">
        <div class="card__header">
            <h3><i class="fas fa-chart-line"></i> Flux de Dépenses Hebdomadaires</h3>
        </div>
        <div class="card__body">
            <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 200px; padding: 20px 0;">
                <?php foreach ($history as $h): ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <?php 
                        $height = ($h['spent'] / $h['budget']) * 150; 
                        $color = $h['spent'] > $h['budget'] ? '#fc8181' : '#68d391';
                        ?>
                        <div style="width: 30px; height: <?= $height ?>px; background: <?= $color ?>; border-radius: 4px; transition: height 0.5s;"></div>
                        <span style="font-size: 0.7rem; color: var(--text-muted);"><?= date('D', strtotime($h['date'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1rem; font-size: 0.8rem;">
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-square" style="color: #68d391;"></i> Sous Budget (Épargne)</span>
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-square" style="color: #fc8181;"></i> Dépassement</span>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
