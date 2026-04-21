/**
 * Validation de formulaires côté client
 */

function validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

function clearFieldError(fieldName) {
  const field = document.querySelector(`[name="${fieldName}"]`);
  if (!field) return;
  
  const errorElement = field.parentElement.querySelector('small[data-error]');
  if (errorElement) {
    errorElement.remove();
  }
  field.style.borderColor = '';
}

function showFieldError(fieldName, message) {
  const field = document.querySelector(`[name="${fieldName}"]`);
  if (!field) return;
  
  clearFieldError(fieldName);
  
  const errorElement = document.createElement('small');
  errorElement.setAttribute('data-error', 'true');
  errorElement.style.cssText = 'color:var(--fw-alert);display:block;margin-top:0.25rem';
  errorElement.textContent = message;
  
  field.parentElement.appendChild(errorElement);
  field.style.borderColor = 'var(--fw-alert, #d32f2f)';
}

function validateConnexion(form) {
  const errors = {};
  const email = form.querySelector('[name="email"]').value.trim();
  const password = form.querySelector('[name="password"]').value.trim();
  
  // Vider les erreurs précédentes
  clearFieldError('email');
  clearFieldError('password');
  
  if (email === '') {
    errors.email = 'Veuillez saisir votre adresse e-mail.';
  } else if (!validateEmail(email)) {
    errors.email = 'Le format de l\'adresse est invalide.';
  }
  
  if (password === '') {
    errors.password = 'Veuillez saisir votre mot de passe.';
  }
  
  // Afficher les erreurs
  if (errors.email) showFieldError('email', errors.email);
  if (errors.password) showFieldError('password', errors.password);
  
  return Object.keys(errors).length === 0;
}

function validateInscription(form) {
  const errors = {};
  const nom = form.querySelector('[name="nom"]').value.trim();
  const email = form.querySelector('[name="email"]').value.trim();
  const password = form.querySelector('[name="password"]').value.trim();
  const passwordConfirm = form.querySelector('[name="password_confirm"]').value.trim();
  
  // Vider les erreurs précédentes
  clearFieldError('nom');
  clearFieldError('email');
  clearFieldError('password');
  clearFieldError('password_confirm');
  
  if (nom === '') {
    errors.nom = 'Le nom est requis.';
  }
  
  if (email === '') {
    errors.email = 'Le courriel est requis.';
  } else if (!validateEmail(email)) {
    errors.email = 'Le courriel est invalide.';
  }
  
  if (password === '') {
    errors.password = 'Le mot de passe est requis.';
  } else if (password.length < 8) {
    errors.password = 'Le mot de passe doit contenir au moins 8 caractères.';
  }
  
  if (passwordConfirm !== password) {
    errors.password_confirm = 'Les mots de passe ne correspondent pas.';
  }
  
  // Afficher les erreurs
  if (errors.nom) showFieldError('nom', errors.nom);
  if (errors.email) showFieldError('email', errors.email);
  if (errors.password) showFieldError('password', errors.password);
  if (errors.password_confirm) showFieldError('password_confirm', errors.password_confirm);
  
  return Object.keys(errors).length === 0;
}

function validateProfilEdit(form) {
  const errors = {};
  const poidsKg = form.querySelector('[name="poids_kg"]').value.trim();
  const tailleCm = form.querySelector('[name="taille_cm"]').value.trim();
  const objectif = form.querySelector('[name="objectif"]').value;
  
  // Vider les erreurs précédentes
  clearFieldError('poids_kg');
  clearFieldError('taille_cm');
  clearFieldError('objectif');
  
  if (poidsKg === '') {
    errors.poids_kg = 'Le poids est requis.';
  } else if (isNaN(parseFloat(poidsKg)) || parseFloat(poidsKg) <= 0) {
    errors.poids_kg = 'Le poids doit être un nombre positif.';
  }
  
  if (tailleCm === '') {
    errors.taille_cm = 'La taille est requise.';
  } else if (!/^\d+$/.test(tailleCm) || parseInt(tailleCm) <= 0) {
    errors.taille_cm = 'La taille doit être un entier positif.';
  }
  
  const allowed = ['perte', 'maintien', 'prise', 'performance'];
  if (objectif === '' || !allowed.includes(objectif)) {
    errors.objectif = 'Veuillez choisir un objectif valide.';
  }
  
  // Afficher les erreurs
  if (errors.poids_kg) showFieldError('poids_kg', errors.poids_kg);
  if (errors.taille_cm) showFieldError('taille_cm', errors.taille_cm);
  if (errors.objectif) showFieldError('objectif', errors.objectif);
  
  return Object.keys(errors).length === 0;
}

function validateAllergiesRegimes(form) {
  const errors = {};
  const allergies = form.querySelector('[name="allergies"]').value.trim();
  const regimes = form.querySelector('[name="regimes"]').value.trim();
  const intolerances = form.querySelector('[name="intolerances"]').value.trim();
  
  // Vider les erreurs précédentes
  clearFieldError('allergies');
  clearFieldError('regimes');
  clearFieldError('intolerances');
  
  if (allergies === '' && regimes === '' && intolerances === '') {
    errors.global = 'Au moins un champ doit être renseigné.';
    // Afficher l'erreur globale au-dessus du formulaire
    let globalError = form.querySelector('[data-error-global]');
    if (!globalError) {
      globalError = document.createElement('div');
      globalError.setAttribute('data-error-global', 'true');
      globalError.style.cssText = 'color:var(--fw-alert);margin-bottom:1rem;font-weight:bold';
      form.insertBefore(globalError, form.firstChild);
    }
    globalError.textContent = errors.global;
  } else {
    const globalError = form.querySelector('[data-error-global]');
    if (globalError) globalError.remove();
  }
  
  return Object.keys(errors).length === 0;
}

// Initialiser les validations au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
  const forms = document.querySelectorAll('.fw-form');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      let isValid = true;
      
      // Déterminer le type de formulaire et valider
      if (form.querySelector('[name="password"]') && form.querySelector('[name="password_confirm"]')) {
        // Inscription
        isValid = validateInscription(form);
      } else if (form.querySelector('[name="password"]') && !form.querySelector('[name="nom"]')) {
        // Connexion
        isValid = validateConnexion(form);
      } else if (form.querySelector('[name="poids_kg"]')) {
        // Profil edit
        isValid = validateProfilEdit(form);
      } else if (form.querySelector('[name="allergies"]')) {
        // Allergies et régimes
        isValid = validateAllergiesRegimes(form);
      }
      
      // Empêcher la soumission si validation échouée
      if (!isValid) {
        e.preventDefault();
      }
    });
  });
});
