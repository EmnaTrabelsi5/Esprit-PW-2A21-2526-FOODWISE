/**
 * Auto Nutrition Coach System — v3
 * 3 coach personalities: strict 😡 | friendly 😊 | funny 😂
 */

(function (global) {

  // ── Goals & thresholds ───────────────────────────────────────────────────────
  const CALORIE_GOAL  = 2000;
  const PROTEIN_GOAL  = 75;
  const CARB_GOAL     = 250;
  const FAT_GOAL      = 70;
  const SUGAR_LIMIT   = 50;
  const WATER_GOAL    = 2000;
  const ACTIVITY_GOAL = 300;

  const GOAL_MULTIPLIERS = {
    weight_loss: { calories: 0.80, sugar: 0.50, protein: 1.20, carbs: 0.80 },
    fitness:     { calories: 1.00, sugar: 0.80, protein: 1.50, carbs: 1.00 },
    muscle_gain: { calories: 1.15, sugar: 0.70, protein: 1.80, carbs: 1.20 },
    endurance:   { calories: 1.10, sugar: 0.90, protein: 1.20, carbs: 1.30 },
    maintenance: { calories: 1.00, sugar: 1.00, protein: 1.00, carbs: 1.00 },
  };

  // ── Message banks per personality ───────────────────────────────────────────
  const PERSONALITIES = {

    // ── STRICT 😡 ──────────────────────────────────────────────────────────────
    strict: {
      meta: { emoji: '😡', name: 'Strict Coach', color: '#c0392b' },
      calories_over: [
        (d) => `${d} kcal over limit. Cut portions NOW. No excuses.`,
        (d) => `You exceeded your goal by ${d} kcal. Fix it tomorrow. Period.`,
        (d) => `${d} extra kcal. That's a failure. Discipline starts today.`,
      ],
      calories_under_fitness: [
        () => `You're underfueling. Eat more protein. Your muscles need it.`,
        () => `Low calories for a fitness goal. Add a meal. Stop skipping.`,
      ],
      protein_low: [
        () => `Protein is too low. Chicken, eggs, legumes — pick one. Now.`,
        () => `No protein, no progress. Fix your next meal immediately.`,
        () => `Weak protein intake. You won't build anything like this.`,
      ],
      protein_high: [
        () => `Good protein. That's the minimum expected. Keep it up.`,
      ],
      sugar_high: [
        (g) => `${g}g of sugar. Unacceptable. Drop the sweets.`,
        (g) => `${g}g sugar — way over limit. Cut processed food. Now.`,
      ],
      water_low: [
        (ml) => `${ml}ml of water missing. Drink. No excuses.`,
        (ml) => `Dehydrated by ${ml}ml. Grab a glass right now.`,
      ],
      activity_low: [
        () => `Zero activity. Unacceptable. Move for at least 20 minutes.`,
        () => `Sedentary day. Get up. Walk. Stretch. Do something.`,
      ],
      carbs_high: [
        () => `Too many carbs. Drop the white bread and pasta.`,
      ],
      fats_high: [
        () => `Fat intake too high. Stop the fried food.`,
      ],
      motivation_good_day: [
        () => `Acceptable day. Don't get comfortable — push harder tomorrow.`,
        () => `Decent. But decent isn't great. Raise the bar.`,
      ],
      motivation_goal_fitness: [
        () => `Protein on track. That's what discipline looks like.`,
      ],
      motivation_goal_weight_loss: [
        () => `Calorie deficit maintained. That's the only way it works.`,
      ],
      trend_improving: [
        () => `Trend improving. Good. Now make it consistent.`,
      ],
      trend_declining: [
        () => `Declining trend. Stop making excuses and get back on track.`,
      ],
      time_morning: [
        () => `Morning. High-protein breakfast. No skipping. Go.`,
      ],
      time_evening: [
        () => `Evening. Light meal only. No late-night snacking.`,
      ],
    },

    // ── FRIENDLY 😊 ────────────────────────────────────────────────────────────
    friendly: {
      meta: { emoji: '😊', name: 'Friendly Coach', color: '#27ae60' },
      calories_over: [
        (d) => `You went ${d} kcal over today — no worries! Just aim for lighter portions tomorrow.`,
        (d) => `A little over by ${d} kcal. It happens! Focus on veggies and lean protein next meal.`,
        (d) => `${d} extra kcal today. You've got this — a small adjustment tomorrow will balance it out.`,
      ],
      calories_under_fitness: [
        () => `Your calories are a bit low for your fitness goal. A protein snack would help a lot!`,
        () => `Feeling low on energy? A banana with almond butter could be just what you need.`,
      ],
      protein_low: [
        () => `Your protein is a little low today. Try adding eggs, chicken or Greek yogurt to your next meal.`,
        () => `A handful of nuts or some cottage cheese can easily boost your protein — give it a try!`,
        () => `Proteins help your body recover and stay strong. Let's add a little more to your next meal.`,
      ],
      protein_high: [
        () => `Great protein intake today! Your muscles are going to love you for this.`,
        () => `Excellent! You're fueling your body really well with protein today.`,
      ],
      sugar_high: [
        (g) => `You had ${g}g of sugar today. Try swapping sugary drinks for water or herbal tea.`,
        (g) => `${g}g of sugar is a bit high. Whole fruits are a great sweet alternative!`,
      ],
      water_low: [
        (ml) => `You still need ${ml}ml of water. Try keeping a bottle on your desk — it really helps!`,
        (ml) => `Staying hydrated is so important. Just ${ml}ml more and you'll hit your goal!`,
      ],
      activity_low: [
        () => `No activity logged today — even a 15-minute walk makes a big difference. You can do it!`,
        () => `A short stretch or a gentle walk would do wonders for your energy and mood today.`,
      ],
      carbs_high: [
        () => `Carbs are a bit high today. Swapping one portion for veggies or protein is an easy fix!`,
      ],
      fats_high: [
        () => `Fat intake is slightly high. Avocado and olive oil are great healthy alternatives.`,
      ],
      motivation_good_day: [
        () => `You're doing really well today! Keep up this great balance.`,
        () => `Wonderful day! Your consistency is truly paying off.`,
        () => `You should be proud — today's balance is exactly what healthy living looks like!`,
      ],
      motivation_goal_fitness: [
        () => `Your nutrition is perfectly supporting your fitness goal. Keep it up!`,
        () => `You're fueling your workouts really well. Your body will thank you!`,
      ],
      motivation_goal_weight_loss: [
        () => `Great calorie management today! Every step counts on your journey.`,
        () => `You're staying on track with your weight loss goal. That takes real commitment!`,
      ],
      trend_improving: [
        () => `Your habits have been improving all week — that's amazing progress!`,
        () => `You're on a positive streak! Keep building on this momentum.`,
      ],
      trend_declining: [
        () => `This week has been a little tough, but that's okay. Let's refocus together.`,
        () => `Small setbacks are normal. Let's get back to your good habits — one step at a time.`,
      ],
      time_morning: [
        () => `Good morning! Starting with a protein-rich breakfast will set you up for a great day.`,
      ],
      time_evening: [
        () => `Evening time! A light, easy-to-digest meal will help you sleep better tonight.`,
      ],
    },

    // ── FUNNY 😂 ───────────────────────────────────────────────────────────────
    funny: {
      meta: { emoji: '😂', name: 'Funny Coach', color: '#8e44ad' },
      calories_over: [
        (d) => `Whoa! ${d} extra kcal? Your stomach called — it says "I'm full, bro." 🍕`,
        (d) => `${d} kcal over budget. Your fridge is NOT your best friend today. 😅`,
        (d) => `${d} bonus kcal! You're basically a human calorie savings account. Time to spend less.`,
      ],
      calories_under_fitness: [
        () => `Your muscles are literally texting you: "WHERE IS THE FOOD?!" 💪 Eat something!`,
        () => `Low calories for fitness? Even your gym shoes are disappointed. Grab a snack!`,
      ],
      protein_low: [
        () => `Protein is low. Your muscles are filing a complaint. Chicken to the rescue! 🍗`,
        () => `Without protein, your gains are just... vibes. Add some eggs or legumes!`,
        () => `Your muscles called. They're on strike until you eat more protein. 😤`,
      ],
      protein_high: [
        () => `Protein level: LEGENDARY. Your muscles are doing a happy dance right now. 💃`,
        () => `Look at those protein numbers! Arnold Schwarzenegger would be proud. 🏋️`,
      ],
      sugar_high: [
        (g) => `${g}g of sugar?! Your pancreas just sent you a strongly-worded letter. 📬`,
        (g) => `${g}g sugar — you're basically a human candy bar today. Time to dial it back! 🍬`,
      ],
      water_low: [
        (ml) => `${ml}ml of water missing. You're basically a raisin right now. Drink up! 🍇`,
        (ml) => `Still need ${ml}ml of water. Your cells are sending SOS signals. 🆘 Hydrate!`,
      ],
      activity_low: [
        () => `Zero activity today? Your couch is getting too comfortable. Time to break up! 🛋️`,
        () => `Your fitness tracker is crying. Even a 10-minute walk would cheer it up! 😢`,
      ],
      carbs_high: [
        () => `Carbs are high. You've basically become a pasta dish. Swap one for veggies! 🍝`,
      ],
      fats_high: [
        () => `Fat intake is high. Your arteries are politely asking for a break. 🙏`,
      ],
      motivation_good_day: [
        () => `Perfect day! You're basically a nutrition superhero. Cape not included. 🦸`,
        () => `Balanced day! If nutrition were a sport, you'd be winning gold right now. 🥇`,
        () => `Nailed it today! Your future self is already writing you a thank-you note. 📝`,
      ],
      motivation_goal_fitness: [
        () => `Fitness nutrition on point! Your muscles are throwing a party. You're invited. 🎉`,
        () => `Protein game strong! Even your dumbbells are impressed. 🏋️`,
      ],
      motivation_goal_weight_loss: [
        () => `Calorie deficit achieved! Your fat cells are packing their bags. Bye bye! 👋`,
        () => `On track for weight loss! Your scale is already planning a surprise for you. ⚖️`,
      ],
      trend_improving: [
        () => `Weekly trend: IMPROVING! You're basically a nutrition glow-up in progress. ✨`,
        () => `7-day streak of getting better! Your habits are leveling up like a video game. 🎮`,
      ],
      trend_declining: [
        () => `Rough week, huh? Even Batman has bad days. Let's get back on track! 🦇`,
        () => `Trend going down? No worries — every legend has a plot twist. Yours starts now! 📖`,
      ],
      time_morning: [
        () => `Rise and shine! Your metabolism is already awake and waiting for breakfast. ☀️`,
      ],
      time_evening: [
        () => `Evening mode: activated! Light dinner = better sleep = better you tomorrow. 🌙`,
      ],
    },
  };

  // ── Utility: pick random message from bank ───────────────────────────────────
  function pick(bank, ...args) {
    if (!bank || !bank.length) return () => '';
    const fn = bank[Math.floor(Math.random() * bank.length)];
    return fn(...args);
  }

  // ── Get personality bank (fallback to friendly) ──────────────────────────────
  function getBank(personality) {
    return PERSONALITIES[personality] || PERSONALITIES.friendly;
  }

  // ── Generate coaching messages ───────────────────────────────────────────────
  function generateCoachMessages(data, personality) {
    personality = personality || 'friendly';
    const bank     = getBank(personality);
    const analysis = analyzeData(data);
    const trends   = analyzeTrends(data.history || []);
    const messages = [];
    const hour     = new Date().getHours();

    if (analysis.calories.status === 'over') {
      messages.push({ message: pick(bank.calories_over, analysis.calories.diff), type: 'warning', priority: 5 });
    } else if (analysis.calories.status === 'under' && (data.goal === 'fitness' || data.goal === 'muscle_gain')) {
      messages.push({ message: pick(bank.calories_under_fitness), type: 'advice', priority: 3 });
    }

    if (analysis.protein.status === 'low') {
      messages.push({ message: pick(bank.protein_low), type: 'advice', priority: 4 });
    } else if (analysis.protein.status === 'high' && (data.goal === 'fitness' || data.goal === 'muscle_gain')) {
      messages.push({ message: pick(bank.protein_high), type: 'motivation', priority: 2 });
    }

    if (analysis.sugar.status === 'high') {
      messages.push({ message: pick(bank.sugar_high, analysis.sugar.amount), type: 'warning', priority: 4 });
    }

    if (analysis.carbs.status === 'high') {
      messages.push({ message: pick(bank.carbs_high), type: 'warning', priority: 3 });
    }

    if (analysis.fats.status === 'high') {
      messages.push({ message: pick(bank.fats_high), type: 'warning', priority: 3 });
    }

    if (analysis.water.status === 'low') {
      const remaining = Math.max(0, WATER_GOAL - (data.water || 0));
      messages.push({ message: pick(bank.water_low, remaining), type: 'advice', priority: 3 });
    }

    if (analysis.activity.status === 'low') {
      messages.push({ message: pick(bank.activity_low), type: 'advice', priority: 3 });
    }

    if (data.goal === 'weight_loss' && analysis.calories.status !== 'over') {
      messages.push({ message: pick(bank.motivation_goal_weight_loss), type: 'motivation', priority: 2 });
    }
    if ((data.goal === 'fitness' || data.goal === 'muscle_gain') && analysis.protein.status === 'good') {
      messages.push({ message: pick(bank.motivation_goal_fitness), type: 'motivation', priority: 2 });
    }

    const warningCount = messages.filter(m => m.type === 'warning').length;
    if (warningCount === 0 && messages.length <= 2) {
      messages.push({ message: pick(bank.motivation_good_day), type: 'motivation', priority: 1 });
    }

    if (trends.improving) {
      messages.push({ message: pick(bank.trend_improving), type: 'motivation', priority: 1 });
    } else if (trends.declining) {
      messages.push({ message: pick(bank.trend_declining), type: 'warning', priority: 2 });
    }

    if (hour >= 6 && hour < 10) {
      messages.push({ message: pick(bank.time_morning), type: 'advice', priority: 1 });
    } else if (hour >= 19) {
      messages.push({ message: pick(bank.time_evening), type: 'advice', priority: 1 });
    }

    return messages.sort((a, b) => b.priority - a.priority);
  }

  // ── Summary line ─────────────────────────────────────────────────────────────
  function generateCoachSummary(data, personality) {
    personality = personality || 'friendly';
    const bank     = getBank(personality);
    const analysis = analyzeData(data);
    const trends   = analyzeTrends(data.history || []);

    let key = 'motivation_good_day';
    if (analysis.water.status === 'low')         key = 'water_low';
    else if (analysis.calories.status === 'over') key = 'calories_over';
    else if (analysis.protein.status === 'low')   key = 'protein_low';
    else if (analysis.sugar.status === 'high')    key = 'sugar_high';

    const arg = key === 'water_low'
      ? Math.max(0, WATER_GOAL - (data.water || 0))
      : key === 'calories_over' ? analysis.calories.diff
      : key === 'sugar_high'    ? analysis.sugar.amount
      : undefined;

    let summary = arg !== undefined ? pick(bank[key], arg) : pick(bank[key]);

    if (trends.declining) summary += personality === 'funny'
      ? ' Plot twist incoming — let\'s fix this! 🔄'
      : personality === 'strict'
      ? ' Trend declining. Correct it.'
      : ' Let\'s refocus this week together.';
    else if (trends.improving) summary += personality === 'funny'
      ? ' You\'re on fire this week! 🔥'
      : personality === 'strict'
      ? ' Trend improving. Maintain it.'
      : ' Keep riding this positive wave!';

    return summary;
  }

  // ── Action plan ──────────────────────────────────────────────────────────────
  function generateCoachPlan(data, personality) {
    personality = personality || 'friendly';
    const analysis = analyzeData(data);
    const trends   = analyzeTrends(data.history || []);
    const goal     = data.goal || 'fitness';
    const plan     = [];

    const tone = {
      strict:   { water: 'Drink now.', cal_over: 'Light meal only.', cal_under: 'Eat more. Now.', protein: 'Fix protein intake.', sugar: 'Cut sugar immediately.', activity: 'Move. 20 min minimum.', decline: 'Get back on track.', improve: 'Maintain discipline.' },
      friendly: { water: 'Keep a bottle on your desk — it really helps!', cal_over: 'A light soup or salad tonight would be perfect.', cal_under: 'A banana + almond butter snack would do the trick!', protein: 'Greek yogurt or eggs are easy protein boosts.', sugar: 'Try herbal tea instead of sugary drinks.', activity: 'Even a 15-min walk will boost your energy!', decline: 'Let\'s get back to your good habits, one step at a time.', improve: 'Keep building on this great momentum!' },
      funny:    { water: 'Your cells are thirsty! Drink up before they file a complaint. 💧', cal_over: 'Your stomach needs a timeout. Light dinner tonight! 🥗', cal_under: 'Your muscles are on hunger strike. Feed them! 🍗', protein: 'Your gains are waiting. Protein them up! 💪', sugar: 'Your pancreas sent a memo: less sugar please. 📬', activity: 'Your couch misses you, but your body doesn\'t. Move! 🛋️', decline: 'Even Batman has bad weeks. Yours ends now! 🦇', improve: 'You\'re leveling up like a video game character! 🎮' },
    };

    const t = tone[personality] || tone.friendly;

    if (analysis.water.status === 'low') {
      const ml = Math.max(0, WATER_GOAL - (data.water || 0));
      plan.push({ title: personality === 'strict' ? 'Hydrate.' : personality === 'funny' ? 'Hydration Emergency! 🚨' : 'Hydration Priority', detail: `${ml}ml remaining. ${t.water}`, tag: 'Focus' });
    }

    if (analysis.calories.status === 'over') {
      plan.push({ title: personality === 'strict' ? 'Reduce calories.' : personality === 'funny' ? 'Calorie Overload! 🍕' : 'Reduce Calories', detail: t.cal_over, tag: 'Action' });
    } else if (analysis.calories.status === 'under' && (goal === 'fitness' || goal === 'muscle_gain')) {
      plan.push({ title: personality === 'strict' ? 'Eat more.' : personality === 'funny' ? 'Feed the Machine! ⚙️' : 'Boost Your Energy', detail: t.cal_under, tag: 'Action' });
    }

    if (analysis.protein.status === 'low') {
      plan.push({ title: personality === 'strict' ? 'More protein.' : personality === 'funny' ? 'Protein SOS! 🆘' : 'Boost Protein', detail: t.protein, tag: 'Conseil' });
    }

    if (analysis.sugar.status === 'high') {
      plan.push({ title: personality === 'strict' ? 'Cut sugar.' : personality === 'funny' ? 'Sugar Overload! 🍬' : 'Reduce Sugar', detail: t.sugar, tag: 'Action' });
    }

    if (analysis.activity.status === 'low') {
      plan.push({ title: personality === 'strict' ? 'Move.' : personality === 'funny' ? 'Wake Up Your Body! 🏃' : 'Get Moving', detail: t.activity, tag: 'Motivation' });
    }

    if (trends.declining) {
      plan.push({ title: personality === 'strict' ? 'Fix your habits.' : personality === 'funny' ? 'Plot Twist Time! 📖' : 'Refocus This Week', detail: t.decline, tag: 'Urgent' });
    } else if (trends.improving) {
      plan.push({ title: personality === 'strict' ? 'Keep the discipline.' : personality === 'funny' ? 'Level Up! 🎮' : 'Keep the Momentum', detail: t.improve, tag: 'Motivation' });
    }

    if (plan.length === 0) {
      plan.push({
        title: personality === 'strict' ? 'Acceptable day.' : personality === 'funny' ? 'You\'re a Nutrition Hero! 🦸' : 'Great Balanced Day!',
        detail: personality === 'strict' ? 'No major issues. Maintain discipline tomorrow.' : personality === 'funny' ? 'No issues today! Your future self is writing you a thank-you note. 📝' : 'Everything looks great! Keep this balance going.',
        tag: 'Maintien'
      });
    }

    return plan;
  }

  // ── Get personality metadata ─────────────────────────────────────────────────
  function getPersonalityMeta(personality) {
    return (PERSONALITIES[personality] || PERSONALITIES.friendly).meta;
  }

  // ── Analysis helpers ─────────────────────────────────────────────────────────
  function analyzeData(data) {
    const m = GOAL_MULTIPLIERS[data.goal] || GOAL_MULTIPLIERS.maintenance;
    return {
      calories: analyzeCalories(data.calories,       CALORIE_GOAL * m.calories),
      protein:  analyzeProtein(data.proteins,         PROTEIN_GOAL * m.protein),
      carbs:    analyzeCarbs(data.carbs,              CARB_GOAL    * m.carbs),
      fats:     analyzeFats(data.fats,                FAT_GOAL),
      sugar:    analyzeSugar(data.sugar,              SUGAR_LIMIT  * m.sugar),
      water:    analyzeWater(data.water,              WATER_GOAL),
      activity: analyzeActivity(data.caloriesBurned,  ACTIVITY_GOAL),
    };
  }

  function analyzeTrends(history) {
    if (!history || history.length < 3) return { improving: false, declining: false };
    const recent        = history.slice(-7);
    const caloriesTrend = calculateTrend(recent.map(d => d.calories  || 0));
    const proteinTrend  = calculateTrend(recent.map(d => d.proteins  || 0));
    const sugarTrend    = calculateTrend(recent.map(d => d.sugar     || 0));
    const waterTrend    = calculateTrend(recent.map(d => d.water     || 0));
    const improving = (caloriesTrend === 'stable' || caloriesTrend === 'decreasing') && proteinTrend === 'increasing' && (sugarTrend === 'stable' || sugarTrend === 'decreasing') && (waterTrend === 'stable' || waterTrend === 'increasing');
    const declining = caloriesTrend === 'increasing' && (proteinTrend === 'stable' || proteinTrend === 'decreasing') && sugarTrend === 'increasing' && (waterTrend === 'stable' || waterTrend === 'decreasing');
    return { improving, declining };
  }

  function getWeeklyConsistencyScore(history) {
    if (!history || !history.length) return 0;
    let score = 0;
    history.slice(-7).forEach(d => {
      const cal = d.calories || 0;
      if (cal >= CALORIE_GOAL * 0.85 && cal <= CALORIE_GOAL * 1.15) score += 14;
      if ((d.proteins || 0) >= PROTEIN_GOAL * 0.8) score += 7;
      if ((d.water    || 0) >= WATER_GOAL   * 0.8) score += 7;
    });
    return Math.min(100, Math.round(score));
  }

  function analyzeCalories(a, g) { if (!a) return { status: 'unknown' }; const d = Math.abs(a - g); if (a > g * 1.10) return { status: 'over', diff: Math.round(d) }; if (a < g * 0.90) return { status: 'under', diff: Math.round(d) }; return { status: 'good' }; }
  function analyzeProtein(a, g)  { if (!a) return { status: 'low' }; if (a < g * 0.80) return { status: 'low' }; if (a > g * 1.30) return { status: 'high' }; return { status: 'good' }; }
  function analyzeCarbs(a, g)    { if (!a) return { status: 'good' }; if (a > g * 1.20) return { status: 'high' }; return { status: 'good' }; }
  function analyzeFats(a, g)     { if (!a) return { status: 'good' }; if (a > g * 1.30) return { status: 'high' }; return { status: 'good' }; }
  function analyzeSugar(a, l)    { if (!a) return { status: 'good' }; if (a > l) return { status: 'high', amount: Math.round(a) }; return { status: 'good' }; }
  function analyzeWater(a, g)    { if (!a) return { status: 'low', amount: 0 }; if (a < g * 0.80) return { status: 'low', amount: a }; return { status: 'good' }; }
  function analyzeActivity(a, g) { if (!a || a < g * 0.50) return { status: 'low' }; return { status: 'good' }; }

  function calculateTrend(values) {
    const v = values.filter(x => x != null && !isNaN(x));
    if (v.length < 2) return 'stable';
    const h = Math.floor(v.length / 2);
    const a1 = v.slice(0, h).reduce((s, x) => s + x, 0) / h;
    const a2 = v.slice(h).reduce((s, x) => s + x, 0) / (v.length - h);
    const d  = a2 - a1;
    if (d >  10) return 'increasing';
    if (d < -10) return 'decreasing';
    return 'stable';
  }

  // ── Export ───────────────────────────────────────────────────────────────────
  global.nutritionCoach = {
    generateCoachMessages,
    generateCoachSummary,
    generateCoachPlan,
    getPersonalityMeta,
    analyzeData,
    analyzeTrends,
    getWeeklyConsistencyScore,
    PERSONALITIES,
  };

})(window);
