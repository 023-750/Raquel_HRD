-- ============================================================================
-- After 02_test_hrd_portal_accounts.sql
-- Assign Board and Audit governance users used by package route generation.
-- Only NEW packages created after this import get Audit -> Board steps.
-- ============================================================================
USE raquel_hris;

CREATE TABLE IF NOT EXISTS evaluation_governance_approvers (
    governance_approver_id INT AUTO_INCREMENT PRIMARY KEY,
    governance_type ENUM('Board of Directors','Audit Committee') NOT NULL,
    user_id INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_governance_user (governance_type, user_id),
    CONSTRAINT fk_test_governance_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO evaluation_governance_approvers (governance_type, user_id, is_active)
SELECT 'Board of Directors', u.user_id, 1
FROM users u
WHERE u.username = 'GOV-BOD' AND u.is_active = 1
LIMIT 1
ON DUPLICATE KEY UPDATE is_active = 1;

INSERT INTO evaluation_governance_approvers (governance_type, user_id, is_active)
SELECT 'Audit Committee', u.user_id, 1
FROM users u
WHERE u.username = 'GOV-AUD' AND u.is_active = 1
LIMIT 1
ON DUPLICATE KEY UPDATE is_active = 1;
