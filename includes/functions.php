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

        case 'Pending Dept Supervisor':

            return 'bg-warning text-dark';

        case 'Pending Dept Manager':

            return 'bg-info text-dark';

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
 * Return the hex color for a given performance level.
 * Outstanding        → #198754 (Green)
 * Exceeds Expectations → #0DCAF0 (Cyan/Info)
 * Meets Expectations → #FFC107 (Yellow/Warning)
 * Needs Improvement  → #DC3545 (Red/Danger)
 */
function getPerformanceLevelColor(string $level): string
{
    $map = [
        'Outstanding'          => '#198754',
        'Exceeds Expectations' => '#0DCAF0',
        'Meets Expectations'   => '#FFC107',
        'Needs Improvement'    => '#DC3545',
    ];
    return $map[$level] ?? '#6c757d';
}

/**
 * Return a Bootstrap badge class for a performance level.
 */
function getPerformanceLevelBadgeClass(string $level): string
{
    $map = [
        'Outstanding'          => 'bg-success',
        'Exceeds Expectations' => 'bg-info text-dark',
        'Meets Expectations'   => 'bg-warning text-dark',
        'Needs Improvement'    => 'bg-danger',
    ];
    return $map[$level] ?? 'bg-secondary';
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
    return round(($kra_subtotal * $kra_weight / 100) + ($behavior_average * $behavior_weight / 100), 2);
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
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
            $result->free();
        }
    } catch (mysqli_sql_exception $e) {
        return [];
    }
    return $columns;
}



