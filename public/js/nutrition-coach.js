/**
 * Auto Nutrition Coach System — v2
 * Frontend-only JavaScript logic for personalized nutrition coaching
 * Améliorations v2 :
 *  - Messages plus variés (rotation aléatoire par catégorie)
 *  - Analyse des glucides et lipides en plus des protéines
 *  - Meilleure logique de tendances (seuil assoupli à 3 jours min)
 *  - Nouveaux objectifs : muscle_gain, endurance
 *  - Score de cohérence hebdomadaire
 *  - Messages contextuels heure de la journée
 */

(function (global) {
  // ── Constantes de base ──────────────────────────────────────────────────────
  const CALORIE_GOAL   = 2000;
  const PROTEIN_GOAL   = 75;
  const CARB_GOAL      = 250;
  const FAT_GOAL       = 70;
  const SUGAR_LIMIT    = 50;
  const WATER_GOAL     = 2000;
  const ACTIVITY_GOAL  = 300;

  // ── Multiplicateurs par objectif ────────────────────────────────────────────
  const GOAL_MULTIPLIERS = {
    weight_loss:  { calories: 0.80, sugar: 0.50, protein: 1.20, carbs: 0.80 },
    fitness:      { calories: 1.00, sugar: 0.80, protein: 1.50, carbs: 1.00 },
    muscle_gain:  { calories: 1.15, sugar: 0.70, protein: 1.80, carbs: 1.20 },
    endurance:    { calories: 1.10, sugar: 0.90, protein: 1.20, carbs: 1.30 },
    maintenance:  { calories: 1.00, sugar: 1.00, protein: 1.00, carbs: 1.00 },
  };

  // ── Banques de messages variés ───────────────────────────────────────────────
  const MSG = {
    calories_over: [
      (diff) => `Vous dépassez votre objectif de ${diff} kcal. Réduisez les portions ce soir.`,
      (diff) => `Surplus de ${diff} kcal aujourd'hui. Privilégiez des légumes et protéines maigres demain.`,
      (diff) => `${diff} kcal de trop. Évitez les snacks sucrés en fin de journée.`,
    ],
    calories_under_fitness: [
      () => 'Calories un peu basses pour votre objectif fitness. Ajoutez une collation protéinée.',
      () => 'Manque d\'énergie détecté. Un smoothie banane-protéines peut compléter votre journée.',
      () => 'Apport calorique insuffisant pour soutenir vos entraînements. Pensez à un repas supplémentaire.',
    ],
    protein_low: [
      () => 'Protéines insuffisantes. Ajoutez poulet, œufs, tofu ou légumineuses à votre prochain repas.',
      () => 'Votre apport protéique est faible. Pensez à un yaourt grec ou une poignée de noix.',
      () => 'Renforcez vos protéines : elles sont essentielles pour la récupération musculaire.',
    ],
    protein_high: [
      () => 'Excellent apport protéique ! Vos muscles vous remercient.',
      () => 'Très bon niveau de protéines aujourd\'hui. Continuez sur cette lancée.',
    ],
    sugar_high: [
      (g) => `Trop de sucre (${g}g). Limitez les boissons sucrées et les desserts industriels.`,
      (g) => `Sucre élevé (${g}g). Préférez des fruits entiers aux jus et aux sucreries.`,
      (g) => `${g}g de sucre, c\'est au-dessus de la limite. Réduisez les produits ultra-transformés.`,
    ],
    water_low: [
      (ml) => `Hydratation insuffisante. Buvez encore ${ml} ml pour atteindre votre objectif.`,
      (ml) => `Il vous manque ${ml} ml d\'eau. Posez un verre d\'eau sur votre bureau maintenant.`,
      (ml) => `Pensez à vous hydrater : ${ml} ml restants pour la journée.`,
    ],
    activity_low: [
      () => 'Aucune activité détectée. Même 20 minutes de marche font une vraie différence.',
      () => 'Journée sédentaire. Essayez une courte session de stretching ou de vélo.',
      () => 'Bougez un peu ! Une pause active de 15 minutes améliore l\'énergie et la digestion.',
    ],
    carbs_high: [
      () => 'Glucides élevés aujourd\'hui. Réduisez le pain blanc, les pâtes et les sucreries.',
      () => 'Trop de glucides. Remplacez une portion par des légumes ou des protéines.',
    ],
    fats_high: [
      () => 'Lipides au-dessus de l\'objectif. Limitez les fritures et les sauces grasses.',
      () => 'Trop de graisses aujourd\'hui. Préférez les graisses saines : avocat, huile d\'olive, noix.',
    ],
    motivation_good_day: [
      () => 'Journée bien équilibrée ! Vous êtes dans la bonne direction.',
      () => 'Excellent bilan aujourd\'hui. Gardez ce rythme demain.',
      () => 'Bravo pour cette journée équilibrée. La régularité est la clé du succès.',
    ],
    motivation_goal_fitness: [
      () => 'Alimentation adaptée au fitness. Vos muscles sont bien nourris !',
      () => 'Parfait pour votre objectif forme. Continuez à prioriser les protéines.',
    ],
    motivation_goal_weight_loss: [
      () => 'Bonne gestion calorique pour votre perte de poids. Continuez !',
      () => 'Déficit calorique maîtrisé. Vous progressez vers votre objectif.',
    ],
    trend_improving: [
      () => 'Tendance positive cette semaine. Vos habitudes s\'améliorent !',
      () => 'Progression détectée sur 7 jours. Vous êtes sur la bonne voie.',
    ],
    trend_declining: [
      () => 'Quelques habitudes à corriger cette semaine. Revenez à vos fondamentaux.',
      () => 'Tendance à la baisse détectée. Concentrez-vous sur la régularité.',
    ],
    time_morning: [
      () => 'Bon matin ! Commencez par un petit-déjeuner riche en protéines pour bien démarrer.',
    ],
    time_evening: [
      () => 'En fin de journée, privilégiez un repas léger et digeste.',
    ],
  };

  // ── Utilitaire : choisir un message aléatoire dans une banque ────────────────
  function pick(bank, ...args) {
    const fn = bank[Math.floor(Math.random() * bank.length)];
    return fn(...args);
  }

  // ── Génération des messages de coaching ──────────────────────────────────────
  function generateCoachMessages(data) {
    const analysis = analyzeData(data);
    const trends   = analyzeTrends(data.history || []);
    const messages = [];
    const hour     = new Date().getHours();

    // Calories
    if (analysis.calories.status === 'over') {
      messages.push({ message: pick(MSG.calories_over, analysis.calories.diff), type: 'warning', priority: 5 });
    } else if (analysis.calories.status === 'under' && (data.goal === 'fitness' || data.goal === 'muscle_gain')) {
      messages.push({ message: pick(MSG.calories_under_fitness), type: 'advice', priority: 3 });
    }

    // Protéines
    if (analysis.protein.status === 'low') {
      messages.push({ message: pick(MSG.protein_low), type: 'advice', priority: 4 });
    } else if (analysis.protein.status === 'high' && (data.goal === 'fitness' || data.goal === 'muscle_gain')) {
      messages.push({ message: pick(MSG.protein_high), type: 'motivation', priority: 2 });
    }

    // Sucre
    if (analysis.sugar.status === 'high') {
      messages.push({ message: pick(MSG.sugar_high, analysis.sugar.amount), type: 'warning', priority: 4 });
    }

    // Glucides
    if (analysis.carbs.status === 'high') {
      messages.push({ message: pick(MSG.carbs_high), type: 'warning', priority: 3 });
    }

    // Lipides
    if (analysis.fats.status === 'high') {
      messages.push({ message: pick(MSG.fats_high), type: 'warning', priority: 3 });
    }

    // Eau
    if (analysis.water.status === 'low') {
      const remaining = Math.max(0, WATER_GOAL - (data.water || 0));
      messages.push({ message: pick(MSG.water_low, remaining), type: 'advice', priority: 3 });
    }

    // Activité
    if (analysis.activity.status === 'low') {
      messages.push({ message: pick(MSG.activity_low), type: 'advice', priority: 3 });
    }

    // Messages de motivation selon objectif
    if (data.goal === 'weight_loss' && analysis.calories.status !== 'over') {
      messages.push({ message: pick(MSG.motivation_goal_weight_loss), type: 'motivation', priority: 2 });
    }
    if ((data.goal === 'fitness' || data.goal === 'muscle_gain') && analysis.protein.status === 'good') {
      messages.push({ message: pick(MSG.motivation_goal_fitness), type: 'motivation', priority: 2 });
    }

    // Bonne journée globale
    const warningCount = messages.filter(m => m.type === 'warning').length;
    if (warningCount === 0 && messages.length <= 2) {
      messages.push({ message: pick(MSG.motivation_good_day), type: 'motivation', priority: 1 });
    }

    // Tendances
    if (trends.improving) {
      messages.push({ message: pick(MSG.trend_improving), type: 'motivation', priority: 1 });
    } else if (trends.declining) {
      messages.push({ message: pick(MSG.trend_declining), type: 'warning', priority: 2 });
    }

    // Message contextuel heure
    if (hour >= 6 && hour < 10) {
      messages.push({ message: pick(MSG.time_morning), type: 'advice', priority: 1 });
    } else if (hour >= 19) {
      messages.push({ message: pick(MSG.time_evening), type: 'advice', priority: 1 });
    }

    return messages.sort((a, b) => b.priority - a.priority);
  }

  // ── Résumé en une ligne ──────────────────────────────────────────────────────
  function generateCoachSummary(data) {
    const analysis = analyzeData(data);
    const trend    = analyzeTrends(data.history || []);

    let summary = 'Résumé rapide : gestion globale équilibrée.';
    if (analysis.water.status === 'low') {
      summary = 'Votre priorité du jour : hydratation. Buvez un grand verre toutes les 2 heures.';
    } else if (analysis.calories.status === 'over') {
      summary = 'Trop de calories aujourd\'hui. Réduisez légèrement les portions et évitez les produits sucrés.';
    } else if (analysis.protein.status === 'low') {
      summary = 'Renforcez vos protéines pour soutenir l\'énergie et la récupération.';
    } else if (analysis.sugar.status === 'high') {
      summary = 'Sucre trop élevé. Limitez les boissons sucrées et les desserts industriels.';
    }

    if (trend.declining) {
      summary += ' Votre tendance de la semaine montre un recul — ajustez votre plan.';
    } else if (trend.improving) {
      summary += ' Les tendances sont positives, gardez le rythme !';
    }

    return summary;
  }

  // ── Plan d'action du jour ────────────────────────────────────────────────────
  function generateCoachPlan(data) {
    const analysis = analyzeData(data);
    const trend    = analyzeTrends(data.history || []);
    const goal     = data.goal || 'fitness';
    const plan     = [];

    if (analysis.water.status === 'low') {
      const remaining = Math.max(0, WATER_GOAL - (data.water || 0));
      plan.push({
        title: 'Hydratation prioritaire',
        detail: `Buvez encore ${remaining} ml aujourd'hui. Posez un verre d'eau sur votre bureau.`,
        tag: 'Focus'
      });
    }

    if (analysis.calories.status === 'over') {
      plan.push({
        title: 'Réduire les calories',
        detail: 'Choisissez un repas léger ce soir : soupe, salade ou légumes vapeur.',
        tag: 'Action'
      });
    } else if (analysis.calories.status === 'under' && (goal === 'fitness' || goal === 'muscle_gain')) {
      plan.push({
        title: 'Augmenter l\'énergie',
        detail: 'Ajoutez un smoothie protéiné ou une collation saine (banane + beurre d\'amande).',
        tag: 'Action'
      });
    }

    if (analysis.protein.status === 'low') {
      plan.push({
        title: 'Renforcer les protéines',
        detail: 'Privilégiez œufs, poisson, légumineuses ou yaourt grec dans votre prochain repas.',
        tag: 'Conseil'
      });
    }

    if (analysis.sugar.status === 'high') {
      plan.push({
        title: 'Réduire le sucre',
        detail: 'Évitez les boissons sucrées et les desserts industriels pour le reste de la journée.',
        tag: 'Action'
      });
    }

    if (analysis.activity.status === 'low') {
      plan.push({
        title: 'Bouger un peu plus',
        detail: 'Intégrez une marche de 20 minutes ou une session de renforcement léger.',
        tag: 'Motivation'
      });
    }

    if (trend.declining) {
      plan.push({
        title: 'Retour aux fondamentaux',
        detail: 'Analysez vos 3 derniers jours et revenez à des habitudes plus constantes.',
        tag: 'Urgent'
      });
    } else if (trend.improving) {
      plan.push({
        title: 'Maintenir la dynamique',
        detail: 'Continuez ce bon rythme en gardant une routine stable pour les repas et l\'eau.',
        tag: 'Motivation'
      });
    }

    if (plan.length === 0) {
      plan.push({
        title: 'Journée bien équilibrée',
        detail: 'Aucun ajustement majeur nécessaire. Gardez ce niveau et revoyez vos objectifs demain.',
        tag: 'Maintien'
      });
    }

    return plan;
  }

  // ── Analyse des données du jour ──────────────────────────────────────────────
  function analyzeData(data) {
    const m = GOAL_MULTIPLIERS[data.goal] || GOAL_MULTIPLIERS.maintenance;

    return {
      calories: analyzeCalories(data.calories,      CALORIE_GOAL  * m.calories),
      protein:  analyzeProtein(data.proteins,        PROTEIN_GOAL  * m.protein),
      carbs:    analyzeCarbs(data.carbs,             CARB_GOAL     * m.carbs),
      fats:     analyzeFats(data.fats,               FAT_GOAL),
      sugar:    analyzeSugar(data.sugar,             SUGAR_LIMIT   * m.sugar),
      water:    analyzeWater(data.water,             WATER_GOAL),
      activity: analyzeActivity(data.caloriesBurned, ACTIVITY_GOAL),
    };
  }

  // ── Analyse des tendances 7 jours ────────────────────────────────────────────
  // Fonctionne avec 3 jours minimum (au lieu de 7 strict)
  function analyzeTrends(history) {
    if (!history || history.length < 3) {
      return { improving: false, declining: false };
    }

    const recent = history.slice(-7);
    const caloriesTrend  = calculateTrend(recent.map(d => d.calories      || 0));
    const proteinTrend   = calculateTrend(recent.map(d => d.proteins      || 0));
    const sugarTrend     = calculateTrend(recent.map(d => d.sugar         || 0));
    const waterTrend     = calculateTrend(recent.map(d => d.water         || 0));

    const improving =
      (caloriesTrend === 'stable' || caloriesTrend === 'decreasing') &&
      proteinTrend   === 'increasing' &&
      (sugarTrend    === 'stable' || sugarTrend === 'decreasing') &&
      (waterTrend    === 'stable' || waterTrend === 'increasing');

    const declining =
      caloriesTrend === 'increasing' &&
      (proteinTrend === 'stable' || proteinTrend === 'decreasing') &&
      sugarTrend    === 'increasing' &&
      (waterTrend   === 'stable' || waterTrend === 'decreasing');

    return { improving, declining };
  }

  // ── Score de cohérence hebdomadaire (0-100) ──────────────────────────────────
  function getWeeklyConsistencyScore(history) {
    if (!history || history.length === 0) return 0;
    const days = history.slice(-7);
    let score = 0;
    days.forEach(d => {
      const cal = d.calories || 0;
      if (cal >= CALORIE_GOAL * 0.85 && cal <= CALORIE_GOAL * 1.15) score += 14;
      if ((d.proteins || 0) >= PROTEIN_GOAL * 0.8) score += 7;
      if ((d.water || 0) >= WATER_GOAL * 0.8) score += 7;
    });
    return Math.min(100, Math.round(score));
  }

  // ── Helpers d'analyse ────────────────────────────────────────────────────────
  function analyzeCalories(amount, goal) {
    if (!amount) return { status: 'unknown' };
    const diff = Math.abs(amount - goal);
    if (amount > goal * 1.10) return { status: 'over',  diff: Math.round(diff) };
    if (amount < goal * 0.90) return { status: 'under', diff: Math.round(diff) };
    return { status: 'good' };
  }

  function analyzeProtein(amount, goal) {
    if (!amount) return { status: 'low' };
    if (amount < goal * 0.80) return { status: 'low' };
    if (amount > goal * 1.30) return { status: 'high' };
    return { status: 'good' };
  }

  function analyzeCarbs(amount, goal) {
    if (!amount) return { status: 'good' };
    if (amount > goal * 1.20) return { status: 'high' };
    return { status: 'good' };
  }

  function analyzeFats(amount, goal) {
    if (!amount) return { status: 'good' };
    if (amount > goal * 1.30) return { status: 'high' };
    return { status: 'good' };
  }

  function analyzeSugar(amount, limit) {
    if (!amount) return { status: 'good' };
    if (amount > limit) return { status: 'high', amount: Math.round(amount) };
    return { status: 'good' };
  }

  function analyzeWater(amount, goal) {
    if (!amount) return { status: 'low', amount: 0 };
    if (amount < goal * 0.80) return { status: 'low', amount };
    return { status: 'good' };
  }

  function analyzeActivity(amount, goal) {
    if (!amount || amount < goal * 0.50) return { status: 'low' };
    return { status: 'good' };
  }

  function calculateTrend(values) {
    const valid = values.filter(v => v != null && !isNaN(v));
    if (valid.length < 2) return 'stable';
    const half     = Math.floor(valid.length / 2);
    const first    = valid.slice(0, half);
    const second   = valid.slice(half);
    const avgFirst  = first.reduce((a, b) => a + b, 0)  / first.length;
    const avgSecond = second.reduce((a, b) => a + b, 0) / second.length;
    const diff = avgSecond - avgFirst;
    if (diff >  10) return 'increasing';
    if (diff < -10) return 'decreasing';
    return 'stable';
  }

  // ── Export global ────────────────────────────────────────────────────────────
  global.nutritionCoach = {
    generateCoachMessages,
    generateCoachSummary,
    generateCoachPlan,
    analyzeData,
    analyzeTrends,
    getWeeklyConsistencyScore,
  };

})(window);
