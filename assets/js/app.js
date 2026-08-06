/* Global Scripts for SIPERKUL */

$(document).ready(function() {
    // Setup Global AJAX with CSRF if needed (assuming CI3 default cookie setup)
    // CodeIgniter automatically handles CSRF if config is set, but we might need it for AJAX
    /*
    var csrfName = 'csrf_test_name';
    var csrfHash = $('meta[name="csrf-token"]').attr('content');
    
    $.ajaxSetup({
        data: {
            [csrfName]: csrfHash
        }
    });
    */

    // Initialize Select2 globally
    if ($('.select2').length > 0) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// SweetAlert Global Functions
function showSuccess(title, message) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message
    });
}

function showWarning(title, message) {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message
    });
}

function confirmDelete(callback) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}
