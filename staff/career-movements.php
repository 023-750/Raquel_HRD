<?php
/**
 * HR Staff Portal - Career Movements (Under Construction)
 */
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';
require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Staff Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Career Movements</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-route me-1"></i>Future validation and approval center for promotions, demotions, and lateral transfers.
            </p>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6">
        <div class="content-card text-center p-5 shadow-lg border-0" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(15px); border-radius: 20px;">
            <div class="under-construction-icon mb-4">
                <span class="fa-stack fa-3x">
                    <i class="fas fa-circle fa-stack-2x text-primary-light" style="color: rgba(41, 67, 6, 0.08);"></i>
                    <i class="fas fa-route fa-stack-1x text-primary animate-pulse" style="color: var(--primary-blue);"></i>
                </span>
            </div>
            
            <h3 class="fw-bold mb-2 text-dark">Feature Under Construction</h3>
            <div class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-4" style="font-weight: 700; font-size: 0.8rem; letter-spacing: 0.5px;">
                <i class="fas fa-hammer me-1 animate-spin"></i>COMING SOON
            </div>
            
            <p class="text-muted mb-4 fs-6" style="line-height: 1.6;">
                The **Career Movements Approval & Validation System** is currently under development. This space will soon house advanced tools for HR Staff to manage and process personnel movement requisitions:
            </p>
            
            <div class="text-start mx-auto p-4 mb-4 rounded-4" style="background: rgba(41, 67, 6, 0.03); max-width: 460px; border: 1px solid rgba(41, 67, 6, 0.05);">
                <div class="d-flex align-items-start mb-3 gap-3">
                    <i class="fas fa-check-circle text-success mt-1"></i>
                    <div>
                        <strong class="text-dark small d-block">Career Suitability Verification</strong>
                        <span class="text-muted small">Verify employee suitability forms completed by Area Supervisors.</span>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3 gap-3">
                    <i class="fas fa-check-circle text-success mt-1"></i>
                    <div>
                        <strong class="text-dark small d-block">Promotion Routing & Audits</strong>
                        <span class="text-muted small">Route lateral movements, promotions, or transfers directly to the HR Manager.</span>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <i class="fas fa-check-circle text-success mt-1"></i>
                    <div>
                        <strong class="text-dark small d-block">Historical Progression Tracking</strong>
                        <span class="text-muted small">Review complete career progress trajectories for all Raquel Pawnshop staff.</span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 mt-2">
                <a href="dashboard.php" class="btn btn-primary rounded-pill px-4 py-2">
                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                </a>
                <a href="search-employees.php" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                    <i class="fas fa-users me-2"></i>Employee Directory
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.under-construction-icon {
    display: inline-block;
}
.animate-pulse {
    animation: pulse 2s infinite ease-in-out;
}
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}
.animate-spin {
    animation: hammer 2.5s infinite ease-in-out;
}
@keyframes hammer {
    0%, 100% {
        transform: rotate(0deg);
    }
    30% {
        transform: rotate(-25deg);
    }
    50% {
        transform: rotate(15deg);
    }
    70% {
        transform: rotate(-10deg);
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
