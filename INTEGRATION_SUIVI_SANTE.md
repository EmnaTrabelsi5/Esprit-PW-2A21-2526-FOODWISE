# 🔧 Guide d'Intégration - SuiviSante

## Mise en place dans votre projet existant

### 1️⃣ DATABASE - Créer la table

Exécutez le SQL suivant dans votre base de données:

```sql
CREATE TABLE IF NOT EXISTS suivi_sante (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL DEFAULT 1,
  date DATE NOT NULL,
  type_activite VARCHAR(100) NOT NULL,
  duree INT NOT NULL COMMENT 'Durée en minutes',
  calories_brulees DECIMAL(8,2) NOT NULL DEFAULT 0,
  intensite VARCHAR(20) NOT NULL DEFAULT 'moyen' COMMENT 'faible, moyen, élevé',
  quantite_eau DECIMAL(8,2) NOT NULL DEFAULT 0 COMMENT 'En mL',
  note TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_date_activity (user_id, date, type_activite),
  INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Ou** utilisez le fichier SQL fourni: `database.sql`

---

### 2️⃣ FICHIERS - Vérifier que tous les fichiers sont en place

```
✅ model/SuiviSante.php
✅ controller/SuiviSanteController.php
✅ view/front/ajouter-suivi.php
✅ view/front/modifier-suivi.php
✅ view/front/consulter-suivi.php
✅ view/back/dashboard-suivi.php
✅ view/back/manage-suivi.php
✅ view/back/modifier-suivi.php
✅ public/js/validation.js (enrichi)
✅ public/css/foodwise.css (à adapter si nécessaire)
```

---

### 3️⃣ NAVIGATION - Ajouter les liens dans vos templates

#### Dans `view/template/header.php` (Front-office)

Ajoutez dans le menu de navigation principal:

```php
<!-- Menu déroulant Santé -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navDropdownSante" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-heartbeat"></i> Santé
    </a>
    <ul class="dropdown-menu" aria-labelledby="navDropdownSante">
        <li>
            <a class="dropdown-item" href="./consulter-suivi.php">
                <i class="fas fa-chart-line"></i> Mon Suivi du Jour
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="./ajouter-suivi.php">
                <i class="fas fa-plus-circle"></i> Ajouter une Activité
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="./journal-alimentaire.php">
                <i class="fas fa-utensils"></i> Mon Journal
            </a>
        </li>
    </ul>
</li>
```

#### Dans le back-office (`view/back/dashboard-admin.php`)

Ajoutez un lien vers le dashboard santé:

```php
<!-- Section Santé -->
<div class="col-md-6 mb-4">
    <div class="card text-center">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-heartbeat text-success"></i> Suivi Santé
            </h5>
            <p class="card-text">Gérer l'activité physique et hydratation</p>
            <a href="./dashboard-suivi.php" class="btn btn-outline-success">
                <i class="fas fa-chart-bar"></i> Voir le Dashboard
            </a>
            <a href="./manage-suivi.php" class="btn btn-outline-primary">
                <i class="fas fa-cogs"></i> Gérer les Suivis
            </a>
        </div>
    </div>
</div>
```

---

### 4️⃣ CSS - Styles (optionnel, si besoin)

Si vous voulez ajouter des styles personnalisés, ajoutez dans `public/css/foodwise.css`:

```css
/* Suivi Santé - Couleurs des intensités */
.intensity-faible {
    background-color: #e7f3ff;
    border-left: 4px solid #0066cc;
}

.intensity-moyen {
    background-color: #fff4e6;
    border-left: 4px solid #ff9800;
}

.intensity-eleve {
    background-color: #ffe6e6;
    border-left: 4px solid #dc3545;
}

/* Cards statistiques */
.stat-card {
    border-radius: 8px;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
```

---

### 5️⃣ AUTHENTIFICATION - Adapter le user_id

Selon votre système d'authentification, adaptez dans chaque vue:

**Avec Sessions:**
```php
$userId = $_SESSION['user_id'] ?? 1;
```

**Avec classe Auth:**
```php
$userId = Auth::getCurrentUser()['id'];
```

**Autre système:**
```php
$userId = $user->getId();
```

---

### 6️⃣ CONFIGURATION - Modifier settings.php si besoin

Vérifiez dans `config/settings.php`:

```php
<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'foodlog');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

---

## 🧪 Tests de vérification

### Test 1: Vérifier que la table est créée

```bash
mysql -u root -p foodlog -e "DESC suivi_sante;"
```

Résultat attendu:
```
+-------------------+---------------+
| Field             | Type          |
+-------------------+---------------+
| id                | int           |
| user_id           | int           |
| date              | date          |
| type_activite     | varchar(100)  |
| duree             | int           |
| calories_brulees  | decimal(8,2)  |
| intensite         | varchar(20)   |
| quantite_eau      | decimal(8,2)  |
| note              | text          |
| created_at        | datetime      |
| updated_at        | datetime      |
+-------------------+---------------+
```

