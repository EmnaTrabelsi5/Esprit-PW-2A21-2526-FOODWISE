<?php
$pageTitle = 'Magic AI Recipe Importer — FoodWise';
$sidebarActive = 'magic_recipe';
require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
  <header class="page-header fade-in">
    <div class="page-header__row">
      <h1 class="page-title">
        Magic AI Recipe Importer
        <small>Recherchez et importez des milliers de recettes instantanément</small>
      </h1>
      <a class="btn btn--outline" href="<?= htmlspecialchars($url::to('front', 'home', 'index'), ENT_QUOTES, 'UTF-8') ?>">Tableau de bord</a>
    </div>

    <form action="<?= htmlspecialchars($url::to('front', 'magic_recipe', 'index'), ENT_QUOTES, 'UTF-8') ?>" method="get" class="search-bar" style="margin-top: 1.5rem; gap: 1rem; flex-wrap: wrap;">
      <input type="hidden" name="area" value="front">
      <input type="hidden" name="resource" value="magic_recipe">
      <input type="hidden" name="action" value="index">
      
      <div style="flex: 1; display: flex; align-items: center; gap: 0.5rem; min-width: 300px;">
        <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input class="search-bar__input" type="search" name="q" value="<?= htmlspecialchars($query ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Quel plat voulez-vous cuisiner ?" aria-label="Recherche">
      </div>

      <select name="diet" class="form-input" style="width: auto; padding: 0.5rem 1rem;">
        <option value="">Tous les régimes</option>
        <option value="balanced" <?= ($diet ?? '') === 'balanced' ? 'selected' : '' ?>>Équilibré</option>
        <option value="high-protein" <?= ($diet ?? '') === 'high-protein' ? 'selected' : '' ?>>Riche en Protéines</option>
        <option value="low-fat" <?= ($diet ?? '') === 'low-fat' ? 'selected' : '' ?>>Faible en Gras</option>
        <option value="low-carb" <?= ($diet ?? '') === 'low-carb' ? 'selected' : '' ?>>Faible en Glucides</option>
      </select>

      <button type="submit" class="btn btn--primary">
        <span>Lancer la Magie</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      </button>
    </form>
  </header>

  <?php if (isset($_SESSION['flash_success'])) : ?>
    <div class="notification-item notification-item--green fade-in" style="margin-bottom: 2rem;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px;"><path d="M20 6L9 17l-5-5"/></svg>
      <span><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_success']); ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)) : ?>
    <div class="notification-item notification-item--brown fade-in" style="margin-bottom: 2rem;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  <?php endif; ?>

  <div class="dashboard-grid fade-in">
    <?php if (empty($recipes) && !empty($query)) : ?>
      <p style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">Aucune recette trouvée pour votre recherche.</p>
    <?php elseif (!empty($recipes)) : ?>
      <?php foreach ($recipes as $hit) : $r = $hit['recipe']; ?>
        <article class="card fade-in" style="display: flex; flex-direction: column;">
          <div style="position: relative; height: 200px; overflow: hidden;">
            <img src="<?= htmlspecialchars($r['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem 1rem 0.75rem; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%); color: white;">
              <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?></h3>
            </div>
          </div>
          
          <div class="card__body" style="flex: 1;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
              <div class="stat-box">
                <span class="stat-label" style="font-size: 0.6rem;">Énergie</span>
                <span style="font-weight: 700; font-size: 1rem; color: var(--primary);"><?= round($r['calories']) ?> <small>kcal</small></span>
              </div>
              <div class="stat-box">
                <span class="stat-label" style="font-size: 0.6rem;">Ingrédients</span>
                <span style="font-weight: 700; font-size: 1rem;"><?= count($r['ingredients']) ?></span>
              </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 1.5rem;">
              <?php if (isset($r['totalNutrients']['PROCNT'])) : ?>
                <span class="badge" style="background: #e3f2fd; color: #1565c0; font-size: 0.65rem;">P: <?= round($r['totalNutrients']['PROCNT']['quantity']) ?>g</span>
              <?php endif; ?>
              <?php if (isset($r['totalNutrients']['CHOCDF'])) : ?>
                <span class="badge" style="background: #fff3e0; color: #ef6c00; font-size: 0.65rem;">G: <?= round($r['totalNutrients']['CHOCDF']['quantity']) ?>g</span>
              <?php endif; ?>
              <?php if (isset($r['totalNutrients']['FAT'])) : ?>
                <span class="badge" style="background: #fce4ec; color: #c2185b; font-size: 0.65rem;">L: <?= round($r['totalNutrients']['FAT']['quantity']) ?>g</span>
              <?php endif; ?>
            </div>

            <form action="<?= htmlspecialchars($url::to('front', 'magic_recipe', 'import'), ENT_QUOTES, 'UTF-8') ?>" method="post">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="recipe_json" value="<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn btn--primary" style="width: 100%; justify-content: center; background: var(--secondary);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span>Importer Magiquement</span>
              </button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else : ?>
      <div class="card" style="grid-column: 1 / -1; background: #fdf6ec; border: 1px dashed var(--fw-brun-pale);">
        <div class="card__body" style="text-align: center; padding: 5rem 2rem;">
          <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
            <svg viewBox="0 0 24 24" fill="none" stroke="#a0522d" stroke-width="1.5" style="width: 40px;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <h2 style="color: var(--fw-brun-fonce); margin-bottom: 1rem;">Votre assistant de cuisine intelligent</h2>
          <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">Connectez-vous aux meilleures bases de données de recettes mondiales. Recherchez par ingrédient ou par régime pour importer instantanément des recettes complètes dans votre MealPlanner.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
