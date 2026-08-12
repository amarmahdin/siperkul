<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Mata Kuliah</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Mata Kuliah</h3>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_mk()"><i class="fas fa-plus"></i> Tambah Data</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($sync_message)): ?>
                        <div class="alert alert-<?= $sync_message['type'] ?> alert-dismissible fade show" role="alert">
                            <?= $sync_message['text'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <table id="table_mk" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode MK</th>
                                <th>Nama MK</th>
                                <th>SKS</th>
                                <th>Semester</th>
                                <th>Jenis</th>
                                <th>Prodi</th>
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
                <h5 class="modal-title">Form Mata Kuliah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_mk"/>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Program Studi</label>
                        <select name="id_prodi" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <?php foreach($prodi as $p): ?>
                                <option value="<?= $p->id_prodi ?>"><?= $p->nama_prodi ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Kode MK</label>
                        <input name="kode_mk" placeholder="Contoh: IT1234" class="form-control" type="text" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Nama MK</label>
                        <input name="nama_mk" placeholder="Contoh: Pemrograman Web" class="form-control" type="text" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">SKS</label>
                                <input name="sks" placeholder="Contoh: 3" class="form-control" type="number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Semester</label>
                                <input name="semester" placeholder="Contoh: 5" class="form-control" type="number" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label mb-1">Jenis MK</label>
                        <select name="jenis" class="form-control select2" style="width: 100%;" required>
                            <option value="Wajib">Wajib</option>
                            <option value="Pilihan">Pilihan</option>
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
    
    // Select2 diinisialisasi global via assets/js/app.js (dengan search)

    table = $('#table_mk').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('mata_kuliah/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 7 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });

    $('#table_mk').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('mata_kuliah/delete')?>/"+id,
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

    $('#table_mk').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        $('.select2').val('').trigger('change');
        
        $.ajax({
            url : "<?= base_url('mata_kuliah/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_mk"]').val(data.id_mk);
                $('[name="id_prodi"]').val(data.id_prodi).trigger('change');
                $('[name="kode_mk"]').val(data.kode_mk);
                $('[name="nama_mk"]').val(data.nama_mk);
                $('[name="sks"]').val(data.sks);
                $('[name="semester"]').val(data.semester);
                $('[name="jenis"]').val(data.jenis).trigger('change');
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Mata Kuliah'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    window.add_mk = function() {
        $('#form')[0].reset();
        $('[name="id_mk"]').val('');
        $('.select2').val('').trigger('change');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Mata Kuliah');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('mata_kuliah/save')?>",
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
