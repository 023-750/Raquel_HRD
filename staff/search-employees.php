<?php
$page_title = 'Employee Search';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Fetch departments for the filter dropdown
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);

// Fetch branches for the filter dropdown
$branch_stmt = $conn->query("SELECT branch_id, branch_name FROM branches ORDER BY branch_name");
$branches = $branch_stmt ? $branch_stmt->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:0;color:rgba(255,255,255,.55);">HR Staff · Employee Directory</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-search me-2" style="color:#BD9414;"></i>Employee Search</h4>
        </div>
        <div style="color:rgba(255,255,255,.65);font-size:.8rem;">
            <i class="fas fa-filter me-1"></i>Live search and filters
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-users-viewfinder me-1"></i>Find employee records by company ID, name, branch, department, position, status, or employment type.</p>
</div>

<div class="staff-search-page">
<div class="row">
    <!-- Advanced Filters Sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="content-card sticky-top" style="top: 100px; z-index: 10;">
            <div class="card-header border-0 bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Search Filters</h6>
            </div>
            <div class="card-body pt-0">
                <form id="advancedSearchForm" class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">General Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Name or company ID..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Branch</label>
                        <select name="branch" class="form-select" id="filterBranch">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?php echo (int)$b['branch_id']; ?>"><?php echo e($b['branch_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Department</label>
                        <select name="department" class="form-select" id="filterDept">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo e($d['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Job Position</label>
                        <input type="text" name="position" class="form-control" placeholder="e.g. Accountant" id="filterPosition">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Employment Status</label>
                        <select name="status" class="form-select" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="OJT">OJT</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Project Based">Project Based</option>
                            <option value="Project-Based">Project-Based</option>
                            <option value="Regular">Regular</option>
                            <option value="Separated">Separated</option>
                            <option value="Trainee">Trainee</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Employment Type</label>
                        <select name="type" class="form-select" id="filterType">
                            <option value="">Any Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Search</button>
                        <button type="reset" id="resetBtn" class="btn btn-outline-secondary w-100 mt-2">Reset All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="col-lg-9">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-th-list me-2"></i>Search Results</h6>
                <div id="resultCount" class="badge bg-light text-muted fw-normal">0 results</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="searchResultsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th>Department & Position</th>
                                <th>Branch</th>
                                <th>Employment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="searchResultsBody">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search-plus fa-3x mb-3 opacity-25"></i>
                                        <p>Use the filters on the left to search the employee directory.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('advancedSearchForm');
    const resetBtn = document.getElementById('resetBtn');
    const resultsBody = document.getElementById('searchResultsBody');
    const resultCount = document.getElementById('resultCount');

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    resetBtn.addEventListener('click', function() {
        setTimeout(performSearch, 50);
    });

    // Live search on typing (debounced)
    let debounceTimer;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 400);
    });

    function performSearch() {
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData).toString();
        
        resultsBody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';

        fetch(`ajax/search-employees-handler.php?${params}`)
            .then(response => response.json())
            .then(data => {
                resultsBody.innerHTML = '';
                resultCount.textContent = `${data.length} result${data.length !== 1 ? 's' : ''}`;

                if (data.length === 0) {
                    resultsBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-user-slash fa-2x mb-2 d-block opacity-25"></i>No employees found matching those criteria.</td></tr>';
                    return;
                }

                data.forEach(emp => {
                    const avatarContent = `<img src="${emp.avatar_url}" class="rounded-circle" style="width:100%; height:100%; object-fit:cover;">`;

                    const row = `
                        <tr class="search-result-row">
                            <td data-label="Employee">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm me-3 bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center text-secondary fw-bold" style="width:40px; height:40px; min-width:40px;">
                                        ${avatarContent}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0 text-dark">${emp.full_name}</div>
                                        <small class="company-id-text">Company ID: <span class="company-id-value">${emp.employee_code || 'Unassigned'}</span></small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Department">
                                <div class="small fw-semibold text-dark">${emp.job_title}</div>
                                <div class="small text-muted">${emp.department_name || 'N/A'}</div>
                            </td>
                            <td data-label="Branch">
                                <div class="small text-dark">${emp.branch_name || 'N/A'}</div>
                            </td>
                            <td data-label="Employment">
                                <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 mb-1">${emp.employment_status}</div>
                                <div class="small text-muted">${emp.employment_type}</div>
                            </td>
                            <td data-label="Actions">
                                <a href="view-employee.php?id=${emp.employee_id}" class="btn btn-sm btn-outline-info" title="View Profile">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    `;
                    resultsBody.innerHTML += row;
                });
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                resultsBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>An error occurred while searching. Please try again.</td></tr>';
            });
    }
});
</script>

