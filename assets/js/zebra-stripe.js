function applyZebraStriping(tableSelector) {
    let visibleIndex = 0;
    const table = document.querySelector(tableSelector);
    if (!table) return;
    if (table.dataset.noStripe === 'true') return;
    table.querySelectorAll('tbody tr').forEach(row => {
        if (row.style.display === 'none') return;
        row.classList.remove('odd-row', 'even-row');
        row.classList.add(visibleIndex % 2 === 0 ? 'even-row' : 'odd-row');
        visibleIndex++;
    });
}

// Auto-apply to all .table elements on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table').forEach(table => {
        if (table.dataset.noStripe === 'true') return;
        if (table.id) {
            applyZebraStriping('#' + table.id);
        } else {
            // If the table doesn't have an ID, we can still apply striping directly to it
            let visibleIndex = 0;
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                row.classList.remove('odd-row', 'even-row');
                row.classList.add(visibleIndex % 2 === 0 ? 'even-row' : 'odd-row');
                visibleIndex++;
            });
        }
    });
});
