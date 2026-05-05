document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#entry-form');
  const suiviForm = document.querySelector('#suiviForm');
  const deleteButtons = document.querySelectorAll('.confirm-delete');

  const errorBox = form ? document.querySelector('#form-errors') : null;
  const invalidClass = 'field-invalid';

  const formatFieldName = (name) => name.charAt(0).toUpperCase() + name.slice(1).replace('_', ' ');

  const showErrors = (errors) => {
    if (!errorBox) {
      return;
    }
    if (errors.length === 0) {
      errorBox.style.display = 'none';
      errorBox.classList.remove('error');
      errorBox.innerHTML = '';
      return;
    }

    errorBox.style.display = 'block';
    errorBox.classList.add('error');
    errorBox.innerHTML = '<ul>' +
      errors.map(error => '<li>' + error + '</li>').join('') +
      '</ul>';
  };

  const clearFieldState = () => {
    form.querySelectorAll('.' + invalidClass).forEach((field) => field.classList.remove(invalidClass));
    showErrors([]);
  };

  const isPositiveNumber = (value) => {
    return /^\d+(?:[.,]\d+)?$/.test(value) && Number(value.replace(',', '.')) >= 0;
  };

  const validate = () => {
    clearFieldState();

    const fields = {
      date: form.querySelector('[name="date"]'),
      time: form.querySelector('[name="time"]'),
      meal_type: form.querySelector('[name="meal_type"]'),
      food: form.querySelector('[name="food"]'),
      quantity: form.querySelector('[name="quantity"]'),
      calories: form.querySelector('[name="calories"]'),
      proteins: form.querySelector('[name="proteins"]'),
      carbs: form.querySelector('[name="carbs"]'),
      fats: form.querySelector('[name="fats"]'),
    };

    const data = Object.fromEntries(Object.entries(fields).map(([key, input]) => [key, input.value.trim()]));
    const errors = [];
    const mealTypes = ['Petit-déjeuner', 'Déjeuner', 'Collation', 'Dîner'];

    if (!data.date) {
      errors.push('La date est requise.');
      fields.date.classList.add(invalidClass);
    }

if (!data.time) {
      errors.push('L\'heure est requise.');
      fields.time.classList.add(invalidClass);
    } else if (!/^\d{2}:\d{2}(?::\d{2})?$/.test(data.time)) {
      errors.push('Le format de l’heure doit être HH:MM ou HH:MM:SS.');
      fields.time.classList.add(invalidClass);
    }

    if (!mealTypes.includes(data.meal_type)) {
      errors.push('Le type de repas sélectionné est invalide.');
      fields.meal_type.classList.add(invalidClass);
    }

    if (!data.food) {
      errors.push('Le champ aliment est requis.');
      fields.food.classList.add(invalidClass);
    }

    if (!data.quantity) {
      errors.push('La quantité est requise.');
      fields.quantity.classList.add(invalidClass);
    } else if (data.quantity.length > 60) {
      errors.push('La quantité est trop longue.');
      fields.quantity.classList.add(invalidClass);
    }

    if (!isPositiveNumber(data.calories)) {
      errors.push('Les calories doivent être un nombre positif.');
      fields.calories.classList.add(invalidClass);
    }

    ['proteins', 'carbs', 'fats'].forEach((fieldName) => {
      if (!isPositiveNumber(data[fieldName])) {
        errors.push(formatFieldName(fieldName) + ' doit être un nombre positif ou 0.');
        fields[fieldName].classList.add(invalidClass);
      }
    });

    showErrors(errors);
    return errors.length === 0;
  };

  if (form) {
    form.addEventListener('submit', function (event) {
      if (!validate()) {
        event.preventDefault();
      }
    });

    form.querySelectorAll('input, select, textarea').forEach((input) => {
      input.addEventListener('input', clearFieldState);
    });
  }

  deleteButtons.forEach((button) => {
    button.addEventListener('click', function (event) {
      const confirmed = window.confirm('Voulez-vous vraiment supprimer cette entrée ?');
      if (!confirmed) {
        event.preventDefault();
      }
    });
  });

  // ========== VALIDATION SUIVI SANTÉ ==========
  const validateSuiviSante = (formElement) => {
    const fields = {
      date: formElement.querySelector('[name="date"]'),
      type_activite: formElement.querySelector('[name="type_activite"]'),
      duree: formElement.querySelector('[name="duree"]'),
      calories_brulees: formElement.querySelector('[name="calories_brulees"]'),
      intensite: formElement.querySelector('[name="intensite"]'),
      quantite_eau: formElement.querySelector('[name="quantite_eau"]'),
    };

    const data = Object.fromEntries(Object.entries(fields).map(([key, input]) => [key, (input?.value || '').trim()]));
    const errors = [];
    const validIntensities = ['faible', 'moyen', 'élevé'];

    // Validation date
    if (!data.date) {
      errors.push('La date est requise.');
      if (fields.date) fields.date.classList.add(invalidClass);
    }

    // Type d'activité : non vide, pas de nombre, pas de symbole
    if (!data.type_activite) {
      errors.push("Le type d'activité est requis.");
      fields.type_activite.classList.add(invalidClass);
    } else if (data.type_activite.length > 100) {
      errors.push("Le type d'activité ne doit pas dépasser 100 caractères.");
      fields.type_activite.classList.add(invalidClass);
    } else if (/[0-9]/.test(data.type_activite)) {
      errors.push("Le type d'activité ne doit pas contenir de chiffres.");
      fields.type_activite.classList.add(invalidClass);
    } else if (/[^a-zA-ZÀ-ÿ\s\-]/u.test(data.type_activite)) {
      errors.push("Le type d'activité ne doit pas contenir de symboles ou caractères spéciaux.");
      fields.type_activite.classList.add(invalidClass);
    }

    // Durée : entier positif, pas de symbole, pas de lettre
    if (!data.duree) {
      errors.push("La durée est requise.");
      fields.duree.classList.add(invalidClass);
    } else if (!/^\d+$/.test(data.duree)) {
      errors.push("La durée doit être un nombre entier positif sans symbole ni lettre.");
      fields.duree.classList.add(invalidClass);
    } else if (parseInt(data.duree) <= 0) {
      errors.push("La durée doit être strictement positive.");
      fields.duree.classList.add(invalidClass);
    } else if (parseInt(data.duree) > 1440) {
      errors.push("La durée ne peut pas dépasser 1440 minutes (24 heures).");
      fields.duree.classList.add(invalidClass);
    }

    // Calories brûlées : nombre positif ou 0, pas de symbole, pas de lettre
    if (!data.calories_brulees && data.calories_brulees !== '0') {
      errors.push("Les calories brûlées sont requises.");
      fields.calories_brulees.classList.add(invalidClass);
    } else if (!/^\d+(?:[.,]\d+)?$/.test(data.calories_brulees)) {
      errors.push("Les calories brûlées doivent être un nombre positif sans symbole ni lettre.");
      fields.calories_brulees.classList.add(invalidClass);
    } else if (parseFloat(data.calories_brulees.replace(',', '.')) < 0) {
      errors.push("Les calories brûlées doivent être positives ou nulles.");
      fields.calories_brulees.classList.add(invalidClass);
    } else if (parseFloat(data.calories_brulees.replace(',', '.')) > 99999.99) {
      errors.push("Les calories brûlées sont invalides.");
      fields.calories_brulees.classList.add(invalidClass);
    }

    // Intensité
    if (!data.intensite || !validIntensities.includes(data.intensite)) {
      errors.push("L'intensité doit être: faible, moyen ou élevé.");
      fields.intensite.classList.add(invalidClass);
    }

    // Quantité d'eau : nombre positif ou 0, pas de symbole, pas de lettre
    if (!data.quantite_eau && data.quantite_eau !== '0') {
      errors.push("La quantité d'eau est requise.");
      fields.quantite_eau.classList.add(invalidClass);
    } else if (!/^\d+(?:[.,]\d+)?$/.test(data.quantite_eau)) {
      errors.push("La quantité d'eau doit être un nombre positif sans symbole ni lettre.");
      fields.quantite_eau.classList.add(invalidClass);
    } else if (parseFloat(data.quantite_eau.replace(',', '.')) < 0) {
      errors.push("La quantité d'eau doit être positive ou nulle.");
      fields.quantite_eau.classList.add(invalidClass);
    } else if (parseFloat(data.quantite_eau.replace(',', '.')) > 99999.99) {
      errors.push("La quantité d'eau est invalide.");
      fields.quantite_eau.classList.add(invalidClass);
    }

    return errors.length === 0;
  };

  // Attacher validation au formulaire Suivi Santé si présent
  if (suiviForm) {
    suiviForm.addEventListener('submit', function (event) {
      if (!validateSuiviSante(suiviForm)) {
        event.preventDefault();
      }
    });
  }
});
