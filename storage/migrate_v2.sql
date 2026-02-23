-- ============================================================
-- Migration v2 – Run this on an EXISTING hackathon_db
-- ============================================================

USE hackathon_db;

-- 1. Conversations table
CREATE TABLE IF NOT EXISTS conversations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    module     VARCHAR(50) NOT NULL DEFAULT 'tutor',
    title      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Messages table
CREATE TABLE IF NOT EXISTS messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role            ENUM('user','assistant') NOT NULL,
    content         LONGTEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Widen activity_history.module from ENUM to VARCHAR
ALTER TABLE activity_history MODIFY COLUMN module VARCHAR(50) NOT NULL;

-- 4. Add conversation_id FK to activity_history
ALTER TABLE activity_history ADD COLUMN conversation_id INT DEFAULT NULL AFTER response;
ALTER TABLE activity_history ADD FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL;

-- 5. Exams table (long-answer + image-upload tests)
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
