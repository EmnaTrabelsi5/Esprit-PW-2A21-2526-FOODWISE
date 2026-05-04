-- Migration 004: Ajouter colonnes de confidentialité au profil nutritionnel
ALTER TABLE profils_nutritionnels ADD COLUMN show_weight BOOLEAN DEFAULT 0;
ALTER TABLE profils_nutritionnels ADD COLUMN show_height BOOLEAN DEFAULT 0;
ALTER TABLE profils_nutritionnels ADD COLUMN show_diet BOOLEAN DEFAULT 1;
ALTER TABLE profils_nutritionnels ADD COLUMN show_allergies BOOLEAN DEFAULT 1;
ALTER TABLE profils_nutritionnels ADD COLUMN show_goal BOOLEAN DEFAULT 1;
