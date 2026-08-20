document.addEventListener('DOMContentLoaded', function () {
    var department = document.getElementById('governance-department');
    var user = document.getElementById('governance-user');
    if (!department || !user) return;

    function filterAuthorizedUsers() {
        var selectedDepartment = department.value;
        user.value = '';
        Array.prototype.forEach.call(user.options, function (option) {
            if (!option.value) return;
            option.hidden = selectedDepartment !== '0' && option.dataset.departmentId !== selectedDepartment;
        });
    }

    department.addEventListener('change', filterAuthorizedUsers);
});
