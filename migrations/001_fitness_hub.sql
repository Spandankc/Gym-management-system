-- Apply once to an existing gymnsb database; retains accounts and records.
ALTER TABLE members
  MODIFY fullname VARCHAR(100) NOT NULL,
  MODIFY address VARCHAR(255) NOT NULL,
  MODIFY pay_date DATE NULL DEFAULT NULL,
  MODIFY plan_link VARCHAR(255) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS trainer_id INT NULL DEFAULT NULL;
DROP TRIGGER IF EXISTS update_existing_member_status;
DROP TRIGGER IF EXISTS update_member_status;
DROP TRIGGER IF EXISTS update_status;
DROP TRIGGER IF EXISTS update_status_before_reminder_update;
CREATE TABLE IF NOT EXISTS class_schedules (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  trainer_id INT NULL,
  starts_at DATETIME NOT NULL,
  duration_minutes INT NOT NULL DEFAULT 60,
  location VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  KEY schedule_date (starts_at),
  KEY schedule_trainer (trainer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO progress (ini_weight, curr_weight, ini_bodytype, curr_bodytype, member_id)
SELECT 0, 0, '', '', members.id FROM members
LEFT JOIN progress ON progress.member_id = members.id WHERE progress.id IS NULL;
