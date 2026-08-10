<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Dosen</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <?php if (!empty($sync_message) && is_array($sync_message)): ?>
            <div class="alert alert-<?= htmlspecialchars($sync_message['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($sync_message['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Dosen</h3>
                </div>
                <div class="card-body">
                    <table id="table_dosen" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>NIDN</th>
                                <th>Kode Dosen</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th width="12%">Aksi</th>
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
                <h5 class="modal-title">Form Dosen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_dosen"/>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">NIDN</label>
                        <input name="nidn" placeholder="Kosongkan jika tidak ada" class="form-control" type="text">
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Kode Dosen</label>
                        <input name="kode_dosen" placeholder="Contoh: IT01" class="form-control" type="text" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Nama Lengkap (beserta gelar)</label>
                        <input name="nama" placeholder="Contoh: Dr. Budi, S.Kom, M.Kom" class="form-control" type="text" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Email</label>
                        <input name="email" placeholder="contoh@itpln.ac.id" class="form-control" type="email">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">No HP</label>
                        <input name="no_hp" placeholder="08..." class="form-control" type="text">
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

    table = $('#table_dosen').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('dosen/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 6 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });

    $('#table_dosen').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('dosen/delete')?>/"+id,
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

    $('#table_dosen').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        
        $.ajax({
            url : "<?= base_url('dosen/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_dosen"]').val(data.id_dosen);
                $('[name="nidn"]').val(data.nidn);
                $('[name="kode_dosen"]').val(data.kode_dosen);
                $('[name="nama"]').val(data.nama);
                $('[name="email"]').val(data.email);
                $('[name="no_hp"]').val(data.no_hp);
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Dosen'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });



    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('dosen/save')?>",
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
