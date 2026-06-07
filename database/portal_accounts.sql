USE raquel_hris;

REPLACE INTO users (
    employee_id,
    username,
    email,
    password_hash,
    full_name,
    role,
    branch_id,
    is_active,
    first_login_completed,
    created_at
)
SELECT
    e.employee_id,
    e.employee_code AS username,
    e.employee_code AS email,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password_hash,
    TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name, e.name_extension)) AS full_name,
    'Employee' AS role,
    e.branch_id,
    1 AS is_active,
    0 AS first_login_completed,
    NOW() AS created_at
FROM employees e
WHERE e.employee_code IS NOT NULL
  AND TRIM(e.employee_code) <> ''
  AND (
      NOT EXISTS (
          SELECT 1
          FROM users u
          WHERE u.employee_id = e.employee_id
            AND u.role = 'Employee'
      )
      OR EXISTS (
          SELECT 1
          FROM users u
          WHERE u.employee_id = e.employee_id
            AND u.role = 'Employee'
            AND u.username = e.employee_code
      )
  );
