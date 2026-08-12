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
                                <th>Pilih Dosen (untuk Acc)</th>
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
                                    <?php
                                    $preselect = '';
                                    $email_sso = strtolower(trim($p->email));
                                    foreach ($dosen as $d) {
                                        if (!empty($d->email) && strtolower(trim($d->email)) === $email_sso) {
                                            $preselect = $d->id_dosen;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr data-id="<?= $p->id_user ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($p->nama_lengkap) ?></td>
                                        <td><?= htmlspecialchars($p->email) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($p->created_at)) ?></td>
                                        <td>
                                            <select class="form-control form-control-sm select2 select-dosen" style="width:100%;">
                                                <option value="">-- Pilih Dosen --</option>
                                                <?php foreach ($dosen as $d): ?>
                                                    <option value="<?= $d->id_dosen ?>" <?= ((string)$preselect === (string)$d->id_dosen) ? 'selected' : '' ?>>
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
