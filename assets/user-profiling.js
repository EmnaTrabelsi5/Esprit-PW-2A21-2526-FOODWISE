/**
 * User Behavioral Profiling System
 * Frontend-only JavaScript logic for user classification
 */

(function (global) {
  // Scoring thresholds
  const THRESHOLDS = {
    calories: { healthy: [1800, 2200], junk: [2500, 4000], sportif: [2200, 2800] },
    protein: { healthy: [60, 90], junk: [30, 60], sportif: [90, 150] },
    carbs: { healthy: [200, 280], junk: [300, 500], sportif: [250, 350] },
    fats: { healthy: [50, 80], junk: [80, 150], sportif: [60, 100] },
    sugar: { healthy: [0, 40], junk: [60, 150], sportif: [20, 60] },
    water: { healthy: [1800, 2500], junk: [800, 1500], sportif: [2000, 3000] },
    meals: { healthy: [3, 5], junk: [1, 3], sportif: [4, 6] },
    activity: { healthy: [200, 400], junk: [0, 100], sportif: [400, 800] },
    frequency: { healthy: [3, 5], junk: [0, 2], sportif: [5, 7] }
  };

  /**
   * Main function to profile a user
   * @param {Object} data - User data
   * @returns {Object} Profile result
   */
  function profileUser(data) {
    const scores = calculateScores(data);
    const profile = classifyUser(scores);
    const explanation = generateExplanation(data, scores);

    return {
      profile,
      score: scores[profile + 'Score'],
      reasons: explanation
    };
  }

  /**
   * Calculate scores for each profile type
   * @param {Object} data - User data
   * @returns {Object} Scores object
   */
  function calculateScores(data) {
    const scores = {
      healthyScore: 0,
      junkScore: 0,
      sportifScore: 0
    };

    // Calories scoring
    if (data.calories >= THRESHOLDS.calories.healthy[0] && data.calories <= THRESHOLDS.calories.healthy[1]) {
      scores.healthyScore += 20;
    } else if (data.calories >= THRESHOLDS.calories.junk[0]) {
      scores.junkScore += 25;
    } else if (data.calories >= THRESHOLDS.calories.sportif[0] && data.calories <= THRESHOLDS.calories.sportif[1]) {
      scores.sportifScore += 20;
    }

    // Protein scoring
    if (data.proteins >= THRESHOLDS.protein.healthy[0] && data.proteins <= THRESHOLDS.protein.healthy[1]) {
      scores.healthyScore += 15;
    } else if (data.proteins <= THRESHOLDS.protein.junk[1]) {
      scores.junkScore += 20;
    } else if (data.proteins >= THRESHOLDS.protein.sportif[0]) {
      scores.sportifScore += 25;
    }

    // Carbs scoring
    if (data.carbs >= THRESHOLDS.carbs.healthy[0] && data.carbs <= THRESHOLDS.carbs.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.carbs >= THRESHOLDS.carbs.junk[0]) {
      scores.junkScore += 15;
    } else if (data.carbs >= THRESHOLDS.carbs.sportif[0] && data.carbs <= THRESHOLDS.carbs.sportif[1]) {
      scores.sportifScore += 15;
    }

    // Fats scoring
    if (data.fats >= THRESHOLDS.fats.healthy[0] && data.fats <= THRESHOLDS.fats.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.fats >= THRESHOLDS.fats.junk[0]) {
      scores.junkScore += 15;
    } else if (data.fats >= THRESHOLDS.fats.sportif[0] && data.fats <= THRESHOLDS.fats.sportif[1]) {
      scores.sportifScore += 10;
    }

    // Sugar scoring
    if (data.sugar >= THRESHOLDS.sugar.healthy[0] && data.sugar <= THRESHOLDS.sugar.healthy[1]) {
      scores.healthyScore += 15;
    } else if (data.sugar >= THRESHOLDS.sugar.junk[0]) {
      scores.junkScore += 25;
    } else if (data.sugar >= THRESHOLDS.sugar.sportif[0] && data.sugar <= THRESHOLDS.sugar.sportif[1]) {
      scores.sportifScore += 10;
    }

    // Water scoring
    if (data.water >= THRESHOLDS.water.healthy[0] && data.water <= THRESHOLDS.water.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.water <= THRESHOLDS.water.junk[1]) {
      scores.junkScore += 15;
    } else if (data.water >= THRESHOLDS.water.sportif[0]) {
      scores.sportifScore += 10;
    }

    // Meals scoring
    if (data.meals >= THRESHOLDS.meals.healthy[0] && data.meals <= THRESHOLDS.meals.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.meals <= THRESHOLDS.meals.junk[1]) {
      scores.junkScore += 10;
    } else if (data.meals >= THRESHOLDS.meals.sportif[0]) {
      scores.sportifScore += 5;
    }

    // Activity scoring
    if (data.caloriesBurned >= THRESHOLDS.activity.healthy[0] && data.caloriesBurned <= THRESHOLDS.activity.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.caloriesBurned <= THRESHOLDS.activity.junk[1]) {
      scores.junkScore += 10;
    } else if (data.caloriesBurned >= THRESHOLDS.activity.sportif[0]) {
      scores.sportifScore += 15;
    }

    // Frequency scoring
    if (data.frequency >= THRESHOLDS.frequency.healthy[0] && data.frequency <= THRESHOLDS.frequency.healthy[1]) {
      scores.healthyScore += 10;
    } else if (data.frequency <= THRESHOLDS.frequency.junk[1]) {
      scores.junkScore += 10;
    } else if (data.frequency >= THRESHOLDS.frequency.sportif[0]) {
      scores.sportifScore += 15;
    }

    // History analysis bonus
    if (data.history && data.history.length >= 7) {
      const consistency = analyzeConsistency(data.history);
      scores.healthyScore += consistency.healthyBonus;
      scores.junkScore += consistency.junkBonus;
      scores.sportifScore += consistency.sportifBonus;
    }

    return scores;
  }

  /**
   * Classify user based on scores
   * @param {Object} scores - Calculated scores
   * @returns {string} Profile type
   */
  function classifyUser(scores) {
    const profiles = [
      { name: 'healthy', score: scores.healthyScore },
      { name: 'junk_food', score: scores.junkScore },
      { name: 'sportif', score: scores.sportifScore }
    ];

    profiles.sort((a, b) => b.score - a.score);
    return profiles[0].score > profiles[1].score ? profiles[0].name : 'healthy'; // Default to healthy if tie
  }

  /**
   * Generate explanation for the classification
   * @param {Object} data - User data
   * @param {Object} scores - Calculated scores
   * @returns {Array} Array of reason strings
   */
  function generateExplanation(data, scores) {
    const reasons = [];
    const profile = classifyUser(scores);

    if (profile === 'healthy') {
      if (data.calories >= THRESHOLDS.calories.healthy[0] && data.calories <= THRESHOLDS.calories.healthy[1]) {
        reasons.push('Apport calorique équilibré');
      }
      if (data.proteins >= THRESHOLDS.protein.healthy[0] && data.proteins <= THRESHOLDS.protein.healthy[1]) {
        reasons.push('Protéines bien dosées');
      }
      if (data.sugar <= THRESHOLDS.sugar.healthy[1]) {
        reasons.push('Faible consommation de sucre');
      }
      if (data.water >= THRESHOLDS.water.healthy[0]) {
        reasons.push('Bonne hydratation');
      }
      if (data.meals >= THRESHOLDS.meals.healthy[0] && data.meals <= THRESHOLDS.meals.healthy[1]) {
        reasons.push('Rythme alimentaire régulier');
      }
    } else if (profile === 'junk_food') {
      if (data.calories >= THRESHOLDS.calories.junk[0]) {
        reasons.push('Apport calorique élevé');
      }
      if (data.sugar >= THRESHOLDS.sugar.junk[0]) {
        reasons.push('Consommation de sucre importante');
      }
      if (data.proteins <= THRESHOLDS.protein.junk[1]) {
        reasons.push('Protéines insuffisantes');
      }
      if (data.water <= THRESHOLDS.water.junk[1]) {
        reasons.push('Hydratation faible');
      }
      if (data.meals <= THRESHOLDS.meals.junk[1]) {
        reasons.push('Repas irréguliers');
      }
    } else if (profile === 'sportif') {
      if (data.proteins >= THRESHOLDS.protein.sportif[0]) {
        reasons.push('Apport protéique élevé pour la musculation');
      }
      if (data.caloriesBurned >= THRESHOLDS.activity.sportif[0]) {
        reasons.push('Activité physique intense');
      }
      if (data.frequency >= THRESHOLDS.frequency.sportif[0]) {
        reasons.push('Fréquence d\'activité élevée');
      }
      if (data.calories >= THRESHOLDS.calories.sportif[0]) {
        reasons.push('Calories adaptées à l\'activité sportive');
      }
    }

    return reasons;
  }

  /**
   * Analyze consistency in history data
   * @param {Array} history - 7 days history
   * @returns {Object} Consistency bonuses
   */
  function analyzeConsistency(history) {
    const bonuses = { healthyBonus: 0, junkBonus: 0, sportifBonus: 0 };

    // Check for consistent healthy patterns
    const healthyDays = history.filter(day =>
      day.calories >= 1800 && day.calories <= 2200 &&
      day.proteins >= 60 && day.sugar <= 40 &&
      day.water >= 1800
    ).length;

    if (healthyDays >= 5) {
      bonuses.healthyBonus += 15;
    }

    // Check for consistent junk patterns
    const junkDays = history.filter(day =>
      day.calories >= 2500 && day.sugar >= 60 &&
      day.proteins <= 60 && day.water <= 1500
    ).length;

    if (junkDays >= 4) {
      bonuses.junkBonus += 15;
    }

    // Check for consistent sportif patterns
    const sportifDays = history.filter(day =>
      day.proteins >= 90 && day.caloriesBurned >= 400 &&
      day.frequency >= 5
    ).length;

    if (sportifDays >= 5) {
      bonuses.sportifBonus += 15;
    }

    return bonuses;
  }

  // Expose functions globally
  global.userProfiler = {
    profileUser,
    calculateScores,
    classifyUser,
    generateExplanation
  };

})(window);