### Test 2: Ajouter manuellement un suivi (SQL)

```sql
INSERT INTO suivi_sante 
(user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note)
VALUES 
(1, '2024-04-21', 'Course', 30, 250.50, 'moyen', 500, 'Test');
```

### Test 3: Tester le formulaire front

1. Allez à `http://localhost/projet_M5/view/front/ajouter-suivi.php`
2. Remplissez le formulaire
3. Vérifiez que le suivi est ajouté en BD

### Test 4: Tester le back-office

1. Allez à `http://localhost/projet_M5/view/back/dashboard-suivi.php`
2. Vérifiez que les stats s'affichent
3. Allez à `http://localhost/projet_M5/view/back/manage-suivi.php`
4. Vérifiez que le CRUD fonctionne

---

## 🔄 Intégration avec Journal Alimentaire

Le suivi santé et le journal alimentaire sont intégrés pour afficher:
- Calories consommées (Journal)
- Calories brûlées (SuiviSante)
- Bilan calorique (différence)

**Page d'intégration**: `view/front/consulter-suivi.php`

Cette page affiche automatiquement:
```
Calories Consommées: XXX kcal
Calories Brûlées: XXX kcal
Bilan: +/- XXX kcal
```

---

## 📱 Points de contact clés

### Classe Model
```php
require_once __DIR__ . '/../model/SuiviSante.php';
$model = new SuiviSante($pdo);
```

### Classe Controller
```php
require_once __DIR__ . '/../controller/SuiviSanteController.php';
$controller = new SuiviSanteController($pdo);
```

### Utilisation dans une vue
```php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/SuiviSanteController.php';

$pdo = require __DIR__ . '/../../config/config.php';
$controller = new SuiviSanteController($pdo);
$userId = $_SESSION['user_id'] ?? 1;
?>
```

---

## ⚠️ Erreurs courantes et solutions

| Erreur | Cause | Solution |
|--------|-------|----------|
| `SQLSTATE[42S02]: Table 'foodlog.suivi_sante' doesn't exist` | Table non créée | Exécuter le SQL CREATE TABLE |
| `Call to undefined function htmlspecialchars()` | Fonction non définie | Vérifier la version PHP (>= 5.4) |
| `UNIQUE constraint failed` | Doublon user_id + date + type_activite | Modifier ou supprimer le doublon |
| Validation ne fonctionne pas | Form ID incorrect | Vérifier que l'ID du form est `suiviForm` |
| Statistiques vides | Pas de donnés en BD | Ajouter au moins un suivi |

---

## 🔐 Sécurité - Checklist

- [x] PDO avec prepared statements
- [x] htmlspecialchars() sur tous les affichages
- [x] Validation côté serveur
- [x] Validation côté client
- [x] Vérification d'accès user_id
- [x] Contrainte UNIQUE en BD
- [ ] **À faire**: Authentification complète (adapter selon votre système)
- [ ] **À faire**: Rate limiting sur les requêtes
- [ ] **À faire**: CSRF tokens (si formulaires non intégrés)

---

## 🚀 Déploiement

### Avant de mettre en production

```bash
# 1. Vérifier tous les fichiers sont en place
ls -la model/SuiviSante.php
ls -la controller/SuiviSanteController.php
ls -la view/front/ajouter-suivi.php
ls -la view/back/dashboard-suivi.php

# 2. Exécuter le SQL
mysql -u root -p foodlog < database.sql

# 3. Tester les formulaires
# >> Formulaire d'ajout: ../view/front/ajouter-suivi.php
# >> Dashboard admin: ../view/back/dashboard-suivi.php

# 4. Adapter l'authentification
# >> Chercher tous les "user_id" et adapter

# 5. Vérifier les permissions
chmod 644 public/js/validation.js
chmod 755 view/front/
chmod 755 view/back/
```

---

## 📚 Ressources additionnelles

- PDO Documentation: https://www.php.net/manual/en/book.pdo.php
- MVC Pattern: https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller
- Bootstrap 5: https://getbootstrap.com/docs/5.0/
- Font Awesome: https://fontawesome.com/icons

---

## ✅ Validation finale

Avant de considérer l'intégration comme complète:

- [ ] Table créée et contient des données
- [ ] Formulaire d'ajout fonctionne
- [ ] Formulaire de modification fonctionne
- [ ] Suppression fonctionne
- [ ] Dashboard affiche les stats
- [ ] Intégration avec Journal visible
- [ ] Validation client et serveur fonctionne
- [ ] Responsive sur mobile
- [ ] Pas d'erreurs PHP en logs

---

**Statut**: Guide complet  
**Version**: 1.0  
**Date**: Avril 2024
