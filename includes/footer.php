</main><!-- end .main-content -->

<?php 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Employee'): 
    $curr_p = $current_page ?? basename($_SERVER['PHP_SELF']);
    $m_notif_count = $notif_count ?? 0;
?>
    <!-- Mobile Bottom Navigation -->
    <nav class="employee-bottom-nav d-md-none">
        <a href="<?php echo BASE_URL; ?>/employee/my-employment.php" class="nav-item <?php echo ($curr_p === 'my-employment.php') ? 'active' : ''; ?>">
            <i class="fas fa-briefcase nav-icon"></i>
            <span class="nav-label">My Employment</span>
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
        <a href="<?php echo BASE_URL; ?>/employee/completed-ratings.php" class="nav-item <?php echo ($curr_p === 'completed-ratings.php') ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check nav-icon"></i>
            <span class="nav-label">Evaluation Status</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/employee/notifications.php" class="nav-item <?php echo ($curr_p === 'notifications.php') ? 'active' : ''; ?>">
            <div class="position-relative">
                <i class="fas fa-bell nav-icon"></i>
                <?php if ($m_notif_count > 0): ?>
                    <span class="mobile-notif-badge"><?php echo $m_notif_count > 9 ? '9+' : $m_notif_count; ?></span>
                <?php endif; ?>
            </div>
            <span class="nav-label">Notifications</span>
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
<script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
