<?php
declare(strict_types=1);
?>
    </main>
    <footer class="fw-footer" role="contentinfo">
      <div>
        <a href="#">Dashboard</a>
        <a href="#">Liens</a>
        <a href="#">Formations Contenu</a>
        <a href="#">Confidentialité</a>
        <a href="#">Aide</a>
      </div>
      <div>
        <a href="#">Aide</a>
        <a href="#">Confidentialité</a>
        <a href="#">Plan du Site</a>
        <span>© <?= date('Y') ?> FoodWise. Tous Droits Réservés.</span>
      </div>
    </footer>
  </div>
</div>
<script src="<?= htmlspecialchars(assetUrl('validation-foodlog.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
  // Afficher le badge de messages non lus
  document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= htmlspecialchars($routesModule2['front_get_unread_count'] ?? '', ENT_QUOTES, 'UTF-8') ?>')
      .then(response => response.json())
      .then(data => {
        const badge = document.getElementById('fw-unread-badge');
        if (data.unread_count > 0 && badge) {
          badge.textContent = data.unread_count;
          badge.style.display = 'inline-flex';
        }
      })
      .catch(err => console.log('Erreur lors du chargement des messages:', err));
  });
</script>
</body>
</html>

