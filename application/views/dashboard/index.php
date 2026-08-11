<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">
                        <?= !empty($is_viewer) ? 'Dashboard Saya' : 'Dashboard' ?>
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid fade-in">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_fakultas ?></h3>
                            <p><?= !empty($is_viewer) ? 'Fakultas Terkait' : 'Fakultas' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_prodi ?></h3>
                            <p><?= !empty($is_viewer) ? 'Prodi Diampu' : 'Program Studi' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_dosen ?></h3>
                            <p><?= !empty($is_viewer) ? 'Profil Dosen' : 'Dosen' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_ruangan ?></h3>
                            <p><?= !empty($is_viewer) ? 'Ruangan Dipakai' : 'Ruangan' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_jadwal ?></h3>
                            <p><?= !empty($is_viewer) ? 'Jadwal Saya' : 'Total Jadwal' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary rounded-3 shadow-sm">
                        <div class="inner p-3">
                            <h3><?= $jml_mk ?></h3>
                            <p><?= !empty($is_viewer) ? 'MK Diampu' : 'Mata Kuliah' ?></p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
            
            <!-- Charts Section (Placeholders for future data) -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= !empty($is_viewer) ? 'Jadwal Saya per Hari' : 'Grafik Penggunaan Ruang' ?></h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartPenggunaan" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= !empty($is_viewer) ? 'Jadwal Saya per Fakultas' : 'Jadwal per Fakultas' ?></h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartFakultas" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<script>
// Will initialize chart when DOM is fully loaded via footer's app.js
document.addEventListener('DOMContentLoaded', function () {
    const ctx1 = document.getElementById('chartPenggunaan').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= $chart_hari_labels ?>,
            datasets: [{
                label: 'Jumlah Jadwal',
                data: <?= $chart_hari_data ?>,
                backgroundColor: 'rgba(0, 62, 126, 0.7)',
                borderColor: 'rgba(0, 62, 126, 1)',
                borderWidth: 1
            }]
        }
    });

    const ctx2 = document.getElementById('chartFakultas').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= $chart_fakultas_labels ?>,
            datasets: [{
                data: <?= $chart_fakultas_data ?>,
                backgroundColor: <?= $chart_fakultas_colors ?>,
            }]
        }
    });
});
</script>
