-- ============================================================
-- Hackathon Learning App – MySQL Schema
-- Run this ONCE to set up your database.
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

-- ── Activity History (tutor / notes / quiz) ──────────────────

CREATE TABLE IF NOT EXISTS activity_history (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    module     ENUM('tutor','notes','quiz') NOT NULL,
    topic      VARCHAR(255) NOT NULL,
    prompt     TEXT NOT NULL,
    response   LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Tests (AI-generated question sets) ───────────────────────

CREATE TABLE IF NOT EXISTS tests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    topic           VARCHAR(255) NOT NULL,
    questions       JSON NOT NULL,
    total_questions INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Attempts (submitted tests + analysis + study plan) ───────

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