<style>
.user-avatar-sm { font-size: 0.8rem; }
.sticky-top { top: 1.5rem !important; }
.table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0; padding: 12px 16px; border-bottom: 2px solid #f1f1f1; }
.table td { padding: 16px; border-bottom: 1px solid #f8f9fa; }
.form-select, .form-control { border-radius: 8px; border: 1.5px solid #eee; padding: 0.6rem 0.8rem; }
.form-select:focus, .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); border-color: #0d6efd; }
.bg-primary-subtle { background-color: #e7f1ff; }
.badge { border-radius: 6px; font-weight: 600; font-size: 0.7rem; letter-spacing: 0; }
.search-result-row { transition: background-color 0.2s ease; }
.search-result-row:hover { background-color: #f8fafe; }

.staff-search-page .card-header {
    gap: 12px;
}

@media (max-width: 768px) {
    .staff-search-page .sticky-top {
        position: static !important;
        top: auto !important;
        z-index: auto !important;
    }

    .staff-search-page .col-lg-3 .content-card {
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(12, 32, 8, 0.06);
    }

    .staff-search-page .col-lg-9 > .content-card {
        background: transparent;
        border: 0;
        box-shadow: none;
    }

    .staff-search-page .col-lg-9 > .content-card > .card-header {
        align-items: stretch;
        background: #fff;
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(12, 32, 8, 0.06);
        flex-direction: column;
        margin-bottom: 12px;
        padding: 14px 16px;
    }

    .staff-search-page .col-lg-9 > .content-card > .card-body {
        background: transparent;
    }

    .staff-search-page .table-responsive {
        overflow-x: visible;
    }

    .staff-search-page #searchResultsTable {
        border-collapse: separate;
        border-spacing: 0;
    }

    .staff-search-page #searchResultsTable thead {
        display: none;
    }

    .staff-search-page #searchResultsTable,
    .staff-search-page #searchResultsTable tbody,
    .staff-search-page #searchResultsTable tr,
    .staff-search-page #searchResultsTable td {
        display: block;
        width: 100%;
    }

    .staff-search-page #searchResultsTable tbody tr {
        background: #fff;
        border: 1px solid rgba(41, 67, 6, 0.1);
        border-radius: 15px;
        box-shadow: 0 8px 24px rgba(12, 32, 8, 0.07);
        margin-bottom: 14px;
        overflow: hidden;
        padding: 8px 14px;
    }

    .staff-search-page #searchResultsTable tbody tr:hover {
        background: #fff;
    }

    .staff-search-page #searchResultsTable tbody td {
        align-items: center;
        border: 0;
        border-bottom: 1px solid #eef2e8;
        display: grid;
        gap: 10px;
        grid-template-columns: minmax(104px, 36%) minmax(0, 1fr);
        overflow-wrap: anywhere;
        padding: 10px 0;
        text-align: right;
    }

    .staff-search-page #searchResultsTable tbody td::before {
        color: var(--text-muted);
        content: attr(data-label);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.2;
        text-align: left;
        text-transform: uppercase;
    }

    .staff-search-page #searchResultsTable tbody td:first-child {
        border-bottom: 1px solid #e8eee3;
        display: flex;
        justify-content: flex-start;
        padding-bottom: 14px;
        text-align: left;
    }

    .staff-search-page #searchResultsTable tbody td:first-child::before,
    .staff-search-page #searchResultsTable tbody td:last-child::before,
    .staff-search-page #searchResultsTable tbody td[colspan]::before {
        content: none;
    }

    .staff-search-page #searchResultsTable tbody td:first-child .user-avatar-sm {
        height: 46px !important;
        width: 46px !important;
    }

    .staff-search-page #searchResultsTable tbody td:first-child .fw-bold {
        color: var(--primary-blue);
        font-size: 1rem;
    }

    .staff-search-page #searchResultsTable tbody td:last-child {
        border-bottom: 0;
        display: block;
        padding-top: 14px;
    }

    .staff-search-page #searchResultsTable tbody td:last-child .btn {
        align-items: center;
        display: inline-flex;
        justify-content: center;
        min-height: 38px;
        width: 100%;
    }

    .staff-search-page #searchResultsTable tbody td[colspan] {
        display: block;
        padding: 28px 16px;
        text-align: center;
    }
}

@media (max-width: 420px) {
    .staff-search-page #searchResultsTable tbody td {
        grid-template-columns: 1fr;
        gap: 4px;
        text-align: left;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
