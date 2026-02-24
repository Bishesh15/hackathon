-- ============================================================
-- v3 Migration – Tutor Notes + Saved Plans + Exam Analysis
-- ============================================================

USE hackathon_db;

-- ── Tutor Notes (saved from AI Tutor notepad) ────────────────

CREATE TABLE IF NOT EXISTS tutor_notes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Saved Plans (analysis + study plans from quiz/test) ──────

CREATE TABLE IF NOT EXISTS saved_plans (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    type       ENUM('analysis','study_plan') NOT NULL,
    topic      VARCHAR(255) NOT NULL,
    content    JSON NOT NULL,
    source     VARCHAR(50) NOT NULL DEFAULT 'quiz',
    attempt_id INT DEFAULT NULL,
    exam_id    INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Add analysis + study_plan columns to exams table ─────────

ALTER TABLE exams
    ADD COLUMN analysis   JSON DEFAULT NULL AFTER feedback,
    ADD COLUMN study_plan JSON DEFAULT NULL AFTER analysis;
