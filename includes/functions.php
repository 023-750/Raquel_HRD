<?php

// ============================================

// Helper Functions

// ============================================



/**

 * Sanitize output to prevent XSS

 */

function e($string)
{

    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');

}



/**

 * Get a time-based greeting for the logged-in user

 * Returns HTML string with greeting text and icon

 */

function getGreeting($full_name = '')
{

    // Use Philippine Standard Time (UTC+8) explicitly to avoid server UTC offset issues

    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));

    $hour = (int) $now->format('G'); // 0-23



    if ($hour >= 5 && $hour < 12) {

        $greeting = 'Good morning';

        $icon = 'fas fa-sun';

        $color = '#F6C23E';

    } elseif ($hour >= 12 && $hour < 18) {

        $greeting = 'Good afternoon';

        $icon = 'fas fa-cloud-sun';

        $color = '#F6A623';

    } else {

        $greeting = 'Good evening';

        $icon = 'fas fa-moon';

        $color = '#a78bfa';

    }



    // Extract first name only for a friendly feel

    $first_name = trim(explode(' ', trim($full_name))[0]);

    $display = $first_name ? ", {$first_name}!" : '!';



    return '<span style="opacity:.85;font-size:.92rem;font-weight:500;">'

        . '<i class="' . $icon . ' me-2" style="color:' . $color . ';"></i>'

        . $greeting . e($display)

        . '</span>';

}



/**

 * Get employee profile picture URL with fallback

 */

function getEmployeeAvatar($profile_picture)
{

    $fallback = BASE_URL . '/assets/img/logo/logo.png';

    if (!empty($profile_picture)) {

        // Check standard employee folder

        $path = __DIR__ . '/../assets/img/employees/' . $profile_picture;

        if (file_exists($path)) {

            return BASE_URL . '/assets/img/employees/' . $profile_picture;

        }

        // Check sample images folder (for seed data support)

        $path_sample = __DIR__ . '/../assets/img/sample_images/' . $profile_picture;

        if (file_exists($path_sample)) {

            return BASE_URL . '/assets/img/sample_images/' . $profile_picture;

        }

    }

    return $fallback;

}



/**

 * Get the company-facing employee ID/code for display.

 */

function getEmployeeDisplayId($employee)
{

    if (is_array($employee) && !empty($employee['employee_code'])) {

        return (string) $employee['employee_code'];

    }



    if (is_array($employee) && !empty($employee['employee_id'])) {

        return (string) $employee['employee_id'];

    }



    return 'Not Assigned';

}



/**

 * Create a notification for a user

 */

function createNotification($conn, $user_id, $title, $message, $link = null)
{

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");

    $stmt->bind_param("isss", $user_id, $title, $message, $link);

    $stmt->execute();

    $stmt->close();

}



/**

 * Resolve the preferred linked user account for a specific portal context.

 * For the employee portal, only the explicit Employee account should be used.

 */

