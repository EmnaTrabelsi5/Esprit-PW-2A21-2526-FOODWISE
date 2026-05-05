-- ================================================================
-- DONNÉES DE TEST - SUIVI SANTE
-- Exécutez ce fichier pour ajouter des données de test
-- ================================================================

-- Nettoyer avant d'insérer (optionnel)
-- DELETE FROM suivi_sante;

-- ================================================================
-- TEST DATA - USER 1 (Semaine complète)
-- ================================================================

INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) VALUES
(1, '2024-04-15', 'Course', 30, 280.00, 'moyen', 500, 'Bonne séance'),
(1, '2024-04-15', 'Yoga', 45, 120.00, 'faible', 300, 'Relaxation'),
(1, '2024-04-16', 'Fitness', 50, 350.00, 'élevé', 750, 'Entraînement intensif'),
(1, '2024-04-16', 'Marche', 20, 80.00, 'faible', 200, 'Marche digestive'),
(1, '2024-04-17', 'Natation', 45, 400.00, 'élevé', 600, 'Piscine - 1km'),
(1, '2024-04-17', 'Stretching', 15, 50.00, 'faible', 250, 'Avant dodo'),
(1, '2024-04-18', 'Cyclisme', 60, 500.00, 'élevé', 1000, 'Balade en montagne'),
(1, '2024-04-18', 'Yoga', 30, 90.00, 'faible', 350, 'Soir relaxant'),
(1, '2024-04-19', 'HIIT', 25, 350.00, 'élevé', 800, 'Intensif 25/35 min'),
(1, '2024-04-19', 'Marche', 15, 60.00, 'faible', 200, 'Récupération'),
(1, '2024-04-20', 'Course', 45, 420.00, 'moyen', 600, 'Long lent'),
(1, '2024-04-20', 'Musculation', 40, 300.00, 'moyen', 700, 'Jambes + bras'),
(1, '2024-04-21', 'Cross Training', 50, 450.00, 'élevé', 900, 'Complet'),
(1, '2024-04-21', 'Yoga', 30, 100.00, 'faible', 400, 'Étirements');

-- ================================================================
-- TEST DATA - USER 2 (Moins actif)
-- ================================================================

INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) VALUES
(2, '2024-04-18', 'Marche', 30, 120.00, 'faible', 400, 'Promenade simple'),
(2, '2024-04-19', 'Marche', 25, 100.00, 'faible', 350, 'Du travail'),
(2, '2024-04-20', 'Fitness', 40, 280.00, 'moyen', 600, 'Salle d\'entraînement'),
(2, '2024-04-21', 'Danse', 45, 300.00, 'moyen', 700, 'Cours de Zumba');

-- ================================================================
-- TEST DATA - USER 3 (Très actif)
-- ================================================================

INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) VALUES
(3, '2024-04-15', 'Course', 60, 650.00, 'élevé', 1200, 'Marathon prep'),
(3, '2024-04-15', 'Musculation', 45, 350.00, 'élevé', 800, 'Pectoraux'),
(3, '2024-04-16', 'Natation', 60, 550.00, 'élevé', 1000, 'Entraînement compétition'),
(3, '2024-04-16', 'Cyclisme', 90, 800.00, 'élevé', 1500, 'Longue sortie'),
(3, '2024-04-17', 'HIIT', 30, 420.00, 'élevé', 900, 'Intensif complet'),
(3, '2024-04-17', 'Yoga', 20, 80.00, 'faible', 300, 'Récupération active'),
(3, '2024-04-18', 'Cross Training', 60, 550.00, 'élevé', 1100, 'Full body'),
(3, '2024-04-18', 'Marche', 30, 120.00, 'faible', 400, 'Cool down'),
(3, '2024-04-19', 'Musculation', 50, 380.00, 'élevé', 850, 'Dos et bras'),
(3, '2024-04-19', 'Course', 45, 500.00, 'élevé', 750, 'Tempo moyen'),
(3, '2024-04-20', 'Natation', 50, 480.00, 'élevé', 950, 'Entraînement'),
(3, '2024-04-20', 'Stretching', 20, 60.00, 'faible', 250, 'Étirements'),
(3, '2024-04-21', 'Cyclisme', 120, 1200.00, 'élevé', 2000, 'Longue rando'),
(3, '2024-04-21', 'Yoga', 30, 100.00, 'faible', 400, 'Soir');

-- ================================================================
-- COMBINAISONS DE TEST
-- ================================================================

-- Jour avec peu d'eau
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) 
VALUES (1, '2024-04-14', 'Course', 45, 400.00, 'élevé', 200, 'Oubli bouteille eau');

-- Jour avec beaucoup d'eau
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) 
VALUES (1, '2024-04-13', 'Fitness', 60, 500.00, 'élevé', 2000, 'Hyper hydratation');

-- Activité rare unique
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) 
VALUES (2, '2024-04-15', 'Tennis', 90, 650.00, 'élevé', 1000, 'Match complet');

-- Activité très faible calorie
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) 
VALUES (3, '2024-04-14', 'Méditation marche', 30, 40.00, 'faible', 300, 'Très relaxant');

-- Activité très haute calorie
INSERT INTO suivi_sante (user_id, date, type_activite, duree, calories_brulees, intensite, quantite_eau, note) 
VALUES (3, '2024-04-22', 'Escalade', 120, 1500.00, 'élevé', 1800, 'Paroi de 8h');

-- ================================================================
-- VÉRIFICATION DES DONNÉES
-- ================================================================

-- Compter les suivis par user
-- SELECT user_id, COUNT(*) as total FROM suivi_sante GROUP BY user_id;

-- Voir stats par user et jour
-- SELECT user_id, date, COUNT(*) as activites, SUM(duree) as duree_tot, SUM(calories_brulees) as calories_tot 
-- FROM suivi_sante 
-- GROUP BY user_id, date
-- ORDER BY user_id, date DESC;

-- Stats globales
-- SELECT 
--   COUNT(*) as total_followups,
--   COUNT(DISTINCT user_id) as total_users,
--   SUM(duree) as total_duree,
--   SUM(calories_brulees) as total_calories,
--   SUM(quantite_eau) as total_eau,
--   COUNT(DISTINCT DATE(date)) as jours_suivis
-- FROM suivi_sante;

-- Vérifier les insertions
SELECT * FROM suivi_sante ORDER BY user_id, date DESC;
