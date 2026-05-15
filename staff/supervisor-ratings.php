<?php
/**
 * HR Staff Portal - Supervisor Ratings Overview
 * Shows evaluations that have been confirmed by supervisors and sent to HR
 */
$page_title = 'Supervisor Ratings';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);

// Fetch evaluations confirmed by supervisors
// These are evaluations where status is 'Pending HR Consolidation'
$query = "
    SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
           sup.first_name as sup_first, sup.last_name as sup_last,
           t.template_name, d.department_name, b.branch_name
    FROM evaluations e
    JOIN employees emp ON e.employee_id = emp.employee_id
    LEFT JOIN employees sup ON e.supervisor_confirmed_by = sup.employee_id
    LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
    LEFT JOIN departments d ON emp.department_id = d.department_id
    LEFT JOIN branches b ON emp.branch_id = b.branch_id
    WHERE e.status IN ('Pending HR Consolidation', 'Supervisor Confirmed', 'Approved')
      AND e.supervisor_confirmed_date IS NOT NULL
    ORDER BY e.supervisor_confirmed_date DESC
";

$results = $conn->query($query);
$evaluations = $results->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Staff Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Supervisor Ratings</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-check-double me-1"></i>360° Self-ratings confirmed by Area Supervisors / Immediate Heads
            </p>
        </div>
        <div class="d-flex gap-2">
            <div class="stat-pill">
                <span class="label">Total Received</span>
                <span class="value"><?php echo count($evaluations); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="content-card fadeup-1">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Evaluation Records</h5>
        <div class="search-box">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="recordSearch" class="form-control border-start-0" placeholder="Search employee or supervisor...">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($evaluations)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                <p>No supervisor-confirmed ratings found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="ratingsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Details</th>
                            <th>Branch / Dept</th>
                            <th>Rating Details</th>
                            <th>Confirmed By</th>
                            <th>Date Received</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluations as $eval): 
                            $status_class = getStatusBadgeClass($eval['status']);
                            $score = (float)$eval['total_score'];
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle-sm me-3">
                                            <?php echo strtoupper(substr($eval['first_name'], 0, 1) . substr($eval['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($eval['last_name'] . ', ' . $eval['first_name']); ?></div>
                                            <div class="small text-muted"><?php echo e($eval['employee_code']); ?> • <?php echo e($eval['job_title']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?php echo e($eval['branch_name'] ?: 'N/A'); ?></div>
                                    <div class="small text-muted"><?php echo e($eval['department_name'] ?: 'N/A'); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold fs-5"><?php echo number_format($score, 2); ?></span>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo e($eval['performance_level']); ?></span>
                                    </div>
                                    <div class="small text-muted mt-1"><?php echo e($eval['template_name']); ?></div>
                                </td>
                                <td>
                                    <?php if ($eval['sup_first']): ?>
                                        <div class="small fw-semibold"><?php echo e($eval['sup_last'] . ', ' . $eval['sup_first']); ?></div>
                                        <div class="small text-primary" style="font-size: 0.7rem;">Area Supervisor / Head</div>
                                    <?php else: ?>
                                        <span class="text-muted small">System / Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small"><?php echo formatDate($eval['supervisor_confirmed_date']); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?php echo date('h:i A', strtotime($eval['supervisor_confirmed_date'])); ?></div>
                                </td>
                                <td class="text-end">
                                    <a href="view-supervisor-rating.php?id=<?php echo $eval['evaluation_id']; ?>" class="btn btn-sm btn-light border rounded-pill px-3">
                                        <i class="fas fa-eye me-1 text-primary"></i>View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-circle-sm {
    width: 38px;
    height: 38px;
    background: var(--primary-light);
    color: var(--primary-blue);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    border: 1px solid rgba(41, 67, 6, 0.1);
}
.stat-pill {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 8px 16px;
    display: flex;
    flex-direction: column;
    min-width: 120px;
}
.stat-pill .label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.7);
}
.stat-pill .value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
}
</style>

<script>
document.getElementById('recordSearch')?.addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelector("#ratingsTable tbody").rows;
    
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].textContent.toUpperCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