function getPreferredLinkedUserId($conn, $employee_id, $context = 'employee_portal')
{

    $employee_id = (int) $employee_id;

    if ($employee_id <= 0) {

        return null;

    }



    if ($context === 'employee_portal') {

        $stmt = $conn->prepare("

            SELECT user_id

            FROM users

            WHERE employee_id = ? AND role = 'Employee' AND is_active = 1

            ORDER BY user_id ASC

            LIMIT 1

        ");

    } else {

        $stmt = $conn->prepare("

            SELECT user_id

            FROM users

            WHERE employee_id = ? AND is_active = 1

            ORDER BY user_id ASC

            LIMIT 1

        ");

    }



    $stmt->bind_param("i", $employee_id);

    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();



    return $result ? (int) $result['user_id'] : null;

}



/**

 * Log an audit event

 */

function logAudit($conn, $user_id, $action_type, $entity_type, $entity_id = null, $details = null)
{

    // Ensure user_id actually exists to avoid Foreign Key constraint errors (e.g. after DB reset)

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");

    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $user_exists = $stmt->get_result()->num_rows > 0;

    $stmt->close();



    if (!$user_exists) {

        $user_id = null; // Record as system/unknown if user doesn't exist

    }



    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ississ", $user_id, $action_type, $entity_type, $entity_id, $details, $ip);

    $stmt->execute();

    $stmt->close();

}



/**

 * Get unread notification count for current user, filtered by portal context

 * @param string $context 'employee' or 'hr'

 */

function getUnreadNotificationCount($conn, $user_id, $context = null)
{

    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";

    if ($context === 'employee') {

        $sql .= " AND (link LIKE '%/employee/%' OR link IS NULL OR link = '')";

    } elseif ($context === 'hr') {

        $sql .= " AND (link NOT LIKE '%/employee/%' OR link IS NULL OR link = '')";

    }



    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $result['count'];

}



/**

 * Get recent notifications for a user, filtered by portal context

 * @param string $context 'employee' or 'hr'

 */

function getRecentNotifications($conn, $user_id, $limit = 5, $context = null)
{

    $sql = "SELECT * FROM notifications WHERE user_id = ?";

    if ($context === 'employee') {

        $sql .= " AND (link LIKE '%/employee/%' OR link IS NULL OR link = '')";

    } elseif ($context === 'hr') {

        $sql .= " AND (link NOT LIKE '%/employee/%' OR link IS NULL OR link = '')";

    }

    $sql .= " ORDER BY created_at DESC LIMIT ?";



    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $user_id, $limit);

    $stmt->execute();

    $result = $stmt->get_result();

    $notifications = [];

    while ($row = $result->fetch_assoc()) {

        $notifications[] = $row;

    }

    $stmt->close();

    return $notifications;

}



/**

 * Format date for display

 */

function formatDate($date, $format = 'M d, Y')
{

    if (empty($date))

        return 'N/A';

    return date($format, strtotime($date));

}



/**

 * Format datetime for display

 */

function formatDateTime($datetime, $format = 'M d, Y h:i A')
{

    if (empty($datetime))

        return 'N/A';

    return date($format, strtotime($datetime));

}



/**

 * Get performance level badge class

 */

function getPerformanceBadgeClass($level)
{

    switch ($level) {

        case 'Outstanding':

            return 'bg-success';

        case 'Exceeds Expectations':

            return 'bg-info';

        case 'Meets Expectations':

            return 'bg-warning text-dark';

        case 'Needs Improvement':

            return 'bg-danger';

        // Legacy support

        case 'Excellent':

            return 'bg-success';

        case 'Above Average':

            return 'bg-info';

        case 'Average':

            return 'bg-warning text-dark';

        default:

            return 'bg-secondary';

    }

}



/**

 * Get status badge class

 */

function getStatusBadgeClass($status)
{

    switch ($status) {

        case 'Draft':

            return 'bg-secondary';

        case 'Pending Self-Rating':

            return 'bg-primary';

        case 'Pending Supervisor':

            return 'bg-warning text-dark';

        case 'Pending HR Consolidation':

            return 'bg-warning text-dark';

        case 'Pending Manager':

            return 'bg-info';

        case 'Supervisor Confirmed':

            return 'bg-info text-dark';

        case 'Approved':

            return 'bg-success';

        case 'Rejected':

            return 'bg-danger';

        case 'Returned':

            return 'bg-purple';

        default:

            return 'bg-secondary';

    }

}



/**

 * Calculate performance level based on score

 */

function getPerformanceLevel($score)
{

    // HRD Form-013.01 rating scale (1.00-4.00)

    if ($score >= 3.60)

        return 'Outstanding';

    if ($score >= 2.60)

        return 'Exceeds Expectations';

    if ($score >= 2.00)

        return 'Meets Expectations';

    return 'Needs Improvement';

}



/**

 *  ================================================================================

 * Calculate evaluation total using: weight × rating × average

 *

 * Formula: total = (kra_subtotal × behavior_average) / 4.0

 *

 *   kra_subtotal    = Σ(criterion_weight/100 × rating)  ← encodes weight × rating

 *   behavior_average= avg of all behavior ratings        ← the "average" factor

 *   ÷ 4.0           = normalises the product to 1–4 scale

 *

 * Examples (with weights summing correctly):

 *   Perfect KRA (4.0) × Perfect behavior (4.0) / 4 = 4.00  → Outstanding

 *   Perfect KRA (4.0) × Avg behavior    (2.0) / 4 = 2.00  → Meets Expectations

 * ================================================================================

 */

function calculateEvalTotal($kra_subtotal, $behavior_average, $kra_weight = 80, $behavior_weight = 20)
{

    // ── ORIGINAL FORMULA (additive 80/20 weighted sum) ── COMMENTED OUT ──────────

    // To revert: uncomment the line below and remove / comment the NEW formula line.

    // return round(($kra_subtotal * $kra_weight / 100) + ($behavior_average * $behavior_weight / 100), 2);

    // ─────────────────────────────────────────────────────────────────────────────



    // NEW FORMULA — weight × rating × average  (÷ 4 keeps result on the 1–4 scale)

    return round(($kra_subtotal * $behavior_average) / 4.0, 2);

}



/**

 * Redirect with a flash message

 */

function redirectWith($url, $type, $message)
{

    $_SESSION['flash_type'] = $type;

    $_SESSION['flash_message'] = $message;

    header("Location: " . $url);

    exit();

}



function getCareerProgressionMovementColumns($conn)
{

    $columns = [];



    try {

        $result = $conn->query("SHOW COLUMNS FROM career_movements");

        while ($result && $row = $result->fetch_assoc()) {

            $columns[$row['Field']] = true;

        }

    } catch (mysqli_sql_exception $e) {

        return [];

    }



    return $columns;

}



function ensureCareerProgressionMovements($conn)
{

    try {

        $conn->query("

            CREATE TABLE IF NOT EXISTS career_movements (

                movement_id INT AUTO_INCREMENT PRIMARY KEY,

                employee_id INT NOT NULL,

                movement_type ENUM('Promotion', 'Transfer', 'Demotion', 'Role Change') NOT NULL,

                previous_position VARCHAR(100) NULL,

                new_position VARCHAR(100) NOT NULL,

                previous_branch_id INT NULL,

                new_branch_id INT NULL,

                effective_date DATE NOT NULL,

                reason TEXT NULL,

                logged_by INT NULL,

                approved_by INT NULL,

                decision_date DATETIME NULL,

                manager_comments TEXT NULL,

                approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',

                is_applied TINYINT(1) DEFAULT 0,

                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT fk_career_progression_employee FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,

                CONSTRAINT fk_career_progression_logger FOREIGN KEY (logged_by) REFERENCES users(user_id) ON DELETE SET NULL,

                CONSTRAINT fk_career_progression_approver FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,

                CONSTRAINT fk_career_progression_prev_branch FOREIGN KEY (previous_branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL,

                CONSTRAINT fk_career_progression_new_branch FOREIGN KEY (new_branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4

        ");



        $columns = getCareerProgressionMovementColumns($conn);

        $optional_columns = [

            'decision_date' => "ALTER TABLE career_movements ADD COLUMN decision_date DATETIME NULL AFTER approved_by",

            'manager_comments' => "ALTER TABLE career_movements ADD COLUMN manager_comments TEXT NULL AFTER decision_date",

            'is_applied' => "ALTER TABLE career_movements ADD COLUMN is_applied TINYINT(1) DEFAULT 0 AFTER approval_status",

            'initiated_by_name' => "ALTER TABLE career_movements ADD COLUMN initiated_by_name VARCHAR(100) NULL AFTER reason",

            'initiated_by_role' => "ALTER TABLE career_movements ADD COLUMN initiated_by_role VARCHAR(50) NULL AFTER initiated_by_name",

            'initiated_via' => "ALTER TABLE career_movements ADD COLUMN initiated_via ENUM('Memo','Email','Verbal','Letter','Meeting','Employee Portal') NULL AFTER initiated_by_role",

            'request_source' => "ALTER TABLE career_movements ADD COLUMN request_source ENUM('HR Portal','Employee Portal') DEFAULT 'HR Portal' AFTER initiated_via",

        ];



        foreach ($optional_columns as $column => $sql) {

            if (!isset($columns[$column])) {

                $conn->query($sql);

            }

        }

    } catch (mysqli_sql_exception $e) {

        return false;

    }



    return true;

}



function applyDueCareerProgressionMovements($conn)
{

    if (!ensureCareerProgressionMovements($conn)) {

        return;

    }



    $today = date('Y-m-d');

    $stmt = $conn->prepare("

        SELECT movement_id, employee_id, new_position, new_branch_id

        FROM career_movements

        WHERE approval_status = 'Approved' AND is_applied = 0 AND effective_date <= ?

    ");

    $stmt->bind_param("s", $today);

    $stmt->execute();

    $result = $stmt->get_result();



    while ($movement = $result->fetch_assoc()) {

        $employee_id = (int) $movement['employee_id'];

        $new_position = $movement['new_position'];



        if (!empty($movement['new_branch_id'])) {

            $new_branch_id = (int) $movement['new_branch_id'];

            $update = $conn->prepare("UPDATE employees SET job_title = ?, branch_id = ? WHERE employee_id = ?");

            $update->bind_param("sii", $new_position, $new_branch_id, $employee_id);

        } else {

            $update = $conn->prepare("UPDATE employees SET job_title = ? WHERE employee_id = ?");

            $update->bind_param("si", $new_position, $employee_id);

        }

        $update->execute();

        $update->close();



        $movement_id = (int) $movement['movement_id'];

        $mark = $conn->prepare("UPDATE career_movements SET is_applied = 1 WHERE movement_id = ?");

        $mark->bind_param("i", $movement_id);

        $mark->execute();

        $mark->close();

    }



    $stmt->close();

}



/**

 * Display flash message if exists

 */

function renderFlashPopup($type, $message)
{

    $flash_icons = [

        'success' => 'fas fa-check-circle',

        'danger' => 'fas fa-exclamation-circle',

        'warning' => 'fas fa-exclamation-triangle',

        'info' => 'fas fa-info-circle',

        'primary' => 'fas fa-bell',

    ];

    $icon = $flash_icons[$type] ?? 'fas fa-bell';

    $flash_titles = [

        'success' => 'Success!',

        'danger' => 'Failed!',

        'warning' => 'Warning!',

        'info' => 'Notice!',

        'primary' => 'Notice!',

    ];

    $title = $flash_titles[$type] ?? 'Notice!';

    $allowed_types = ['success', 'danger', 'warning', 'info', 'primary'];
    $type_class = in_array($type, $allowed_types, true) ? $type : 'info';

    echo '<div class="flash-message-banner flash-message-' . e($type_class) . ' alert alert-dismissible fade show" role="alert">';
    echo '<div class="flash-message-icon"><i class="' . e($icon) . '"></i></div>';
    echo '<div class="flash-message-copy">';
    echo '<span class="flash-message-app">Raquel HRIS</span>';
    echo '<strong class="flash-message-title">' . e($title) . '</strong>';
    echo '<span class="flash-message-text">' . e($message) . '</span>';
    echo '</div>';
    echo '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';

}



function displayFlashMessage()
{

    if (isset($_SESSION['flash_message'])) {

        $type = $_SESSION['flash_type'] ?? 'info';

        $message = $_SESSION['flash_message'];

        unset($_SESSION['flash_type'], $_SESSION['flash_message']);

        renderFlashPopup($type, $message);

    }

}



/**

 * Get a single system setting by key

 */

function getSetting($conn, $key, $default = null)
{

    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");

    $stmt->bind_param("s", $key);

    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $res ? $res['setting_value'] : $default;

}



/**

 * Update a system setting

 */

function updateSetting($conn, $key, $value)
{

    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    $stmt->bind_param("sss", $key, $value, $value);

    $success = $stmt->execute();

    $stmt->close();

    return $success;

}

/**

 * Check if the login attempt should be blocked due to brute force

 */

function checkLoginBruteForce($conn, $identifier, $ip)
{

    $lockout_time = 5; // minutes

    $max_attempts = 5;

    $max_ip_attempts = 10;



    // Check by Identifier (Username/Email)

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE identifier = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");

    $stmt->bind_param("si", $identifier, $lockout_time);

    $stmt->execute();

    $id_count = $stmt->get_result()->fetch_assoc()['count'];

    $stmt->close();



    if ($id_count >= $max_attempts) {

        return true;

    }



    // Check by IP

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");

    $stmt->bind_param("si", $ip, $lockout_time);

    $stmt->execute();

    $ip_count = $stmt->get_result()->fetch_assoc()['count'];

    $stmt->close();



    if ($ip_count >= $max_ip_attempts) {

        return true;

    }



    return false;

}



/**

 * Register a failed login attempt

 */

function registerLoginAttempt($conn, $identifier, $ip)
{

    $stmt = $conn->prepare("INSERT INTO login_attempts (identifier, ip_address) VALUES (?, ?)");

    $stmt->bind_param("ss", $identifier, $ip);

    $stmt->execute();

    $stmt->close();

}



/**

 * Clear login attempts for a successful login

 */

function clearLoginAttempts($conn, $identifier, $ip)
{

    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE identifier = ? OR ip_address = ?");

    $stmt->bind_param("ss", $identifier, $ip);

    $stmt->execute();

    $stmt->close();

}



/**

 * Ensure employees table has reports_to column for supervisor-subordinate relationships

 */

function ensureEmployeesReportsTo($conn)
{

    try {

        $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'reports_to'");

        if ($result->num_rows === 0) {

            $conn->query("ALTER TABLE employees ADD COLUMN reports_to INT NULL AFTER branch_id, ADD CONSTRAINT fk_employees_reports_to FOREIGN KEY (reports_to) REFERENCES employees(employee_id) ON DELETE SET NULL");

        }

    } catch (mysqli_sql_exception $e) {

        return false;

    }

    return true;

}



/**

 * Get all subordinates (employees who report to the given employee_id)

 */

function getEmployeeSubordinates($conn, $supervisor_employee_id)
{

    ensureEmployeesReportsTo($conn);

    $supervisor_employee_id = (int) $supervisor_employee_id;

    $subordinates = [];



    $stmt = $conn->prepare("

        SELECT employee_id, employee_code, first_name, last_name, job_title, branch_id

        FROM employees

        WHERE reports_to = ? AND is_active = 1

        ORDER BY last_name, first_name

    ");

    $stmt->bind_param("i", $supervisor_employee_id);

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $subordinates[] = $row;

    }

    $stmt->close();



    return $subordinates;

}



/**

 * Check if an employee has subordinates (is a supervisor)

 */

function hasEmployeeSubordinates($conn, $employee_id)
{

    ensureEmployeesReportsTo($conn);

    $employee_id = (int) $employee_id;



    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE reports_to = ? AND is_active = 1");

    $stmt->bind_param("i", $employee_id);

    $stmt->execute();

    $count = (int) ($stmt->get_result()->fetch_assoc()['count'] ?? 0);

    $stmt->close();



    return $count > 0;

}



/**

 * Get employee name by ID

 */

function getEmployeeNameById($conn, $employee_id)
{

    $employee_id = (int) $employee_id;

    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM employees WHERE employee_id = ? LIMIT 1");

    $stmt->bind_param("i", $employee_id);

    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $result ? $result['full_name'] : 'Unknown';

}



/**

 * Get columns for evaluations table

 */

function getEvaluationsTableColumns($conn)
{

    $columns = [];

    try {

        $result = $conn->query("SHOW COLUMNS FROM evaluations");

        while ($result && $row = $result->fetch_assoc()) {

            $columns[$row['Field']] = true;

        }

    } catch (mysqli_sql_exception $e) {

        return [];

    }

    return $columns;

}



function getEvaluationCriteriaTableColumns($conn)
{

    $columns = [];

    try {

        $result = $conn->query("SHOW COLUMNS FROM evaluation_criteria");

        while ($result && $row = $result->fetch_assoc()) {

            $columns[$row['Field']] = true;

        }

    } catch (mysqli_sql_exception $e) {

        return [];

    }

    return $columns;

}



function evaluationsStatusSupports($conn, $required_statuses)
{

    try {

        $result = $conn->query("SHOW COLUMNS FROM evaluations LIKE 'status'");
        $status_column = $result ? $result->fetch_assoc() : null;
        $column_type = (string) ($status_column['Type'] ?? '');

        if ($column_type === '') {

            return false;

        }

        foreach ($required_statuses as $status) {

            if (strpos($column_type, "'" . $status . "'") === false) {

                return false;

            }

        }

        return true;

    } catch (mysqli_sql_exception $e) {

        return false;

    }

}



function ensureEvaluationWorkflowSchema($conn)
{

    try {

        $columns = getEvaluationsTableColumns($conn);

        $optional_columns = [

            'assigned_by' => "ALTER TABLE evaluations ADD COLUMN assigned_by INT NULL AFTER submitted_by",

            'assigned_at' => "ALTER TABLE evaluations ADD COLUMN assigned_at DATETIME NULL AFTER assigned_by",

            'supervisor_confirmed_by' => "ALTER TABLE evaluations ADD COLUMN supervisor_confirmed_by INT NULL AFTER approved_by",

            'supervisor_confirmed_date' => "ALTER TABLE evaluations ADD COLUMN supervisor_confirmed_date DATETIME NULL AFTER supervisor_confirmed_by",

            'supervisor_altered_scores' => "ALTER TABLE evaluations ADD COLUMN supervisor_altered_scores TINYINT(1) DEFAULT 0 AFTER supervisor_confirmed_date",

            'sent_to_hr_date' => "ALTER TABLE evaluations ADD COLUMN sent_to_hr_date DATETIME NULL AFTER supervisor_altered_scores",

            'sent_to_hr_by' => "ALTER TABLE evaluations ADD COLUMN sent_to_hr_by INT NULL AFTER sent_to_hr_date",

            'supervisor_rating' => "ALTER TABLE evaluations ADD COLUMN supervisor_rating DECIMAL(5,2) NULL AFTER supervisor_comments",

            'manager_rating' => "ALTER TABLE evaluations ADD COLUMN manager_rating DECIMAL(5,2) NULL AFTER manager_comments",

        ];



        foreach ($optional_columns as $column => $sql) {

            if (!isset($columns[$column])) {

                $conn->query($sql);

            }

        }



        $required_statuses = [
            'Draft',
            'Pending Self-Rating',
            'Pending Supervisor',
            'Pending HR Consolidation',
            'Pending Manager',
            'Supervisor Confirmed',
            'Approved',
            'Rejected',
            'Returned'
        ];

        if (!evaluationsStatusSupports($conn, $required_statuses)) {
            $conn->query("
                ALTER TABLE evaluations
                MODIFY COLUMN status ENUM(
                    'Draft',
                    'Pending Self-Rating',
                    'Pending Supervisor',
                    'Pending HR Consolidation',
                    'Pending Manager',
                    'Supervisor Confirmed',
                    'Approved',
                    'Rejected',
                    'Returned'
                ) DEFAULT 'Draft'
            ");
        }

        $criteria_columns = getEvaluationCriteriaTableColumns($conn);

        if (!isset($criteria_columns['is_custom'])) {
            $conn->query("ALTER TABLE evaluation_criteria ADD COLUMN is_custom TINYINT(1) DEFAULT 0 AFTER sort_order");
        }

        return true;

    } catch (mysqli_sql_exception $e) {

        return false;

    }

}



/**

 * Ensure evaluations table has 360-degree workflow columns

 */

function ensure360DegreeEvaluationColumns($conn)
{

    return ensureEvaluationWorkflowSchema($conn);

}



/**

 * Get employee's supervisor (immediate head) based on reports_to

 */

function getEmployeeSupervisor($conn, $employee_id)
{

    ensureEmployeesReportsTo($conn);

    $employee_id = (int) $employee_id;



    $stmt = $conn->prepare("

        SELECT e.reports_to, s.employee_id as supervisor_employee_id, 

               s.first_name, s.last_name, s.job_title,

               u.user_id, u.full_name, u.email

        FROM employees e

        LEFT JOIN employees s ON e.reports_to = s.employee_id

        LEFT JOIN users u ON s.employee_id = u.employee_id

        WHERE e.employee_id = ? AND s.employee_id IS NOT NULL

        LIMIT 1

    ");

    $stmt->bind_param("i", $employee_id);

    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();



    return $result ?: null;

}



/**

 * Notify supervisor that subordinate submitted self-rating

 */

function notifySupervisorOfSelfRating($conn, $employee_id, $evaluation_id)
{

    $supervisor = getEmployeeSupervisor($conn, $employee_id);

    if (!$supervisor || empty($supervisor['user_id'])) {

        return false;

    }



    $employee_name = getEmployeeNameById($conn, $employee_id);



    createNotification(

        $conn,

        (int) $supervisor['user_id'],

        'Self-Rating Pending Confirmation',

        $employee_name . ' submitted a self-rating awaiting your confirmation.',

        BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id

    );



    return true;

}



/**

 * Check if user is supervisor of specific employee

 */

function isSupervisorOfEmployee($conn, $supervisor_user_id, $employee_id)
{

    $supervisor_user_id = (int) $supervisor_user_id;

    $employee_id = (int) $employee_id;



    // Get the employee_id of the supervisor user

    $stmt = $conn->prepare("SELECT employee_id FROM users WHERE user_id = ? LIMIT 1");

    $stmt->bind_param("i", $supervisor_user_id);

    $stmt->execute();

    $supervisor_employee_id = $stmt->get_result()->fetch_assoc()['employee_id'] ?? 0;

    $stmt->close();



    if (!$supervisor_employee_id) {

        return false;

    }



    // Check if the employee reports to this supervisor

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE employee_id = ? AND reports_to = ?");

    $stmt->bind_param("ii", $employee_id, $supervisor_employee_id);

    $stmt->execute();

    $count = (int) ($stmt->get_result()->fetch_assoc()['count'] ?? 0);

    $stmt->close();



    return $count > 0;

}

?>
