/* Global Scripts for SIPERKUL */

function initSelect2(context) {
    var $root = context ? $(context) : $(document);
    $root.find('select.select2, select.select-dosen').each(function () {
        var $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }

        var opts = {
            theme: 'bootstrap-5',
            width: '100%',
            minimumResultsForSearch: 0, // selalu tampilkan kotak search
            language: {
                noResults: function () { return 'Tidak ditemukan'; },
                searching: function () { return 'Mencari...'; }
            }
        };

        var $modal = $el.closest('.modal');
        if ($modal.length) {
            opts.dropdownParent = $modal;
        }

        $el.select2(opts);
    });
}

$(document).ready(function() {
    initSelect2();

    // Ini jalankan ulang Select2 di dalam modal setelah modal ditampilkan
    $(document).on('shown.bs.modal', '.modal', function () {
        initSelect2(this);
    });

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Fokus otomatis ke kotak pencarian Select2 saat dropdown dibuka
    $(document).on('select2:open', function () {
        var $searchField = $('.select2-container--open .select2-search--dropdown .select2-search__field');
        if ($searchField.length) {
            $searchField.focus();
        }
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
