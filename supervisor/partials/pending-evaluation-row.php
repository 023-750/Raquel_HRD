<?php
/**
 * @var array $row
 * @var string $rowLayout full|nested
 */
$has_score = supervisorPendingHasScore($row);
$score = $has_score ? (float) $row['total_score'] : null;
$score_width = $has_score ? max(0, min(100, ($score / 4) * 100)) : 0;
$days_pending = (int) ($row['days_pending'] ?? 0);
$attention_flags = supervisorPendingAttentionFlags($row);
$is_low_score = in_array('Low score', $attention_flags, true);
$is_overdue = $days_pending >= 7;
$is_missing_score = in_array('No score', $attention_flags, true);
$row_class = trim(($is_low_score ? 'pending-low-score ' : '') . ($is_overdue ? 'pending-overdue ' : '') . ($is_missing_score ? 'pending-missing-score' : ''));
$age_label = $days_pending === 0 ? 'Today' : $days_pending . ' day' . ($days_pending === 1 ? '' : 's');
$age_class = $is_overdue ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle';
$avatar_url = getEmployeeAvatar($row['profile_picture'] ?? '');
$score_label = $has_score ? number_format($score, 2) . ' / 4' : 'No score';
$is_nested = $rowLayout === 'nested';

$perf_level = $row['performance_level'] ?? '';
if ($has_score && (empty($perf_level) || $perf_level === '0')) {
    $perf_level = getPerformanceLevel($score);
}
?>
<tr class="pending-eval-row <?php echo e($row_class); ?>">
    <?php if ($is_nested): ?>
        <td class="ps-3" data-label="Template">
            <div class="fw-semibold"><?php echo e($row['template_name'] ?? 'Template not assigned'); ?></div>
            <?php if (!empty($attention_flags)): ?>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <?php foreach ($attention_flags as $flag): ?>
                        <span class="attention-chip <?php echo $flag === 'Low score' ? 'is-danger' : ($flag === 'Overdue' ? 'is-warning' : 'is-muted'); ?>"><?php echo e($flag); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </td>
    <?php else: ?>
        <td class="ps-3 pending-primary" data-label="Employee">
            <div class="d-flex align-items-center gap-3">
                <div class="pending-avatar">
                    <img src="<?php echo e($avatar_url); ?>?v=<?php echo time(); ?>" alt="<?php echo e($row['employee_name']); ?> profile picture">
                </div>
                <div class="min-w-0">
                    <div class="fw-bold"><?php echo e($row['employee_name']); ?></div>
                    <div class="small company-id-text">Company ID: <span class="company-id-value"><?php echo e(getEmployeeDisplayId($row)); ?></span></div>
                    <div class="small text-muted"><?php echo e($row['template_name'] ?? 'Template not assigned'); ?></div>
                    <?php if (!empty($attention_flags)): ?>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <?php foreach ($attention_flags as $flag): ?>
                                <span class="attention-chip <?php echo $flag === 'Low score' ? 'is-danger' : ($flag === 'Overdue' ? 'is-warning' : 'is-muted'); ?>"><?php echo e($flag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </td>
        <td data-label="Department">
            <div class="fw-semibold"><?php echo e($row['job_title'] ?? 'N/A'); ?></div>
            <small class="text-muted"><?php echo e($row['department_name'] ?? 'Unassigned'); ?></small>
        </td>
    <?php endif; ?>
    <td data-label="Submitted By">
        <?php if ($row['eval_status'] === 'Pending HR Consolidation' && !empty($row['dept_manager_endorsed_by_name'])): ?>
            <div class="small fw-semibold text-success">
                <i class="fas fa-check-circle me-1"></i><?php echo e($row['dept_manager_endorsed_by_name']); ?>
            </div>
            <div class="small text-muted">Branch Manager Confirmed</div>
            <?php if (!empty($row['supervisor_altered_scores'])): ?>
                <span class="badge bg-warning text-dark">Scores Altered</span>
            <?php endif; ?>
        <?php elseif ($row['eval_status'] === 'Pending HR Consolidation' && !empty($row['supervisor_confirmed_by_name'])): ?>
            <div class="small fw-semibold text-success">
                <i class="fas fa-check-circle me-1"></i><?php echo e($row['supervisor_confirmed_by_name']); ?>
            </div>
            <div class="small text-muted">Supervisor Confirmed</div>
            <?php if (!empty($row['supervisor_altered_scores'])): ?>
                <span class="badge bg-warning text-dark">Scores Altered</span>
            <?php endif; ?>
        <?php else: ?>
            <div class="small fw-semibold"><?php echo e($row['submitted_by_name'] ?? 'Unknown Staff'); ?></div>
            <div class="small text-muted">Direct Submission</div>
        <?php endif; ?>
    </td>
    <td data-label="Submitted">
        <span class="pending-age-badge <?php echo e($age_class); ?>"><i class="fas fa-clock"></i><?php echo e($age_label); ?></span>
        <div><small class="text-muted"><?php echo formatDate($row['submitted_date']); ?></small></div>
    </td>
    <td data-label="Type & Progress">
        <span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo e($row['evaluation_type'] ?? 'Annual'); ?></span>
        <?php if ($row['eval_status'] === 'Pending HR Consolidation'): ?>
            <?php if (!empty($row['dept_manager_endorsed_by_name']) || !empty($row['dept_manager_endorsed_by'])): ?>
                <div class="pending-stage text-success">
                    <i class="fas fa-check me-1"></i>Branch Manager confirmed &rarr; <strong>Ready for HR Consolidation</strong>
                </div>
            <?php else: ?>
                <div class="pending-stage text-success">
                    <i class="fas fa-check me-1"></i>Supervisor confirmed &rarr; <strong>Ready for HR Consolidation</strong>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="pending-stage">Staff submitted &rarr; Supervisor review</div>
        <?php endif; ?>
    </td>
    <td data-label="Score & Alerts">
        <div class="score-meter">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="fw-bold"><?php echo e($score_label); ?></span>
                <span class="badge <?php echo getPerformanceBadgeClass($perf_level); ?> rounded-pill px-2" style="font-size:0.68rem;"><?php echo e($perf_level ?: ($has_score ? 'Unrated' : 'Unscored')); ?></span>
            </div>
            <?php if ($has_score): ?>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar <?php echo $is_low_score ? 'bg-danger' : 'bg-primary'; ?>" style="width: <?php echo $score_width; ?>%;"></div>
                </div>
            <?php else: ?>
                <div class="small text-muted mt-2">Score not calculated yet.</div>
            <?php endif; ?>
        </div>
    </td>
    <td class="text-end pe-3" data-label="Actions">
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo (int) $row['evaluation_id']; ?>">
            <i class="fas fa-clipboard-check me-1"></i>Review
        </button>
    </td>
</tr>
