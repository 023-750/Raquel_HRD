<?php
/**
 * Common Header - includes navbar, sidebar, and CDN links
 * Usage: include this file at the top of every dashboard page
 * Requires: $page_title (string), session must be active
 */

require_once __DIR__ . '/functions.php';

// Get dynamic branding settings
$sys_pawnshop_name = getSetting($conn, 'company_name', 'Raquel Pawnshop');
$sys_logo = getSetting($conn, 'system_logo', 'assets/img/logo/logo.png');

// Determine current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

$effective_role = $_SESSION['role'] ?? '';

// Notifications are strictly account-based but now isolated by portal context.
$notif_context = ($current_dir === 'employee') ? 'employee' : 'hr';
$notif_count = getUnreadNotificationCount($conn, (int) $_SESSION['user_id'], $notif_context);
$notifications = getRecentNotifications($conn, (int) $_SESSION['user_id'], 5, $notif_context);

// 1. Get profile picture from the linked EMPLOYEE account
$stmt = $conn->prepare("
    SELECT e.profile_picture 
    FROM users u 
    LEFT JOIN employees e ON u.employee_id = e.employee_id 
    WHERE u.user_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$display_avatar = getEmployeeAvatar(''); // Default
if ($row = $res->fetch_assoc()) {
    $display_avatar = getEmployeeAvatar($row['profile_picture']);
}
$stmt->close();

// Define sidebar menus per role
$sidebar_menus = [];

switch ($effective_role) {
    case 'Admin':
        $sidebar_menus = [
            'MAIN' => [
                ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => BASE_URL . '/admin/dashboard.php', 'page' => 'dashboard.php'],
            ],
            'MANAGEMENT' => [
                ['icon' => 'fas fa-id-badge', 'label' => 'Member List', 'url' => BASE_URL . '/admin/members.php', 'page' => 'members.php'],
                ['icon' => 'fas fa-user-lock', 'label' => 'Portal Accounts', 'url' => BASE_URL . '/admin/employee-accounts.php', 'page' => 'employee-accounts.php'],
                ['icon' => 'fas fa-users', 'label' => 'User Management', 'url' => BASE_URL . '/admin/users.php', 'page' => 'users.php'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Audit Trail', 'url' => BASE_URL . '/admin/audit-trail.php', 'page' => 'audit-trail.php'],
            ],
            'SYSTEM' => [
                ['icon' => 'fas fa-database', 'label' => 'System Backup', 'url' => BASE_URL . '/admin/backup.php', 'page' => 'backup.php'],
                ['icon' => 'fas fa-cogs', 'label' => 'System Config', 'url' => BASE_URL . '/admin/config.php', 'page' => 'config.php'],
            ],
        ];
        break;

    case 'HR Manager':
        $sidebar_menus = [
            'MAIN' => [
                ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => BASE_URL . '/manager/dashboard.php', 'page' => 'dashboard.php'],
            ],
            'EMPLOYEES' => [
                ['icon' => 'fas fa-users', 'label' => 'Employees', 'url' => BASE_URL . '/manager/employees.php', 'page' => 'employees.php'],
                ['icon' => 'fas fa-user-plus', 'label' => 'Add Employee', 'url' => BASE_URL . '/manager/add-employee.php', 'page' => 'add-employee.php'],
            ],
            'ORGANIZATION' => [
                ['icon' => 'fas fa-building', 'label' => 'Branches', 'url' => BASE_URL . '/manager/branches.php', 'page' => 'branches.php'],
                ['icon' => 'fas fa-sitemap', 'label' => 'Departments', 'url' => BASE_URL . '/manager/departments.php', 'page' => 'departments.php'],
                ['icon' => 'fas fa-briefcase', 'label' => 'Positions', 'url' => BASE_URL . '/manager/positions.php', 'page' => 'positions.php'],
                // ['icon' => 'fas fa-project-diagram', 'label' => 'Operation Management', 'url' => BASE_URL . '/manager/operation-management.php', 'page' => 'operation-management.php'],
            ],
            'EVALUATIONS' => [
                ['icon' => 'fas fa-file-alt', 'label' => 'Templates', 'url' => BASE_URL . '/manager/templates.php', 'page' => 'templates.php'],
                ['icon' => 'fas fa-check-double', 'label' => 'Pending Approvals', 'url' => BASE_URL . '/manager/pending-approvals.php', 'page' => 'pending-approvals.php'],
                ['icon' => 'fas fa-history', 'label' => 'Evaluation History', 'url' => BASE_URL . '/manager/evaluation-history.php', 'page' => 'evaluation-history.php'],
            ],

            'CAREER' => [
                ['icon' => 'fas fa-route', 'label' => 'Career Movements', 'url' => BASE_URL . '/manager/career-movements.php', 'page' => 'career-movements.php'],
                ['icon' => 'fas fa-chart-line', 'label' => 'Career Progression', 'url' => BASE_URL . '/manager/career-progression.php', 'page' => 'career-progression.php'],
            ],
            'ANALYTICS' => [
                ['icon' => 'fas fa-chart-bar', 'label' => 'Analytics', 'url' => BASE_URL . '/manager/analytics.php', 'page' => 'analytics.php'],
                ['icon' => 'fas fa-file-pdf', 'label' => 'Reports', 'url' => BASE_URL . '/manager/reports.php', 'page' => 'reports.php'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Audit Trail', 'url' => BASE_URL . '/manager/audit-trail.php', 'page' => 'audit-trail.php'],
            ],
        ];
        break;

    case 'HR Supervisor':
        $sidebar_menus = [
            'MAIN' => [
                ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => BASE_URL . '/supervisor/dashboard.php', 'page' => 'dashboard.php'],
            ],
            'EMPLOYEES' => [
                ['icon' => 'fas fa-address-book', 'label' => 'Employee Info', 'url' => BASE_URL . '/supervisor/employees.php', 'page' => 'employees.php'],
            ],
            'EVALUATIONS' => [
                ['icon' => 'fas fa-clipboard-check', 'label' => 'Pending Validations', 'url' => BASE_URL . '/supervisor/pending-endorsements.php', 'page' => 'pending-endorsements.php'],
                ['icon' => 'fas fa-history', 'label' => 'Evaluation History', 'url' => BASE_URL . '/supervisor/evaluation-history.php', 'page' => 'evaluation-history.php'],
            ],
            'CAREER' => [
                ['icon' => 'fas fa-route', 'label' => 'Career Movements', 'url' => BASE_URL . '/supervisor/career-movements.php', 'page' => 'career-movements.php'],
                ['icon' => 'fas fa-chart-line', 'label' => 'Career Progression', 'url' => BASE_URL . '/supervisor/career-progression.php', 'page' => 'career-progression.php'],
            ],
            'ANALYTICS' => [
                ['icon' => 'fas fa-chart-bar', 'label' => 'Branch Analytics', 'url' => BASE_URL . '/supervisor/analytics.php', 'page' => 'analytics.php'],
                ['icon' => 'fas fa-file-alt', 'label' => 'Reports', 'url' => BASE_URL . '/supervisor/reports.php', 'page' => 'reports.php'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'My Audit Trail', 'url' => BASE_URL . '/supervisor/audit-trail.php', 'page' => 'audit-trail.php'],
            ],
        ];
        break;

    case 'HR Staff':
        $sidebar_menus = [
            'MAIN' => [
                ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => BASE_URL . '/staff/dashboard.php', 'page' => 'dashboard.php'],
            ],
            'DIRECTORY' => [
                ['icon' => 'fas fa-users', 'label' => 'Employee Directory', 'url' => BASE_URL . '/staff/search-employees.php', 'page' => 'search-employees.php'],
            ],
            'EVALUATIONS' => [
                ['icon' => 'fas fa-history', 'label' => 'Evaluation History', 'url' => BASE_URL . '/staff/evaluation-history.php', 'page' => 'evaluation-history.php'],
                ['icon' => 'fas fa-route', 'label' => 'Career Movements', 'url' => BASE_URL . '/staff/career-movements.php', 'page' => 'career-movements.php'],
            ],
        ];
        break;

    case 'Employee':

        // Count pending evaluation templates for mobile bottom-nav badge
        $m_pending_template_count = 0;
        if (isset($_SESSION['employee_id']) && $conn) {
            $_hdr_emp_id = (int) $_SESSION['employee_id'];
            $_hdr_dept_stmt = $conn->prepare("SELECT d.department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employee_id = ? LIMIT 1");
            $_hdr_dept_stmt->bind_param("i", $_hdr_emp_id);
            $_hdr_dept_stmt->execute();
            $_hdr_dept_row = $_hdr_dept_stmt->get_result()->fetch_assoc();
            $_hdr_emp_dept = $_hdr_dept_row['department_name'] ?? '';
            $_hdr_dept_stmt->close();

            $_hdr_pt_stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM evaluation_templates et
                WHERE et.status = 'Active'
                  AND et.deleted_at IS NULL
                  AND (et.target_department IS NULL OR et.target_department = '' OR et.target_department = 'All Departments' OR et.target_department = ?)
                  AND NOT EXISTS (
                      SELECT 1
                      FROM evaluations ev
                      WHERE ev.employee_id = ?
                        AND ev.template_id = et.template_id
                        AND ev.deleted_at IS NULL
                        AND ev.status NOT IN ('Draft', 'Returned', 'Rejected', 'Pending Self-Rating')
                  )
            ");
            $_hdr_pt_stmt->bind_param("si", $_hdr_emp_dept, $_hdr_emp_id);
            $_hdr_pt_stmt->execute();
            $m_pending_template_count = (int) ($_hdr_pt_stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $_hdr_pt_stmt->close();
        }

        $self_service_menu = [
            ['icon' => 'fas fa-briefcase', 'label' => 'My Employment', 'url' => BASE_URL . '/employee/my-employment.php', 'page' => 'my-employment.php'],
            ['icon' => 'fas fa-star', 'label' => 'Self Rating', 'url' => BASE_URL . '/employee/self-rating.php', 'page' => 'self-rating.php'],
            ['icon' => 'fas fa-clipboard-check', 'label' => 'Evaluation Status', 'url' => BASE_URL . '/employee/completed-ratings.php', 'page' => 'completed-ratings.php'],
        ];

        // Add department manager links
        $is_dept_manager_menu = false;
        if (isset($_SESSION['employee_id']) && $conn) {
            $is_dept_manager_menu = isDeptManagerRole($conn, (int) $_SESSION['employee_id']);
        }
        if ($is_dept_manager_menu) {
            $self_service_menu[] = ['icon' => 'fas fa-user-shield', 'label' => 'Dept Manager Review', 'url' => BASE_URL . '/employee/dept-manager-review.php', 'page' => 'dept-manager-review.php'];
        }

        // Add My Team link for supervisors/managers — excluding Human Resources department
        $is_supervisor_menu  = false;
        $hdr_sup_dept_name   = '';
        if (isset($_SESSION['employee_id']) && $conn) {
            $_hdr_sup_id = (int)$_SESSION['employee_id'];
            $is_supervisor_menu = hasSupervisorPrivileges($conn, $_hdr_sup_id);
            if ($is_supervisor_menu) {
                $_hdr_dep_stmt = $conn->prepare('SELECT d.department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employee_id = ? LIMIT 1');
                $_hdr_dep_stmt->bind_param('i', $_hdr_sup_id);
                $_hdr_dep_stmt->execute();
                $hdr_sup_dept_name = $_hdr_dep_stmt->get_result()->fetch_assoc()['department_name'] ?? '';
                $_hdr_dep_stmt->close();
            }
        }
        if ($is_supervisor_menu && $hdr_sup_dept_name !== 'Human Resources') {
            $self_service_menu[] = ['icon' => 'fas fa-users', 'label' => 'My Team', 'url' => BASE_URL . '/employee/team-list.php', 'page' => 'team-list.php'];
        }

        $self_service_menu[] = ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => BASE_URL . '/employee/notifications.php', 'page' => 'notifications.php'];

        $sidebar_menus = [
            'MAIN' => [
                ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => BASE_URL . '/employee/dashboard.php', 'page' => 'dashboard.php'],
            ],
            'SELF SERVICE' => $self_service_menu,
            'SETTINGS' => [
                ['icon' => 'fas fa-user-cog', 'label' => 'Change Password', 'url' => BASE_URL . '/employee/profile-settings.php', 'page' => 'profile-settings.php'],
            ],
        ];
        break;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Dashboard'); ?> - Raquel Pawnshop HRIS</title>
    <meta name="description" content="Raquel Pawnshop Human Resource Information System">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/pjax.js?v=<?php echo time(); ?>"></script>
    <script>
        // Prevent FOUC for collapsed sidebar
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
        // Expose app base URL for shared JS utilities.
        window.APP_BASE_URL = <?php echo json_encode(BASE_URL); ?>;
        window.NOTIF_CONTEXT = <?php echo json_encode($notif_context === 'employee' ? 'employee' : 'hr'); ?>;
    </script>
