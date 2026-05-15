// ============================================
// Raquel Pawnshop HRIS - Main JavaScript
// ============================================

/**
 * Toggle sidebar visibility/collapse
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    const isMobile = window.matchMedia('(max-width: 992px)').matches;

    if (isMobile) {
        // Mobile behavior: slide-in/slide-out
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
        
        // Toggle body scroll lock
        if (sidebar.classList.contains('show')) {
            body.classList.add('mobile-sidebar-active');
            document.documentElement.classList.add('mobile-sidebar-active');
        } else {
            body.classList.remove('mobile-sidebar-active');
            document.documentElement.classList.remove('mobile-sidebar-active');
        }
    } else {
        // Desktop behavior: collapse to icon-only
        document.documentElement.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed);
    }
}

/**
 * Mark all notifications as read (AJAX)
 */
function markAllRead() {
    const baseUrl = (typeof window !== 'undefined' && window.APP_BASE_URL) ? window.APP_BASE_URL : '';
    const context = (typeof window !== 'undefined' && window.NOTIF_CONTEXT) ? window.NOTIF_CONTEXT : 'hr';
    const fd = new FormData();
    fd.append('action', 'mark_all_read');
    fd.append('context', context);

    fetch(baseUrl + '/includes/ajax/notification-action.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove badge
                const badge = document.querySelector('.notification-badge');
                if (badge) badge.style.display = 'none';
                // Remove unread styling from dropdown items
                document.querySelectorAll('.notification-item.unread').forEach(el => {
                    el.classList.remove('unread');
                });
                // If we are on a notifications page, reload to refresh stats/list
                if (window.location.pathname.includes('notifications.php')) {
                    window.location.reload();
                }
            }
        })
        .catch(err => console.error('Error marking notifications:', err));
}

/**
 * Client-side table search filter
 */
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

/**
 * Confirm delete action
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Initialize components that need re-binding after PJAX load
 */
function initDynamicComponents() {
    positionFlashToasts();
    initFlashToasts();

    // 1. Close alert after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        if (alert.dataset.init) return;
        alert.dataset.init = 'true';
        setTimeout(function () {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    initAdminMobileTables();
}

/**
 * Move flash toasts into a page-level anchor when one is available.
 */
function positionFlashToasts() {
    const useInlineAnchor = window.matchMedia('(min-width: 768px)').matches;
    const anchor = useInlineAnchor ? document.querySelector('[data-flash-toast-anchor]') : null;

    document.querySelectorAll('[data-flash-toast-anchor].flash-toast-anchor-active').forEach(function (activeAnchor) {
        activeAnchor.classList.remove('flash-toast-anchor-active');
    });

    document.querySelectorAll('.flash-toast-container').forEach(function (container) {
        if (!container.flashToastPlaceholder && container.parentNode) {
            const placeholder = document.createComment('flash-toast-original-position');
            container.parentNode.insertBefore(placeholder, container);
            container.flashToastPlaceholder = placeholder;
        }

        const currentAnchor = container.closest('[data-flash-toast-anchor]');
        container.classList.toggle('flash-toast-container-mobile-sticky', !useInlineAnchor);

        if (anchor) {
            anchor.appendChild(container);
            anchor.classList.add('flash-toast-anchor-active');
            container.classList.add('flash-toast-container-inline');
            return;
        }

        if (currentAnchor) {
            currentAnchor.classList.remove('flash-toast-anchor-active');
        }

        container.classList.remove('flash-toast-container-inline');

        if (!useInlineAnchor) {
            document.body.appendChild(container);
            return;
        }

        if (container.flashToastPlaceholder && container.flashToastPlaceholder.parentNode) {
            container.flashToastPlaceholder.parentNode.insertBefore(container, container.flashToastPlaceholder.nextSibling);
        }
    });
}

/**
 * Show shared flash messages as popup toasts.
 */
function initFlashToasts() {
    document.querySelectorAll('.flash-toast').forEach(function (toastEl) {
        if (toastEl.dataset.init) return;
        toastEl.dataset.init = 'true';

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            toastEl.addEventListener('show.bs.toast', function () {
                toastEl.classList.remove('flash-toast-leaving');
                toastEl.classList.add('flash-toast-entering');
            });

            toastEl.addEventListener('shown.bs.toast', function () {
                toastEl.classList.remove('flash-toast-entering');
            });

            toastEl.addEventListener('hide.bs.toast', function () {
                toastEl.classList.remove('flash-toast-entering');
                toastEl.classList.add('flash-toast-leaving');
            });

            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.classList.remove('flash-toast-leaving');
                const container = toastEl.closest('.flash-toast-container');
                const anchor = container ? container.closest('[data-flash-toast-anchor]') : null;
                if (anchor) anchor.classList.remove('flash-toast-anchor-active');
                if (container) container.remove();
            });

            bootstrap.Toast.getOrCreateInstance(toastEl).show();
            return;
        }

        toastEl.classList.add('show', 'flash-toast-entering');
        setTimeout(function () {
            toastEl.classList.remove('flash-toast-entering');
            toastEl.classList.add('flash-toast-leaving');
            setTimeout(function () {
                toastEl.classList.remove('show', 'flash-toast-leaving');
                const container = toastEl.closest('.flash-toast-container');
                const anchor = container ? container.closest('[data-flash-toast-anchor]') : null;
                if (anchor) anchor.classList.remove('flash-toast-anchor-active');
                if (container) container.remove();
            }, 700);
        }, 3000);
    });
}

