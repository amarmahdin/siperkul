<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Program Studi</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Program Studi</h3>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_prodi()"><i class="fas fa-plus"></i> Tambah Data</button>
                </div>
                <div class="card-body">
                    <table id="table_prodi" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Prodi</th>
                                <th>Nama Prodi</th>
                                <th>Fakultas</th>
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
                <h5 class="modal-title">Form Program Studi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_prodi"/>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Fakultas</label>
                        <select name="id_fakultas" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Fakultas --</option>
                            <?php foreach($fakultas as $f): ?>
                                <option value="<?= $f->id_fakultas ?>"><?= $f->nama_fakultas ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Kode Prodi</label>
                        <input name="kode_prodi" placeholder="Contoh: TI" class="form-control" type="text" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Nama Prodi</label>
                        <input name="nama_prodi" placeholder="Contoh: S1 Teknik Informatika" class="form-control" type="text" required>
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
    
    // Select2 diinisialisasi global via assets/js/app.js (dengan search)

    table = $('#table_prodi').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('prodi/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 4 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });

    $('#table_prodi').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('prodi/delete')?>/"+id,
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

    $('#table_prodi').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        $('.select2').val('').trigger('change');
        
        $.ajax({
            url : "<?= base_url('prodi/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_prodi"]').val(data.id_prodi);
                $('[name="id_fakultas"]').val(data.id_fakultas).trigger('change');
                $('[name="kode_prodi"]').val(data.kode_prodi);
                $('[name="nama_prodi"]').val(data.nama_prodi);
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Program Studi'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    window.add_prodi = function() {
        $('#form')[0].reset();
        $('[name="id_prodi"]').val('');
        $('.select2').val('').trigger('change');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Program Studi');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('prodi/save')?>",
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
