# 📋 Entité SuiviSante - Documentation Complète

## 📖 Vue d'ensemble

L'entité `SuiviSante` regroupeendage l'activité physique et la consommation d'eau pour suivre l'état de santé global des utilisateurs. Elle est intégrée à l'architecture MVC existante avec validation complète côté serveur et client.

---

## 🗂️ Structure des Fichiers

### Model
- **`model/SuiviSante.php`** - Classe model avec CRUD complet
  - `add()` - Ajouter un suivi
  - `update()` - Modifier un suivi
  - `delete()` - Supprimer un suivi
  - `getAll()` - Récupérer tous les suivis (admin)
  - `getById()` - Récupérer un suivi par ID
  - `getByUserAndDate()` - Récupérer les suivis d'un jour
  - `getTotalCaloriesBurnedByUserAndDate()` - Calories brûlées du jour
  - `getTotalWaterByUserAndDate()` - Eau consommée du jour
  - `getTotalActivityDurationByUserAndDate()` - Durée activité du jour
  - `getGlobalStats()` - Statistiques globales
  - `getStatsByActivityType()` - Stats par type d'activité
  - `getStatsByIntensity()` - Stats par intensité
  - `getMostFrequentActivities()` - Top activités

### Controller
- **`controller/SuiviSanteController.php`** - Contrôleur avec gestion des routes
  - `validateSuiviData()` - Validation serveur complète
  - `addSuivi()`, `updateSuivi()`, `deleteSuivi()` - Opérations CRUD
  - `getDailySuivis()` - Suivis du jour
  - `getDailyCaloriesBurned()` - Calories brûlées du jour
  - `getDailyWaterIntake()` - Eau du jour
  - `getGlobalStats()` - Stats admin
  - `getDailyHealthReport()` - Rapport combiné avec Journal

### Views - Front-office
- **`view/front/ajouter-suivi.php`** - Formulaire d'ajout
- **`view/front/modifier-suivi.php`** - Formulaire de modification
- **`view/front/consulter-suivi.php`** - Affichage du suivi du jour avec intégration Journal

### Views - Back-office
- **`view/back/dashboard-suivi.php`** - Dashboard avec statistiques globales
- **`view/back/manage-suivi.php`** - Gestion complète des suivis (CRUD)
- **`view/back/modifier-suivi.php`** - Modifier depuis back-office

### Base de Données
- **`database.sql`** - Table `suivi_sante` avec contraintes

### JavaScript
- **`public/js/validation.js`** - Enrichi avec validation client pour SuiviSante

---

## 📊 Schéma de la Base de Données

```sql
CREATE TABLE suivi_sante (
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
);
```

### Champs
| Champ | Type | Description |
|-------|------|-------------|
| `id` | INT | Identifiant unique, auto-incrémenté |
| `user_id` | INT | ID de l'utilisateur (clé étrangère) |
| `date` | DATE | Date du suivi |
| `type_activite` | VARCHAR(100) | Type d'activité (Marche, Course, etc.) |
| `duree` | INT | Durée en minutes |
| `calories_brulees` | DECIMAL(8,2) | Calories brûlées (kcal) |
| `intensite` | VARCHAR(20) | Intensité (faible/moyen/élevé) |
| `quantite_eau` | DECIMAL(8,2) | Quantité d'eau en mL |
| `note` | TEXT | Note optionnelle |
| `created_at` | DATETIME | Timestamp de création |
| `updated_at` | DATETIME | Timestamp de modification |

---

## 🧪 Règles de Validation

### Côté Serveur (PHP)
```php
// Dans SuiviSanteController::validateSuiviData()

✓ Date: requise, format YYYY-MM-DD
✓ Type d'activité: requise, max 100 caractères
✓ Durée: requise, entier > 0, max 1440 min (24h)
✓ Calories brûlées: requise, >= 0, max 99999.99
✓ Intensité: requise, valeur dans [faible, moyen, élevé]
✓ Quantité d'eau: requise, >= 0, max 99999.99
✓ Note: optionnelle, max 500 caractères
```

### Côté Client (JavaScript)
```javascript
// Dans public/js/validation.js -> validateSuiviSante()

✓ Même validation que serveur
✓ Affichage immédiat des erreurs
✓ Highlight des champs invalides
```

---

## 🔗 Intégration avec Journal Alimentaire

### Points d'intégration
1. **Rapports combinés**: Calories consommées vs brûlées
2. **Bilan calorique**: Surplus/Déficit automatique
3. **Visualisation unique**: Sur la page de consultation du jour

### Exemple d'utilisation
```php
$journalController = new JournalController($pdo);
$suiviController = new SuiviSanteController($pdo);

// Rapport complet d'une journée
$report = $suiviController->getDailyHealthReport(
    $userId, 
    '2024-04-21', 
    $journalController
);

echo "Calories consommées: " . $report['calories_consumed'];
echo "Calories brûlées: " . $report['total_calories_burned'];
echo "Bilan: " . $report['balance'];
```

---

## 🎯 Fonctionnalités

### Front-office (Utilisateur)
- ✅ Ajouter un suivi santé
- ✅ Modifier un suivi existant
- ✅ Supprimer un suivi
- ✅ Consulter le suivi du jour
- ✅ Voir les calories brûlées vs consommées
- ✅ Tracker la consommation d'eau

### Back-office (Admin)
- ✅ Lister tous les suivis santé
- ✅ Modifier/Supprimer un suivi
- ✅ Voir statistiques globales
- ✅ Statistiques par type d'activité
- ✅ Statistiques par intensité
- ✅ Top 10 activités les plus fréquentes
- ✅ Pagination des résultats

