document.addEventListener('DOMContentLoaded', function () {
    var department = document.getElementById('governance-department');
    var user = document.getElementById('governance-user');
    if (!department || !user) return;

    function filterAuthorizedUsers() {
        var selectedDepartment = department.value;
        user.value = '';

        var options = Array.prototype.slice.call(user.options);
        var currentHeader = null;
        var currentHeaderHasVisible = false;

        options.forEach(function (option) {
            // Skip the placeholder "Select user" option
            if (!option.value && !option.disabled) return;

            if (option.disabled) {
                // This is a rank group header — finalize the previous header visibility
                if (currentHeader) {
                    currentHeader.hidden = !currentHeaderHasVisible;
                }
                // Start tracking a new group
                currentHeader = option;
                currentHeaderHasVisible = false;
            } else {
                // Regular user option
                var isVisible = selectedDepartment === '0' || option.dataset.departmentId === selectedDepartment;
                option.hidden = !isVisible;
                if (isVisible) currentHeaderHasVisible = true;
            }
        });

        // Finalize the last group
        if (currentHeader) {
            currentHeader.hidden = !currentHeaderHasVisible;
        }
    }

    department.addEventListener('change', filterAuthorizedUsers);
});
