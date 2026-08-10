<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Fakultas</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Fakultas</h3>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_fakultas()"><i class="fas fa-plus"></i> Tambah Data</button>
                </div>
                <div class="card-body">
                    <table id="table_fakultas" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Fakultas</th>
                                <th>Nama Fakultas</th>
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
                <h5 class="modal-title">Form Fakultas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_fakultas"/>
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Kode Fakultas</label>
                        <input name="kode_fakultas" placeholder="Contoh: FTIK" class="form-control" type="text" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Nama Fakultas</label>
                        <input name="nama_fakultas" placeholder="Contoh: Fakultas Telematika Energi" class="form-control" type="text" required>
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
    
    // DataTables Server-Side Processing
    table = $('#table_fakultas').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('fakultas/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 3 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });

    // Delete handling
    $('#table_fakultas').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('fakultas/delete')?>/"+id,
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

    // Edit handling
    $('#table_fakultas').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        
        $.ajax({
            url : "<?= base_url('fakultas/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_fakultas"]').val(data.id_fakultas);
                $('[name="kode_fakultas"]').val(data.kode_fakultas);
                $('[name="nama_fakultas"]').val(data.nama_fakultas);
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Fakultas'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    // Expose functions globally so they can be called by inline onclick
    window.add_fakultas = function() {
        $('#form')[0].reset();
        $('[name="id_fakultas"]').val('');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Fakultas');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('fakultas/save')?>",
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
