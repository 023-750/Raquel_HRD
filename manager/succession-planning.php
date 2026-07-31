<?php
$page_title = 'Succession Planning';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch departments, branches, and active job titles for filter dropdowns
$depts_res    = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name ASC");
$branches_res = $conn->query("SELECT branch_id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name ASC");
$positions_res= $conn->query("SELECT DISTINCT job_title FROM employees WHERE job_title IS NOT NULL AND job_title != '' AND is_active = 1 ORDER BY job_title ASC");
?>

<div class="page-hero fadeup mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Career Movements</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-user-tie me-2" style="color:#BD9414;"></i>Career Movements & Succession Planning</h4>
            <p class="text-white-50 small mb-0 mt-1">Identify top performing candidates for key positions, promotions, and internal transfers based on historical performance evaluations.</p>
        </div>
        <div>
            <a href="career-movements.php" class="btn btn-outline-light btn-sm"><i class="fas fa-exchange-alt me-1"></i> View Movement Logs</a>
        </div>
    </div>

    <!-- Rating Scale Legend -->
    <div class="d-flex flex-wrap gap-2 pt-2 border-top border-white-10">
        <span class="badge" style="background:rgba(40,167,69,0.2); border:1px solid rgba(40,167,69,0.4); color:#96e0a8;">
            <i class="fas fa-star me-1"></i> Outstanding (3.60 – 4.00)
        </span>
        <span class="badge" style="background:rgba(23,162,184,0.2); border:1px solid rgba(23,162,184,0.4); color:#9de0ec;">
            <i class="fas fa-thumbs-up me-1"></i> Exceeds Expectations (2.60 – 3.59)
        </span>
        <span class="badge" style="background:rgba(255,193,7,0.2); border:1px solid rgba(255,193,7,0.4); color:#ffe699;">
            <i class="fas fa-check-circle me-1"></i> Meets Expectations (2.00 – 2.59)
        </span>
        <span class="badge" style="background:rgba(220,53,69,0.2); border:1px solid rgba(220,53,69,0.4); color:#f5a3ab;">
            <i class="fas fa-exclamation-triangle me-1"></i> Needs Improvement (1.00 – 1.99)
        </span>
    </div>
</div>

