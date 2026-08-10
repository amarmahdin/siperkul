<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--primary-color); font-weight: 700;">Audit Trail</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid fade-in">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Log Aktivitas Pengguna</h3>
                </div>
                <div class="card-body">
                    <table id="table_audit" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal & Waktu</th>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                                <th>Keterangan</th>
                                <th>IP Address</th>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var table;

    table = $('#table_audit').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?= base_url('audit/get_data')?>",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": [ 0 ], "orderable": false }
        ],
        "language": {
            "url": "<?= base_url('assets/datatables/i18n/id.json') ?>"
        }
    });
});
</script>
