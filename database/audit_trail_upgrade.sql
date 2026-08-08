-- Additive upgrade for existing HRIS databases. Audit records remain insert-only.
ALTER TABLE audit_logs
    ADD COLUMN module_name VARCHAR(100) NULL AFTER entity_type,
    ADD COLUMN target_employee_id INT NULL AFTER entity_id,
    ADD COLUMN previous_value TEXT NULL AFTER details,
    ADD COLUMN new_value TEXT NULL AFTER previous_value,
    ADD COLUMN branch_id INT NULL AFTER new_value,
    ADD COLUMN department_id INT NULL AFTER branch_id,
    ADD COLUMN user_agent VARCHAR(500) NULL AFTER ip_address,
    ADD COLUMN action_status ENUM('Successful','Failed','Cancelled') NOT NULL DEFAULT 'Successful' AFTER user_agent;
