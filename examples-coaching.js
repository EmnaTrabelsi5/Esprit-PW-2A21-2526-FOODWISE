/**
 * Exemples d'utilisation des systèmes de coaching et de profilage
 * Version Node.js pour test
 */

// Simuler l'objet window pour les tests
global.window = {};

// Charger les modules (dans un vrai navigateur, ils seraient chargés via <script>)
require('./public/js/nutrition-coach.js');
require('./public/js/user-profiling.js');

// ============================================
// EXEMPLE 1: Auto Nutrition Coach System
// ============================================

console.log("=== AUTO NUTRITION COACH SYSTEM ===");

// Données d'exemple pour le coaching
const userData = {
  calories: 2100,
  proteins: 85,
  carbs: 220,
  fats: 75,
  sugar: 45,
  water: 1800,
  caloriesBurned: 250,
  goal: "fitness",
  history: [
    { calories: 2000, proteins: 80, sugar: 40, water: 1900, caloriesBurned: 200 },
    { calories: 2200, proteins: 90, sugar: 50, water: 1700, caloriesBurned: 300 },
    { calories: 2050, proteins: 85, sugar: 35, water: 2000, caloriesBurned: 250 },
    { calories: 2150, proteins: 88, sugar: 42, water: 1850, caloriesBurned: 280 },
    { calories: 2100, proteins: 82, sugar: 48, water: 1750, caloriesBurned: 220 },
    { calories: 2250, proteins: 95, sugar: 55, water: 1600, caloriesBurned: 350 },
    { calories: 2100, proteins: 85, sugar: 45, water: 1800, caloriesBurned: 250 }
  ]
};

// Générer les messages de coaching
const coachMessages = window.nutritionCoach.generateCoachMessages(userData);

console.log("Messages de coaching générés:");
coachMessages.forEach((msg, index) => {
  console.log(`${index + 1}. [${msg.type.toUpperCase()}] ${msg.message} (Priorité: ${msg.priority})`);
});

// ============================================
// EXEMPLE 2: User Behavioral Profiling System
// ============================================

console.log("\n=== USER BEHAVIORAL PROFILING SYSTEM ===");

// Données d'exemple pour le profilage
const profileData = {
  calories: 2100,
  proteins: 85,
  carbs: 220,
  fats: 75,
  sugar: 45,
  water: 1800,
  meals: 4,
  caloriesBurned: 250,
  frequency: 5,
  history: userData.history // Même historique
};

// Profiler l'utilisateur
const profile = window.userProfiler.profileUser(profileData);

console.log("Profil utilisateur:");
console.log(`Type: ${profile.profile}`);
console.log(`Score: ${profile.score}`);
console.log("Raisons:");
profile.reasons.forEach((reason, index) => {
  console.log(`  ${index + 1}. ${reason}`);
});

// ============================================
// EXEMPLE 3: Intégration dans une application
// ============================================

console.log("\n=== EXEMPLE D'INTÉGRATION ===");

// Fonction d'exemple pour afficher les résultats dans l'UI
function displayResults(userData, profileData) {
  // Obtenir les conseils de coaching
  const messages = window.nutritionCoach.generateCoachMessages(userData);

  // Obtenir le profil utilisateur
  const profile = window.userProfiler.profileUser(profileData);

  // Afficher dans la console (remplacer par du DOM manipulation)
  console.log("=== RÉSULTATS POUR L'UTILISATEUR ===");
  console.log(`Profil: ${profile.profile} (Score: ${profile.score})`);

  console.log("\nConseils du coach:");
  messages.slice(0, 3).forEach(msg => {
    console.log(`• ${msg.message}`);
  });

  console.log("\nRaisons du profil:");
  profile.reasons.forEach(reason => {
    console.log(`• ${reason}`);
  });
}

// Tester l'intégration
displayResults(userData, profileData);