function ensureCareerProgressionMovements($conn)
{
    static $ensured = false;
    if ($ensured) {
        return true;
    }

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
    static $ensured = false;
    if ($ensured) {
        return true;
    }

    try {
        $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'reports_to'");
        if ($result) {
            if ($result->num_rows === 0) {
                $conn->query("ALTER TABLE employees ADD COLUMN reports_to INT NULL AFTER branch_id, ADD CONSTRAINT fk_employees_reports_to FOREIGN KEY (reports_to) REFERENCES employees(employee_id) ON DELETE SET NULL");
            }
            $result->free();
        }
    } catch (mysqli_sql_exception $e) {
        return false;
    }
    $ensured = true;
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
 * Check if an employee has supervisor privileges.
 * Returns true if the employee:
 *   (1) has at least one active subordinate (reports_to rows), OR
 *   (2) holds a supervisor/manager job title or rank category (rank_category_id 3 or 4).
 * This allows Branch Supervisors and Branch Managers to access supervisor features
 * even when reports_to relationships have not been fully set up in the database.
 */
function hasSupervisorPrivileges($conn, $employee_id)
{
    $employee_id = (int) $employee_id;
    if (!$employee_id) {
        return false;
    }

    // Check 1: Has at least one direct subordinate
    if (hasEmployeeSubordinates($conn, $employee_id)) {
        return true;
    }

    // Check 2: Holds a supervisor or manager job title / rank category
    $stmt = $conn->prepare("
        SELECT employee_id
        FROM employees
        WHERE employee_id = ?
          AND is_active = 1
          AND (
              rank_category_id IN (3, 4)
              OR job_title LIKE '%Supervisor%'
              OR job_title LIKE '%Manager%'
          )
        LIMIT 1
    ");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $res !== null;
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
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
            $result->free();
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
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
            $result->free();
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
        if ($result) {
            $result->free();
        }
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
    static $ensured = false;
    if ($ensured) {
        return true;
    }

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

            'dept_supervisor_confirmed_by' => "ALTER TABLE evaluations ADD COLUMN dept_supervisor_confirmed_by INT NULL AFTER supervisor_confirmed_date",

            'dept_supervisor_confirmed_date' => "ALTER TABLE evaluations ADD COLUMN dept_supervisor_confirmed_date DATETIME NULL AFTER dept_supervisor_confirmed_by",

            'dept_manager_endorsed_by' => "ALTER TABLE evaluations ADD COLUMN dept_manager_endorsed_by INT NULL AFTER dept_supervisor_confirmed_date",

            'dept_manager_endorsed_date' => "ALTER TABLE evaluations ADD COLUMN dept_manager_endorsed_date DATETIME NULL AFTER dept_manager_endorsed_by",

            'dept_manager_comments' => "ALTER TABLE evaluations ADD COLUMN dept_manager_comments TEXT NULL AFTER supervisor_comments",

            'supervisor_lock_user_id' => "ALTER TABLE evaluations ADD COLUMN supervisor_lock_user_id INT NULL AFTER dept_manager_endorsed_date",

            'supervisor_lock_expires' => "ALTER TABLE evaluations ADD COLUMN supervisor_lock_expires DATETIME NULL AFTER supervisor_lock_user_id",

            'manager_lock_user_id' => "ALTER TABLE evaluations ADD COLUMN manager_lock_user_id INT NULL AFTER supervisor_lock_expires",

            'manager_lock_expires' => "ALTER TABLE evaluations ADD COLUMN manager_lock_expires DATETIME NULL AFTER manager_lock_user_id",

        ];



        foreach ($optional_columns as $column => $sql) {

            if (!isset($columns[$column])) {

                $conn->query($sql);

            }

        }



        $required_statuses = [
            'Draft',
            'Pending Self-Rating',
            'Pending Dept Supervisor',
            'Pending Dept Manager',
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
                    'Pending Dept Supervisor',
                    'Pending Dept Manager',
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

        $score_columns = [];
        $score_cols_result = $conn->query('SHOW COLUMNS FROM evaluation_scores');
        if ($score_cols_result) {
            while ($score_col = $score_cols_result->fetch_assoc()) {
                $score_columns[$score_col['Field']] = true;
            }
            $score_cols_result->free();
        }

        $score_optional_columns = [
            'supervisor_override_score' => "ALTER TABLE evaluation_scores ADD COLUMN supervisor_override_score DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Score overridden by HR Supervisor' AFTER weighted_score",
            'supervisor_override_by' => "ALTER TABLE evaluation_scores ADD COLUMN supervisor_override_by INT NULL DEFAULT NULL COMMENT 'User ID of HR Supervisor who overrode' AFTER supervisor_override_score",
            'supervisor_override_at' => "ALTER TABLE evaluation_scores ADD COLUMN supervisor_override_at DATETIME NULL DEFAULT NULL COMMENT 'When override was made' AFTER supervisor_override_by",
            'manager_override_score' => "ALTER TABLE evaluation_scores ADD COLUMN manager_override_score DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Score overridden by HR Manager' AFTER supervisor_override_at",
            'manager_override_by' => "ALTER TABLE evaluation_scores ADD COLUMN manager_override_by INT NULL DEFAULT NULL COMMENT 'User ID of HR Manager who overrode' AFTER manager_override_score",
            'manager_override_at' => "ALTER TABLE evaluation_scores ADD COLUMN manager_override_at DATETIME NULL DEFAULT NULL COMMENT 'When manager override was made' AFTER manager_override_by",
        ];

        foreach ($score_optional_columns as $column => $sql) {
            if (!isset($score_columns[$column])) {
                $conn->query($sql);
            }
        }

        $conn->query("
            UPDATE notifications
            SET link = REPLACE(link, '/staff/my-submissions.php', '/staff/evaluation-history.php')
            WHERE link LIKE '%/staff/my-submissions.php%'
        ");

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
    $employee_id = (int)$employee_id;
    if ($employee_id <= 0) {
        return null;
    }

    static $supervisor_cache = [];
    if (array_key_exists($employee_id, $supervisor_cache)) {
        return $supervisor_cache[$employee_id];
    }

    ensureEmployeesReportsTo($conn);

    // Get the employee's direct reports_to, branch_id, and department_id
    $emp_stmt = $conn->prepare("SELECT reports_to, branch_id, department_id FROM employees WHERE employee_id = ? LIMIT 1");
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $emp_info = $emp_stmt->get_result()->fetch_assoc();
    $emp_stmt->close();

    if (!$emp_info) {
        return null;
    }

    $reports_to = $emp_info['reports_to'] ? (int) $emp_info['reports_to'] : 0;
    $branch_id = $emp_info['branch_id'] ? (int) $emp_info['branch_id'] : 0;
    $department_id = $emp_info['department_id'] ? (int) $emp_info['department_id'] : 0;
    $supervisor_employee_id = 0;

    if ($reports_to > 0) {
        // Direct supervisor reports_to exists
        // Check if this supervisor is active
        $check_active_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1");
        $check_active_stmt->bind_param("i", $reports_to);
        $check_active_stmt->execute();
        $is_active_res = $check_active_stmt->get_result()->fetch_assoc();
        $check_active_stmt->close();

        if ($is_active_res) {
            $supervisor_employee_id = $reports_to;
        }
    }

    if ($supervisor_employee_id <= 0 && $branch_id > 0) {
        // Step 2: Query the same branch for any active employee with Supervisor rank (rank_category_id = 4)
        // or job title containing "Supervisor"
        $sup_stmt = $conn->prepare("
            SELECT employee_id 
            FROM employees 
            WHERE branch_id = ? 
              AND is_active = 1 
              AND deleted_at IS NULL
              AND employee_id != ?
              AND (? = 0 OR department_id = ?)
              AND (rank_category_id = 4 OR job_title LIKE '%Supervisor%')
            LIMIT 1
        ");
        $sup_stmt->bind_param("iiii", $branch_id, $employee_id, $department_id, $department_id);
        $sup_stmt->execute();
        $sup_res = $sup_stmt->get_result()->fetch_assoc();
        $sup_stmt->close();

        if ($sup_res) {
            $supervisor_employee_id = (int)$sup_res['employee_id'];
        }
    }

    if ($supervisor_employee_id <= 0 && $branch_id > 0) {
        // Step 3: Query the same branch for any active employee with Manager rank (rank_category_id = 3)
        // or job title containing "Manager"
        $mgr_stmt = $conn->prepare("
            SELECT employee_id 
            FROM employees 
            WHERE branch_id = ? 
              AND is_active = 1 
              AND deleted_at IS NULL
              AND employee_id != ?
              AND (? = 0 OR department_id = ?)
              AND (rank_category_id = 3 OR job_title LIKE '%Manager%')
            LIMIT 1
        ");
        $mgr_stmt->bind_param("iiii", $branch_id, $employee_id, $department_id, $department_id);
        $mgr_stmt->execute();
        $mgr_res = $mgr_stmt->get_result()->fetch_assoc();
        $mgr_stmt->close();

        if ($mgr_res) {
            $supervisor_employee_id = (int)$mgr_res['employee_id'];
        }
    }

    if ($supervisor_employee_id > 0) {
        // Fetch full supervisor info, including user account if exists
        $stmt = $conn->prepare("
            SELECT ? as reports_to, s.employee_id as supervisor_employee_id, 
                   s.first_name, s.last_name, s.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees s
            LEFT JOIN users u ON s.employee_id = u.employee_id
            WHERE s.employee_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $reports_to, $supervisor_employee_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $supervisor_cache[$employee_id] = $result ?: null;
        return $supervisor_cache[$employee_id];
    }

    $supervisor_cache[$employee_id] = null;
    return null;
}



/**

 * Notify supervisor that subordinate submitted self-rating

 */

function notifySupervisorOfSelfRating($conn, $employee_id, $evaluation_id)
{
    $employee_id = (int)$employee_id;
    $evaluation_id = (int)$evaluation_id;

    // Get employee details
    $emp_stmt = $conn->prepare("SELECT reports_to, branch_id, department_id FROM employees WHERE employee_id = ? LIMIT 1");
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $emp_info = $emp_stmt->get_result()->fetch_assoc();
    $emp_stmt->close();

    if (!$emp_info) {
        return false;
    }

    $reports_to = $emp_info['reports_to'] ? (int) $emp_info['reports_to'] : 0;
    $branch_id = $emp_info['branch_id'] ? (int) $emp_info['branch_id'] : 0;
    $department_id = $emp_info['department_id'] ? (int) $emp_info['department_id'] : 0;

    // We want to find ALL supervisor user accounts who are:
    // 1. The direct supervisor (reports_to) OR
    // 2. Active supervisors/managers in the same branch & department as the employee.
    $query = "
        SELECT DISTINCT u.user_id
        FROM users u
        JOIN employees s ON u.employee_id = s.employee_id
        WHERE s.is_active = 1 
          AND s.deleted_at IS NULL
          AND (
            (s.employee_id = ? AND ? > 0)
            OR (
              s.branch_id = ? 
              AND s.department_id = ? 
              AND s.employee_id != ?
              AND (s.rank_category_id IN (3, 4) OR s.job_title LIKE '%Supervisor%' OR s.job_title LIKE '%Manager%')
            )
          )
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $reports_to, $reports_to, $branch_id, $department_id, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notified = false;
    $employee_name = getEmployeeNameById($conn, $employee_id);

    while ($row = $result->fetch_assoc()) {
        $supervisor_user_id = (int)$row['user_id'];
        createNotification(
            $conn,
            $supervisor_user_id,
            'Self-Rating Pending Confirmation',
            $employee_name . ' submitted a self-rating awaiting your confirmation.',
            BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id
        );
        $notified = true;
    }
    $stmt->close();

    return $notified;
}



/**

 * Check if user is supervisor of specific employee

 */

function isSupervisorOfEmployee($conn, $supervisor_user_id, $employee_id)
{
    $supervisor_user_id = (int) $supervisor_user_id;
    $employee_id = (int) $employee_id;

    // Get the supervisor details
    $stmt = $conn->prepare("
        SELECT e.employee_id, e.branch_id, e.department_id, e.rank_category_id, e.job_title
        FROM users u
        JOIN employees e ON u.employee_id = e.employee_id
        WHERE u.user_id = ? AND e.is_active = 1 AND e.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->bind_param("i", $supervisor_user_id);
    $stmt->execute();
    $supervisor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$supervisor) {
        return false;
    }

    $supervisor_employee_id = (int)$supervisor['employee_id'];

    // Get employee details
    $stmt = $conn->prepare("SELECT reports_to, branch_id, department_id, rank_category_id FROM employees WHERE employee_id = ? LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$employee) {
        return false;
    }

    // Special rule for Branch Manager self-rating: reviewed by a Branch Supervisor (rank 4) in the same branch
    if ((int)$employee['rank_category_id'] === 3 && (int)$supervisor['rank_category_id'] === 4 && (int)$supervisor['branch_id'] === (int)$employee['branch_id']) {
        return true;
    }

    $reports_to = $employee['reports_to'] ? (int)$employee['reports_to'] : 0;
    if ($reports_to > 0 && $reports_to === $supervisor_employee_id) {
        return true;
    }

    // Check same branch/department supervisor or manager
    if ((int)$supervisor['branch_id'] === (int)$employee['branch_id'] && 
        (int)$supervisor['department_id'] === (int)$employee['department_id'] &&
        $supervisor_employee_id !== $employee_id) {
        
        $rank_cat = (int)$supervisor['rank_category_id'];
        $job_title = $supervisor['job_title'];
        if ($rank_cat === 3 || $rank_cat === 4 || 
            stripos($job_title, 'Supervisor') !== false || 
            stripos($job_title, 'Manager') !== false) {
            return true;
        }
    }

    // Fallback to the default getEmployeeSupervisor matching logic
    $fallback_sup = getEmployeeSupervisor($conn, $employee_id);
    if ($fallback_sup && (int)$fallback_sup['supervisor_employee_id'] === $supervisor_employee_id) {
        return true;
    }

    return false;
}

/**
 * Recalculate evaluation scores (KRA subtotal, Behavior average, total score, performance level)
 * after a supervisor or manager override.
 */
function recalculateEvaluationScores($conn, $evaluation_id)
{
    $evaluation_id = (int)$evaluation_id;

    // Fetch the evaluation details (template weight splits)
    $eval_q = $conn->query("SELECT ev.*, et.kra_weight, et.behavior_weight 
                            FROM evaluations ev 
                            LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id 
                            WHERE ev.evaluation_id = $evaluation_id");
    
    if (!$eval_q || $eval_q->num_rows === 0) {
        return false;
    }
    
    $eval = $eval_q->fetch_assoc();
    $kra_weight_pct = (float)($eval['kra_weight'] ?? 80);
    $beh_weight_pct = (float)($eval['behavior_weight'] ?? 20);

    // Fetch all scores for this evaluation
    $scores_q = $conn->query("SELECT es.*, ec.section, ec.weight 
                              FROM evaluation_scores es 
                              JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id 
                              WHERE es.evaluation_id = $evaluation_id");

    $kra_subtotal = 0;
    $beh_total = 0;
    $beh_count = 0;

    while ($row = $scores_q->fetch_assoc()) {
        $score_id = (int)$row['score_id'];
        
        // Hierarchy of scores: Manager Override > Supervisor Override > Original Score
        $effective_score = $row['score_value'];
        if ($row['supervisor_override_score'] !== null) {
            $effective_score = (float)$row['supervisor_override_score'];
        }
        if ($row['manager_override_score'] !== null) {
            $effective_score = (float)$row['manager_override_score'];
        }

        // Keep database up-to-date with active weighted score
        if ($row['section'] === 'KRA') {
            $weight = (float)$row['weight'];
            $weighted = round(($weight / 100) * $effective_score, 2);
            $kra_subtotal += $weighted;
            
            $conn->query("UPDATE evaluation_scores SET weighted_score = $weighted WHERE score_id = $score_id");
        } else {
            $beh_total += $effective_score;
            $beh_count++;
            
            $conn->query("UPDATE evaluation_scores SET weighted_score = $effective_score WHERE score_id = $score_id");
        }
    }

    $kra_subtotal = round($kra_subtotal, 2);
    $behavior_average = $beh_count > 0 ? round($beh_total / $beh_count, 2) : 0;

    // Recalculate overall score
    $total_score = calculateEvalTotal($kra_subtotal, $behavior_average, $kra_weight_pct, $beh_weight_pct);
    $performance_level = getPerformanceLevel($total_score);

    // Update main evaluations table
    $stmt = $conn->prepare("UPDATE evaluations SET kra_subtotal = ?, behavior_average = ?, total_score = ?, performance_level = ?, updated_at = NOW() WHERE evaluation_id = ?");
    $stmt->bind_param("dddsi", $kra_subtotal, $behavior_average, $total_score, $performance_level, $evaluation_id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Get employee's department manager (supervisor of supervisor) based on reports_to
 */
function getDeptManagerOfEmployee($conn, $employee_id)
{
    $employee_id = (int)$employee_id;
    if ($employee_id <= 0) {
        return null;
    }

    // Get immediate supervisor (reports_to), branch_id, and rank/job title
    $stmt = $conn->prepare("SELECT reports_to, branch_id, department_id, job_title, rank_category_id FROM employees WHERE employee_id = ? LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $emp_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$emp_info) {
        return null;
    }

    $reports_to = $emp_info['reports_to'] ? (int)$emp_info['reports_to'] : 0;
    $branch_id = $emp_info['branch_id'] ? (int)$emp_info['branch_id'] : 0;
    $department_id = $emp_info['department_id'] ? (int)$emp_info['department_id'] : 0;
    $job_title = $emp_info['job_title'] ?? '';
    $rank_category_id = (int)($emp_info['rank_category_id'] ?? 0);

    if (($rank_category_id === 4 || stripos($job_title, 'Supervisor') !== false) && $reports_to > 0) {
        $stmt = $conn->prepare("
            SELECT m.reports_to, m.employee_id as supervisor_employee_id,
                   m.first_name, m.last_name, m.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees m
            LEFT JOIN users u ON m.employee_id = u.employee_id AND u.is_active = 1
            WHERE m.employee_id = ?
              AND m.is_active = 1
              AND m.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("i", $reports_to);
        $stmt->execute();
        $manager = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($manager && !empty($manager['user_id'])) {
            return $manager;
        }
    }

    // 1. Try reports_to chain: employee -> supervisor -> manager
    if ($reports_to > 0) {
        $stmt = $conn->prepare("
            SELECT m.reports_to, m.employee_id as supervisor_employee_id,
                   m.first_name, m.last_name, m.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees s
            JOIN employees m ON s.reports_to = m.employee_id
            LEFT JOIN users u ON m.employee_id = u.employee_id AND u.is_active = 1
            WHERE s.employee_id = ?
              AND s.is_active = 1
              AND s.deleted_at IS NULL
              AND m.is_active = 1
              AND m.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("i", $reports_to);
        $stmt->execute();
        $manager = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($manager && !empty($manager['user_id'])) {
            return $manager;
        }
    }

    // 2. Fallback to branch-based hierarchy if reports_to chain is not set or doesn't yield a manager
    if ($branch_id > 0) {
        // If the employee is a Manager themselves, they don't have a department manager above them.
        if ($rank_category_id === 3 || stripos($job_title, 'Manager') !== false) {
            return null;
        }

        // Check if there is an active Manager in the same branch
        $mgr_stmt = $conn->prepare("
            SELECT employee_id 
            FROM employees 
            WHERE branch_id = ? 
              AND is_active = 1 
              AND deleted_at IS NULL
              AND employee_id != ?
              AND (? = 0 OR department_id = ?)
              AND (rank_category_id = 3 OR job_title LIKE '%Manager%')
            ORDER BY employee_id ASC
            LIMIT 1
        ");
        $mgr_stmt->bind_param("iiii", $branch_id, $employee_id, $department_id, $department_id);
        $mgr_stmt->execute();
        $mgr_res = $mgr_stmt->get_result()->fetch_assoc();
        $mgr_stmt->close();

        if ($mgr_res) {
            $manager_employee_id = (int)$mgr_res['employee_id'];

            // Check if there is also an active Supervisor in the same branch
            $sup_stmt = $conn->prepare("
                SELECT employee_id 
                FROM employees 
                WHERE branch_id = ? 
                  AND is_active = 1 
                  AND deleted_at IS NULL
                  AND employee_id != ?
                  AND (? = 0 OR department_id = ?)
                  AND (rank_category_id = 4 OR job_title LIKE '%Supervisor%')
                ORDER BY employee_id ASC
                LIMIT 1
            ");
            $sup_stmt->bind_param("iiii", $branch_id, $employee_id, $department_id, $department_id);
            $sup_stmt->execute();
            $sup_res = $sup_stmt->get_result()->fetch_assoc();
            $sup_stmt->close();

            // If we have a Supervisor in the branch:
            // - A Staff member (not Supervisor, not Manager) reports to Supervisor, who reports to Manager. So Manager is Dept Manager.
            // - A Supervisor reports to Manager. So Manager is Dept Manager.
            if ($sup_res || $rank_category_id === 4 || stripos($job_title, 'Supervisor') !== false) {
                $stmt = $conn->prepare("
                    SELECT s.reports_to, s.employee_id as supervisor_employee_id, 
                           s.first_name, s.last_name, s.job_title,
                           u.user_id, u.full_name, u.email
                    FROM employees s
                    LEFT JOIN users u ON s.employee_id = u.employee_id
                    WHERE s.employee_id = ? AND s.is_active = 1 AND s.deleted_at IS NULL
                    LIMIT 1
                ");
                $stmt->bind_param("i", $manager_employee_id);
                $stmt->execute();
                $mgr_info = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $mgr_info;
            }
        }
    }

    return null;
}
/**
 * Get all active department managers of employee (either via reports_to chain or same branch & department fallback)
 */
function getDeptManagersOfEmployee($conn, $employee_id)
{
    $employee_id = (int)$employee_id;
    if ($employee_id <= 0) {
        return [];
    }

    // Get immediate supervisor (reports_to), branch_id, and rank/job title
    $stmt = $conn->prepare("SELECT reports_to, branch_id, department_id, job_title, rank_category_id FROM employees WHERE employee_id = ? LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $emp_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$emp_info) {
        return [];
    }

    $reports_to = $emp_info['reports_to'] ? (int)$emp_info['reports_to'] : 0;
    $branch_id = $emp_info['branch_id'] ? (int)$emp_info['branch_id'] : 0;
    $department_id = $emp_info['department_id'] ? (int)$emp_info['department_id'] : 0;
    $job_title = $emp_info['job_title'] ?? '';
    $rank_category_id = (int)($emp_info['rank_category_id'] ?? 0);

    $managers = [];

    // 1. If employee is a supervisor and reports_to is set, get that manager
    if (($rank_category_id === 4 || stripos($job_title, 'Supervisor') !== false) && $reports_to > 0) {
        $stmt = $conn->prepare("
            SELECT m.reports_to, m.employee_id as supervisor_employee_id,
                   m.first_name, m.last_name, m.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees m
            LEFT JOIN users u ON m.employee_id = u.employee_id AND u.is_active = 1
            WHERE m.employee_id = ?
              AND m.is_active = 1
              AND m.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $reports_to);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['user_id'])) {
                $managers[$row['user_id']] = $row;
            }
        }
        $stmt->close();
    }

    // 2. Try reports_to chain: employee -> supervisor -> manager
    if ($reports_to > 0) {
        $stmt = $conn->prepare("
            SELECT m.reports_to, m.employee_id as supervisor_employee_id,
                   m.first_name, m.last_name, m.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees s
            JOIN employees m ON s.reports_to = m.employee_id
            LEFT JOIN users u ON m.employee_id = u.employee_id AND u.is_active = 1
            WHERE s.employee_id = ?
              AND s.is_active = 1
              AND s.deleted_at IS NULL
              AND m.is_active = 1
              AND m.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $reports_to);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['user_id'])) {
                $managers[$row['user_id']] = $row;
            }
        }
        $stmt->close();
    }

    // 3. Fallback to branch-based hierarchy if reports_to chain is not set or doesn't yield a manager
    if (empty($managers) && $branch_id > 0) {
        // If the employee is a Manager themselves, they don't have a department manager above them.
        if ($rank_category_id === 3 || stripos($job_title, 'Manager') !== false) {
            return [];
        }

        // Get all active managers in the same branch/department
        $mgr_stmt = $conn->prepare("
            SELECT m.employee_id as supervisor_employee_id, m.reports_to,
                   m.first_name, m.last_name, m.job_title,
                   u.user_id, u.full_name, u.email
            FROM employees m
            LEFT JOIN users u ON m.employee_id = u.employee_id AND u.is_active = 1
            WHERE m.branch_id = ? 
              AND m.is_active = 1 
              AND m.deleted_at IS NULL
              AND m.employee_id != ?
              AND (? = 0 OR m.department_id = ?)
              AND (m.rank_category_id = 3 OR m.job_title LIKE '%Manager%')
        ");
        $mgr_stmt->bind_param("iiii", $branch_id, $employee_id, $department_id, $department_id);
        $mgr_stmt->execute();
        $res = $mgr_stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['user_id'])) {
                $managers[$row['user_id']] = $row;
            }
        }
        $mgr_stmt->close();
    }

    return array_values($managers);
}

/**
 * Check if user is department manager of specific employee (supervisor's supervisor)
 */
function isDeptManagerOfEmployee($conn, $manager_user_id, $employee_id)
{
    $manager_user_id = (int)$manager_user_id;
    $employee_id = (int)$employee_id;

    // Get manager's employee_id
    $stmt = $conn->prepare("SELECT employee_id FROM users WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $manager_user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $manager_employee_id = (int)($res['employee_id'] ?? 0);
    $stmt->close();

    if (!$manager_employee_id) {
        return false;
    }

    // 1. Try direct reports_to check first
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM employees e
        JOIN employees s ON e.reports_to = s.employee_id
        WHERE e.employee_id = ? AND s.reports_to = ? AND e.is_active = 1 AND s.is_active = 1
    ");
    $stmt->bind_param("ii", $employee_id, $manager_employee_id);
    $stmt->execute();
    $count = (int)($stmt->get_result()->fetch_assoc()['count'] ?? 0);
    $stmt->close();

    if ($count > 0) {
        return true;
    }

    // 2. Fallback: Resolve employee's department managers using new logic
    $dept_managers = getDeptManagersOfEmployee($conn, $employee_id);
    foreach ($dept_managers as $mgr) {
        if ((int)$mgr['supervisor_employee_id'] === $manager_employee_id) {
            return true;
        }
    }

    return false;
}

/**
 * Notify all Branch Supervisors of an employee when department manager returns/rejects the evaluation
 */
function notifySupervisorOfReturnedEvaluation($conn, $employee_id, $evaluation_id, $manager_name)
{
    $employee_id = (int)$employee_id;
    $evaluation_id = (int)$evaluation_id;

    // Get employee details
    $emp_stmt = $conn->prepare("SELECT reports_to, branch_id, department_id FROM employees WHERE employee_id = ? LIMIT 1");
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $emp_info = $emp_stmt->get_result()->fetch_assoc();
    $emp_stmt->close();

    if (!$emp_info) {
        return false;
    }

    $reports_to = $emp_info['reports_to'] ? (int)$emp_info['reports_to'] : 0;
    $branch_id = $emp_info['branch_id'] ? (int)$emp_info['branch_id'] : 0;
    $department_id = $emp_info['department_id'] ? (int)$emp_info['department_id'] : 0;

    // Find all supervisors/managers who can confirm the rating (excluding the returning manager)
    $query = "
        SELECT DISTINCT u.user_id
        FROM users u
        JOIN employees s ON u.employee_id = s.employee_id
        WHERE s.is_active = 1 
          AND s.deleted_at IS NULL
          AND (
            (s.employee_id = ? AND ? > 0)
            OR (
              s.branch_id = ? 
              AND s.department_id = ? 
              AND s.employee_id != ?
              AND (s.rank_category_id = 4 OR s.job_title LIKE '%Supervisor%')
            )
          )
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $reports_to, $reports_to, $branch_id, $department_id, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notified = false;
    $employee_name = getEmployeeNameById($conn, $employee_id);

    while ($row = $result->fetch_assoc()) {
        $supervisor_user_id = (int)$row['user_id'];
        createNotification(
            $conn,
            $supervisor_user_id,
            'Evaluation Returned by Department Manager',
            $manager_name . ' returned the evaluation for ' . $employee_name . ' to your level for re-evaluation.',
            BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id
        );
        $notified = true;
    }
    $stmt->close();

    // Fallback: Notify HR Supervisor if no branch supervisors found
    if (!$notified) {
        $hr_supervisors_stmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND branch_id = ? AND is_active = 1");
        $hr_supervisors_stmt->bind_param("i", $branch_id);
        $hr_supervisors_stmt->execute();
        $hr_supervisors = $hr_supervisors_stmt->get_result();
        while ($hr_sup = $hr_supervisors->fetch_assoc()) {
            createNotification(
                $conn,
                (int)$hr_sup['user_id'],
                'Evaluation Returned by Department Manager',
                $manager_name . ' returned the evaluation for ' . $employee_name . ' for re-evaluation. (No supervisor assigned)',
                BASE_URL . '/supervisor/pending-endorsements.php'
            );
            $notified = true;
        }
        $hr_supervisors_stmt->close();
    }

    return $notified;
}

/**
 * Check if employee is a department manager (has subordinate supervisors who themselves have subordinates)
 */
function isDeptManagerRole($conn, $employee_id)
{
    $employee_id = (int)$employee_id;
    if (!$employee_id) {
        return false;
    }

    if (getEmployeeHRRole($conn, $employee_id) !== null || isMainOfficeHumanResourcesEmployee($conn, $employee_id)) {
        return false;
    }

    // 1. Direct reports_to check: Check if there is any active employee whose supervisor reports to this employee
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM employees e
        JOIN employees s ON e.reports_to = s.employee_id
        WHERE s.reports_to = ? AND e.is_active = 1 AND s.is_active = 1
    ");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $count = (int)($stmt->get_result()->fetch_assoc()['count'] ?? 0);
    $stmt->close();

    if ($count > 0) {
        return true;
    }

    // 2. Fallback check: is the employee themselves a Manager (rank_category_id = 3 or job_title containing Manager)?
    $stmt = $conn->prepare("
        SELECT rank_category_id, job_title 
        FROM employees 
        WHERE employee_id = ? AND is_active = 1 AND deleted_at IS NULL 
        LIMIT 1
    ");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($emp) {
        $rank_category_id = (int)($emp['rank_category_id'] ?? 0);
        $job_title = $emp['job_title'] ?? '';
        if ($rank_category_id === 3 || stripos($job_title, 'Manager') !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Get employee's HR role from active user accounts
 */
function getEmployeeHRRole($conn, $employee_id)
{
    $employee_id = (int)$employee_id;
    if (!$employee_id || !$conn) {
        return null;
    }
    $stmt = $conn->prepare("SELECT role FROM users WHERE employee_id = ? AND role IN ('HR Staff', 'HR Supervisor', 'HR Manager') AND is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res ? $res['role'] : null;
}

function ensureEmployeeEvaluationStatusViewSchema($conn): bool
{
    static $ensured = false;
    if ($ensured) {
        return true;
    }

    try {
        $conn->query("
            CREATE TABLE IF NOT EXISTS employee_evaluation_status_views (
                employee_id INT NOT NULL PRIMARY KEY,
                last_viewed_at DATETIME NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (mysqli_sql_exception $e) {
        return false;
    }

    $ensured = true;
    return true;
}

function markEmployeeEvaluationStatusViewed($conn, $employee_id): bool
{
    $employee_id = (int)$employee_id;
    if ($employee_id <= 0 || !ensureEmployeeEvaluationStatusViewSchema($conn)) {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO employee_evaluation_status_views (employee_id, last_viewed_at)
        VALUES (?, NOW())
        ON DUPLICATE KEY UPDATE last_viewed_at = NOW()
    ");
    $stmt->bind_param("i", $employee_id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function isSupervisorLevelEmployee($employee): bool
{
    if (!is_array($employee)) {
        return false;
    }

    $rank_category_id = (int)($employee['rank_category_id'] ?? 0);
    $job_title = (string)($employee['job_title'] ?? '');

    return $rank_category_id === 4 || stripos($job_title, 'Supervisor') !== false;
}

function isMainOfficeHumanResourcesEmployee($conn, $employee_id): bool
{
    $employee_id = (int)$employee_id;
    if ($employee_id <= 0) {
        return false;
    }

    $stmt = $conn->prepare("
        SELECT d.department_name, b.branch_name
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN branches b ON e.branch_id = b.branch_id
        WHERE e.employee_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return false;
    }

    $department = strtolower(trim((string)($row['department_name'] ?? '')));
    $branch = strtolower(trim((string)($row['branch_name'] ?? '')));

    $is_hr_department = in_array($department, ['human resources', 'human resource', 'hr'], true)
        || strpos($department, 'human resources') !== false;
    $is_main_office = strpos($branch, 'main') !== false || strpos($branch, 'head office') !== false;

    return $is_hr_department && $is_main_office;
}
?>
