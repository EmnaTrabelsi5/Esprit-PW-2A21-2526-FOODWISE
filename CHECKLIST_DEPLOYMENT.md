# ✅ CHECKLIST DE DÉPLOIEMENT - SuiviSante

> Utilisez cette checklist pour vous assurer que tout fonctionne avant production

---

## PHASE 1: VÉRIFICATION DES FICHIERS

### Model
- [ ] `model/SuiviSante.php` exists and readable
- [ ] File size > 10KB
- [ ] No syntax errors (`php -l model/SuiviSante.php`)

### Controllers
- [ ] `controller/SuiviSanteController.php` exists
- [ ] File size > 8KB
- [ ] No syntax errors

### Views Front
- [ ] `view/front/ajouter-suivi.php` exists
- [ ] `view/front/modifier-suivi.php` exists
- [ ] `view/front/consulter-suivi.php` exists
- [ ] All 3 accessible via browser

### Views Back
- [ ] `view/back/dashboard-suivi.php` exists
- [ ] `view/back/manage-suivi.php` exists
- [ ] `view/back/modifier-suivi.php` exists
- [ ] All 3 accessible via browser

### Database
- [ ] `database.sql` exists
- [ ] Contains full CREATE TABLE statement
- [ ] Has proper constraints and indexes

### Frontend
- [ ] `public/js/validation.js` modified
- [ ] Contains validation for SuiviSante
- [ ] No syntax errors in JS console

### Documentation
- [ ] `SUIVI_SANTE_DOCUMENTATION.md` exists
- [ ] `INTEGRATION_SUIVI_SANTE.md` exists
- [ ] `EXEMPLES_UTILISATION.php` exists
- [ ] Readme files are readable

---

## PHASE 2: BASE DE DONNÉES

### Création de la table
```bash
$ mysql -u root -p foodlog < database.sql
```

Vérifier:
- [ ] Command executed without error
- [ ] No "already exists" error (optional)

Vérifier la structure:
```bash
$ mysql -u root -p foodlog -e "DESC suivi_sante;"
```

- [ ] 11 columns présentes
- [ ] id is PRIMARY KEY AUTO_INCREMENT
- [ ] Indexes created (UNIQUE, INDEX)
- [ ] Default values set

Vérifier les contraintes:
```bash
$ mysql -u root -p foodlog -e "SHOW CREATE TABLE suivi_sante\G"
```

- [ ] UNIQUE constraint on (user_id, date, type_activite)
- [ ] Single INDEX on (user_id, date)
- [ ] Proper CHARSET (utf8mb4)

### Données de test (optionnel)
```bash
$ mysql -u root -p foodlog < donnees_test_suivi_sante.sql
```

Vérifier:
- [ ] Inserted 30+ rows without error
- [ ] Data visible: `SELECT * FROM suivi_sante;`

---

## PHASE 3: AUTHENTIFICATION

### Adapter user_id

Chercher dans chaque VUE:
```grep
grep -r "user_id = \$_SESSION" view/
```

Pour chaque occurrence, adapter selon votre système:
```php
// Option 1 (Sessions)
$userId = $_SESSION['user_id'] ?? 1;

// Option 2 (Auth class)
$userId = Auth::getUser()['id'];

// Option 3 (JWT)
$userId = $jwtToken->getClaim('user_id');
```

Tasks:
- [ ] Adapter `view/front/ajouter-suivi.php`
- [ ] Adapter `view/front/modifier-suivi.php`
- [ ] Adapter `view/front/consulter-suivi.php`
- [ ] Adapter `view/back/modifier-suivi.php`

Vérifier que les autres fichiers ne nécessitent pas d'adaptation:
- [ ] `view/back/dashboard-suivi.php` (OK)
- [ ] `view/back/manage-suivi.php` (OK)

---

## PHASE 4: TESTS MANUELS FRONT-OFFICE

### Test 1: Ajouter un suivi
```
1. Accédez à: view/front/ajouter-suivi.php
2. Remplissez les champs:
   - Date: Aujourd'hui
   - Activité: "Course"
   - Durée: 30
   - Calories: 250.5
   - Intensité: "moyen"
   - Eau: 500
   - Note: "Test"
3. Cliquez "Enregistrer"
```

Vérifier:
- [ ] Message "Succès" affiché
- [ ] Pas d'erreur PHP
- [ ] Données dans BD: `SELECT * FROM suivi_sante WHERE type_activite='Course';`

### Test 2: Validation client
```
1. Allez à: view/front/ajouter-suivi.php
2. Remplissez mal:
   - Durée: 0 (devrait être rejeté)
   - Calories: -50 (devrait être rejeté)
   - Eau: "abc" (devrait être rejeté)
3. Cliquez "Enregistrer"
```

Vérifier:
- [ ] Erreurs affichées avant envoi
- [ ] Champs surlignés en rouge
- [ ] Pas d'appel serveur

