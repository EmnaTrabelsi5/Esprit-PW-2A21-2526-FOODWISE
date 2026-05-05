# Systèmes de Coaching et Profilage Nutritionnel

## Vue d'ensemble

Deux systèmes JavaScript frontend-only pour l'analyse nutritionnelle intelligente :

1. **Auto Nutrition Coach System** - Coaching personnalisé quotidien
2. **User Behavioral Profiling System** - Classification comportementale des utilisateurs

## 1. Auto Nutrition Coach System

### Fonctionnalités

- **Conseils quotidiens** : Messages personnalisés basés sur les données nutritionnelles
- **Personnalisation par objectif** : Adaptation selon weight_loss, fitness, ou maintenance
- **Analyse des tendances** : Détection des améliorations/déclin sur 7 jours
- **Système de priorité** : Messages critiques en premier

### Utilisation

```javascript
// Données utilisateur
const userData = {
  calories: 2100,
  proteins: 85,
  carbs: 220,
  fats: 75,
  sugar: 45,
  water: 1800,
  caloriesBurned: 250,
  goal: "fitness", // "weight_loss", "fitness", "maintenance"
  history: [
    // Tableau des 7 derniers jours avec mêmes propriétés
  ]
};

// Générer les messages de coaching
const messages = window.nutritionCoach.generateCoachMessages(userData);

// Résultat : Array de messages
[
  {
    message: "Augmentez votre apport en protéines...",
    type: "advice", // "advice", "warning", "motivation"
    priority: 4 // 1-5, 5 étant le plus prioritaire
  }
]
```

### Types de messages

- **advice** : Conseils d'amélioration
- **warning** : Alertes sur les dépassements
- **motivation** : Encouragements positifs

## 2. User Behavioral Profiling System

### Fonctionnalités

- **Classification automatique** : 3 profils (healthy, junk_food, sportif)
- **Système de scoring** : Points pour chaque catégorie nutritionnelle
- **Explications détaillées** : Raisons de la classification
- **Analyse historique** : Bonus de cohérence sur 7 jours

### Utilisation

```javascript
// Données utilisateur
const profileData = {
  calories: 2100,
  proteins: 85,
  carbs: 220,
  fats: 75,
  sugar: 45,
  water: 1800,
  meals: 4, // Nombre de repas par jour
  caloriesBurned: 250,
  frequency: 5, // Jours d'activité par semaine
  history: [
    // Historique des 7 jours
  ]
};

// Profiler l'utilisateur
const profile = window.userProfiler.profileUser(profileData);

// Résultat
{
  profile: "healthy", // "healthy", "junk_food", "sportif"
  score: 95, // Score du profil dominant
  reasons: [
    "Apport calorique équilibré",
    "Protéines bien dosées",
    // ...
  ]
}
```

### Profils disponibles

- **healthy** : Nutrition équilibrée, hydratation correcte, activité modérée
- **junk_food** : Calories élevées, sucre important, nutriments insuffisants
- **sportif** : Protéines élevées, activité intense, alimentation adaptée

## Intégration dans l'application

### Inclusion des scripts

```html
<script src="public/js/nutrition-coach.js"></script>
<script src="public/js/user-profiling.js"></script>
```

### Exemple d'intégration

```javascript
// Dans votre composant frontend
function updateUserDashboard(userData) {
  // Obtenir les conseils de coaching
  const coachMessages = window.nutritionCoach.generateCoachMessages(userData);

  // Obtenir le profil utilisateur
  const profile = window.userProfiler.profileUser(userData);

  // Afficher dans l'interface
  displayCoachMessages(coachMessages);
  displayUserProfile(profile);
}
```

## Personnalisation

### Seuils configurables

Les seuils sont définis dans les constantes des fichiers JS :

```javascript
// nutrition-coach.js
const CALORIE_GOAL = 2000;
const PROTEIN_GOAL = 75;
// ...

// user-profiling.js
const THRESHOLDS = {
  calories: { healthy: [1800, 2200], junk: [2500, 4000], sportif: [2200, 2800] },
  // ...
};
```

### Extension

Pour ajouter de nouveaux objectifs ou profils :

1. Modifier les constantes `GOAL_MULTIPLIERS` et `THRESHOLDS`
2. Ajouter la logique dans les fonctions d'analyse
3. Tester avec différents jeux de données

## Tests

Un fichier d'exemple `examples-coaching.js` est fourni pour tester les systèmes :

```bash
node examples-coaching.js
```

## Architecture

- **Frontend-only** : Pas de dépendances backend
- **Modulaire** : Fonctions indépendantes et réutilisables
- **Extensible** : Facilement personnalisable
- **Performant** : Calculs légers côté client