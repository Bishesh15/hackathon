-- ============================================================
-- Hackathon Learning App – MySQL Schema  (v2 – redesign)
-- Run the ALTER / CREATE statements to migrate.
-- ============================================================

CREATE DATABASE IF NOT EXISTS hackathon_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE hackathon_db;

-- ── Users ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT NULL,
    google_id     VARCHAR(255) DEFAULT NULL UNIQUE,
    avatar        VARCHAR(500) DEFAULT NULL,
    auth_provider ENUM('local','google') NOT NULL DEFAULT 'local',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Conversations (ChatGPT-style continuous chat) ────────────

CREATE TABLE IF NOT EXISTS conversations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    module     VARCHAR(50) NOT NULL DEFAULT 'tutor',
    title      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Messages (individual chat messages) ──────────────────────

CREATE TABLE IF NOT EXISTS messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role            ENUM('user','assistant') NOT NULL,
    content         LONGTEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Activity History (tutor / notes / quiz) ──────────────────

CREATE TABLE IF NOT EXISTS activity_history (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    module          VARCHAR(50) NOT NULL,
    topic           VARCHAR(255) NOT NULL,
    prompt          TEXT NOT NULL,
    response        LONGTEXT NOT NULL,
    conversation_id INT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)         REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Tests (AI-generated question sets – now MCQ "Quiz") ──────

CREATE TABLE IF NOT EXISTS tests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    topic           VARCHAR(255) NOT NULL,
    questions       JSON NOT NULL,
    total_questions INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Attempts (submitted quizzes + analysis + study plan) ─────

CREATE TABLE IF NOT EXISTS attempts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    test_id    INT NOT NULL,
    user_id    INT NOT NULL,
    answers    JSON NOT NULL,
    score      INT NOT NULL,
    total      INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    analysis   JSON DEFAULT NULL,
    study_plan JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Exams (long-answer / image-upload tests) ─────────────────

CREATE TABLE IF NOT EXISTS exams (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    topic      VARCHAR(255) NOT NULL,
    questions  JSON NOT NULL,
    answers    JSON DEFAULT NULL,
    feedback   JSON DEFAULT NULL,
    score      DECIMAL(5,2) DEFAULT NULL,
    status     ENUM('pending','submitted','graded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
