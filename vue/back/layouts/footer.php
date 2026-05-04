<?php /** Pied de page — Back-office */ ?>
<footer class="site-footer">
  <div class="site-footer__links">
    <a href="#">Aide</a>
    <a href="#">Confidentialité</a>
    <a href="#">Plan du Site</a>
  </div>
  <div class="site-footer__links" style="text-align: right;">
    <span>© <?= date('Y') ?> FoodWise. Tous Droits Réservés.</span>
  </div>
</footer>
</div>
<?php if (!empty($crudFormScript)) : ?>
<?php
$__crudSrc = preg_match('#^[a-z][a-z0-9+.-]*://#i', $crudFormScript) === 1
    ? $crudFormScript
    : \Controller\Url::asset(ltrim($crudFormScript, '/'));
?>
<script src="<?= htmlspecialchars($__crudSrc, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>
</body>
</html>
