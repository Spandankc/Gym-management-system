-- Keep each recorded cash payment and renewal for receipts and earnings.
CREATE TABLE IF NOT EXISTS payments (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  monthly_amount DECIMAL(12,2) NOT NULL,
  months INT NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  paid_on DATE NOT NULL,
  recorded_by INT NULL,
  recorded_role VARCHAR(20) NOT NULL,
  KEY member_payments (member_id, paid_on),
  CONSTRAINT payment_member FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
