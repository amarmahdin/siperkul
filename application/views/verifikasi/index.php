<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Verifikasi Akun Viewer</h1>
                    <p class="text-muted mb-0">Persetujuan dosen yang mendaftar via Microsoft SSO</p>
                </div>
                <div class="col-sm-6 text-right">
                    <span class="badge badge-warning p-2">Menunggu: <?= (int) $jml_pending ?></span>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">Antrian Menunggu Verifikasi</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped" id="tablePending">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama</th>
                                <th>Email SSO</th>
                                <th>Tanggal Daftar</th>
                                <th>Hubungkan ke Dosen</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada akun menunggu verifikasi.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($pending as $p): ?>
                                    <tr data-id="<?= $p->id_user ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($p->nama_lengkap) ?></td>
                                        <td><?= htmlspecialchars($p->email) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($p->created_at)) ?></td>
                                        <td>
                                            <select class="form-control form-control-sm select-dosen" style="width:100%;">
                                                <option value="">-- Pilih Dosen --</option>
                                                <?php foreach ($dosen as $d): ?>
                                                    <option value="<?= $d->id_dosen ?>">
                                                        <?= htmlspecialchars($d->kode_dosen . ' - ' . $d->nama) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success btn-approve">
                                                <i class="fas fa-check"></i> Acc
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-reject">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-primary card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title">Semua Akun Viewer</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover" id="tableAll">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Dosen Terhubung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($viewers as $v): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($v->nama_lengkap) ?></td>
                                    <td><?= htmlspecialchars($v->email ?: '-') ?></td>
                                    <td>
                                        <?php if ($v->status === 'Aktif'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php elseif ($v->status === 'Menunggu'): ?>
                                            <span class="badge badge-warning">Menunggu</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $v->kode_dosen
                                            ? htmlspecialchars($v->kode_dosen . ' - ' . $v->nama_dosen)
                                            : '<span class="text-muted">-</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function () {
    $('.select-dosen').select2({ width: '100%' });

    $(document).on('click', '.btn-approve', function () {
        var row = $(this).closest('tr');
        var id_user = row.data('id');
        var id_dosen = row.find('.select-dosen').val();

        if (!id_dosen) {
            Swal.fire('Perhatian', 'Pilih dosen yang akan dihubungkan terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Aktifkan akun ini?',
            text: 'Viewer akan terhubung ke dosen terpilih dan bisa melihat jadwalnya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Acc'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post('<?= base_url('verifikasi/approve') ?>', {
                id_user: id_user,
                id_dosen: id_dosen
            }, function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', res.message, 'success').then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            });
        });
    });

    $(document).on('click', '.btn-reject', function () {
        var id_user = $(this).closest('tr').data('id');

        Swal.fire({
            title: 'Tolak akun ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post('<?= base_url('verifikasi/reject') ?>', {
                id_user: id_user
            }, function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', res.message, 'success').then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            });
        });
    });
});
</script>
