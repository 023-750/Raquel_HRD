<?php
$page_title = 'Career Progression';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';
require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Supervisor &middot; Career</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-chart-line me-2" style="color:#BD9414;"></i>Career Progression</h4>
            <p class="text-white-50 small mb-0 mt-2">Identify employee growth opportunities and review career readiness across your assigned workforce.</p>
        </div>
    </div>
</div>

<div class="chart-card fadeup text-center py-5">
    <div class="cc-body">
        <i class="fas fa-project-diagram mb-4" style="font-size: 4rem; color: var(--primary-gold); opacity: 0.2;"></i>
        <h3 class="fw-bold">Career Progression Roadmap</h3>
        <p class="text-muted mx-auto" style="max-width: 500px;">
            We are building a comprehensive career progression visualizer. This section will soon feature employee growth roadmaps, skill gap analysis, and automated promotion tracks.
        </p>
        <div class="mt-4">
            <a href="career-movements.php" class="btn btn-primary px-4">
                <i class="fas fa-route me-2"></i>View Career Movements
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
