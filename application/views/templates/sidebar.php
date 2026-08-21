    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?= base_url(($this->session->userdata('role') === 'Viewer') ? 'monitoring' : 'dashboard') ?>" class="brand-link text-center">
            <i class="fas fa-university fa-2x mb-2 text-warning"></i><br>
            <span class="brand-text font-weight-bold">SIPERKUL ITPLN</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-4">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    <li class="nav-item">
                        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($this->uri->segment(1) == 'dashboard') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <?php if(in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])): ?>
                    <li class="nav-header">MASTER DATA</li>
                    
                    <?php /* Menu master disembunyikan sementara
                    <li class="nav-item">
                        <a href="<?= base_url('fakultas') ?>" class="nav-link <?= ($this->uri->segment(1) == 'fakultas') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Data Fakultas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('prodi') ?>" class="nav-link <?= ($this->uri->segment(1) == 'prodi') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-graduation-cap"></i>
                            <p>Data Program Studi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('dosen') ?>" class="nav-link <?= ($this->uri->segment(1) == 'dosen') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>Data Dosen</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('mata_kuliah') ?>" class="nav-link <?= ($this->uri->segment(1) == 'mata_kuliah') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Mata Kuliah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('gedung') ?>" class="nav-link <?= ($this->uri->segment(1) == 'gedung') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-city"></i>
                            <p>Gedung</p>
                        </a>
                    </li>
                    */ ?>
                    <li class="nav-item">
                        <a href="<?= base_url('ruangan') ?>" class="nav-link <?= ($this->uri->segment(1) == 'ruangan') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-door-open"></i>
                            <p>Ruangan</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-header">TRANSAKSI</li>
                    <li class="nav-item">
                        <a href="<?= base_url('jadwal') ?>" class="nav-link <?= ($this->uri->segment(1) == 'jadwal') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Jadwal Kuliah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('monitoring') ?>" class="nav-link <?= ($this->uri->segment(1) == 'monitoring') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Monitoring Ruangan</p>
                        </a>
                    </li>

                    <?php /* Menu Verifikasi Viewer disembunyikan sementara
                    <?php if(in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])): ?>
                    <li class="nav-header">VERIFIKASI</li>
                    <li class="nav-item">
                        <a href="<?= base_url('verifikasi') ?>" class="nav-link <?= ($this->uri->segment(1) == 'verifikasi') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-check"></i>
                            <p>
                                Verifikasi Viewer
                                <?php
                                $CI =& get_instance();
                                if (!isset($CI->Verifikasi_model)) {
                                    $CI->load->model('Verifikasi_model');
                                }
                                $jml_v = $CI->Verifikasi_model->count_pending();
                                if ($jml_v > 0):
                                ?>
                                <span class="badge badge-warning right"><?= $jml_v ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                    <?php endif; ?>
                    */ ?>

                    <?php if(in_array($this->session->userdata('role'), ['Administrator'])): ?>
                    <li class="nav-header">PENGATURAN</li>
                    <li class="nav-item">
                        <a href="<?= base_url('pengaturan') ?>" class="nav-link <?= ($this->uri->segment(1) == 'pengaturan') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Sistem Settings</p>
                        </a>
                    </li>
                    <?php /* Menu Audit Trail disembunyikan sementara
                    <li class="nav-item">
                        <a href="<?= base_url('audit') ?>" class="nav-link <?= ($this->uri->segment(1) == 'audit') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Audit Trail</p>
                        </a>
                    </li>
                    */ ?>
                    <?php endif; ?>
                    
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
