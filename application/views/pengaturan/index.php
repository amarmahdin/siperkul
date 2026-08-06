<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Sistem Settings</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Manajemen Tahun Akademik</h3>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_ta()"><i class="fas fa-plus"></i> Tambah TA Baru</button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Info!</h5>
                        Tahun Akademik yang berstatus <strong>Aktif</strong> akan digunakan sebagai referensi utama saat melakukan <i>input</i> Jadwal Kuliah oleh seluruh Operator. Hanya boleh ada 1 Tahun Akademik yang aktif secara bersamaan.
                    </div>
                    
                    <table id="table_ta" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tahun Akademik</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modal_form" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title">Form Tahun Akademik</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Tahun Akademik</label>
                        <input name="tahun_akademik" placeholder="Contoh: 2023/2024" class="form-control" type="text" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Semester</label>
                        <select name="semester" class="form-control select2" style="width: 100%;" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                            <option value="Pendek">Pendek</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var table;

    $('.select2').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modal_form')
    });

    table = $('#table_ta').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('pengaturan/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 4 ], "orderable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });

    $('#table_ta').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('pengaturan/delete')?>/"+id,
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    showSuccess('Sukses!', data.message);
                    table.ajax.reload(null,false);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showError('Error!', 'Gagal menghapus data.');
                }
            });
        });
    });

    $('#table_ta').on('click', '.btn-activate', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Aktifkan TA?',
            text: "Semua transaksi input jadwal selanjutnya akan menggunakan Tahun Akademik ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--primary-color)',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url : "<?= base_url('pengaturan/set_aktif')?>/"+id,
                    type: "POST",
                    dataType: "JSON",
                    success: function(data) {
                        showSuccess('Berhasil!', data.message);
                        table.ajax.reload(null,false);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        showError('Error!', 'Terjadi kesalahan sistem.');
                    }
                });
            }
        })
    });

    window.add_ta = function() {
        $('#form')[0].reset();
        $('.select2').val('Ganjil').trigger('change');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Tahun Akademik');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('pengaturan/save')?>",
            type: "POST",
            data: $('#form').serialize(),
            dataType: "JSON",
            success: function(data) {
                if(data.status === 'success') {
                    $('#modal_form').modal('hide');
                    showSuccess('Sukses!', data.message);
                    table.ajax.reload(null,false);
                } else {
                    showError('Gagal!', data.message);
                }
                $('#btnSave').text('Simpan');
                $('#btnSave').attr('disabled',false);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Terjadi kesalahan saat menyimpan.');
                $('#btnSave').text('Simpan');
                $('#btnSave').attr('disabled',false);
            }
        });
    };
});
</script>