### Test 3: Modifier un suivi
```
1. Allez à: view/front/consulter-suivi.php
2. Sélectionnez aujourd'hui
3. Cliquez "Modifier" sur un suivi
4. Changez les données
5. Cliquez "Enregistrer"
```

Vérifier:
- [ ] Formulaire pré-rempli avec données actuelles
- [ ] Timestamps affichés (created/updated)
- [ ] Données mises à jour en BD

### Test 4: Supprimer un suivi
```
1. Allez à: view/front/consulter-suivi.php
2. Cliquez "Supprimer" sur un suivi
3. Confirmez le dialogue
```

Vérifier:
- [ ] Message de confirmation
- [ ] Suivi disparu de la liste
- [ ] Pas en BD: `SELECT * FROM suivi_sante WHERE id=X;`

### Test 5: Consulter la journée
```
1. Allez à: view/front/consulter-suivi.php
2. Sélectionnez une date
3. Observez l'affichage
```

Vérifier:
- [ ] Calories consommées affichées (intégration Journal)
- [ ] Calories brûlées affichées
- [ ] Bilan automatique calculé
- [ ] Eau affichée avec % objectif (2L)
- [ ] Tableau des activités
- [ ] Durée totale affichée

### Test 6: Responsive design
```
1. Ouvrez view/front/ajouter-suivi.php
2. Réduisez la fenêtre (mobile)
3. Testez sur smartphone
```

Vérifier:
- [ ] Layout adapté (pas de scroll horizontal)
- [ ] Boutons cliquables
- [ ] Texte lisible
- [ ] Navigation OK

---

## PHASE 5: TESTS BACK-OFFICE

### Test 7: Dashboard statistiques
```
1. Allez à: view/back/dashboard-suivi.php
2. Observez les statistiques
```

Vérifier:
- [ ] Total suivis correct
- [ ] Utilisateurs actifs correct
- [ ] Calories totales = SUM correct
- [ ] Stats par activité affichées
- [ ] Stats par intensité affichées
- [ ] Top 10 activités visible

Valider avec SQL:
```sql
SELECT COUNT(*) FROM suivi_sante;
SELECT COUNT(DISTINCT user_id) FROM suivi_sante;
SELECT SUM(calories_brulees) FROM suivi_sante;
```

### Test 8: Gestion CRUD
```
1. Allez à: view/back/manage-suivi.php
2. Vérifiez le tableau
3. Testez filtres et recherche
4. Testez pagination
```

Vérifier:
- [ ] Tous les suivis listés
- [ ] Colonnes correctes
- [ ] Pagination fonctionne
- [ ] Boutons modifier/supprimer présents
- [ ] Filtres fonctionnent

### Test 9: Modifier depuis admin
```
1. Cliquez "modifier" sur un suivi
2. Changez les données
3. Enregistrez
```

Vérifier:
- [ ] Formulaire pré-rempli
- [ ] Données sauvegardées
- [ ] Redirection vers manage

---

## PHASE 6: VALIDATION

### Test 10: Validation serveur
```
Tester en modifiant les données POST (DevTools Network):

1. Durée = -1           → Erreur affichée
2. Calories = "abc"     → Erreur affichée
3. Intensité = "nope"   → Erreur affichée
4. Eau = -500           → Erreur affichée
```

Vérifier:
- [ ] Aucuns données invalides acceptées
- [ ] Messages d'erreur clairs

### Test 11: Validation client
```
Via formulaire normal:

1. Laissez date vide    → Erreur JS
2. Durée <= 0           → Erreur JS
3. Calories < 0         → Erreur JS
```

Vérifier:
- [ ] Erreurs avant envoi
- [ ] Pas d'appel serveur
- [ ] Champs surlignés

---

## PHASE 7: INTÉGRATION JOURNAL

### Test 12: Rapport combiné
```
1. Allez à: view/front/consulter-suivi.php
2. Sélectionnez un jour avec:
   - Entrées Journal (aliments)
   - Suivis santé (activité)
```

Vérifier:
- [ ] Calories consommées = SUM de Journal
- [ ] Calories brûlées = SUM de Suivi
- [ ] Bilan = consommées - brûlées
- [ ] Affichage user-friendly

Valider avec SQL:
```sql
-- Calories Journal
SELECT COALESCE(SUM(calories), 0) 
FROM entrees_journal 
WHERE user_id=1 AND date='2024-04-21';

-- Calories SuiviSante
SELECT COALESCE(SUM(calories_brulees), 0) 
FROM suivi_sante 
WHERE user_id=1 AND date='2024-04-21';
```

---

## PHASE 8: SÉCURITÉ

### Test 13: SQL Injection
```
Essayez dans le formulaire:
- Type activité: "'; DROP TABLE suivi_sante; --"
```

Vérifier:
- [ ] Aucune erreur SQL
- [ ] Table toujours présente
- [ ] Donnée sauvegardée littéralement si nécessaire

### Test 14: XSS
```
Type activité: "<script>alert('XSS')</script>"
```

Vérifier:
- [ ] Pas d'alerté pop-up
- [ ] Code stocké littéralement en BD
- [ ] Affichage sans exécution du JS

