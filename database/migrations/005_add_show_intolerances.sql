-- Migration 005: Add show_intolerances column
ALTER TABLE profils_nutritionnels ADD COLUMN show_intolerances BOOLEAN DEFAULT 1 AFTER show_allergies;
