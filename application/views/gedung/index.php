<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Gedung</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Gedung</h3>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_gedung()"><i class="fas fa-plus"></i> Tambah Data</button>
                </div>
                <div class="card-body">
                    <table id="table_gedung" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Gedung</th>
                                <th>Nama Gedung</th>
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
                <h5 class="modal-title">Form Gedung</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_gedung"/>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Kode Gedung</label>
                        <input name="kode_gedung" placeholder="Contoh: GDG-A" class="form-control" type="text" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Nama Gedung</label>
                        <input name="nama_gedung" placeholder="Contoh: Gedung A" class="form-control" type="text" required>
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

    table = $('#table_gedung').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('gedung/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 3 ], "orderable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });

    $('#table_gedung').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('gedung/delete')?>/"+id,
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

    $('#table_gedung').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        
        $.ajax({
            url : "<?= base_url('gedung/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_gedung"]').val(data.id_gedung);
                $('[name="kode_gedung"]').val(data.kode_gedung);
                $('[name="nama_gedung"]').val(data.nama_gedung);
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Gedung'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    window.add_gedung = function() {
        $('#form')[0].reset();
        $('[name="id_gedung"]').val('');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Gedung');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('gedung/save')?>",
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
</script
