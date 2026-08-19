-- HR Admin Portal schema.
-- This portal reads/writes the SAME database as the petrogistix.com careers
-- site repo (jobs + applications). If that DB already exists, skip the
-- `jobs`/`applications` CREATE TABLE statements below and only run the
-- admin_users + application_scores ones, then apply migrations as needed.

CREATE TABLE IF NOT EXISTS jobs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    job_uuid        CHAR(36) NOT NULL UNIQUE,
    title           VARCHAR(255) NOT NULL,
    location        VARCHAR(255) NOT NULL,
    department      VARCHAR(255) DEFAULT NULL,
    employment_type VARCHAR(100) DEFAULT NULL,
    description     MEDIUMTEXT NOT NULL,
    requirements    MEDIUMTEXT DEFAULT NULL,
    status          ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    job_id                  INT NOT NULL,
    full_name               VARCHAR(255) NOT NULL,
    email                    VARCHAR(255) NOT NULL,
    phone                    VARCHAR(50) DEFAULT NULL,
    cv_path                  VARCHAR(500) DEFAULT NULL,
    age                      INT DEFAULT NULL,
    gender                   VARCHAR(30) DEFAULT NULL,
    nationality              VARCHAR(100) DEFAULT NULL,
    current_location         VARCHAR(255) DEFAULT NULL,
    city                     VARCHAR(255) DEFAULT NULL,
    willing_to_relocate      TINYINT(1) DEFAULT NULL,
    education_level          VARCHAR(100) DEFAULT NULL,
    institution              VARCHAR(255) DEFAULT NULL,
    specialization           VARCHAR(255) DEFAULT NULL,
    status                   VARCHAR(50) DEFAULT NULL, -- Employed / Unemployed / Fresh Graduate / Coop Student
    linkedin_url             VARCHAR(500) DEFAULT NULL,
    other_link               VARCHAR(500) DEFAULT NULL,
    experience_json          JSON DEFAULT NULL,
    education_json           JSON DEFAULT NULL,
    job_interest             VARCHAR(255) DEFAULT NULL,
    previous_employer        VARCHAR(255) DEFAULT NULL,
    interview_transcript_consent TINYINT(1) DEFAULT NULL,
    legal_consent            TINYINT(1) NOT NULL DEFAULT 0,
    ai_score                 TINYINT UNSIGNED DEFAULT NULL,
    ai_rationale             TEXT DEFAULT NULL,
    scored_at                DATETIME DEFAULT NULL,
    review_status            ENUM('new','shortlisted','rejected','hired') NOT NULL DEFAULT 'new',
    created_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX idx_applications_job (job_id),
    INDEX idx_applications_score (job_id, ai_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    name          VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','recruiter') NOT NULL DEFAULT 'recruiter',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed sample job + a default admin (password: ChangeMe123!) so the portal
-- is usable immediately. CHANGE THIS PASSWORD before deploying anywhere real.
INSERT INTO jobs (job_uuid, title, location, department, employment_type, description, requirements, status)
VALUES ('cef6d85b-23f1-465c-b43e-47344e003489', 'Field Service Engineer', 'Dammam, Saudi Arabia', 'Operations', 'Full-time',
        'Petrogistix is looking for a Field Service Engineer to support our oilfield equipment clients across the Eastern Province.',
        'Bachelor''s degree in Mechanical or Petroleum Engineering. 2+ years field experience. Willingness to travel to client sites.',
        'open')
ON DUPLICATE KEY UPDATE title = title;

-- No admin user is seeded here (a hard-coded bcrypt hash in source control is
-- a bad habit). Create the first admin with:
--   php backend/scripts/create-admin.php hr@petrogistix.com "HR Admin" admin
