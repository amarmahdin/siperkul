<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;"><?= isset($title) ? $title : 'Monitoring Ruangan' ?></h1>
                    <?php if (!empty($is_viewer)): ?>
                        <?php if (!empty($dosen_linked)): ?>
                            <p class="text-muted mb-0 mt-1">Menampilkan jadwal mengajar: <strong><?= htmlspecialchars($nama_dosen) ?></strong></p>
                        <?php else: ?>
                            <p class="text-danger mb-0 mt-1">Akun belum dihubungkan ke data dosen. Hubungi Admin/BAAK untuk verifikasi ulang.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Filter Hari</label>
                                <select id="filter_hari" class="form-control select2">
                                    <?php 
                                    $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    foreach($hari_list as $h): ?>
                                        <option value="<?= $h ?>" <?= $h == $hari_ini ? 'selected' : '' ?>><?= $h ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Filter Gedung</label>
                                <select id="filter_gedung" class="form-control select2">
                                    <option value="">-- Semua Gedung --</option>
                                    <?php foreach($gedung as $g): ?>
                                        <option value="<?= $g->id_gedung ?>"><?= $g->nama_gedung ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button class="btn btn-primary w-100" onclick="loadGrid()"><i class="fas fa-sync"></i> Refresh Data</button>
                                <small class="text-muted d-block mt-1 text-center">Auto-refresh setiap 10 detik</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legenda -->
            <div class="mb-3">
                <span class="badge bg-warning text-dark px-3 py-2 mr-2"><i class="fas fa-exclamation-circle"></i> Hampir Penuh (>90%)</span>
                <span class="badge bg-danger px-3 py-2 mr-2"><i class="fas fa-times-circle"></i> Over Kapasitas</span>
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-bed"></i> Kosong</span>
            </div>

            <!-- Grid Excel-like -->
            <div class="card">
                <div class="card-body p-0" style="overflow-x: auto;">
                    <table class="table table-bordered table-sm text-center mb-0" id="monitoring_grid" style="min-width: 1200px;">
                        <thead class="bg-primary text-white">
                            <tr id="grid_header">
                                <!-- Generated via AJAX -->
                            </tr>
                        </thead>
                        <tbody id="grid_body">
                            <!-- Generated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal_detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Penggunaan Ruang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detail_content">
                <!-- Content injected here -->
            </div>
        </div>
    </div>
</div>

<style>
    .cell-terpakai { cursor: pointer; transition: transform 0.2s; }
    .cell-terpakai:hover { transform: scale(0.95); opacity: 0.9; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select2 diinisialisasi global via assets/js/app.js (dengan search)

    // Initial load
    loadGrid();

    // Auto refresh every 10 seconds
    setInterval(loadGrid, 10000);
});

function loadGrid() {
    let hari = $('#filter_hari').val();
    let gedung = $('#filter_gedung').val();

    $.ajax({
        url: "<?= base_url('monitoring/load_grid') ?>",
        type: "POST",
        data: { hari: hari, id_gedung: gedung },
        dataType: "JSON",
        success: function(response) {
            // Build Header
            let th = '<th width="150" class="align-middle">Ruangan</th>';
            response.jam_slots.forEach(jam => {
                th += `<th>${jam}</th>`;
            });
            $('#grid_header').html(th);

            // Build Body
            let tbody = '';
            response.grid.forEach(row => {
                let tr = `<tr><td class="font-weight-bold bg-light text-left pl-3">${row.ruangan}<br><small class="text-muted">Kap: ${row.kapasitas}</small></td>`;
                
                row.cells.forEach(cell => {
                    if(cell.status === 'terpakai') {
                        // Serialize data for modal
                        let dataCell = `data-mk="${cell.mk}" data-dosen="${cell.dosen}" data-kelas="${cell.kelas}" data-waktu="${cell.waktu}" data-mhs="${cell.mhs}" data-ruang="${row.ruangan}"`;
                        
                        tr += `<td class="${cell.warna} text-white cell-terpakai align-middle" style="font-size: 12px; line-height: 1.1;" onclick='showDetail(this)' ${dataCell}>
                                <strong>${cell.mk} - ${cell.dosen}</strong><br>
                                ${cell.kelas}<br>
                            </td>`;
                    } else {
                        tr += `<td class="${cell.warna} align-middle text-muted" style="font-size:10px;">-</td>`;
                    }
                });
                tr += '</tr>';
                tbody += tr;
            });

            $('#grid_body').html(tbody);
        }
    });
}

function showDetail(el) {
    let mk = $(el).data('mk');
    let dosen = $(el).data('dosen');
    let kelas = $(el).data('kelas');
    let waktu = $(el).data('waktu');
    let mhs = $(el).data('mhs');
    let ruang = $(el).data('ruang');

    let html = `
        <table class="table table-borderless table-sm">
            <tr><th width="35%">Ruangan</th><td>: ${ruang}</td></tr>
            <tr><th>Mata Kuliah</th><td>: <strong>${mk}</strong></td></tr>
            <tr><th>Kelas</th><td>: ${kelas}</td></tr>
            <tr><th>Dosen</th><td>: ${dosen}</td></tr>
            <tr><th>Waktu</th><td>: ${waktu}</td></tr>
            <tr><th>Jml Mahasiswa</th><td>: ${mhs} orang</td></tr>
        </table>
    `;

    $('#detail_content').html(html);
    $('#modal_detail').modal('show');
}
</script>