### Statistiques disponibles
- Total des suivis enregistrés
- Nombre d'utilisateurs actifs
- Calories brûlées totales
- Jours suivis
- Durée totale d'activité
- Répartition par type d'activité
- Répartition par intensité

---

## 💻 Utilisation de l'API Model

### Exemple 1: Ajouter un suivi
```php
$suiviModel = new SuiviSante($pdo);

$data = [
    'user_id' => 1,
    'date' => '2024-04-21',
    'type_activite' => 'Course',
    'duree' => 30,
    'calories_brulees' => 250.50,
    'intensite' => 'moyen',
    'quantite_eau' => 500,
    'note' => 'Bon effort'
];

if ($suiviModel->add($data)) {
    echo "Suivi ajouté!";
}
```

### Exemple 2: Récupérer les suivis du jour
```php
$suivis = $suiviModel->getByUserAndDate(1, '2024-04-21');

foreach ($suivis as $suivi) {
    echo $suivi['type_activite'] . ": " . $suivi['duree'] . " min";
}
```

### Exemple 3: Obtenir les statistiques
```php
$stats = $suiviModel->getGlobalStats();

echo "Total: " . $stats['total_followups'];
echo "Utilisateurs: " . $stats['total_users'];
echo "Calories: " . $stats['total_calories_burned'];
```

---

## 🔒 Sécurité

### Mesures implémentées
1. **Prepared Statements (PDO)** - Prévention SQL injection
2. **Type casting** - Validation des types
3. **Validation multiples niveaux** - Serveur + Client
4. **Sanitization HTML** - htmlspecialchars() pour l'affichage
5. **Vérification d'accès** - Contrôle user_id pour modifications
6. **Constraintes DB** - UNIQUE sur user_id + date + type_activite

### Exemple: Protection accès utilisateur
```php
public function userHasAccessToSuivi(int $userId, int $suiviId): bool {
    $suivi = $this->model->getById($suiviId);
    return $suivi !== null && (int)$suivi['user_id'] === $userId;
}
```

---

## 🚀 Routes REC commandées (à implémenter si routeur externe)

```
GET   /suivi                      - Lister mes suivis
POST  /suivi                      - Ajouter un suivi
GET   /suivi/add                  - Formulaire d'ajout
GET   /suivi/{id}/edit            - Formulaire de modification
POST  /suivi/{id}                 - Modifier un suivi
POST  /suivi/{id}/delete          - Supprimer un suivi
GET   /suivi/date/{date}          - Suivis d'un jour
GET   /admin/suivi                - Admin: lister tout
GET   /admin/suivi/stats          - Admin: statistiques
GET   /admin/suivi/{id}/edit      - Admin: modifier
```

---

## 📱 Types d'Activité Prédéfinis

Vous pouvez ajouter d'autres types, ceux-ci sont suggérés:
- Marche
- Course
- Fitness
- Natation
- Cyclisme
- Yoga
- Musculation
- Danse
- Sports collectifs
- Escalade
- Boxe
- TennisE
- Randonnée
- Vélo d'appartement
- VTT
- Jogging
- Gym aquatique

---

## 🧩 Points d'extension Future

1. **Importation de données** - Depuis smartwatch/fitness tracker
2. **Objectifs quotidiens** - Configuration personnalisée
3. **Graphiques avancés** - Charts.js pour visualisations
4. **Notifications** - Rappels d'hydratation
5. **Intégration API** - Strava, MyFitnessPal
6. **Historique détaillé** - Export PDF/CSV
7. **Partage** - Avec médecin/coach

---

## 🧭 Intégration avec le Système Existant

### Authentification
Adapter avec votre système d'authentification:
```php
// Remplacer:
$userId = $_SESSION['user_id'] ?? 1;

// Par votre logique d'authentification
$userId = Auth::getCurrentUserId();
```

### Navigation
Ajouter liens dans les menus:
```php
// Front-office menu
<a href="./consulter-suivi.php">Mon Suivi Santé</a>
<a href="./ajouter-suivi.php">Ajouter une Activité</a>

// Back-office menu
<a href="./dashboard-suivi.php">Santé (Dashboard)</a>
<a href="./manage-suivi.php">Gérer Suivis</a>
```

---

## 📋 Checklist d'Installation

- [ ] Exécuter le SQL de création de la table
- [ ] Vérifier les permissions de la base de données
- [ ] Tester les formulaires front-office
- [ ] Tester le CRUD back-office
- [ ] Vérifier la validation client/serveur
- [ ] Adapter l'authentification
- [ ] Ajouter les liens de navigation
- [ ] Tester l'intégration avec Journal
- [ ] Vérifier les statistiques

---

## 🐛 Dépannage

### Erreur: Table inexistante
- Solution: Exécuter le SQL dans `database.sql`

### Erreur: UNIQUE constraint
- Cause: Tentative d'ajouter deux fois la même activité pour un jour
- Solution: Modifier ou supprimer avant d'ajouter

### Validation ne fonctionne pas
- Vérifier: ID du formulaire est `suiviForm`
- Vérifier: Noms des inputs correspondent au controller

### Statistiques vides
- Vérifier: Les données sont insérées avec PDO::FETCH_ASSOC
- Vérifier: Les index de tableau sont corrects

---

## 📧 Support & Maintenance

- Code testé avec PHP 7.4+ et MySQL 5.7+
- Compatible Bootstrap 5 (CSS)
- Utilise Font Awesome 5+ pour les icônes
- Responsive design (mobile-friendly)

---

**Version**: 1.0.0  
**Dernière mise à jour**: Avril 2024  
**Statut**: Production Ready ✅
