-- Migration: Add user sanctions (suspension and ban)
-- Add columns to manage user suspensions and bans

ALTER TABLE utilisateurs ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active' AFTER role;
ALTER TABLE utilisateurs ADD COLUMN suspended_until DATETIME DEFAULT NULL AFTER status;
ALTER TABLE utilisateurs ADD COLUMN ban_reason VARCHAR(500) DEFAULT NULL AFTER suspended_until;
ALTER TABLE utilisateurs ADD COLUMN banned_at DATETIME DEFAULT NULL AFTER ban_reason;

-- Add indexes for faster queries
CREATE INDEX idx_user_status ON utilisateurs(status);
CREATE INDEX idx_user_suspended_until ON utilisateurs(suspended_until);
