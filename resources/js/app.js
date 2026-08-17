import '../css/app.css';

const checkboxes = document.querySelectorAll('.student-checkbox');
const selectAll = document.getElementById('selectAll');
const selectedCount = document.getElementById('selectedCount');
const editSelectedBtn = document.getElementById('editSelectedBtn');
const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
const bulkDeleteForm = document.getElementById('bulkDeleteForm');

// Only run Student List JavaScript if the required elements exist
if (
    checkboxes.length > 0 &&
    selectAll &&
    selectedCount &&
    editSelectedBtn &&
    deleteSelectedBtn &&
    bulkDeleteForm
) {
    // Update selection
    function updateSelection() {
        const selected = document.querySelectorAll(
            '.student-checkbox:checked'
        );

        const count = selected.length;

        if (count === 0) {
            selectedCount.classList.add('hidden');
            editSelectedBtn.classList.add('hidden');
            deleteSelectedBtn.classList.add('hidden');
        } else {
            selectedCount.classList.remove('hidden');
            editSelectedBtn.classList.remove('hidden');
            deleteSelectedBtn.classList.remove('hidden');

            selectedCount.textContent = `${count} selected`;
        }

        // Highlight selected rows
        checkboxes.forEach((checkbox) => {
            const row = checkbox.closest('.student-row');

            if (checkbox.checked) {
                row.classList.add('bg-blue-50');
            } else {
                row.classList.remove('bg-blue-50');
            }
        });

        // Update Select All
        selectAll.checked =
            count === checkboxes.length && count > 0;
    }

    // Individual checkbox
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateSelection);
    });

    // Select All
    selectAll.addEventListener('change', function () {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
        });

        updateSelection();
    });

    // Delete Selected
    deleteSelectedBtn.addEventListener('click', function () {
        const selected = document.querySelectorAll(
            '.student-checkbox:checked'
        );

        const count = selected.length;

        if (count === 0) {
            return;
        }

        const confirmed = confirm(
            `Are you sure you want to delete ${count} selected student(s)? This action cannot be undone.`
        );

        if (!confirmed) {
            return;
        }

        selected.forEach((checkbox) => {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'students[]';
            input.value = checkbox.value;

            bulkDeleteForm.appendChild(input);
        });

        bulkDeleteForm.submit();
    });

    // Initial state
    updateSelection();
}

// Individual Delete
window.deleteStudent = function (id) {
    const confirmed = confirm(
        'Are you sure you want to delete this student?'
    );

    if (!confirmed) {
        return;
    }

    const form = document.getElementById('individualDeleteForm');

    form.action = `/students/${id}`;
    form.submit();
};
const successMessage = document.getElementById('successMessage');

if (successMessage) {
    setTimeout(() => {
        successMessage.style.transition = 'opacity 0.5s ease';
        successMessage.style.opacity = '0';

        setTimeout(() => {
            successMessage.remove();
        }, 500);
    }, 3000);
}