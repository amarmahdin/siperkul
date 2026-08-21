<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Data Ruangan</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0">Daftar Ruangan</h3>
                    <div class="d-flex align-items-center ml-auto mt-2 mt-md-0" style="gap: 8px;">
                        <label class="mb-0 mr-1 text-muted" for="filter_gedung">Filter Kampus/Gedung</label>
                        <select id="filter_gedung" class="form-control form-control-sm" style="min-width: 220px;">
                            <option value="">Semua</option>
                            <?php foreach ($gedung as $g): ?>
                                <option value="<?= (int) $g->id_gedung ?>"><?= htmlspecialchars($g->nama_gedung) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php /* <button class="btn btn-primary btn-sm" onclick="add_ruangan()"><i class="fas fa-plus"></i> Tambah Data</button> */ ?>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table_ruangan" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Ruangan</th>
                                <th>Nama Ruangan</th>
                                <th>Gedung</th>
                                <th>Lokasi</th>
                                <th>Lantai</th>
                                <th>Kapasitas (K)</th>
                                <th>Kapasitas (U)</th>
                                <th>Status</th>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title">Form Ruangan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_ruangan"/>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Kode Ruangan</label>
                                <input name="kode_ruangan" placeholder="Contoh: R-101" class="form-control" type="text" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Nama Ruangan</label>
                                <input name="nama_ruangan" placeholder="Contoh: Ruang Kuliah 101" class="form-control" type="text" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Gedung</label>
                                <select name="id_gedung" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Gedung --</option>
                                    <?php foreach ($gedung as $g): ?>
                                        <option value="<?= $g->id_gedung ?>"><?= htmlspecialchars($g->nama_gedung) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Lantai</label>
                                <input name="lantai" placeholder="Contoh: 1" class="form-control" type="number" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Nomor Ruang</label>
                                <input name="nomor_ruang" placeholder="Contoh: 101" class="form-control" type="text" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Kapasitas Kuliah</label>
                                <input name="kapasitas_kuliah" placeholder="Contoh: 40" class="form-control" type="number" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Kapasitas Ujian</label>
                                <input name="kapasitas_ujian" placeholder="Contoh: 20" class="form-control" type="number" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Status</label>
                                <select name="status" class="form-control select2" style="width: 100%;" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Non-Aktif">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
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

    table = $('#table_ruangan').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('ruangan/get_data')?>",
            "type": "POST",
            "data": function (d) {
                d.id_gedung = $('#filter_gedung').val();
            }
        },
        "columnDefs": [
            { "targets": [ 0, 9 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });

    $('#filter_gedung').on('change', function () {
        table.ajax.reload();
    });

    $('#table_ruangan').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('ruangan/delete')?>/"+id,
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

    $('#table_ruangan').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset();
        $('.select2').val('').trigger('change');

        $.ajax({
            url : "<?= base_url('ruangan/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_ruangan"]').val(data.id_ruangan);
                $('[name="kode_ruangan"]').val(data.kode_ruangan);
                $('[name="nama_ruangan"]').val(data.nama_ruangan);
                $('[name="id_gedung"]').val(data.id_gedung).trigger('change');
                $('[name="lantai"]').val(data.lantai);
                $('[name="nomor_ruang"]').val(data.nomor_ruang);
                $('[name="kapasitas_kuliah"]').val(data.kapasitas_kuliah);
                $('[name="kapasitas_ujian"]').val(data.kapasitas_ujian);
                $('[name="status"]').val(data.status).trigger('change');

                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Ruangan');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    window.add_ruangan = function() {
        $('#form')[0].reset();
        $('[name="id_ruangan"]').val('');
        $('.select2').val('').trigger('change');
        $('[name="status"]').val('Aktif').trigger('change');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Ruangan');
    };

    window.save = function() {
        $('#btnSave').text('Menyimpan...');
        $('#btnSave').attr('disabled',true);

        $.ajax({
            url : "<?= base_url('ruangan/save')?>",
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
