<?php
// ============================================
// Session Validation
// ============================================

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If accessing employee folder, redirect to employee login
    if (strpos($_SERVER['REQUEST_URI'], '/employee/') !== false) {
        header("Location: " . BASE_URL . "/employee/index.php");
    } else {
        header("Location: " . BASE_URL . "/index.php");
    }
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify user & employee account status
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $active_chk = $conn->prepare("SELECT u.is_active as user_active, e.is_active as emp_active, e.employment_status FROM users u LEFT JOIN employees e ON u.employee_id = e.employee_id WHERE u.user_id = ?");
    if ($active_chk) {
        $active_chk->bind_param("i", $uid);
        $active_chk->execute();
        $active_res = $active_chk->get_result();
        if ($row = $active_res->fetch_assoc()) {
            $is_emp_inactive = ($row['emp_active'] !== null && (int)$row['emp_active'] === 0) || (strcasecmp($row['employment_status'] ?? '', 'Inactive') === 0);
            if (!$row['user_active'] || $is_emp_inactive) {
                session_unset();
                session_destroy();
                if (strpos($_SERVER['REQUEST_URI'], '/employee/') !== false) {
                    header("Location: " . BASE_URL . "/employee/index.php");
                } else {
                    header("Location: " . BASE_URL . "/index.php");
                }
                exit();
            }
        }
        $active_chk->close();
    }
}

/**
 * Check if current user has the required role.
 * 
 * @param array $allowed_roles Array of allowed role strings
 */
function checkRole($allowed_roles)
{
    $current_role = $_SESSION['role'] ?? '';

    if (!in_array($current_role, $allowed_roles, true)) {
        if (in_array('Employee', $allowed_roles, true)) {
            header("Location: " . BASE_URL . "/employee/index.php");
        } else {
            header("Location: " . BASE_URL . "/index.php");
        }
        exit();
    }
}
?>
