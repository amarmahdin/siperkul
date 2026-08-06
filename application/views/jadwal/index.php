<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Jadwal Kuliah</h1>
                    <p class="text-muted">Tahun Akademik: <strong><?= $ta_aktif->tahun_akademik ?> (<?= $ta_aktif->semester ?>)</strong></p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Jadwal</h3>
                    <?php if(in_array($this->session->userdata('role'), ['Administrator', 'BAAK', 'Operator Prodi'])): ?>
                    <button class="btn btn-primary btn-sm ml-auto" onclick="add_jadwal()"><i class="fas fa-plus"></i> Tambah Jadwal</button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <table id="table_jadwal" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Prodi</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Dosen</th>
                                <th>Waktu</th>
                                <th>Ruangan</th>
                                <th>Peserta</th>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title">Form Jadwal Kuliah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" value="" name="id_jadwal"/>
                    
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6 border-right">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Informasi Akademik</h6>
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Program Studi</label>
                                <select name="id_prodi" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Prodi --</option>
                                    <?php foreach($prodi as $p): ?>
                                        <option value="<?= $p->id_prodi ?>"><?= $p->nama_prodi ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Mata Kuliah</label>
                                <select name="id_mk" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    <?php foreach($mata_kuliah as $mk): ?>
                                        <option value="<?= $mk->id_mk ?>"><?= $mk->kode_mk ?> - <?= $mk->nama_mk ?> (<?= $mk->sks ?> SKS)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="control-label mb-1">Kelas</label>
                                        <input name="kelas" placeholder="Contoh: A, B, C, Pagi" class="form-control" type="text" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="control-label mb-1">Jml Mahasiswa</label>
                                        <input name="kapasitas_mhs" placeholder="Contoh: 40" class="form-control" type="number" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Dosen Pengajar</label>
                                <select name="id_dosen" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Dosen --</option>
                                    <?php foreach($dosen as $d): ?>
                                        <option value="<?= $d->id_dosen ?>"><?= $d->nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <h6 class="text-warning border-bottom pb-2 mb-3">Plotting Waktu & Ruang</h6>
                            
                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Hari</label>
                                <select name="hari" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="control-label mb-1">Jam Mulai</label>
                                        <input name="jam_mulai" class="form-control" type="time" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="control-label mb-1">Jam Selesai</label>
                                        <input name="jam_selesai" class="form-control" type="time" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="control-label mb-1">Ruangan</label>
                                <select name="id_ruangan" class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    <?php foreach($ruangan as $r): ?>
                                        <option value="<?= $r->id_ruangan ?>"><?= $r->nama_ruangan ?> (Kap: <?= $r->kapasitas_kuliah ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Simpan & Validasi</button>
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

    table = $('#table_jadwal').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('jadwal/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0, 8 ], "orderable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });

    $('#table_jadwal').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        confirmDelete(function(){
            $.ajax({
                url : "<?= base_url('jadwal/delete')?>/"+id,
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

    $('#table_jadwal').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#form')[0].reset(); 
        $('.select2').val('').trigger('change');
        
        $.ajax({
            url : "<?= base_url('jadwal/get_by_id/')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('[name="id_jadwal"]').val(data.id_jadwal);
                $('[name="id_prodi"]').val(data.id_prodi).trigger('change');
                $('[name="id_mk"]').val(data.id_mk).trigger('change');
                $('[name="kelas"]').val(data.kelas);
                $('[name="kapasitas_mhs"]').val(data.kapasitas_mhs);
                $('[name="id_dosen"]').val(data.id_dosen).trigger('change');
                $('[name="hari"]').val(data.hari).trigger('change');
                $('[name="jam_mulai"]').val(data.jam_mulai);
                $('[name="jam_selesai"]').val(data.jam_selesai);
                $('[name="id_ruangan"]').val(data.id_ruangan).trigger('change');
                
                $('#modal_form').modal('show');
                $('.modal-title').text('Edit Jadwal Kuliah'); 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error!', 'Gagal mengambil data dari ajax');
            }
        });
    });

    window.add_jadwal = function() {
        $('#form')[0].reset();
        $('[name="id_jadwal"]').val('');
        $('.select2').val('').trigger('change');
        $('#modal_form').modal('show');
        $('.modal-title').text('Tambah Jadwal Kuliah Baru');
    };

    window.save = function() {
        $('#btnSave').text('Memvalidasi Bentrok...');
        $('#btnSave').attr('disabled',true);
        
        $.ajax({
            url : "<?= base_url('jadwal/save')?>",
            type: "POST",
            data: $('#form').serialize(),
            dataType: "JSON",
            success: function(data) {
                if(data.status === 'success') {
                    $('#modal_form').modal('hide');
                    if(data.message.includes('PERINGATAN')) {
                        Swal.fire('Tersimpan dengan Peringatan!', data.message, 'warning');
                    } else {
                        showSuccess('Sukses!', data.message);
                    }
                    table.ajax.reload(null,false);
                } else {
                    showError('Validasi Gagal!', data.message);
                }
                $('#btnSave').text('Simpan & Validasi');
                $('#btnSave').attr('disabled',false);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError('Error Server!', 'Terjadi kesalahan saat memvalidasi dan menyimpan data.');
                $('#btnSave').text('Simpan & Validasi');
                $('#btnSave').attr('disabled',false);
            }
        });
    };
});
</script>