</head>

<body class="<?php echo ($current_dir === 'admin' ? 'admin-area' : '') . ($effective_role === 'Employee' ? ' role-employee' : ''); ?>">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo BASE_URL . '/' . e($sys_logo); ?>" alt="Logo"
                style="width: 50px; height: 50px; border-radius: 12px; object-fit: cover; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); background: white; border: 2px solid rgba(255,255,255,0.1);">
            <h2><?php echo e($sys_pawnshop_name); ?></h2>
            <?php if ($effective_role === 'Employee'): ?>
                <small>Your HRIS Employee Portal</small>
            <?php else: ?>
                <small>HRIS • <?php echo e($effective_role); ?></small>
            <?php endif; ?>
        </div>

        <nav class="sidebar-nav" id="sidebar-nav">
            <?php foreach ($sidebar_menus as $label => $items): ?>
                <div class="nav-label"><?php echo e($label); ?></div>
                <?php foreach ($items as $item): ?>
                    <?php
                    $classes = ($current_page === $item['page']) ? 'active' : '';
                    if (!empty($item['class']))
                        $classes .= ($classes ? ' ' : '') . $item['class'];
                    ?>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo $classes; ?>"
                        title="<?php echo e($item['label']); ?>">
                        <i class="<?php echo $item['icon']; ?>"></i>
                        <span class="nav-text"><?php echo e($item['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <div class="nav-label">ACCOUNT</div>
            <a href="<?php echo BASE_URL; ?>/logout.php" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle <?php echo ($effective_role === 'Employee') ? 'd-none d-md-block' : ''; ?>" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-logo d-flex align-items-center gap-2">
                <img src="<?php echo BASE_URL . '/' . e($sys_logo); ?>" alt="Logo"
                    style="width: 35px; height: 35px; border-radius: 8px; object-fit: cover;">
                <h1 class="page-title mb-0"><?php echo e($page_title ?? 'Dashboard'); ?></h1>
            </div>
        </div>

        <div class="nav-right">
            <!-- Notification Bell (visible on all screen sizes) -->
            <div class="dropdown">
                <button class="notification-btn" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBtn">
                    <i class="fas fa-bell"></i>
                    <?php if ($notif_count > 0): ?>
                        <span class="notification-badge"><?php echo $notif_count > 9 ? '9+' : $notif_count; ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                    <div class="dropdown-header">
                        Notifications
                        <?php if ($notif_count > 0): ?>
                            <a href="#" onclick="markAllRead(); return false;"
                                style="font-size:0.75rem;font-weight:400;">Mark all read</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($notifications)): ?>
                        <div class="p-3 text-center text-muted" style="font-size:0.85rem;">
                            <i class="fas fa-bell-slash d-block mb-2" style="font-size:1.5rem;opacity:0.3;"></i>
                            No notifications
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <a href="<?php echo e($notif['link'] ?? '#'); ?>"
                                class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                <div class="notif-title"><?php echo e($notif['title']); ?></div>
                                <div class="notif-message"><?php echo e($notif['message']); ?></div>
                                <div class="notif-time"><?php echo formatDateTime($notif['created_at']); ?></div>
                            </a>
                        <?php endforeach; ?>

                        <?php
                        $current_portal = basename(dirname($_SERVER['SCRIPT_NAME']));
                        // Use portal name as URL part, fallback to session role for others
                        if (in_array($current_portal, ['employee', 'staff', 'manager', 'supervisor', 'admin'])) {
                            $notif_url = BASE_URL . '/' . $current_portal . '/notifications.php';
                        } else {
                            $role_map = [
                                'Admin' => 'admin',
                                'HR Manager' => 'manager',
                                'HR Supervisor' => 'supervisor',
                                'HR Staff' => 'staff',
                                'Employee' => 'employee'
                            ];
                            $portal_name = $role_map[$_SESSION['role'] ?? 'Employee'] ?? 'employee';
                            $notif_url = BASE_URL . '/' . $portal_name . '/notifications.php';
                        }
                        ?>
                        <div class="dropdown-footer text-center p-2 border-top mt-1" style="background: var(--bg-gray);">
                            <a href="<?php echo $notif_url; ?>" class="text-decoration-none"
                                style="font-size: 0.85rem; font-weight: 600; color: var(--primary-blue);">
                                View All Notifications
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown user-dropdown <?php echo ($effective_role === 'Employee') ? 'd-none d-md-block' : ''; ?>">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <img src="<?php echo $display_avatar . '?v=' . time(); ?>" alt="Avatar">
                    </div>
                    <span class="d-none d-md-inline"><?php echo e($_SESSION['full_name']); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text"><small
                                class="text-muted"><?php echo e($_SESSION['role']); ?></small></span></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php"><i
                                class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>

            <?php if ($effective_role === 'Employee'): ?>
                <!-- Mobile Settings Gear Dropdown (shows on mobile only) -->
                <div class="dropdown d-md-none">
                    <button class="notification-btn" data-bs-toggle="dropdown" aria-expanded="false" id="mobileGearBtn" style="color: #074B02;">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08);">
                        <li>
                            <span class="dropdown-item-text fw-bold" style="font-size:0.85rem;color:var(--text-muted);">
                                <i class="fas fa-user-circle me-1"></i><?php echo e($_SESSION['full_name']); ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/employee/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2" style="width: 20px; text-align: center;"></i>Dashboard
                            </a>
                        </li>
                        <?php if (isset($_SESSION['employee_id']) && $conn && isDeptManagerRole($conn, (int)$_SESSION['employee_id'])): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/employee/dept-manager-review.php">
                                    <i class="fas fa-user-shield me-2" style="width: 20px; text-align: center;"></i>Dept Manager Review
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php
                        // My Team — for supervisors/managers excluding Human Resources dept
                        if (isset($_SESSION['employee_id']) && $conn) {
                            $_gear_sup_id = (int)$_SESSION['employee_id'];
                            if (hasSupervisorPrivileges($conn, $_gear_sup_id)) {
                                $_gear_dep = $conn->query("SELECT d.department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employee_id = $_gear_sup_id LIMIT 1");
                                $_gear_dept_name = $_gear_dep ? ($_gear_dep->fetch_assoc()['department_name'] ?? '') : '';
                                if ($_gear_dept_name !== 'Human Resources'):
                        ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/employee/team-list.php">
                                    <i class="fas fa-users me-2" style="width: 20px; text-align: center;"></i>My Team
                                </a>
                            </li>
                        <?php
                                endif;
                            }
                        }
                        ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/employee/profile-settings.php">
                                <i class="fas fa-user-cog me-2" style="width: 20px; text-align: center;"></i>Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt me-2" style="width: 20px; text-align: center;"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <main class="main-content">
        <?php
        // Always consume the flash session to prevent it leaking to the next page.
        // Suppress the visual banner on pages that have their own inline feedback:
        //   - employee-accounts.php  → Portal Accounts (uses credential slip modal)
        //   - users.php              → User Management (has its own inline alerts)
        $suppress_flash_pages = ['employee-accounts.php', 'users.php'];
        if (isset($_SESSION['flash_message'])) {
            if (!in_array($current_page, $suppress_flash_pages, true)) {
                displayFlashMessage(); // renders and clears
            } else {
                unset($_SESSION['flash_type'], $_SESSION['flash_message']); // clear only
            }
        }
        ?>