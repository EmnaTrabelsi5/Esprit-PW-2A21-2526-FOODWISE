/*
  Smart Feedback System for frontend use.
  Pure JavaScript logic with no backend or API dependencies.
*/

(function (global) {
  const DEFAULT_CALORIE_GOAL = 2000;
  const DEFAULT_PROTEIN_GOAL = 75;
  const LOW_WATER_THRESHOLD = 1500;
  const LOW_PROTEIN_RATIO = 0.7; // 70% of protein goal

  function calculateScore(data) {
    const nutritionScore = calculateNutritionScore(data);
    const hydrationScore = calculateHydrationScore(data.waterIntakeMl);
    const activityScore = calculateActivityScore(data.caloriesBurned);

    const total = Math.round((nutritionScore * 0.45) + (hydrationScore * 0.25) + (activityScore * 0.30));
    return clampScore(total);
  }

  function calculateNutritionScore(data) {
    const caloriesGoal = data.calorieGoal || DEFAULT_CALORIE_GOAL;
    const proteinGoal = data.proteinGoal || DEFAULT_PROTEIN_GOAL;
    const caloriesScore = calculateCaloriesScore(data.calories, caloriesGoal, data.goal);
    const macrosBalanceScore = calculateMacroBalanceScore(data);
    return Math.round((caloriesScore * 0.6) + (macrosBalanceScore * 0.4));
  }

  function calculateCaloriesScore(calories, calorieGoal, goalType) {
    if (!calories || !calorieGoal) return 50;
    const delta = calories - calorieGoal;
    const normalized = 100 - Math.min(100, Math.abs(delta) / calorieGoal * 100);
    if (goalType === 'weight loss') {
      return calories <= calorieGoal ? clampScore(normalized + 10) : clampScore(normalized - 20);
    }
    return clampScore(normalized);
  }

  function calculateMacroBalanceScore(data) {
    const { proteins, carbs, fats } = data;
    if (proteins == null || carbs == null || fats == null) return 50;

    const total = proteins + carbs + fats;
    if (total <= 0) return 40;

    const proteinRatio = proteins / total;
    const carbRatio = carbs / total;
    const fatRatio = fats / total;

    let score = 70;
    if (proteinRatio < 0.15) score -= 20;
    if (carbRatio > 0.55) score -= 10;
    if (fatRatio > 0.35) score -= 10;
    if (proteinRatio >= 0.2 && proteinRatio <= 0.35 && carbRatio <= 0.55 && fatRatio <= 0.35) score += 10;

    return clampScore(score);
  }

  function calculateHydrationScore(waterIntakeMl) {
    if (waterIntakeMl == null) return 50;
    const ratio = waterIntakeMl / LOW_WATER_THRESHOLD;
    return clampScore(Math.round(Math.min(100, ratio * 100)));
  }

  function calculateActivityScore(caloriesBurned) {
    if (caloriesBurned == null) return 40;
    if (caloriesBurned === 0) return 20;
    if (caloriesBurned < 200) return 45;
    if (caloriesBurned < 400) return 70;
    return 90;
  }

  function analyzeHistory(history) {
    const result = {
      repeatedHabits: [],
      severityFactor: 1,
      summary: {},
    };

    if (!Array.isArray(history) || history.length === 0) return result;

    const streaks = {
      lowWater: 0,
      highCalories: 0,
      lowProtein: 0,
      noActivity: 0,
    };

    history.slice(-7).forEach((day) => {
      if (day.waterIntakeMl != null && day.waterIntakeMl < LOW_WATER_THRESHOLD) streaks.lowWater += 1;
      if (day.calories != null && day.calorieGoal != null && day.calories > day.calorieGoal) streaks.highCalories += 1;
      if (day.proteins != null && day.proteinGoal != null && day.proteins < day.proteinGoal * LOW_PROTEIN_RATIO) streaks.lowProtein += 1;
      if (day.caloriesBurned == null || day.caloriesBurned === 0) streaks.noActivity += 1;
    });

    if (streaks.lowWater >= 3) result.repeatedHabits.push('low water intake');
    if (streaks.highCalories >= 3) result.repeatedHabits.push('high calorie intake');
    if (streaks.lowProtein >= 3) result.repeatedHabits.push('low protein intake');
    if (streaks.noActivity >= 3) result.repeatedHabits.push('inactive days');

    const repeatedCount = result.repeatedHabits.length;
    result.severityFactor = 1 + repeatedCount * 0.25;
    result.summary = streaks;
    return result;
  }

  function generateFeedback(data) {
    const historyAnalysis = analyzeHistory(data.history || []);
    const priorityBase = getPriorityBase(historyAnalysis);
    const feedback = [];
    const score = calculateScore(data);

    const caloriesWarning = checkCalories(data);
    const waterWarning = checkWater(data);
    const proteinWarning = checkProtein(data);
    const activitySuggestion = checkActivity(data);
    const goalMessages = checkGoalPriority(data);

    addMessage(feedback, waterWarning, priorityBase, 'warning');
    addMessage(feedback, caloriesWarning, priorityBase, 'warning');
    addMessage(feedback, proteinWarning, priorityBase, 'suggestion');
    addMessage(feedback, activitySuggestion, priorityBase, 'suggestion');
    goalMessages.forEach((message) => addMessage(feedback, message, priorityBase, 'positive'));

    if (score >= 85) {
      feedback.push({ message: 'Équilibre optimal aujourd’hui. Continuez sur cette lancée.', type: 'positive', priority: 4 });
    } else if (score >= 65) {
      feedback.push({ message: 'Bon progrès — quelques ajustements peuvent encore améliorer votre journée.', type: 'positive', priority: 3 });
    }

    const historyFeedback = generateHistoryFeedback(historyAnalysis);
    historyFeedback.forEach((item) => addMessage(feedback, item.text, priorityBase, item.type, item.priority));

    return prioritizeMessages(feedback);
  }

  function checkCalories(data) {
    const goal = data.calorieGoal || DEFAULT_CALORIE_GOAL;
    if (data.calories == null) return null;
    if (data.calories > goal) {
      const over = data.calories - goal;
      return `Vous dépassez votre objectif calorique de ${over} kcal. Privilégiez des portions plus légères ou des ingrédients moins caloriques.`;
    }
    return `Les calories sont bien maîtrisées aujourd'hui. Bravo !`;
  }

  function checkWater(data) {
    if (data.waterIntakeMl == null) return null;
    if (data.waterIntakeMl < LOW_WATER_THRESHOLD) {
      return `Hydratation insuffisante : essayez de boire au moins ${LOW_WATER_THRESHOLD} ml aujourd'hui.`;
    }
    return `Hydratation satisfaisante. Pensez à maintenir ce rythme.`;
  }

  function checkProtein(data) {
    const proteinGoal = data.proteinGoal || DEFAULT_PROTEIN_GOAL;
    if (data.proteins == null) return null;
    if (data.proteins < proteinGoal * LOW_PROTEIN_RATIO) {
      return `Apport protéique faible. Augmentez les protéines avec œufs, poulet, tofu ou légumineuses.`;
    }
    return `Apport en protéines satisfaisant pour aujourd'hui.`;
  }

  function checkActivity(data) {
    if (data.caloriesBurned == null || data.caloriesBurned === 0) {
      return 'Aucune activité détectée aujourd’hui. Envisagez une marche, un entraînement ou une pause active.';
    }
    return null;
  }

  function checkGoalPriority(data) {
    const messages = [];
    if (!data.goal) return messages;
    if (data.goal === 'weight loss') {
      messages.push('Pour une perte de poids, surveillez les calories et choisissez des aliments riches en nutriments.');
    }
    if (data.goal === 'fitness') {
      messages.push('Pour la forme, privilégiez les protéines et les repas soutenant la force musculaire.');
    }
    return messages;
  }

  function generateHistoryFeedback(historyAnalysis) {
    const items = [];
    if (!historyAnalysis.repeatedHabits.length) return items;

    historyAnalysis.repeatedHabits.forEach((habit) => {
      let text = '';
      let type = 'warning';
      let priority = 3;
      if (habit === 'inactive days') {
        text = 'Plusieurs jours peu actifs ont été détectés. Ajoutez un peu de mouvement chaque jour.';
      } else if (habit === 'low water intake') {
        text = 'Hydratation faible de façon répétée. Réservez des pauses eau régulières.';
      } else if (habit === 'high calorie intake') {
        text = 'Apports caloriques souvent supérieurs à l’objectif. Planifiez des repas plus légers.';
      } else if (habit === 'low protein intake') {
        text = 'Protéines souvent insuffisantes. Renforcez vos repas en protéines maigres.';
      }
      priority = Math.min(5, priority + Math.round(historyAnalysis.severityFactor - 1));
      items.push({ text, type, priority });
    });

    return items;
  }

  function getPriorityBase(historyAnalysis) {
    return historyAnalysis.repeatedHabits.length ? 1 : 2;
  }

  function addMessage(feedback, text, priorityBase, defaultType, explicitPriority) {
    if (!text) return;
    feedback.push({
      message: text,
      type: defaultType,
      priority: explicitPriority != null ? explicitPriority : priorityBase,
    });
  }

  function prioritizeMessages(messages) {
    return messages
      .filter(Boolean)
      .map((item) => ({
        message: item.message,
        type: item.type || 'suggestion',
        priority: clampPriority(item.priority),
      }))
      .sort((a, b) => b.priority - a.priority);
  }

  function clampScore(value) {
    return Math.max(0, Math.min(100, value));
  }

  function clampPriority(value) {
    const pr = Number.isFinite(value) ? value : 1;
    return Math.max(1, Math.min(5, pr));
  }

  // Example usage data. In a real frontend app, replace this with dynamic user input.
  const sampleUserData = {
    goal: 'fitness',
    calories: 1850,
    calorieGoal: 1800,
    proteins: 55,
    proteinGoal: 90,
    carbs: 210,
    fats: 65,
    waterIntakeMl: 1200,
    caloriesBurned: 120,
    mealTimes: ['08:00', '12:30', '19:00'],
    history: [
      { date: '2026-04-27', waterIntakeMl: 1400, calories: 2000, calorieGoal: 1800, proteins: 55, proteinGoal: 90, caloriesBurned: 0 },
      { date: '2026-04-28', waterIntakeMl: 1300, calories: 1900, calorieGoal: 1800, proteins: 50, proteinGoal: 90, caloriesBurned: 0 },
      { date: '2026-04-29', waterIntakeMl: 1550, calories: 1700, calorieGoal: 1800, proteins: 60, proteinGoal: 90, caloriesBurned: 200 },
      { date: '2026-04-30', waterIntakeMl: 1450, calories: 1850, calorieGoal: 1800, proteins: 52, proteinGoal: 90, caloriesBurned: 0 },
      { date: '2026-05-01', waterIntakeMl: 1100, calories: 1950, calorieGoal: 1800, proteins: 48, proteinGoal: 90, caloriesBurned: 0 },
      { date: '2026-05-02', waterIntakeMl: 1600, calories: 1800, calorieGoal: 1800, proteins: 70, proteinGoal: 90, caloriesBurned: 300 },
      { date: '2026-05-03', waterIntakeMl: 1250, calories: 1820, calorieGoal: 1800, proteins: 60, proteinGoal: 90, caloriesBurned: 0 },
    ],
  };

  const exampleOutput = generateFeedback(sampleUserData);
  const exampleScore = calculateScore(sampleUserData);

  global.healthFeedback = {
    calculateScore,
    generateFeedback,
    analyzeHistory,
    sampleUserData,
    exampleOutput,
    exampleScore,
  };
})(window);