<!-- Controls & Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form id="successionFilterForm" class="row g-3 align-items-end">
            <!-- Scope Filter -->
            <div class="col-md-3">
                <label class="form-label fw-bold text-dark small mb-1"><i class="fas fa-globe me-1 text-primary"></i> Evaluation Scope</label>
                <select class="form-select form-select-sm rounded-3" id="filterScope" name="scope">
                    <option value="company" selected>Company-Wide (All Employees)</option>
                    <option value="branch">By Branch</option>
                    <option value="department">By Department</option>
                </select>
            </div>

            <!-- Branch Select (shown if branch scope) -->
            <div class="col-md-3" id="branchFilterContainer" style="display:none;">
                <label class="form-label fw-bold text-dark small mb-1"><i class="fas fa-building me-1 text-success"></i> Branch</label>
                <select class="form-select form-select-sm rounded-3" id="filterBranch" name="branch_id">
                    <option value="0">Select Branch...</option>
                    <?php while ($b = $branches_res->fetch_assoc()): ?>
                        <option value="<?php echo $b['branch_id']; ?>"><?php echo e($b['branch_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Department Select (shown if department scope) -->
            <div class="col-md-3" id="deptFilterContainer" style="display:none;">
                <label class="form-label fw-bold text-dark small mb-1"><i class="fas fa-sitemap me-1 text-info"></i> Department</label>
                <select class="form-select form-select-sm rounded-3" id="filterDept" name="dept_id">
                    <option value="0">Select Department...</option>
                    <?php while ($d = $depts_res->fetch_assoc()): ?>
                        <option value="<?php echo $d['department_id']; ?>"><?php echo e($d['department_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Target Position -->
            <div class="col-md-4">
                <label class="form-label fw-bold text-dark small mb-1"><i class="fas fa-briefcase me-1 text-warning"></i> Target Position / Role</label>
                <select class="form-select form-select-sm rounded-3" id="filterPosition" name="position">
                    <option value="">All Qualified Positions</option>
                    <?php while ($p = $positions_res->fetch_assoc()): ?>
                        <option value="<?php echo e($p['job_title']); ?>"><?php echo e($p['job_title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2 text-end">
                <button type="button" id="btnSearchCandidates" class="btn btn-primary btn-sm rounded-3 w-100 fw-bold">
                    <i class="fas fa-search me-1"></i> Analyze Candidates
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Section -->
<style>
.candidate-row:hover {
    background-color: rgba(41, 67, 6, 0.04) !important;
    transition: background-color 0.2s ease;
}
.candidate-row.table-active {
    background-color: rgba(41, 67, 6, 0.08) !important;
    border-left: 4px solid #294306 !important;
}
</style>

<div class="row g-4 mb-4">
    <!-- Candidates Leaderboard Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-award me-2" style="color:#BD9414;"></i>Top 10 Succession Candidates</h6>
                <span class="badge bg-light text-secondary border px-2 py-1 small" id="resultCountBadge">10 Candidates Ranked</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="candidatesTable">
                        <thead class="bg-light text-muted small uppercase">
                            <tr>
                                <th class="ps-3" style="width: 50px;">Rank</th>
                                <th>Candidate</th>
                                <th>Current Position & Dept</th>
                                <th class="text-center">Yrs Service</th>
                                <th class="text-center">Avg Rating</th>
                                <th class="text-center">Trend</th>
                                <th class="pe-3 text-end">Recommendation</th>
                            </tr>
                        </thead>
                        <tbody id="candidatesTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Analyzing performance evaluation records...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Breakdown & Recommendation Panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 px-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-check me-2 text-success"></i>Candidate Details</h6>
            </div>
            <div class="card-body" id="candidateDetailCard">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-hand-pointer fa-2x mb-2 opacity-50"></i>
                    <p class="small mb-0">Select any candidate from the table to inspect their evaluation summary and initiate career movement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeSelect = document.getElementById('filterScope');
    const branchContainer = document.getElementById('branchFilterContainer');
    const deptContainer = document.getElementById('deptFilterContainer');
    const btnSearch = document.getElementById('btnSearchCandidates');
    const tableBody = document.getElementById('candidatesTableBody');
    const detailCard = document.getElementById('candidateDetailCard');

    function getInitials(name) {
        if (!name) return 'U';
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        return parts[0].substr(0, 2).toUpperCase();
    }

    function getAvatarHtml(c, size = 34) {
        const name = c ? (c.full_name || '') : '';
        const initials = getInitials(name);
        const fontSize = size <= 40 ? '0.78rem' : '1.2rem';
        const avatarUrl = c ? (c.avatar_url || '') : '';
        
        if (avatarUrl && !avatarUrl.includes('/logo/logo.png')) {
            return `<img src="${avatarUrl}" class="rounded-circle flex-shrink-0" style="width:${size}px;height:${size}px;object-fit:cover;" onerror="this.onerror=null; this.outerHTML='<div class=\\'rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white flex-shrink-0\\' style=\\'width:${size}px;height:${size}px;font-size:${fontSize};\\'>${initials}</div>';">`;
        }
        return `<div class="rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white flex-shrink-0" style="width:${size}px;height:${size}px;font-size:${fontSize};">${initials}</div>`;
    }

    scopeSelect.addEventListener('change', function() {
        if (this.value === 'branch') {
            branchContainer.style.display = 'block';
            deptContainer.style.display = 'none';
        } else if (this.value === 'department') {
            branchContainer.style.display = 'none';
            deptContainer.style.display = 'block';
        } else {
            branchContainer.style.display = 'none';
            deptContainer.style.display = 'none';
        }
    });

    function loadCandidates() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Calculating evaluation ratings and succession scores...
                </td>
            </tr>`;

        const scope = scopeSelect.value;
        const branchId = document.getElementById('filterBranch').value;
        const deptId = document.getElementById('filterDept').value;
        const position = document.getElementById('filterPosition').value;

        const url = `ajax/get-succession-candidates.php?scope=${scope}&branch_id=${branchId}&dept_id=${deptId}&position=${encodeURIComponent(position)}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.candidates || data.candidates.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0">No eligible employees found with approved evaluations for the selected filter.</p>
                            </td>
                        </tr>`;
                    detailCard.innerHTML = `<div class="text-center py-5 text-muted"><p class="small">No candidates available.</p></div>`;
                    return;
                }

                window.candidatesList = data.candidates;
                let html = '';
                data.candidates.forEach((c, idx) => {
                    const rank = idx + 1;
                    let medalBadge = `<span class="fw-bold text-secondary">#${rank}</span>`;
                    if (rank === 1) medalBadge = `<i class="fas fa-crown text-warning fa-lg" title="Top 1 Candidate"></i>`;
                    else if (rank === 2) medalBadge = `<i class="fas fa-medal text-secondary fa-lg" title="Rank 2"></i>`;
                    else if (rank === 3) medalBadge = `<i class="fas fa-medal fa-lg" style="color:#cd7f32;" title="Rank 3"></i>`;

                    let trendIcon = '<span class="badge bg-light text-dark"><i class="fas fa-minus text-muted me-1"></i> Stable</span>';
                    if (c.trend === 'improving') trendIcon = '<span class="badge bg-success-subtle text-success"><i class="fas fa-arrow-up me-1"></i> Improving</span>';
                    else if (c.trend === 'declining') trendIcon = '<span class="badge bg-danger-subtle text-danger"><i class="fas fa-arrow-down me-1"></i> Declining</span>';

                    let badgeClass = 'bg-primary';
                    if (c.badge === 'Highly Recommended') badgeClass = 'bg-success';
                    else if (c.badge === 'Recommended') badgeClass = 'bg-info text-dark';
                    else if (c.badge === 'Qualified') badgeClass = 'bg-warning text-dark';

                    const avatarHtml = getAvatarHtml(c, 34);

                    html += `
                        <tr class="candidate-row" data-index="${idx}" style="cursor:pointer;">
                            <td class="ps-3 text-center">${medalBadge}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    ${avatarHtml}
                                    <div>
                                        <div class="fw-bold text-dark mb-0 small">${escapeHtml(c.full_name)}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">${escapeHtml(c.branch_name || 'Main')}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">${escapeHtml(c.job_title || 'N/A')}</div>
                                <div class="text-muted" style="font-size:0.75rem;">${escapeHtml(c.department_name || 'N/A')}</div>
                            </td>
                            <td class="text-center small">${c.years_of_service} yrs</td>
                            <td class="text-center">
                                <span class="badge bg-success px-2 py-1" style="font-size:0.85rem;">${c.avg_score.toFixed(2)}</span>
                                <div class="text-muted" style="font-size:0.68rem;">(${c.eval_count} eval${c.eval_count > 1 ? 's' : ''})</div>
                            </td>
                            <td class="text-center">${trendIcon}</td>
                            <td class="pe-3 text-end">
                                <span class="badge ${badgeClass} px-2 py-1">${c.badge}</span>
                            </td>
                        </tr>`;
                });

                tableBody.innerHTML = html;

                // Add row click listeners
                document.querySelectorAll('.candidate-row').forEach(row => {
                    row.addEventListener('click', function() {
                        document.querySelectorAll('.candidate-row').forEach(r => r.classList.remove('table-active'));
                        this.classList.add('table-active');
                        const index = parseInt(this.getAttribute('data-index'));
                        if (window.candidatesList && window.candidatesList[index]) {
                            renderCandidateDetails(window.candidatesList[index]);
                        }
                    });
                });

                // Auto select first candidate
                if (data.candidates.length > 0) {
                    const firstRow = document.querySelector('.candidate-row[data-index="0"]');
                    if (firstRow) firstRow.classList.add('table-active');
                    renderCandidateDetails(data.candidates[0]);
                }
            })
            .catch(err => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i> Error loading succession candidates.
                        </td>
                    </tr>`;
            });
    }

    window.renderCandidateDetails = function(c) {
        const detailAvatarHtml = getAvatarHtml(c, 65);
        detailCard.innerHTML = `
            <div class="text-center mb-3">
                <div class="d-flex justify-content-center mb-2">
                    ${detailAvatarHtml}
                </div>
                <h6 class="fw-bold mb-0">${escapeHtml(c.full_name)}</h6>
                <span class="badge bg-light text-dark border small mt-1">${escapeHtml(c.job_title || 'N/A')}</span>
            </div>

            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Department:</span>
                    <span class="fw-semibold small">${escapeHtml(c.department_name || 'N/A')}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Branch:</span>
                    <span class="fw-semibold small">${escapeHtml(c.branch_name || 'N/A')}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Years of Service:</span>
                    <span class="fw-semibold small">${c.years_of_service} Years</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Average Rating Score:</span>
                    <span class="fw-bold text-success small">${c.avg_score.toFixed(2)} / 4.00</span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted small">Performance Classification:</span>
                    <span class="fw-bold text-primary small">${c.performance_level}</span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="view-employee.php?id=${c.employee_id}" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="fas fa-user me-1"></i> View Full Employee Profile & Trend
                </a>
                <a href="career-movements.php?action=create&emp_id=${c.employee_id}" class="btn btn-success btn-sm rounded-3 fw-bold">
                    <i class="fas fa-level-up-alt me-1"></i> Initiate Promotion / Transfer
                </a>
            </div>`;
    };

    function escapeHtml(str) {
        return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    btnSearch.addEventListener('click', loadCandidates);
    loadCandidates();
});
</script>

<?php require_once '../includes/footer.php'; ?>
