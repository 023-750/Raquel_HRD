<?php
$page_title = 'Report Generation';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';
redirectWith(BASE_URL . '/manager/dashboard.php', 'info', 'Reports module is disabled.');
checkRole(['HR Manager']);
require_once '../includes/header.php';

$branches = $conn->query("SELECT branch_id, branch_name FROM branches WHERE is_active = 1 AND deleted_at IS NULL ORDER BY branch_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name");
?>

<div class="reports-module">
    <div class="page-hero fadeup">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Reports</div>
                <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-file-pdf me-2" style="color:#BD9414;"></i>Report Generation</h4>
                <p class="text-white-50 small mb-0 mt-2">Build and export HR reports using the employee, performance, and organizational data you need.</p>
            </div>
            <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
                <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value">3</div>
                            <div class="stat-label">Report Types</div>
                        </div>
                        <i class="fas fa-layer-group stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value">4</div>
                            <div class="stat-label">Filters</div>
                        </div>
                        <i class="fas fa-filter stat-icon" style="color:#BD9414;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value">CSV</div>
                            <div class="stat-label">Spreadsheet Export</div>
                        </div>
                        <i class="fas fa-file-csv stat-icon" style="color:#28a745;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value">PDF</div>
                            <div class="stat-label">Printable Export</div>
                        </div>
                        <i class="fas fa-file-pdf stat-icon" style="color:#dc3545;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="reportForm" class="fadeup-1">
        <input type="hidden" name="report_type" id="reportType" value="employee_masterlist">

        <div class="report-builder-grid mb-4">
            <section class="content-card report-picker-card">
                <div class="card-header">
                    <h5><i class="fas fa-layer-group me-2"></i>Report Type</h5>
                    <span class="report-step-badge">1</span>
                </div>
                <div class="card-body">
                    <div class="report-type-list" id="reportTypeCards">
                        <button type="button" class="report-type-card active" data-type="employee_masterlist" id="card-employee_masterlist">
                            <span class="rtc-icon"><i class="fas fa-address-book"></i></span>
                            <span class="rtc-info">
                                <strong>Employee Masterlist</strong>
                                <small>Roster, assignment, contact, and employment status details.</small>
                            </span>
                            <span class="rtc-check"><i class="fas fa-check"></i></span>
                        </button>

                        <button type="button" class="report-type-card" data-type="performance_summary" id="card-performance_summary">
                            <span class="rtc-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="rtc-info">
                                <strong>Performance Summary</strong>
                                <small>Approved evaluations, scores, levels, templates, and periods.</small>
                            </span>
                            <span class="rtc-check"><i class="fas fa-check"></i></span>
                        </button>

                    </div>
                </div>
            </section>

            <section class="content-card report-filter-card">
                <div class="card-header">
                    <h5><i class="fas fa-sliders-h me-2"></i>Filters</h5>
                    <span class="report-step-badge">2</span>
                </div>
                <div class="card-body">
                    <div class="report-selected-summary mb-3">
                        <div>
                            <span class="text-muted small d-block">Selected report</span>
                            <strong id="selectedReportName">Employee Masterlist</strong>
                        </div>
                        <span class="report-format-pill"><i class="fas fa-file-export me-1"></i>CSV / PDF</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch_id" id="filterBranch">
                                <option value="">All Branches</option>
                                <?php while ($b = $branches->fetch_assoc()): ?>
                                    <option value="<?php echo $b['branch_id']; ?>"><?php echo e($b['branch_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department" id="filterDepartment">
                                <option value="">All Departments</option>
                                <?php while ($d = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $d['department_id']; ?>"><?php echo e($d['department_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-lg-6" id="dateFromGroup">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" id="filterDateFrom">
                        </div>

                        <div class="col-lg-6" id="dateToGroup">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" id="filterDateTo">
                        </div>
                    </div>

                    <div class="report-filter-actions mt-4">
                        <button type="submit" class="btn btn-primary" id="btnGeneratePreview">
                            <i class="fas fa-search me-1"></i>Generate Preview
                        </button>
                        <button class="btn btn-outline-secondary" type="button" id="btnResetFilters">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </form>

    <div class="report-action-bar mb-3" id="reportActionBar" style="display:none;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="report-result-badge">
                    <i class="fas fa-table me-1"></i><span id="rowCount">0</span> records
                </span>
                <span class="report-scope-badge" id="reportScopeText">All branches and departments</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" id="btnExportCSV" type="button">
                    <i class="fas fa-file-csv me-1"></i>CSV
                </button>
                <button class="btn btn-danger btn-sm" id="btnExportPDF" type="button">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </button>
            </div>
        </div>
    </div>

    <div class="content-card" id="previewCard" style="display:none;">
        <div class="card-header">
            <h5><i class="fas fa-eye me-2"></i>Preview</h5>
            <span class="badge bg-info" id="reportTypeBadge">Employee Masterlist</span>
        </div>
        <div class="card-body p-0">
            <div id="reportPreviewArea"></div>
        </div>
    </div>

    <div class="report-loading-overlay" id="loadingOverlay" style="display:none;">
        <div class="report-spinner">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-3 mb-0 text-muted">Generating report...</p>
        </div>
    </div>

    <div class="content-card report-empty-state" id="emptyState">
        <div class="card-body">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <h5>Ready to generate</h5>
                <p class="mb-0 text-muted">Choose a report, apply optional filters, then preview it before exporting.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE = '<?php echo BASE_URL; ?>';
    const reportTypeCards = document.querySelectorAll('.report-type-card');
    const reportTypeInput = document.getElementById('reportType');
    const reportForm = document.getElementById('reportForm');
    const previewCard = document.getElementById('previewCard');
    const previewArea = document.getElementById('reportPreviewArea');
    const actionBar = document.getElementById('reportActionBar');
    const emptyState = document.getElementById('emptyState');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const reportTypeBadge = document.getElementById('reportTypeBadge');
    const selectedReportName = document.getElementById('selectedReportName');
    const reportScopeText = document.getElementById('reportScopeText');
    const rowCountEl = document.getElementById('rowCount');
    const dateFromGroup = document.getElementById('dateFromGroup');
    const dateToGroup = document.getElementById('dateToGroup');
    const filterBranch = document.getElementById('filterBranch');
    const filterDepartment = document.getElementById('filterDepartment');

    const typeLabels = {
        employee_masterlist: 'Employee Masterlist',
        performance_summary: 'Performance Summary'
    };

    function setDateFieldsEnabled(enabled) {
        [dateFromGroup, dateToGroup].forEach(group => {
            const input = group.querySelector('input');
            input.disabled = !enabled;
            if (!enabled) {
                input.value = '';
            }
            group.classList.toggle('is-disabled', !enabled);
        });
    }

    function updateSelectedReport(type) {
        reportTypeInput.value = type;
        selectedReportName.textContent = typeLabels[type] || type;
        reportTypeBadge.textContent = typeLabels[type] || type;
        setDateFieldsEnabled(true);
    }

    function updateScopeText() {
        const scopes = [];
        if (filterBranch.value) {
            scopes.push(filterBranch.options[filterBranch.selectedIndex].text);
        }
        if (filterDepartment.value) {
            scopes.push(filterDepartment.options[filterDepartment.selectedIndex].text);
        }
        reportScopeText.textContent = scopes.length ? scopes.join(' / ') : 'All branches and departments';
    }

    reportTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            reportTypeCards.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            updateSelectedReport(this.dataset.type);
        });
    });

    updateSelectedReport('employee_masterlist');

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        emptyState.style.display = 'none';
        loadingOverlay.style.display = 'flex';
        previewCard.style.display = 'none';
        actionBar.style.display = 'none';

        fetch(BASE + '/manager/ajax/generate-report.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            loadingOverlay.style.display = 'none';
            if (data.success) {
                previewArea.innerHTML = data.html;
                previewCard.style.display = 'block';
                actionBar.style.display = 'block';
                rowCountEl.textContent = data.count || 0;
                updateScopeText();
                previewCard.style.animation = 'fadeSlideUp 0.4s ease';
                actionBar.style.animation = 'fadeSlideUp 0.3s ease';
                return;
            }

            previewArea.innerHTML = '<div class="report-message-state text-muted"><i class="fas fa-exclamation-triangle"></i><span>' + (data.message || 'No results found.') + '</span></div>';
            previewCard.style.display = 'block';
        })
        .catch(() => {
            loadingOverlay.style.display = 'none';
            previewArea.innerHTML = '<div class="report-message-state text-danger"><i class="fas fa-times-circle"></i><span>An error occurred while generating the report.</span></div>';
            previewCard.style.display = 'block';
        });
    });

    document.getElementById('btnExportCSV').addEventListener('click', function() {
        exportReport('csv');
    });

    document.getElementById('btnExportPDF').addEventListener('click', function() {
        exportReport('pdf');
    });

    function exportReport(exportType) {
        const formData = new FormData(reportForm);
        formData.append('export_type', exportType);
        window.location.href = BASE + '/manager/export-report.php?' + new URLSearchParams(formData).toString();
    }

    document.getElementById('btnResetFilters').addEventListener('click', function() {
        reportForm.reset();
        reportTypeCards.forEach(item => item.classList.remove('active'));
        document.getElementById('card-employee_masterlist').classList.add('active');
        updateSelectedReport('employee_masterlist');
        previewCard.style.display = 'none';
        actionBar.style.display = 'none';
        emptyState.style.display = 'block';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
