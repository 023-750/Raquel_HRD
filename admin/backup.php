<?php
$page_title = 'System Backup';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';

function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
    return round($bytes, 2) . ' ' . $units[$i];
}

// Handle Download
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $path = dirname(__DIR__) . '/backups/' . $file;
    if (file_exists($path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = dirname(__DIR__) . '/backups/' . $file;
    if (file_exists($path)) {
        unlink($path);
        logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Backup', 0, 'Deleted backup file: ' . $file);
        redirectWith(BASE_URL . '/admin/backup.php', 'success', 'Backup deleted successfully.');
    }
}

require_once '../includes/header.php';

// Get all backups
$backup_dir = dirname(__DIR__) . '/backups/';
$files = array_diff(scandir($backup_dir), array('.', '..', '.htaccess'));
$backups = [];
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        // Detect backup type from filename
        if (strpos($file, '_schema_') !== false) {
            $btype = 'schema';
        } elseif (strpos($file, '_data_') !== false) {
            $btype = 'data';
        } else {
            $btype = 'full';
        }
        $backups[] = [
            'name' => $file,
            'size' => filesize($backup_dir . $file),
            'date' => filemtime($backup_dir . $file),
            'type' => $btype,
        ];
    }
}

// Sort by date desc
usort($backups, function($a, $b) { return $b['date'] - $a['date']; });
?>

<div class="backup-module">
    <div class="page-hero fadeup">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">System Admin · Recovery Center</div>
                <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-database me-2" style="color:var(--primary-light);"></i>System Backup &amp; Recovery</h4>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light text-primary fw-semibold" id="btnFullBackup" title="Full: Table structures + all data">
                    <i class="fas fa-database me-2"></i>Full Backup
                </button>
                <button class="btn btn-outline-light fw-semibold" id="btnSchemaBackup" title="Schema: Table structures only (no data)">
                    <i class="fas fa-sitemap me-2"></i>Schema Only
                </button>
                <button class="btn btn-outline-light fw-semibold" id="btnDataBackup" title="Data: Records/values only (no table structures)">
                    <i class="fas fa-table me-2"></i>Data Only
                </button>
            </div>
        </div>
        <p class="text-white-50 small mb-0"><i class="fas fa-shield-alt me-1"></i>Manage database snapshots and system exports.</p>
    </div>

    <!-- Stats Info -->
    <div class="row g-3 mb-4 fadeup-1">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-file-archive"></i></div>
                <div class="stat-info">
                    <h3><?php echo count($backups); ?></h3>
                    <p>Stored Backups</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-hdd"></i></div>
                <div class="stat-info">
                    <?php $total_size = array_sum(array_column($backups, 'size')); ?>
                    <h3><?php echo formatSize($total_size); ?></h3>
                    <p>Total Backup Size</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-shield-alt"></i></div>
                <div class="stat-info">
                    <h3>Encrypted</h3>
                    <p>Storage Security</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="content-card fadeup-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-history me-2"></i>Backup History</h5>
            <span class="badge bg-info">Auto-cleanup: Not Active</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="backupTable">
                    <thead class="table-light">
                        <tr>
                            <th>Backup Filename</th>
                            <th>Type</th>
                            <th>Created Date</th>
                            <th>File Size</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                                    No backups found. Use the buttons above to create a backup.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $b):
                                if ($b['type'] === 'schema') {
                                    $type_badge = '<span class="badge" style="background:#6366f1"><i class="fas fa-sitemap me-1"></i>Schema Only</span>';
                                    $file_icon  = 'fas fa-file-alt';
                                    $icon_color = 'color:#6366f1';
                                } elseif ($b['type'] === 'data') {
                                    $type_badge = '<span class="badge" style="background:#0891b2"><i class="fas fa-table me-1"></i>Data Only</span>';
                                    $file_icon  = 'fas fa-file-alt';
                                    $icon_color = 'color:#0891b2';
                                } else {
                                    $type_badge = '<span class="badge bg-success"><i class="fas fa-database me-1"></i>Full Backup</span>';
                                    $file_icon  = 'fas fa-file-code';
                                    $icon_color = 'color:#16a34a';
                                }
                            ?>
                                <tr>
                                    <td><strong><i class="<?php echo $file_icon; ?> me-2" style="<?php echo $icon_color; ?>"></i><?php echo e($b['name']); ?></strong></td>
                                    <td><?php echo $type_badge; ?></td>
                                    <td><?php echo formatDateTime(date('Y-m-d H:i:s', $b['date'])); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo formatSize($b['size']); ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="?download=<?php echo urlencode($b['name']); ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDeleteBackup('<?php echo e($b['name']); ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="backupProgressModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h4 class="fw-bold" id="backupProgressLabel">Generating Backup...</h4>
                <p class="text-muted mb-0" id="backupProgressSub">Please wait while the system exports the database. This may take a few seconds.</p>
            </div>
        </div>
    </div>
</div>

<script>
const AJAX_URL = '<?php echo BASE_URL; ?>/includes/ajax/system-backup.php';

function runBackup(type, labelText) {
    document.getElementById('backupProgressLabel').textContent = 'Generating ' + labelText + '...';
    document.getElementById('backupProgressSub').textContent  = 'Please wait while the system exports the database. This may take a few seconds.';

    const modal = new bootstrap.Modal(document.getElementById('backupProgressModal'));
    modal.show();

    const body = new FormData();
    body.append('type', type);

    fetch(AJAX_URL, { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            modal.hide();
            if (data.success) {
                location.reload();
            } else {
                alert('Backup Error: ' + (data.error || 'Unknown error occurred.'));
            }
        })
        .catch(err => {
            modal.hide();
            alert('A system error occurred while generating the backup.');
            console.error(err);
        });
}

document.getElementById('btnFullBackup').addEventListener('click',   () => runBackup('full',   'Full Backup'));
document.getElementById('btnSchemaBackup').addEventListener('click', () => runBackup('schema', 'Schema Only Backup'));
document.getElementById('btnDataBackup').addEventListener('click',   () => runBackup('data',   'Data Only Backup'));

function confirmDeleteBackup(filename) {
    if (confirm(`Are you sure you want to delete backup "${filename}"? This cannot be undone.`)) {
        window.location.href = `?delete=${encodeURIComponent(filename)}`;
    }
}
</script>

<?php
require_once '../includes/footer.php';
?>
