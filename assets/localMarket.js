/* ============================================================
   FoodWise — LocalMarket JS
   Validation côté client SANS attributs HTML5 natifs
   (required, pattern, type=email… remplacés par JS)
   ============================================================ */
 
document.addEventListener('DOMContentLoaded', () => {
 
  /* ── 1. Validation formulaire Commerçant ── */
  const formCommerçant = document.querySelector('form[action*="commercant.php"]');
  if (formCommerçant) {
    formCommerçant.addEventListener('submit', e => {
      clearErrors();
      let ok = true;
 
      const nom   = formCommerçant.querySelector('#nom');
      const email = formCommerçant.querySelector('#email');
      const adresse = formCommerçant.querySelector('#adresse');
      const ville = formCommerçant.querySelector('#ville');
 
      if (nom && !nom.value.trim()) {
        showError(nom, 'Le nom est obligatoire.');
        ok = false;
      }
      if (email && !email.value.trim()) {
        showError(email, "L'email est obligatoire.");
        ok = false;
      } else if (email && !isValidEmail(email.value)) {
        showError(email, "Format d'email invalide (ex: nom@domaine.tn)");
        ok = false;
      }
      if (adresse && !adresse.value.trim()) {
        showError(adresse, "L'adresse est obligatoire.");
        ok = false;
      }
      if (ville && !ville.value.trim()) {
        showError(ville, "La ville est obligatoire.");
        ok = false;
      }
 
      if (!ok) e.preventDefault();
    });
  }
 
  /* ── 2. Validation formulaire Offre ── */
  const formOffre = document.querySelector('form[action*="offre.php"]');
  if (formOffre) {
    formOffre.addEventListener('submit', e => {
      clearErrors();
      let ok = true;
 
      const commercantId    = formOffre.querySelector('#commercant_id');
      const titre           = formOffre.querySelector('#titre');
      const prix            = formOffre.querySelector('#prix_unitaire');
      const stock           = formOffre.querySelector('#stock');
      const dateDebut       = formOffre.querySelector('#date_debut');
      const dateExpiration  = formOffre.querySelector('#date_expiration');
 
      if (commercantId && !commercantId.value) {
        showError(commercantId, 'Veuillez sélectionner un commerçant.');
        ok = false;
      }
      if (titre && !titre.value.trim()) {
        showError(titre, 'Le titre est obligatoire.');
        ok = false;
      }
      if (prix && (prix.value === '' || parseFloat(prix.value) < 0 || isNaN(parseFloat(prix.value)))) {
        showError(prix, 'Le prix doit être un nombre positif.');
        ok = false;
      }
      if (stock && (stock.value === '' || parseInt(stock.value) < 0 || isNaN(parseInt(stock.value)))) {
        showError(stock, 'La quantité en stock doit être un entier positif ou nul.');
        ok = false;
      }
      if (dateExpiration && !dateExpiration.value) {
        showError(dateExpiration, "La date d'expiration est obligatoire.");
        ok = false;
      } else if (dateDebut && dateExpiration && dateDebut.value && dateExpiration.value) {
        if (new Date(dateExpiration.value) <= new Date(dateDebut.value)) {
          showError(dateExpiration, "La date d'expiration doit être postérieure à la date de début.");
          ok = false;
        }
      }
 
      if (!ok) {
        e.preventDefault();
        // Scroll vers la première erreur
        const firstErr = formOffre.querySelector('.form-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
 
    /* Mise à jour dynamique du statut selon le stock */
    const stockInput = formOffre.querySelector('#stock');
    const statutSel  = formOffre.querySelector('#statut');
    if (stockInput && statutSel) {
      stockInput.addEventListener('input', () => {
        const s = parseInt(stockInput.value);
        if (!isNaN(s)) {
          if (s === 0) statutSel.value = 'epuise';
          else if (s > 0 && statutSel.value === 'epuise') statutSel.value = 'disponible';
        }
      });
    }
 
    /* Date minimum pour expiration = date début */
    const dateDebutInp = formOffre.querySelector('#date_debut');
    const dateExpInp   = formOffre.querySelector('#date_expiration');
    if (dateDebutInp && dateExpInp) {
      dateDebutInp.addEventListener('change', () => {
        if (dateExpInp.value && dateExpInp.value <= dateDebutInp.value) {
          dateExpInp.value = '';
          showError(dateExpInp, "La date d'expiration doit être postérieure à la date de début.");
        }
      });
    }
  }
 
  /* ── 3. Compteur caractères description ── */
  const textareas = document.querySelectorAll('textarea.form-control');
  textareas.forEach(ta => {
    const counter = document.createElement('div');
    counter.style.cssText = 'text-align:right;font-size:11px;color:var(--texte-leger);margin-top:3px';
    ta.parentNode.appendChild(counter);
    const update = () => { counter.textContent = ta.value.length + ' caractère(s)'; };
    ta.addEventListener('input', update);
    update();
  });
 
  /* ── 4. Fermeture modal au clic sur l'overlay ── */
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.style.display = 'none';
    });
  });
 
  /* ── 5. Highlight nav active ── */
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-item').forEach(link => {
    if (link.href && currentPath.includes(link.getAttribute('href')?.split('?')[0])) {
      link.classList.add('active');
    }
  });
 
  /* ── Helpers ── */
  function showError(input, message) {
    input.classList.add('is-invalid');
    // Enlève l'ancien message s'il existe
    const existing = input.parentNode.querySelector('.form-error');
    if (existing) existing.remove();
    const err = document.createElement('span');
    err.className = 'form-error';
    err.textContent = message;
    input.parentNode.appendChild(err);
  }
 
  function clearErrors() {
    document.querySelectorAll('.form-error').forEach(e => e.remove());
    document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  }
 
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.trim());
  }
});