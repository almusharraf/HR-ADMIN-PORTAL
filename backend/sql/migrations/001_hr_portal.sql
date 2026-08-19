-- Run this against an existing petrogistix careers database that already
-- has `jobs` and `applications` tables (from the careers-site repo), to add
-- what the HR admin portal needs on top: admin accounts, review workflow,
-- and AI scoring columns.

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    name          VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','recruiter') NOT NULL DEFAULT 'recruiter',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS ai_score TINYINT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS ai_rationale TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS scored_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS review_status ENUM('new','shortlisted','rejected','hired') NOT NULL DEFAULT 'new';

ALTER TABLE jobs
    ADD COLUMN IF NOT EXISTS department VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS employment_type VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS requirements MEDIUMTEXT DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_applications_score ON applications (job_id, ai_score);
