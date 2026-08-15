/**
 * hr-department-mobile.js
 * Raquel Pawnshop HRIS — HR Department Exclusive Mobile Interactions
 * Applies to: HR Manager, HR Supervisor, HR Staff, Admin roles
 *
 * Sections:
 *   1. Bootstrap & Body Setup
 *   2. HR Bottom Navigation (Active State, More Dropdown)
 *   3. Table-to-Card Transformation
 *   4. Mobile Search Bar & Filter Bottom Sheet
 *   5. Mobile Action Sheet (Approve/Reject/Return)
 *   6. Chart.js Mobile Canvas Resizer
 *   7. Mobile Card Accordion (Expand/Collapse Details)
 *   8. Utilities & Init
 */

(function () {
    'use strict';

    /* ============================================================
       1. BOOTSTRAP & BODY SETUP
       Adds `hr-mobile-active` to <body> on mobile viewports so
       all mobile-scoped CSS rules activate. Removed on resize up.
    ============================================================ */
    const MOBILE_BP  = 768;
    const TABLET_BP  = 992;

    function applyMobileClass() {
        if (window.innerWidth < TABLET_BP) {
            document.body.classList.add('hr-mobile-active');
        } else {
            document.body.classList.remove('hr-mobile-active');
        }
    }

    let _resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(_resizeTimer);
        _resizeTimer = setTimeout(applyMobileClass, 80);
    });


    /* ============================================================
       2. HR BOTTOM NAVIGATION
       - Sets `.active` on the nav item matching the current page.
       - Manages the "More" dropdown open/close.
    ============================================================ */

    /**
     * Highlight the correct bottom nav item based on the current URL path.
     */
    function initHRMobileNav() {
        const navItems = document.querySelectorAll('.hr-bottom-nav .hr-nav-item[data-page]');
        const currentPath = window.location.pathname;

        navItems.forEach(function (item) {
            const pages = (item.dataset.page || '').split(',').map(function (p) { return p.trim(); });
            const isActive = pages.some(function (p) { return currentPath.endsWith(p); });
            if (isActive) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    /**
     * Toggle the "More" quick-menu dropdown in the bottom nav.
     */
    function initHRMoreMenu() {
        const moreBtn  = document.getElementById('hrNavMoreBtn');
        const moreMenu = document.getElementById('hrNavMoreMenu');
        if (!moreBtn || !moreMenu) return;

        moreBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            moreMenu.classList.toggle('show');
            moreBtn.classList.toggle('active');
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!moreMenu.contains(e.target) && e.target !== moreBtn) {
                moreMenu.classList.remove('show');
                moreBtn.classList.remove('active');
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                moreMenu.classList.remove('show');
                moreBtn.classList.remove('active');
            }
        });
    }


    /* ============================================================
       3. TABLE-TO-CARD TRANSFORMATION
       On viewports < MOBILE_BP, data tables that have the class
       `hr-data-table` are hidden and sibling `.hr-card-view`
       containers (if already in the DOM) are revealed.

       For tables WITHOUT a pre-built `.hr-card-view` sibling,
       cards are generated automatically from <thead>/<tbody>.
    ============================================================ */

    /**
     * Build a single card element from a <tr> row and its column headers.
     * @param {HTMLTableRowElement} row
     * @param {string[]} headers
     * @param {Object} options - { nameCol, subCol, statusCol, actionCol }
     * @returns {HTMLElement}
     */
    function buildCardFromRow(row, headers, options) {
        const cells = row.querySelectorAll('td');
        if (!cells.length) return null;

        const card = document.createElement('div');
        card.className = 'hr-mobile-card';

        // --- Card Body (avatar + main info) ---
        const body = document.createElement('div');
        body.className = 'hr-mobile-card-body';

        // Avatar or initials
        const avatarImgEl = row.querySelector('img.avatar, img.employee-avatar, td:first-child img');
        const avatarWrap = document.createElement('div');

        if (avatarImgEl) {
            const img = document.createElement('img');
            img.src = avatarImgEl.src;
            img.alt = 'Avatar';
            img.className = 'hr-card-avatar';
            avatarWrap.appendChild(img);
        } else {
            const nameText = cells[options.nameCol] ? cells[options.nameCol].textContent.trim() : '?';
            const initials = nameText.split(' ').slice(0, 2).map(function (w) { return w[0] || ''; }).join('').toUpperCase();
            const initialsEl = document.createElement('div');
            initialsEl.className = 'hr-card-avatar-initials';
            initialsEl.textContent = initials || '?';
            avatarWrap.appendChild(initialsEl);
        }

        body.appendChild(avatarWrap);

        // Info block
        const info = document.createElement('div');
        info.className = 'hr-card-info';

        // Name
        const nameEl = document.createElement('div');
        nameEl.className = 'hr-card-name';
        nameEl.textContent = cells[options.nameCol]
            ? cells[options.nameCol].textContent.trim()
            : '—';
        info.appendChild(nameEl);

        // Sub-label (job title / email / etc.)
        if (options.subCol !== null && cells[options.subCol]) {
            const subEl = document.createElement('div');
            subEl.className = 'hr-card-sub';
            subEl.textContent = cells[options.subCol].textContent.trim();
            info.appendChild(subEl);
        }

        // Status / tag pills
        if (options.statusCol !== null && cells[options.statusCol]) {
            const statusText = cells[options.statusCol].textContent.trim();
            const tagsEl = document.createElement('div');
            tagsEl.className = 'hr-card-tags';

            // Clone any existing badge/pill HTML from the status cell
            const badgeEl = cells[options.statusCol].querySelector('.badge, .status-badge, span[class*="badge"]');
            if (badgeEl) {
                const cloned = badgeEl.cloneNode(true);
                cloned.style.fontSize = '0.68rem';
                tagsEl.appendChild(cloned);
            } else if (statusText) {
                const pill = document.createElement('span');
                pill.className = 'hr-tag ' + resolveStatusTag(statusText);
                pill.textContent = statusText;
                tagsEl.appendChild(pill);
            }

            info.appendChild(tagsEl);
        }

        body.appendChild(info);
        card.appendChild(body);

        // --- Card Detail rows (all non-action, non-name columns) ---
        const detailSection = document.createElement('div');
        detailSection.className = 'hr-card-detail';

        let hasDetail = false;
        cells.forEach(function (cell, i) {
            if (i === options.nameCol || i === options.subCol || i === options.actionCol) return;
            const label = headers[i] || '';
            if (!label || label === '#' || label === '') return;

            const cellText = cell.textContent.trim();
            if (!cellText || cellText === '—' || cellText === '-') return;

            hasDetail = true;
            const detailRow = document.createElement('div');
            detailRow.className = 'hr-card-detail-row';
            detailRow.innerHTML =
                '<span class="hr-card-detail-label">' + escapeHtml(label) + '</span>' +
                '<span class="hr-card-detail-value">' + escapeHtml(cellText) + '</span>';
            detailSection.appendChild(detailRow);
        });

        // --- Action Buttons ---
        const actionsSection = document.createElement('div');
        actionsSection.className = 'hr-card-actions';

        if (options.actionCol !== null && cells[options.actionCol]) {
            const actionLinks = cells[options.actionCol].querySelectorAll('a, button');
            actionLinks.forEach(function (link) {
                const btn = document.createElement('a');
                btn.className = 'hr-card-btn ' + resolveActionBtnClass(link);
                btn.href = link.href || '#';

                // Forward data-* attributes
                Array.from(link.attributes).forEach(function (attr) {
                    if (attr.name.startsWith('data-') || attr.name === 'onclick') {
                        btn.setAttribute(attr.name, attr.value);
                    }
                });

                // Icon + label
                const iconEl = link.querySelector('i');
                if (iconEl) {
                    const i = document.createElement('i');
                    i.className = iconEl.className;
                    btn.appendChild(i);
                }
                const btnText = link.textContent.trim() || link.title || link.getAttribute('aria-label') || '';
                if (btnText) {
                    btn.appendChild(document.createTextNode(' ' + btnText));
                }

                actionsSection.appendChild(btn);
            });
        }

        if (hasDetail) {
            card.appendChild(detailSection);

            const toggle = document.createElement('button');
            toggle.className = 'hr-card-toggle';
            toggle.type = 'button';
            toggle.innerHTML = 'View Details <i class="fas fa-chevron-down toggle-chevron"></i>';
            toggle.addEventListener('click', function () {
                detailSection.classList.toggle('open');
                toggle.classList.toggle('expanded');
                toggle.querySelector('i').style.transform = detailSection.classList.contains('open') ? 'rotate(180deg)' : '';
            });
            card.appendChild(toggle);
        }

        if (actionsSection.children.length > 0) {
            card.appendChild(actionsSection);
        }

        return card;
    }

    /** Map status text to a CSS tag colour class */
    function resolveStatusTag(text) {
        const t = text.toLowerCase();
        if (t.includes('active') || t.includes('approved') || t.includes('completed')) return 'hr-tag-green';
        if (t.includes('pending')) return 'hr-tag-amber';
        if (t.includes('reject') || t.includes('inactive') || t.includes('terminated')) return 'hr-tag-red';
        if (t.includes('return')) return 'hr-tag-gold';
        if (t.includes('draft')) return 'hr-tag-slate';
        return 'hr-tag-slate';
    }

    /** Map an action link's appearance to a card button class */
    function resolveActionBtnClass(link) {
        const cls = (link.className || '').toLowerCase();
        const txt = (link.textContent || '').toLowerCase().trim();
        if (cls.includes('danger') || cls.includes('delete') || txt.includes('delete') || txt.includes('deactivate')) return 'hr-card-btn hr-card-btn-danger';
        if (cls.includes('warning') || txt.includes('return') || txt.includes('pending')) return 'hr-card-btn hr-card-btn-gold';
        if (cls.includes('success') || txt.includes('approve') || txt.includes('activate')) return 'hr-card-btn hr-card-btn-primary';
        if (txt.includes('view') || txt.includes('profile') || txt.includes('review')) return 'hr-card-btn hr-card-btn-outline';
        return 'hr-card-btn hr-card-btn-outline';
    }

    /** Basic HTML escape */
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /**
     * Transform all data tables with `data-hr-mobile="true"` or the
     * class `.hr-data-table` into mobile card views.
     */
    function initHRMobileTables() {
        if (window.innerWidth >= MOBILE_BP) return;

        const tables = document.querySelectorAll(
            'table.hr-data-table, table[data-hr-mobile="true"]'
        );

        tables.forEach(function (table) {
            if (table.dataset.hrMobileInit === 'true') return;
            table.dataset.hrMobileInit = 'true';

            // Find or create the card view container
            let cardView = table.closest('.hr-table-wrapper')
                ? table.closest('.hr-table-wrapper').querySelector('.hr-card-view')
                : null;

            if (!cardView) {
                cardView = document.createElement('div');
                cardView.className = 'hr-card-view';
                table.parentNode.insertBefore(cardView, table.nextSibling);
            }

            // Infer column mapping from data attributes or smart defaults
            const nameCol   = parseInt(table.dataset.nameCol   || '0', 10);
            const subCol    = table.dataset.subCol    !== undefined ? parseInt(table.dataset.subCol, 10)    : 1;
            const statusCol = table.dataset.statusCol !== undefined ? parseInt(table.dataset.statusCol, 10) : null;
            const actionCol = table.dataset.actionCol !== undefined ? parseInt(table.dataset.actionCol, 10) : null;

            // Collect headers
            const headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
                return th.textContent.replace(/\s+/g, ' ').trim();
            });

            // Auto-detect action column (last col if header is blank/"Action"/"Actions")
            let resolvedActionCol = actionCol;
            if (resolvedActionCol === null && headers.length > 0) {
                const lastLabel = headers[headers.length - 1].toLowerCase();
                if (lastLabel === '' || lastLabel === 'action' || lastLabel === 'actions') {
                    resolvedActionCol = headers.length - 1;
                }
            }

            // Build card list
            const list = document.createElement('div');
            list.className = 'hr-card-list';

            const rows = table.querySelectorAll('tbody tr');
            if (!rows.length) {
                const empty = document.createElement('div');
                empty.className = 'hr-mobile-empty';
                empty.innerHTML = '<i class="fas fa-inbox hr-empty-icon"></i><p>No records found.</p>';
                cardView.appendChild(empty);
                return;
            }

            rows.forEach(function (row) {
                if (row.querySelector('td[colspan]')) {
                    // Skip "no records" colspan rows — render empty state instead
                    const msg = row.textContent.trim();
                    if (msg) {
                        const empty = document.createElement('div');
                        empty.className = 'hr-mobile-empty';
                        empty.innerHTML = '<i class="fas fa-inbox hr-empty-icon"></i><p>' + escapeHtml(msg) + '</p>';
                        list.appendChild(empty);
                    }
                    return;
                }

                const card = buildCardFromRow(row, headers, {
                    nameCol:   nameCol,
                    subCol:    subCol,
                    statusCol: statusCol,
                    actionCol: resolvedActionCol
                });

                if (card) list.appendChild(card);
            });

            cardView.innerHTML = '';
            cardView.appendChild(list);
        });
    }


    /* ============================================================
       4. MOBILE SEARCH BAR & FILTER BOTTOM SHEET
    ============================================================ */

    /**
     * Wire up the HR mobile search input (`.hr-search-input`) to
     * filter both table rows and mobile card items simultaneously.
     */
    function initHRMobileSearch() {
        const searchInputs = document.querySelectorAll('.hr-search-input[data-hr-search]');

        searchInputs.forEach(function (input) {
            const targetId = input.dataset.hrSearch;

            input.addEventListener('input', function () {
                const filter = input.value.toLowerCase().trim();

                // Filter table rows
                if (targetId) {
                    const table = document.getElementById(targetId);
                    if (table) {
                        table.querySelectorAll('tbody tr').forEach(function (row) {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(filter) ? '' : 'none';
                        });
                    }
                }

                // Filter mobile cards (any visible hr-mobile-card in the same section)
                const section = input.closest('section, .content-card, .card, .hr-table-wrapper, main') || document.body;
                section.querySelectorAll('.hr-mobile-card').forEach(function (card) {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        });
    }

    /**
     * Wire up the HR Filter Bottom Sheet drawer.
     * Opens on `.hr-filter-btn` click, closes on backdrop click / close btn.
     */
    function initHRFilterSheet() {
        const openBtns   = document.querySelectorAll('[data-hr-filter-open]');
        const backdrop   = document.getElementById('hrFilterBackdrop');
        const sheet      = document.getElementById('hrFilterSheet');
        const clearBtn   = document.getElementById('hrFilterClear');
        const applyBtn   = document.getElementById('hrFilterApply');

        if (!backdrop || !sheet) return;

        function openSheet() {
            backdrop.classList.add('open');
            sheet.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeSheet() {
            backdrop.classList.remove('open');
            sheet.classList.remove('open');
            document.body.style.overflow = '';
        }

        openBtns.forEach(function (btn) {
            btn.addEventListener('click', openSheet);
        });

        backdrop.addEventListener('click', closeSheet);

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                sheet.querySelectorAll('select, input').forEach(function (el) {
                    el.value = el.type === 'checkbox' ? false : '';
                    el.checked = false;
                });
                // Update active filter count on trigger button
                updateFilterCount(0);
            });
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', function () {
                // Count active filters (non-empty/non-default values)
                const activeCount = Array.from(sheet.querySelectorAll('select, input')).filter(function (el) {
                    if (el.type === 'checkbox') return el.checked;
                    return el.value && el.value !== 'all' && el.value !== '';
                }).length;

                updateFilterCount(activeCount);
                closeSheet();
                // NOTE: page-level hrFilterApply listener handles actual filtering (syncMobileFiltersToMain / renderTable)
            });
        }

        function updateFilterCount(n) {
            document.querySelectorAll('.hr-filter-count').forEach(function (el) {
                el.textContent = n;
                el.style.display = n > 0 ? '' : 'none';
            });
            document.querySelectorAll('.hr-filter-btn').forEach(function (btn) {
                btn.classList.toggle('active', n > 0);
            });
        }

        // Touch drag-to-dismiss
        let startY = 0;
        sheet.addEventListener('touchstart', function (e) {
            startY = e.touches[0].clientY;
        }, { passive: true });

        sheet.addEventListener('touchmove', function (e) {
            const deltaY = e.touches[0].clientY - startY;
            if (deltaY > 60) closeSheet();
        }, { passive: true });
    }


    /* ============================================================
       5. MOBILE ACTION SHEET (Approve / Reject / Return)
       Triggered by buttons with `data-hr-action-sheet` attribute.
    ============================================================ */
    function initHRActionSheet() {
        const backdrop  = document.getElementById('hrActionBackdrop');
        const sheet     = document.getElementById('hrActionSheet');
        if (!backdrop || !sheet) return;

        function openAction(triggerBtn) {
            // Populate sheet with data from trigger button attributes
            const nameEl = sheet.querySelector('[data-bind="employee-name"]');
            if (nameEl && triggerBtn.dataset.employeeName) {
                nameEl.textContent = triggerBtn.dataset.employeeName;
            }
            const subtitleEl = sheet.querySelector('[data-bind="eval-period"]');
            if (subtitleEl && triggerBtn.dataset.evalPeriod) {
                subtitleEl.textContent = triggerBtn.dataset.evalPeriod;
            }
            const selfScore = sheet.querySelector('[data-bind="self-score"]');
            if (selfScore && triggerBtn.dataset.selfScore) {
                selfScore.textContent = triggerBtn.dataset.selfScore;
            }
            const supScore = sheet.querySelector('[data-bind="supervisor-score"]');
            if (supScore && triggerBtn.dataset.supervisorScore) {
                supScore.textContent = triggerBtn.dataset.supervisorScore;
            }
            const overallScore = sheet.querySelector('[data-bind="overall-score"]');
            if (overallScore && triggerBtn.dataset.overallScore) {
                overallScore.textContent = triggerBtn.dataset.overallScore;
            }

            // Wire up approve / reject / return buttons inside sheet
            const approveBtn = sheet.querySelector('[data-action="approve"]');
            const rejectBtn  = sheet.querySelector('[data-action="reject"]');
            const returnBtn  = sheet.querySelector('[data-action="return"]');
            const evalId     = triggerBtn.dataset.evalId || '';
            const remarksEl  = sheet.querySelector('textarea[name="manager_remarks"], textarea[name="remarks"]');

            function doAction(action) {
                const remarks = remarksEl ? remarksEl.value.trim() : '';
                const form = document.getElementById('hrActionForm');
                if (form) {
                    form.querySelector('[name="action"]').value = action;
                    form.querySelector('[name="evaluation_id"]').value = evalId;
                    if (form.querySelector('[name="remarks"]') && remarks) {
                        form.querySelector('[name="remarks"]').value = remarks;
                    }
                    form.submit();
                } else {
                    // Fallback: find a standard action form on the page
                    const fallbackForm = document.querySelector('form[data-eval-action]');
                    if (fallbackForm) {
                        fallbackForm.querySelector('[name="action"]').value = action;
                        fallbackForm.querySelector('[name="evaluation_id"]').value = evalId;
                        fallbackForm.submit();
                    }
                }
                closeAction();
            }

            if (approveBtn) approveBtn.onclick = function () { doAction('approve'); };
            if (rejectBtn)  rejectBtn.onclick  = function () { doAction('reject'); };
            if (returnBtn)  returnBtn.onclick   = function () { doAction('return'); };

            backdrop.classList.add('open');
            sheet.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeAction() {
            backdrop.classList.remove('open');
            sheet.classList.remove('open');
            document.body.style.overflow = '';
        }

        // Wire all trigger buttons
        document.querySelectorAll('[data-hr-action-sheet]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openAction(btn);
            });
        });

        backdrop.addEventListener('click', closeAction);

        const closeBtn = sheet.querySelector('[data-hr-action-close]');
        if (closeBtn) closeBtn.addEventListener('click', closeAction);

        // Drag to dismiss
        let startY = 0;
        sheet.addEventListener('touchstart', function (e) { startY = e.touches[0].clientY; }, { passive: true });
        sheet.addEventListener('touchmove', function (e) {
            if (e.touches[0].clientY - startY > 60) closeAction();
        }, { passive: true });
    }


    /* ============================================================
       6. CHART.JS MOBILE CANVAS RESIZER
       Adjusts Chart.js chart height dynamically on mobile to
       preserve aspect ratio without distortion.
    ============================================================ */
    function initHRMobileCharts() {
        if (window.innerWidth >= MOBILE_BP) return;
        if (typeof Chart === 'undefined') return;

        // Give Chart.js a moment to render, then fix heights
        setTimeout(function () {
            document.querySelectorAll('canvas').forEach(function (canvas) {
                if (!canvas.closest('.chart-container') && !canvas.id) return;

                const parent = canvas.parentElement;
                if (!parent) return;

                // Set responsive container height
                parent.style.height   = '220px';
                parent.style.maxHeight = '220px';
                canvas.style.maxHeight = '220px';

                // Resize the Chart.js instance if accessible
                const chartId = canvas.id;
                if (chartId && Chart.getChart) {
                    const chartInstance = Chart.getChart(chartId);
                    if (chartInstance) {
                        chartInstance.options.maintainAspectRatio = false;
                        chartInstance.resize();
                    }
                }
            });
        }, 200);
    }


    /* ============================================================
       7. MOBILE CARD ACCORDION (Expand/Collapse Details)
       Handles cards rendered server-side (not by JS transformer).
    ============================================================ */
    function initHRCardAccordions() {
        document.querySelectorAll('.hr-card-toggle').forEach(function (toggle) {
            if (toggle.dataset.hrAccordionInit) return;
            toggle.dataset.hrAccordionInit = 'true';

            const card   = toggle.closest('.hr-mobile-card');
            const detail = card ? card.querySelector('.hr-card-detail') : null;
            if (!detail) return;

            toggle.addEventListener('click', function () {
                const isOpen = detail.classList.toggle('open');
                toggle.classList.toggle('expanded', isOpen);
            });
        });
    }


    /* ============================================================
       8. BACK TO TOP BUTTON (Inline & Floating)
    ============================================================ */
    function initHRBackToTop() {
        if (window.innerWidth >= 992) return;

        // 1. Create floating back to top button if not exists
        let floatBtn = document.getElementById('hrFloatingTopBtn');
        if (!floatBtn) {
            floatBtn = document.createElement('button');
            floatBtn.id = 'hrFloatingTopBtn';
            floatBtn.className = 'hr-floating-top-btn d-lg-none';
            floatBtn.type = 'button';
            floatBtn.setAttribute('aria-label', 'Back to top');
            floatBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            document.body.appendChild(floatBtn);
        }

        window.addEventListener('scroll', function () {
            if (window.scrollY > 250) {
                floatBtn.classList.add('visible');
            } else {
                floatBtn.classList.remove('visible');
            }
        }, { passive: true });

        floatBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // 2. Wire up inline back to top buttons (.hr-back-to-top-btn)
        document.querySelectorAll('.hr-back-to-top-btn').forEach(function (btn) {
            if (btn.dataset.hrBackTopInit) return;
            btn.dataset.hrBackTopInit = 'true';
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }


    /* ============================================================
       9. UTILITIES & INITIALISATION
    ============================================================ */

    /**
     * Run all HR mobile initialisers once the DOM is ready.
     */
    function initAll() {
        applyMobileClass();
        initHRMobileNav();
        initHRMoreMenu();
        initHRMobileTables();
        initHRMobileSearch();
        initHRFilterSheet();
        initHRActionSheet();
        initHRMobileCharts();
        initHRCardAccordions();
        initHRBackToTop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Re-run table & chart init after PJAX-style navigation
    document.addEventListener('pjax:success', function () {
        initAll();
    });

    // Expose public API for manual re-init from other scripts
    window.HRMobile = {
        init:             initAll,
        initTables:       initHRMobileTables,
        initFilterSheet:  initHRFilterSheet,
        initActionSheet:  initHRActionSheet,
        initCharts:       initHRMobileCharts,
        initCardAccordions: initHRCardAccordions,
    };

}());