### Test 15: Contrôle d'accès
```
1. Connectez-vous comme User 1
2. Modifiez directement l'URL:
   view/front/modifier-suivi.php?id=[suivi_user_2]
```

Vérifier:
- [ ] Accès refusé OU
- [ ] Redirection vers liste

---

## PHASE 9: NAVIGATION

### Test 16: Intégration menus
- [ ] Lien front-office ajouté dans header
- [ ] Lien dashboard admin ajouté
- [ ] Lien manage-suivi admin ajouté
- [ ] Liens de retour fonctionnent

Test:
```
1. Cliquez lien depuis menu
2. Page charge correctement
3. Contenu visible sans erreur
```

---

## PHASE 10: LOGS ET ERREURS

### Test 17: Erreurs PHP
```bash
$ tail -f /var/log/php-errors.log
```

Pendant tous les tests précédents:
- [ ] Aucune erreur PHP
- [ ] Aucun warning
- [ ] Aucun notice

Vérifier dans le fichier logs du serveur:
```bash
$ grep -i "error" /var/log/apache2/error.log | tail -20
```

### Test 18: Erreurs JavaScript
```
1. Ouvrez DevTools (F12)
2. Allez à Console
3. Faites tous les tests précédents
```

Vérifier:
- [ ] Aucune erreur JS en rouge
- [ ] Aucun warning important

---

## PHASE 11: PERFORMANCE

### Test 19: Temps de chargement
```bash
$ curl -w "@curl-format.txt" -o /dev/null -s http://localhost/projet_M5/view/back/manage-suivi.php
```

Vérifier:
- [ ] Dashboard charge en < 1s
- [ ] Manager charge en < 1s
- [ ] Pas de timeout

### Test 20: Requêtes BD
```
Durant tests:
1. Ouvrez Browser DevTools
2. Network tab
3. Comptez requêtes XHR
```

Vérifier:
- [ ] Pas de requêtes dupliquées
- [ ] Pas de requêtes inutiles
- [ ] Temps réponse BD < 200ms

---

## PHASE 12: DONNÉES MASSIVES

### Test 21: Avec 1000+ enregistrements

Insérez:
```sql
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau)
SELECT 
  (RAND() * 5) + 1 as user_id,
  DATE_SUB(CURRENT_DATE, INTERVAL RAND() * 100 DAY) as date,
  ELT(FLOOR(RAND()*10)+1, 'Course', 'Marche', 'Fitness', 'Natation', 'Yoga', 'Cyclisme', 'Musculation', 'Danse', 'HIIT', 'Escalade'),
  FLOOR(RAND() * 120) + 10 as duree,
  RAND() * 500 as calories_brulees,
  ELT(FLOOR(RAND()*3)+1, 'faible', 'moyen', 'élevé') as intensite,
  FLOOR(RAND() * 1500) as quantite_eau
FROM (SELECT 1 UNION SELECT 2 UNION SELECT 3) t
LIMIT 1000;
```

Vérifier:
- [ ] Dashboard charge toujours rapidement
- [ ] Pagination fonctionne
- [ ] Stats correctes
- [ ] Pas de timeout

---

## PHASE 13: NAVIGATEURS

Test sur:
- [ ] Chrome/Chromium (latest)
- [ ] Firefox (latest)
- [ ] Safari (si Mac)
- [ ] Edge (si Windows)
- [ ] Mobile Safari (iPhone)
- [ ] Chrome Mobile (Android)

Vérifier:
- [ ] Layout OK sur tous
- [ ] Formulaires OK
- [ ] Pas d'erreurs console

---

## CHECKLIST FINALE

### Avant production:
- [ ] Tous les tests PASS
- [ ] Aucune erreur PHP
- [ ] Aucune erreur JS
- [ ] Aucune erreur SQL
- [ ] Authentification adaptée
- [ ] Navigation intégrée
- [ ] Données sensibles non exposées
- [ ] Passwords hashés (si applicable)

### Backup:
- [ ] BD sauvegardée
- [ ] Code versionné (Git)
- [ ] Documentation à jour

### Final check:
- [ ] Responsable de projet a validé
- [ ] Tests d'acceptation utilisateur OK
- [ ] Documentation accessible aux users
- [ ] Plan de support en place

---

## 🚀 DÉPLOIEMENT APPROUVÉ

Une fois toutes les cases cochées:

```
✅ READY FOR PRODUCTION
```

---

## POINTS DE CONTACT EN PRODUCTION

### Erreurs rencontrées
1. Vérifier logs: `/var/log/php-errors.log`
2. Vérifier BD: `SELECT * FROM suivi_sante LIMIT 5;`
3. Vérifier permissions: `ls -la model/`, `ls -la view/`

### Support urgences
- Créer backup BD
- Vérifier le dernier changement
- Revert si mecessaire

---

**Statut**: Checklist complète  
**Version**: 1.0  
**Mise à jour**: Avril 2024

✅ Prêt pour production!
