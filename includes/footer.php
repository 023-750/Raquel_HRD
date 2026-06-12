</main><!-- end .main-content -->

<?php 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Employee'): 
    $curr_p = $current_page ?? basename($_SERVER['PHP_SELF']);
    $m_notif_count = $notif_count ?? 0;
?>
    <!-- Mobile Bottom Navigation -->
    <nav class="employee-bottom-nav d-md-none">
        <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="nav-item <?php echo ($curr_p === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home nav-icon"></i>
            <span class="nav-label">Home</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/employee/my-employment.php" class="nav-item <?php echo ($curr_p === 'my-employment.php') ? 'active' : ''; ?>">
            <i class="fas fa-briefcase nav-icon"></i>
            <span class="nav-label">Employment</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/employee/self-rating.php" class="nav-item <?php echo ($curr_p === 'self-rating.php') ? 'active' : ''; ?>">
            <div class="position-relative">
                <i class="fas fa-star nav-icon"></i>
                <?php
                    $m_sr_count = $m_pending_template_count ?? 0;
                    if ($m_sr_count > 0):
                ?>
                    <span class="mobile-notif-badge"><?php echo $m_sr_count > 9 ? '9+' : $m_sr_count; ?></span>
                <?php endif; ?>
            </div>
            <span class="nav-label">Self Rating</span>
        </a>
        <?php
        $is_dept_mgr = false;
        if (isset($_SESSION['employee_id']) && $conn) {
            $is_dept_mgr = isDeptManagerRole($conn, (int)$_SESSION['employee_id']);
        }
        if ($is_dept_mgr):
            $m_dept_pending_count = $m_dept_review_count ?? 0;
            if (!isset($m_dept_review_count)) {
                $_hdr_mgr_id = (int)$_SESSION['employee_id'];
                $_hdr_dept_stmt = $conn->prepare("
                    SELECT e.evaluation_id, e.employee_id
                    FROM evaluations e
                    JOIN employees emp ON e.employee_id = emp.employee_id
                    WHERE e.status = 'Pending Dept Manager'
                      AND e.deleted_at IS NULL
                      AND emp.is_active = 1
                      AND emp.deleted_at IS NULL
                ");
                if ($_hdr_dept_stmt) {
                    $_hdr_dept_stmt->execute();
                    $_hdr_pending_rows = $_hdr_dept_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $_hdr_dept_stmt->close();
                    foreach ($_hdr_pending_rows as $_hdr_pending) {
                        $_hdr_dm = getDeptManagerOfEmployee($conn, (int)$_hdr_pending['employee_id']);
                        if ($_hdr_dm && (int)$_hdr_dm['supervisor_employee_id'] === $_hdr_mgr_id) {
                            $m_dept_pending_count++;
                        }
                    }
                }
            }
        ?>
            <a href="<?php echo BASE_URL; ?>/employee/dept-manager-review.php" class="nav-item <?php echo ($curr_p === 'dept-manager-review.php') ? 'active' : ''; ?>">
                <div class="position-relative">
                    <i class="fas fa-user-check nav-icon"></i>
                    <?php if ($m_dept_pending_count > 0): ?>
                        <span class="mobile-notif-badge"><?php echo $m_dept_pending_count > 9 ? '9+' : $m_dept_pending_count; ?></span>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Dept Review</span>
            </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>/employee/completed-ratings.php" class="nav-item <?php echo ($curr_p === 'completed-ratings.php') ? 'active' : ''; ?>">
            <div class="position-relative">
                <i class="fas fa-list-check nav-icon"></i>
                <?php
                    $m_status_count = $m_eval_status_count ?? 0;
                    if ($m_status_count > 0):
                ?>
                    <span class="mobile-notif-badge"><?php echo $m_status_count > 9 ? '9+' : $m_status_count; ?></span>
                <?php endif; ?>
            </div>
            <span class="nav-label">Status</span>
        </a>
        <?php
        // "Confirm Rating" shortcut — only for immediate heads outside Human Resources department
        $m_confirm_dept_name = '';
        $m_is_supervisor     = false;
        if (isset($_SESSION['employee_id']) && $conn) {
            $_m_sup_id = (int)$_SESSION['employee_id'];
            $m_is_supervisor = hasSupervisorPrivileges($conn, $_m_sup_id);
            if ($m_is_supervisor) {
                $_m_dep_r = $conn->query("SELECT d.department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employee_id = $_m_sup_id LIMIT 1");
                if ($_m_dep_r) {
                    $m_confirm_dept_name = $_m_dep_r->fetch_assoc()['department_name'] ?? '';
                }
            }
        }
        if ($m_is_supervisor && !$is_dept_mgr && $m_confirm_dept_name !== 'Human Resources'):
            // Count members directly reporting to this supervisor with a pending status
            $_m_confirm_count = $m_confirm_rating_count ?? 0;
            if (!isset($m_confirm_rating_count)) {
                $_m_c_stmt = $conn->prepare("
                    SELECT ev.evaluation_id, ev.employee_id
                    FROM evaluations ev
                    JOIN employees e ON ev.employee_id = e.employee_id
                    WHERE e.employee_id <> ?
                      AND ev.status IN ('Pending Dept Supervisor','Pending Supervisor')
                      AND ev.deleted_at IS NULL
                      AND e.is_active = 1
                      AND e.deleted_at IS NULL
                ");
                if ($_m_c_stmt) {
                    $_m_c_stmt->bind_param('i', $_m_sup_id);
                    $_m_c_stmt->execute();
                    $_m_confirm_rows = $_m_c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $_m_c_stmt->close();
                    foreach ($_m_confirm_rows as $_m_confirm_row) {
                        $_m_supervisor = getEmployeeSupervisor($conn, (int)$_m_confirm_row['employee_id']);
                        if ($_m_supervisor && (int)$_m_supervisor['supervisor_employee_id'] === $_m_sup_id) {
                            $_m_confirm_count++;
                        }
                    }
                }
            }
        ?>
            <a href="<?php echo BASE_URL; ?>/employee/confirm-rating.php"
               class="nav-item <?php echo ($curr_p === 'confirm-rating.php') ? 'active' : ''; ?>">
                <div class="position-relative">
                    <i class="fas fa-user-check nav-icon"></i>
                    <?php if ($_m_confirm_count > 0): ?>
                        <span class="mobile-notif-badge"><?php echo $_m_confirm_count > 9 ? '9+' : $_m_confirm_count; ?></span>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Confirm</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>/employee/notifications.php" class="nav-item <?php echo ($curr_p === 'notifications.php') ? 'active' : ''; ?>" aria-label="Notifications<?php echo $m_notif_count > 0 ? ', ' . $m_notif_count . ' unread' : ''; ?>">
            <div class="position-relative">
                <i class="fas fa-bell nav-icon"></i>
                <?php if ($m_notif_count > 0): ?>
                    <span class="badge-dot" style="font-size:0.85rem;" aria-hidden="true"><?php echo $m_notif_count > 9 ? '9+' : $m_notif_count; ?></span>
                <?php endif; ?>
            </div>
            <span class="nav-label">Alerts</span>
        </a>
    </nav>


<?php endif; ?>


<!-- Shared Image View Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.85);">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close btn-close-white p-2 rounded-circle shadow position-absolute" 
                style="top: 15px; right: 15px; z-index: 1100; background-color: rgba(0,0,0,0.6); border: none;"
                data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body p-0 text-center">
                <img id="fullImage" src="" class="img-fluid rounded shadow" style="max-height: 85vh; border: 3px solid rgba(255,255,255,0.1);">
                <h6 id="fullImageName" class="text-white mt-3 fw-bold" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></h6>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/zebra-stripe.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Employee'): ?>
<!-- Employee Portal UX JS utilities — deferred for performance -->
<script src="<?php echo BASE_URL; ?>/assets/js/auto-save.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo BASE_URL; ?>/assets/js/form-validation.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo BASE_URL; ?>/assets/js/employee-portal-feedback.js?v=<?php echo time(); ?>" defer></script>
<?php endif; ?>
</body>
</html>
