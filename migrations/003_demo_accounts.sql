-- Requested local demo credentials for both roles: @spandan / 123.
-- Widen the legacy admin column to store a modern password hash.
ALTER TABLE admin MODIFY password VARCHAR(100) NOT NULL;
START TRANSACTION;
INSERT INTO admin (username, password, name)
SELECT '@spandan', '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', 'Spandan'
WHERE NOT EXISTS (SELECT 1 FROM admin WHERE username = '@spandan');
UPDATE admin SET password = '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu' WHERE username = '@spandan';
INSERT INTO staffs (fullname, username, password, gender, email, contact, address, designation)
SELECT 'Spandan', '@spandan', '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', 'Others', 'spandan.demo@example.invalid', '', '', 'Trainer'
WHERE NOT EXISTS (SELECT 1 FROM staffs WHERE username = '@spandan');
UPDATE staffs SET password = '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', designation = 'Trainer' WHERE username = '@spandan';
COMMIT;
