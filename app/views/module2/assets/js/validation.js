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
  const prenom = form.querySelector('[name="prenom"]').value.trim();
  const email = form.querySelector('[name="email"]').value.trim();
  const password = form.querySelector('[name="password"]').value.trim();
  const passwordConfirm = form.querySelector('[name="password_confirm"]').value.trim();
  
  // Vider les erreurs précédentes
  clearFieldError('nom');
  clearFieldError('prenom');
  clearFieldError('email');
  clearFieldError('password');
  clearFieldError('password_confirm');
  
  if (nom === '') {
    errors.nom = 'Le nom est requis.';
  } else if (!/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ\s\-]*$/.test(nom)) {
    errors.nom = 'Le nom ne doit contenir que des lettres, espaces ou tirets, et ne pas commencer par un chiffre.';
  }
  
  if (prenom === '') {
    errors.prenom = 'Le prénom est requis.';
  } else if (!/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ\s\-]*$/.test(prenom)) {
    errors.prenom = 'Le prénom ne doit contenir que des lettres, espaces ou tirets, et ne pas commencer par un chiffre.';
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
  } else if (!/[A-Z]/.test(password)) {
    errors.password = 'Le mot de passe doit contenir au moins une majuscule.';
  } else if (!/[0-9]/.test(password)) {
    errors.password = 'Le mot de passe doit contenir au moins un chiffre.';
  }
  
  if (passwordConfirm !== password) {
    errors.password_confirm = 'Les mots de passe ne correspondent pas.';
  }
  
  // Afficher les erreurs
  if (errors.nom) showFieldError('nom', errors.nom);
  if (errors.prenom) showFieldError('prenom', errors.prenom);
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
  } else if (parseFloat(poidsKg) < 25 || parseFloat(poidsKg) > 250) {
    errors.poids_kg = 'Veuillez entrer une valeur de poids réaliste.';
  }
  
  if (tailleCm === '') {
    errors.taille_cm = 'La taille est requise.';
  } else if (!/^\d+$/.test(tailleCm) || parseInt(tailleCm) <= 0) {
    errors.taille_cm = 'La taille doit être un entier positif.';
  } else if (parseInt(tailleCm) < 100 || parseInt(tailleCm) > 250) {
    errors.taille_cm = 'Veuillez entrer une valeur de taille réaliste.';
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
  
  return Object.keys(errors).length === 0;
}

/**
 * Calcule l'IMC et l'affiche en temps réel
 */
function updateIMCDisplay(form) {
  const poidsInput = form.querySelector('[name="poids_kg"]');
  const tailleInput = form.querySelector('[name="taille_cm"]');
  
  if (!poidsInput || !tailleInput) return;
  
  const poids = parseFloat(poidsInput.value);
  const taille = parseInt(tailleInput.value);
  
  // Ne rien faire si les valeurs sont invalides
  if (isNaN(poids) || isNaN(taille) || poids <= 0 || taille <= 0) {
    return;
  }
  
  // Calculer l'IMC
  const taille_m = taille / 100;
  const imc = (poids / (taille_m * taille_m)).toFixed(2);
  
  // Déterminer la catégorie
  let categorie, couleur, description;
  if (imc < 18.5) {
    categorie = 'Insuffisance pondérale';
    couleur = 'info';
    description = 'Poids insuffisant';
  } else if (imc < 25) {
    categorie = 'Normal';
    couleur = 'success';
    description = 'Poids normal';
  } else if (imc < 30) {
    categorie = 'Surpoids';
    couleur = 'warning';
    description = 'Surpoids léger';
  } else if (imc < 35) {
    categorie = 'Obésité classe I';
    couleur = 'alert';
    description = 'Obésité de classe I';
  } else if (imc < 40) {
    categorie = 'Obésité classe II';
    couleur = 'alert';
    description = 'Obésité de classe II';
  } else {
    categorie = 'Obésité classe III';
    couleur = 'alert';
    description = 'Obésité sévère';
  }
  
  // Chercher ou créer l'élément d'affichage IMC
  let imcDisplay = form.querySelector('[data-imc-display]');
  if (!imcDisplay) {
    imcDisplay = document.createElement('div');
    imcDisplay.setAttribute('data-imc-display', 'true');
    tailleInput.parentElement.parentElement.insertAdjacentElement('afterend', imcDisplay);
  }
  
  // Déterminer les couleurs
  let bgColor, textColor;
  switch(couleur) {
    case 'success':
      bgColor = '#d4edda';
      textColor = '#155724';
      break;
    case 'warning':
      bgColor = '#fff3cd';
      textColor = '#856404';
      break;
    case 'alert':
      bgColor = '#f8d7da';
      textColor = '#721c24';
      break;
    default:
      bgColor = '#e7f3ff';
      textColor = '#004085';
  }
  
  // Afficher l'IMC
  imcDisplay.style.cssText = `background-color:${bgColor};border:1px solid ${textColor};border-radius:6px;padding:1rem;margin-top:1rem`;
  imcDisplay.innerHTML = `
    <strong style="color:${textColor}">Indice de Masse Corporelle (IMC)</strong>
    <p style="margin:0.5rem 0;color:${textColor}">
      <strong>${imc}</strong> — ${categorie}
    </p>
    <small style="color:${textColor}">${description}</small>
  `;
}

// Initialiser les validations au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
  const forms = document.querySelectorAll('.fw-form');
  
  forms.forEach(form => {
    // Ajouter les écouteurs pour le calcul en temps réel de l'IMC
    const poidsInput = form.querySelector('[name="poids_kg"]');
    const tailleInput = form.querySelector('[name="taille_cm"]');
    
    if (poidsInput && tailleInput) {
      poidsInput.addEventListener('input', function() {
        updateIMCDisplay(form);
      });
      tailleInput.addEventListener('input', function() {
        updateIMCDisplay(form);
      });
      
      // Calculer à la première charge
      updateIMCDisplay(form);
    }
    
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
