// Initialize sidebar toggle
window.addEventListener('DOMContentLoaded', event => {
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Initialize DataTables
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            perPage: 10,
            perPageSelect: [5, 10, 20, 50],
            columns: [
                { select: [5], sortable: false } // Assuming column with index 5 has action buttons
            ]
        });
    }

    // Initialize form validations
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Flash messages auto-hide
    const flashMessages = document.querySelectorAll('.alert-dismissible');
    flashMessages.forEach(function(flash) {
        setTimeout(function() {
            const closeButton = flash.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                flash.style.display = 'none';
            }
        }, 5000);
    });

    // Initialize Select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: "Silahkan pilih",
            allowClear: true
        });
    }
});