  </div><!-- /.page-body -->
</div><!-- /.main-content -->
 
<!-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ -->
<footer class="site-footer">
  <p>
    <a href="/FOODWISE/">Dashboard</a> &nbsp;·&nbsp;
    <a href="/FOODWISE/?route=offres">Le Marché</a> &nbsp;·&nbsp;
    <a href="#">Commerçants</a> &nbsp;·&nbsp;
    <a href="#">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#">Aide</a>
    &emsp;|&emsp;
    &copy; 2026 FoodWise. Tous Droits Réservés.
  </p>
</footer>
 
<script src="/FOODWISE/assets/localMarket.js"></script>
<script>
function doTopbarSearch() {
  const q = document.getElementById('topbarSearch').value.trim();
  if (q) window.location.href = '?route=offres&action=index&search=' + encodeURIComponent(q);
}
document.getElementById('topbarSearch')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') doTopbarSearch();
});
// Auto-dismiss flash
setTimeout(() => {
  const f = document.getElementById('flash-msg');
  if (f) f.style.opacity = '0', setTimeout(() => f.remove(), 400);
}, 4500);
</script>
</body>
</html>