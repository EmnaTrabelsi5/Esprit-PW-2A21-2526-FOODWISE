document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#entry-form');
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

    if (data.time && !/^\d{2}:\d{2}(?::\d{2})?$/.test(data.time)) {
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
});
