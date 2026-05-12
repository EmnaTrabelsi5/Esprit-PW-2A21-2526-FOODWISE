/**
 * Validation côté client (alertes personnalisées) — complète la validation PHP.
 * Aucun attribut HTML required / pattern sur les champs ciblés.
 */
(function () {
  'use strict';

  function val(id) {
    var el = document.getElementById(id);
    return el ? String(el.value || '').trim() : '';
  }

  function showErrors(messages) {
    window.alert(messages.join('\n'));
  }

  function parseIsoDate(raw) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
    if (!m) return null;
    var y = parseInt(m[1], 10);
    var mo = parseInt(m[2], 10);
    var d = parseInt(m[3], 10);
    var dt = new Date(y, mo - 1, d);
    if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) {
      return null;
    }
    return dt;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var planForm = document.querySelector('form[data-crud="plan_alimentaire"]');
    if (planForm) {
      var startInput = document.getElementById('date_debut');
      var endInput = document.getElementById('date_fin');
      var syncDateBounds = function () {
        if (!startInput || !endInput) return;
        endInput.min = startInput.value || '';
        startInput.max = endInput.value || '';
      };
      if (startInput && endInput) {
        syncDateBounds();
        startInput.addEventListener('change', syncDateBounds);
        endInput.addEventListener('change', syncDateBounds);
      }

      planForm.addEventListener('submit', function (e) {
        var errs = [];
        var titre = val('titre');
        var d0 = val('date_debut');
        var d1 = val('date_fin');
        var statut = val('statut');
        if (!titre) errs.push('Le titre est obligatoire.');
        if (titre.length > 200) errs.push('Le titre ne doit pas dépasser 200 caractères.');
        if (!d0) errs.push('La date de début est obligatoire.');
        if (!d1) errs.push('La date de fin est obligatoire.');
        if (!statut) errs.push('Le statut est obligatoire.');

        var startDate = d0 ? parseIsoDate(d0) : null;
        var endDate = d1 ? parseIsoDate(d1) : null;
        if (d0 && !startDate) errs.push('Date de début invalide.');
        if (d1 && !endDate) errs.push('Date de fin invalide.');
        if (startDate && endDate && startDate.getTime() > endDate.getTime()) {
          errs.push('La date de début doit précéder ou égaler la date de fin.');
        }
        var cal = val('calories_cible');
        if (cal !== '' && !/^\d+$/.test(cal)) errs.push('Les calories cibles doivent être un entier.');
        if (cal !== '' && /^\d+$/.test(cal)) {
          var n = parseInt(cal, 10);
          if (n < 800 || n > 8000) errs.push('Les calories cibles doivent être entre 800 et 8000.');
        }
        var desc = val('description');
        if (desc.length > 5000) errs.push('La description est trop longue (5000 caractères max).');
        if (errs.length) {
          e.preventDefault();
          showErrors(errs);
        }
      });
    }

    var recForm = document.querySelector('form[data-crud="plan_recette"]');
    if (recForm) {
      recForm.addEventListener('submit', function (e) {
        var errs = [];
        var planSel = document.getElementById('plan_alimentaire_id');
        if (planSel && planSel.tagName === 'SELECT') {
          if (!planSel.value) errs.push('Vous devez choisir un plan alimentaire.');
        }
        var jour = val('jour_semaine');
        var creneau = val('creneau');
        var nom = val('nom_recette');
        if (!jour) errs.push('Le jour est obligatoire.');
        if (!creneau) errs.push('Le créneau est obligatoire.');
        if (!nom) errs.push('Le nom de la recette est obligatoire.');
        if (nom.length > 200) errs.push('Le nom de la recette ne doit pas dépasser 200 caractères.');
        var duree = val('duree_minutes');
        if (duree !== '' && !/^\d+$/.test(duree)) errs.push('La durée doit être un entier (minutes).');
        if (duree !== '' && /^\d+$/.test(duree)) {
          var m = parseInt(duree, 10);
          if (m < 1 || m > 600) errs.push('La durée doit être entre 1 et 600 minutes.');
        }
        var notes = val('notes');
        if (notes.length > 2000) errs.push('Les notes sont trop longues (2000 caractères max).');
        if (errs.length) {
          e.preventDefault();
          showErrors(errs);
        }
      });
    }

    var objectifForm = document.querySelector('form[data-crud="objectif"]');
    if (objectifForm) {
      objectifForm.addEventListener('submit', function (e) {
        var errs = [];
        var type = val('type');
        var age = val('age');
        var poids = val('poids_kg');
        var taille = val('taille_cm');
        var sexe = val('sexe');
        var activite = val('niveau_activite');

        if (!type) errs.push("Le type d'objectif est obligatoire.");
        if (!age) errs.push("L'age est obligatoire.");
        if (!poids) errs.push('Le poids est obligatoire.');
        if (!taille) errs.push('La taille est obligatoire.');
        if (!sexe) errs.push('Le sexe est obligatoire.');
        if (!activite) errs.push("Le niveau d'activite est obligatoire.");

        if (age && !/^\d+$/.test(age)) errs.push("L'age doit etre un entier.");
        if (age && /^\d+$/.test(age)) {
          var a = parseInt(age, 10);
          if (a < 15 || a > 100) errs.push("L'age doit etre compris entre 15 et 100 ans.");
        }

        if (poids && !/^\d+$/.test(poids)) errs.push('Le poids doit etre un entier positif.');
        if (poids && /^\d+$/.test(poids) && parseInt(poids, 10) <= 0) errs.push('Le poids doit etre strictement positif.');

        if (taille && !/^\d+$/.test(taille)) errs.push('La taille doit etre un entier positif.');
        if (taille && /^\d+$/.test(taille) && parseInt(taille, 10) <= 0) errs.push('La taille doit etre strictement positive.');

        if (errs.length) {
          e.preventDefault();
          showErrors(errs);
        }
      });
    }
  });
})();
