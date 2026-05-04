-- Migration pour ajouter les colonnes de réinitialisation de mot de passe
-- Exécutez ce script si vous avez déjà une base de données existante

ALTER TABLE utilisateurs ADD COLUMN photo_profil VARCHAR(500) DEFAULT NULL;
ALTER TABLE utilisateurs ADD COLUMN reset_code VARCHAR(10) DEFAULT NULL;
ALTER TABLE utilisateurs ADD COLUMN reset_code_expires_at DATETIME DEFAULT NULL;