/**
 * Add readable labels to admin table cells for the mobile card layout.
 */
function initAdminMobileTables() {
    if (!document.body.classList.contains('admin-area')) return;

    document.querySelectorAll('.table-responsive table').forEach(function (table) {
        if (table.dataset.mobileLabels === 'true') return;

        const headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
            return th.textContent.replace(/\s+/g, ' ').trim();
        });

        if (!headers.length) return;

        table.querySelectorAll('tbody tr').forEach(function (row) {
            const cells = Array.from(row.children).filter(function (cell) {
                return cell.tagName.toLowerCase() === 'td';
            });

            if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                cells[0].classList.add('mobile-empty-cell');
                return;
            }

            cells.forEach(function (cell, index) {
                if (!cell.dataset.label && headers[index]) {
                    cell.dataset.label = headers[index];
                }
            });
        });

        table.dataset.mobileLabels = 'true';
    });
}

document.addEventListener('DOMContentLoaded', initDynamicComponents);
window.addEventListener('resize', positionFlashToasts);

/**
 * Common Main JS
 */

document.addEventListener("DOMContentLoaded", function () {
    // Prevent FOUC from sidebar collapsed class
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
        const sb = document.getElementById('sidebar');
        if (sb) {
            sb.classList.add('collapsed');
        }
    }

    // Global Fix: Move all modals to body to prevent Bootstrap z-index layer issues (black shadow bug)
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    // Clear Employee Draft if success feedback exists
    const successFeedback = document.querySelector('.alert-success, .flash-toast-success .toast-body');
    if (successFeedback) {
        const text = successFeedback.innerText.toLowerCase();
        if (text.includes('successfully') || text.includes('added') || text.includes('saved')) {
            localStorage.removeItem('hris_add_employee_draft');
        }
    }
});

// Export for PJAX use
if (typeof window !== 'undefined') {
    window.initDynamicComponents = initDynamicComponents;
    window.positionFlashToasts = positionFlashToasts;
    window.initFlashToasts = initFlashToasts;
    window.initAdminMobileTables = initAdminMobileTables;
}

// Back to Top Logic
window.addEventListener('scroll', function() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;
    if (window.pageYOffset > 300) {
        btn.style.display = 'flex';
    } else {
        btn.style.display = 'none';
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * View full-size profile picture or any image in a shared modal
 */
function viewFullImage(src, name) {
    const modalEl = document.getElementById('imageModal');
    if (!modalEl) return;
    
    const modal = new bootstrap.Modal(modalEl);
    const fullImage = document.getElementById('fullImage');
    const fullImageName = document.getElementById('fullImageName');
    
    if (fullImage) fullImage.src = src;
    if (fullImageName) fullImageName.textContent = name || '';
    
    modal.show();
}
