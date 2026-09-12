/* LeadForge AI - Main Application JS */

// Toastr defaults
if (typeof toastr !== 'undefined') {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 4000,
        extendedTimeOut: 2000,
    };
}

// Show flash messages as toastr on DOM load
document.addEventListener('DOMContentLoaded', function () {
    const success = document.getElementById('flash-success');
    const error = document.getElementById('flash-error');
    if (success && typeof toastr !== 'undefined') toastr.success(success.dataset.msg);
    if (error && typeof toastr !== 'undefined') toastr.error(error.dataset.msg);
});

// Global SweetAlert2 delete confirmation
function confirmDelete(btn) {
    const form = btn.closest('form');
    const msg = form ? (form.dataset.confirm || 'Are you sure?') : 'Are you sure?';
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirm',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    } else {
        if (confirm(msg)) {
            form.submit();
        }
    }
}

// HTML entity decoding helper
function decodeHtmlEntities(str) {
    if (!str) return '';
    const txt = document.createElement('textarea');
    let prev = '';
    let current = String(str);
    while (current !== prev && (current.includes('&') || current.includes('&#'))) {
        prev = current;
        txt.innerHTML = current;
        current = txt.value;
    }
    return current;
}
